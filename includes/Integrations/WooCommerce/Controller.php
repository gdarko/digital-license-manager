<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Abstracts\IntegrationController as AbstractIntegrationController;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\IntegrationController as IntegrationControllerInterface;
use IdeoLogix\DigitalLicenseManager\Settings as SettingsData;
use WC_Order;
use WC_Product;

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

		add_action( 'dlm_settings_defaults_general', array( $this, 'settingsGeneralDefaults' ), 10, 1 );
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

		if ( SettingsData::get( 'myaccount_endpoint', SettingsData::SECTION_WOOCOMMERCE ) ) {
			new MyAccount();
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
	 */
	public function settingsGeneralDefaults( $settings ) {
		$settings['auto_delivery']           = 1;
		$settings['order_delivery_statuses'] = array(
			'wc-completed'  => array(
				'send' => '1'
			),
			'wc-processing' => array(
				'send' => '1',
			)
		);

		return $settings;
	}

	/**
	 * Return the WooCommerce template path
	 * @return string
	 */
	public static function getTemplatePath() {
		return trailingslashit( DLM_TEMPLATES_DIR ) . 'woocommerce' . DIRECTORY_SEPARATOR;
	}
}
