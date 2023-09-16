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

final class TemplateHelper {

	/**
	 * Return the licenses table
	 *
	 * @param $path
	 * @param array $data
	 * @param string $type
	 *
	 * @return false|string
	 *
	 * @since 1.5.1
	 */
	public static function render( $path, $data = [], $type = 'frontend' ) {

		$path = str_replace( [ '.', '/' ], DIRECTORY_SEPARATOR, $path );

		$spath = function_exists( 'get_stylesheet_directory' ) ? get_stylesheet_directory() : '';

		if ( file_exists( $spath . DIRECTORY_SEPARATOR . 'dlm' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $path . '.php' ) ) {
			$full_path = $spath . DIRECTORY_SEPARATOR . 'dlm' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $path . '.php';
		} else if ( file_exists( DLM_TEMPLATES_DIR . $type . DIRECTORY_SEPARATOR . $path . '.php' ) ) {
			$full_path = DLM_TEMPLATES_DIR . $type . DIRECTORY_SEPARATOR . $path . '.php';
		} else {
			$full_path = null;
		}

		if ( is_null( $full_path ) ) {
			return '';
		}

		if ( ! empty( $data ) ) {
			extract( $data );
		}

		ob_start();
		include $full_path;

		return ob_get_clean();
	}

}