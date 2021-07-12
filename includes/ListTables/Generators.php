<?php

namespace IdeoLogix\DigitalLicenseManager\ListTables;

use IdeoLogix\DigitalLicenseManager\Abstracts\ListTable;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use Exception;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Products;
use IdeoLogix\DigitalLicenseManager\Utils\Notice as AdminNotice;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Class Generators
 * @package IdeoLogix\DigitalLicenseManager\ListTables
 */
class Generators extends ListTable {

	/**
	 * GeneratorsList constructor.
	 */
	public function __construct() {
		global $wpdb;

		parent::__construct(
			array(
				'singular' => __( 'Generator', 'digital-license-manager' ),
				'plural'   => __( 'Generators', 'digital-license-manager' ),
				'ajax'     => false
			)
		);

		$this->slug      = PageSlug::GENERATORS;
		$this->table     = $wpdb->prefix . DatabaseTable::GENERATORS;
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
		global $wpdb;

		$perPage    = (int) $perPage;
		$pageNumber = (int) $pageNumber;

		$sql = "SELECT * FROM {$this->table}";
		$sql .= ' ORDER BY ' . ( empty( $_REQUEST['orderby'] ) ? 'id' : esc_sql( sanitize_text_field( $_REQUEST['orderby'] ) ) );
		$sql .= ' ' . ( empty( $_REQUEST['order'] ) ? 'DESC' : esc_sql( sanitize_text_field( $_REQUEST['order'] ) ) );
		$sql .= " LIMIT {$perPage}";
		$sql .= ' OFFSET ' . ( $pageNumber - 1 ) * $perPage;

		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results;
	}

	/**
	 * Retrieves the number of records in the database
	 * @return int
	 */
	private function getRecordsCount() {
		global $wpdb;

		return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
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
	 * Name column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_name( $item ) {

		try {
			$products = Products::getByGenerator( $item['id'] );
		} catch ( Exception $e ) {
			$products = array();
		}
		$actions = array();
		$title   = '<strong>' . $item['name'] . '</strong>';

		if ( count( $products ) > 0 ) {
			$title .= sprintf(
				'<span class="dlm-badge info" title="%s">%d</span>',
				__( 'Number of products assigned to this generator. This generator can not be deleted as long as products are assigned.', 'digital-license-manager' ),
				count( $products )
			);
		}

		$actions['id'] = sprintf( __( 'ID: %d', 'digital-license-manager' ), (int) $item['id'] );

		if ( ! $products && $this->canDelete ) {
			$actions['delete'] = sprintf(
				'<a href="?page=%s&action=%s&id=%s&_wpnonce=%s" class="dlm-confirm-dialog">%s</a>',
				$this->slug,
				'delete',
				absint( $item['id'] ),
				wp_create_nonce( 'delete' ),
				__( 'Delete', 'digital-license-manager' )
			);
		}

		if ( $this->canEdit ) {
			$actions['edit'] = sprintf(
				'<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
				$this->slug,
				'edit',
				absint( $item['id'] ),
				wp_create_nonce( 'edit' ),
				__( 'Edit', 'digital-license-manager' )
			);
		}


		return $title . $this->row_actions( $actions );
	}

	/**
	 * Character map column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_charset( $item ) {
		$charset = '';

		if ( $item['charset'] ) {
			$charset = sprintf( '<code>%s</code>', $item['charset'] );
		}

		return $charset;
	}

	/**
	 * Separator column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_separator( $item ) {
		$separator = '';

		if ( $item['separator'] ) {
			$separator = sprintf( '<code>%s</code>', $item['separator'] );
		}

		return $separator;
	}

	/**
	 * Prefix column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_prefix( $item ) {
		$prefix = '';

		if ( $item['prefix'] ) {
			$prefix = sprintf( '<code>%s</code>', $item['prefix'] );
		}

		return $prefix;
	}

	/**
	 * Suffix column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_suffix( $item ) {
		$suffix = '';

		if ( $item['suffix'] ) {
			$suffix = sprintf( '<code>%s</code>', $item['suffix'] );
		}

		return $suffix;
	}

	/**
	 * Expires in column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_expires_in( $item ) {
		$expiresIn = '';

		if ( ! $item['expires_in'] ) {
			return $expiresIn;
		}

		$expiresIn .= sprintf( '%d %s', $item['expires_in'], __( 'day(s)', 'digital-license-manager' ) );
		$expiresIn .= '<br>';
		$expiresIn .= sprintf( '<small>%s</small>', __( 'After purchase', 'digital-license-manager' ) );

		return $expiresIn;
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
		return $item[ $column_name ];
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

		$result = GeneratorResourceRepository::instance()->delete( $generatorsToDelete );

		if ( $result ) {
			AdminNotice::success( sprintf( __( '%d generator(s) permanently deleted.', 'digital-license-manager' ), $result ) );

			wp_redirect(
				admin_url(
					sprintf( 'admin.php?page=%s', $this->slug )
				)
			);
		} else {
			AdminNotice::error( __( 'There was a problem deleting the generators.', 'digital-license-manager' ) );

			wp_redirect(
				admin_url(
					sprintf( 'admin.php?page=%s', $this->slug )
				)
			);
		}
	}
}
