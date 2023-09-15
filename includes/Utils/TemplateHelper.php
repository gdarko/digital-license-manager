<?php

namespace IdeoLogix\DigitalLicenseManager\Utils;

final class TemplateHelper {

	/**
	 * Return the licenses table
	 *
	 * @param $path
	 * @param $data
	 *
	 * @return false|string
	 */
	public static function render( $path, $data = [], $type = 'frontend' ) {

		$path = str_replace( [ '.', '/' ], DIRECTORY_SEPARATOR, $path );

		$spath = function_exists('get_stylesheet_directory') ? get_stylesheet_directory() : '';

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