<?php


namespace IdeoLogix\DigitalLicenseManager\Utils\Data;


use DateInterval;
use DateTime;
use Exception;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseActivation;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation as LicenseActivationResourcesRepository;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus as LicenseStatusEnum;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Stock;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;
use WC_Order;
use WP_Error;

/**
 * Class License
 * @package IdeoLogix\DigitalLicenseManager\Utils\Data
 */
class License {

	/**
	 * Retrieves a single license from the database.
	 *
	 * @param string $licenseKey The license key to be deleted.
	 *
	 * @return LicenseResourceModel|WP_Error
	 */
	public static function find( $licenseKey ) {

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->findBy(
			array(
				'hash' => StringHasher::license( $licenseKey ),
			)
		);

		if ( ! $license ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' could not be found", 'digital-license-manager' ), $licenseKey ), array( 'code' => 404 ) );
		}

		return $license;
	}

	/**
	 * Retrieves a single license from the database by ID
	 *
	 * @param $licenseId
	 *
	 * @return LicenseResourceModel|WP_Error
	 */
	public static function findById( $licenseId ) {

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->find( $licenseId );
		if ( ! $license ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' could not be found", 'digital-license-manager' ), $licenseKey ), array( 'code' => 404 ) );
		}

		return $license;
	}

	/**
	 * Retrieves multiple license keys by a query array.
	 *
	 * @param array $query Key/value pairs with the license table column names as keys
	 *
	 * @return LicenseResourceModel[]|WP_Error
	 */
	public static function get( $query = array() ) {
		if ( array_key_exists( 'license_key', $query ) ) {
			$query['hash'] = StringHasher::license( $query['license_key'] );
			unset( $query['license_key'] );
		}

		/** @var LicenseResourceModel[] $licenses */
		$licenses = ! empty( $query ) ? LicenseResourceRepository::instance()->findAllBy( $query ) : LicenseResourceRepository::instance()->findAll();

		if ( ! $licenses ) {
			return new WP_Error( 'data_error', __( "No licence keys found for your query.", 'digital-license-manager' ), array( 'code' => 404 ) );
		}

		return $licenses;
	}

	/**
	 * Adds a new license to the database.
	 *
	 * @param string $licenseKey The license key being added
	 * @param array $licenseData Key/value pairs with the license table column names as keys
	 *
	 * @return LicenseResourceModel|\WP_Error
	 */
	public static function create( $licenseKey, $licenseData = array() ) {

		$status           = LicenseStatusEnum::INACTIVE;
		$orderId          = null;
		$productId        = null;
		$userId           = null;
		$expiresAt        = null;
		$activationsLimit = null;
		$source           = LicenseSource::IMPORT;

		if ( array_key_exists( 'status', $licenseData ) ) {
			$status = $licenseData['status'];
		}

		if ( array_key_exists( 'order_id', $licenseData ) ) {
			$orderId = is_numeric( $licenseData['order_id'] ) ? absint( $licenseData['order_id'] ) : null;
		}

		if ( array_key_exists( 'product_id', $licenseData ) ) {
			$productId = is_numeric( $licenseData['product_id'] ) ? absint( $licenseData['product_id'] ) : null;
		}

		if ( array_key_exists( 'user_id', $licenseData ) ) {
			$userId = is_numeric( $licenseData['user_id'] ) ? absint( $licenseData['user_id'] ) : null;
		}

		if ( array_key_exists( 'expires_at', $licenseData ) ) {
			$expiresAt = $licenseData['expires_at'];
		}

		if ( array_key_exists( 'source', $licenseData ) ) {
			$source = is_numeric( $licenseData['source'] ) ? absint( $licenseData['source'] ) : null;
		}

		if ( array_key_exists( 'activations_limit', $licenseData ) ) {
			$activationsLimit = is_numeric( $licenseData['activations_limit'] ) ? absint( $licenseData['activations_limit'] ) : null;
		}

		if ( ! in_array( $status, LicenseStatusEnum::$status ) ) {
			return new WP_Error( 'data_error', "The license status is invalid. Possible values are: 1 for SOLD, 2 for DELIVERED, 3 for ACTIVE, 4 for INACTIVE, and 5 for DISABLED.", array( 'status' => 400 ) );
		}

		if ( empty( $licenseKey ) ) {
			return new WP_Error( 'data_error', 'The license key is invalid.', array( 'status' => 422 ) );
		}

		if ( self::isKeyDuplicate( $licenseKey ) ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' already exists", 'digital-license-manager' ), $licenseKey ), array( 'code' => 409 ) );
		}

		if ( ! empty( $expiresAt ) ) {
			try {
				new DateTime( $expiresAt );
			} catch ( Exception $e ) {
				return new WP_Error( 'data_error', $e->getMessage(), array( 'code' => 422 ) );
			}
		} else {
			$expiresAt = null;
		}

		$encryptedLicenseKey = CryptoHelper::encrypt( $licenseKey );
		if ( is_wp_error( $encryptedLicenseKey ) ) {
			return $encryptedLicenseKey;
		}
		$hashedLicenseKey = StringHasher::license( $licenseKey );

		$queryData = array(
			'order_id'          => $orderId,
			'product_id'        => $productId,
			'user_id'           => $userId,
			'license_key'       => $encryptedLicenseKey,
			'hash'              => $hashedLicenseKey,
			'expires_at'        => $expiresAt,
			'source'            => $source,
			'status'            => $status,
			'activations_limit' => $activationsLimit
		);

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->insert( $queryData );

		if ( ! $license ) {
			return new WP_Error( 'server_error', sprintf( __( "The license key '%s' could not be added", 'digital-license-manager' ), $licenseKey ), array( 'code' => 500 ) );
		}

		// Update the stock
		if ( $license->getProductId() !== null && $license->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::increase( $license->getProductId() );
		}

		return $license;
	}

