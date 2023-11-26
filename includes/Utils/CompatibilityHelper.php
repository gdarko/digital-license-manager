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

defined( 'ABSPATH' ) || exit;

/**
 * Class CompatibilityHelper
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class CompatibilityHelper {

	/**
	 * Check if is plugin active
	 *
	 * @param $plugin
	 *
	 * @return bool
	 */
	public static function is_plugin_active( $plugin ) {

		$check = apply_filters( 'dlm_mock_is_plugin_active', false, $plugin );
		if ( $check ) {
			return true;
		}

		if ( function_exists( '\is_plugin_active' ) ) {
			return \is_plugin_active( $plugin );
		} else {
			return in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
		}
	}

	/**
	 * Returns list of multisite sites
	 * @return array|int
	 */
	public static function get_site_ids() {

		global $wp_version;

		if ( version_compare( $wp_version, '4.6', '>=' ) ) {
			$blog_ids = get_sites( [ 'fields' => 'ids' ] );
		} else {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		}

		return $blog_ids;

	}

	/**
	 * Determines whether a $post or a string contains a specific block type.
	 *
	 * @param string $block_name Full block type to look for.
	 * @param int|string|\WP_Post|null $post Optional. Post content, post ID, or post object.
	 *                                            Defaults to global $post.
	 *
	 * @return bool Whether the post content contains the specified block.
	 * @see parse_blocks()
	 *
	 */
	public static function has_block( $block_name, $post = null ) {
		if ( function_exists( '\has_block' ) ) {
			return \has_block( $block_name, $post );
		} else {
			return false;
		}
	}

}
