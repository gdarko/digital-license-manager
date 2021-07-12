<?php


namespace IdeoLogix\DigitalLicenseManager\Controllers;


use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Utils\Hash;
use WP_User;
use WP_User_Query;

/**
 * Class Dropdowns
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Dropdowns {

	/**
	 * Dropdowns constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_dlm_dropdown_search', array( $this, 'dropdownSearch' ), 10 );
	}

	/**
	 * The dropdown search
	 */
	public function dropdownSearch() {

		check_ajax_referer( 'dlm_dropdown_search', 'security' );

		if ( ! current_user_can( 'dlm_read_licenses' ) ) {
			wp_die();
		}

		$type    = (string) sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$page    = 1;
		$limit   = 10;
		$results = array();
		$term    = isset( $_POST['term'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		$more    = true;
		$offset  = 0;
		$ids     = array();

		if ( ! $term ) {
			wp_die();
		}

		if ( array_key_exists( 'page', $_POST ) ) {
			$page = (int) $_POST['page'];
		}

		if ( $page > 1 ) {
			$offset = ( $page - 1 ) * $limit;
		}

		if ( is_numeric( $term ) ) {
			// Search for a specific license
			if ( $type === 'license' ) {

				/** @var LicenseResourceModel $license */
				$license = LicenseResourceRepository::instance()->find( (int) $term );

				// Product exists.
				if ( $license ) {
					$text      = sprintf(
						'#%s',
						$license->getId()
					);
					$results[] = array(
						'id'   => $license->getId(),
						'text' => $text
					);
				}
			} // Search for a specific user
			elseif ( $type === 'user' ) {

				$users = new WP_User_Query(
					array(
						'search'         => '*' . esc_attr( $term ) . '*',
						'search_columns' => array(
							'user_id'
						),
					)
				);

				/** @var WP_User $user */
				foreach ( $users->get_results() as $user ) {
					$results[] = array(
						'id'   => $user->ID,
						'text' => sprintf(
						/* translators: $1: user nicename, $2: user id, $3: user email */
							'%1$s (#%2$d - %3$s)',
							$user->user_nicename,
							$user->ID,
							$user->user_email
						)
					);
				}
			} else {
				$result = apply_filters( 'dlm_dropdown_search_single', [], $type, $term );
				if ( ! empty( $result ) ) {
					$results[] = $result;
				}
			}
		}

		if ( empty( $ids ) ) {
			$args = array(
				'type'     => $type,
				'limit'    => $limit,
				'offset'   => $offset,
				'customer' => $term,
			);

			// Search for licenses
			if ( $type === 'license' ) {
				$licenses = $this->searchLicenses( $term, $limit, $offset );

				if ( count( $licenses ) < $limit ) {
					$more = false;
				}

				foreach ( $licenses as $licenseId ) {
					/** @var LicenseResourceModel $license */
					$text      = sprintf(
						'#%s',
						$licenseId
					);
					$results[] = array(
						'id'   => $licenseId,
						'text' => $text
					);
				}
			} // Search for users
			elseif ( $type === 'user' ) {
				$users = new WP_User_Query(
					array(
						'search'         => '*' . esc_attr( $term ) . '*',
						'search_columns' => array(
							'user_login',
							'user_nicename',
							'user_email',
							'user_url',
						),
					)
				);

				if ( $users->get_total() < $limit ) {
					$more = false;
				}

				/** @var WP_User $user */
				foreach ( $users->get_results() as $user ) {
					$results[] = array(
						'id'   => $user->ID,
						'text' => sprintf( '%s (#%d - %s)', $user->user_nicename, $user->ID, $user->user_email )
					);
				}
			} else {
				$result = apply_filters(
					'dlm_dropdown_search_multiple',
					array(
						'records' => array(),
						'more'    => $more
					),
					$type,
					$ids,
					$args
				);
				if ( ! empty( $result['records'] ) ) {
					$results = array_merge( $results, $result['records'] );
				}
				if ( isset( $result['more'] ) ) {
					$more = $result['more'];
				}
			}
		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $more
				)
			)
		);
	}

	/**
	 * Searches the database for posts that match the given term.
	 *
	 * @param string $term The search term
	 * @param int $limit Maximum number of search results
	 * @param int $offset Search offset
	 *
	 * @return array
	 */
	private function searchLicenses( $term, $limit, $offset ) {
		global $wpdb;

		$tblLicenses = $wpdb->prefix . DatabaseTable::LICENSES;

		$sql = $wpdb->prepare( "
            SELECT
                DISTINCT (licenses.id)
            FROM
                {$tblLicenses} as licenses
            WHERE
                1=1
                AND (licenses.hash LIKE %s OR licenses.id=%d)
            ORDER BY licenses.ID DESC
            LIMIT %d
            OFFSET %d
        ", "%" . $wpdb->esc_like( Hash::license( $term ) ) . "%", intval( $term ), $limit, $offset );

		return $wpdb->get_col( $sql );
	}
}