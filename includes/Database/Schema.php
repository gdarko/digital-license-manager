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

namespace IdeoLogix\DigitalLicenseManager\Database;

use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class Schema {

	/**
	 * Creates the tables
	 * @return void
	 */
	public static function create() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$schema = self::getSchema();
		foreach ( $schema as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Drops the tables
	 * @return void
	 */
	public static function drop() {
		global $wpdb;
		$tables = array(
			$wpdb->prefix . DatabaseTable::LICENSES,
			$wpdb->prefix . DatabaseTable::GENERATORS,
			$wpdb->prefix . DatabaseTable::API_KEYS,
			$wpdb->prefix . DatabaseTable::LICENSE_META,
			$wpdb->prefix . DatabaseTable::LICENSE_ACTIVATIONS,
			$wpdb->prefix . DatabaseTable::PRODUCT_DOWNLOADS,
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}
	}

	/**
	 * Defines the  tables
	 * @return string[]
	 */
	private static function getSchema() {

		global $wpdb;
		$table1 = $wpdb->prefix . DatabaseTable::LICENSES;
		$table2 = $wpdb->prefix . DatabaseTable::GENERATORS;
		$table3 = $wpdb->prefix . DatabaseTable::API_KEYS;
		$table4 = $wpdb->prefix . DatabaseTable::LICENSE_META;
		$table5 = $wpdb->prefix . DatabaseTable::LICENSE_ACTIVATIONS;
		$table6 = $wpdb->prefix . DatabaseTable::PRODUCT_DOWNLOADS;

		return [
			"CREATE TABLE IF NOT EXISTS $table1 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `product_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `license_key` LONGTEXT NOT NULL COMMENT 'Encrypted License Key',
                `hash` LONGTEXT NOT NULL COMMENT 'Hashed License Key ID	',
                `valid_for` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Valid for X time (when ordered from stock)',
                `expires_at` DATETIME NULL DEFAULT NULL COMMENT 'Expiration Date',
                `source` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) UNSIGNED NOT NULL,
                `activations_limit` INT(10) UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL COMMENT 'Creation Date',
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Update Date',
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
          ",

			"CREATE TABLE IF NOT EXISTS $table2 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `charset` VARCHAR(255) NOT NULL,
                `chunks` INT(10) UNSIGNED NOT NULL,
                `chunk_length` INT(10) UNSIGNED NOT NULL,
                `activations_limit` INT(10) UNSIGNED NULL DEFAULT NULL,
                `separator` VARCHAR(255) NULL DEFAULT NULL,
                `prefix` VARCHAR(255) NULL DEFAULT NULL,
                `suffix` VARCHAR(255) NULL DEFAULT NULL,
                `expires_in` INT(10) UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ",
			"CREATE TABLE IF NOT EXISTS $table3 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `description` VARCHAR(200) NULL DEFAULT NULL,
                `permissions` VARCHAR(10) NOT NULL,
                `endpoints` LONGTEXT NULL DEFAULT NULL,
                `consumer_key` CHAR(64) NOT NULL,
                `consumer_secret` CHAR(43) NOT NULL,
                `nonces` LONGTEXT NULL,
                `truncated_key` CHAR(7) NOT NULL,
                `last_access` DATETIME NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `consumer_key` (`consumer_key`),
                INDEX `consumer_secret` (`consumer_secret`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ",
			"CREATE TABLE IF NOT EXISTS $table4 (
                `meta_id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED DEFAULT 0 NOT NULL,
                `meta_key` VARCHAR(255) NULL,
                `meta_value` LONGTEXT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`meta_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ",
			"CREATE TABLE IF NOT EXISTS $table5 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `token` LONGTEXT NOT NULL COMMENT 'Public identifier',
                `license_id` BIGINT(20) UNSIGNED NOT NULL,
                `label` VARCHAR(255) NULL DEFAULT NULL,
                `source` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(255) NULL DEFAULT NULL,
                `user_agent` TEXT NULL DEFAULT NULL,
                `meta_data` LONGTEXT NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `deactivated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ",
			"CREATE TABLE IF NOT EXISTS $table6 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED NOT NULL,
                `activation_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `source` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(255) NULL DEFAULT NULL,
                `user_agent` TEXT NULL DEFAULT NULL, 
                `meta_data` LONGTEXT NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        "

		];
	}

}