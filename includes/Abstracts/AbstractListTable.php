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

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class ListTable
 * @package IdeoLogix\DigitalLicenseManager\Abstracts
 */
abstract class AbstractListTable extends \WP_List_Table {

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
	protected function validateNonce( $nonce ) {

		$nonceValue = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';

		if ( empty( $nonceValue ) ) {
			NoticeFlasher::error( __( 'Permission denied.', 'digital-license-manager' ) );
			exit;
		}

		if ( ! wp_verify_nonce( $nonceValue, $nonce ) && ! wp_verify_nonce( $nonceValue, 'bulk-' . $this->_args['plural'] ) ) {
			NoticeFlasher::error( __( 'The nonce is invalid or has expired.', 'digital-license-manager' ) );
			HttpHelper::redirect(
				admin_url( sprintf( 'admin.php?page=%s', $this->slug ) )
			);
		}
	}

	/**
	 * Makes sure that license keys were selected for the bulk action.
	 */
	protected function validateSelection() {

		if ( ! isset( $_REQUEST['id'] ) ) {
			$message = sprintf( __( 'No %s were selected.', 'digital-license-manager' ), $this->_args['plural'] );
			NoticeFlasher::warning( $message );

			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
		}
	}

	/**
	 * Output in case no items exist.
	 */
	public function no_items() {
		echo sprintf( __( 'No %s found.', 'digital-license-manager' ), $this->_args['plural'] );
	}

	/**
	 * Search box.
	 *
	 * @param string $text Button text
	 * @param string $input_id Input ID
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$key   = sprintf( '%s-search-input', $input_id );
		$query = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

		?>
        <p class="search-box">
			<?php echo sprintf( '<label class="screen-reader-text" for="%s">%s</label>', esc_attr( $key ), esc_html( $text ) ); ?>
			<?php echo sprintf( '<input type="search" id="%s" name="s" value="%s"/>', esc_attr( $key ), esc_attr( $query ) ); ?>
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit', ) ); ?>
        </p>
		<?php
	}

}
