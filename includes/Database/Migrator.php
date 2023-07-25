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

defined( 'ABSPATH' ) || exit;

/**
 * Class Migrator
 * @package IdeoLogix\DigitalLicenseManager\Database
 */
class Migrator {

	/**
	 * The migraitons path
	 * @var string
	 */
	protected $path;

	/**
	 * The current database version
	 * @var string
	 */
	protected $current_version;

	/**
	 * The NEW database version
	 * @var string
	 */
	protected $new_version;

	/**
	 * Db option
	 * @var string
	 */
	protected $db_option;

	/**
	 * Migration mode UP
	 */
	const MODE_UP = 1;

	/**
	 * Migration mode Down
	 */
	const MODE_DOWN = 2;

	/**
	 * Migrator constructor.
	 *
	 * @param $path
	 * @param $dbOption
	 * @param $newVersion
	 */
	public function __construct( $path, $db_option, $new_version ) {
		$this->path            = $path;
		$this->new_version     = $new_version;
		$this->current_version = get_option( $db_option );
		$this->db_option       = $db_option;
	}

	/**
	 * Performs a database upgrade.
	 */
	public function up() {
		$migrationMode = Migrator::MODE_UP;
		foreach ( glob( $this->path ) as $fileName ) {
			if ( preg_match( '/(\d{14})_(.*?)_(.*?)\.php/', $fileName, $match ) ) {
				$fileBasename    = $match[0];
				$fileDateTime    = $match[1];
				$fileVersion     = $match[2];
				$fileDescription = $match[3];
				if ( ( (int) $fileVersion <= $this->new_version ) && (int) $fileVersion > $this->current_version ) {
					require_once $fileName;
					update_option( $this->db_option, $fileVersion );
				}
			}
		}
	}

	/**
	 * Run the database migrator
	 */
	public function run() {
		if ( $this->current_version < $this->new_version ) {
			$this->up();
		} else {
			// TODO: Implement
		}
	}

	/**
	 * Drops column from database table, if it exists.
	 *
	 * @param string $table_name Database table name.
	 * @param string $column_name Table column name.
	 * @param string $drop_ddl SQL statement to drop column.
	 *
	 * @return bool True on success or if the column doesn't exist. False on failure.
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 */
	public static function maybe_drop_column( $table_name, $column_name, $drop_ddl = 'ALTER TABLE {table} DROP COLUMN {column}' ) {
		global $wpdb;

		foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
			if ( $column === $column_name ) {

				// Found it, so try to drop it.
				$drop_ddl = str_replace( '{table}', $table_name, $drop_ddl );
				$drop_ddl = str_replace( '{column}', $column_name, $drop_ddl );
				$wpdb->query( $drop_ddl );

				// We cannot directly tell that whether this succeeded!
				foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column2 ) {
					if ( $column2 === $column_name ) {
						return false;
					}
				}
			}
		}

		// Else didn't find it.
		return true;
	}

}
