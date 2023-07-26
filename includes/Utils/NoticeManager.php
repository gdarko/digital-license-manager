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
use IgniteKit\WP\Notices\NoticesManager as IgniteKitNoticesManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class NoticeManager
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class NoticeManager {

	use Singleton;

	/**
	 * The manager
	 * @var IgniteKitNoticesManager
	 */
	protected $manager;

	/**
	 * Initializes the notice manager
	 * @return void
	 */
	protected function init() {
		$this->manager = new IgniteKitNoticesManager( 'dlm' );
	}

	/**
	 * Add success notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_success( $key, $message, $expiry ) {
		return $this->manager->add_success( $key, $message, $expiry );
	}

	/**
	 * Add warning notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_warning( $key, $message, $expiry ) {
		return $this->manager->add_warning( $key, $message, $expiry );
	}

	/**
	 * Add error notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_error( $key, $message, $expiry ) {
		return $this->manager->add_error( $key, $message, $expiry );
	}

	/**
	 * Add info notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_info( $key, $message, $expiry ) {
		return $this->manager->add_info( $key, $message, $expiry );
	}

	/**
	 * Add custom notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_custom( $key, $message, $expiry ) {
		return $this->manager->add_custom( $key, $message, $expiry );
	}

	/**
	 * Returns notice
	 *
	 * @param $key
	 * @param $type
	 *
	 * @return \IgniteKit\WP\Notices\Notice|null
	 */
	public function get_notice( $key, $type ) {
		return $this->manager->get_notice( $key, $type );
	}

}