	/**
	 * Updates the specified license.
	 *
	 * @param string $licenseKey The license key being updated.
	 * @param array $licenseData Key/value pairs of the updated data.
	 *
	 * @return LicenseResourceModel|WP_Error
	 */
	public static function update( $licenseKey, $licenseData ) {
		$updateData = array();

		if ( ! $licenseKey ) {
			return new WP_Error( 'data_error', 'The license key is invalid.', array( 'status' => 422 ) );
		}

		/** @var LicenseResourceModel $oldLicense */
		if ( is_numeric( $licenseKey ) ) {
			$oldLicense = LicenseResourceRepository::instance()->find( $licenseKey );
		} else {
			$oldLicense = LicenseResourceRepository::instance()->findBy( array( 'hash' => StringHasher::license( $licenseKey ), ) );
		}

		if ( ! $oldLicense ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' could not be found", 'digital-license-manager' ), $licenseKey ), array( 'code' => 404 ) );
		}

		// Order ID
		if ( array_key_exists( 'order_id', $licenseData ) ) {
			if ( $licenseData['order_id'] === null ) {
				$updateData['order_id'] = null;
			} else {
				$updateData['order_id'] = (int) $licenseData['order_id'];
			}
		}

		// Product ID
		if ( array_key_exists( 'product_id', $licenseData ) ) {
			if ( $licenseData['product_id'] === null ) {
				$updateData['product_id'] = null;
			} else {
				$updateData['product_id'] = (int) $licenseData['product_id'];
			}
		}

		// User ID
		if ( array_key_exists( 'user_id', $licenseData ) ) {
			if ( $licenseData['user_id'] === null ) {
				$updateData['user_id'] = null;
			} else {
				$updateData['user_id'] = (int) $licenseData['user_id'];
			}
		}

		// Valid for
		if ( array_key_exists( 'valid_for', $licenseData ) ) {
			if ( empty( $licenseData['valid_for'] ) ) {
				$updateData['valid_for'] = null;
			} else {
				$updateData['valid_for'] = (int) $licenseData['valid_for'];
			}
		}


		// License key
		if ( array_key_exists( 'license_key', $licenseData ) ) {
			// Check for possible duplicates
			if ( self::isKeyDuplicate( $licenseData['license_key'], $oldLicense->getId() ) ) {
				return new WP_Error( 'data_error', sprintf( __( "The license key '%s' already exists", 'digital-license-manager' ), $licenseData['license_key'] ), array( 'code' => 409 ) );
			}

			$updateData['license_key'] = CryptoHelper::encrypt( $licenseData['license_key'] );
			if ( is_wp_error( $updateData['license_key'] ) ) {
				return $licenseData['license_key'];
			}
			$updateData['hash'] = StringHasher::license( $licenseData['license_key'] );
		}

		// Expires at
		if ( array_key_exists( 'expires_at', $licenseData ) ) {
			if ( ! empty( $licenseData['expires_at'] ) ) {
				try {
					new DateTime( $licenseData['expires_at'] );
				} catch ( Exception $e ) {
					return new WP_Error( 'data_error', $e->getMessage(), array( 'code' => 422 ) );
				}
				$updateData['expires_at'] = $licenseData['expires_at'];
			} else {
				$updateData['expires_at'] = null;
			}
		}

		// Status
		if ( array_key_exists( 'status', $licenseData ) ) {
			if ( ! in_array( (int) $licenseData['status'], LicenseStatusEnum::$status ) ) {
				return new WP_Error( 'data_error', "The license status is invalid. Possible values are: 1 for SOLD, 2 for DELIVERED, 3 for ACTIVE, 4 for INACTIVE, and 5 for DISABLED.", array( 'status' => 400 ) );
			}

			$updateData['status'] = (int) $licenseData['status'];
		}

		// Times activated max
		if ( array_key_exists( 'activations_limit', $licenseData ) ) {
			$updateData['activations_limit'] = is_numeric( $licenseData['activations_limit'] ) ? absint( $licenseData['activations_limit'] ) : null;
		}

		// Update the stock
		if ( $oldLicense->getProductId() !== null && $oldLicense->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::decrease( $oldLicense->getCreatedAt() );
		}

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->updateBy(
			array(
				'hash' => $oldLicense->getHash()
			),
			$updateData
		);

		if ( ! $license ) {
			return new WP_Error( 'server_error', sprintf( __( "The license key '%s' could not be updated.", 'digital-license-manager' ), $licenseKey ), array( 'code' => 500 ) );
		}

		$newLicenseHash = StringHasher::license( $licenseKey );

		if ( array_key_exists( 'hash', $updateData ) ) {
			$newLicenseHash = $updateData['hash'];
		}

		/** @var LicenseResourceModel $newLicense */
		$newLicense = LicenseResourceRepository::instance()->findBy( array( 'hash' => $newLicenseHash ) );

		if ( ! $newLicense ) {
			return new WP_Error( 'server_error', __( 'The updated license key could not be found.', 'digital-license-manager' ), array( 'code' => 500 ) );
		}

		// Update the stock
		if ( $newLicense->getProductId() !== null && $newLicense->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::increase( $newLicense->getProductId() );
		}

		return $newLicense;
	}

