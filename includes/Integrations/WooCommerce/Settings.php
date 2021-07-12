<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

/**
 * Class Settings
 * @package IdeoLogix\DigitalLicenseManagerPro\Controllers
 */
class Settings {

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_filter( 'dlm_settings_fields', array( $this, 'addSettings' ), 10, 2 );
	}

	/**
	 * Add additional settings to the plugin
	 *
	 * @param $settings
	 * @param $url
	 *
	 * @return mixed
	 */
	public function addSettings( $settings, $url ) {

		if ( isset( $settings['general']['sections']['licenses']['fields'] ) ) {

			$settings['general']['sections']['licenses']['fields'][50] = array(
				'id'       => 'enable_stock_manager',
				'title'    => __( 'Stock management', 'digital-license-manager' ),
				'callback' => array( $this, 'fieldManageStock' ),
			);
			$settings['general']['sections']['licenses']['fields'][60] = array(
				'id'       => 'auto_delivery',
				'title'    => __( 'Automatic delivery', 'digital-license-manager' ),
				'callback' => array( $this, 'fieldCheckbox' ),
				'args'     => array(
					'label'   => __( "Enable this option to delivery License keys once specific product is purchased.", 'digital-license-manager' ),
					'explain' => __( "If enabled the customer will receive License keys once they purchase specific product, based on the product configuration.", 'digital-license-manager' ),
				)
			);
			$settings['general']['sections']['licenses']['fields'][70] = array(
				'id'       => 'order_delivery_statuses',
				'title'    => __( 'Order status delivery', 'digital-license-manager' ),
				'callback' => array( $this, 'fieldLicenseKeyDeliveryOptions' ),
				'args'     => array(
					'label'   => __( "Enable this option to safe guard the data on plugin removal/uninstallation.", 'digital-license-manager' ),
					'explain' => __( "If enabled your data will NOT be removed once this plugin is uninstalled. This is usually prefered option in case you want to use the plugin again in future.", 'digital-license-manager' ),
				)
			);
		}

		return $settings;
	}

	/**
	 * Render the default checkbox option
	 *
	 * @param $args
	 */
	public function fieldCheckbox( $args ) {

		$key     = isset( $args['key'] ) ? $args['key'] : ''; // database key.
		$field   = isset( $args['field'] ) ? $args['field'] : ''; // field name/id.
		$value   = isset( $args['value'] ) && (bool) $args['value']; // field name/id.
		$label   = isset( $args['label'] ) ? $args['label'] : '';
		$explain = isset( $args['explain'] ) ? $args['explain'] : '';

		$html = '<fieldset>';
		$html .= sprintf( '<label for="%s">', $field );
		$html .= sprintf(
			'<input id="%s" type="checkbox" name="%s[%s]" value="1" %s/>',
			$key,
			$key,
			$field,
			checked( true, $value, false )
		);
		$html .= sprintf( '<span>%s</span>', $label );
		$html .= '</label>';
		$html .= sprintf( '<p class="description">%s</p>', $explain );
		$html .= '</fieldset>';

		echo $html;

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
				'<input type="checkbox" name="dlm_settings_general[%s][%s][send]" value="1" %s>',
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
	 * Callback for the "enable_stock_manager" field.
	 *
	 * @param array $args
	 */
	public function fieldManageStock( $args ) {

		$field = 'enable_stock_manager';
		$value = ! empty( $args['value'] ) && (bool) $args['value'];

		$html = '<fieldset style="margin-bottom: 0;">';
		$html .= '<label for="' . $field . '">';
		$html .= sprintf(
			'<input id="%s" type="checkbox" name="dlm_settings_general[%s]" value="1" %s/>',
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
			__( 'License Manager &rarr; Sell license keys', 'digital-license-manager' ),
			__( 'License Manager &rarr; Sell from stock', 'digital-license-manager' )
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
}