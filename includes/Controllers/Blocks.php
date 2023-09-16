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

use IdeoLogix\DigitalLicenseManager\Utils\CompatibilityHelper;

class Blocks {

	/**
	 * List of blocks
	 * @since 1.5.1
	 * @var array[]
	 */
	private $blocks;

	/**
	 * Constructor
	 * @since 1.5.1
	 */
	public function __construct() {

		$this->blocks = [
			'licenses-check' => [
				'i18n'      => [
					'block_title'                 => __( 'Licenses Table', 'digital-license-manager' ),
					'checkbox_require_email'      => __( 'Require email', 'digital-license-manager' ),
					'checkbox_require_email_help' => __( 'Require license owner email to confirm if user owns the license', 'digital-license-manager' ),
				],
				'custom_js' => true,
			],
			'licenses-table' => [
				'i18n'      => [
					'block_title'           => __( 'Licenses Table', 'digital-license-manager' ),
					'block_settings_title'  => __( 'Licenses Table Settings', 'digital-license-manager' ),
					'settings_label_status' => __( 'Status', 'digital-license-manager' ),
				],
				'settings'  => [
					'statuses' => [
						[ 'label' => __( 'All', 'digital-license-manager' ), 'value' => 'all' ],
						[ 'label' => __( 'Valid', 'digital-license-manager' ), 'value' => 'valid' ],
						[ 'label' => __( 'Expired', 'digital-license-manager' ), 'value' => 'expired' ],
					]
				],
				'custom_js' => false,
			]
		];


		$this->blocks['licenses-table']['settings'] = apply_filters( 'dlm_block_licenses_table_settings', $this->blocks['licenses-table']['settings'] );


		add_action( 'init', [ $this, 'register_blocks' ] );
		//add_action( 'enqueue_block_editor_assets', [ $this, 'register_block_editor_assets' ] );
		add_action( 'dlm_enqueue_scripts', [ $this, 'enqueue_scripts' ], 10, 1 );
	}

	/**
	 * Initialize blocks
	 * @return void
	 * @since 1.5.1
	 */
	public function register_blocks() {
		$block_path = DLM_ABSPATH . 'blocks/dist/';
		foreach ( $this->blocks as $block => $params ) {
			$asset_file = include $block_path . $block . DIRECTORY_SEPARATOR . 'index.asset.php';

			wp_register_script(
				'dlm-block-' . $block,
				DLM_PLUGIN_URL . 'blocks/dist/' . $block . '/index.js',
				$asset_file['dependencies'],
				$asset_file['version']
			);

			$sanitized_name = str_replace( [ '-', ' ' ], '_', $block );

			if ( ! empty( $params ) ) {
				$object_name = strtoupper( sprintf( 'dlm_block_%s', $sanitized_name ) );
				wp_localize_script( 'dlm-block-' . $block, $object_name, $params );
			}

			register_block_type( $block_path . $block, array(
				'api_version'     => 3,
				'editor_script'   => 'dlm-block-' . $block,
				'render_callback' => [ $this, 'render_' . $sanitized_name . '_block' ],
			) );
		}
	}

	/**
	 * Enqueues the required JS
	 *
	 * @param $version
	 *
	 * @return void
	 * @since 1.5.1
	 */
	public function enqueue_scripts( $version ) {
		if ( CompatibilityHelper::has_block( 'digital-license-manager/licenses-check' ) ) {
			wp_enqueue_style( 'dlm_global' );
			wp_enqueue_script( 'dlm_licenses_check' );
		}
	}

	/**
	 * Register the block editor assets
	 * @return void
	 *
	 * @since 1.5.1
	 */
	public function register_block_editor_assets() {
		$url  = DLM_PLUGIN_URL;
		$path = DLM_ABSPATH;

		foreach ( $this->blocks as $block => $params ) {
			wp_register_style( 'dlm-block-' . $block, $url . 'blocks/dist/' . $block . '/index.css', array(), filemtime( $path . 'blocks/dist/' . $block . '/index.css' ) );
		}
	}

	/**
	 * Render the block
	 *
	 * @param array $block_attributes
	 * @param string $content
	 *
	 * @since 1.5.1
	 */
	public function render_licenses_table_block( $block_attributes, $content ) {
		return Frontend::render_licenses_table( $block_attributes );
	}

	/**
	 * Render the block
	 *
	 * @param array $block_attributes
	 * @param string $content
	 *
	 * @since 1.5.1
	 */
	public function render_licenses_check_block( $block_attributes, $content ) {
		return Frontend::render_licenses_check( $block_attributes );
	}

}