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

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\ListTables\Licenses as LicensesListTable;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper;
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
		add_filter( 'woocommerce_order_actions', array( $this, 'addSendLicenseKeysAction' ), 10, 2 );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'showOrderedLicenses' ), 10, 3 );
		add_filter( 'dlm_woocommerce_order_item_actions', array( $this, 'orderItemActions' ), 10, 4 );
		add_action( 'dlm_generated_licenses_saved', array( $this, 'markOrderAsComplete' ), 10, 3 );
		add_filter( 'dlm_validate_order_id', array( $this, 'validateOrderId' ), 10, 2 );
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
				array( false, $orderId, $product->get_id(), $orderItem ),
				'1.2.2',
				'dlm_skip_licenses_generation_for_order_product',
			);
			$skip = apply_filters( 'dlm_skip_licenses_generation_for_order_product', $skip, $orderId, $product->get_id(), $orderItem );
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
	 * @throws \Exception
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
		$licenseSrc   = $product->get_meta( 'dlm_licensed_product_licenses_source', true );
		$useStock     = 'stock' === $licenseSrc;
		$useGenerator = 'generators' === $licenseSrc;

		/**
		 * Skip this product because neither selling from stock or from generators is active.
		 */
		if ( ! $useStock && ! $useGenerator ) {
			return false;
		}

		$deliveredQuantity = absint(
			$product->get_meta( 'dlm_licensed_product_delivered_quantity', true )
		);

		/**
		 * Determines how many times should the license key be delivered
		 */
		if ( ! $deliveredQuantity ) {
			$deliveredQuantity = 1;
		}

		/**
		 * Determines whether max activations field in the generated license
		 * is based on the generator settings or woocommerce quantity.
		 */
		$maxActivationsBehavior = $product->get_meta( 'dlm_licensed_product_activations_behavior', true );
		if ( empty( $maxActivationsBehavior ) ) {
			$maxActivationsBehavior = 'standard';
		}

		/**
		 * Override activations limit in case on quantity behavior
		 */
		$activationsLimit = 'quantity' === $maxActivationsBehavior ? $quantity : null;

		/**
		 * Set the needed delivery amount
		 */
		$neededAmount   = 'standard' === $maxActivationsBehavior ? ( absint( $quantity ) * $deliveredQuantity ) : $deliveredQuantity;
		$licenseService = new LicensesService();

		if ( $useStock ) {  // Sell Licenses through available stock.

			/**
			 * Retrieve the current stock amount
			 */
			$availableStock = $licenseService->getLicensesStockCount( $product );

			/**
			 * If there are enough keys, grab some and mark as "SOLD", otherwise add order notice.
			 */
			$assignedLicenses = [];
			if ( $neededAmount <= $availableStock ) {
				$order->add_order_note( sprintf( __( 'Delivering licenses from stock. (Current stock: %d | Required: %d).', 'digital-license-manager' ), $availableStock, $neededAmount ) );
				$assignedLicenses = $licenseService->assignLicensesFromStock(
					$product,
					$order,
					$neededAmount,
					$activationsLimit
				);

				if ( is_wp_error( $assignedLicenses ) ) {
					$order->add_order_note( sprintf( __( 'License delivery failed: %s.', 'digital-license-manager' ), $assignedLicenses->get_error_message() ) );
				} else {
					$order->add_order_note( sprintf( __( 'Delivered in total %d licenses from stock.', 'digital-license-manager' ), count( $assignedLicenses ) ) );
				}

			} else {
				$order->add_order_note( sprintf( __( 'License delivery failed: Could not find enough licenses in stock (Current stock: %d | Required %d).' ), $availableStock, $neededAmount ) );
			}

			do_action( 'dlm_stock_delivery_assigned_licenses', $assignedLicenses, $neededAmount, $availableStock, $order, $product );

		} else if ( $useGenerator ) { // Sell Licenses through the active generator

			$generatorId = $product->get_meta( 'dlm_licensed_product_assigned_generator', true );

			/**
			 * Retrieve the generator from the database and set up the args.
			 * Skip the process if generator doesn't exist.
			 * @var Generator $generator
			 */
			$generator = Generators::instance()->find( $generatorId );
			if ( ! $generator ) {
				$order->add_order_note( sprintf( __( 'License delivery failed: No generator assigned for product #%d.', 'digital-license-manager' ), $product->get_id() ) );

				return false;
			}

			/**
			 * Run the generator and create the licenses, if everything ok, save them.
			 */
			$generatorsService = new GeneratorsService();
			$generatedLicenses = $generatorsService->generateLicenses( $neededAmount, $generator, [], $order, $product );
			if ( ! is_wp_error( $generatedLicenses ) ) {
				$result = $licenseService->saveGeneratedLicenseKeys(
					$order->get_id(),
					$product->get_id(),
					$generatedLicenses,
					LicenseStatus::SOLD,
					$generator,
					null,
					$activationsLimit,
					false
				);
				if ( ! is_wp_error( $result ) ) {
					$order->add_order_note( sprintf( __( 'Delivered %d licenses with generator #%d.', 'digital-license-manager' ), $neededAmount, $generatorId ) );
				} else {
					$order->add_order_note( sprintf( __( 'License delivery failed: %s.', 'digital-license-manager' ), $result->get_error_message() ) );
				}
			} else {
				$order->add_order_note( sprintf( __( 'License delivery failed: %s.', 'digital-license-manager' ), $generatedLicenses->get_error_message() ) );
			}
		}

		/**
		 * Flag the order as complete. Use custom flag.
		 */
		$order->update_meta_data( 'dlm_order_complete', 1 );
		$order->save();

		/**
		 * Set status to delivered if the setting is on.
		 */
		if ( Settings::isAutoDeliveryEnabled() ) {
			Licenses::instance()->updateBy(
				array( 'order_id' => $order->get_id() ),
				array( 'status' => LicenseStatus::DELIVERED )
			);
		}


		/**
		 * Set activations limit on the ordered licenses based on the max activations behavior.
		 * @var License[] $orderedLicenses
		 */
		$orderedLicenses = Licenses::instance()->findAllBy( array( 'order_id' => $order->get_id() ) );
		if ( 'quantity' === $maxActivationsBehavior ) {
			foreach ( $orderedLicenses as $license ) {
				Licenses::instance()->update( $license->getId(), [ 'activations_limit' => $quantity ] );
				$orderedLicenses = Licenses::instance()->findAllBy( array( 'order_id' => $order->get_id() ) ); // Reload.
			}
		}

		/**
		 * Fire an action as a final step, to allow the developers to hook into.
		 */
		do_action(
			'dlm_licenses_generated_on_order',
			array(
				'orderId'  => $order->get_id(),
				'licenses' => $orderedLicenses
			)
		);

		return count( $orderedLicenses );
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

		$customerLicenseKeys = Orders::getLicenses( $args );

		if ( ! $customerLicenseKeys['data'] ) {
			return;
		}

		echo wc_get_template_html(
			'dlm/my-account/orders/licenses.php',
			array(
				'heading'     => apply_filters( 'dlm_licenses_table_heading', __( 'Your digital license(s)', 'digital-license-manager' ) ),
				'valid_until' => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
				'data'        => $customerLicenseKeys['data'],
				'date_format' => DateFormatter::getExpirationFormat(),
				'args'        => apply_filters( 'dlm_template_args_myaccount_licenses', array() )
			),
			'',
			Controller::getTemplatePath()
		);

	}

    /**
     * Adds a new order action used to resend the sold license keys.
     *
     * @param  array  $actions
     * @param \WC_Order $order
     *
     * @return array
     */
	public function addSendLicenseKeysAction( $actions, $order ) {

		if ( Licenses::instance()->countBy( array( 'order_id' => $order->get_id() ) ) ) {
			$actions['dlm_send_licenses'] = __( 'Resend license(s) to customer', 'digital-license-manager' );
		}

		return $actions;
	}

	/**
	 * Hook into the WordPress Order Item Meta Box and display the license(s).
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

		/** @var License[] $licenses */
		$licenses = Licenses::instance()->findAllBy( $query );

		// No license keys? Nothing to do...
		if ( ! $licenses ) {
			return;
		}

		echo self::getOrderedLicensesHtml( $licenses, $item );
	}

	/**
	 * Add order item actions
	 *
	 * @param array $actions
	 * @param \WC_Order_Item_Product $order_item
	 * @param $licenses
	 *
	 * @return array
	 */
	public function orderItemActions( $actions, $order_item, $licenses, $hide_keys ) {

		if ( $hide_keys ) {
			$actions['toggle'] = array(
				'main_html'  => sprintf(
					'<a class="button dlm-license-keys-toggle-all" data-order-id="%d" data-toggle-text="%s" data-toggle-current="hide"><img class="dlm-spinner" style="display:none;" alt="%s" src="%s"><span>%s</span></a>',
					$order_item->get_order_id(),
					__( 'Hide license(s)', 'digital-license-manager' ),
					__( 'Please wait...', 'digital-license-manager' ),
					LicensesListTable::SPINNER_URL,
					__( 'Show license(s)', 'digital-license-manager' )
				),
				'after_html' => '',
				'priority'   => 20,
			);
		}

		return $actions;
	}

	/**
	 * Check if order id is valid
	 *
	 * @param $isValid
	 * @param $orderId
	 *
	 * @return bool
	 */
	public function validateOrderId( $isValid, $orderId ) {
		$isValid = function_exists( 'wc_get_order' ) && wc_get_order( $orderId );

		return $isValid;
	}

	/**
	 * Mark as complete
	 *
	 * @param $order_id
	 * @param $licenses
	 * @param $shouldMark
	 *
	 * @return void
	 */
	public function markOrderAsComplete( $order_id, $licenses, $shouldMark ) {
		if ( ! $shouldMark ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$order->update_meta_data( 'dlm_order_complete', 1 );
		$order->save();

	}

	/**
	 * Checks whether an order has already been completed or not.
	 *
	 * @param $orderId
	 *
	 * @return bool
	 */
	public static function isComplete( $orderId ) {

		$order = wc_get_order( $orderId );

		if ( ! is_a( $order, WC_Order::class ) ) {
			return false;
		}

		if ( ! (int) $order->get_meta( 'dlm_order_complete', true ) ) {
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
	public static function getLicenses( $args ) {
		/** @var WC_Order $order */
		$order = $args['order'];
		$data  = array();

		/** @var WC_Order_Item_Product $item */
		foreach ( $order->get_items() as $item ) {

			$product   = self::getProductByLineItem( $item );
			$productId = $product->get_id();
			$orderId   = self::getLicenseOrderId( $item->get_order_id(), $product->get_id() );
			$order     = wc_get_order( $orderId );

			$licenses = self::getLicensesByLineItemData( $item, $order, $product );

			if ( empty( $licenses ) ) {
				continue;
			}

			$data[ $productId ]['name'] = $product->get_name();
			$data[ $productId ]['keys'] = $licenses;
		}

		$args['data'] = $data;

		return $args;
	}


	/**
	 * Return the product id by line item
	 *
	 * @param \WC_Order_Item_Product $item
	 *
	 * @since 1.5.1
	 *
	 * @return \WC_Product
	 */
	public static function getProductByLineItem( $item ) {

		$product   = wc_get_product( $item->get_product_id() );
		$productId = $item->get_product_id();
		if ( $product->is_type( 'variable' ) ) {
			$productId = $item->get_variation_id();
		}

		$productId = apply_filters( 'dlm_order_licensed_product', $productId, $item, $item->get_order() );

		return wc_get_product( $productId );
	}

	/**
	 * Return's list of licenses by line item.
	 *
	 * @param WC_Order $order
	 * @param WC_Product $product
	 *
	 * @since 1.5.1
	 *
	 * @return License[]
	 */
	public static function getLicensesByLineItemData( $item, $order, $product ) {

		$query = apply_filters(
			'dlm_admin_get_order_licenses_query',
			array(
				'order_id'   => $order->get_id(),
				'product_id' => $product->get_id(),
			),
			$item,
			$product
		);

		return Licenses::instance()->findAllBy( $query );
	}

	/**
	 * Retrieves ordered license keys.
	 *
	 * @param array $args
	 *
	 * @return array
	 * @deprecated 1.3.0
	 *
	 */
	public static function getLicenseKeys( $args ) {
		return self::getLicenses( $args );
	}

	/**
	 * Print the ordered licenses
	 *
	 * @param $licenses
	 * @param \WC_Order_Item_Product $order_item
	 *
	 * @return string
	 */
	public static function getOrderedLicensesHtml( $licenses, $order_item ) {

		$hide_keys = (int) Settings::get( 'hide_license_keys' );
		$actions   = apply_filters( 'dlm_woocommerce_order_item_actions', [], $order_item, $licenses, $hide_keys );

		// Generate licenses.
		$html = sprintf( '<p>%s:</p>', __( 'The following licenses have been generated for this order item', 'digital-license-manager' ) );
		$html .= '<ul class="dlm-license-list">';
		foreach ( $licenses as $license ) {
			if ( ! $hide_keys ) {
				$decrypted = $license->getDecryptedLicenseKey();
				$decrypted = is_wp_error( $decrypted ) ? 'ERROR' : $decrypted;
				$html      .= sprintf( '<li> <code class="dlm-placeholder">%s</code></li>', $decrypted );
			} else {
				$html .= sprintf( '<li><code class="dlm-placeholder empty" data-id="%d"></code></li>', $license->getId() );
			}
		}
		$html .= '</ul>';

		// Render Actions.
		if ( ! empty( $actions ) ) {
			$html .= '<ul class="dlm-license-actions">';
			foreach ( $actions as $key => $action ) {
				if ( isset( $action['main_html'] ) ) {
					$html .= '<li class="dlm-orderitem-action-' . $key . '">' . $action['main_html'] . '</li>';
				}
			}
			$html .= '</ul>';

			foreach ( $actions as $key => $action ) {
				if ( ! empty( $action['after_html'] ) ) {
					add_action( 'admin_footer', function () use ( $action ) {
						echo wp_kses( $action['after_html'], SanitizeHelper::ksesAllowedHtmlTags() );
					}, 100000 );
				}
			}
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
