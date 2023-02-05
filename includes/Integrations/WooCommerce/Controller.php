<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractIntegrationController;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\IntegrationControllerInterface;
use IdeoLogix\DigitalLicenseManager\Settings as SettingsData;

defined( 'ABSPATH' ) || exit;

/**
 * Class Controller
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class Controller extends AbstractIntegrationController implements IntegrationControllerInterface {
	/**
	 * Controller constructor.
	 */
	public function __construct() {
		$this->bootstrap();

		add_filter( 'dlm_default_settings', array( $this, 'defaultWooCommerceSettings' ), 10, 1 );
		add_filter( 'dlm_dropdown_searchable_post_types', array( $this, 'dropdownSearchablePostTypes' ), 10, 1 );
		add_filter( 'dlm_dropdown_search_query_default_status', array( $this, 'dropdownSearchQDefaultStatus' ), 10, 2 );
	}

	/**
	 * Initializes the integration component
	 */
	private function bootstrap() {
		new Stock();
		new Orders();
		new Emails();
		new Products();
		new Settings();

		if ( Certificates::isLicenseCertificationEnabled() ) {
			new Certificates();
		}

		if ( SettingsData::get( 'myaccount_endpoint', SettingsData::SECTION_WOOCOMMERCE ) ) {
			new MyAccount();
			new Activations();
		}
	}

	/**
	 * Enable searchable post types to be products and orders
	 *
	 * @param $types
	 *
	 * @return array|string[]
	 */
	public function dropdownSearchablePostTypes( $types ) {

		if ( ! is_array( $types ) ) {
			$types = array();
		}

		return array_merge( $types, array(
			'product',
			'shop_order'
		) );
	}

	/**
	 * Default search query status for shop order.
	 *
	 * @param $status
	 * @param $type
	 *
	 * @return array|string
	 */
	public function dropdownSearchQDefaultStatus( $status, $type ) {
		if ( 'shop_order' === $type ) {
			$status = array_keys( wc_get_order_statuses() );
		}

		return $status;
	}

	/**
	 * Default settings
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function defaultWooCommerceSettings( $settings ) {

		if ( ! isset( $settings[ SettingsData::SECTION_WOOCOMMERCE ] ) ) {

			$default_settings = array(
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
			);

			$settings[ SettingsData::SECTION_WOOCOMMERCE ] = apply_filters( 'dlm_default_woocommerce_settings', $default_settings );
		}

		return $settings;
	}

	/**
	 * Return the WooCommerce template path
	 * @return string
	 */
	public static function getTemplatePath() {
		return trailingslashit( DLM_TEMPLATES_DIR ) . 'woocommerce' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Return license url
	 *
	 * @param $license
	 *
	 * @return string|null
	 */
	public static function getAccountLicenseUrl( $license_id ) {
		return esc_url( wc_get_account_endpoint_url( 'digital-licenses/' . $license_id ) );
	}
}