	/**
	 * Deletes the specified license.
	 *
	 * @param string $licenseKey The license key to be deleted.
	 *
	 * @return bool|WP_Error
	 */
	public static function delete( $licenseKey ) {
		/** @var LicenseResourceModel $oldLicense */
		$oldLicense = LicenseResourceRepository::instance()->findBy(
			array(
				'hash' => StringHasher::license( $licenseKey )
			)
		);

		// Update the stock
		if ( $oldLicense && $oldLicense->getProductId() !== null && $oldLicense->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::decrease( $oldLicense->getProductId() );
		}

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->deleteBy(
			array(
				'hash' => StringHasher::license( $licenseKey ),
			)
		);

		if ( ! $license ) {
			return new WP_Error( 'server_error', sprintf( __( "The license key '%s' could not be found.", 'digital-license-manager' ), $licenseKey ), array( 'code' => 500 ) );
		}

		return true;
	}

	/**
	 * Activates license and returns the activation data.
	 *
	 * @param string $licenseKey The license key to be activated.
	 * @param array $params
	 *
	 * @return LicenseActivation|WP_Error
	 */
	public static function activate( $licenseKey, $params ) {

		$activationLabel = isset( $params['label'] ) ? $params['label'] : '';
		$activationMeta  = isset( $params['meta'] ) && is_array( $params['meta'] ) ? $params['meta'] : array();

		/** @var LicenseResourceModel $license */
		$license = LicenseResourceRepository::instance()->findBy( array( 'hash' => StringHasher::license( $licenseKey ) ) );

		if ( ! $license ) {
			return new WP_Error( sprintf( __( "The license key '%s' could not be found", 'digital-license-manager' ), $licenseKey ) );
		}

		if ( ! $licenseKey ) {
			return new WP_Error(
				'data_error',
				'License key is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var LicenseResourceModel $license */
			$license = LicenseResourceRepository::instance()->findBy( array( 'hash' => StringHasher::license( $licenseKey ) ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'data_error', $e->getMessage(), array( 'status' => 404 ) );
		}

		if ( ! $license ) {
			return new WP_Error( 'data_error', sprintf( 'License Key: %s could not be found.', $licenseKey ), array( 'status' => 404 ) );
		}

		if ( false !== $licenseExpired = self::hasLicenseExpired( $license ) ) {
			return $licenseExpired;
		}

		if ( false !== $licenseDisabled = self::isLicenseDisabled( $license ) ) {
			return $licenseDisabled;
		}

		$validateLimit = self::validateActivationLimit( $license );
		if ( is_wp_error( $validateLimit ) ) {
			return $validateLimit;
		}

		// Activate the license key
		try {

			$newToken = self::generateActivationToken( $licenseKey );
			if ( is_null( $newToken ) ) {
				return new WP_Error( 'data_error', sprintf( 'Unable to generate activation token hash for license: %s', $licenseKey ), array( 'status' => 404 ) );
			}

			/* @var LicenseActivation $licenseActivation */
			$activationParams = array(
				'license_id' => $license->getId(),
				'token'      => $newToken,
				'source'     => ActivationSource::API,
				'ip_address' => HttpHelper::clientIp(),
				'user_agent' => HttpHelper::userAgent(),
			);

			// Set label
			if ( ! empty( $activationLabel ) ) {
				$activationParams['label'] = $activationLabel;
			}

			// Set metadata
			if ( is_array( $activationMeta ) ) {
				$activationParams['meta_data'] = $activationMeta;
			}

			// Store.
			$licenseActivation = LicenseActivationResourcesRepository::instance()->insert( $activationParams );

			if ( ! $licenseActivation ) {
				return new WP_Error( 'server_error', __( 'Unable to activate key', 'digital-license-manager' ), array( 'status' => 500 ) );
			}

		} catch ( Exception $e ) {
			return new WP_Error( 'server_error', $e->getMessage(), array( 'status' => 500 ) );
		}

		return $licenseActivation;
	}

	/**
	 * Reactivate license
	 *
	 * @param $activationToken
	 *
	 * @return bool|AbstractResourceModel|WP_Error
	 */
	public static function reactivate( $activationToken, $licenseKey = null ) {
		if ( ! $activationToken ) {
			return new WP_Error(
				'data_error',
				'License activation token is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var LicenseActivation $activation */
			$activation = LicenseActivationResourcesRepository::instance()->findBy(
				array(
					'token' => $activationToken
				)
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'data_error',
				$e->getMessage(),
				array( 'status' => 404 )
			);
		}

		if ( ! $activation ) {
			return new WP_Error(
				'data_error',
				'License activation token could not be found.',
				array( 'status' => 404 )
			);
		}

		if ( empty( $activation->getDeactivatedAt() ) ) {
			return new WP_Error(
				'data_error',
				'License activation token is already activated.',
				array( 'status' => 404 )
			);
		}

		$license = $activation->getLicense();
		if ( ! $license ) {
			return new WP_Error(
				'data_error',
				'License could not be found.',
				array( 'status' => 404 )
			);
		}
		if ( ! is_null( $licenseKey ) ) {
			$licenseKeyDec = $license->getDecryptedLicenseKey();
			if ( $licenseKeyDec !== $licenseKey ) {
				return new WP_Error(
					'data_error',
					'License invalid. Cheati\'n huh?.',
					array( 'status' => 404 )
				);
			}
		}

		if ( false !== $licenseExpired = self::hasLicenseExpired( $license ) ) {
			return $licenseExpired;
		}
		if ( false !== $licenseDisabled = self::isLicenseDisabled( $license ) ) {
			return $licenseDisabled;
		}

		$validateLimit = self::validateActivationLimit( $license );
		if ( is_wp_error( $validateLimit ) ) {
			return $validateLimit;
		}

		$updated = LicenseActivationResourcesRepository::instance()->update( $activation->getId(), array(
			'deactivated_at' => null,
		) );

		if ( $updated ) {
			$updatedActivation = LicenseActivationResourcesRepository::instance()->find( $activation->getId() );
		} else {
			return new WP_Error(
				'server_error',
				'Unable to activate license key',
				array( 'status' => 500 )
			);
		}

		return $updatedActivation;
	}

	/**
	 * Deactivates license in the database
	 *
	 * @param $activationToken
	 *
	 * @return bool|AbstractResourceModel|WP_Error
	 */
	public static function deactivate( $activationToken ) {

		if ( ! $activationToken ) {
			return new WP_Error(
				'data_error',
				'License activation token is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var LicenseActivation $activation */
			$activation = LicenseActivationResourcesRepository::instance()->findBy(
				array(
					'token' => $activationToken
				)
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'data_error',
				$e->getMessage(),
				array( 'status' => 404 )
			);
		}

		if ( ! $activation ) {
			return new WP_Error(
				'data_error',
				'License activation token could not be found.',
				array( 'status' => 404 )
			);
		}

		if ( ! empty( $activation->getDeactivatedAt() ) ) {
			return new WP_Error(
				'data_error',
				'License activation token is already deactivated.',
				array( 'status' => 404 )
			);
		}

		$license = $activation->getLicense();
		if ( ! $license ) {
			return new WP_Error(
				'data_error',
				'License could not be found.',
				array( 'status' => 404 )
			);
		}

		if ( false !== $licenseExpired = self::hasLicenseExpired( $license ) ) {
			return $licenseExpired;
		}
		if ( false !== $licenseDisabled = self::isLicenseDisabled( $license ) ) {
			return $licenseDisabled;
		}

		$updated = LicenseActivationResourcesRepository::instance()->update( $activation->getId(), array(
			'deactivated_at' => gmdate( 'Y-m-d H:i:s' ),
		) );

		if ( $updated ) {
			$updatedActivation = LicenseActivationResourcesRepository::instance()->find( $activation->getId() );
		} else {
			return new WP_Error(
				'server_error',
				'Unable to deactivate license key',
				array( 'status' => 500 )
			);
		}

		return $updatedActivation;

	}

	/**
	 * Checks if the license has an expiry date and if it has expired already.
	 *
	 * @param LicenseResourceModel $license
	 *
	 * @return false|WP_Error
	 */
	public static function hasLicenseExpired( $license ) {

		if ( $license->isExpired() ) {
			return new WP_Error(
				'license_expired',
				sprintf(
				/* translators: %s: expiration date */
					__( 'The license key expired at %s.', 'digital-license-manager' ),
					wp_date( DateFormatter::getExpirationFormat(), strtotime( $license->getExpiresAt() ) )
				),
				array( 'status' => 405 )
			);
		}

		return false;
	}

	/**
	 * Checks if the license is disabled.
	 *
	 * @param LicenseResourceModel $license
	 *
	 * @return false|WP_Error
	 */
	public static function isLicenseDisabled( $license ) {
		if ( $license->getStatus() === LicenseStatus::DISABLED ) {
			return new WP_Error(
				'license_disabled',
				'The license Key is disabled.',
				array( 'status' => 405 )
			);
		}

		return false;
	}

	/**
	 * Checks if a license key already exists inside the database table.
	 *
	 * @param string $licenseKey
	 * @param null|int $licenseKeyId
	 *
	 * @return bool
	 */
	public static function isKeyDuplicate( $licenseKey, $licenseKeyId = null ) {

		if ( Settings::get( 'allow_duplicates', Settings::SECTION_GENERAL ) ) {
			return false;
		}

		$duplicate = false;
		$hash      = StringHasher::license( $licenseKey );

		// Add action
		if ( is_null( $licenseKeyId ) ) {
			$query = array( 'hash' => $hash );
			if ( LicenseResourceRepository::instance()->findBy( $query ) ) {
				$duplicate = true;
			}
		} // Update action
		elseif ( is_numeric( $licenseKeyId ) ) {
			global $wpdb;
			$table = LicenseResourceRepository::instance()->getTable();
			$query = $wpdb->prepare( "SELECT id FROM {$table} WHERE hash=%s AND id NOT LIKE %s", $hash, "%" . $licenseKeyId . "%" );
			if ( LicenseResourceRepository::instance()->query( $query ) ) {
				$duplicate = true;
			}
		}

		return $duplicate;
	}

	/**
	 * Imports an array of un-encrypted license keys.
	 *
	 * @param array $licenseKeys License keys to be stored
	 * @param int $status License key status
	 * @param int $orderId WooCommerce Order ID
	 * @param int $productId WooCommerce Product ID
	 * @param int $userId WordPress User ID
	 * @param int $validFor Validity period (in days)
	 * @param int $activationsLimit Maximum activation count
	 *
	 * @return array|WP_Error
	 */
	public static function saveImportedLicenseKeys( $licenseKeys, $status, $orderId, $productId, $userId, $validFor, $activationsLimit ) {
		$result                = array();
		$cleanLicenseKeys      = array();
		$cleanStatus           = $status ? absint( $status ) : null;
		$cleanOrderId          = $orderId ? absint( $orderId ) : null;
		$cleanProductId        = $productId ? absint( $productId ) : null;
		$cleanUserId           = $userId ? absint( $userId ) : null;
		$cleanValidFor         = $validFor ? absint( $validFor ) : null;
		$cleanActivationsLimit = $activationsLimit ? absint( $activationsLimit ) : null;

		if ( ! is_array( $licenseKeys ) ) {
			return new WP_Error( 'data_error', __( 'License Keys must be provided as array', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		if ( ! $cleanStatus || ! in_array( $cleanStatus, LicenseStatus::$status ) ) {
			return new WP_Error( 'data_error', __( 'License Status is invalid', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		foreach ( $licenseKeys as $licenseKey ) {
			array_push( $cleanLicenseKeys, sanitize_text_field( $licenseKey ) );
		}

		$result['added']  = 0;
		$result['failed'] = 0;


		// Add the keys to the database table.
		foreach ( $cleanLicenseKeys as $licenseKey ) {

			$encrypted = CryptoHelper::encrypt( $licenseKey );
			if ( is_wp_error( $encrypted ) ) {
				return $encrypted;
			}
			$hashed = StringHasher::license( $licenseKey );

			$license = LicenseResourceRepository::instance()->insert(
				array(
					'order_id'          => $cleanOrderId,
					'product_id'        => $cleanProductId,
					'user_id'           => $cleanUserId,
					'license_key'       => $encrypted,
					'hash'              => $hashed,
					'source'            => LicenseSource::IMPORT,
					'status'            => $cleanStatus,
					'valid_for'         => $cleanValidFor,
					'activations_limit' => $cleanActivationsLimit,
				)
			);

			if ( $license ) {
				$result['added'] ++;
			} else {
				$result['failed'] ++;
			}
		}

		return $result;
	}

	/**
	 * Save the license keys for a given product to the database.
	 *
	 * @param int $orderId WooCommerce Order ID
	 * @param int $productId WooCommerce Product ID
	 * @param string[] $licenseKeys License keys to be stored
	 * @param int $status License key status
	 * @param GeneratorResourceModel $generator
	 * @param int $validFor
	 *
	 * @return array|bool|WP_Error
	 */
	public static function saveGeneratedLicenseKeys( $orderId, $productId, $licenseKeys, $status, $generator, $validFor ) {

		$cleanLicenseKeys = array();
		$cleanOrderId     = ( $orderId ) ? absint( $orderId ) : null;
		$cleanProductId   = ( $productId ) ? absint( $productId ) : null;
		$cleanStatus      = ( $status ) ? absint( $status ) : null;
		$validFor         = is_numeric( $validFor ) && absint( $validFor ) > 0 ? absint( $validFor ) : null;
		$userId           = null;

		if ( ! $cleanStatus || ! in_array( $cleanStatus, LicenseStatus::$status ) ) {
			return new WP_Error( 'data_error', __( 'License Status is invalid', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		if ( ! is_array( $licenseKeys ) ) {
			return new WP_Error( 'data_error', __( 'License Keys must be provided as array', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		foreach ( $licenseKeys as $licenseKey ) {
			array_push( $cleanLicenseKeys, sanitize_text_field( $licenseKey ) );
		}

		if ( count( $cleanLicenseKeys ) === 0 ) {
			return new WP_Error( 'data_error', __( 'No License Keys were provided', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		/** @var WC_Order $order */
		if ( function_exists( 'wc_get_order' ) ) {
			if ( $order = wc_get_order( $orderId ) ) {
				$userId = $order->get_user_id();
			}
		}

		try {
			$expiresAt = null;
			if ( $generator->getExpiresIn() && $status == LicenseStatus::SOLD ) {
				$expiresAt = null;
				if ( is_numeric( $generator->getExpiresIn() ) && $generator->getExpiresIn() > 0 ) {
					$expiresAt = DateFormatter::addDaysInFuture( $generator->getExpiresIn(), 'now', 'Y-m-d H:i:s' );
				}
			}
		} catch ( \Exception $e ) {
			return new WP_Error( 'data_error', $e->getMessage(), array( 'code' => 500 ) );
		}

		// Add the keys to the database table.
		$invalidKeysAmount = 0;
		foreach ( $cleanLicenseKeys as $licenseKey ) {
			// Key exists, up the invalid keys count.
			if ( self::isKeyDuplicate( $licenseKey ) ) {
				$invalidKeysAmount ++;
				continue;
			}

			// Key doesn't exist, add it to the database table.
			$encryptedLicenseKey = CryptoHelper::encrypt( $licenseKey );
			if ( is_wp_error( $encryptedLicenseKey ) ) {
				return $encryptedLicenseKey;
			}
			$hashedLicenseKey = StringHasher::license( $licenseKey );

			// Save to database.
			LicenseResourceRepository::instance()->insert(
				array(
					'order_id'          => $cleanOrderId,
					'product_id'        => $cleanProductId,
					'user_id'           => $userId,
					'license_key'       => $encryptedLicenseKey,
					'hash'              => $hashedLicenseKey,
					'expires_at'        => $expiresAt,
					'source'            => LicenseSource::GENERATOR,
					'status'            => $cleanStatus,
					'activations_limit' => $generator->getActivationsLimit() ?: null,
					'valid_for'         => $validFor,
				)
			);
		}

		// There have been duplicate keys, regenerate and add them.
		if ( $invalidKeysAmount > 0 ) {

			$newKeys = Generator::generateLicenseKeys( $invalidKeysAmount, $generator );
			if ( is_wp_error( $newKeys ) ) {
				return $newKeys;
			}

			return self::saveGeneratedLicenseKeys(
				$cleanOrderId,
				$cleanProductId,
				$newKeys,
				$cleanStatus,
				$generator,
				$validFor
			);
		} else {
			// Keys have been generated and saved, this order is now complete.
			update_post_meta( $cleanOrderId, 'dlm_order_complete', 1 );

			return true;
		}
	}

	/**
	 * Mark imported license keys as sold
	 *
	 * @param LicenseResourceModel[] $licenses License key resource models
	 * @param int $orderId WooCommerce Order ID
	 * @param int $amount Amount to be marked as sold
	 *
	 * @return bool|WP_Error
	 */
	public static function sellImportedLicenseKeys( $licenses, $orderId, $amount ) {
		$cleanLicenseKeys = $licenses;
		$cleanOrderId     = $orderId ? absint( $orderId ) : null;
		$cleanAmount      = $amount ? absint( $amount ) : null;
		$userId           = null;

		if ( ! is_array( $licenses ) || count( $licenses ) <= 0 ) {
			return new WP_Error( 'data_error', __( 'License Keys are invalid.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		if ( ! $cleanOrderId ) {
			return new WP_Error( 'data_error', __( 'Order ID is invalid.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		if ( ! $amount ) {
			return new WP_Error( 'data_error', __( 'Amount is invalid.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		if ( function_exists( 'wc_get_order' ) ) {
			/** @var WC_Order $order */
			if ( $order = wc_get_order( $cleanOrderId ) ) {
				$userId = $order->get_user_id();
			}
		}


		for ( $i = 0; $i < $cleanAmount; $i ++ ) {
			$license   = $cleanLicenseKeys[ $i ];
			$validFor  = (int) $license->getValidFor(); // In days.
			$expiresAt = $license->getExpiresAt();

			if ( $validFor ) {
				try {
					$date         = new DateTime();
					$dateInterval = new DateInterval( 'P' . $validFor . 'D' );
					$expiresAt    = $date->add( $dateInterval )->format( 'Y-m-d H:i:s' );
				} catch ( Exception $e ) {
					if ( empty( $expiresAt ) ) {
						return new WP_Error( 'data_error', __( 'Valid for invalid.', 'digital-license-manager' ), array( 'code' => 422 ) );
					}
				}
			}

			LicenseResourceRepository::instance()->update(
				$license->getId(),
				array(
					'order_id'   => $cleanOrderId,
					'user_id'    => $userId,
					'expires_at' => $expiresAt,
					'status'     => LicenseStatus::SOLD
				)
			);
		}

		return true;
	}

	/**
	 * Check the activations limit.
	 *
	 * @param LicenseResourceModel $license
	 *
	 * @return bool|WP_Error
	 */
	private static function validateActivationLimit( $license, $licenseKey = null ) {

		if ( empty( $license ) || ! ( $license instanceof LicenseResourceModel ) ) {
			return new WP_Error( 'license_not_found', __( 'Unknown license', 'digital-license-manager' ), array( 'status' => 404 ) );
		}

		$timesActivated   = $license->getTimesActivated();
		$activationsLimit = $license->getActivationsLimit();

		if ( empty( $licenseKey ) ) {
			$licenseKey = $license->getDecryptedLicenseKey();
		}

		if ( $timesActivated !== null ) {
			$timesActivated = absint( $timesActivated );
		}
		if ( $activationsLimit !== null ) {
			$activationsLimit = absint( $activationsLimit );
		}

		if ( $activationsLimit && ( $timesActivated >= $activationsLimit ) ) {
			return new WP_Error( 'license_activation_limit_reached', sprintf( 'License Key: %s reached maximum activation count.', $licenseKey ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Generates activation token
	 *
	 * @param $licenseKey
	 *
	 * @return string|null
	 */
	public static function generateActivationToken( $licenseKey ) {
		$token    = StringHasher::activation( $licenseKey );
		$reps     = 0;
		$max_reps = 20;
		while ( true ) {
			if ( (int) LicenseActivationResourcesRepository::instance()->countBy( [ 'token' => $token ] ) === 0 ) {
				break;
			} else if ( $reps > $max_reps ) {
				$token = null; // Do not enter in infinite loop.
				break;
			} else {
				$token = StringHasher::activation( $licenseKey );
			}
			$reps ++;
		}

		return $token;
	}
}
