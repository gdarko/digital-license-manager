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
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher as AdminNotice;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activations
 * @package IdeoLogix\DigitalLicenseManager\ListTables
 */
class Activations extends AbstractListTable {

	/**
	 *  Whether user can activate records
	 * @var bool
	 */
	protected $canActivate;

	/**
	 *  Whether user can deactivate records
	 * @var bool
	 */
	protected $canDeactivate;

	/**
	 * ActivationsList constructor.
	 */
	public function __construct() {
		global $wpdb;

		parent::__construct(
			array(
				'singular' => __( 'Activation', 'digital-license-manager' ),
				'plural'   => __( 'Activations', 'digital-license-manager' ),
				'ajax'     => false
			)
		);

		$this->slug       = PageSlug::ACTIVATIONS;
		$this->table      = $wpdb->prefix . DatabaseTable::LICENSE_ACTIVATIONS;
		$this->dateFormat = get_option( 'date_format' );
		$this->timeFormat = get_option( 'time_format' );
		$this->gmtOffset  = get_option( 'gmt_offset' );

		$this->canActivate   = current_user_can( 'dlm_activate_licenses' );
		$this->canDeactivate = current_user_can( 'dlm_deactivate_licenses' );
		$this->canDelete     = current_user_can( 'dlm_delete_activations' );
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
		global $wpdb;

		$perPage    = (int) $perPage;
		$pageNumber = (int) $pageNumber;

		$sql = $this->getRecordsQuery();
		$sql .= " LIMIT {$perPage}";
		$sql .= ' OFFSET ' . ( $pageNumber - 1 ) * $perPage;

		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results;
	}

	/**
	 * Returns records query
	 * @return string
	 */
	private function getRecordsQuery( $status = '', $count = false ) {

		global $wpdb;
		$tblLicenses = $wpdb->prefix . esc_sql( DatabaseTable::LICENSES );

		$what = $count ? "COUNT(*)" : " {$this->table}.*";
		$sql  = esc_sql( "SELECT {$what} FROM {$this->table} INNER JOIN {$tblLicenses} ON {$tblLicenses}.id={$this->table}.license_id WHERE 1 = 1" );

		// Applies the view filter
		if ( ! empty( $status ) || $this->isViewFilterActive() ) {

			if ( empty( $status ) ) {
				$status = sanitize_text_field( $_GET['status'] );
			}

			if ( 'inactive' === $status ) {
				$sql .= esc_sql( ' AND ' . $this->table . '.deactivated_at IS NOT NULL' );
			} else {
				$sql .= esc_sql( ' AND ' . $this->table . '.deactivated_at IS NULL' );
			}

		}

		// Applies the search box filter
		if ( array_key_exists( 's', $_REQUEST ) && ! empty( $_REQUEST['s'] ) ) {
			$sql .= $wpdb->prepare(
				' AND ( ' . $tblLicenses . '.hash=%s OR ' . $this->table . '.label LIKE %s )',
				StringHasher::license( sanitize_text_field( $_REQUEST['s'] ) ),
				'%' . $wpdb->esc_like( sanitize_text_field( $_REQUEST['s'] ) ) . '%'
			);
		}

		// Applies the order filter
		if ( isset( $_REQUEST['license-id'] ) && is_numeric( $_REQUEST['license-id'] ) ) {
			$sql .= $wpdb->prepare( ' AND ' . $tblLicenses . '.id=%d', (int) $_REQUEST['license-id'] );
		}

		// Applies the order filter
		if ( isset( $_REQUEST['license-source'] ) && is_numeric( $_REQUEST['license-source'] ) ) {
			$sql .= $wpdb->prepare( ' AND ' . $this->table . '.source=%d', (int) $_REQUEST['license-source'] );
		}

		$sql .= ' ORDER BY ' . $this->table . '.' . ( empty( $_REQUEST['orderby'] ) ? 'id' : esc_sql( $_REQUEST['orderby'] ) );
		$sql .= ' ' . ( empty( $_REQUEST['order'] ) ? 'DESC' : esc_sql( $_REQUEST['order'] ) );

		return $sql;
	}

	/**
	 * Retrieves the number of records in the database
	 * @return int
	 */
	private function getRecordsCount( $status = '' ) {
		global $wpdb;
		$sql = $this->getRecordsQuery( $status, true );

		return $wpdb->get_var( $sql );
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%s" />', $item['id'] );
	}

