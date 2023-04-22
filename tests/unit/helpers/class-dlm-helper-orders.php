<?php
/**
 * Order helpers.
 *
 * @package WooCommerce/SubscriptionsCore/Tests/Helper
 */

/**
 * Class WC_Helper_Order.
 *
 * This helper class should ONLY be used for unit tests!.
 */
class DLM_WC_Helper_Order {

	/**
	 * Create a order.
	 *
	 * @param int $customer_id The ID of the customer the order is for.
	 * @param WC_Product $product The product to add to the order.
	 *
	 * @return WC_Order
	 */
	public static function create_order( $customer_id = 1, $product = null ) {

		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = DLM_WC_Helper_Product::create_simple_product();
		}

		$order_data = array(
			'status'        => 'pending',
			'customer_id'   => $customer_id,
			'customer_note' => '',
			'total'         => '',
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // Required, else wc_create_order throws an exception.
		$order                  = wc_create_order( $order_data );

		// Add order products.
		$item = new WC_Order_Item_Product();
		$item->set_props(
			array(
				'product'  => $product,
				'quantity' => 4,
				'subtotal' => wc_get_price_excluding_tax( $product, array( 'qty' => 4 ) ),
				'total'    => wc_get_price_excluding_tax( $product, array( 'qty' => 4 ) ),
			)
		);
		$item->save();
		$order->add_item( $item );

		// Set billing address.
		$order->set_billing_first_name( 'Jeroen' );
		$order->set_billing_last_name( 'Sormani' );
		$order->set_billing_company( 'WooCompany' );
		$order->set_billing_address_1( 'WooAddress' );
		$order->set_billing_address_2( '' );
		$order->set_billing_city( 'WooCity' );
		$order->set_billing_state( 'NY' );
		$order->set_billing_postcode( '12345' );
		$order->set_billing_country( 'US' );
		$order->set_billing_email( 'admin@example.org' );
		$order->set_billing_phone( '555-32123' );

		// Set payment gateway.
		$payment_gateways = WC()->payment_gateways->payment_gateways();
		$order->set_payment_method( $payment_gateways['bacs'] );

		// Set totals.
		$order->set_discount_total( 0 );
		$order->set_discount_tax( 0 );
		$order->set_cart_tax( 0 );
		$order->set_total( 50 ); // 4 x $10 simple helper product

		// Set meta
		$order->save();

		return $order;
	}

	/**
	 * Delete an order.
	 *
	 * @param int $order_id ID of the order to delete.
	 */
	public static function delete_order( $order_id ) {

		$order = wc_get_order( $order_id );

		// Delete all products in the order.
		foreach ( $order->get_items() as $item ) {
			DLM_WC_Helper_Product::delete_product( $item['product_id'] );
		}

		// Delete the order post.
		$order->delete( true );
	}
}
