<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;

class Certificates {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'dlm_myaccount_licenses_single_page_table_details', array( $this, 'addSingleLicenseCertificationDownload' ), 10, 5 );
		add_action( 'dlm_myaccount_handle_action', array( $this, 'handleAdditionalAccountActions' ) );
		add_filter( 'dlm_myaccount_whitelisted_actions', array( $this, 'whitelistAdditionalAccountActions' ) );
	}


	/**
	 * Handle additional account actions (eg. license certificate download)
	 * @return void
	 */
	public function handleAdditionalAccountActions( $action ) {

		if ( 'license_certificate_download' !== $action ) {
			return;
		}

		$licenseKey = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : null;
		$license    = LicenseUtil::find( $licenseKey );

		$this->generateCertificatePDF( $license );

	}

	/**
	 * Add the license certification download button to the single page
	 * @return void
	 */
	public function addSingleLicenseCertificationDownload( $license, $order, $product, $date_format, $license_key ) {

		echo wc_get_template_html(
			'dlm/my-account/licenses/partials/single-certificate-button.php',
			array(
				'license'     => $license,
				'license_key' => $license_key,
				'order'       => $order,
				'product'     => $product,
				'date_format' => $date_format,
			),
			'',
			Controller::getTemplatePath()
		);

	}

	/**
	 * Whitelist additional account actions
	 * @return array
	 */
	public function whitelistAdditionalAccountActions( $actions ) {

		return array_merge( $actions, array(
			'license_certificate_download',
		) );
	}


	/**
	 * Return the license certification data
	 *
	 * @param License $license
	 *
	 * @return mixed|void
	 */
	private function getCertificateData( $license ) {

		/**
		 * The data template
		 */
		$data = array(
			'title'                => '',
			'logo'                 => '',
			'license_product_name' => '',
			'license_details'      => array(), // eg. array('title' => 'Product Name', 'value' => 'Counter Strike')
		);

		/**
		 * Add option to developers to add their own data and skip our data generation process
		 */
		$data = apply_filters( 'dlm_license_certification_prefilter_data', $data, $license );
		if ( ! empty( $data['is_final'] ) ) {
			return apply_filters( $data, 'dlm_license_certification_data', $data, $license );
		}


		/**
		 * Get the logo
		 */
		$logo = Settings::get( 'company_logo', Settings::SECTION_GENERAL );
		if ( ! is_numeric( $logo ) ) {
			$logo = get_theme_mod( 'custom_logo' );
		}

		/**
		 * Get basic details
		 */
		$product  = $license->getProductId() ? wc_get_product( $license->getProductId() ) : null;
		$order    = $license->getOrderId() ? wc_get_order( $license->getOrderId() ) : null;
		$customer = $order ? $order->get_customer_id() : null;


		/**
		 * Setup the license details
		 */
		$license_details = array(
			array(
				'title' => __( 'License ID', 'digital-license-manager' ),
				'value' => sprintf( '#%d', $license->getId() ),
			),
			array(
				'title' => __( 'License Key', 'digital-license-manager' ),
				'value' => $license->getDecryptedLicenseKey(),
			),
			array(
				'title' => __( 'Expiry Date', 'digital-license-manager' ),
				'value' => empty( $license->getExpiresAt() ) ? __( 'Valid Permanently', 'digital-license-manager' ) : date_i18n( wc_date_format(), strtotime( $license->getExpiresAt() ) ),
			)
		);
		if ( $customer ) {
			$customer          = get_user_by( 'id', $customer );
			$license_details[] = array(
				'title' => __( 'Licensee', 'digital-license-manager' ),
				'value' => sprintf(
					'%s (#%d - %s)',
					$customer->display_name,
					$customer->ID,
					$customer->user_email
				)
			);
			if ( $order ) {
				$license_details[] = array(
					'title' => __( 'Order ID', 'digital-license-manager' ),
					'value' => sprintf( '#%d', $order->get_id() ),
				);
				$license_details[] = array(
					'title' => __( 'Order Date', 'digital-license-manager' ),
					'value' => date_i18n( wc_date_format(), strtotime( $order->get_date_paid() ) ),
				);
			}
		}
		if ( $product ) {
			$license_details[] = array(
				'title' => __( 'Product Name', 'digital-license-manager' ),
				'value' => $product->get_formatted_name(),
			);
			$license_details[] = array(
				'title' => __( 'Product URL', 'digital-license-manager' ),
				'value' => $product->get_permalink(),
			);
		}

		/**
		 * Setup the data
		 */
		$data['title']                = get_bloginfo( 'name' );
		$data['logo']                 = is_numeric( $logo ) ? wp_get_attachment_image_url( $logo, 'full' ) : null;
		$data['license_product_name'] = $product ? $product->get_formatted_name() : null;
		$data['license_details']      = $license_details;

		return apply_filters( 'dlm_license_certification_data', $data, $license );
	}


	/**
	 * Generate license certificate in PDF
	 * @param $license
	 *
	 * @return void
	 */
	public function generateCertificatePDF( $license ) {

		$errors = array();
		$order  = null;

		if ( is_wp_error( $license ) ) {
			array_push( $errors, $license->get_error_message() );
		} else {
			$order = wc_get_order( $license->getOrderId() );
			if ( empty( $order ) ) {
				array_push( $errors, __( 'Permission denied.', 'digital-license-manager' ) );
			}
		}

		/**
		 *  Validate customer
		 */
		if ( ! $order || get_current_user_id() !== $order->get_customer_id() ) {
			array_push( $errors, __( 'Permission denied.', 'digital-license-manager' ) );
		}
		if ( ! empty( $errors ) ) {
			wp_die( $errors[0] );
		}

		/**
		 * Render the template
		 */
		$content = wc_get_template_html(
			'dlm/my-account/licenses/partials/single-certificate.php',
			$this->getCertificateData( $license ),
			'',
			Controller::getTemplatePath()
		);


		/**
		 * Output the template
		 */
		try {
			$html2pdf = new Html2Pdf( 'L', 'A4', 'EN' );
			$html2pdf->addFont( 'DejaVuSans', '', 'dejavusans.php' );
			$html2pdf->addFont( 'Courier', '', 'courier.php' );
			$html2pdf->setDefaultFont('DejaVuSans');
			$html2pdf->writeHTML( $content );
			$html2pdf->output( 'license-certificate-'.$license->getId().'.pdf', 'D' );
		} catch ( Html2PdfException $e ) {
			$html2pdf->clean();
			$formatter = new ExceptionFormatter( $e );
			wp_die( $formatter->getHtmlMessage() );
		}
	}


	/**
	 * Check if license certification is enabled.
	 * @return bool
	 */
	public static function isLicenseCertificationEnabled() {
		static $value = null;
		if ( is_null( $value ) ) {
			$value = ( (int) Settings::get( 'enable_certificates', Settings::SECTION_WOOCOMMERCE ) ) > 0;

		}

		return $value;
	}


}
