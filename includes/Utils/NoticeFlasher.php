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

use IdeoLogix\DigitalLicenseManager\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class NoticeFlasher
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class NoticeFlasher {

	use Singleton;

	const MESSAGE_DISMISSIBLE = '<div class="notice %s is-dismissible"><p><strong>Digital License Manager</strong>: %s</p></div>';
	const NOTICE_ERROR = 'notice-error';
	const NOTICE_SUCCESS = 'notice-success';
	const NOTICE_WARNING = 'notice-warning';
	const NOTICE_INFO = 'notice-info';

	/**
	 * @var array
	 */
	protected $types;

	/**
	 * Notice constructor.
	 */
	protected function init() {
		$this->types = array(
			'error'   => self::NOTICE_ERROR,
			'success' => self::NOTICE_SUCCESS,
			'warning' => self::NOTICE_WARNING,
			'info'    => self::NOTICE_INFO
		);

		add_action( 'admin_notices', array( $this, 'wpInit' ) );
	}

	/**
	 * Retrieves the notice message from the transients, displays it and finally deletes the transient itself.
	 */
	public function wpInit() {
		foreach ( $this->types as $type => $class ) {
			$messages = get_transient( 'dlm_notice_' . $type );

			if ( $messages && is_array( $messages ) ) {
				foreach ( $messages as $message ) {
					echo sprintf(
						self::MESSAGE_DISMISSIBLE,
						$class,
						$message
					);
				}

				delete_transient( 'dlm_notice_' . $type );
			}
		}
	}

	/**
	 * Adds a dashboard notice to be displayed on the next page reload.
	 *
	 * @param string $level
	 * @param string $message
	 * @param int $duration
	 */
	public static function add( $level, $message, $duration = 60 ) {
		$messages = get_transient( 'dlm_notice_' . $level );

		if ( $messages && is_array( $messages ) ) {
			if ( ! in_array( $message, $messages ) ) {
				$messages[] = $message;
			}
		} else {
			$messages = array( $message );
		}

		set_transient( 'dlm_notice_' . $level, $messages, $duration );
	}

	/**
	 * Log and display exception.
	 *
	 * @param string $message The error message
	 */
	public static function error( $message ) {
		self::add( 'error', $message );
	}

	/**
	 * Display a success message.
	 *
	 * @param string $message The success message to be display
	 */
	public static function success( $message ) {
		self::add( 'success', $message );
	}

	/**
	 * Display a warning message.
	 *
	 * @param string $message The warning message to be display
	 */
	public static function warning( $message ) {
		self::add( 'warning', $message );
	}

	/**
	 * Display a info message.
	 *
	 * @param string $message The info message to be display
	 */
	public static function info( $message ) {
		self::add( 'info', $message );
	}
}
