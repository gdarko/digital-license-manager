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

namespace IdeoLogix\DigitalLicenseManager\Utils;

use IdeoLogix\DigitalLicenseManager\Setup;

class DebugLogger {

	/**
	 * Writes INFO message into logfile.
	 *
	 * @param string|array|object $message
	 *
	 * @return bool
	 */
	public static function info( $message ) {
		return self::write( $message, 'INFO' );
	}

	/**
	 * Writes ERROR message into logfile.
	 *
	 * @param string|array|object $message
	 *
	 * @return bool
	 */
	public static function error( $message ) {
		return self::write( $message, 'ERROR' );
	}


	/**
	 * Writes WARN message into logfile.
	 *
	 * @param string|array|object $message
	 *
	 * @return bool
	 */
	public static function warn( $message ) {
		return self::write( $message, 'WARN' );
	}


	/**
	 * Writes into a logfile.
	 *
	 * @param string|array|object $message
	 * @param string $type
	 *
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
		if ( ! is_scalar( $message ) ) {
			ob_start();
			$dump_callback = apply_filters( 'dlm_log_dump_callback', 'print_r' );
			if ( ! is_callable( $dump_callback ) ) {
				$dump_callback = 'print_r';
			}
			call_user_func( $dump_callback, $message );
			$message = ob_get_clean();
			$scalar  = false;
		} else if ( ! is_string( $message ) ) {
			return false;
		}
		$logTime = date( 'Y-m-d H:i:s' );
		$logType = strtoupper( $type );
		if ( ! $scalar ) {
			$message = sprintf( "[%s] - %s - %s", $logTime, $logType, "DUMP:" . PHP_EOL . $message );
		} else {
			$message = sprintf( "[%s] - %s - %s", $logTime, $logType, $message );
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
