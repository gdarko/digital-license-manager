<?php

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use Exception;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus as LicenseStatusEnum;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Stock;
use IdeoLogix\DigitalLicenseManager\Utils\ArrayFormatter as ArrayUtil;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher as AdminNotice;
use IdeoLogix\DigitalLicenseManager\Utils\StringFormatter;
use TCPDF;

defined( 'ABSPATH' ) || exit;

/**
 * Class Licenses
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Licenses {
	/**
	 * Licenses constructor.
	 */
	public function __construct() {

		// Admin POST requests
		add_action( 'admin_post_dlm_import_license_keys', array( $this, 'importLicenseKeys' ), 10 );
		add_action( 'admin_post_dlm_add_license_key', array( $this, 'createLicenseKey' ), 10 );
		add_action( 'admin_post_dlm_update_license_key', array( $this, 'updateLicenseKey' ), 10 );
		add_action( 'admin_post_dlm_licenses_export', array( $this, 'exportLicensesForm' ), 10, 1 );

		// AJAX calls
		add_action( 'wp_ajax_dlm_show_license_key', array( $this, 'showLicenseKey' ), 10 );
		add_action( 'wp_ajax_dlm_show_all_license_keys', array( $this, 'showAllLicenseKeys' ), 10 );

		// Export related
		add_action( 'dlm_export_license_keys_pdf', array( $this, 'exportLicensesPDF' ), 10, 1 );
		add_action( 'dlm_export_license_keys_csv', array( $this, 'exportLicensesCSV' ), 10, 1 );
		add_action( 'wp_ajax_dlm_license_certificate_export', array( $this, 'exportLicenseCertificate' ), 10 );
	}

	/**
	 * Import licenses from a compatible CSV or TXT file into the database.
	 */
	public function importLicenseKeys() {

		check_admin_referer( 'dlm_import_license_keys' );

		if ( ! current_user_can( 'dlm_create_licenses' ) ) {
			AdminNotice::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}

		$orderId     = null;
		$productId   = null;
		$userId      = null;
		$status      = LicenseStatusEnum::ACTIVE;
		$source      = isset( $_POST['source'] ) ? sanitize_text_field( $_POST['source'] ) : 0;
		$licenseKeys = array();

		if ( array_key_exists( 'order_id', $_POST ) && $_POST['order_id'] ) {
			$orderId = intval( $_POST['order_id'] );
		}

		if ( array_key_exists( 'product_id', $_POST ) && $_POST['product_id'] ) {
			$productId = intval( $_POST['product_id'] );
		}

		if ( array_key_exists( 'user_id', $_POST ) && $_POST['user_id'] ) {
			$userId = intval( $_POST['user_id'] );
		}

		if ( array_key_exists( 'status', $_POST ) && $_POST['status'] && in_array( $_POST['status'], LicenseStatusEnum::$status ) ) {
			$status = intval( $_POST['status'] );
		}

		if ( $source === 'file' ) {
			$licenseKeys = $this->parseImportFile();
		} elseif ( $source === 'clipboard' ) {
			$licenseKeys = $this->parseImportClipboard();
		}

		if ( ! is_array( $licenseKeys ) || count( $licenseKeys ) === 0 ) {
			AdminNotice::error( __( 'There was a problem importing the license keys.', 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}
		$validFor       = isset( $_POST['valid_for'] ) ? intval( $_POST['valid_for'] ) : null;
		$maxActivations = isset( $_POST['activations_limit'] ) ? intval( $_POST['activations_limit'] ) : null;

		// Save the imported keys
		$result = LicenseUtil::saveImportedLicenseKeys(
			$licenseKeys,
			$status,
			$orderId,
			$productId,
			$userId,
			$validFor,
			$maxActivations
		);
		if ( is_wp_error( $result ) ) {
			AdminNotice::error( __( $result->get_error_message(), 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}

		// Redirect according to $result.
		if ( $result['failed'] == 0 && $result['added'] == 0 ) {
			AdminNotice::error( __( 'There was a problem importing the license keys.', 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}

		if ( $result['failed'] == 0 && $result['added'] > 0 ) {
			// Update the stock
			if ( $status === LicenseStatusEnum::ACTIVE ) {
				Stock::syncrhonizeProductStock($productId);
			}

			// Display a success message
			AdminNotice::success(
				sprintf(
					__( '%d license key(s) added successfully.', 'digital-license-manager' ),
					(int) $result['added']
				)
			);
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}

		if ( $result['failed'] > 0 && $result['added'] == 0 ) {
			AdminNotice::error( __( 'There was a problem importing the license keys.', 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}

		if ( $result['failed'] > 0 && $result['added'] > 0 ) {
			// Update the stock
			if ( $status === LicenseStatusEnum::ACTIVE ) {
				Stock::syncrhonizeProductStock($productId);
			}

			// Display a warning message
			AdminNotice::warning(
				sprintf(
					__( '%d key(s) have been imported, while %d key(s) were not imported.', 'digital-license-manager' ),
					(int) $result['added'],
					(int) $result['failed']
				)
			);
			wp_redirect( sprintf( 'admin.php?page=%s&action=import', PageSlug::LICENSES ) );
			exit();
		}
	}

	/**
	 * Add a single license key to the database.
	 */
	public function createLicenseKey() {

		// Check the nonce
		check_admin_referer( 'dlm_add_license_key' );

		if ( ! current_user_can( 'dlm_create_licenses' ) ) {
			AdminNotice::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s', PageSlug::LICENSES ) );
		} else {
			$licenseKey  = isset( $_POST['license_key'] ) ? sanitize_text_field( $_POST['license_key'] ) : '';
			$licenseData = ArrayUtil::only( $_POST, array(
				'license_key',
				'status',
				'order_id',
				'product_id',
				'user_id',
				'expires_at',
				'source',
				'activations_limit',
			) );

			$license = LicenseUtil::create( $licenseKey, $licenseData );

			if ( is_wp_error( $license ) ) {
				if ( 'data_error' === $license->get_error_code() ) {
					AdminNotice::error( $license->get_error_message() );
				} else {
					AdminNotice::error( __( 'There was a problem adding the license key.', 'digital-license-manager' ) );
				}
			} else {
				AdminNotice::success( __( '1 license key(s) added successfully.', 'digital-license-manager' ) );
			}

			wp_redirect( sprintf( 'admin.php?page=%s&action=add', PageSlug::LICENSES ) );
		}
		exit();

	}

	/**
	 * Updates an existing license keys.
	 *
	 * @throws Exception
	 */
	public function updateLicenseKey() {
		// Check the nonce
		check_admin_referer( 'dlm_update_license_key' );

		if ( ! current_user_can( 'dlm_edit_licenses' ) ) {
			AdminNotice::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			wp_redirect( sprintf( 'admin.php?page=%s', PageSlug::LICENSES ) );
		} else {
			$licenseId   = isset( $_POST['license_id'] ) ? absint( $_POST['license_id'] ) : null;
			$licenseData = ArrayUtil::only( $_POST, array(
				'license_key',
				'status',
				'order_id',
				'product_id',
				'user_id',
				'expires_at',
				'source',
				'activations_limit',
				'valid_for',
			) );
			$license     = LicenseUtil::update( $licenseId, $licenseData );
			if ( is_wp_error( $license ) ) {
				if ( 'data_error' === $license->get_error_code() ) {
					AdminNotice::error( $license->get_error_message() );
				} else {
					AdminNotice::error( __( 'There was a problem updating the license key.', 'digital-license-manager' ) );
				}
			} else {
				AdminNotice::success( __( 'Your license key has been updated successfully.', 'digital-license-manager' ) );
			}
			wp_redirect( sprintf( 'admin.php?page=%s&action=edit&id=%d', PageSlug::LICENSES, $licenseId ) );
		}

		exit();
	}

	/**
	 * Show a single license key.
	 */
	public function showLicenseKey() {
		// Validate request.
		check_ajax_referer( 'dlm_show_license_key', 'show' );

		if ( ! current_user_can( 'dlm_read_licenses' ) ) {
			wp_send_json( 'ERROR' );
			wp_die();
		}

		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			wp_die( __( 'Invalid request.', 'digital-license-manager' ) );
		}

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->findBy( array( 'id' => intval( $_POST['id'] ) ) );

		$decrypted = $license->getDecryptedLicenseKey();
		if ( is_wp_error( $decrypted ) ) {
			wp_send_json( 'ERROR' );
		}

		wp_send_json( $decrypted );

		wp_die();
	}

	/**
	 * Shows all visible license keys.
	 */
	public function showAllLicenseKeys() {
		// Validate request.
		check_ajax_referer( 'dlm_show_all_license_keys', 'show_all' );

		if ( ! current_user_can( 'dlm_read_licenses' ) ) {
			wp_send_json( 'ERROR' );
			wp_die();
		}

		if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			wp_die( __( 'Invalid request.', 'digital-license-manager' ) );
		}

		$licenseKeysIds = array();

		foreach ( json_decode( $_POST['ids'] ) as $licenseKeyId ) {
			$licenseKeyId = intval( $licenseKeyId );
			/** @var LicenseResourceModel $license */
			$license = LicenseResourceRepository::instance()->find( $licenseKeyId );

			$licenseKey = $license->getDecryptedLicenseKey();
			if ( ! is_wp_error( $license ) ) {
				$licenseKeysIds[ $licenseKeyId ] = $licenseKey;
			} else {
				$licenseKeysIds[ $licenseKeyId ] = 'ERROR';
			}
		}

		wp_send_json( $licenseKeysIds );
	}


	/**
	 * Parses the licenses from the uploaded CSV/TXT file.
	 *
	 * @return array|false|null
	 */
	public function parseImportFile() {
		$tmp_file             = 'import.tmp';
		$duplicateLicenseKeys = array();
		$licenseKeys          = null;
		$ext                  = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
		$mimes                = array( 'application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv' );
		$fileName             = $_FILES['file']['tmp_name'];
		$filePath             = DLM_ASSETS_DIR . $tmp_file;

		/**
		 * Validate the file extension
		 */
		if ( ! in_array( $ext, array( 'txt', 'csv' ) ) || ! in_array( $_FILES['file']['type'], $mimes ) ) {
			AdminNotice::error( __( 'Invalid file type, only TXT and CSV allowed.', 'digital-license-manager' ) );
			wp_redirect(
				sprintf(
					'admin.php?page=%s&action=import',
					PageSlug::LICENSES
				)
			);
			exit();
		}

		/**
		 * File upload file, return with error.
		 */
		if ( ! move_uploaded_file( $fileName, $filePath ) ) {
			return null;
		}

		/**
		 * Handle txt and csv types
		 */
		if ( $ext == 'txt' ) {
			$licenseKeys = file( DLM_ASSETS_DIR . $tmp_file, FILE_IGNORE_NEW_LINES );
			if ( ! is_array( $licenseKeys ) ) {
				AdminNotice::error( __( 'Invalid file content.', 'digital-license-manager' ) );
				wp_redirect(
					sprintf(
						'admin.php?page=%s&action=import',
						PageSlug::LICENSES
					)
				);
				exit();
			}
		} elseif ( $ext == 'csv' ) {
			$licenseKeys = array();
			if ( ( $handle = fopen( DLM_ASSETS_DIR . $tmp_file, 'r' ) ) !== false ) {
				while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
					if ( $data && is_array( $data ) && count( $data ) > 0 ) {
						$licenseKeys[] = $data[0];
					}
				}

				fclose( $handle );
			}
		}

		/**
		 * Check for duplicates
		 */
		foreach ( $licenseKeys as $i => $licenseKey ) {
			if ( LicenseUtil::isKeyDuplicate( $licenseKey ) ) {
				unset( $licenseKeys[ $i ] );
				$duplicateLicenseKeys[] = $licenseKey;
				continue;
			}
		}
		if ( count( $duplicateLicenseKeys ) > 0 ) {
			AdminNotice::warning(
				sprintf(
					__( '%d license(s) skipped because they already exist.', 'digital-license-manager' ),
					count( $duplicateLicenseKeys )
				)
			);
			if ( count( $licenseKeys ) === 0 ) {
				wp_redirect(
					sprintf(
						'admin.php?page=%s&action=import',
						PageSlug::LICENSES
					)
				);
				exit();
			}
		}

		/**
		 * Delete the temporary file now that we're done.
		 */
		unlink( $tmp_file );

		return $licenseKeys;
	}

	/**
	 * Parses the licenses clipboard and prepares for import.
	 * @return array|false|string[]
	 */
	public function parseImportClipboard() {
		$data = preg_split( '/[\r\n]+/', $_POST['clipboard'] );
		if ( ! empty( $data ) ) {
			$data = array_map( 'sanitize_text_field', $data );
		}

		return $data;
	}

	/**
	 * Creates a PDF of license keys by the given array of ID's.
	 *
	 * @param array $licenseKeyIds
	 */
	public function exportLicensesPDF( $licenseKeyIds ) {
		$licenseKeys = array();

		foreach ( $licenseKeyIds as $licenseKeyId ) {
			/** @var LicenseResourceModel $license */
			$license = LicenseResourceRepository::instance()->find( $licenseKeyId );

			if ( ! $license ) {
				continue;
			}

			$decrypted = $license->getDecryptedLicenseKey();
			if ( is_wp_error( $decrypted ) ) {
				$decrypted = '';
			}

			$licenseKeys[] = array(
				'id'          => esc_attr( $license->getId() ),
				'order_id'    => esc_attr( $license->getOrderId() ),
				'product_id'  => esc_attr( $license->getProductId() ),
				'license_key' => esc_attr( $decrypted )
			);
		}

		$header = array(
			'id'          => __( 'ID', 'digital-license-manager' ),
			'order_id'    => __( 'Order ID', 'digital-license-manager' ),
			'product_id'  => __( 'Product ID', 'digital-license-manager' ),
			'license_key' => __( 'License key', 'digital-license-manager' )
		);

		ob_clean();

		$pdf = new TCPDF( 'P', 'mm', 'A4' );
		$pdf->AddPage();
		$pdf->AddFont( 'Helvetica', '', 'helvetica.php' );
		$pdf->AddFont( 'Courier', '', 'courier.php' );
		$pdf->setFont('Helvetica');

		// Header
		$pdf->Text( 10, 10, get_bloginfo( 'name' ) );
		$pdf->Ln( 25 );

		// Table Header
		$pdf->SetDrawColor( 200, 200, 200 );

		foreach ( $header as $columnName => $col ) {
			$width = 40;

			if ( $columnName == 'id' ) {
				$width = 12;
			}

			if ( $columnName == 'order_id'
			     || $columnName == 'product_id'
			) {
				$width = 20;
			}

			if ( $columnName == 'license_key' ) {
				$width = 0;
			}

			$pdf->Cell( $width, 10, $col, 'B' );
		}

		// Data
		$pdf->Ln();

		foreach ( $licenseKeys as $row ) {
			foreach ( $row as $columnName => $col ) {
				$pdf->SetFont( 'DejaVuSans', '', 8 );
				$width = 40;

				if ( $columnName == 'id' ) {
					$width = 12;
				}

				if ( $columnName == 'order_id'
				     || $columnName == 'product_id'
				) {
					$width = 20;
				}

				if ( $columnName == 'license_key' ) {
					$pdf->SetFont( 'Courier', '', 8 );
					$width = 0;
				}

				$pdf->Cell( $width, 6, $col, 'B' );
			}

			$pdf->Ln();
		}

		$pdf->Output( date( 'YmdHis' ) . '_license_keys_export.pdf', 'D' );
	}

	/**
	 * Creates a CSV of license keys by the given array of ID's.
	 *
	 * @param array $licenseKeyIds
	 */
	public function exportLicensesCSV( $licenseKeyIds, $columns = array() ) {
		$licenseKeys = array();

		if ( empty( $columns ) ) {
			$columns = self::exportColumns();
			$columns = array_column( $columns, 'slug' );
		}

		foreach ( $licenseKeyIds as $licenseKeyId ) {
			/** @var LicenseResourceModel $license */
			$license = LicenseResourceRepository::instance()->find( $licenseKeyId );
			$data    = array();

			if ( ! $license ) {
				continue;
			}

			foreach ( $columns as $exportColumn ) {

				switch ( $exportColumn ) {
					case 'license_key':
						$decrypted = $license->getDecryptedLicenseKey();
						if ( is_wp_error( $decrypted ) ) {
							$decrypted = '';
						}
						$data[ $exportColumn ] = esc_attr( $decrypted );
						break;
					case 'status':
						$data[ $exportColumn ] = LicenseStatus::getExportLabel( $license->getStatus() );
						break;
					default:
						$getter                = 'get' . StringFormatter::camelize( $exportColumn );
						$data[ $exportColumn ] = null;

						if ( method_exists( $license, $getter ) ) {
							$data[ $exportColumn ] = esc_attr( $license->{$getter}() );
						}

						break;
				}
			}

			$licenseKeys[] = $data;
		}

		$licenseKeys = apply_filters( 'dlm_export_license_csv', $licenseKeys );
		$filename    = date( 'YmdHis' ) . '_license_keys_export.csv';

		// Disable caching
		status_header( 200 );
		$now = gmdate( "D, d M Y H:i:s" );
		header( "Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate" );
		header( "Last-Modified: {$now} GMT" );

		// Force download
		header( "Content-Type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Type: application/download" );

		// Disposition / encoding on response body
		header( "Content-Disposition: attachment;filename={$filename}" );
		header( "Content-Transfer-Encoding: binary" );

		ob_clean();
		ob_start();
		$df = fopen( "php://output", 'w' );
		fputcsv( $df, array_keys( $licenseKeys[0] ) );

		foreach ( $licenseKeys as $row ) {
			fputcsv( $df, $row );
		}

		fclose( $df );
		ob_end_flush();

		exit();
	}

	/**
	 * Export the licenses from custom form.
	 */
	public function exportLicensesForm() {

		$errors = array();

		check_admin_referer( 'dlm_export_licenses' );

		if ( ! current_user_can( 'dlm_export_licenses' ) ) {
			array_push( $errors, __( 'Permission denied. You don\'t have access to this resource.', 'digital-license-manager' ) );
		}

		$list    = isset( $_POST['dlm_export_licenses'] ) && ! empty( $_POST['dlm_export_licenses'] ) ? explode( ',', $_POST['dlm_export_licenses'] ) : array();
		$columns = isset( $_POST['dlm_export_columns'] ) && ! empty( $_POST['dlm_export_columns'] ) ? $_POST['dlm_export_columns'] : array();

		if ( ! empty( $list ) ) {
			$list = array_map( 'intval', $list );
		}
		if ( ! empty( $columns ) ) {
			$columns = array_map( 'sanitize_text_field', $_POST['dlm_export_columns'] );
		}

		if ( empty( $list ) ) {
			array_push( $errors, __( 'No licenses were selected.', 'digital-license-manager' ) );
		}

		if ( ! empty( $errors ) ) {
			AdminNotice::warning( $errors[0] );
			wp_redirect(
				admin_url(
					sprintf( 'admin.php?page=%s', PageSlug::LICENSES )
				)
			);
		} else {
			$this->exportLicensesCSV( $list, $columns );
		}
		exit();
	}

	/**
	 * List of available columns
	 * @return array[]
	 */
	public static function exportColumns() {
		return array(
			array(
				'slug' => 'id',
				'name' => __( 'ID', 'digital-license-manager' )
			),
			array(
				'slug' => 'order_id',
				'name' => __( 'Order ID', 'digital-license-manager' )
			),
			array(
				'slug' => 'product_id',
				'name' => __( 'Product ID', 'digital-license-manager' )
			),
			array(
				'slug' => 'user_id',
				'name' => __( 'User ID', 'digital-license-manager' )
			),
			array(
				'slug' => 'license_key',
				'name' => __( 'License key', 'digital-license-manager' )
			),
			array(
				'slug' => 'expires_at',
				'name' => __( 'Expires at', 'digital-license-manager' )
			),
			array(
				'slug' => 'status',
				'name' => __( 'Status', 'digital-license-manager' )
			),
			array(
				'slug' => 'activations_limit',
				'name' => __( 'Activation Limit', 'digital-license-manager' )
			),
			array(
				'slug' => 'created_at',
				'name' => __( 'Created at', 'digital-license-manager' )
			),
			array(
				'slug' => 'created_by',
				'name' => __( 'Created by', 'digital-license-manager' )
			),
			array(
				'slug' => 'updated_at',
				'name' => __( 'Updated at', 'digital-license-manager' )
			),
			array(
				'slug' => 'updated_by',
				'name' => __( 'Updated by', 'digital-license-manager' )
			)
		);
	}

	/**
	 * Handle the license export
	 *
	 * @param $license
	 *
	 * @return void
	 */
	public function exportLicenseCertificate() {
		check_admin_referer( 'dlm_export_licenses' );
		echo 'hery.';
	}

	/**
	 * Returns the license certificate export url
	 *
	 * @param $license_id
	 *
	 * @return string
	 */
	public static function getLicenseCertificateExportUrl( $license_id ) {
		return add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( self::getLicenseCertificateExportNonceKey() ),
				'action'   => 'dlm_license_certificate_export',
				'license'  => $license_id,
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the license certificate export nonce key
	 * @return string
	 */
	public static function getLicenseCertificateExportNonceKey() {
		return 'dlm_license_export';
	}
}
