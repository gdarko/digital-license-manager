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
		/**
		 * @see https://www.proy.info/woocommerce-admin-custom-product-data-tab/
		 */
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'simpleProductTab' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'styleTab' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'simpleProductDataPanel' ), 10, 1 );
		add_action( 'save_post_product', array( $this, 'simpleProductSave' ), 10, 1 );
		add_action( 'woocommerce_product_after_variable_attributes', array(
			$this,
			'variableProductDataPanel'
		), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'variableProductSave' ), 10, 2 );
	}

	/**
	 * Adds a product data tab for simple WooCommerce products.
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function simpleProductTab( $tabs ) {
		$tabs[ self::ADMIN_TAB_NAME ] = array(
			'label'    => __( 'License Manager Settings', 'digital-license-manager' ),
			'target'   => self::ADMIN_TAB_TARGET,
			'class'    => array( 'show_if_simple' ),
			'priority' => 21
		);

		return $tabs;
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
	 * Displays the new fields inside the new product data tab.
	 */
	public function simpleProductDataPanel() {
		global $post;

		/** @var GeneratorResourceModel[] $generators */
		$generators        = GeneratorResourceRepository::instance()->findAll();
		$licensed          = get_post_meta( $post->ID, 'dlm_licensed_product', true );
		$deliveredQuantity = get_post_meta( $post->ID, 'dlm_licensed_product_delivered_quantity', true );
		$generatorId       = get_post_meta( $post->ID, 'dlm_licensed_product_assigned_generator', true );
		$useGenerator      = get_post_meta( $post->ID, 'dlm_licensed_product_use_generator', true );
		$useStock          = get_post_meta( $post->ID, 'dlm_licensed_product_use_stock', true );
		$generatorOptions  = array( '' => __( 'Please select a generator', 'digital-license-manager' ) );
		$licenseStockCount = LicenseResourceRepository::instance()->countBy(
			array(
				'product_id' => $post->ID,
				'status'     => LicenseStatus::ACTIVE
			)
		);

		if ( $generators ) {
			foreach ( $generators as $generator ) {
				$generatorOptions[ $generator->getId() ] = sprintf(
					'(#%d) %s',
					$generator->getId(),
					$generator->getName()
				);
			}
		}

		echo sprintf(
			'<div id="%s" class="panel woocommerce_options_panel"><div class="options_group">',
			self::ADMIN_TAB_TARGET
		);

		echo '<input type="hidden" name="dlm_edit_flag" value="true" />';

		// Checkbox "dlm_licensed_product"
		woocommerce_wp_checkbox(
			array(
				'id'          => 'dlm_licensed_product',
				'label'       => esc_html__( 'Sell license keys', 'digital-license-manager' ),
				'description' => esc_html__( 'Sell license keys for this product', 'digital-license-manager' ),
				'value'       => $licensed,
				'cbvalue'     => 1,
				'desc_tip'    => false
			)
		);

		// Number "dlm_licensed_product_deliver_amount"
		woocommerce_wp_text_input(
			array(
				'id'                => 'dlm_licensed_product_delivered_quantity',
				'label'             => esc_html__( 'Delivered quantity', 'digital-license-manager' ),
				'value'             => $deliveredQuantity ? $deliveredQuantity : 1,
				'description'       => esc_html__( 'Defines the amount of license keys to be delivered upon purchase.', 'digital-license-manager' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '1'
				)
			)
		);

		echo '</div><div class="options_group">';

		// Checkbox "dlm_licensed_product_use_generator"
		woocommerce_wp_checkbox(
			array(
				'id'          => 'dlm_licensed_product_use_generator',
				'label'       => esc_html__( 'Generate license keys', 'digital-license-manager' ),
				'description' => esc_html__( 'Automatically generate license keys with each sold product', 'digital-license-manager' ),
				'value'       => $useGenerator,
				'cbvalue'     => 1,
				'desc_tip'    => false
			)
		);

		// Dropdown "dlm_licensed_product_assigned_generator"
		woocommerce_wp_select(
			array(
				'id'      => 'dlm_licensed_product_assigned_generator',
				'label'   => __( 'Assign generator', 'digital-license-manager' ),
				'options' => $generatorOptions,
				'value'   => $generatorId
			)
		);

		echo '</div><div class="options_group">';

		// Checkbox "dlm_licensed_product_use_stock"
		woocommerce_wp_checkbox(
			array(
				'id'          => 'dlm_licensed_product_use_stock',
				'label'       => esc_html__( 'Sell from stock', 'digital-license-manager' ),
				'description' => esc_html__( 'Sell license keys from the available stock.', 'digital-license-manager' ),
				'value'       => $useStock,
				'cbvalue'     => 1,
				'desc_tip'    => false
			)
		);

		echo sprintf(
			'<p class="form-field"><label>%s</label><span class="description">%d %s</span></p>',
			__( 'Available', 'digital-license-manager' ),
			$licenseStockCount,
			__( 'License key(s) in stock and available for sale', 'digital-license-manager' )
		);

		do_action( 'dlm_simple_product_data_panel', $post );

		echo '</div></div>';
	}

	/**
	 * Hook which triggers when the WooCommerce Product is being saved or updated.
	 *
	 * @param int $postId
	 */
	public function simpleProductSave( $postId ) {
		// Edit flag isn't set
		if ( ! isset( $_POST['dlm_edit_flag'] ) ) {
			return;
		}

		// Update licensed product flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product'] ) ) {
			update_post_meta( $postId, 'dlm_licensed_product', 1 );
		} else {
			update_post_meta( $postId, 'dlm_licensed_product', 0 );
		}

		// Update delivered quantity, according to field.
		$deliveredQuantity = (int) $_POST['dlm_licensed_product_delivered_quantity'];

		update_post_meta(
			$postId,
			'dlm_licensed_product_delivered_quantity',
			$deliveredQuantity ? $deliveredQuantity : 1
		);

		// Update the use stock flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product_use_stock'] ) ) {
			update_post_meta( $postId, 'dlm_licensed_product_use_stock', 1 );
		} else {
			update_post_meta( $postId, 'dlm_licensed_product_use_stock', 0 );
		}

		// Update the assigned generator id, according to select field.
		update_post_meta(
			$postId,
			'dlm_licensed_product_assigned_generator',
			(int) $_POST['dlm_licensed_product_assigned_generator']
		);

		// Update the use generator flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product_use_generator'] ) ) {
			// You must select a generator if you wish to assign it to the product.
			if ( ! $_POST['dlm_licensed_product_assigned_generator'] ) {
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

		do_action( 'dlm_simple_product_save', $postId );
	}

	/**
	 * Adds the new product data fields to variable WooCommerce Products.
	 *
	 * @param int $loop
	 * @param array $variationData
	 * @param WP_Post $variation
	 */
	public function variableProductDataPanel( $loop, $variationData, $variation ) {
		/** @var GeneratorResourceModel[] $generators */
		$generators        = GeneratorResourceRepository::instance()->findAll();
		$productId         = $variation->ID;
		$licensed          = get_post_meta( $productId, 'dlm_licensed_product', true );
		$deliveredQuantity = get_post_meta( $productId, 'dlm_licensed_product_delivered_quantity', true );
		$generatorId       = get_post_meta( $productId, 'dlm_licensed_product_assigned_generator', true );
		$useGenerator      = get_post_meta( $productId, 'dlm_licensed_product_use_generator', true );
		$useStock          = get_post_meta( $productId, 'dlm_licensed_product_use_stock', true );
		$generatorOptions  = array( '' => __( 'Please select a generator', 'digital-license-manager' ) );
		$licenseStockCount = LicenseResourceRepository::instance()->countBy(
			array(
				'product_id' => $productId,
				'status'     => LicenseStatus::ACTIVE
			)
		);

		foreach ( $generators as $generator ) {
			$generatorOptions[ $generator->getId() ] = sprintf(
				'(#%d) %s',
				$generator->getId(),
				$generator->getName()
			);
		}

		echo sprintf(
			'<p class="form-row form-row-full"><strong>%s</strong></p>',
			__( 'Digital License Manager', 'digital-license-manager' )
		);

		echo '<input type="hidden" name="dlm_edit_flag" value="true" />';

		$dataTip = esc_attr__( 'Sell license keys for this variation', 'digital-license-manager' );
		$name    = esc_attr( "dlm_licensed_product[{$loop}]" );
		$checked = checked( 1, $licensed, false );

		echo '
            <p class="form-row form-row-full options">
                <label class="tips" data-tip="' . $dataTip . '">
                    ' . __( 'Sell license keys', 'digital-license-manager' ) . '
                    <input type="checkbox" class="checkbox" name="' . $name . '" ' . $checked . '/>
                </label>
            </p>
        ';

		woocommerce_wp_text_input(
			array(
				'id'                => "dlm_licensed_product_delivered_quantity_{$loop}",
				'name'              => "dlm_licensed_product_delivered_quantity[{$loop}]",
				'label'             => __( 'Delivered quantity', 'digital-license-manager' ),
				'value'             => ( $deliveredQuantity ) ? $deliveredQuantity : 1,
				'wrapper_class'     => 'form-row form-row-full',
				'description'       => __(
					'Defines the amount of license keys to be delivered upon purchase.',
					'digital-license-manager'
				),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '1'
				)
			)
		);

		$dataTip = esc_attr__(
			'Automatically generate license keys with each sold variation',
			'digital-license-manager'
		);
		$name    = esc_attr( "dlm_licensed_product_use_generator[{$loop}]" );
		$checked = checked( 1, $useGenerator, false );

		echo '
            <p class="form-row form-row-full options">
                <label class="tips" data-tip="' . $dataTip . '">
                    ' . __( 'Generate license keys', 'digital-license-manager' ) . '
                    <input type="checkbox" class="checkbox" name="' . $name . '" ' . $checked . '/>
                </label>
            </p>
        ';

		woocommerce_wp_select(
			array(
				'id'            => 'dlm_licensed_product_assigned_generator',
				'name'          => "dlm_licensed_product_assigned_generator[{$loop}]",
				'label'         => __( 'Assign generator', 'digital-license-manager' ),
				'options'       => $generatorOptions,
				'value'         => $generatorId,
				'wrapper_class' => 'form-row form-row-full',
			)
		);

		$dataTip = esc_attr__( 'Sell license keys from the available stock.', 'digital-license-manager' );
		$name    = esc_attr( "dlm_licensed_product_use_stock[{$loop}]" );
		$checked = checked( 1, $useStock, false );
		$label   = sprintf(
			'%d %s',
			$licenseStockCount,
			__( 'License key(s) in stock and available for sale.', 'digital-license-manager' )
		);

		echo '
            <p class="form-row form-row-full options">
                <label class="tips" data-tip="' . $dataTip . '">
                    ' . __( 'Sell from stock', 'digital-license-manager' ) . '
                    <input type="checkbox" class="checkbox" name="' . $name . '" ' . $checked . '/>
                </label>
                <label>' . esc_html( $label ) . '</label>
            </p>
        ';

		do_action( 'dlm_variable_product_data_panel', $loop, $variationData, $variation );
	}

	/**
	 * Saves the data from the product variation fields.
	 *
	 * @param int $variationId
	 * @param int $i
	 */
	public function variableProductSave( $variationId, $i ) {
		// Update licensed product flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product'] ) && isset( $_POST['dlm_licensed_product'][ $i ] ) ) {
			update_post_meta( $variationId, 'dlm_licensed_product', 1 );
		} else {
			update_post_meta( $variationId, 'dlm_licensed_product', 0 );
		}

		// Update delivered quantity, according to field.
		$deliveredQuantity = (int) $_POST['dlm_licensed_product_delivered_quantity'][ $i ];

		update_post_meta(
			$variationId,
			'dlm_licensed_product_delivered_quantity',
			$deliveredQuantity ? $deliveredQuantity : 1
		);

		// Update the use stock flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product_use_stock'] )
		     && isset( $_POST['dlm_licensed_product_use_stock'][ $i ] )
		) {
			update_post_meta( $variationId, 'dlm_licensed_product_use_stock', 1 );
		} else {
			update_post_meta( $variationId, 'dlm_licensed_product_use_stock', 0 );
		}

		// Update the assigned generator id, according to select field.
		update_post_meta(
			$variationId,
			'dlm_licensed_product_assigned_generator',
			(int) $_POST['dlm_licensed_product_assigned_generator'][ $i ]
		);

		// Update the use generator flag, according to checkbox.
		if ( isset( $_POST['dlm_licensed_product_use_generator'] )
		     && isset( $_POST['dlm_licensed_product_use_generator'][ $i ] )
		) {
			// You must select a generator if you wish to assign it to the product.
			if ( ! $_POST['dlm_licensed_product_assigned_generator'][ $i ] ) {
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
}
