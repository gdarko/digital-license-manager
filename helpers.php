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
 *
 * -------------------------------------------------------------------
 *
 * @note - DEVELOPER NOTE:
 *
 *     This file contains public functions that you can use in your code
 *     to interact with the Digital Licnese Manager database.
 *
 *     The functions are mostly (with small changes) backwards compatible
 *     with "License Manager for WooCommerce".
 *
 *     If migrating your code to Digital License Manager, replace the
 *     function calls from "lmfwc_" prefix with "dlm_" in your code.
 *     Please test your code before going live!
 */

if ( ! function_exists( 'dlm_get_license' ) ) {
	/**
	 * Returns license data.
	 *
	 * @param $key
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\License|WP_Error
	 */
	function dlm_get_license( $key ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->find( $key );
	}
}

if ( ! function_exists( 'dlm_get_licenses' ) ) {
	/**
	 * Queries multipele licenses
	 *
	 * @param $query
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel[]|\IdeoLogix\DigitalLicenseManager\Database\Models\License[]|WP_Error
	 */
	function dlm_get_licenses( $query ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->get( $query );
	}
}

if ( ! function_exists( 'dlm_create_license' ) ) {
	/**
	 * Creates license data.
	 *
	 * @param $key
	 * @param array $data
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\License|void|WP_Error
	 */
	function dlm_create_license( $key, $data = array() ) {

		if ( isset( $data['times_activated_max'] ) ) {
			$data['activations_limit'] = is_numeric( $data['times_activated_max'] ) ? absint( $data['times_activated_max'] ) : null;
			unset( $data['times_activated_max'] );
		}

		$status = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : null;
		if ( ! is_numeric( $status ) && ! is_null( $status ) ) {
			$status         = strtolower( $status );
			$data['status'] = isset( \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::$values[ $status ] ) ? \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::$values[ $status ] : null;
		} else {
			$status = absint( $status );
		}

		$data['license_key'] = $key;

		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->create( $data );
	}

}

if ( ! function_exists( 'dlm_add_license' ) ) {
	/**
	 * Creates license data.
	 *
	 * @param $key
	 * @param array $data
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\License|void|WP_Error
	 */
	function dlm_add_license( $key, $data = array() ) {
		return dlm_create_license( $key, $data );
	}
}

if ( ! function_exists( 'dlm_update_license' ) ) {
	/**
	 * Updates License by key
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\License|void|WP_Error
	 */
	function dlm_update_license( $key, $data ) {

		if ( isset( $data['times_activated_max'] ) ) {
			$data['activations_limit'] = is_numeric( $data['times_activated_max'] ) ? absint( $data['times_activated_max'] ) : null;
			unset( $data['times_activated_max'] );
		}

		$status = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : null;
		if ( ! is_numeric( $status ) && ! is_null( $status ) ) {
			$status         = strtolower( $status );
			$data['status'] = isset( \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::$values[ $status ] ) ? \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::$values[ $status ] : null;
		} else {
			$status = absint( $status );
		}

		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->update( $key, $data );
	}
}

if ( ! function_exists( 'dlm_delete_license' ) ) {
	/**
	 * Deletes license by key
	 *
	 * @param $key
	 *
	 * @return bool|\IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\License|WP_Error
	 */
	function dlm_delete_license( $key ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();
		return $service->delete( $key );
	}
}

if ( ! function_exists( 'dlm_activate_license' ) ) {
	/**
	 * Activates license by key
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return bool|\IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation|WP_Error
	 */
	function dlm_activate_license( $key, $data ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->activate( $key, $data );
	}
}


if ( ! function_exists( 'dlm_deactivate_license' ) ) {
	/**
	 * Deactivates license by key
	 *
	 * If token is provided, you will deactivate specific LicenseActivation row directly. Otherwise, it deactivates the last LicenseActivation row.
	 *
	 * @param $key
	 * @param string $token
	 *
	 * @return WP_Error|\IdeoLogix\DigitalLicenseManager\Database\Models\License
	 */
	function dlm_deactivate_license( $key, $token = '' ) {

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		if ( ! empty( $token ) ) {
			$result = $service->deactivate( $token );
		} else {
			$license = $service->find( $key );
			if ( $license ) {
				/**
				 * @var \IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation[] $activations
				 */
				$activations = $license->getActivations( [ 'active' => 1 ] );
				if ( empty( $activations ) ) {
					return new \WP_Error( 'data_error', sprintf( 'The license %s has not been activated so far.', $key ), array( 'status' => 404 ) );
				}
				$last   = end( $activations );
				$result = $service->deactivate( $last->getToken() );
			} else {
				$result = new \WP_Error( 'data_error', sprintf( 'License not found: %s.', $key ), array( 'status' => 404 ) );
			}
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		} else {
			return $service->find( $key );
		}
	}
}

if ( ! function_exists( 'dlm_delete_activation' ) ) {
	/**
	 * Deletes license activation by id or token
	 *
	 * @param $token
	 *
	 * @return bool|true|WP_Error
	 */
	function dlm_delete_activation( $token ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->deleteActivation( $token );
	}
}

if ( ! function_exists( 'dlm_reactivate_license' ) ) {
	/**
	 * Reactivates license activation by token
	 *
	 * @param $token
	 *
	 * @return bool|\IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|WP_Error
	 */
	function dlm_reactivate_license( $token ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->reactivate( $token );
	}
}


if ( ! function_exists( 'dlm_add_license_meta' ) ) {
	/**
	 * Add license meta data
	 *
	 * @param $licenseId
	 * @param $metaKey
	 * @param $metaValue
	 *
	 * @return bool
	 */
	function dlm_add_license_meta( $licenseId, $metaKey, $metaValue ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->addMeta( $licenseId, $metaKey, $metaValue );
	}
}

if ( ! function_exists( 'dlm_update_license_meta' ) ) {
	/**
	 * Update license meta data
	 *
	 * @param $licenseId
	 * @param $metaKey
	 * @param $metaValue
	 * @param null $previousValue
	 *
	 * @return bool
	 */
	function dlm_update_license_meta( $licenseId, $metaKey, $metaValue, $previousValue = null ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->updateMeta( $licenseId, $metaKey, $metaValue, $previousValue );
	}
}

if ( ! function_exists( 'dlm_delete_license_meta' ) ) {
	/**
	 * Delete license meta data
	 *
	 * @param $licenseId
	 * @param $metaKey
	 * @param $metaValue
	 *
	 * @return bool
	 */
	function dlm_delete_license_meta( $licenseId, $metaKey, $metaValue = null ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->deleteMeta( $licenseId, $metaKey, $metaValue );
	}
}

if ( ! function_exists( 'dlm_get_license_meta' ) ) {
	/**
	 * Return's license meta
	 *
	 * @param $licenseId
	 * @param $metaKey
	 * @param $single
	 *
	 * @return mixed
	 */
	function dlm_get_license_meta( $licenseId, $metaKey, $single = false ) {
		return ( new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService() )->getMeta( $licenseId, $metaKey, $single );
	}
}
