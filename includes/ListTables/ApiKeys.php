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

use Exception;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractListTable;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey as ApiKeyResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher as AdminNotice;

defined( 'ABSPATH' ) || exit;

/**
 * Class APIKeys
 * @package IdeoLogix\DigitalLicenseManager\ListTables
 */
class ApiKeys extends AbstractListTable {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Key', 'digital-license-manager' ),
				'plural'   => __( 'Keys', 'digital-license-manager' ),
				'ajax'     => false
			)
		);

		$this->slug      = PageSlug::SETTINGS;
		$this->table     = DatabaseTable::API_KEYS;
		$this->canEdit   = current_user_can( 'dlm_edit_api_keys' );
		$this->canDelete = current_user_can( 'dlm_delete_api_keys' );
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'title'         => __( 'Description', 'digital-license-manager' ),
			'truncated_key' => __( 'Consumer key ending in', 'digital-license-manager' ),
			'user'          => __( 'User', 'digital-license-manager' ),
			'permissions'   => __( 'Permissions', 'digital-license-manager' ),
			'last_access'   => __( 'Last access', 'digital-license-manager' ),
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%1$s" />', $item['id'] );
	}

	/**
	 * Title column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_title( $item ) {
		$keyId  = (int) $item['id'];
		$url    = admin_url( sprintf( 'admin.php?page=%s&tab=rest_api&edit_key=%d', $this->slug, $keyId ) );
		$userId = (int) $item['user_id'];

		// Check if current user can edit other users or if it's the same user.
		$output = '<strong>';

		if ( $this->canEdit ) {
			$output .= '<a href="' . esc_url( $url ) . '" class="row-title">';
		}

		if ( empty( $item['description'] ) ) {
			$output .= esc_html__( 'API key', 'digital-license-manager' );
		} else {
			$output .= esc_html( $item['description'] );
		}

		if ( $this->canEdit ) {
			$output .= '</a>';
		}

		$output .= '</strong>';

		// Get actions.
		$actions = array(
			'id' => sprintf( __( 'ID: %d', 'digital-license-manager' ), $keyId ),
		);

		if ( $this->canEdit ) {
			$actions['edit'] = '<a href="' . esc_url( $url ) . '">' . __( 'Edit', 'digital-license-manager' ) . '</a>';
		}

		if ( $this->canDelete ) {
			$actions['trash'] = '<a class="submitdelete dlm-confirm-dialog" aria-label="' . esc_attr__( 'Delete', 'digital-license-manager' ) . '" href="' . esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $keyId,
							),
							admin_url( sprintf( 'admin.php?page=%s&tab=rest_api', $this->slug ) )
						),
						'delete'
					)
				) . '">' . esc_html__( 'Delete', 'digital-license-manager' ) . '</a>';
		}

		$rowActions = array();

		foreach ( $actions as $action => $link ) {
			$rowActions[] = '<span class="' . esc_attr( $action ) . '">' . $link . '</span>';
		}

		$output .= '<div class="row-actions">' . implode( ' | ', $rowActions ) . '</div>';

		return $output;
	}

	/**
	 * Truncated consumer key column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_truncated_key( $item ) {
		return '<code>&hellip;' . esc_html( $item['truncated_key'] ) . '</code>';
	}

	/**
	 * User column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_user( $item ) {
		$user = get_user_by( 'id', $item['user_id'] );

		if ( ! $user ) {
			return '';
		}

		if ( current_user_can( 'edit_user', $user->ID ) ) {
			return '<a href="' . esc_url( add_query_arg( array( 'user_id' => $user->ID ), admin_url( 'user-edit.php' ) ) ) . '">' . esc_html( $user->display_name ) . '</a>';
		}

		return esc_html( $user->display_name );
	}

	/**
	 * Permissions column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_permissions( $item ) {
		$permissionKey = $item['permissions'];
		$permissions   = array(
			'read'       => __( 'Read', 'digital-license-manager' ),
			'write'      => __( 'Write', 'digital-license-manager' ),
			'read_write' => __( 'Read/Write', 'digital-license-manager' ),
		);

		if ( isset( $permissions[ $permissionKey ] ) ) {
			return esc_html( $permissions[ $permissionKey ] );
		}

		return '';
	}

	/**
	 * Last access column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_last_access( $item ) {
		$dateFormat = get_option( 'date_format' );
		$timeFormat = get_option( 'time_format' );
		if ( ! empty( $item['last_access'] ) ) {
			$date = sprintf(
				__( '%1$s at %2$s', 'digital-license-manager' ),
				date_i18n( $dateFormat, strtotime( $item['last_access'] ) ),
				date_i18n( $timeFormat, strtotime( $item['last_access'] ) )
			);

			return apply_filters( 'woocommerce_api_key_last_access_datetime', $date, $item['last_access'] );
		}

		return __( 'Unknown', 'digital-license-manager' );
	}

	/**
	 * Defines items in the bulk action dropdown.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {

		$actions = array();

		if ( $this->canDelete ) {
			$actions['delete'] = __( 'Delete', 'digital-license-manager' );
		}

		return $actions;
	}

	/**
	 * Handle bulk action requests.
	 *
	 * @throws Exception
	 */
	private function processBulkActions() {
		if ( ! $action = $this->current_action() ) {
			return;
		}

		switch ( $action ) {
			case 'delete':
				$this->verifyNonce( 'delete' );
				$this->verifySelection();
				if ( $this->canDelete ) {
					$this->handleDelete();
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Search box.
	 *
	 * @param string $text Button text
	 * @param string $inputId Input ID
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
	 * Prepare table list items.
	 */
	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->processBulkActions();

		$perPage     = (int) $this->get_items_per_page( 'dlm_keys_per_page' );
		$currentPage = (int) $this->get_pagenum();
		$records     = $this->getRecords( $perPage, $currentPage );
		$count       = $this->getRecordsCount();

		$this->items = $records;

		// Set the pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $perPage,
				'total_pages' => ceil( $count / $perPage ),
			)
		);
	}

	/**
	 * Retrieves the records from the database.
	 *
	 * @param int $perPage Default amount of records per page
	 * @param int $pageNumber Default page number
	 *
	 * @return array
	 */
	protected function getRecords( $perPage = 20, $pageNumber = 1 ) {
		global $wpdb;

		$search = '';
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search = "AND description LIKE '%" . esc_sql( $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) ) . "%'";
		}

		return $wpdb->get_results( "
            SELECT
                id, user_id, description, permissions, truncated_key, last_access
            FROM
                {$wpdb->prefix}dlm_api_keys
            WHERE
                1=1
                {$search} " . $wpdb->prepare( 'ORDER BY id DESC LIMIT %d OFFSET %d;', $perPage, ( $pageNumber - 1 ) * $perPage ), ARRAY_A );
	}

	/**
	 * Retrieves the number of records in the database
	 * @return int
	 */
	protected function getRecordsCount() {
		global $wpdb;
		$search = '';
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search = "AND description LIKE '%" . esc_sql( $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) ) . "%'";
		}

		return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}dlm_api_keys WHERE 1 = 1 {$search};" );
	}

	/**
	 * Permanently deletes records from the database
	 *
	 * @throws Exception
	 */
	protected function handleDelete() {

		$keys = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
		if ( ! empty( $keys ) ) {
			$keys = array_map( 'intval', $keys );
		}

		if ( $count = ApiKeyResourceRepository::instance()->delete( $keys ) ) {
			AdminNotice::success( sprintf( __( '%d API key(s) permanently deleted.', 'digital-license-manager' ), $count ) );
		} else {
			AdminNotice::error( __( 'There was a problem deleting the API key(s).', 'digital-license-manager' ) );
		}

		wp_redirect( sprintf( 'admin.php?page=%s&tab=rest_api', $this->slug ) );
		exit();
	}

}
