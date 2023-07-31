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

namespace IdeoLogix\DigitalLicenseManager\ListTables;

use DateTime;
use Exception;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractListTable;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses as LicensesRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Stock;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;
use WC_Product;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Class Licenses
 * @package IdeoLogix\DigitalLicenseManager\ListTables
 */
class Licenses extends AbstractListTable {
	/**
	 * Path to spinner image.
	 */
	const SPINNER_URL = '/wp-admin/images/loading.gif';

	/**
	 * Can hide?
	 * @var bool|null
	 */
	protected $canHide;

	/**
	 * LicensesList constructor.
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Licenses', 'digital-license-manager' ),
				'plural'   => __( 'Licenses', 'digital-license-manager' ),
				'ajax'     => false
			)
		);

		$this->slug      = PageSlug::LICENSES;
		$this->table     = LicensesRepository::instance()->getTable();
		$this->canEdit   = current_user_can( 'dlm_edit_licenses' );
		$this->canDelete = current_user_can( 'dlm_delete_licenses' );
		$this->canExport = current_user_can( 'dlm_export_licenses' );
	}

	/**
	 * Creates the different status filter links at the top of the table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$statusLinks = array();
		$current     = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all';

		// All link
		$class              = $current == 'all' ? ' class="current"' : '';
		$allUrl             = remove_query_arg( 'status' );
		$statusLinks['all'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$allUrl,
			$class,
			__( 'All', 'digital-license-manager' ),
			LicensesRepository::instance()->count()
		);

		// Sold link
		$class               = $current == LicenseStatus::SOLD ? ' class="current"' : '';
		$soldUrl             = esc_url( add_query_arg( 'status', LicenseStatus::SOLD ) );
		$statusLinks['sold'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$soldUrl,
			$class,
			__( 'Sold', 'digital-license-manager' ),
			LicensesRepository::instance()->countBy( array( 'status' => LicenseStatus::SOLD ) )
		);

		// Delivered link
		$class                    = $current == LicenseStatus::DELIVERED ? ' class="current"' : '';
		$deliveredUrl             = esc_url( add_query_arg( 'status', LicenseStatus::DELIVERED ) );
		$statusLinks['delivered'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$deliveredUrl,
			$class,
			__( 'Delivered', 'digital-license-manager' ),
			LicensesRepository::instance()->countBy( array( 'status' => LicenseStatus::DELIVERED ) )
		);

		// Active link
		$class                 = $current == LicenseStatus::ACTIVE ? ' class="current"' : '';
		$activeUrl             = esc_url( add_query_arg( 'status', LicenseStatus::ACTIVE ) );
		$statusLinks['active'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$activeUrl,
			$class,
			__( 'Active', 'digital-license-manager' ),
			LicensesRepository::instance()->countBy( array( 'status' => LicenseStatus::ACTIVE ) )
		);

		// Inactive link
		$class                   = $current == LicenseStatus::INACTIVE ? ' class="current"' : '';
		$inactiveUrl             = esc_url( add_query_arg( 'status', LicenseStatus::INACTIVE ) );
		$statusLinks['inactive'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$inactiveUrl,
			$class,
			__( 'Inactive', 'digital-license-manager' ),
			LicensesRepository::instance()->countBy( array( 'status' => LicenseStatus::INACTIVE ) )
		);

		// Disabled link
		$class                   = $current == LicenseStatus::DISABLED ? ' class="current"' : '';
		$disabledUrl             = esc_url( add_query_arg( 'status', LicenseStatus::DISABLED ) );
		$statusLinks['disabled'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$disabledUrl,
			$class,
			__( 'Disabled', 'digital-license-manager' ),
			LicensesRepository::instance()->countBy( array( 'status' => LicenseStatus::DISABLED ) )
		);

		return $statusLinks;
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			echo '<div class="alignleft actions">';
			$this->orderDropdown();
			$this->productDropdown();
			$this->userDropdown();
			submit_button( __( 'Filter', 'digital-license-manager' ), '', 'filter-action', false );
			echo '</div>';
		}
	}

	/**
	 * Displays the order dropdown filter.
	 */
	public function orderDropdown() {

		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$selected = isset( $_REQUEST['order-id'] ) && ! empty( $_REQUEST['order-id'] ) ? wc_get_order( (int) $_REQUEST['order-id'] ) : '';
		?>
        <label for="filter-by-order-id" class="screen-reader-text">
            <span><?php _e( 'Filter by order', 'digital-license-manager' ); ?></span>
        </label>

        <select name="order-id" id="filter-by-order-id">
			<?php if ( $selected ): ?>
                <option selected
                        value="<?php echo esc_attr( $selected->get_id() ); ?>"><?php echo sprintf( '#%d', $selected->get_id() ); ?></option>
			<?php endif; ?>
        </select>
		<?php
	}

