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

use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;

/**
 * General admin related hooks
 * @since 1.5.6
 */
class Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
	}

	/**
	 *
	 * @param $classes
	 *
	 * @return void
	 */
	public function body_class( $classes ) {

		if ( ( isset( $_GET['page'] ) && in_array( $_GET['page'], [ PageSlug::LICENSES, PageSlug::GENERATORS, PageSlug::ACTIVATIONS ] ) ) && ( isset( $_GET['action'] ) && in_array( $_GET['action'], [ 'edit', 'add', 'import', 'generate' ] ) ) ) {
			$classes .= ' dlm-single-admin-page';
		}

		return $classes;

	}

}