<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Tools\GeneratePastOrderLicenses;

use IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use WC_Order;
use WC_Order_Item_Product;
use WP_Error;

class StandardLicenseGenerator extends BaseLicenseGenerator {

	/**
	 * The generator service
	 * @var GeneratorsService
	 */
	protected $generatorService;


	/**
	 * Constructor
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		parent::__construct( $args );
		$this->generatorService = new GeneratorsService();

	}

	/**
	 * Generates licenses for the specific order item
	 *
	 * @param WC_Order $order
	 * @param WC_Order_Item_Product $item *
	 *
	 * @return LicenseGeneratorResult|WP_Error
	 */
	public function generate( WC_Order $order, WC_Order_Item_Product $item ) : LicenseGeneratorResult|WP_Error {

		$licensesServ = new LicensesService();

		$quantity = $item->get_quantity();

		$generator = $this->getLicenseGenerator( $item );

		if ( is_wp_error( $generator) ) {
			return $generator;
		}

		$licenses = $this->generatorService->generateLicenses( $quantity, $generator, [] );
		if ( is_wp_error( $licenses ) ) {
			return $licenses;
		}

		$licenseArgs = apply_filters( 'dlm_tool_generate_past_order_licenses_license_args', [
			'order_id'          => $item->get_order_id(),
			'product_id'        => $item->get_product_id(),
			'status'            => LicensePrivateStatus::SOLD,
			'source'            => LicenseSource::GENERATOR,
			'activations_limit' => $generator->getActivationsLimit(),
			'valid_for'         => $generator->getExpiresIn(),
		], $licenses, $item, $order, $generator );

		$status = $licensesServ->createMultiple( $licenses, $licenseArgs );

		if ( is_wp_error( $status ) ) {
			return $status;
		}

		$result = new LicenseGeneratorResult();

		$result->total    = count( $status['licenses'] );
		$result->licenses = $status['licenses'];

		$order->add_order_note( sprintf( __( 'Generated %d license(s) for order item #%d (product #%d) with generator #%d via the "Past Orders License Generator" tool.', 'digital-license-manager' ), $total, $item->get_id(), $item->get_product_id(), $productGenerators[ $productId ]->getId() ) );
		$item->add_meta_data( 'generated_licenses', time() );
		$item->save_meta_data();

		do_action( 'dlm_tool_generate_past_order_after_generation', $status['licenses'], $item, $order );

		return $result;

	}


	/**
	 * Return the product generator
	 *
	 * @param $item
	 *
	 * @return Generator|WP_Error
	 */
	private function getLicenseGenerator( $item ) {

		$productId = $item->get_product_id();

		/**
		 * Static cache of product generators
		 * @var Generator[] $productGenerators
		 */
		static $productGenerators = [];
		if ( isset( $productGenerators[ $productId ] ) ) {
			return $productGenerators[ $productId ];
		}

		$useProductConf = $this->get_arg( 'use_product_licensing_configuration' );
		$generatorId    = $this->get_arg( 'generator_id', null );

		/* @var WC_Order_Item_Product $item */
		if ( $useProductConf ) {
			$product = $item->get_product();
			if ( $product ) {
				$generatorId = $product->get_meta( 'dlm_licensed_product_assigned_generator' );
			}
		}

		if ( ! $generatorId ) {
			return new WP_Error( 'generator_not_found', 'Generator not found.' );
		}

		$productGenerator = $this->generatorService->findById( $generatorId );
		if ( is_wp_error( $productGenerator ) ) {
			return $productGenerator;
		}

		$productGenerators[ $productId ] = $productGenerator;

		return $productGenerators[ $productId ];

	}
}