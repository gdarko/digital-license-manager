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
			'name'              => esc_html__( 'WooCommerce', 'digital-license-manager' ),
			'url'               => add_query_arg( 'tab', 'woocommerce', $baseUrl ),
			'priority'          => 15,
			'sanitize_callback' => array( $this, 'sanitizeArray' ),
			'sections'          => array(
				'general' => array(
					'name' => esc_html__('General'),
					'page' => 'general',
					'priority' => 5,
					'fields' => array(
						10 => array(
							'id'       => 'auto_delivery',
							'title'    => esc_html__( 'Automatic delivery', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => esc_html__( "Enable this option to delivery License keys once specific product is purchased.", 'digital-license-manager' ),
								'explain' => esc_html__( "If enabled the customer will receive License keys once they purchase specific product, based on the product configuration.", 'digital-license-manager' ),
							)
						),
						15 => array(
							'id'       => 'order_delivery_statuses',
							'title'    => esc_html__( 'Order status delivery', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldLicenseKeyDeliveryOptions' ),
							'args'     => array(
								'label'   => esc_html__( "Enable this option to safe guard the data on plugin removal/uninstallation.", 'digital-license-manager' ),
								'explain' => esc_html__( "If enabled your data will NOT be removed once this plugin is uninstalled. This is usually prefered option in case you want to use the plugin again in future.", 'digital-license-manager' ),
							)
						),
						20 => array(
							'id'       => 'stock_management',
							'title'    => esc_html__( 'Stock management', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldManageStock' ),
						),
						30 => array(
							'id'       => 'hide_license_keys',
							'title'    => esc_html__( 'Obscure licenses', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => esc_html__( 'Hide license keys in the public facing pages like "Order Received".', 'digital-license-manager' ),
								'explain' => esc_html__( "The license keys will be masked with stars on the public facing pages for security purposes", 'digital-license-manager' ),
							)
						),
					)
				),
				'my_account' => array(
					'name'     => esc_html__( 'My Account' ),
					'page'     => 'my_account',
					'priority' => 10,
					'fields'   => array(
						10 => array(
							'id'       => 'myaccount_endpoint',
							'title'    => esc_html__( 'Enable "Licenses"', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => esc_html__( "Display the 'Licenses' section inside WooCommerce's 'My Account'.", 'digital-license-manager' ),
								'explain' => esc_html__( "You might need to save your permalinks after enabling this option.", 'digital-license-manager' ),
							)
						),
						30 => array(
							'id'       => 'enable_activations_table',
							'title'    => esc_html__( 'Activation History', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => esc_html__( "Enable historical records that shows previous activations in the license page in My Account dashboard.", 'digital-license-manager' ),
								'explain' => esc_html__( "Use this option to display table that shows list of activations and labels.", 'digital-license-manager' ),
							)
						),
						50 => array(
							'id'       => 'enable_manual_activations',
							'title'    => esc_html__( 'Allow Manual Activations', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => esc_html__( "Enable this to allow manual license activation. Users will be able to create activations from the admin without using the REST API.", 'digital-license-manager' ),
								'explain' => esc_html__( "Normally activations should be done through the REST API from your apps, however this is more a psychological feature to give the users a feeling that they can activate the product.", 'digital-license-manager' ),
							)
						),
						70 => array(
							'id'       => 'enable_certificates',
							'title'    => esc_html__( 'Enable "PDF Certificates"', 'digital-license-manager' ),
							'callback' => array( $this, 'fieldCheckbox' ),
							'args'     => array(
								'label'   => esc_html__( "Enable License PDF certificates in the single license page in My Account dashboard.", 'digital-license-manager' ),
								'explain' => esc_html__( "Use this option if you want to allow customers to download License certificate from the single license page.", 'digital-license-manager' ),
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
		$html .= '<td><strong>' . esc_html__( 'Statuses', 'digital-license-manager' ) . '</strong></td>';
		$html .= '<td><strong>' . esc_html__( 'Send', 'digital-license-manager' ) . '</strong></td>';
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

		$html .= '<span>' . esc_html__( 'Enable automatic stock management for WooCommerce products.', 'digital-license-manager' ) . '</span>';
		$html .= '</label>';
		$html .= sprintf(
			'<p class="description">%s<br/>1. %s<br/>2. %s<br/>3. %s</p>',
			esc_html__( 'To use this feature, you also need to enable the following settings at a product level:', 'digital-license-manager' ),
			esc_html__( 'Inventory &rarr; Manage stock?', 'digital-license-manager' ),
			esc_html__( 'License Manager &rarr; Sell Licenses', 'digital-license-manager' ),
			esc_html__( 'License Manager &rarr; Licenses source &rarr; Provide licenses from stock', 'digital-license-manager' )
		);
		$html .= '</fieldset>';

		$html .= '
            <fieldset style="margin-top: 1em;">
                <button class="button button-secondary"
                        type="submit"
                        name="dlm_stock_synchronize"
                        value="1">' . esc_html__( 'Synchronize', 'digital-license-manager' ) . '</button>
                <p class="description" style="margin-top: 1em;">
                    ' . esc_html__( 'The "Synchronize" button can be used to manually synchronize the product stock.', 'digital-license-manager' ) . '
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
					sprintf( esc_html__( 'Successfully updated the stock of %d WooCommerce products.', 'digital-license-manager' ), $productsSynchronized ),
					'success'
				);
			} else {
				add_settings_error(
					'dlm_settings_group_general',
					'dlm_stock_update',
					esc_html__( 'The stock of all WooCommerce products is already synchronized.', 'digital-license-manager' ),
					'success'
				);
			}
		}
	}
}
