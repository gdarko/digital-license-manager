<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\ListTables\Licenses;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Settings;

use IdeoLogix\DigitalLicenseManager\Utils\Data\Generator as GeneratorUtil;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;

use WC_Order_Item_Product;
use WC_Product_Simple;
use WC_Order;
use WC_Order_Item;
use WC_Product;

use function WC;

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
	 * Hooks the license generation method into the woocommerce order status
	 * change hooks.
	 */
	private function addOrderStatusHooks() {
		$orderStatusSettings = Settings::get( 'order_delivery_statuses', Settings::SECTION_WOOCOMMERCE );

		// The order status settings haven't been configured.
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
	 * Generates licenses for an order.
	 *
	 * @param int $orderId
	 */
	public function generateOrderLicenses( $orderId ) {
		// Keys have already been generated for this order.
		if ( Orders::isComplete( $orderId ) ) {
			return;
		}

		/** @var WC_Order $order */
		$order = wc_get_order( $orderId );

		// The given order does not exist
		if ( ! $order ) {
			return;
		}

		/** @var WC_Order_Item $orderItem */
		foreach ( $order->get_items() as $orderItem ) {
			/** @var WC_Product $product */
			$product = $orderItem->get_product();

			// Skip this product because it's not a licensed product.
			if ( ! Products::isLicensed( $product->get_id() ) ) {
				continue;
			}

			// Instead of generating new license keys, the plugin will extend
			// the expiration date of existing licenses, if configured.
			$abortEarly = apply_filters( 'dlm_maybe_skip_subscription_renewals', false, $orderId, $product->get_id() );

			if ( $abortEarly === true ) {
				continue;
			}

			$licenseSrc   = get_post_meta( $product->get_id(), 'dlm_licensed_product_licenses_source', true );
			$useStock     = 'stock' === $licenseSrc;
			$useGenerator = 'generators' === $licenseSrc;

			// Skip this product because neither selling from stock or from
			// generators is active.
			if ( ! $useStock && ! $useGenerator ) {
				continue;
			}

			$deliveredQuantity = absint(
				get_post_meta(
					$product->get_id(),
					'dlm_licensed_product_delivered_quantity',
					true
				)
			);

			// Determines how many times should the license key be delivered
			if ( ! $deliveredQuantity ) {
				$deliveredQuantity = 1;
			}

			// Set the needed delivery amount
			$neededAmount = absint( $orderItem->get_quantity() ) * $deliveredQuantity;

			if ( $useStock ) { // Sell license keys through available stock.

				// Retrieve the available license keys.
				/** @var LicenseResourceModel[] $licenseKeys */
				$licenseKeys = LicenseResourceRepository::instance()->findAllBy(
					array(
						'product_id' => $product->get_id(),
						'status'     => LicenseStatus::ACTIVE
					)
				);

				// Retrieve the current stock amount
				$availableStock = count( $licenseKeys );

				// There are enough keys.
				if ( $neededAmount <= $availableStock ) {
					// Set the retrieved license keys as "SOLD".
					LicenseUtil::sellImportedLicenseKeys(
						$licenseKeys,
						$orderId,
						$neededAmount
					);
				} else {
					$order->add_order_note( sprintf( __( 'License delivery failed: Could not find enough licenses in stock (Current stock: %d | Required %d)' ), $neededAmount, $availableStock ) );
				}
			} else if ( $useGenerator ) { // Sell license keys through the active generator

				$generatorId = get_post_meta(
					$product->get_id(),
					'dlm_licensed_product_assigned_generator',
					true
				);

				// Retrieve the generator from the database and set up the args.
				/** @var GeneratorResourceModel $generator */
				$generator = GeneratorResourceRepository::instance()->find( $generatorId );

				// The assigned generator no longer exists
				if ( ! $generator ) {
					continue;
				}

				$licenses = GeneratorUtil::generateLicenseKeys( $neededAmount, $generator );

				if ( ! is_wp_error( $licenses ) ) {
					// Save the license keys.
					LicenseUtil::saveGeneratedLicenseKeys(
						$orderId,
						$product->get_id(),
						$licenses,
						LicenseStatus::SOLD,
						$generator
					);
				}
			}

			// Set the order as complete.
			update_post_meta( $orderId, 'dlm_order_complete', 1 );

			// Set status to delivered if the setting is on.
			if ( Settings::isAutoDeliveryEnabled() ) {
				LicenseResourceRepository::instance()->updateBy(
					array( 'order_id' => $orderId ),
					array( 'status' => LicenseStatus::DELIVERED )
				);
			}

			$orderedLicenseKeys = LicenseResourceRepository::instance()->findAllBy( array( 'order_id' => $orderId ) );

			/** Plugin event, Type: post, Name: order_license_keys */
			do_action(
				'dlm_event_post_order_license_keys',
				array(
					'orderId'  => $orderId,
					'licenses' => $orderedLicenseKeys
				)
			);
		}
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
	 * @param WC_Product_Simple|bool $product
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

		/** @var LicenseResourceModel[] $licenses */
		$licenses = LicenseResourceRepository::instance()->findAllBy(
			array(
				'order_id'   => $item->get_order_id(),
				'product_id' => $product->get_id()
			)
		);

		// No license keys? Nothing to do...
		if ( ! $licenses ) {
			return;
		}

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
				$item->get_order_id(),
				__( 'Show license(s)', 'digital-license-manager' )
			);

			$html .= sprintf(
				'<a class="button dlm-license-keys-hide-all" data-order-id="%d">%s</a>',
				$item->get_order_id(),
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

		echo $html;
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

			$orderId = apply_filters( 'dlm_get_customer_licenses_order_id', $orderId, $productId );

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
}
