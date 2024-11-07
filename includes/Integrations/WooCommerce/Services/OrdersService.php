<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Services;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;

class OrdersService {

	/**
	 * Returns the order item licenses determined by WooCommerce structure (the old way)
	 *
	 * @param \WC_Order_Item_Product $product
	 *
	 * @return License[]|\IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel[]
	 */
	public function getOrderItemLicensesRaw( \WC_Order_Item_Product $order_item_product ) {

		$order   = $order_item_product->get_order();
		$product = $order_item_product->get_product();

		$query = apply_filters(
			'dlm_admin_get_order_licenses_query',
			array(
				'order_id'   => $order->get_id(),
				'product_id' => $product->get_id(),
			),
			$order_item_product,
			$product
		);

		return Licenses::instance()->findAllBy( $query );
	}


	/**
	 * Returns the order item licenses determined by exact ids attached to the order item
	 *
	 * Note: This is the recommended way if you performed the database migration with the Orders Database Migration Tool.
	 *
	 * @param \WC_Order_Item_Product $order_item_product
	 *
	 * @return License[]|\IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel[]
	 */
	public function getOrderItemLicenses( \WC_Order_Item_Product $order_item_product ) {

		$result = $order_item_product->get_meta( '_dlm_license_id', false );
		$items  = array_map( function ( \WC_Meta_Data $item ) {
			$data = $item->get_data();

			return isset( $data['value'] ) ? intval( $data['value'] ) : null;
		}, $result );
		$items  = array_filter( $items );

		$query = apply_filters(
			'dlm_orders_get_order_licenses_query',
			array(
				'id' => $items,
			),
			$order_item_product,
		);

		return Licenses::instance()->findAllBy( $query );

	}


	/**
	 * Set the order item licenses
	 *
	 * @param \WC_Order_Item $orderItem
	 * @param License[] $licenses
	 *
	 * @return void
	 */
	public function updateOrderItemLicenses( $orderItem, $licenses, $unique = false ) {

		if ( empty( $licenses ) ) {
			return;
		}

		if ( $unique ) {
			try {
				$this->clearOrderItemLicenses( $orderItem );
			} catch ( \Exception $e ) {
			}
		}

		foreach ( $licenses as $license ) {
			$orderItem->add_meta_data( '_dlm_license_id', $license->getId(), false );
		}
		$orderItem->save();
	}

	/**
	 * Clear the order item licenses
	 *
	 * @param \WC_Order_Item $orderItem
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function clearOrderItemLicenses( $orderItem ) {
		$metaData = $orderItem->get_meta( '_dlm_license_id', false );
		foreach ( $metaData as $meta ) {
			/* @var \WC_Meta_Data $meta */
			$data  = $meta->get_data();
			$value = ! empty( $data['value'] ) ? $data['value'] : null;
			if ( $value ) {
				wc_delete_order_item_meta( $orderItem->get_id(), '_dlm_license_id', $value );
			}
		}
	}

}