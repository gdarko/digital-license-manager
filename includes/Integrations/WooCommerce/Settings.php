<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Abstracts\SettingsFieldsTrait;

/**
 * Class Settings
 * @package IdeoLogix\DigitalLicenseManagerPro\Controllers
 */
class Settings {

	use SettingsFieldsTrait;

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_filter( 'dlm_settings_fields', array( $this, 'addSettings' ), 10, 2 );
		add_filter( 'dlm_settings_sanitized', array( $this, 'afterSanitize' ) );
	}

	/**
	 * Add additional settings to the plugin
	 *
	 * @param $settings
	 * @param $baseUrl
	 *
	 * @return mixed
	 */
	public function addSettings( $settings, $baseUrl ) {

		$settings['woocommerce'] = array(
			'slug'              => 'woocommerce',
			'name'              => __( 'WooCommerce', 'digital-license-manager' ),
			'url'               => add_query_arg( 'tab', 'woocommerce', $baseUrl ),
			'priority'          => 15,
			'sanitize_callback' => array( $this, 'sanitizeArray' ),
			'sections'          => array(
				'general' => array(
					'name' => __('General'),
					'page' => 'general',
					'priority' => 5,
					'fields' => array(
						10 => array(
							'id'       => 'auto_delivery',
							'title'    => __( 'Automatic delivery', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => __( "Enable this option to delivery License keys once specific product is purchased.", 'digital-license-manager' ),
								'explain' => __( "If enabled the customer will receive License keys once they purchase specific product, based on the product configuration.", 'digital-license-manager' ),
							)
						),
						15 => array(
							'id'       => 'order_delivery_statuses',
							'title'    => __( 'Order status delivery', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldLicenseKeyDeliveryOptions' ),
							'args'     => array(
								'label'   => __( "Enable this option to safe guard the data on plugin removal/uninstallation.", 'digital-license-manager' ),
								'explain' => __( "If enabled your data will NOT be removed once this plugin is uninstalled. This is usually prefered option in case you want to use the plugin again in future.", 'digital-license-manager' ),
							)
						),
						20 => array(
							'id'       => 'stock_management',
							'title'    => __( 'Stock management', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldManageStock' ),
						),
						30 => array(
							'id'       => 'hide_license_keys',
							'title'    => __( 'Obscure licenses', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => __( 'Hide license keys in the public facing pages like "Order Received".', 'digital-license-manager' ),
								'explain' => __( "The license keys will be masked with stars on the public facing pages for security purposes", 'digital-license-manager' ),
							)
						),
					)
				),
				'my_account' => array(
					'name'     => __( 'My Account' ),
					'page'     => 'my_account',
					'priority' => 10,
					'fields'   => array(
						10 => array(
							'id'       => 'myaccount_endpoint',
							'title'    => __( 'Enable "Licenses"', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => __( "Display the 'Licenses' section inside WooCommerce's 'My Account'.", 'digital-license-manager' ),
								'explain' => __( "You might need to save your permalinks after enabling this option.", 'digital-license-manager' ),
							)
						),
						30 => array(
							'id'       => 'enable_activations_table',
							'title'    => __( 'Activation History', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => __( "Enable historical records that shows previous activations in the license page in My Account dashboard.", 'digital-license-manager' ),
								'explain' => __( "Use this option to display table that shows list of activations and labels.", 'digital-license-manager' ),
							)
						),
						50 => array(
							'id'       => 'enable_manual_activations',
							'title'    => __( 'Allow Manual Activations', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => __( "Enable this to allow manual license activation. Users will be able to create activations from the admin without using the REST API.", 'digital-license-manager' ),
								'explain' => __( "Normally activations should be done through the REST API from your apps, however this is more a psychological feature to give the users a feeling that they can activate the product.", 'digital-license-manager' ),
							)
						),
						70 => array(
							'id'       => 'enable_certificates',
							'title'    => __( 'Enable Certificates', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => __( "Enable license PDF certificates in the single license page in My Account dashboard.", 'digital-license-manager' ),
								'explain' => __( "Use this option if you want to allow customers to download license certificate from the single license page.", 'digital-license-manager' ),
							)
						),
					)
				)
			),
		);

		return $settings;
	}

	/**
	 * Callback for the "order_delivery_statuses" field.
	 *
	 * @param array $args
	 */
	public function fieldLicenseKeyDeliveryOptions( $args ) {

		$value = ! empty( $args['value'] ) ? (array) $args['value'] : array();

		$field = 'order_delivery_statuses';
		$html  = '';

		$html .= '<table class="wp-list-table widefat fixed striped posts dlm-checkbox-table">';

		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<td><strong>' . __( 'Statuses', 'digital-license-manager' ) . '</strong></td>';
		$html .= '<td><strong>' . __( 'Send', 'digital-license-manager' ) . '</strong></td>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		foreach ( wc_get_order_statuses() as $slug => $name ) {
			$send = false;
			if ( array_key_exists( $slug, $value ) ) {
				if ( array_key_exists( 'send', $value[ $slug ] ) && $value[ $slug ] ) {
					$send = true;
				}
			}
			$html .= '<tr>';
			$html .= '<td>' . $name . '</td>';
			$html .= '<td>';
			$html .= sprintf(
				'<input type="checkbox" name="dlm_settings_woocommerce[%s][%s][send]" value="1" %s>',
				$field,
				$slug,
				$send ? 'checked="checked"' : ''
			);
			$html .= '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		echo $html;
	}

	/**
	 * Callback for the "stock_management" field.
	 *
	 * @param array $args
	 */
	public function fieldManageStock( $args ) {

		$field = 'stock_management';
		$value = ! empty( $args['value'] ) && (bool) $args['value'];

		$html = '<fieldset style="margin-bottom: 0;">';
		$html .= '<label for="' . $field . '">';
		$html .= sprintf(
			'<input id="%s" type="checkbox" name="dlm_settings_woocommerce[%s]" value="1" %s/>',
			$field,
			$field,
			checked( true, $value, false )
		);

		$html .= '<span>' . __( 'Enable automatic stock management for WooCommerce products.', 'digital-license-manager' ) . '</span>';
		$html .= '</label>';
		$html .= sprintf(
			'<p class="description">%s<br/>1. %s<br/>2. %s<br/>3. %s</p>',
			__( 'To use this feature, you also need to enable the following settings at a product level:', 'digital-license-manager' ),
			__( 'Inventory &rarr; Manage stock?', 'digital-license-manager' ),
			__( 'License Manager &rarr; Sell Licenses', 'digital-license-manager' ),
			__( 'License Manager &rarr; Licenses source &rarr; Provide licenses from stock', 'digital-license-manager' )
		);
		$html .= '</fieldset>';

		$html .= '
            <fieldset style="margin-top: 1em;">
                <button class="button button-secondary"
                        type="submit"
                        name="dlm_stock_synchronize"
                        value="1">' . __( 'Synchronize', 'digital-license-manager' ) . '</button>
                <p class="description" style="margin-top: 1em;">
                    ' . __( 'The "Synchronize" button can be used to manually synchronize the product stock.', 'digital-license-manager' ) . '
                </p>
            </fieldset>
        ';

		echo $html;
	}

	/**
	 * Fired after sanitization
	 */
	public function afterSanitize() {
		if ( isset( $_POST['dlm_stock_synchronize'] ) ) {
			if ( ! current_user_can( 'dlm_manage_settings' ) ) {
				return;
			}
			$productsSynchronized = Stock::synchronize();
			if ( $productsSynchronized > 0 ) {
				add_settings_error(
					'dlm_settings_group_general',
					'dlm_stock_update',
					sprintf( __( 'Successfully updated the stock of %d WooCommerce products.', 'digital-license-manager' ), $productsSynchronized ),
					'success'
				);
			} else {
				add_settings_error(
					'dlm_settings_group_general',
					'dlm_stock_update',
					__( 'The stock of all WooCommerce products is already synchronized.', 'digital-license-manager' ),
					'success'
				);
			}
		}
	}
}
