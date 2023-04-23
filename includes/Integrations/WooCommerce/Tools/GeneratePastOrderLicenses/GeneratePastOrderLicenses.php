<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Tools\GeneratePastOrderLicenses;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool;
use IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;

class GeneratePastOrderLicenses extends AbstractTool {

	/**
	 * The id
	 * @var string
	 */
	protected $id = 'generate_past_order_licenses';

	/**
	 * The description
	 * @var string
	 */
	protected $description = 'Generate Licneses For Past Orders';

	/**
	 * Returns the view
	 * @return string
	 */
	public function getView() {
		ob_start();

		$tool = $this;
		include_once DLM_ABSPATH . 'templates/admin/settings/tools/generate-past-order-licenses.php';

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
	 * @param  null  $identifier
	 *
	 * @return array|\WP_Error
	 */
	public function getSteps( $identifier = null ) {
		return [
			1 => array(
				'name'  => 'Generate Licenses',
				'pages' => $this->getPagesCount()
			),
			2 => array(
				'name'  => 'Clean up',
				'pages' => 1,
			)
		];
	}

	/**
	 * Initializes the process
	 *
	 * @param  null  $identifier
	 *
	 * @return bool|\WP_Error
	 */
	public function initProcess( $identifier = null ) {

		if ( empty( $_POST['generator'] ) ) {
			return ( new \WP_Error( 'data_error', __( 'Please select a generator that will be used to generate the licenses.', 'digital-license-manager' ) ) );
		}

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
	 * @param  null  $identifier
	 *
	 * @return bool|\WP_Error
	 */
	public function doStep( $step, $page, $identifier = null ) {

		switch ( $step ) {
			case 1:

				$query = array_merge( $this->getOrdersQuery(), [
					'page' => $page,
				] );

				$results = wc_get_orders( $query );
				if ( empty( $results->orders ) ) {
					return new \WP_Error( 'not_found', sprintf( __( 'No orders found for step "%s", page "%s"' ), $step, $page ) );
				}
				$generatorId    = isset( $_POST['generator'] ) ? intval( $_POST['generator'] ) : 0;
				$useProductConf = isset( $_POST['use_product_licensing_configuration'] ) ? intval( $_POST['use_product_licensing_configuration'] ) : 0;
				$licensesServ   = new LicensesService();
				$generatorServ  = new GeneratorsService();
				$generator      = $generatorServ->findById( $generatorId );

				static $productGenerators = [];

				$generated = 0;

				foreach ( $results->orders as $order ) {
					/* @var \WC_Order $order */
					$skip_order = (bool) $order->get_meta( '_subscription_renewal' ); // Skip renewal orders?
					if ( apply_filters( 'dlm_tool_generate_past_order_licenses_skip_order', $skip_order, $order ) ) {
						continue;
					}
					foreach ( $order->get_items( [ 'line_item' ] ) as $item ) {
						if ( apply_filters( 'dlm_tool_generate_past_order_licenses_skip_order_item', false, $item, $order ) ) {
							continue;
						}
						/* @var \WC_Order_Item_Product $item */
						$productId = $item->get_product_id();
						$quantity  = $item->get_quantity();
						if ( $useProductConf ) {
							$product = $item->get_product();
							if ( $product ) {
								$productGeneratorId = $product->get_meta( 'dlm_licensed_product_assigned_generator' );
								if ( $productGeneratorId ) {
									$productGenerator = $generatorServ->findById( $productGeneratorId );
									if ( ! is_wp_error( $productGenerator ) ) {
										$productGenerators[ $productId ] = $productGenerator;
									}
								}
							}
						}

						if ( ! isset( $productGenerators[ $productId ] ) ) {
							if ( ! is_wp_error( $generator ) ) {
								$productGenerators[ $productId ] = $generator;
							}
						}

						if ( isset( $productGenerators[ $productId ] ) ) {
							$licenses = $generatorServ->generateLicenses( $quantity, $generator, [] );
							if ( ! is_wp_error( $licenses ) ) {
								$validFor = $productGenerators[ $productId ]->getExpiresIn();
								$status   = $licensesServ->saveGeneratedLicenseKeys(
									$item->get_order_id(),
									$productId,
									$licenses,
									LicenseStatus::DELIVERED,
									$generator,
									$validFor
								);

								if ( ! is_wp_error( $status ) ) {
									$order->add_order_note( sprintf( __( 'Generated %d licenses for order item #%d (product %d) with generator #%d via the "Past Orders License Generator" tool.', 'digital-license-manager' ), count( $licenses ), $item->get_id(), $item->get_product_id(), $productGenerators[$productId]->getId() ) );
									$item->add_meta_data( 'generated_licenses', time() );
									$item->save_meta_data();;
									$generated ++;
								}

								$generated += is_wp_error( $status ) ? 0 : 1;
							}
						}
					}
				}

				return $generated ? true : new \WP_Error( 'not_generated', __( 'No licenses generated for this page.', 'digital-license-manager' ) );
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
		return apply_filters( 'dlm_tool_generate_past_order_licenses_query', [
			'paginate'     => true,
			'status'       => array( 'wc-processing', 'wc-completed' ),
			'limit'        => 2,
			'meta_key'     => 'dlm_order_complete',
			'meta_compare' => 'NOT EXISTS',
		] );
	}
}