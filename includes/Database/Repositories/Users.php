<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

defined( 'ABSPATH' ) || exit;

class Users {
	/**
	 * Retrieve assigned products for a specific generator.
	 *
	 * @return array
	 */
	public static function getUsers() {
		global $wpdb;

		return $wpdb->get_results(
			"
                SELECT
                    ID
                    , user_login
                    , user_email
                FROM
                    {$wpdb->users}
            ",
			OBJECT
		);
	}

}