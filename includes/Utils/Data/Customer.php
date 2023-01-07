<?php

namespace IdeoLogix\DigitalLicenseManager\Utils\Data;

use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\MyAccount;

/**
 * Class Customer
 * @deprecated 1.3.9
 * @package IdeoLogix\DigitalLicenseManager\Core\Services
 */
class Customer {

	/**
	 * Get licenses for a customer
	 *
	 * @param $userId
	 *
	 * @return array
	 */
	public static function getLicenses( $userId ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Integrations\WooCommerce\MyAccount::getLicenses()' );

		return MyAccount::getLicenses( $userId ); // I know, i know...
	}

}
