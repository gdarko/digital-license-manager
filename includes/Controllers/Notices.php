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

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Tools\Migration\Migrators\LMFW;
use IdeoLogix\DigitalLicenseManager\Utils\CompatibilityHelper;
use IdeoLogix\DigitalLicenseManager\Utils\EnvironmentHelper;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeManager;
use IgniteKit\WP\Notices\NoticesInterface;

class Notices {

	/**
	 * State
	 * @var array
	 */
	private $showing = [
		'welcome' => false,
		'lmfwc'   => false
	];

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		$this->notice_welcome();
		$this->notice_lmfwc();
	}

	/**
	 * Initializes the notice.
	 * @return void
	 */
	private function notice_welcome() {
		$key  = apply_filters( 'dlm_welcome_notice_key', 'dlm_welcome' );
		$path = apply_filters( 'dlm_welcome_notice_path', DLM_ABSPATH . 'templates/admin/welcome.php' );
		if ( file_exists( $path ) ) {
			$path                     = trim( $path );
			$notice                   = NoticeManager::instance()->add_custom( $key, "file://{$path}", "never" );
			$this->showing['welcome'] = ! $notice->is_dismissed();
		}
	}

	/**
	 * Show notice about the lmfwc plugin migration
	 *
	 * @return void
	 */
	private function notice_lmfwc() {

		if ( $this->showing['welcome'] ) {
			return; // Do not show multiple notices at once
		}

		if ( isset( $_GET['page'] ) && 'dlm_settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'tools' === $_GET['tab'] ) {
			return; // Do not show on the tools page.
		}

		if ( LMFW::alreadyMigrated() ) {
			// Do not nag, when all of those met.
			return;
		}

		$notice = NoticeManager::instance()->add_info(
			'dlm_lmfwc',
			sprintf( 'file://%s', DLM_ABSPATH . 'templates/admin/migration.php' ),
			NoticesInterface::DISMISS_FOREVER
		);

		$this->showing['lmfwc'] = $notice->is_dismissed();
	}

}