	/**
	 * Displays the product dropdown filter.
	 */
	public function productDropdown() {

		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$selected = isset( $_REQUEST['product-id'] ) && ! empty( $_REQUEST['product-id'] ) ? wc_get_product( (int) $_REQUEST['product-id'] ) : '';
		?>
        <label for="filter-by-product-id" class="screen-reader-text">
            <span><?php _e( 'Filter by product', 'digital-license-manager' ); ?></span>
        </label>

        <select name="product-id" id="filter-by-product-id">
			<?php if ( $selected ): ?>
                <option selected value="<?php echo esc_attr( $selected->get_id() ); ?>">
					<?php
					echo sprintf(
					/* translators: $1: order id, $2 customer name */
						'(#%1$s) %2$s',
						$selected->get_id(),
						$selected->get_formatted_name()
					);
					?>
                </option>
			<?php endif; ?>
        </select>
		<?php
	}

	/**
	 * Displays the user dropdown filter.
	 */
	public function userDropdown() {

		$selected = isset( $_REQUEST['user-id'] ) && ! empty( $_REQUEST['user-id'] ) ? get_user_by( (int) $_REQUEST['user-id'], 'id' ) : '';
		?>
        <label for="filter-by-user-id" class="screen-reader-text">
            <span><?php _e( 'Filter by user', 'digital-license-manager' ); ?></span>
        </label><select name="user-id" id="filter-by-user-id">
			<?php if ( $selected ): ?>
                <option selected value="<?php echo esc_attr( $selected->ID ); ?>">
					<?php
					printf(
						'<option value="%d" selected>%s (#%d - %s)</option>',
						$selected->ID,
						$selected->display_name,
						$selected->ID,
						$selected->user_email
					);
					?>
                </option>
			<?php endif; ?>
        </select>
		<?php
	}

