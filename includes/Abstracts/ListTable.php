<?php


namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Utils\Notice as AdminNotice;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class ListTable
 * @package IdeoLogix\DigitalLicenseManager\Abstracts
 */
abstract class ListTable extends \WP_List_Table {

	/**
	 * The Database table
	 * @var string
	 */
	protected $table;

	/**
	 * The date format
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * The time format
	 * @var string
	 */
	protected $timeFormat;

	/**
	 * The GMT offset
	 * @var string
	 */
	protected $gmtOffset;

	/**
	 * Whether user can create records
	 * @var bool
	 */
	protected $canCreate = null;

	/**
	 * Whether user can edit records
	 * @var bool
	 */
	protected $canEdit = null;

	/**
	 * Whether user can delete records
	 * @var bool
	 */
	protected $canDelete = null;

	/**
	 * Whether user can export records
	 * @var bool
	 */
	protected $canExport = null;

	/**
	 * The page slug
	 * @var string
	 */
	protected $slug;

	public function __construct( $args = array() ) {
		$this->dateFormat = get_option( 'date_format' );
		$this->timeFormat = get_option( 'time_format' );
		$this->gmtOffset  = get_option( 'gmt_offset' );
		parent::__construct( $args );
	}

	/**
	 * Checks if the given nonce is (still) valid.
	 *
	 * @param $nonce
	 */
	protected function verifyNonce( $nonce ) {
		$currentNonce = $_REQUEST['_wpnonce'];

		if ( ! wp_verify_nonce( $currentNonce, $nonce )
		     && ! wp_verify_nonce( $currentNonce, 'bulk-' . $this->_args['plural'] )
		) {
			AdminNotice::error( __( 'The nonce is invalid or has expired.', 'digital-license-manager' ) );
			wp_redirect(
				admin_url( sprintf( 'admin.php?page=%s', $this->slug ) )
			);

			exit();
		}
	}

	/**
	 * Makes sure that license keys were selected for the bulk action.
	 */
	protected function verifySelection() {
		if ( ! array_key_exists( 'id', $_REQUEST ) ) {
			$message = sprintf( __( 'No %s were selected.', 'digital-license-manager' ), $this->_args['plural'] );
			AdminNotice::warning( $message );

			wp_redirect(
				admin_url(
					sprintf( 'admin.php?page=%s', $this->slug )
				)
			);

			exit();
		}
	}

	/**
	 * Output in case no items exist.
	 */
	public function no_items() {
		echo sprintf( __( 'No %s found.', 'digital-license-manager' ), $this->_args['plural'] );
	}

}