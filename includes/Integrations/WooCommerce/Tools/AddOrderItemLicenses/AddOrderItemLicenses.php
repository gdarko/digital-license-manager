<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Tools\AddOrderItemLicenses;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Services\OrdersService;
use WC_Order;

class AddOrderItemLicenses extends AbstractTool {

	/**
	 * The id
	 * @var string
	 */
	protected $slug = 'add_order_item_licenses';

	/**
	 * The description
	 * @var string
	 */
	protected $description = 'Add Order Item Licenses for past orders';

	/**
	 * Returns the view
	 * @return string
	 */
	public function getView() {
		ob_start();

		$tool = $this;
		include_once DLM_ABSPATH . 'templates/admin/settings/tools/add-order-item-licenses.php';

		return ob_get_clean();
	}

	/**
	 * Returns the tool steps
	 *
	 * eg:
	 *
	 *    [
	 *        1 => array( 'name' => 'Step 1', 'pages' => 3 ),
	 *        2 => array( 'name' => 'Step 2', 'pages' => 4 ),
	 *        3 => array( 'name' => 'Step 3', 'pages' => 5 ),
	 *        4 => array( 'name' => 'Step 4', 'pages' => 6 ),
	 *        5 => array( 'name' => 'Step 5', 'pages' => 7 )
	 *    ];
	 *
	 * @return array|\WP_Error
	 */
	public function getSteps() {
		$list = $this->getData( 'steps' );

		if ( ! is_array( $list ) || empty( $list ) ) {
			$list = $this->setData( 'steps', [
				1 => array(
					'name'  => 'Process Orders',
					'pages' => $this->getPagesCount()
				),
			] );
		}

		return $list;
	}

	/**
	 * Initializes the process
	 *
	 * @return bool|\WP_Error
	 */
	public function initProcess() {
		$query   = $this->getOrdersQuery();
		$results = wc_get_orders( $query );

		if ( isset( $results->total ) && $results->total === 0 ) {
			return ( new \WP_Error( 'data_error', __( 'No orders found without licenses.', 'digital-license-manager' ) ) );
		}

		return true;
	}

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param $page
	 *
	 * @return bool|\WP_Error
	 */
	public function doStep( $step, $page ) {

		switch ( $step ) {
			case 1:
				$query = array_merge( $this->getOrdersQuery(), [
					'page' => $page,
				] );

				$results = wc_get_orders( $query );
				if ( empty( $results->orders ) ) {
					return new \WP_Error( 'not_found', sprintf( __( 'No orders found for step "%s", page "%s"' ), $step, $page ) );
				}

				$orderService = new OrdersService();

				foreach ( $results->orders as $i => $order ) {
					/* @var WC_Order $order */
					$items = $order->get_items();
					foreach ( $items as $item ) {
						/* @var \WC_Order_Item_Product $item */
						$licenses = $orderService->getOrderItemLicensesRaw( $item );
						$orderService->resyncOrderItemLicenses( $item, $licenses, true );
					}
				}


				return true;

			default:
				return true;
		}

	}

	/**
	 * Returns the count of the records
	 * @return int
	 */
	private function getPagesCount() {

		$query  = array_merge( $this->getOrdersQuery(), [
			'page'   => 1,
			'format' => 'ids',
		] );
		$orders = wc_get_orders( $query );

		return isset( $orders->max_num_pages ) ? (int) $orders->max_num_pages : 0;
	}

	/**
	 * Returns the orders query
	 * @return mixed|null
	 */
	private function getOrdersQuery() {
		return apply_filters( 'dlm_tool_add_order_item_license_query', [
			'paginate'     => true,
			'limit'        => 10,
			'meta_key'     => 'dlm_order_complete',
			'meta_value'   => '1',
			'meta_compare' => '=',
		] );
	}

}