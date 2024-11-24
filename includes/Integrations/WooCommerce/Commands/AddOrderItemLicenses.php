<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Commands;


use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractCommand;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Services\OrdersService;
use WC_Order;

class AddOrderItemLicenses extends AbstractCommand {

	/**
	 * Returns the name
	 * @return string
	 */
	public function get_name() {
		return 'add_order_item_licenses';
	}

	/**
	 * Adds _dlm_license_id to order items
	 *
	 * ## EXAMPLES
	 *
	 *     wp dlm:add_order_item_licenses
	 *
	 * @when after_wp_load
	 */
	public function handle() {

		$progressBar  = null;
		$currPage     = 1;
		$orderService = new OrdersService();

		while ( true ) {

			$query = wc_get_orders( $this->get_args( $currPage ) );

			if ( null === $progressBar ) {
				$progressBar = \WP_CLI\Utils\make_progress_bar( __( 'Processing orders' ), $query->total );
			}

			foreach ( $query->orders as $i => $order ) {
				/* @var WC_Order $order */
				$items = $order->get_items();
				foreach ( $items as $item ) {
					/* @var \WC_Order_Item_Product $item */
					$licenses = $orderService->getOrderItemLicensesRaw( $item );
					$orderService->updateOrderItemLicenses( $item, $licenses, 1 );
				}
				$progressBar->tick();
			}

			if ( $query->max_num_pages == $currPage ) {
				$progressBar->finish();
				\WP_CLI::line('- Done. Processed '.$query->total . ' orders.');
				break;
			} else {
				$currPage ++;
			}
		}
	}

	/**
	 * Return the query arguments
	 *
	 * @param $page
	 *
	 * @return array
	 */
	public function get_args( $page = 1 ) {
		return [
			'paginate'     => true,
			'meta_key'     => 'dlm_order_complete',
			'meta_value'   => '1',
			'meta_compare' => '=',
			'limit'        => 15,
			'page'         => $page,
		];
	}
}