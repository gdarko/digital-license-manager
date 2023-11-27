<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManager\Core\Services;

use DateInterval;
use DateTime;
use Exception;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ServiceInterface;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\MetadataInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseMeta;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseMeta as LicenseMetaRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations as LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
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
 * @package IdeoLogix\DigitalLicenseManager\Core\Services
 */
class LicensesService implements ServiceInterface, MetadataInterface {

	/**
	 * Find a single item from the database.
	 *
	 * @param mixed $id
	 *
	 * @return AbstractResourceModel|License|\WP_Error
	 */
	public function find( $id ) {

		/** @var License $license */
		$license = Licenses::instance()->findBy(
			array(
				'hash' => StringHasher::license( $id ),
			)
		);

		if ( ! $license ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' could not be found", 'digital-license-manager' ), $id ), array( 'code' => 404 ) );
		}

		return $license;
	}

	/**
	 * Retrieves single item from the database by ID
	 *
	 * @param $id
	 *
	 * @return AbstractResourceModel|License|WP_Error
	 */
	public function findById( $id ) {

		/** @var License $license */
		$license = Licenses::instance()->find( $id );
		if ( ! $license ) {
			return new WP_Error( 'data_error', sprintf( __( "The license id '%s' could not be found", 'digital-license-manager' ), $id ), array( 'code' => 404 ) );
		}

		return $license;
	}

	/**
	 * Retrieves multiple items by a query array.
	 *
	 * @param array $query
	 *
	 * @return AbstractResourceModel[]|License[]|WP_Error
	 */
	public function get( $query = array() ) {
		if ( array_key_exists( 'license_key', $query ) ) {
			$query['hash'] = StringHasher::license( $query['license_key'] );
			unset( $query['license_key'] );
		}

		/** @var License[] $licenses */
		$licenses = ! empty( $query ) ? Licenses::instance()->findAllBy( $query ) : Licenses::instance()->findAll();

		if ( empty( $licenses ) ) {
			return new WP_Error( 'data_error', __( "No licence keys found for your query.", 'digital-license-manager' ), array( 'code' => 404 ) );
		}

		return $licenses;
	}

