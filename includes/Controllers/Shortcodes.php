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

use IdeoLogix\DigitalLicenseManager\Utils\ArrayFormatter;

class Shortcodes {

	/**
	 * Constructor
	 * @since 1.5.1
	 */
	public function __construct() {
		add_shortcode( 'dlm_licenses_table', [ $this, 'render_licenses_table_shortcode' ] );
		add_shortcode( 'dlm_licenses_check', [ $this, 'render_licenses_check_shortcode' ] );
	}


	/**
	 * Render the licenses table shortcode
	 *
	 * @param $atts
	 *
	 * @return string
	 * @since 1.5.1
	 *
	 */
	public function render_licenses_table_shortcode( $atts ) {

		wp_enqueue_style( 'dlm_global' );

		$params = shortcode_atts( array(
			'status_filter' => 'all',
		), $atts );


		return Frontend::render_licenses_table( ArrayFormatter::camelCaseKeys($params) );
	}

	/**
	 * Render the licenses check form shortcode
	 *
	 * @param $atts
	 *
	 * @return string
	 * @since 1.5.1
	 *
	 */
	public function render_licenses_check_shortcode( $atts ) {
		wp_enqueue_style( 'dlm_global' );
		wp_enqueue_script( 'dlm_licenses_check' );

		$params = shortcode_atts( array(
			'email_required' => false,
		), $atts );

		$params['email_required'] = filter_var($params['email_required'], FILTER_VALIDATE_BOOLEAN);

		return Frontend::render_licenses_check( ArrayFormatter::camelCaseKeys($params) );
	}

}