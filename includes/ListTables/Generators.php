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
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators as GeneratorsRepository;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Products;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;

defined( 'ABSPATH' ) || exit;

/**
 * Class Generators
 * @package IdeoLogix\DigitalLicenseManager\ListTables
 */
class Generators extends AbstractListTable {

	/**
	 * GeneratorsList constructor.
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Generator', 'digital-license-manager' ),
				'plural'   => __( 'Generators', 'digital-license-manager' ),
				'ajax'     => false
			)
		);

		$this->slug      = PageSlug::GENERATORS;
		$this->table     = GeneratorsRepository::instance()->getTable();
		$this->canEdit   = current_user_can( 'dlm_edit_generators' );
		$this->canDelete = current_user_can( 'dlm_delete_generators' );
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

		$where = [];
		if ( ! empty( $_REQUEST['s'] ) ) {
			$where['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		$offset = ( (int) $pageNumber - 1 ) * (int) $perPage;

		$order_by = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'created_at';
		$order    = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc';


		return GeneratorsRepository::instance()->get( $where, $order_by, $order, $offset, $perPage );
	}

	/**
	 * Retrieves the number of records in the database
	 * @return int
	 */
	private function getRecordsCount() {
		$where = [];
		if ( ! empty( $_REQUEST['s'] ) ) {
			$where['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		return GeneratorsRepository::instance()->count( $where );
	}

	/**
	 * Checkbox column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%d" />', $item->getId() );
	}

	/**
	 * Name column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_name( $item ) {

		try {
			$products = Products::getByGenerator( $item->getId() );
		} catch ( Exception $e ) {
			$products = array();
		}
		$actions = array();
		$title   = '<strong>' . $item->getName() . '</strong>';

		if ( count( $products ) > 0 ) {
			$title .= sprintf(
				'<span class="dlm-badge info" title="%s">%d</span>',
				__( 'Number of products assigned to this generator. This generator can not be deleted as long as products are assigned.', 'digital-license-manager' ),
				count( $products )
			);
		}

		$actions['id'] = sprintf( __( 'ID: %d', 'digital-license-manager' ), (int) $item->getId() );

		if ( ! $products && $this->canDelete ) {
			$actions['delete'] = sprintf(
				'<a href="?page=%s&action=%s&id=%s&_wpnonce=%s" class="dlm-confirm-dialog">%s</a>',
				$this->slug,
				'delete',
				absint( $item->getId() ),
				wp_create_nonce( 'delete' ),
				__( 'Delete', 'digital-license-manager' )
			);
		}

		if ( $this->canEdit ) {
			$actions['edit'] = sprintf(
				'<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
				$this->slug,
				'edit',
				absint( $item->getId() ),
				wp_create_nonce( 'edit' ),
				__( 'Edit', 'digital-license-manager' )
			);
		}


		return $title . $this->row_actions( $actions );
	}

	/**
	 * Character map column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_charset( $item ) {
		$charset = '';

		if ( $item->getCharset() ) {
			$charset = sprintf( '<code>%s</code>', $item->getCharset() );
		}

		return $charset;
	}

	/**
	 * Separator column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_separator( $item ) {
		$separator = '';

		if ( $item->getSeparator() ) {
			$separator = sprintf( '<code>%s</code>', $item->getSeparator() );
		}

		return $separator;
	}

	/**
	 * Prefix column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_prefix( $item ) {
		$prefix = '';

		if ( $item->getPrefix() ) {
			$prefix = sprintf( '<code>%s</code>', $item->getPrefix() );
		}

		return $prefix;
	}

	/**
	 * Suffix column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_suffix( $item ) {
		$suffix = '';

		if ( $item->getSuffix() ) {
			$suffix = sprintf( '<code>%s</code>', $item->getSuffix() );
		}

		return $suffix;
	}

	/**
	 * Expires in column.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_expires_in( $item ) {
		$expiresIn = '';

		if ( ! $item->getExpiresIn() ) {
			return $expiresIn;
		}

		$expiresIn .= sprintf( '%d %s', $item->getExpiresIn(), __( 'day(s)', 'digital-license-manager' ) );
		$expiresIn .= '<br>';
		$expiresIn .= sprintf( '<small>%s</small>', __( 'After purchase', 'digital-license-manager' ) );

		return $expiresIn;
	}

	/**
	 * Default column value.
	 *
	 * @param Generator $item Associative array of column name and value pairs
	 * @param string $column_name Name of the current column
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return $item->get( $column_name );
	}

	/**
	 * Set the table columns.
	 */
	public function get_columns() {
		return array(
			'cb'                => '<input type="checkbox" />',
			'name'              => __( 'Name', 'digital-license-manager' ),
			'charset'           => __( 'Character map', 'digital-license-manager' ),
			'chunks'            => __( 'Number of chunks', 'digital-license-manager' ),
			'chunk_length'      => __( 'Chunk length', 'digital-license-manager' ),
			'activations_limit' => __( 'Maximum activation count', 'digital-license-manager' ),
			'separator'         => __( 'Separator', 'digital-license-manager' ),
			'prefix'            => __( 'Prefix', 'digital-license-manager' ),
			'suffix'            => __( 'Suffix', 'digital-license-manager' ),
			'expires_in'        => __( 'Expires in', 'digital-license-manager' )
		);
	}

	/**
	 * Defines sortable columns and their sort value.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'name'              => array( 'name', true ),
			'charset'           => array( 'charset', true ),
			'chunks'            => array( 'chunks', true ),
			'chunk_length'      => array( 'chunk_length', true ),
			'activations_limit' => array( 'activations_limit', true ),
			'expires_in'        => array( 'expires_in', true ),
		);
	}

	/**
	 * Defines items in the bulk action dropdown.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
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
		$action = $this->current_action();

		switch ( $action ) {
			case 'delete':
				$this->validateNonce( 'delete' );
				$this->validateSelection();
				if ( $this->canDelete ) {
					$this->handleDelete();
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Initialization function.
	 *
	 * @throws Exception
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		$this->processBulkActions();

		$perPage     = $this->get_items_per_page( 'generators_per_page', 10 );
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
	 * Bulk deletes the generators from the table by a single ID or an array of ID's.
	 *
	 * @throws Exception
	 */
	private function handleDelete() {

		$selectedGenerators = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
		if ( ! empty( $selectedGenerators ) ) {
			$selectedGenerators = array_map( 'intval', $selectedGenerators );
		}
		$generatorsToDelete = array();
		foreach ( $selectedGenerators as $generatorId ) {

			if ( $products = Products::getByGenerator( $generatorId ) ) {
				continue;
			} else {
				array_push( $generatorsToDelete, $generatorId );
			}
		}

		$result = GeneratorsRepository::instance()->delete( $generatorsToDelete );

		if ( $result ) {
			NoticeFlasher::success( sprintf( __( '%d generator(s) permanently deleted.', 'digital-license-manager' ), $result ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
		} else {
			NoticeFlasher::error( __( 'There was a problem deleting the generators.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
		}
	}
}
