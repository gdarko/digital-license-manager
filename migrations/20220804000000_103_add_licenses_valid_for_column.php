<?php
/* @var int $migrationMode */

use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrade script
 */
if ( $migrationMode === Migrator::MODE_UP ) {
	global $wpdb;
	$table = $wpdb->prefix . DatabaseTable::LICENSES;
	return $wpdb->query(
		"ALTER TABLE `{$table}`
	        ADD `valid_for` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Valid for X time (for stock purchases)'
        	AFTER `hash`;"
	);

}

return false;