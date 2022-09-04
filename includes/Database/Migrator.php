<?php

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
	 * The OLD database version
	 * @var string
	 */
	protected $oldVersion;

	/**
	 * The NEW database version
	 * @var string
	 */
	protected $newVersion;

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
	 * @param $oldVersion
	 * @param $newVersion
	 */
	public function __construct( $path, $oldVersion, $newVersion ) {
		$this->path       = $path;
		$this->oldVersion = $oldVersion;
		$this->newVersion = $newVersion;
	}

	/**
	 * Performs a database upgrade.
	 */
	public function up() {
		$migrationMode = self::MODE_UP;
		$regExFileName = '/(\d{14})_(.*?)_(.*?)\.php/';
		foreach ( glob( $this->path ) as $fileName ) {
			if ( 'index.php' === $fileName ) {
				continue;
			}
			if ( preg_match( $regExFileName, basename( $fileName ), $match ) ) {
				$fileBasename    = $match[0];
				$fileDateTime    = $match[1];
				$fileVersion     = $match[2];
				$fileDescription = $match[3];
				global $wpdb;
				if ( ( (int) $fileVersion <= $this->newVersion ) && (int) $fileVersion > $this->oldVersion ) {
					require_once $fileName;
				}
			}
		}

		update_option( 'dlm_db_version', $this->newVersion, true );
	}

	/**
	 * Performs a database downgrade (Currently not in use).
	 */
	public function down() {
		$migrationMode = self::MODE_DOWN;
		// TODO: Not implemented.
	}

	/**
	 * Run the database migrator
	 */
	public function run() {
		if ( $this->oldVersion < $this->newVersion ) {
			$this->up();
		} else if ( $this->oldVersion > $this->newVersion ) {
			$this->down();
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