	/**
	 * Creates a new entry to the database
	 *
	 * @param array $data
	 *
	 * @return AbstractResourceModel|License|\WP_Error
	 */
	public function create( $data = array() ) {

		$licenseKey       = isset( $data['license_key'] ) ? $data['license_key'] : null;
		$status           = LicenseStatusEnum::INACTIVE;
		$orderId          = null;
		$productId        = null;
		$userId           = null;
		$expiresAt        = null;
		$activationsLimit = null;
		$source           = LicenseSource::IMPORT;

		if ( array_key_exists( 'status', $data ) ) {
			$status = $data['status'];
		}

		if ( array_key_exists( 'order_id', $data ) ) {
			$orderId = is_numeric( $data['order_id'] ) ? absint( $data['order_id'] ) : null;
		}

		if ( array_key_exists( 'product_id', $data ) ) {
			$productId = is_numeric( $data['product_id'] ) ? absint( $data['product_id'] ) : null;
		}

		if ( array_key_exists( 'user_id', $data ) ) {
			$userId = is_numeric( $data['user_id'] ) ? absint( $data['user_id'] ) : null;
		}

		if ( array_key_exists( 'expires_at', $data ) ) {
			$expiresAt = $data['expires_at'];
		}

		if ( array_key_exists( 'source', $data ) ) {
			$source = is_numeric( $data['source'] ) ? absint( $data['source'] ) : null;
		}

		if ( array_key_exists( 'activations_limit', $data ) ) {
			$activationsLimit = is_numeric( $data['activations_limit'] ) ? absint( $data['activations_limit'] ) : null;
		}

		if ( ! in_array( $status, LicenseStatusEnum::$status ) ) {
			return new WP_Error( 'data_error', "The license status is invalid. Possible values are: 1 for SOLD, 2 for DELIVERED, 3 for ACTIVE, 4 for INACTIVE, and 5 for DISABLED.", array( 'status' => 400 ) );
		}

		if ( empty( $licenseKey ) ) {
			return new WP_Error( 'data_error', 'The license key is invalid.', array( 'status' => 422 ) );
		}

		if ( $this->isKeyDuplicate( $licenseKey ) ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' already exists", 'digital-license-manager' ), $licenseKey ), array( 'code' => 409 ) );
		}

		if ( ! empty( $expiresAt ) ) {
			if ( ! DateFormatter::validate( 'Y-m-d H:i:s', $expiresAt ) ) {
				return new WP_Error( 'data_error', __( 'Invalid expires at date format', 'digital-license-manager' ), array( 'code' => 422 ) );
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

		/** @var License $license */
		$license = Licenses::instance()->insert( $queryData );

		if ( ! $license ) {
			return new WP_Error( 'server_error', sprintf( __( "The license key '%s' could not be added", 'digital-license-manager' ), $licenseKey ), array( 'code' => 500 ) );
		}

		// Update the stock
		if ( $license->getProductId() !== null && $license->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::syncrhonizeProductStock( $license->getProductId() );
		}

		return $license;
	}

	/**
	 * Updates specific entry in the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return AbstractResourceModel|License|WP_Error
	 */
	public function update( $id, $data = [] ) {

		$licenseKey = (string) $id;
		$updateData = array();

		if ( ! $licenseKey ) {
			return new WP_Error( 'data_error', 'The license key is invalid.', array( 'status' => 422 ) );
		}

		/** @var License $oldLicense */
		if ( is_numeric( $licenseKey ) ) {
			$oldLicense = Licenses::instance()->find( $licenseKey );
		} else {
			$oldLicense = Licenses::instance()->findBy( array( 'hash' => StringHasher::license( $licenseKey ), ) );
		}

		if ( ! $oldLicense ) {
			return new WP_Error( 'data_error', sprintf( __( "The license key '%s' could not be found", 'digital-license-manager' ), $licenseKey ), array( 'code' => 404 ) );
		}

		if ( is_numeric( $licenseKey ) ) {
			$licenseKey = $oldLicense->getDecryptedLicenseKey();
		}

		// Order ID
		if ( array_key_exists( 'order_id', $data ) ) {
			if ( $data['order_id'] === null ) {
				$updateData['order_id'] = null;
			} else {
				$updateData['order_id'] = (int) $data['order_id'];
			}
		}

		// Product ID
		if ( array_key_exists( 'product_id', $data ) ) {
			if ( $data['product_id'] === null ) {
				$updateData['product_id'] = null;
			} else {
				$updateData['product_id'] = (int) $data['product_id'];
			}
		}

		// User ID
		if ( array_key_exists( 'user_id', $data ) ) {
			if ( $data['user_id'] === null ) {
				$updateData['user_id'] = null;
			} else {
				$updateData['user_id'] = (int) $data['user_id'];
			}
		}

		// Valid for
		if ( array_key_exists( 'valid_for', $data ) ) {
			if ( empty( $data['valid_for'] ) ) {
				$updateData['valid_for'] = null;
			} else {
				$updateData['valid_for'] = (int) $data['valid_for'];
			}
		}


		// License key
		if ( array_key_exists( 'license_key', $data ) && $data['license_key'] != $oldLicense->getDecryptedLicenseKey() ) {
			// Check for possible duplicates
			if ( $this->isKeyDuplicate( $data['license_key'], $oldLicense->getId() ) ) {
				return new WP_Error( 'data_error', sprintf( __( "The license key '%s' already exists", 'digital-license-manager' ), $data['license_key'] ), array( 'code' => 409 ) );
			}

			$updateData['license_key'] = CryptoHelper::encrypt( $data['license_key'] );
			if ( is_wp_error( $updateData['license_key'] ) ) {
				return $data['license_key'];
			}
			$updateData['hash'] = StringHasher::license( $data['license_key'] );
		}

		// Expires at
		if ( array_key_exists( 'expires_at', $data ) ) {
			if ( ! empty( $data['expires_at'] ) ) {
				try {
					new DateTime( $data['expires_at'] );
				} catch ( Exception $e ) {
					return new WP_Error( 'data_error', $e->getMessage(), array( 'code' => 422 ) );
				}
				$updateData['expires_at'] = $data['expires_at'];
			} else {
				$updateData['expires_at'] = null;
			}
		}

		// Status
		if ( array_key_exists( 'status', $data ) ) {
			if ( ! in_array( (int) $data['status'], LicenseStatusEnum::$status ) ) {
				return new WP_Error( 'data_error', "The license status is invalid. Possible values are: 1 for SOLD, 2 for DELIVERED, 3 for ACTIVE, 4 for INACTIVE, and 5 for DISABLED.", array( 'status' => 400 ) );
			}

			$updateData['status'] = (int) $data['status'];
		}

		// Times activated max
		if ( array_key_exists( 'activations_limit', $data ) ) {
			$updateData['activations_limit'] = is_numeric( $data['activations_limit'] ) ? absint( $data['activations_limit'] ) : null;
		}

		// Update the stock
		if ( $oldLicense->getProductId() !== null && $oldLicense->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::syncrhonizeProductStock( $oldLicense->getProductId() );
		}

		/** @var License $license */
		$license = Licenses::instance()->updateBy(
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

		/** @var License $newLicense */
		$newLicense = Licenses::instance()->findBy( array( 'hash' => $newLicenseHash ) );

		if ( ! $newLicense ) {
			return new WP_Error( 'server_error', __( 'The updated license key could not be found.', 'digital-license-manager' ), array( 'code' => 500 ) );
		}

		// Update the stock
		if ( $newLicense->getProductId() !== null && $newLicense->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::syncrhonizeProductStock( $newLicense->getProductId() );

		}

		return $newLicense;
	}

	/**
	 * Deletes specific entry from the database
	 *
	 * @param int|int[] $id
	 *
	 * @return bool|WP_Error
	 */
	public function delete( $id ) {

		$licenseKey = (string) $id;

		/** @var License $oldLicense */
		$oldLicense = Licenses::instance()->findBy(
			array(
				'hash' => StringHasher::license( $licenseKey )
			)
		);

		// Update the stock
		if ( $oldLicense && $oldLicense->getProductId() !== null && $oldLicense->getStatus() === LicenseStatusEnum::ACTIVE ) {
			Stock::syncrhonizeProductStock( $oldLicense->getProductId() );
		}

		/** @var License $license */
		$license = Licenses::instance()->deleteBy(
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
	public function activate( $licenseKey, $params ) {

		$activationLabel = isset( $params['label'] ) ? $params['label'] : '';
		$activationMeta  = isset( $params['meta'] ) && is_array( $params['meta'] ) ? $params['meta'] : array();

		/** @var License $license */
		$license = Licenses::instance()->findBy( array( 'hash' => StringHasher::license( $licenseKey ) ) );

		if ( ! $license ) {
			return new WP_Error(
				'data_error',
				sprintf(
					__( "The license key '%s' could not be found", 'digital-license-manager' ),
					$licenseKey
				),
				array( 'status' => 404 )
			);
		}

		if ( ! $licenseKey ) {
			return new WP_Error(
				'data_error',
				'License key is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var License $license */
			$license = Licenses::instance()->findBy( array( 'hash' => StringHasher::license( $licenseKey ) ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'data_error', $e->getMessage(), array( 'status' => 404 ) );
		}

		if ( ! $license ) {
			return new WP_Error( 'data_error', sprintf( 'License Key: %s could not be found.', $licenseKey ), array( 'status' => 404 ) );
		}

		$licenseExpired = $this->hasLicenseExpired( $license );
		if ( false !== $licenseExpired ) {
			return $licenseExpired;
		}

		$licenseDisabled = $this->isLicenseDisabled( $license );
		if ( false !== $licenseDisabled ) {
			return $licenseDisabled;
		}

		$validateLimit = $this->validateActivationLimit( $license );
		if ( is_wp_error( $validateLimit ) ) {
			return $validateLimit;
		}

		// Activate the license key
		try {

			$newToken = $this->generateActivationToken( $licenseKey );
			if ( is_null( $newToken ) ) {
				return new WP_Error( 'data_error', sprintf( 'Unable to generate activation token hash for license: %s', $licenseKey ), array( 'status' => 404 ) );
			}

			/* @var LicenseActivation $licenseActivation */
			$activationParams = array(
				'license_id' => $license->getId(),
				'token'      => $newToken,
				'source'     => isset( $params['source'] ) ? intval( $params['source'] ) : ActivationSource::API,
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
			$licenseActivation = LicenseActivations::instance()->insert( $activationParams );

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
	 * @param null $licenseKey
	 *
	 * @return bool|AbstractResourceModel|WP_Error
	 */
	public function reactivate( $activationToken, $licenseKey = null ) {
		if ( ! $activationToken ) {
			return new WP_Error(
				'data_error',
				'License activation token is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var LicenseActivation $activation */
			$activation = LicenseActivations::instance()->findBy(
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

		$licenseExpired = $this->hasLicenseExpired( $license );
		if ( false !== $licenseExpired ) {
			return $licenseExpired;
		}
		$licenseDisabled = $this->isLicenseDisabled( $license );
		if ( false !== $licenseDisabled ) {
			return $licenseDisabled;
		}

		$validateLimit = $this->validateActivationLimit( $license );
		if ( is_wp_error( $validateLimit ) ) {
			return $validateLimit;
		}

		$updated = LicenseActivations::instance()->update( $activation->getId(), array(
			'deactivated_at' => null,
		) );

		if ( $updated ) {
			$updatedActivation = LicenseActivations::instance()->find( $activation->getId() );
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
	public function deactivate( $activationToken ) {

		if ( ! $activationToken ) {
			return new WP_Error(
				'data_error',
				'License activation token is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var LicenseActivation $activation */
			$activation = LicenseActivations::instance()->findBy(
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
		$licenseDisabled = $this->isLicenseDisabled( $license );
		if ( false !== $licenseDisabled ) {
			return $licenseDisabled;
		}

		$updated = LicenseActivations::instance()->update( $activation->getId(), array(
			'deactivated_at' => gmdate( 'Y-m-d H:i:s' ),
		) );

		if ( $updated ) {
			$updatedActivation = LicenseActivations::instance()->find( $activation->getId() );
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
	 * Delete activation token
	 *
	 * @param $activationToken
	 *
	 * @return WP_Error|true
	 */
	public function deleteActivation( $activationToken ) {

		if ( ! $activationToken ) {
			return new WP_Error(
				'data_error',
				'License activation token is invalid.',
				array( 'status' => 404 )
			);
		}

		try {
			/** @var LicenseActivation $activation */
			return (bool) LicenseActivations::instance()->deleteBy(
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

	}

	/**
	 * Checks if the license has an expiry date and if it has expired already.
	 *
	 * @param License $license
	 *
	 * @return false|WP_Error
	 */
	public function hasLicenseExpired( $license ) {

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
	 * @param License $license
	 *
	 * @return false|WP_Error
	 */
	public function isLicenseDisabled( $license ) {
		if ( $license->getStatus() === LicenseStatusEnum::DISABLED ) {
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
	public function isKeyDuplicate( $licenseKey, $licenseKeyId = null ) {

		if ( intval( Settings::get( 'allow_duplicates', Settings::SECTION_GENERAL ) ) ) {
			return false;
		}

		$duplicate = false;
		$hash      = StringHasher::license( $licenseKey );

		// Add action
		if ( is_null( $licenseKeyId ) ) {
			$query = array( 'hash' => $hash );
			if ( Licenses::instance()->findBy( $query ) ) {
				$duplicate = true;
			}
		} // Update action
		elseif ( is_numeric( $licenseKeyId ) ) {
			global $wpdb;
			$table = Licenses::instance()->getTable();
			$query = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE hash=%s AND id NOT LIKE %s", $hash, "%" . $licenseKeyId . "%" );
			if ( $wpdb->get_var( $query ) ) {
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
	public function saveImportedLicenseKeys( $licenseKeys, $status, $orderId, $productId, $userId, $validFor, $activationsLimit ) {
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

		if ( ! $cleanStatus || ! in_array( $cleanStatus, LicenseStatusEnum::$status ) ) {
			return new WP_Error( 'data_error', __( 'License Status is invalid', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		foreach ( $licenseKeys as $licenseKey ) {
			if ( empty( $licenseKey ) ) {
				continue;
			}
			array_push( $cleanLicenseKeys, sanitize_text_field( $licenseKey ) );
		}

		$result['added']      = 0;
		$result['failed']     = 0;
		$result['duplicates'] = 0;

		if ( ! intval( Settings::get( 'allow_duplicates', Settings::SECTION_GENERAL ) ) ) {
			$origLicensesCount    = count( $cleanLicenseKeys );
			$licenseKeys          = array_unique( $licenseKeys ); // filter for duplicates
			$currLicensesCount    = count( $licenseKeys );
			$result['duplicates'] = $origLicensesCount - $currLicensesCount;
		}

		// Add the keys to the database table.
		foreach ( $cleanLicenseKeys as $licenseKey ) {

			$encrypted = CryptoHelper::encrypt( $licenseKey );
			if ( is_wp_error( $encrypted ) ) {
				return $encrypted;
			}
			$hashed = StringHasher::license( $licenseKey );

			$license = Licenses::instance()->insert(
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
	 * @param int|null $orderId WooCommerce Order ID
	 * @param int|null $productId WooCommerce Product ID
	 * @param string[] $licenseKeys License keys to be stored
	 * @param int $status License key status
	 * @param Generator $generator
	 * @param int|null $validFor
	 * @param int|null $activationsLimit
	 *
	 * @return array|bool|WP_Error
	 */
	public function saveGeneratedLicenseKeys( $orderId, $productId, $licenseKeys, $status, $generator, $validFor = null, $activationsLimit = null, $markAsComplete = true ) {

		$cleanLicenseKeys = array();
		$cleanOrderId     = ( $orderId ) ? absint( $orderId ) : null;
		$cleanProductId   = ( $productId ) ? absint( $productId ) : null;
		$cleanStatus      = ( $status ) ? absint( $status ) : null;
		$validFor         = is_numeric( $validFor ) && absint( $validFor ) > 0 ? absint( $validFor ) : null;
		$userId           = null;

		if ( ! $cleanStatus || ! in_array( $cleanStatus, LicenseStatusEnum::$status ) ) {
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
			if ( $generator->getExpiresIn() && $status == LicenseStatusEnum::SOLD ) {
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
			if ( $this->isKeyDuplicate( $licenseKey ) ) {
				$invalidKeysAmount ++;
				continue;
			}

			// Key doesn't exist, add it to the database table.
			$encryptedLicenseKey = CryptoHelper::encrypt( $licenseKey );
			if ( is_wp_error( $encryptedLicenseKey ) ) {
				return $encryptedLicenseKey;
			}
			$hashedLicenseKey = StringHasher::license( $licenseKey );

			$generatorActivationsLimit = ! empty( $generator->getActivationsLimit() ) ? $generator->getActivationsLimit() : null;
			$activationsLimit          = is_numeric( $activationsLimit ) ? (int) $activationsLimit : $generatorActivationsLimit;

			// Save to database.
			Licenses::instance()->insert(
				array(
					'order_id'          => $cleanOrderId,
					'product_id'        => $cleanProductId,
					'user_id'           => $userId,
					'license_key'       => $encryptedLicenseKey,
					'hash'              => $hashedLicenseKey,
					'expires_at'        => $expiresAt,
					'source'            => LicenseSource::GENERATOR,
					'status'            => $cleanStatus,
					'activations_limit' => $activationsLimit,
					'valid_for'         => $validFor,
				)
			);
		}

		// There have been duplicate keys, regenerate and add them.
		if ( $invalidKeysAmount > 0 ) {

			$generatorsService = new GeneratorsService();
			$newKeys           = $generatorsService->generateLicenses( $invalidKeysAmount, $generator );
			if ( is_wp_error( $newKeys ) ) {
				return $newKeys;
			}

			return $this->saveGeneratedLicenseKeys(
				$cleanOrderId,
				$cleanProductId,
				$newKeys,
				$cleanStatus,
				$generator,
				$validFor
			);
		} else {
			// Keys have been generated and saved, this order is now complete.
			if ( $markAsComplete ) {
				do_action( 'dlm_generated_licenses_saved', $cleanOrderId, [], $markAsComplete );
			}

			return true;
		}
	}

	/**
	 * Queries available licenses for selling from stock
	 *
	 * @param $product
	 * @param $params
	 *
	 * @return bool|AbstractResourceModel[]
	 */
	public function getStockLicensesQuery( $product, $params = [] ) {

		$product_id = is_object( $product ) ? $product->get_id() : $product;
		$query_args = wp_parse_args( $params, array(
			'product_id' => $product_id,
			'status'     => LicenseStatusEnum::ACTIVE
		) );

		return apply_filters( 'dlm_license_stock_query', $query_args, $product, $params );
	}

	/**
	 * Returns a count for licenses available in stock
	 *
	 * @param $product
	 * @param $params
	 *
	 * @return false|int
	 */
	public function getLicensesStockCount( $product, $params = [] ) {
		$query_args = $this->getStockLicensesQuery( $product, $params );

		return Licenses::instance()->countBy( $query_args );
	}

	/**
	 * Queries licenses from available stock
	 *
	 * @param $product
	 * @param int $amount
	 * @param array $params
	 *
	 * @return array|bool|\IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel[]|AbstractResourceModel|License
	 */
	public function getLicensesFromStock( $product, $amount = - 1, $params = [] ) {

		$query = $this->getStockLicensesQuery( $product, $params );

		return Licenses::instance()->findAllBy(
			$query,
			'created_at',
			'asc',
			- 1,
			$amount
		);
	}

	/**
	 * Assign licenses from stock
	 *
	 * @param $product
	 * @param $order
	 * @param $amount
	 * @param null $activationsLimit
	 *
	 * @return AbstractResourceModel|License|WP_Error
	 */
	public function assignLicensesFromStock( $product, $order, $amount, $activationsLimit = null ) {

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

		$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
		if ( ! $order ) {
			return new WP_Error( 'data_error', __( 'Invalid order provided.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		$orderId     = $order->get_id();
		$orderUserId = $order->get_user_id();
		$amount      = is_numeric( $amount ) ? intval( $amount ) : 0;

		if ( ! $amount ) {
			return new WP_Error( 'data_error', __( 'Amount is invalid.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		$licenses = $this->getLicensesFromStock( $product, $amount );

		if ( ! is_array( $licenses ) || count( $licenses ) <= 0 ) {
			return new WP_Error( 'data_error', sprintf( __( 'Required amout of %d licenses was not found in stock.', 'digital-license-manager' ), $amount ), array( 'code' => 422 ) );
		}


		$assignedLicenses = [];
		for ( $i = 0; $i < $amount; $i ++ ) {
			$license   = $licenses[ $i ];
			$validFor  = (int) $license->getValidFor(); // In days.
			$expiresAt = $license->getExpiresAt();

			if ( $validFor ) {
				try {
					$date         = new DateTime();
					$dateInterval = new DateInterval( 'P' . $validFor . 'D' );
					$expiresAt    = $date->add( $dateInterval )->format( 'Y-m-d H:i:s' );
				} catch ( Exception $e ) {
					if ( empty( $expiresAt ) ) {
						return new WP_Error( 'data_error', __( 'Valid for is not set or invalid.', 'digital-license-manager' ), array( 'code' => 422 ) );
					}
				}
			}

			$params = array(
				'order_id'   => $orderId,
				'user_id'    => $orderUserId,
				'expires_at' => $expiresAt,
				'status'     => LicenseStatusEnum::SOLD
			);

			if ( is_numeric( $activationsLimit ) ) {
				$params['activations_limit'] = (int) $activationsLimit;
			}

			Licenses::instance()->update( $license->getId(), $params );

			Stock::syncrhonizeProductStock( $product );


			$assignedLicenses[] = $license;
		}

		return $assignedLicenses;
	}

	/**
	 * Mark imported license keys as sold
	 *
	 * @param License[] $licenses License key resource models
	 * @param int $orderId WooCommerce Order ID
	 * @param int $amount Amount to be marked as sold
	 *
	 * @return bool|WP_Error
	 * @deprecated 1.3.5
	 *
	 */
	public function sellImportedLicenseKeys( $licenses, $orderId, $amount ) {

		_deprecated_function( 'sellImportedLicenseKeys', '1.3.5', 'assignLicensesFromStock' );

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

			Licenses::instance()->update(
				$license->getId(),
				array(
					'order_id'   => $cleanOrderId,
					'user_id'    => $userId,
					'expires_at' => $expiresAt,
					'status'     => LicenseStatusEnum::SOLD
				)
			);
		}

		return true;
	}

	/**
	 * Check the activations limit.
	 *
	 * @param License $license
	 *
	 * @return bool|WP_Error
	 */
	public function validateActivationLimit( $license, $licenseKey = null ) {

		if ( empty( $license ) || ! ( $license instanceof License ) ) {
			return new WP_Error( 'license_not_found', __( 'Unknown license', 'digital-license-manager' ), array( 'status' => 404 ) );
		}

		$timesActivated   = $license->getActivationsCount(['active' => 1]);
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
	public function generateActivationToken( $licenseKey ) {
		$token    = StringHasher::activation( $licenseKey );
		$reps     = 0;
		$max_reps = 20;
		while ( true ) {
			if ( (int) LicenseActivations::instance()->countBy( [ 'token' => $token ] ) === 0 ) {
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

	/**
	 * Add metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @return mixed|bool
	 */
	public function addMeta( $id, $key, $value ) {
		$license = Licenses::instance()->find( $id );

		if ( ! $license ) {
			return false;
		}

		/** @var LicenseMeta $licenseMeta */
		$licenseMeta = LicenseMetaRepository::instance()->insert(
			array(
				'license_id' => $id,
				'meta_key'   => $key,
				'meta_value' => maybe_serialize( $value )
			)
		);

		if ( ! $licenseMeta ) {
			return false;
		}

		return $licenseMeta->getMetaValue();
	}

	/**
	 * Returns metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $single
	 *
	 * @return mixed|mixed[]|bool
	 */
	public function getMeta( $id, $key, $single = false ) {
		$license = Licenses::instance()->find( $id );

		if ( ! $license ) {
			return false;
		}

		if ( $single ) {
			/** @var LicenseMeta $licenseMeta */
			$licenseMeta = LicenseMetaRepository::instance()->findBy(
				array(
					'license_id' => $id,
					'meta_key'   => $key
				)
			);

			if ( ! $licenseMeta ) {
				return false;
			}

			return $licenseMeta->getMetaValue();
		}

		$licenseMetas = LicenseMetaRepository::instance()->findAllBy(
			array(
				'license_id' => $id,
				'meta_key'   => $key
			)
		);
		$result       = array();

		/** @var LicenseMeta $licenseMeta */
		foreach ( $licenseMetas as $licenseMeta ) {
			$result[] = $licenseMeta->getMetaValue();
		}

		return $result;
	}

	/**
	 * Update metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 * @param $previousValue
	 *
	 * @return bool
	 */
	public function updateMeta( $id, $key, $value, $previousValue = null ) {
		$license = Licenses::instance()->find( $id );

		if ( ! $license ) {
			return false;
		}

		$selectQuery          = array(
			'license_id' => $id,
			'meta_key'   => $key
		);
		$updateQueryCondition = array(
			'license_id' => $id,
			'meta_key'   => $key
		);
		$updateQueryData      = array(
			'license_id' => $id,
			'meta_key'   => $key,
			'meta_value' => maybe_serialize( $value )
		);

		if ( $previousValue !== null ) {
			$selectQuery['meta_value']          = $previousValue;
			$updateQueryCondition['meta_value'] = $previousValue;
		}

		$metaLicense = LicenseMetaRepository::instance()->findBy( $selectQuery );

		if ( ! $metaLicense ) {
			return false;
		}

		$updateCount = LicenseMetaRepository::instance()->updateBy( $updateQueryCondition, $updateQueryData );

		if ( ! $updateCount ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function deleteMeta( $id, $key, $value = null ) {
		$license = Licenses::instance()->find( $id );

		if ( ! $license ) {
			return false;
		}

		$deleteQueryCondition = array(
			'license_id' => $id,
			'meta_key'   => $key
		);

		if ( $value ) {
			$deleteQueryCondition['meta_value'] = $value;
		}

		$deleteResult = LicenseMetaRepository::instance()->deleteBy( $deleteQueryCondition );

		if ( $deleteResult ) {
			return true;
		}

		return false;
	}
}