	/**
	 * Token column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_token( $item ) {
		$html = '';
		if ( $item['token'] ) {
			$html = sprintf( '<span title="%s">%s</span>', __( 'Unique activation token', 'digital-license-manager' ), $item['token'] );
		}

		return $html;
	}

	/**
	 * Name column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_label( $item ) {
		$actions = array();
		if ( empty( $item['label'] ) ) {
			$title = __( 'Untitled', 'digital-license-manager' );
		} else {
			$title = esc_attr( $item['label'] );
		}
		$title         = '<strong>' . $title . '</strong>';
		$actions['id'] = sprintf( __( 'ID: %d', 'digital-license-manager' ), (int) $item['id'] );

		if ( ! empty( $item['deactivated_at'] ) && $this->canActivate ) {
			$actions['activate'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=activate&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item['id'],
						wp_create_nonce( 'activate' )
					)
				),
				__( 'Activate', 'digital-license-manager' )
			);
		} else if ( empty( $item['deactivated_at'] ) && $this->canDeactivate ) {
			$actions['deactivate'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=deactivate&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item['id'],
						wp_create_nonce( 'deactivate' )
					)
				),
				__( 'Deactivate', 'digital-license-manager' )
			);
		}

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
	 * License ID column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_license_id( $item ) {
		$html = '';

		if ( $item['license_id'] ) {
			$html = sprintf(
				'<a href="%s" target="_blank">#%s</a>',
				esc_url( admin_url( sprintf( 'admin.php?page=%s&action=edit&id=%s', PageSlug::LICENSES, $item['license_id'] ) ) ),
				$item['license_id']
			);
		}

		return $html;
	}

	/**
	 * IP Address column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_ip_address( $item ) {

		$html = '';
		if ( $item['ip_address'] ) {
			$html = esc_attr( $item['ip_address'] );
		}

		return $html;
	}

	/**
	 * IP Address column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_source( $item ) {

		$html = __( 'Other', 'digital-license-manager' );
		if ( $item['source'] ) {
			$html = ActivationSource::format( (int) $item['source'] );
		}

		return $html;
	}

	/**
	 * IP Address column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_status( $item ) {

		$html = '';
		if ( ! empty( $item['deactivated_at'] ) ) {
			$html = sprintf(
				'<div class="dlm-status dlm-status-inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
				__( 'Inactive', 'digital-license-manager' )
			);
		} else {
			$html = sprintf(
				'<div class="dlm-status dlm-status-delivered"><span class="dashicons dashicons-marker"></span> %s</div>',
				__( 'Active', 'digital-license-manager' )
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

		return $html;
	}


	/**
	 * Default column value.
	 *
	 * @param array $item Associative array of column name and value pairs
	 * @param string $column_name Name of the current column
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_attr( $item[ $column_name ] ) : '';
	}

	/**
	 * Set the table columns.
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'label'      => __( 'Label', 'digital-license-manager' ),
			'license_id' => __( 'License', 'digital-license-manager' ),
			'token'      => __( 'Token', 'digital-license-manager' ),
			'source'     => __( 'Source', 'digital-license-manager' ),
			'ip_address' => __( 'IP Address', 'digital-license-manager' ),
			'status'     => __( 'Status', 'digital-license-manager' ),
			'created'    => __( 'Created', 'digital-license-manager' )
		);
	}

	/**
	 * Defines sortable columns and their sort value.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'label' => array( 'name', true ),
		);
	}

	/**
	 * Processes the currently selected action.
	 */
	private function processBulkActions() {
		$action = $this->current_action();

		switch ( $action ) {
			case 'activate':
				if ( $this->canActivate ) {
					$this->toggleStatus( 'activate' );
				}
				break;
			case 'deactivate':
				if ( $this->canDeactivate ) {
					$this->toggleStatus( 'deactivate' );
				}
				break;
			case 'delete':
				if ( $this->canDelete ) {
					$this->handleDelete();
				}
				break;
			default:
				break;
		}
	}


	/**
	 * Changes the license key status
	 *
	 * @param $status
	 *
	 * @throws Exception
	 */
	protected function toggleStatus( $status ) {

		switch ( $status ) {
			case 'activate':
			case 'deactivate':
				$nonce = $status;
				break;
			default:
				$nonce = null;
				break;
		}

		$this->verifyNonce( $nonce );
		$this->verifySelection();

		$recordIds = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
		if ( ! empty( $recordIds ) ) {
			$recordIds = array_map( 'intval', $recordIds );
		}
		$count = 0;

		foreach ( $recordIds as $recordId ) {
			$new_value = 'activate' === $status ? null : date( 'Y-m-d H:i:s' );
			LicenseActivations::instance()->update( $recordId, array( 'deactivated_at' => $new_value ) );
			$count ++;
		}

		AdminNotice::success( sprintf( esc_html__( '%d %s(s) updated successfully.', 'digital-license-manager' ), $count, strtolower( $this->_args['plural'] ) ) );
		wp_redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
		exit();
	}