	/**
	 * Checkbox column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item->getId()
		);
	}

	/**
	 * License key column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_license_key( $item ) {

		if ( is_null( $this->canHide ) ) {
			$this->canHide = (bool) Settings::get( 'hide_license_keys' );
		}

		if ( $this->canHide ) {
			$title = sprintf( '<code class="dlm-placeholder empty" data-id="%d"></code>', $item->getId() );
			$title .= sprintf(
				'<img class="dlm-spinner" data-id="%d" src="%s">',
				$item->getId(),
				self::SPINNER_URL
			);
		} else {

			$decrypted = CryptoHelper::decrypt( $item->getLicenseKey() );
			if ( is_wp_error( $decrypted ) ) {
				$decrypted = 'ERROR';
			}

			$title = sprintf(
				'<code class="dlm-placeholder">%s</code>',
				$decrypted
			);
			$title .= sprintf(
				'<img class="dlm-spinner" data-id="%d" src="%s">',
				$item->getId(),
				self::SPINNER_URL
			);
		}

		// ID
		$actions['id'] = sprintf( __( 'ID: %d', 'digital-license-manager' ), (int) $item->getId() );

		// Edit
		if ( $this->canEdit ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url(
					wp_nonce_url(
						sprintf(
							'admin.php?page=%s&action=edit&id=%d',
							$this->slug,
							(int) $item->getId()
						),
						'dlm_edit_license_key'
					)
				),
				__( 'Edit', 'digital-license-manager' )
			);
		}

		// Hide/Show
		if ( $this->canHide ) {
			$actions['show'] = sprintf(
				'<a class="dlm-license-key-toggle dlm-license-key-show" data-id="%d">%s</a>',
				$item->getId(),
				__( 'Show', 'digital-license-manager' )
			);
			$actions['hide'] = sprintf(
				'<a class="dlm-license-key-toggle dlm-license-key-hide" data-id="%d">%s</a>',
				$item->getId(),
				__( 'Hide', 'digital-license-manager' )
			);
		}


		// Delete
		if ( $this->canDelete ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="dlm-confirm-dialog">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=delete&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item->getId(),
						wp_create_nonce( 'delete' )
					)
				),
				__( 'Delete', 'digital-license-manager' )
			);
		}

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Order ID column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_order_id( $item ) {
		$html = '';

		$order_id = ! empty( $item->getOrderId() ) ? (int) $item->getOrderId() : 0;

		if ( function_exists( 'wc_get_order' ) ) {
			if ( $order = wc_get_order( $order_id ) ) {
				$html = sprintf(
					'<a href="%s" target="_blank">#%s</a>',
					get_edit_post_link( $order_id ),
					$order->get_order_number()
				);
			}
		} else {
			if ( ! empty( $order_id ) ) {
				$html = sprintf( '#%s', $order_id );
			}
		}

		return $html;
	}

	/**
	 * Product ID column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_product_id( $item ) {
		$html = '';

		$product_id = ! empty( $item->getProductId() ) ? (int) $item->getProductId() : '';

		/** @var WC_Product $product */
		if ( function_exists( 'wc_get_product' ) ) {
			if ( $product = wc_get_product( $product_id ) ) {
				if ( $parentId = $product->get_parent_id() ) {
					$html = sprintf(
						'<span>#%s - %s</span>',
						$product->get_id(),
						$product->get_name()
					);

					if ( $parent = wc_get_product( $parentId ) ) {
						$html .= sprintf(
							'<br><small>%s <a href="%s" target="_blank">#%s - %s</a></small>',
							__( 'Variation of', 'digital-license-manager' ),
							get_edit_post_link( $parent->get_id() ),
							$parent->get_id(),
							$parent->get_name()
						);
					}
				} else {
					$html = sprintf(
						'<a href="%s" target="_blank">#%s - %s</a>',
						get_edit_post_link( $item->getProductId() ),
						$product->get_id(),
						$product->get_name()
					);
				}
			}
		} else {
			if ( ! empty( $product_id ) ) {
				$html = sprintf( '#%s', $product_id );
			}
		}


