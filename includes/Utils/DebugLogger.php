<?php

namespace IdeoLogix\DigitalLicenseManager\Utils;

use IdeoLogix\DigitalLicenseManager\Setup;

class DebugLogger {

	/**
	 * Writes INFO message into logfile.
	 *
	 * @param $message
	 *
	 * @return bool
	 */
	public static function info( $message ) {
		return self::write( $message, 'INFO' );
	}

	/**
	 * Writes ERROR message into logfile.
	 *
	 * @param $message
	 *
	 * @return bool
	 */
	public static function error( $message ) {
		return self::write( $message, 'ERROR' );
	}


	/**
	 * Writes WARN message into logfile.
	 *
	 * @param $message
	 *
	 * @return bool
	 */
	public static function warn( $message ) {
		return self::write( $message, 'WARN' );
	}


	/**
	 * Writes into log file
	 * @return bool
	 */
	public static function write( $message, $type = 'INFO' ) {
		$logPath = self::getLogPath();
		if ( ! $logPath ) {
			return false;
		}
		$fp = fopen( $logPath, 'a+' );
		if ( ! $fp ) {
			return false;
		}
		$scalar = true;
		if ( is_object( $message ) || is_array( $message ) ) {
			ob_start();
			print_r( $message, true );
			$message = ob_get_clean();
			$scalar  = false;
		}
		$logTime = date( 'Y-m-d H:i:s' );
		$logType = strtoupper( $type );
		if ( ! $scalar ) {
			$message = sprintf( "%s - %s - %s", $logTime, $logType, "DUMP:" . PHP_EOL . $message );
		} else {
			$message = sprintf( "%s - %s - %s", $logTime, $logType, $message );
		}
		fwrite( $fp, $message . PHP_EOL );
		fclose( $fp );

		return true;
	}

	/**
	 * Returns the path of the log file
	 * @return bool|null|string
	 */
	public static function getLogPath() {
		static $logPath = null;
		if ( is_null( $logPath ) ) {
			try {
				$files = Setup::setDefaultFilesAndFolders();
				if ( ! empty( $files['log'] ) ) {
					$logPath = $files['log'];
				} else {
					$logPath = false;
				}
			} catch ( \Exception $e ) {
				$logPath = false;
			}
		}

		return $logPath;
	}

}
