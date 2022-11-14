<?php


namespace IdeoLogix\DigitalLicenseManager\Controllers;


use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;
use WP_Query;
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
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ $this, 'handleSearchParameter' ], 10, 2 );
	}

	/**
	 * Formats post object
	 *
	 * @param \WP_Post $record
	 */
	private function formatPost( $record ) {
		return array(
			'id'   => $record->ID,
			'text' => sprintf( '#%d - %s', $record->ID, $record->post_title )
		);
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

		$searchable_post_types = apply_filters( 'dlm_dropdown_searchable_post_types', array() );
		$search_query_status   = apply_filters( 'dlm_dropdown_search_query_default_status', array( 'publish' ), $type );

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

				if ( $users->get_total() <= $limit ) {
					$more = false;
				}

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
			} elseif ( $type === 'product' ) {

				$products = [];
				$product  = wc_get_product( $term );
				if ( ! empty( $product ) ) {
					$products[] = $product;
					foreach ( $product->get_children() as $child ) {
						$products[] = wc_get_product( $child );
					}
				}
				foreach ( $products as $product ) {
					$results[] = $this->formatProduct( $product );
				}
				$more = false;

			} elseif ( ! empty( $searchable_post_types ) && in_array( $type, $searchable_post_types ) ) {

				global $wpdb;

				$search_query_status_in = array_map( function ( $item ) {
					return sprintf( "'%s'", esc_sql( $item ) );
				}, $search_query_status );

				$search_query_status_in = implode( ',', $search_query_status_in );

				$query   = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_status IN (" . $search_query_status_in . ") AND post_type=%s AND ID LIKE %s LIMIT %d OFFSET %d", $type, '%' . $term . '%', $limit, $offset );
				$records = $wpdb->get_results( $query );
				$total   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN (" . $search_query_status_in . ") AND post_type=%s AND ID LIKE %s", $type, '%' . $term . '%' ) );

				if ( $total <= $limit ) {
					$more = false;
				}

				if ( ! empty( $records ) ) {
					foreach ( $records as $record ) {
						$results[] = $this->formatPost( $record );
					}
				}

			} else {
				$result = apply_filters( 'dlm_dropdown_search_single', [], $type, $term );
				if ( ! empty( $result ) ) {
					$results[] = $result;
				}
			}
		} else {
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
			} else if ( $type === 'product' ) {

				$all = wc_get_products( [
					'limit'  => - 1,
					'return' => 'ids',
				] );

				$total = count( $all );

				$query = new \WC_Product_Query( [
					'page'   => $page,
					'limit'  => $limit,
					'search' => $term
				] );

				foreach ( $query->get_products() as $product ) {
					/* @var \WC_Product $product */
					$results[] = $this->formatProduct( $product );
					$children  = $product->get_children();
					if ( ! empty( $children ) ) {
						foreach ( $children as $child ) {
							$childProduct = wc_get_product( $child );
							if ( $childProduct ) {
								$results[] = $this->formatProduct( $childProduct );
							}
						}
					}
				}

				$currentTotal = $page * $limit;
				if ( $currentTotal < $total) {
					$more = true;
				} else {
					$more = false;
				}

			} else if ( ! empty( $searchable_post_types ) && in_array( $type, $searchable_post_types ) ) {

				$query = new WP_Query( array(
					'post_type'      => $type,
					's'              => esc_attr( $term ),
					'paged'          => $page,
					'posts_per_page' => $limit,
					'post_status'    => $search_query_status,
				) );

				if ( $query->found_posts <= $limit ) {
					$more = false;
				}

				foreach ( $query->posts as $_post ) {
					$results[] = $this->formatPost( $_post );
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
        ", "%" . $wpdb->esc_like( StringHasher::license( $term ) ) . "%", intval( $term ), $limit, $offset );

		return $wpdb->get_col( $sql );
	}

	/**
	 * Format products
	 *
	 * @param \WC_Product $product
	 *
	 * @return array
	 */
	private function formatProduct( $product ) {

		$type = '';
		if ( $product->is_type( 'variable' ) ) {
			$type .= ' (' . __( 'Variable', 'digital-license-manager' ).')';
		} else if ( $product->is_type( 'variation' ) ) {
			$type .= ' (' . __( 'Variation', 'digital-license-manager' ).')';
		}

		if($product->is_type( 'variation' )) {
			$id = $product->get_parent_id();
			$title = wp_strip_all_tags($product->get_formatted_name());
		} else {
			$title = $product->get_name();
			$id = $product->get_id();
		}


		return [
			'id'   => $product->get_id(),
			'text' => sprintf( '#%d - %s%s', $id, $title, $type )
		];
	}

	/**
	 * Handles search parameter for products
	 *
	 * @param $query
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public function handleSearchParameter( $query, $query_vars ) {
		if ( isset( $query_vars['search'] ) && ! empty( $query_vars['search'] ) ) {
			$query['s'] = esc_attr( $query_vars['search'] );
		}

		return $query;
	}
}
