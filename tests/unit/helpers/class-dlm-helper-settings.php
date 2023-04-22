<?php

class DLM_Helper_Settings {

	public static function setDefaults() {
		$options = [
			'dlm_settings_general'     => array(
				'hide_license_keys' => 0,
				'disable_api_ssl'   => 0,
				'safeguard_data'    => 1,
			),
			'dlm_settings_woocommerce' => array(
				'myaccount_endpoint'        => 1,
				'auto_delivery'             => 1,
				'enable_activations_table'  => 1,
				'enable_manual_activations' => 0,
				'enable_certificates'       => 1,
				'order_delivery_statuses'   => array(
					'wc-completed'  => array(
						'send' => '1'
					),
					'wc-processing' => array(
						'send' => '1',
					)
				)
			)
		];
		foreach ( $options as $group => $group_options ) {
			update_option( $group, $group_options );
		}
	}
}