		return $html;
	}

	/**
	 * User ID column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_user_id( $item ) {
		$html = '';

		if ( ! empty( $item->getUserId() ) ) {
			/** @var WP_User $user */
			$user = get_userdata( $item->getUserId() );

			if ( $user instanceof WP_User ) {
				if ( current_user_can( 'edit_users' ) ) {
					$html .= sprintf(
						'<a href="%s">%s (#%d - %s)</a>',
						get_edit_user_link( $user->ID ),
						$user->display_name,
						$user->ID,
						$user->user_email
					);
				} else {
					$html .= sprintf(
						'<span>%s</span>',
						$user->display_name
					);
				}
			}
		}

		return $html;
	}

	/**
	 * Activation column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_activation( $item ) {
		$html = '';

		$timesActivated = LicenseActivations::instance()->countBy( array(
			'license_id'     => $item->getId(),
			'deactivated_at' => null,
		) );

		if ( $item->getActivationsLimit() === null ) {
			$activationsLimit = null;
		} else {
			$activationsLimit = (int) $item->getActivationsLimit();
		}

		if ( $activationsLimit === null ) {
			return sprintf(
				'<div class="dlm-status-inline %s" title="%s"><small>%d</small> / <strong>%s</strong></div>',
				'activation dlm-status-done',
				__( 'Unlimited Activations', 'digital-license-manager' ),
				$timesActivated,
				'&infin;'
			);
		}

		if ( $timesActivated == $activationsLimit ) {
			$icon   = '<span class="dashicons dashicons-yes"></span>';
			$status = 'activation dlm-status-done';
		} else {
			$icon   = '';
			$status = 'activation dlm-status-pending';
		}

		if ( $timesActivated || $activationsLimit ) {
			$html = sprintf(
				'<div class="dlm-status-inline %s">%s <small>%d</small> / <strong>%d</strong></div>',
				$status,
				$icon,
				$timesActivated,
				$activationsLimit
			);
		}

		return $html;
	}

	/**
	 * Created column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 * @throws Exception
	 */
	public function column_date( $item ) {
		$html = '';

		if ( ! empty( $item->getCreatedAt() ) ) {
			$offsetSeconds = floatval( $this->gmtOffset ) * 60 * 60;
			$timestamp     = strtotime( $item->getCreatedAt() ) + $offsetSeconds;
			$result        = date( 'Y-m-d H:i:s', $timestamp );
			$date          = new DateTime( $result );

			$html .= sprintf(
				'<span><strong>%s, %s</strong></span>',
				$date->format( $this->dateFormat ),
				$date->format( $this->timeFormat )
			);
		}

		return $html;
	}


	/**
	 * Expires at column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 * @throws Exception
	 */
	public function column_expires_at( $item ) {

		$never = '';
		if ( empty( $item->getCreatedAt() ) ) {
			$never = __( 'In stock, not sold yet', 'digital-license-manager' );
		}

		$markup = '<p class="dlm-clear-spacing">' . DateFormatter::toHtml( $item->getExpiresAt(), [ 'expires' => true, 'never' => $never ] ) . '</p>';

		return $markup;
	}

	/**
	 * Status column.
	 *
	 * @param License $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		return LicenseStatus::statusToHtml( $item->getStatus() );
	}

	/**
	 * Default column value.
	 *
	 * @param License $item Associative array of column name and value pairs
	 * @param string $columnName Name of the current column
	 *
	 * @return string
	 */
	public function column_default( $item, $columnName ) {
		return apply_filters( 'dlm_table_licenses_column_value', $item->get( $columnName ), $columnName, $item );
	}

	/**
	 * Defines sortable columns and their sort value.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortableColumns = array(
			'id'         => array( 'id', true ),
			'order_id'   => array( 'order_id', true ),
			'product_id' => array( 'product_id', true ),
			'user_id'    => array( 'user_id', true ),
			'expires_at' => array( 'expires_at', true ),
			'status'     => array( 'status', true ),
			'date'       => array( 'created_at', true ),
			'activation' => array( 'activations_limit', true )
		);

		return apply_filters( 'dlm_table_licenses_column_sortable', $sortableColumns );
	}

	/**
	 * Defines items in the bulk action dropdown.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array();
		if ( $this->canEdit ) {
			$actions = array_merge( $actions, array(
				'disable'           => __( 'Mark as disabled', 'digital-license-manager' ),
				'mark_as_sold'      => __( 'Mark as sold', 'digital-license-manager' ),
				'mark_as_delivered' => __( 'Mark as delivered', 'digital-license-manager' ),
			) );
		}
		if ( $this->canDelete ) {
			$actions['delete'] = __( 'Delete', 'digital-license-manager' );
		}
		if ( $this->canExport ) {
			$actions = array_merge( $actions, array(
				'export_csv' => __( 'Export (CSV)', 'digital-license-manager' ),
				'export_pdf' => __( 'Export (PDF)', 'digital-license-manager' ),
			) );
		}

		return $actions;
	}

	/**
	 * Processes the currently selected action.
	 */
	private function processBulkActions() {
		$action = $this->current_action();

		switch ( $action ) {
			case 'disable':
				if ( $this->canEdit ) {
					$this->toggleStatus( LicenseStatus::DISABLED );
				}
				break;
			case 'mark_as_sold':
				if ( $this->canEdit ) {
					$this->toggleStatus( LicenseStatus::SOLD );
				}
				break;
			case 'mark_as_delivered':
				if ( $this->canEdit ) {
					$this->toggleStatus( LicenseStatus::DELIVERED );
				}
				break;
			case 'delete':
				if ( $this->canDelete ) {
					$this->handleDelete();
				}
				break;
			case 'export_pdf':
				if ( $this->canExport ) {
					$this->handleExport( 'PDF' );
				}
				break;
			case 'export_csv':
				if ( $this->canExport ) {
					$this->handleExport( 'CSV' );
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Initialization function.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		$this->processBulkActions();

		$perPage     = $this->get_items_per_page( 'dlm_licenses_per_page', 10 );
		$currentPage = $this->get_pagenum();
		$totalItems  = $this->getRecordsCount();

		$this->set_pagination_args(
			array(
				'total_items' => $totalItems,
				'per_page'    => $perPage,
				'total_pages' => ceil( $totalItems / $perPage )
			)
		);

		$this->items = $this->getRecords( $perPage, $currentPage );
	}

	/**
	 * Retrieves the records from the database.
	 *
	 * @param int $perPage Default amount of records per page
	 * @param int $pageNumber Default page number
	 *
	 * @return array
	 */
	public function getRecords( $perPage = 20, $pageNumber = 1 ) {

		$perPage    = (int) $perPage;
		$pageNumber = (int) $pageNumber;
		$offset     = ( $pageNumber - 1 ) * $perPage;
		$query      = $this->getRecordsQuery();

		return LicensesRepository::instance()->get( $query['where'], $query['orderby'], $query['order'], $offset, $perPage );
	}

	/**
	 * Retrieves the number of records in the database
	 * @return int
	 */
	private function getRecordsCount( $status = '' ) {
		$query = $this->getRecordsQuery( $status );

		return LicensesRepository::instance()->count( $query['where'] );
	}

	/**
	 * Returns records query
	 * @return array
	 */
	private function getRecordsQuery( $status = '', $count = false ) {

		$where = [];

		// Applies the view filter
		if ( $this->isViewFilterActive() ) {
			$where['status'] = (int) $_GET['status'];
		}

		// Applies the search box filter
		if ( array_key_exists( 's', $_REQUEST ) && $_REQUEST['s'] ) {
			$where['hash'] = StringHasher::license( sanitize_text_field( $_REQUEST['s'] ) );
		}

		if ( isset( $_REQUEST['order-id'] ) && is_numeric( $_REQUEST['order-id'] ) ) {
			$where['order_id'] = (int) $_REQUEST['order-id'];
		}

		// Applies the product filter
		if ( isset( $_REQUEST['product-id'] ) && is_numeric( $_REQUEST['product-id'] ) ) {
			$where['product_id'] = (int) $_REQUEST['product-id'];
		}

		// Applies the user filter
		if ( isset( $_REQUEST['user-id'] ) && is_numeric( $_REQUEST['user-id'] ) ) {
			$where['user_id'] = (int) $_REQUEST['user-id'];
		}

		return [
			'where'   => $where,
			'orderby' => empty( $_REQUEST['orderby'] ) ? 'created_at' : sanitize_text_field( $_REQUEST['orderby'] ),
			'order'   => empty( $_REQUEST['order'] ) ? 'DESC' : sanitize_text_field( $_REQUEST['order'] ),
		];

	}

	/**
	 * Set the table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'license_key' => __( 'License key', 'digital-license-manager' ),
			'order_id'    => __( 'Order', 'digital-license-manager' ),
			'product_id'  => __( 'Product', 'digital-license-manager' ),
			'user_id'     => __( 'Customer', 'digital-license-manager' ),
			'activation'  => __( 'Activations', 'digital-license-manager' ),
			'expires_at'  => __( 'Expires', 'digital-license-manager' ),
			'status'      => __( 'Status', 'digital-license-manager' ),
			'date'        => __( 'Date', 'digital-license-manager' ),
		);

		return apply_filters( 'dlm_table_licenses_column_name', $columns );
	}


	/**
	 * Changes the status of the list table entries
	 *
	 * @param $status
	 *
	 * @throws Exception
	 */
	protected function toggleStatus( $status ) {
		switch ( $status ) {
			case LicenseStatus::SOLD:
				$nonce = 'sell';
				break;
			case LicenseStatus::DELIVERED:
				$nonce = 'deliver';
				break;
			default:
				$nonce = 'disable';
				break;
		}

		$this->validateNonce( $nonce );
		$this->validateSelection();

		$licenseKeyIds = isset( $_REQUEST['id'] ) ? array_map( 'intval', (array) $_REQUEST['id'] ) : array();
		$count         = 0;

		foreach ( $licenseKeyIds as $licenseKeyId ) {
			/** @var License $license */
			$license = LicensesRepository::instance()->find( $licenseKeyId );

			LicensesRepository::instance()->update( $licenseKeyId, array( 'status' => $status ) );

			// The license has a product assigned to it, perhaps a stock update is necessary
			if ( $license->getProductId() !== null ) {
				// License was active, but no longer is
				if ( $license->getStatus() === LicenseStatus::ACTIVE && $status !== LicenseStatus::ACTIVE ) {
					// Update the stock
					Stock::syncrhonizeProductStock( $license->getProductId() );
				}

				// License was not active, but is now
				if ( $license->getStatus() !== LicenseStatus::ACTIVE && $status === LicenseStatus::ACTIVE ) {
					// Update the stock
					Stock::syncrhonizeProductStock( $license->getProductId() );
				}
			}

			$count ++;
		}

		// Set the admin notice, redirect and exit
		NoticeFlasher::success( sprintf( esc_html__( '%d license(s) updated successfully.', 'digital-license-manager' ), $count ) );
		HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
	}

	/**
	 * Removes the records permanently from the database.
	 * @throws Exception
	 */
	private function handleDelete() {

		$this->validateNonce( 'delete' );
		$this->validateSelection();

		$licenseKeyIds = isset( $_REQUEST['id'] ) ? array_map( 'intval', (array) $_REQUEST['id'] ) : array();

		$count = 0;
		foreach ( $licenseKeyIds as $licenseKeyId ) {
			/** @var License $license */
			$license = LicensesRepository::instance()->find( $licenseKeyId );
			if ( ! $license ) {
				continue;
			}
			$result = LicensesRepository::instance()->delete( (array) $licenseKeyId );
			if ( $result ) {
				// Update the stock
				if ( $license->getProductId() !== null && $license->getStatus() === LicenseStatus::ACTIVE ) {
					Stock::syncrhonizeProductStock( $license->getProductId() );
				}

				$count += $result;
			}
		}

		$message = sprintf( esc_html__( '%d license(s) permanently deleted.', 'digital-license-manager' ), $count );

		// Set the admin notice
		NoticeFlasher::success( $message );

		// Redirect and exit
		HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
	}

	/**
	 * Initiates a file download of the exported licenses (PDF or CSV).
	 *
	 * @param string $type
	 *
	 * @throws Exception
	 */
	private function handleExport( $type ) {
		$this->validateSelection();

		if ( $type === 'PDF' ) {
			$this->validateNonce( 'export_pdf' );
			do_action( 'dlm_export_license_keys_pdf', (array) $_REQUEST['id'] );
		}

		if ( $type === 'CSV' ) {
			$this->validateNonce( 'export_csv' );
			do_action( 'dlm_export_license_keys_csv', (array) $_REQUEST['id'] );
		}
	}

	/**
	 * Checks if there are currently any license view filters active.
	 *
	 * @return bool
	 */
	private function isViewFilterActive() {
		if ( array_key_exists( 'status', $_GET )
		     && in_array( $_GET['status'], LicenseStatus::$status )
		) {
			return true;
		}

		return false;
	}
}
