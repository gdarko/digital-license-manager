<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
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
	const ADMIN_TAB_NAME = 'license_manager_tab';

	/**
	 * @var string
	 */
	const ADMIN_TAB_TARGET = 'license_manager_product_data';

	/**
	 * ProductData constructor.
	 */
	public function __construct() {
		add_action( 'admin_head', array( $this, 'styleTab' ), 10, 1 );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'productTab' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'productTabDataPanel' ), 10, 1 );
		add_action( 'save_post_product', array( $this, 'productSave' ), 10, 1 );
		add_action( 'woocommerce_product_after_variable_attributes', array(
			$this,
			'variableProductDataPanel'
		), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'variableProductSave' ), 10, 2 );
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
			'\f160'
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
			'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_variable-subscription' ),
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
							echo $field['after'];
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

		// Update licensed product flag, according to checkbox.
		$licensedProduct = ! empty( $_POST['dlm_licensed_product'] ) ? (int) $_POST['dlm_licensed_product'] : 0;
		update_post_meta( $postId, 'dlm_licensed_product', $licensedProduct );

		// Update delivered quantity, according to field.
		$deliveredQuantity = ! empty( $_POST['dlm_licensed_product_delivered_quantity'] ) ? (int) $_POST['dlm_licensed_product_delivered_quantity'] : 0;
		update_post_meta( $postId, 'dlm_licensed_product_delivered_quantity', $deliveredQuantity ? $deliveredQuantity : 1 );

		// Update the use stock flag, according to checkbox.
		$licensedProduct = ! empty( $_POST['dlm_licensed_product_use_stock'] ) ? (int) $_POST['dlm_licensed_product_use_stock'] : 0;
		update_post_meta( $postId, 'dlm_licensed_product_use_stock', $licensedProduct );

		// Update the assigned generator id, according to select field.
		$assignedGenerator = ! empty( $_POST['dlm_licensed_product_assigned_generator'] ) ? (int) $_POST['dlm_licensed_product_assigned_generator'] : 0;
		update_post_meta( $postId, 'dlm_licensed_product_assigned_generator', $assignedGenerator );

		// Update the use generator flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product_use_generator'] ) && $_POST['dlm_licensed_product_use_generator'] ) {
			// You must select a generator if you wish to assign it to the product.
			if ( ! $assignedGenerator ) {
				$error = new WP_Error( 2, __( 'Assign a generator if you wish to sell automatically generated licenses for this product.', 'digital-license-manager' ) );

				set_transient( 'dlm_error', $error, 45 );
				update_post_meta( $postId, 'dlm_licensed_product_use_generator', 0 );
				update_post_meta( $postId, 'dlm_licensed_product_assigned_generator', 0 );
			} else {
				update_post_meta( $postId, 'dlm_licensed_product_use_generator', 1 );
			}
		} else {
			update_post_meta( $postId, 'dlm_licensed_product_use_generator', 0 );
			update_post_meta( $postId, 'dlm_licensed_product_assigned_generator', 0 );
		}

		do_action( 'dlm_product_save', $postId );
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

		$fields = $this->getVariableProductFields( $product );

		echo sprintf(
			'<p class="form-row form-row-full dlm-form-row-section"><strong>%s</strong></p>',
			__( 'Digital License Manager', 'digital-license-manager' )
		);

		echo '<input type="hidden" name="dlm_edit_flag" value="true" />';

		foreach ( $fields as $group ) {
			foreach ( $group as $field ) {
				$field['params']['id'] = esc_attr( $field['params']['id'] . '[' . $loop . ']' );
				if ( function_exists( 'woocommerce_wp_' . $field['type'] ) ) {
					echo '<div class="options_group">';
					call_user_func( 'woocommerce_wp_' . $field['type'], $field['params'] );
					if ( ! empty( $field['after'] ) ) {
						echo $field['after'];
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

		// Update licensed product flag, according to checkbox.
		$licensedProduct = ! empty( $_POST['dlm_licensed_product'][ $i ] ) ? (int) $_POST['dlm_licensed_product'][ $i ] : 0;
		update_post_meta( $variationId, 'dlm_licensed_product', $licensedProduct );

		// Update delivered quantity, according to field.
		$deliveredQuantity = ! empty( $_POST['dlm_licensed_product_delivered_quantity'][ $i ] ) ? (int) $_POST['dlm_licensed_product_delivered_quantity'][ $i ] : 0;
		update_post_meta( $variationId, 'dlm_licensed_product_delivered_quantity', $deliveredQuantity ? $deliveredQuantity : 1 );

		// Update the use stock flag, according to checkbox.
		$useStock = ! empty( $_POST['dlm_licensed_product_use_stock'][ $i ] ) ? (int) $_POST['dlm_licensed_product_use_stock'][ $i ] : 0;
		update_post_meta( $variationId, 'dlm_licensed_product_use_stock', $useStock );

		// Update the assigned generator id, according to select field.
		$assignedGenerator = ! empty( $_POST['dlm_licensed_product_assigned_generator'][ $i ] ) ? (int) $_POST['dlm_licensed_product_assigned_generator'][ $i ] : 0;
		update_post_meta( $variationId, 'dlm_licensed_product_assigned_generator', $assignedGenerator );

		// Update the use generator flag, according to checkbox.
		if ( ! empty( $_POST['dlm_licensed_product_use_generator'][ $i ] ) ) {
			// You must select a generator if you wish to assign it to the product.
			if ( ! $assignedGenerator ) {
				$error = new WP_Error( 2, __( 'Assign a generator if you wish to sell automatically generated licenses for this product.', 'digital-license-manager' ) );
				set_transient( 'dlm_error', $error, 45 );
				update_post_meta( $variationId, 'dlm_licensed_product_use_generator', 0 );
				update_post_meta( $variationId, 'dlm_licensed_product_assigned_generator', 0 );
			} else {
				update_post_meta( $variationId, 'dlm_licensed_product_use_generator', 1 );
			}
		} else {
			update_post_meta( $variationId, 'dlm_licensed_product_use_generator', 0 );
			update_post_meta( $variationId, 'dlm_licensed_product_assigned_generator', 0 );
		}

		do_action( 'dlm_variable_product_save', $variationId, $i );
	}

	/**
	 * Checks whether a product is licensed.
	 *
	 * @param $productId
	 *
	 * @return bool
	 */
	public static function isLicensed( $productId ) {
		if ( get_post_meta( $productId, 'dlm_licensed_product', true ) ) {
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
		$generators = GeneratorResourceRepository::instance()->findAll();
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
	 * Return the license stock count
	 *
	 * @param $product_id
	 *
	 * @return false|int
	 */
	private function getLicenseStockCount( $product_id ) {
		return LicenseResourceRepository::instance()->countBy( array(
			'product_id' => $product_id,
			'status'     => LicenseStatus::ACTIVE
		) );
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
		$value = get_post_meta( $product_id, $key, true );
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
	private function getProductFields( $product ) {

		$isVariableProduct = $this->isVariableProduct( $product );

		$fields = array(
			array(
				array(
					'type'   => 'select',
					'params' => array(
						'id'            => 'dlm_licensed_product',
						'label'         => esc_html__( 'Sell license keys', 'digital-license-manager' ),
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
						'id'            => 'dlm_licensed_product_use_generator',
						'label'         => esc_html__( 'Generate license keys', 'digital-license-manager' ),
						'description'   => esc_html__( 'Automatically generate license keys with each sold product', 'digital-license-manager' ),
						'value'         => (int) $this->getMeta( $product->get_id(), 'dlm_licensed_product_use_generator', 0 ),
						'cbvalue'       => 1,
						'desc_tip'      => true,
						'options'       => $this->getSwitchOptions(),
						'wrapper_class' => $isVariableProduct ? 'form-row form-row-first' : '',
					)
				),
				array(
					'type'   => 'select',
					'params' => array(
						'id'            => 'dlm_licensed_product_assigned_generator',
						'label'         => __( 'Assign generator', 'digital-license-manager' ),
						'description'   => esc_html__( 'Select the Generator that will be used to generate and deliver keys for this product', 'digital-license-manager' ),
						'desc_tip'      => true,
						'options'       => $this->getGeneratorOptions(),
						'value'         => $this->getMeta( $product->get_id(), 'dlm_licensed_product_assigned_generator', 0 ),
						'wrapper_class' => $isVariableProduct ? 'form-row form-row-last' : '',
					)
				),
			),
			array(
				array(
					'type'   => 'select',
					'params' => array(
						'id'            => 'dlm_licensed_product_use_stock',
						'label'         => esc_html__( 'Sell from stock', 'digital-license-manager' ),
						'description'   => esc_html__( 'Sell license keys from the available stock.', 'digital-license-manager' ),
						'value'         => (int) $this->getMeta( $product->get_id(), 'dlm_licensed_product_use_stock', 0 ),
						'cbvalue'       => 1,
						'desc_tip'      => true,
						'options'       => $this->getSwitchOptions(),
						'wrapper_class' => $isVariableProduct ? 'form-row form-row-full dlm-clear-margin-bottom' : 'dlm-clear-margin-bottom',
					),
					'after'  => sprintf(
						'<p class="form-field" style="margin-top: 2px;font-style: italic;"><label>%s</label><span class="description">%d %s</span></p>',
						'',
						$this->getLicenseStockCount( $product->get_id() ),
						__( 'License key(s) in stock and available for sale', 'digital-license-manager' )
					)
				)
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
	 *
	 * @return mixed|void
	 */
	private function getVariableProductFields( $product ) {

		$fields = apply_filters( 'dlm_variable_product_fields', $this->getProductFields( $product ), $product );

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
}
