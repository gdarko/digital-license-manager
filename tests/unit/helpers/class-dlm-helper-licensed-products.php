<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2024  Darko Gjorgjijoski. All Rights Reserved.
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

class DLM_Helper_Licensed_Products {

	public static function create_simple_product( $args = [] ) {

		$args = wp_parse_args( $args, [
			'license_source'    => 'generators',
			'generator'         => 'new',
			'max_products'      => 15,
			'expires_in'        => 365,
			'activations_limit' => 2,
			'chunks'            => 4,
			'chunk_length'      => 4,
		] );

		$meta = [
			'dlm_licensed_product'                    => 1, // or 0 for no.
			'dlm_licensed_product_delivered_quantity' => 1, // how much keys to be delivered upon purchase.
		];

		$product = DLM_WC_Helper_Product::create_simple_product( true, [
			'name'    => sprintf( 'Software #%d', mt_rand( 1, 1000 ) ),
			'weight'  => '',
			'virtual' => true,
		] );

		$generator_args = [
			'expires_in'        => $args['expires_in'],
			'activations_limit' => $args['activations_limit'],
			'chunks'            => $args['chunks'],
			'chunk_length'      => $args['chunk_length'],
		];

		if ( $args['license_source'] === 'generators' ) {
			if ( 'new' === $args['generator'] ) {
				$generator = DLM_Helper_Generator::create($generator_args);
			} else {
				$generator = is_numeric( $args['generator'] ) ? DLM_Helper_Generator::find( $args['generator'] ) : $args['generator'];
			}
			$meta['dlm_licensed_product_licenses_source']      = 'generators';
			$meta['dlm_licensed_product_assigned_generator']   = $generator->getId();
			$meta['dlm_licensed_product_activations_behavior'] = 'standard';
		} else {
			$generator = DLM_Helper_Generator::create($generator_args);
			DLM_Helper_Generator::generate( $generator, $product->get_id(), (int) $args['max_products'] );
			$meta['dlm_licensed_product_licenses_source'] = 'stock';
			\IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Stock::syncrhonizeProductStock( $product );
		}

		if ( isset( $args['delivered_quantity'] ) ) {
			$meta['dlm_licensed_product_delivered_quantity'] = (int) $args['delivered_quantity'];
		}

		foreach ( $meta as $key => $value ) {
			$product->add_meta_data( $key, $value );
		}

		$product->save();

		return $product;

	}

	public static function create_variable_product( $args = [] ) {

		$generator1 = DLM_Helper_Generator::create( [
			'activations_limit' => 1
		] );

		$generator5 = DLM_Helper_Generator::create( [
			'activations_limit' => 1
		] );

		$generatorUL = DLM_Helper_Generator::create( [
			'activations_limit' => null
		] );

		$variations =  [
			[
				'sku'        => 'DUMMY-SINGLE-INSTALL',
				'price'      => 10,
				'attributes' => array( 'number_installs' => '1' ),
				'meta'       => [
					'dlm_licensed_product'                      => 1,
					'dlm_licensed_product_delivered_quantity'   => 1,
					'dlm_licensed_product_licenses_source'      => 'generators',
					'dlm_licensed_product_assigned_generator'   => $generator1->getId(),
					'dlm_licensed_product_activations_behavior' => 'standard',
				],
			],
			[
				'sku'        => 'DUMMY-FIVE-INSTALLS',
				'price'      => 40,
				'attributes' => array( 'number_installs' => '50' ),
				'meta'       => [
					'dlm_licensed_product'                      => 1,
					'dlm_licensed_product_delivered_quantity'   => 1,
					'dlm_licensed_product_licenses_source'      => 'generators',
					'dlm_licensed_product_assigned_generator'   => $generator5->getId(),
					'dlm_licensed_product_activations_behavior' => 'standard',
				],
			],
			[
				'sku'        => 'DUMMY-UNLIMITED-INSTALLS',
				'price'      => 100,
				'attributes' => array( 'number_installs' => 'unlimited' ),
				'meta'       => [
					'dlm_licensed_product'                      => 1,
					'dlm_licensed_product_delivered_quantity'   => 1,
					'dlm_licensed_product_licenses_source'      => 'generators',
					'dlm_licensed_product_assigned_generator'   => $generatorUL->getId(),
					'dlm_licensed_product_activations_behavior' => 'standard',
				],
			]
		];


		$product = DLM_WC_Helper_Product::create_variation_product( null, [ 'number_installs' => [ '1', '5', 'unlimited' ] ],$variations );

		$product->add_meta_data( 'dlm_licensed_product', 1 );
		$product->add_meta_data( 'dlm_licensed_product_delivered_quantity', 1 );
		$product->save();

		return $product;

	}

}