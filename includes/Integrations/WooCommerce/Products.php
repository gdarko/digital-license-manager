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

use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper;
use WP_Error;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Class Product
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class Products {

	/**
	 * @var string
	 */
	const ADMIN_TAB_NAME = 'digital-license-manager';

	/**
	 * @var string
	 */
	const ADMIN_TAB_TARGET = 'digital-license-manager-data';

	/**
	 * ProductData constructor.
	 */
	public function __construct() {
		add_action( 'admin_head', array( $this, 'styleTab' ), 10, 1 );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'productTab' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'productTabDataPanel' ), 10, 1 );
		add_action( 'save_post_product', array( $this, 'productSave' ), 10, 1 );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variableProductDataPanel' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'variableProductSave' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'adminNotices' ), 10 );
		add_filter( 'dlm_validate_product_id', array( $this, 'validateProductId' ), 10, 2 );
	}

	/**
	 * Outputs the admin notices
	 */
	public function adminNotices() {
		global $post_type;
		$error = get_transient( 'dlm_error' );
		if ( ! empty( $error ) && 'product' === $post_type ) {
			?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo is_wp_error( $error ) ? $error->get_error_message() : $error; ?></p>
            </div>
			<?php
			delete_transient( 'dlm_error' );
		}
	}

	/**
	 * Adds an icon to the new data tab.
	 *
	 * @see https://docs.woocommerce.com/document/utilising-the-woocommerce-icon-font-in-your-extensions/
	 * @see https://developer.wordpress.org/resource/dashicons/
	 */
	public function styleTab() {
		echo sprintf(
			'<style>#woocommerce-product-data ul.wc-tabs li.%s_options a:before { font-family: %s; content: "%s"; }</style>',
			self::ADMIN_TAB_NAME,
			'dashicons',
			'\f112'
		);
	}

	/**
	 * Adds a product data tab for simple WooCommerce products.
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function productTab( $tabs ) {
		$tabs[ self::ADMIN_TAB_NAME ] = array(
			'label'    => __( 'License Manager', 'digital-license-manager' ),
			'target'   => self::ADMIN_TAB_TARGET,
			'class' => apply_filters( 'dlm_woocommerce_product_edit_show_if', array( 'show_if_simple', 'show_if_subscription' ), $tabs, self::ADMIN_TAB_NAME ),
			'priority' => 21
		);

		return $tabs;
	}

	/**
	 * Displays the new fields inside the new product data tab.
	 */
	public function productTabDataPanel() {

		global $post;

		$screen = get_current_screen();
		$is_add = isset( $screen->action ) && $screen->action === 'add' && $screen->post_type === 'product';

		echo '<div id="' . self::ADMIN_TAB_TARGET . '" class="panel woocommerce_options_panel">';
		if ( $is_add ) {
			echo '<p>' . __( 'Those options are only available on the <strong>Edit</strong> screen. Please pick product type and then create the product in order to access those options.', 'digital-license-manager' ) . '</p>';
		} else {
			$product = wc_get_product( $post->ID );
			$fields  = array();
			if ( $product->is_type( array( 'simple', 'subscription' ) ) ) {
				$fields = $this->getSimpleProductFields( $product );
			}
			$globalFields = $this->getGlobalProductFields( $product );
			if ( ! empty( $globalFields ) ) {
				$fields = array_merge( $fields, $globalFields );
			}
			echo '<input type="hidden" name="dlm_edit_flag" value="true" />';
			foreach ( $fields as $group ) {
				echo '<div class="options_group">';
				foreach ( $group as $field ) {
					if ( function_exists( 'woocommerce_wp_' . $field['type'] ) ) {
						call_user_func( 'woocommerce_wp_' . $field['type'], $field['params'] );
						if ( ! empty( $field['after'] ) ) {
							echo wp_kses( $field['after'], SanitizeHelper::ksesAllowedHtmlTags() );
						}
					}
				}
				echo '</div>';
			}
		}
		echo '</div>';
	}

	/**
	 * Hook which triggers when the WooCommerce Product is being saved or updated.
	 *
	 * @param int $postId
	 */
	public function productSave( $postId ) {
		// Edit flag isn't set
		if ( ! isset( $_POST['dlm_edit_flag'] ) ) {
			return;
		}

		$product = wc_get_product( $postId );
		if ( ! $product ) {
			return;
		}

		// Update licensed product flag, according to checkbox.
		$licensedProduct = ! empty( $_POST['dlm_licensed_product'] ) ? (int) $_POST['dlm_licensed_product'] : 0;
		$product->update_meta_data( 'dlm_licensed_product', $licensedProduct );

		// Update delivered quantity, according to field.
		$deliveredQuantity = ! empty( $_POST['dlm_licensed_product_delivered_quantity'] ) ? (int) $_POST['dlm_licensed_product_delivered_quantity'] : 0;
		$product->update_meta_data( 'dlm_licensed_product_delivered_quantity', $deliveredQuantity ? $deliveredQuantity : 1 );

		// Update the licenses source, according to field.
		$licensesSource = ! empty( $_POST['dlm_licensed_product_licenses_source'] ) ? sanitize_text_field( $_POST['dlm_licensed_product_licenses_source'] ) : 'stock';
		$product->update_meta_data( 'dlm_licensed_product_licenses_source', $licensesSource );

		// Update the max activations behavor, according to field
		$maxActivationsBehavior = ! empty( $_POST['dlm_licensed_product_activations_behavior'] ) ? sanitize_text_field( $_POST['dlm_licensed_product_activations_behavior'] ) : 'standard';
		$product->update_meta_data( 'dlm_licensed_product_activations_behavior', $maxActivationsBehavior );


		// Update the assigned generator id, according to select field.
		if ( 'generators' === $licensesSource ) {
			$assignedGenerator = ! empty( $_POST['dlm_licensed_product_assigned_generator'] ) ? (int) $_POST['dlm_licensed_product_assigned_generator'] : 0;
			$product->update_meta_data( 'dlm_licensed_product_assigned_generator', $assignedGenerator );

			// Warn the user if they don't have generator selected.
			if ( ! $assignedGenerator ) {
				$error = new WP_Error( 2, __( '<strong>Error:</strong> Please select valid License generator in "License Manager" options.', 'digital-license-manager' ) );
				set_transient( 'dlm_error', $error, 60 );
			}
		} else {
			$product->delete_meta_data( 'dlm_licensed_product_assigned_generator' );
		}

		do_action( 'dlm_before_product_save', $postId, $product );

		$product->save();

		do_action( 'dlm_product_save', $postId, $product );
	}

	/**
	 * Adds the new product data fields to variable WooCommerce Products.
	 *
	 * @param int $loop
	 * @param array $variationData
	 * @param WP_Post $variation
	 */
	public function variableProductDataPanel( $loop, $variationData, $variation ) {

		$product = wc_get_product( $variation->ID );

		$fields = $this->getVariableProductFields( $product, $loop );

		echo sprintf(
			'<p class="form-row form-row-full dlm-form-row-section"><strong>%s</strong></p>',
			__( 'Digital License Manager', 'digital-license-manager' )
		);

		echo '<input type="hidden" name="dlm_edit_flag" value="true" />';

		foreach ( $fields as $group ) {
			foreach ( $group as $field ) {
				$field_id                = $field['params']['id'];
				$field['params']['id']   = esc_attr( $field_id . '_' . $loop );
				$field['params']['name'] = esc_attr( $field_id . '[' . $loop . ']' );
				if ( isset( $field['params']['custom_attributes']['data-conditional-source'] ) ) {
					$field['params']['custom_attributes']['data-conditional-source'] = esc_attr( $field['params']['custom_attributes']['data-conditional-source'] . '_' . $loop );
				}
				if ( function_exists( 'woocommerce_wp_' . $field['type'] ) ) {
					echo '<div class="options_group">';
					call_user_func( 'woocommerce_wp_' . $field['type'], $field['params'] );
					if ( ! empty( $field['after'] ) ) {
						echo wp_kses( $field['after'], SanitizeHelper::ksesAllowedHtmlTags() );
					}
					echo '</div>';
				}
			}
		}
	}

	/**
	 * Saves the data from the product variation fields.
	 *
	 * @param int $variationId
	 * @param int $i
	 */
	public function variableProductSave( $variationId, $i ) {

		$variation = wc_get_product( $variationId );
		if ( ! $variation ) {
			return;
		}

		// Update licensed product flag, according to checkbox.
		$licensedProduct = ! empty( $_POST['dlm_licensed_product'][ $i ] ) ? (int) $_POST['dlm_licensed_product'][ $i ] : 0;
		$variation->update_meta_data( 'dlm_licensed_product', $licensedProduct );

		// Update delivered quantity, according to field.
		$deliveredQuantity = ! empty( $_POST['dlm_licensed_product_delivered_quantity'][ $i ] ) ? (int) $_POST['dlm_licensed_product_delivered_quantity'][ $i ] : 0;
		$variation->update_meta_data( 'dlm_licensed_product_delivered_quantity', $deliveredQuantity ? $deliveredQuantity : 1 );

		// Update the licenses source, according to field.
		$licensesSource = ! empty( $_POST['dlm_licensed_product_licenses_source'][ $i ] ) ? sanitize_text_field( $_POST['dlm_licensed_product_licenses_source'][ $i ] ) : 'stock';
		$variation->update_meta_data( 'dlm_licensed_product_licenses_source', $licensesSource );

		// Update the assigned generator id, according to select field.
		if ( 'generators' === $licensesSource ) {
			$assignedGenerator = ! empty( $_POST['dlm_licensed_product_assigned_generator'][ $i ] ) ? (int) $_POST['dlm_licensed_product_assigned_generator'][ $i ] : 0;
			$variation->update_meta_data( 'dlm_licensed_product_assigned_generator', $assignedGenerator );

			// Warn the user if they don't have generator selected.
			if ( ! $assignedGenerator ) {
				$error = new WP_Error( 2, sprintf( __( '<strong>Error:</strong> Please select valid License generator in variation #%d Digital License Manager options.', 'digital-license-manager' ), $i ) );
				set_transient( 'dlm_error', $error, 60 );
			}
		} else {
			$variation->delete_meta_data( 'dlm_licensed_product_assigned_generator' );
		}

		do_action( 'dlm_before_variable_product_save', $variationId, $i, $variation );

		$variation->save();

		do_action( 'dlm_variable_product_save', $variationId, $i, $variation );
	}

	/**
	 * Validate the product id
	 *
	 * @param $isValid
	 * @param $productId
	 *
	 * @return bool
	 */
	public function validateProductId( $isValid, $productId ) {
		return function_exists( 'wc_get_product' ) && wc_get_product( $productId );
	}

	/**
	 * Checks whether a product is licensed.
	 *
	 * @param $productId
	 *
	 * @return bool
	 */
	public static function isLicensed( $productId ) {

		$product = wc_get_product( $productId );

		if ( $product->get_meta( 'dlm_licensed_product', true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Retrieve assigned products for a specific generator.
	 *
	 * @param $generatorId
	 *
	 * @return array
	 */
	public static function getByGenerator( $generatorId ) {

		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$cleanGeneratorId = $generatorId ? absint( $generatorId ) : null;

		if ( ! $cleanGeneratorId ) {
			return [];
		}

		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
                    SELECT
                        post_id
                    FROM
                        {$wpdb->postmeta}
                    WHERE
                        1 = 1
                        AND meta_key = %s
                        AND meta_value = %d
                ",
				'dlm_licensed_product_assigned_generator',
				$cleanGeneratorId
			),
			OBJECT
		);

		if ( $results ) {
			$products = [];

			foreach ( $results as $row ) {
				if ( ! $product = wc_get_product( $row->post_id ) ) {
					continue;
				}

				$products[] = $product;
			}
		} else {
			$products = [];
		}

		return $products;
	}


	/**
	 * Return the generator options
	 * @return array
	 */
	private function getGeneratorOptions() {
		$options    = array( '' => __( 'Please select a generator', 'digital-license-manager' ) );
		$generators = Generators::instance()->findAll();
		if ( $generators ) {
			foreach ( $generators as $generator ) {
				$options[ $generator->getId() ] = sprintf(
					'(#%d) %s',
					$generator->getId(),
					$generator->getName()
				);
			}
		}

		return $options;
	}

	/**
	 * Returns the switch options
	 *
	 * @param string $positive
	 * @param string $negative
	 *
	 * @return array|string[]
	 */
	private function getSwitchOptions( $positive = 'Yes', $negative = 'No' ) {
		return array(
			0 => $negative,
			1 => $positive,
		);
	}

	/**
	 * Return metadata
	 *
	 * @param $product_id
	 * @param $key
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	private function getMeta( $product_id, $key, $default = '' ) {

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return $default;
		}

		$value = $product->get_meta( $key, true );
		if ( empty( $value ) ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Return the product fields
	 *
	 * @param \WC_Product $product
	 *
	 * @return mixed|void
	 */
	private function getProductFields( $product, $loop = null ) {

		$licenseService    = new LicensesService();
		$isVariableProduct = $this->isVariableProduct( $product );
		$licenseSource     = $this->getMeta( $product->get_id(), 'dlm_licensed_product_licenses_source', 'stock' );

		$fields = array(
			array(
				array(
					'type'   => 'select',
					'params' => array(
						'id'            => 'dlm_licensed_product',
						'label'         => esc_html__( 'Sell Licenses', 'digital-license-manager' ),
						'description'   => esc_html__( 'Enable license key delivery for this product', 'digital-license-manager' ),
						'value'         => (int) $this->getMeta( $product->get_Id(), 'dlm_licensed_product', 0 ),
						'cbvalue'       => 1,
						'desc_tip'      => true,
						'options'       => $this->getSwitchOptions(),
						'wrapper_class' => $isVariableProduct ? 'form-row form-row-first' : '',
					)
				),
				array(
					'type'   => 'text_input',
					'params' => array(
						'id'                => 'dlm_licensed_product_delivered_quantity',
						'label'             => esc_html__( 'Delivered quantity', 'digital-license-manager' ),
						'description'       => esc_html__( 'Defines the amount of license keys to be delivered upon purchase.', 'digital-license-manager' ),
						'value'             => $this->getMeta( $product->get_id(), 'dlm_licensed_product_delivered_quantity', 1 ),
						'desc_tip'          => true,
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '1'
						),
						'wrapper_class'     => $isVariableProduct ? 'form-row form-row-last' : '',
					)
				),
			),
			array(
				array(
					'type'   => 'select',
					'params' => array(
						'id'            => 'dlm_licensed_product_licenses_source',
						'label'         => esc_html__( 'Licenses source', 'digital-license-manager' ),
						'description'   => esc_html__( 'Select the source of the license keys. If you want them to be generated with a generator select "Provide licenses by using generator" and then specify a generator below. If you want to sell from your existing licenses make sure you have a stock of license that are marked as ACTIVE.', 'digital-license-manager' ),
						'value'         => $licenseSource,
						'cbvalue'       => 1,
						'desc_tip'      => true,
						'options'       => array(
							'stock'      => sprintf( __( 'Provide licenses from stock (%d available)', 'digital-license-manager' ), $licenseService->getLicensesStockCount( $product->get_id() ) ),
							'generators' => __( 'Provide licenses by using generator', 'digital-license-manager' ),
						),
						'wrapper_class' => $isVariableProduct ? 'form-row form-row-first dlm-field-conditional-src' : 'dlm-field-conditional-src',
					)
				),
				array(
					'type'   => 'select',
					'params' => array(
						'id'                => 'dlm_licensed_product_assigned_generator',
						'label'             => __( 'Licenses generator', 'digital-license-manager' ),
						'description'       => esc_html__( 'Select the Generator that will be used to generate and deliver keys for this product. Required only if source is set to "Provide licenses by using generator".', 'digital-license-manager' ),
						'desc_tip'          => true,
						'options'           => $this->getGeneratorOptions(),
						'value'             => $this->getMeta( $product->get_id(), 'dlm_licensed_product_assigned_generator', 0 ),
						'wrapper_class'     => $isVariableProduct ? 'form-row form-row-last dlm-field-conditional-target' : 'dlm-field-conditional-target',
						'custom_attributes' => array(
							'data-conditional-source'  => 'dlm_licensed_product_licenses_source',
							'data-conditional-show-if' => 'generators',
						),
					)
				),
				array(
					'type'   => 'select',
					'params' => array(
						'id'            => 'dlm_licensed_product_activations_behavior',
						'label'         => __( 'Max Activations Behavior', 'digital-license-manager' ),
						'description'   => esc_html__( 'Select the behavior of the max activations for the new licenses whether it to be based on quantity or the generator default value".', 'digital-license-manager' ),
						'desc_tip'      => true,
						'options'       => [
							'standard' => __( 'Standard - Based on the Generator\'s "Max Activations"' ),
							'quantity' => __( 'Product Quantity - Always deliver single license and set activations limit based on product quantity' ),
						],
						'value'         => $this->getMeta( $product->get_id(), 'dlm_licensed_product_activations_behavior', 'standard' ),
						'wrapper_class' => $isVariableProduct ? 'form-row form-row-last dlm-field-conditional-target' : 'dlm-field-conditional-target',
						/*'custom_attributes' => array(
							'data-conditional-source'  => 'dlm_licensed_product_licenses_source',
							'data-conditional-show-if' => 'generators',
						),*/
					)
				),
			)
		);

		return apply_filters( 'dlm_product_fields', $fields, $product );
	}

	/**
	 * Return the simple product fields
	 *
	 * @param $product
	 *
	 * @return mixed|void
	 */
	private function getSimpleProductFields( $product ) {
		return apply_filters( 'dlm_simple_product_fields', $this->getProductFields( $product ), $product );
	}

	/**
	 * Return the variation product fields
	 *
	 * @param $product
	 * @param $loop
	 *
	 * @return mixed|void
	 */
	private function getVariableProductFields( $product, $loop ) {

		$fields = apply_filters( 'dlm_variable_product_fields', $this->getProductFields( $product, $loop ), $product, $loop );

		// Force enable data tip icon for variations and checkbox fields.
		foreach ( $fields as $group_key => $group ) {
			foreach ( $group as $field_key => $field ) {
				$fields[ $group_key ][ $field_key ]['params']['desc_tip'] = true;
			}
		}

		return $fields;
	}

	/**
	 * Returns the global product fields regardless if it is simple or variation product
	 * @return array
	 */
	private function getGlobalProductFields( $product ) {
		return apply_filters( 'dlm_global_product_fields', array(), $product );
	}

	/**
	 * Is simple product?
	 *
	 * @param \WC_Product $product
	 */
	private function isVariableProduct( $product ) {
		return ! in_array( $product->get_type(), array( 'simple', 'subscription' ) );
	}

	/**
	 * Return the license stock count
	 *
	 * @param $product_id
	 *
	 * @return false|int
	 */
	public static function getLicenseStockCount( $product_id ) {

		$licenseService = new LicensesService();
		_deprecated_function( __METHOD__, '1.3.5', 'Core\Services\LicensesService::getLicensesStockCount()' );

		return $licenseService->getLicensesStockCount( $product_id );
	}
}
