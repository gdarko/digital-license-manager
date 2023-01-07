<?php

namespace IdeoLogix\DigitalLicenseManager\Utils;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractGenerator;
use IdeoLogix\DigitalLicenseManager\Core\Generators\StandardGenerator;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use WP_Error;

class GeneratorsHelper {

	/**
	 * Bulk create license keys, if possible for given parameters.
	 *
	 * @param int $amount Number of license keys to be generated
	 * @param GeneratorResourceModel $generator Generator used for the license keys
	 * @param array $licenses Number of license keys to be generated
	 * @param \WC_Order|null $order
	 * @param \WC_Product|null $product
	 *
	 * @return array|WP_Error
	 */
	public static function generateLicenseKeys( $amount, $generator, $licenses = array(), $order = null, $product = null ) {
		$generatorInstance = self::getGeneratorInstance( $generator, $order, $product );

		return $generatorInstance->generate( $amount, $licenses );
	}

	/**
	 * The generator instance
	 *
	 * @param GeneratorResourceModel $generator
	 * @param \WC_Order $order
	 *
	 * @return AbstractGenerator
	 */
	public static function getGeneratorInstance( $generator, $order = null, $product = null ) {

		/**
		 * Determines the generator PHP class, this class should implement AbstractGenerator.
		 *
		 * @param $className
		 * @param $generator
		 * @param $order
		 * @param $product
		 */
		$className = apply_filters( 'dlm_generator_class', StandardGenerator::class, $generator, $order, $product );
		if ( ! class_exists( $className ) ) {
			$className = StandardGenerator::class;
		}

		return ( new $className( $generator ) );

	}

}