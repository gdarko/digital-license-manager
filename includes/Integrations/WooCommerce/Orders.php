<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\ListTables\Licenses;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\Data\Generator as GeneratorUtil;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Class Order
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class Orders {

	/**
	 * OrderManager constructor.
	 */
	public function __construct() {
		$this->addOrderStatusHooks();

		add_action( 'woocommerce_order_action_dlm_send_licenses', array( $this, 'actionResendLicenses' ), 10, 1 );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'showBoughtLicenses' ), 10, 1 );
		add_filter( 'woocommerce_order_actions', array( $this, 'addSendLicenseKeysAction' ), 10, 1 );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'showOrderedLicenses' ), 10, 3 );
	}

	/**
	 * Attach license generation hooks to specific order statuses.
	 *
	 * @return  void
	 */
	private function addOrderStatusHooks() {
		$orderStatusSettings = Settings::get( 'order_delivery_statuses', Settings::SECTION_WOOCOMMERCE );

		/**
		 * The order status settings haven't been configured.
		 */
		if ( empty( $orderStatusSettings ) ) {
			return;
		}

		foreach ( $orderStatusSettings as $status => $settings ) {
			if ( array_key_exists( 'send', $settings ) ) {
				$value = filter_var( $settings['send'], FILTER_VALIDATE_BOOLEAN );

				if ( $value ) {
					$filterStatus = str_replace( 'wc-', '', $status );

					add_action( 'woocommerce_order_status_' . $filterStatus, array( $this, 'generateOrderLicenses' ) );
				}
			}
		}
	}

	/**
	 * Generates licenses for an order, triggered on order status change.
	 *
	 * @param int $orderId
	 */
	public function generateOrderLicenses( $orderId ) {

		/**
		 * Licenses have already been generated for this order.
		 */
		if ( Orders::isComplete( $orderId ) ) {
			return;
		}

		/**
		 * Allow developers to skip the whole process for specific order.
		 */
		if ( apply_filters( 'dlm_skip_licenses_generation_for_order', false, $orderId ) ) {
			return;
		}

		/**
		 * Basic data sanitization
		 *
		 * @var WC_Order $order
		 */
		$order = is_numeric( $orderId ) ? wc_get_order( $orderId ) : $orderId;
		if ( ! $order ) {
			return;
		}

		/**
		 * Loop through the order items and generate license keys
		 *
		 * @var WC_Order_Item_Product [] $items
		 */
		$items = $order->get_items( 'line_item' );
		foreach ( $items as $orderItem ) {

			$product = $orderItem->get_product();

			/**
			 * Skip this product because it's not a licensed product.
			 */
			if ( ! Products::isLicensed( $product->get_id() ) ) {
				continue;
			}

			/**
			 * Allow developers to skip the whole process for specific order.
			 */
			$skip = apply_filters_deprecated(
				'dlm_maybe_skip_subscription_renewals',
				array( false, $orderId, $product->get_id() ),
				'1.2.2',
				'dlm_skip_licenses_generation_for_order_product'
			);
			$skip = apply_filters( 'dlm_skip_licenses_generation_for_order_product', $skip, $orderId, $product->get_id() );
			if ( $skip ) {
				continue;
			}

			/**
			 * Generate the order licenses
			 */
			self::createOrderLicenses( $order, $product, $orderItem->get_quantity() );
		}
	}

	/**
	 * Create licenses
	 *
	 * @param $order
	 * @param $product
	 * @param $quantity
	 *
	 * @return bool
	 */
	public static function createOrderLicenses( $order, $product, $quantity ) {

		/**
		 * Perfform basic data santiization
		 */
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}
		$licenseSrc   = get_post_meta( $product->get_id(), 'dlm_licensed_product_licenses_source', true );
		$useStock     = 'stock' === $licenseSrc;
		$useGenerator = 'generators' === $licenseSrc;

		/**
		 * Skip this product because neither selling from stock or from generators is active.
		 */
		if ( ! $useStock && ! $useGenerator ) {
			return false;
		}

		$deliveredQuantity = absint(
			get_post_meta(
				$product->get_id(),
				'dlm_licensed_product_delivered_quantity',
				true
			)
		);

		/**
		 * Determines how many times should the license key be delivered
		 */
		if ( ! $deliveredQuantity ) {
			$deliveredQuantity = 1;
		}

		/**
		 * Set the needed delivery amount
		 */
		$neededAmount = absint( $quantity ) * $deliveredQuantity;

		if ( $useStock ) {  // Sell license keys through available stock.

			/**
			 * Retrieve the available license keys.
			 * @var LicenseResourceModel[] $licenseKeys
			 */
			$licenseKeys = LicenseResourceRepository::instance()->findAllBy(
				array(
					'product_id' => $product->get_id(),
					'status'     => LicenseStatus::ACTIVE
				)
			);

			/**
			 * Retrieve the current stock amount
			 */
			$availableStock = count( $licenseKeys );

			/**
			 * If there are enough keys, grab some and mark as "SOLD", otherwise add order notice.
			 */
			if ( $neededAmount <= $availableStock ) {
				LicenseUtil::sellImportedLicenseKeys(
					$licenseKeys,
					$order->get_id(),
					$neededAmount
				);
			} else {
				$order->add_order_note( sprintf( __( 'License delivery failed: Could not find enough licenses in stock (Current stock: %d | Required %d)' ), $neededAmount, $availableStock ) );
			}

		} else if ( $useGenerator ) { // Sell license keys through the active generator

			$generatorId = get_post_meta( $product->get_id(), 'dlm_licensed_product_assigned_generator', true );

			/**
			 * Retrieve the generator from the database and set up the args.
			 * Skip the process if generator doesn't exists.
			 * @var GeneratorResourceModel $generator
			 */
			$generator = GeneratorResourceRepository::instance()->find( $generatorId );
			if ( ! $generator ) {
				return false;
			}

			/**
			 * Run the generator and create the licenses, if everything ok, save them.
			 */
			$licenses = GeneratorUtil::generateLicenseKeys( $neededAmount, $generator );
			if ( ! is_wp_error( $licenses ) ) {
				LicenseUtil::saveGeneratedLicenseKeys(
					$order->get_id(),
					$product->get_id(),
					$licenses,
					LicenseStatus::SOLD,
					$generator
				);
			}
		}

		/**
		 * Flag the order as complete. Use custom flag.
		 */
		update_post_meta( $order->get_id(), 'dlm_order_complete', 1 );

		/**
		 * Set status to delivered if the setting is on.
		 */
		if ( Settings::isAutoDeliveryEnabled() ) {
			LicenseResourceRepository::instance()->updateBy(
				array( 'order_id' => $order->get_id() ),
				array( 'status' => LicenseStatus::DELIVERED )
			);
		}

		/**
		 * Fire an action as a final step, to allow the developers to hook into.
		 */
		$orderedLicenses = LicenseResourceRepository::instance()->findAllBy( array( 'order_id' => $order->get_id() ) );
		do_action(
			'dlm_licenses_generated_on_order',
			array(
				'orderId'  => $order->get_id(),
				'licenses' => $orderedLicenses
			)
		);

		return count($orderedLicenses);
	}

	/**
	 * Sends out the ordered license keys.
	 *
	 * @param WC_Order $order
	 */
	public function actionResendLicenses( $order ) {
		do_action( 'dlm_email_customer_deliver_licenses', $order->get_id(), $order );
	}

	/**
	 * Displays the bought licenses in the order view inside "My Account" -> "Orders".
	 *
	 * @param WC_Order $order
	 */
	public function showBoughtLicenses( $order ) {

		// Return if the order isn't complete.
		if ( ! Orders::isComplete( $order->get_id() ) ) {
			return;
		}

		$args = array(
			'order' => $order,
			'data'  => null
		);

		$customerLicenseKeys = Orders::getLicenseKeys( $args );

		if ( ! $customerLicenseKeys['data'] ) {
			return;
		}

		// Add missing style.
		if ( ! wp_style_is( 'dlm_main', 'enqueued' ) ) {
			wp_enqueue_style( 'dlm_main', DLM_CSS_URL . 'main.css' );
		}

		echo wc_get_template_html(
			'myaccount/dlm/licenses-purchased.php',
			array(
				'heading'     => apply_filters( 'dlm_licenses_table_heading', __( 'Your digital license(s)', 'digital-license-manager' ) ),
				'valid_until' => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
				'data'        => $customerLicenseKeys['data'],
				'date_format' => get_option( 'date_format' ),
				'args'        => apply_filters( 'dlm_template_args_myaccount_licenses', array() )
			),
			'',
			DLM_TEMPLATES_DIR
		);

	}

	/**
	 * Adds a new order action used to resend the sold license keys.
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function addSendLicenseKeysAction( $actions ) {
		global $post;

		if ( LicenseResourceRepository::instance()->countBy( array( 'order_id' => $post->ID ) ) ) {
			$actions['dlm_send_licenses'] = __( 'Resend license(s) to customer', 'digital-license-manager' );
		}

		return $actions;
	}

	/**
	 * Hook into the WordPress Order Item Meta Box and display the license key(s).
	 *
	 * @param int $itemId
	 * @param WC_Order_Item_Product $item
	 * @param WC_Product|bool $product
	 */
	public function showOrderedLicenses( $itemId, $item, $product ) {

		// Not a WC_Order_Item_Product object? Nothing to do...
		if ( ! ( $item instanceof WC_Order_Item_Product ) ) {
			return;
		}

		// The product does not exist anymore
		if ( ! $product ) {
			return;
		}

		$query = apply_filters(
			'dlm_admin_get_order_licenses_query',
			array(
				'order_id'   => $item->get_order_id(),
				'product_id' => $product->get_id()
			),
			$item,
			$product
		);

		/** @var LicenseResourceModel[] $licenses */
		$licenses = LicenseResourceRepository::instance()->findAllBy( $query );

		// No license keys? Nothing to do...
		if ( ! $licenses ) {
			return;
		}

		echo self::getOrderedLicensesHtml( $licenses, $item->get_order_id() );
	}

	/**
	 * Checks whether an order has already been completed or not.
	 *
	 * @param $orderId
	 *
	 * @return bool
	 */
	public static function isComplete( $orderId ) {
		if ( ! get_post_meta( $orderId, 'dlm_order_complete' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves ordered license keys.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function getLicenseKeys( $args ) {

		/** @var WC_Order $order */
		$order = $args['order'];
		$data  = array();

		/** @var WC_Order_Item_Product $itemData */
		foreach ( $order->get_items() as $itemData ) {

			/** @var WC_Product $product */
			$product   = $itemData->get_product();
			$productId = $product->get_id();
			$orderId   = $order->get_id();

			// Check if the product has been activated for selling.
			if ( ! Products::isLicensed( $productId ) ) {
				continue;
			}

			$orderId = self::getLicenseOrderId( $orderId, $productId );

			/** @var LicenseResourceModel[] $licenses */
			$licenses = LicenseResourceRepository::instance()->findAllBy( array(
				'order_id'   => $orderId,
				'product_id' => $product->get_id()
			) );

			$data[ $product->get_id() ]['name'] = $product->get_name();
			$data[ $product->get_id() ]['keys'] = $licenses;
		}

		$args['data'] = $data;

		return $args;
	}

	/**
	 * Print the ordered licenses
	 *
	 * @param $licenses
	 * @param $order_id
	 *
	 * @return string
	 */
	public static function getOrderedLicensesHtml( $licenses, $order_id ) {

		$html = sprintf( '<p>%s:</p>', __( 'The following license keys have been sold by this order', 'digital-license-manager' ) );
		$html .= '<ul class="dlm-license-list">';

		if ( ! Settings::get( 'hide_license_keys' ) ) {
			foreach ( $licenses as $license ) {
				$decrypted = $license->getDecryptedLicenseKey();
				$decrypted = is_wp_error( $decrypted ) ? 'ERROR' : $decrypted;
				$html      .= sprintf( '<li></span> <code class="dlm-placeholder">%s</code></li>', $decrypted );
			}

			$html .= '</ul>';

			$html .= '<span class="dlm-txt-copied-to-clipboard" style="display: none">' . __( 'Copied to clipboard', 'digital-license-manager' ) . '</span>';
		} else {
			foreach ( $licenses as $license ) {
				$html .= sprintf(
					'<li><code class="dlm-placeholder empty" data-id="%d"></code></li>',
					$license->getId()
				);
			}

			$html .= '</ul>';
			$html .= '<p>';

			$html .= sprintf(
				'<a class="button dlm-license-keys-show-all" data-order-id="%d">%s</a>',
				$order_id,
				__( 'Show license(s)', 'digital-license-manager' )
			);

			$html .= sprintf(
				'<a class="button dlm-license-keys-hide-all" data-order-id="%d">%s</a>',
				$order_id,
				__( 'Hide license(s)', 'digital-license-manager' )
			);

			$html .= sprintf(
				'<img class="dlm-spinner" alt="%s" src="%s">',
				__( 'Please wait...', 'digital-license-manager' ),
				Licenses::SPINNER_URL
			);

			$html .= '<span class="dlm-txt-copied-to-clipboard" style="display: none">' . __( 'Copied to clipboard', 'digital-license-manager' ) . '</span>';

			$html .= '</p>';
		}

		return $html;
	}

	/**
	 * Returns the license order id
	 *
	 * @param $orderId
	 * @param $productId
	 *
	 * @return mixed|void
	 */
	public static function getLicenseOrderId( $orderId, $productId ) {
		return apply_filters( 'dlm_get_customer_licenses_order_id', $orderId, $productId );
	}
}
