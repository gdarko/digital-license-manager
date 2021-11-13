<?php

namespace IdeoLogix\DigitalLicenseManager\ListTables;

use IdeoLogix\DigitalLicenseManager\Abstracts\ListTable;
use IdeoLogix\DigitalLicenseManager\Utils\Crypto;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Stock;
use IdeoLogix\DigitalLicenseManager\Utils\Hash;
use DateTime;
use Exception;
use IdeoLogix\DigitalLicenseManager\Utils\Moment;
use IdeoLogix\DigitalLicenseManager\Utils\Notice as AdminNotice;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation as LicenseActivationResourceRepository;
use IdeoLogix\DigitalLicenseManager\Settings;
use WC_Product;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Class Licenses
 * @package IdeoLogix\DigitalLicenseManager\ListTables
 */
class Licenses extends ListTable {
	/**
	 * Path to spinner image.
	 */
	const SPINNER_URL = '/wp-admin/images/loading.gif';

	/**
	 * LicensesList constructor.
	 */
	public function __construct() {
		global $wpdb;

		parent::__construct(
			array(
				'singular' => __( 'Licenses', 'digital-license-manager' ),
				'plural'   => __( 'Licenses', 'digital-license-manager' ),
				'ajax'     => false
			)
		);

		$this->slug      = PageSlug::LICENSES;
		$this->table     = $wpdb->prefix . DatabaseTable::LICENSES;
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
			LicenseResourceRepository::instance()->count()
		);

		// Sold link
		$class               = $current == LicenseStatus::SOLD ? ' class="current"' : '';
		$soldUrl             = esc_url( add_query_arg( 'status', LicenseStatus::SOLD ) );
		$statusLinks['sold'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$soldUrl,
			$class,
			__( 'Sold', 'digital-license-manager' ),
			LicenseResourceRepository::instance()->countBy( array( 'status' => LicenseStatus::SOLD ) )
		);

		// Delivered link
		$class                    = $current == LicenseStatus::DELIVERED ? ' class="current"' : '';
		$deliveredUrl             = esc_url( add_query_arg( 'status', LicenseStatus::DELIVERED ) );
		$statusLinks['delivered'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$deliveredUrl,
			$class,
			__( 'Delivered', 'digital-license-manager' ),
			LicenseResourceRepository::instance()->countBy( array( 'status' => LicenseStatus::DELIVERED ) )
		);

		// Active link
		$class                 = $current == LicenseStatus::ACTIVE ? ' class="current"' : '';
		$activeUrl             = esc_url( add_query_arg( 'status', LicenseStatus::ACTIVE ) );
		$statusLinks['active'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$activeUrl,
			$class,
			__( 'Active', 'digital-license-manager' ),
			LicenseResourceRepository::instance()->countBy( array( 'status' => LicenseStatus::ACTIVE ) )
		);

		// Inactive link
		$class                   = $current == LicenseStatus::INACTIVE ? ' class="current"' : '';
		$inactiveUrl             = esc_url( add_query_arg( 'status', LicenseStatus::INACTIVE ) );
		$statusLinks['inactive'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$inactiveUrl,
			$class,
			__( 'Inactive', 'digital-license-manager' ),
			LicenseResourceRepository::instance()->countBy( array( 'status' => LicenseStatus::INACTIVE ) )
		);

