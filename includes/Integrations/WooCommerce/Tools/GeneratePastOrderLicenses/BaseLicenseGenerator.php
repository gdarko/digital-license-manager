<?php
namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Tools\GeneratePastOrderLicenses;

use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use WP_Error;

abstract class BaseLicenseGenerator {

	/**
	 * The arguments' array
	 * @var array
	 */
	protected $args;

	/**
	 * Constructor
	 *
	 * @param array $args
	 */
	public function __construct( array $args ) {
		$this->args = $args;
	}

	/**
	 * Generates licenses for the specific order item
	 *
	 * @param WC_Order $order
	 * @param WC_Order_Item_Product $item
	 *
	 * @return LicenseGeneratorResult|WP_Error
	 */
	abstract function generate( WC_Order $order, WC_Order_Item_Product $item ) : LicenseGeneratorResult|WP_Error;

	/**
	 * Retrieves a specific argument from the arguments array.
	 *
	 * @param mixed $arg The key of the argument to retrieve.
	 * @param mixed $default The default value to return if the argument is not set. Default is null.
	 *
	 * @return mixed The value of the specified argument or the default value if not found.
	 */
	protected function get_arg( $arg, $default = null ) : mixed {
		return isset( $this->args[ $arg ] ) ? $this->args[ $arg ] : $default;
	}
}