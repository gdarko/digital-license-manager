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
		add_filter( 'dlm_dropdown_search_single', array( $this, 'dropdownSearchSingle' ), 10, 2 );
		add_filter( 'dlm_dropdown_search_multiple', array( $this, 'dropdownSearchMultiple' ), 10, 3 );
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
	 * Perform single entry search
	 *
	 * @param $type
	 * @param $term
	 *
	 * @return array
	 */
	public function dropdownSearchSingle( $type, $term ) {

		$result = [];

		// Search for a specific order
		if ( $type === 'shop_order' ) {
			/** @var WC_Order $order */
			$order = wc_get_order( (int) $term );

			// Order exists.
			if ( $order && $order instanceof WC_Order ) {
				$text = sprintf(
				/* translators: $1: order id, $2: customer name, $3: customer email */
					'#%1$s %2$s <%3$s>',
					$order->get_id(),
					$order->get_formatted_billing_full_name(),
					$order->get_billing_email()
				);

				$result = array(
					'id'   => $order->get_id(),
					'text' => $text
				);
			}
		} // Search for a specific product
		elseif ( $type === 'product' ) {
			/** @var WC_Product $product */
			$product = wc_get_product( (int) $term );

			// Product exists.
			if ( $product ) {
				$text = sprintf(
				/* translators: $1: order id, $2 customer name */
					'(#%1$s) %2$s',
					$product->get_id(),
					$product->get_formatted_name()
				);

				$result = array(
					'id'   => $product->get_id(),
					'text' => $text
				);
			}
		}

		return $result;

	}

	/**
	 * Searches the database for multiple records
	 *
	 * @param $type
	 * @param $term
	 * @param $args
	 */
	public function dropdownSearchMultiple( $type, $term, $args ) {

		$limit   = isset( $args['limit'] ) ? (int) $args['limit'] : 20;
		$offset  = isset( $args['offset'] ) ? (int) $args['offset'] : 0;
		$results = array( 'records' => array(), 'more' => false );

		// Search for orders
		if ( $type === 'shop_order' ) {
			/** @var WC_Order[] $orders */
			$orders = wc_get_orders( $args );

			if ( count( $orders ) < $limit ) {
				$results['more'] = false;
			}

			foreach ( $orders as $order ) {
				$text = sprintf(
				/* translators: $1: order id, $2 customer name, $3 customer email */
					'#%1$s %2$s <%3$s>',
					$order->get_id(),
					$order->get_formatted_billing_full_name(),
					$order->get_billing_email()
				);

				$results[] = array(
					'id'   => $order->get_id(),
					'text' => $text
				);
			}
		} // Search for products
		elseif ( $type === 'product' ) {
			$products = $this->searchProducts( $term, $limit, $offset );

			if ( count( $products ) < $limit ) {
				$results['more'] = false;
			}

			foreach ( $products as $productId ) {
				/** @var WC_Product $product */
				$product = wc_get_product( $productId );

				if ( ! $product ) {
					continue;
				}

				$text = sprintf(
				/* translators: $1: product id, $2 product name */
					'(#%1$s) %2$s',
					$product->get_id(),
					$product->get_name()
				);

				$results[] = array(
					'id'   => $product->get_id(),
					'text' => $text
				);
			}
		}

		return $results;
	}

	/**
	 * Searches the database for posts that match the given term.
	 *
	 * @param string $term The search term
	 * @param int $limit Maximum number of search results
	 * @param int $offset Search offset
	 *
	 * @return array
	 */
	private function searchProducts( $term, $limit, $offset ) {
		global $wpdb;

		$sql = $wpdb->prepare( "
            SELECT
                DISTINCT (posts.ID)
            FROM
                $wpdb->posts as posts
            INNER JOIN
                $wpdb->postmeta as meta
                    ON 1=1
                    AND posts.ID = meta.post_id
            WHERE
                1=1
                AND posts.post_title LIKE %s
                AND (posts.post_type = 'product' OR posts.post_type = 'product_variation')
            ORDER BY posts.ID DESC
            LIMIT %d
            OFFSET %d
        ", "%" . $wpdb->esc_like( $term ) . "%", $limit, $offset );

		return $wpdb->get_col( $sql );
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
}