		// Disabled link
		$class                   = $current == LicenseStatus::DISABLED ? ' class="current"' : '';
		$disabledUrl             = esc_url( add_query_arg( 'status', LicenseStatus::DISABLED ) );
		$statusLinks['disabled'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$disabledUrl,
			$class,
			__( 'Disabled', 'digital-license-manager' ),
			LicenseResourceRepository::instance()->countBy( array( 'status' => LicenseStatus::DISABLED ) )
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
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * License key column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_license_key( $item ) {
		if ( Settings::get( 'hide_license_keys' ) ) {
			$title = '<code class="dlm-placeholder empty"></code>';
			$title .= sprintf(
				'<img class="dlm-spinner" data-id="%d" src="%s">',
				$item['id'],
				self::SPINNER_URL
			);
		} else {

			$decrypted = Crypto::decrypt( $item['license_key'] );
			if ( is_wp_error( $decrypted ) ) {
				$decrypted = 'ERROR';
			}

			$title = sprintf(
				'<code class="dlm-placeholder">%s</code>',
				$decrypted
			);
			$title .= sprintf(
				'<img class="dlm-spinner" data-id="%d" src="%s">',
				$item['id'],
				self::SPINNER_URL
			);
		}

		// ID
		$actions['id'] = sprintf( __( 'ID: %d', 'digital-license-manager' ), (int) $item['id'] );

		// Edit
		if ( $this->canEdit ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url(
					wp_nonce_url(
						sprintf(
							'admin.php?page=%s&action=edit&id=%d',
							$this->slug,
							(int) $item['id']
						),
						'dlm_edit_license_key'
					)
				),
				__( 'Edit', 'digital-license-manager' )
			);
		}

		// Hide/Show
		$actions['show'] = sprintf(
			'<a class="dlm-license-key-show" data-id="%d">%s</a>',
			$item['id'],
			__( 'Show', 'digital-license-manager' )
		);
		$actions['hide'] = sprintf(
			'<a class="dlm-license-key-hide" data-id="%d">%s</a>',
			$item['id'],
			__( 'Hide', 'digital-license-manager' )
		);

		// Delete
		if ( $this->canDelete ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="dlm-confirm-dialog">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=delete&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item['id'],
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
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_order_id( $item ) {
		$html = '';

		$order_id = ! empty( $item['order_id'] ) ? (int) $item['order_id'] : 0;

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
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_product_id( $item ) {
		$html = '';

		$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : '';

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
						get_edit_post_link( $item['product_id'] ),
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
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_user_id( $item ) {
		$html = '';

		if ( $item['user_id'] !== null ) {
			/** @var WP_User $user */
			$user = get_userdata( $item['user_id'] );

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
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_activation( $item ) {
		$html = '';

		$timesActivated = LicenseActivationResourceRepository::instance()->countBy( array(
			'license_id'     => $item['id'],
			'deactivated_at' => 'IS NULL',
		) );

		if ( $item['activations_limit'] === null ) {
			$activationsLimit = null;
		} else {
			$activationsLimit = (int) $item['activations_limit'];
		}

		if ( $activationsLimit === null ) {
			return sprintf(
				'<div class="dlm-status-inline %s"><small>%d</small> / <strong>%s</strong></div>',
				'activation done',
				$timesActivated,
				'&infin;'
			);
		}

		if ( $timesActivated == $activationsLimit ) {
			$icon   = '<span class="dashicons dashicons-yes"></span>';
			$status = 'activation done';
		} else {
			$icon   = '';
			$status = 'activation pending';
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
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 * @throws Exception
	 */
	public function column_created( $item ) {
		$html = '';

		if ( $item['created_at'] ) {
			$offsetSeconds = floatval( $this->gmtOffset ) * 60 * 60;
			$timestamp     = strtotime( $item['created_at'] ) + $offsetSeconds;
			$result        = date( 'Y-m-d H:i:s', $timestamp );
			$date          = new DateTime( $result );

			$html .= sprintf(
				'<span><strong>%s, %s</strong></span>',
				$date->format( $this->dateFormat ),
				$date->format( $this->timeFormat )
			);
		}

		if ( $item['created_by'] ) {
			/** @var WP_User $user */
			$user = get_user_by( 'id', $item['created_by'] );

			if ( $user instanceof WP_User ) {
				if ( current_user_can( 'edit_users' ) ) {
					$html .= sprintf(
						'<br>%s <a href="%s">%s</a>',
						__( 'by', 'digital-license-manager' ),
						get_edit_user_link( $user->ID ),
						$user->display_name
					);
				} else {
					$html .= sprintf(
						'<br><span>%s %s</span>',
						__( 'by', 'digital-license-manager' ),
						$user->display_name
					);
				}
			}
		}

		return $html;
	}

	/**
	 * Updated column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 * @throws Exception
	 */
	public function column_updated( $item ) {

		$html = '';

		if ( ! empty( $item['updated_at'] ) ) {
			$offsetSeconds = floatval( $this->gmtOffset ) * 60 * 60;
			$timestamp     = strtotime( $item['updated_at'] ) + $offsetSeconds;
			$result        = date( 'Y-m-d H:i:s', $timestamp );
			$date          = new DateTime( $result );

			$html .= sprintf(
				'<span><strong>%s, %s</strong></span>',
				$date->format( $this->dateFormat ),
				$date->format( $this->timeFormat )
			);
		}

		if ( ! empty( $item['updated_by'] ) ) {
			/** @var WP_User $user */
			$user = get_user_by( 'id', $item['updated_by'] );

			if ( $user instanceof WP_User ) {
				if ( current_user_can( 'edit_users' ) ) {
					$html .= sprintf(
						'<br>%s <a href="%s">%s</a>',
						__( 'by', 'digital-license-manager' ),
						get_edit_user_link( $user->ID ),
						$user->display_name
					);
				} else {
					$html .= sprintf(
						'<br><span>%s %s</span>',
						__( 'by', 'digital-license-manager' ),
						$user->display_name
					);
				}
			}
		}

		return $html;
	}

	/**
	 * Expires at column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 * @throws Exception
	 */
	public function column_expires_at( $item ) {

		$never = '';
		if ( empty( $item['order_id'] ) ) {
			$never = __( 'In stock, not sold yet', 'digital-license-manager' );
		}

		$markup = '<p class="dlm-text-center dlm-clear-spacing">' . Moment::toHtml( $item['expires_at'], true, false, $never ) . '</p>';

		if ( $item['valid_for'] ) {
			$markup .= sprintf(
				'<p class="dlm-text-center dlm-clear-spacing"><strong>%d</strong> %s - <small>%s</small></p>',
				(int) $item['valid_for'],
				__( 'day(s)', 'digital-license-manager' ),
				__( 'After purchase', 'digital-license-manager' )
			);
		}

		return $markup;
	}

	/**
	 * Status column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		return LicenseStatus::statusToHtml( $item['status'] );
	}

	/**
	 * Default column value.
	 *
	 * @param array $item Associative array of column name and value pairs
	 * @param string $columnName Name of the current column
	 *
	 * @return string
	 */
	public function column_default( $item, $columnName ) {
		$item = apply_filters( 'dlm_table_licenses_column_value', $item, $columnName );

		return $item[ $columnName ];
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
			'created'    => array( 'created_at', true ),
			'updated'    => array( 'updated_at', true ),
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
	private function getRecords( $perPage = 20, $pageNumber = 1 ) {
		global $wpdb;

		$sql = "SELECT * FROM {$this->table} WHERE 1 = 1";

		// Applies the view filter
		if ( $this->isViewFilterActive() ) {
			$sql .= $wpdb->prepare( ' AND status = %d', (int) $_GET['status'] );
		}

		// Applies the search box filter
		if ( array_key_exists( 's', $_REQUEST ) && $_REQUEST['s'] ) {
			$sql .= $wpdb->prepare(
				' AND hash = %s',
				Hash::license( sanitize_text_field( $_REQUEST['s'] ) )
			);
		}

		// Applies the order filter
		if ( isset( $_REQUEST['order-id'] ) && is_numeric( $_REQUEST['order-id'] ) ) {
			$sql .= $wpdb->prepare( ' AND order_id = %d', (int) $_REQUEST['order-id'] );
		}

		// Applies the product filter
		if ( isset( $_REQUEST['product-id'] ) && is_numeric( $_REQUEST['product-id'] ) ) {
			$sql .= $wpdb->prepare( ' AND product_id = %d', (int) $_REQUEST['product-id'] );
		}

		// Applies the user filter
		if ( isset( $_REQUEST['user-id'] ) && is_numeric( $_REQUEST['user-id'] ) ) {
			$sql .= $wpdb->prepare( ' AND user_id = %d', (int) $_REQUEST['user-id'] );
		}

		$sql .= ' ORDER BY ' . ( empty( $_REQUEST['orderby'] ) ? 'id' : esc_sql( $_REQUEST['orderby'] ) );
		$sql .= ' ' . ( empty( $_REQUEST['order'] ) ? 'DESC' : esc_sql( $_REQUEST['order'] ) );
		$sql .= " LIMIT {$perPage}";
		$sql .= ' OFFSET ' . ( $pageNumber - 1 ) * $perPage;

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Retrieves the number of records in the database
	 * @return int
	 */
	private function getRecordsCount() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1 = 1";

		if ( $this->isViewFilterActive() ) {
			$sql .= $wpdb->prepare( ' AND status = %d', (int) $_GET['status'] );
		}

		if ( isset( $_REQUEST['order-id'] ) ) {
			$sql .= $wpdb->prepare( ' AND order_id = %d', (int) $_REQUEST['order-id'] );
		}

		if ( array_key_exists( 's', $_REQUEST ) && $_REQUEST['s'] ) {
			$sql .= $wpdb->prepare(
				' AND hash = %s',
				Hash::license( sanitize_text_field( $_REQUEST['s'] ) )
			);
		}

		return $wpdb->get_var( $sql );
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
			'created'     => __( 'Created', 'digital-license-manager' ),
			'updated'     => __( 'Updated', 'digital-license-manager' )
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

		$this->verifyNonce( $nonce );
		$this->verifySelection();

		$licenseKeyIds = isset( $_REQUEST['id'] ) ? array_map( 'intval', (array) $_REQUEST['id'] ) : array();
		$count         = 0;

		foreach ( $licenseKeyIds as $licenseKeyId ) {
			/** @var LicenseResourceModel $license */
			$license = LicenseResourceRepository::instance()->find( $licenseKeyId );

			LicenseResourceRepository::instance()->update( $licenseKeyId, array( 'status' => $status ) );

			// The license has a product assigned to it, perhaps a stock update is necessary
			if ( $license->getProductId() !== null ) {
				// License was active, but no longer is
				if ( $license->getStatus() === LicenseStatus::ACTIVE && $status !== LicenseStatus::ACTIVE ) {
					// Update the stock
					Stock::decrease( $license->getProductId() );
				}

				// License was not active, but is now
				if ( $license->getStatus() !== LicenseStatus::ACTIVE && $status === LicenseStatus::ACTIVE ) {
					// Update the stock
					Stock::increase( $license->getProductId() );
				}
			}

			$count ++;
		}

		// Set the admin notice, redirect and exit
		AdminNotice::success( sprintf( esc_html__( '%d license(s) updated successfully.', 'digital-license-manager' ), $count ) );
		wp_redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
		exit();
	}

	/**
	 * Removes the records permanently from the database.
	 * @throws Exception
	 */
	private function handleDelete() {

		$this->verifyNonce( 'delete' );
		$this->verifySelection();

		$licenseKeyIds = isset( $_REQUEST['id'] ) ? array_map( 'intval', (array) $_REQUEST['id'] ) : array();


		foreach ( $licenseKeyIds as $licenseKeyId ) {
			/** @var LicenseResourceModel $license */
			$license = LicenseResourceRepository::instance()->find( $licenseKeyId );
			if ( ! $license ) {
				continue;
			}
			$result = LicenseResourceRepository::instance()->delete( (array) $licenseKeyId );
			if ( $result ) {
				// Update the stock
				if ( $license->getProductId() !== null && $license->getStatus() === LicenseStatus::ACTIVE ) {
					Stock::decrease( $license->getProductId() );
				}

				$count += $result;
			}
		}

		$message = sprintf( esc_html__( '%d license(s) permanently deleted.', 'digital-license-manager' ), $count );

		// Set the admin notice
		AdminNotice::success( $message );

		// Redirect and exit
		wp_redirect(
			admin_url(
				sprintf( 'admin.php?page=%s', $this->slug )
			)
		);
	}

	/**
	 * Initiates a file download of the exported licenses (PDF or CSV).
	 *
	 * @param string $type
	 *
	 * @throws Exception
	 */
	private function handleExport( $type ) {
		$this->verifySelection();

		if ( $type === 'PDF' ) {
			$this->verifyNonce( 'export_pdf' );
			do_action( 'dlm_export_license_keys_pdf', (array) $_REQUEST['id'] );
		}

		if ( $type === 'CSV' ) {
			$this->verifyNonce( 'export_csv' );
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

	/**
	 * Displays the search box.
	 *
	 * @param string $text
	 * @param string $inputId
	 */
	public function search_box( $text, $inputId ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$inputId     = $inputId . '-search-input';
		$searchQuery = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

		echo '<p class="search-box">';
		echo '<label class="screen-reader-text" for="' . esc_attr( $inputId ) . '">' . esc_html( $text ) . ':</label>';
		echo '<input type="search" id="' . esc_attr( $inputId ) . '" name="s" value="' . esc_attr( $searchQuery ) . '" />';

		submit_button(
			$text, '', '', false,
			array(
				'id' => 'search-submit',
			)
		);

		echo '</p>';
	}
}