	/**
	 * Checks if there are currently any license view filters active.
	 *
	 * @return bool
	 */
	private function isViewFilterActive() {
		if ( array_key_exists( 'status', $_GET )
		     && in_array( $_GET['status'], array( 'active', 'inactive' ) )
		) {
			return true;
		}

		return false;
	}


	/**
	 * Creates the different status filter links at the top of the table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$statusLinks = array();
		$current     = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all';

		$total_active   = $this->getRecordsCount( 'active' );
		$total_inactive = $this->getRecordsCount( 'inactive' );

		// All link
		$class              = $current == 'all' ? ' class="current"' : '';
		$allUrl             = remove_query_arg( 'status' );
		$statusLinks['all'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$allUrl,
			$class,
			__( 'All', 'digital-license-manager' ),
			$total_active + $total_inactive
		);

		// Active link
		$class                 = $current == 'active' ? ' class="current"' : '';
		$activeUrl             = esc_url( add_query_arg( 'status', 'active' ) );
		$statusLinks['active'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$activeUrl,
			$class,
			__( 'Active', 'digital-license-manager' ),
			$total_active
		);

		// Inactive link
		$class                   = $current == 'inactive' ? ' class="current"' : '';
		$inactiveUrl             = esc_url( add_query_arg( 'status', 'inactive' ) );
		$statusLinks['inactive'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$inactiveUrl,
			$class,
			__( 'Inactive', 'digital-license-manager' ),
			$total_inactive
		);

		return $statusLinks;
	}

	/**
	 * Removes the records permanently from the database.
	 * @throws Exception
	 */
	private function handleDelete() {

		$this->verifyNonce( 'delete' );
		$this->verifySelection();

		$recordIds = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
		if ( ! empty( $recordIds ) ) {
			$recordIds = array_map( 'intval', $recordIds );
		}
		$count = 0;

		foreach ( $recordIds as $recordId ) {
			$result = LicenseActivations::instance()->delete( $recordId );
			if ( $result ) {
				$count ++;
			}
		}

		$message = sprintf( esc_html__( '%d activation record(s) permanently deleted.', 'digital-license-manager' ), $count );

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
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			echo '<div class="alignleft actions">';
			$this->licenseDropdown();
			$this->sourceDropdown();
			submit_button( __( 'Filter', 'digital-license-manager' ), '', 'filter-action', false );
			echo '</div>';
		}
	}

	/**
	 * Displays the order dropdown filter.
	 */
	public function licenseDropdown() {

		$selected = isset( $_REQUEST['license-id'] ) ? (int) $_REQUEST['license-id'] : '';
		?>
        <label for="filter-by-license-id" class="screen-reader-text">
            <span><?php _e( 'Filter by license', 'digital-license-manager' ); ?></span>
        </label><select name="license-id" id="filter-by-license-id">
            <option></option>
			<?php if ( $selected ): ?>
                <option selected
                        value="<?php echo $selected; ?>"><?php echo sprintf( '#%d', esc_attr( $selected ) ); ?></option>
			<?php endif; ?>
        </select>
		<?php
	}

	/**
	 * Displays the order dropdown filter.
	 */
	public function sourceDropdown() {

		$selected = isset( $_REQUEST['license-source'] ) ? (int) $_REQUEST['license-source'] : - 1;
		?>
        <label for="filter-by-source" class="screen-reader-text">
            <span><?php _e( 'Filter by source', 'digital-license-manager' ); ?></span>
        </label>

        <select name="license-source" id="filter-by-source">
            <option></option>
			<?php foreach ( ActivationSource::all() as $key => $name ): ?>
                <option value="<?php echo $key; ?>" <?php selected( $selected, $key ); ?>><?php echo esc_attr( $name ); ?></option>
			<?php endforeach; ?>
        </select>
		<?php
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

	/**
	 * Defines items in the bulk action dropdown.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array();
		if ( $this->canActivate ) {
			$actions['activate'] = __( 'Activate', 'digital-license-manager' );
		}
		if ( $this->canDeactivate ) {
			$actions['deactivate'] = __( 'Deactivate', 'digital-license-manager' );
		}
		if ( $this->canDelete ) {
			$actions['delete'] = __( 'Delete', 'digital-license-manager' );
		}

		return $actions;
	}


	/**
	 * Initialization function.
	 *
	 * @throws Exception
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		$this->processBulkActions();

		$perPage     = $this->get_items_per_page( 'activations_per_page', 10 );
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

}
