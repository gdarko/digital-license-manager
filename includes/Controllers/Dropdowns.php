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

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
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

		$type    = (string) sanitize_text_field( wp_unslash( $_REQUEST['type'] ) );
		$page    = 1;
		$limit   = 6;
		$results = array();
		$term    = isset( $_REQUEST['term'] ) ? (string) sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : '';
		$more    = true;
		$offset  = 0;
		$ids     = array();

		if ( ! $term ) {
			wp_die();
		}

		if ( array_key_exists( 'page', $_REQUEST ) ) {
			$page = (int) $_REQUEST['page'];
		}

		if ( $page > 1 ) {
			$offset = ( $page - 1 ) * $limit;
		}

		$searchable_post_types = apply_filters( 'dlm_dropdown_searchable_post_types', array() );
		$search_query_status   = apply_filters( 'dlm_dropdown_search_query_default_status', array( 'publish' ), $type );

		if ( is_numeric( $term ) ) {
			// Search for a specific license
			if ( $type === 'license' ) {

				/** @var License $license */
				$license = Licenses::instance()->find( (int) $term );

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

				$found_records = apply_filters( 'dlm_dropdown_search_post_type', null, $type, $term, $page, $limit );

				if ( is_null( $found_records ) || empty( $found_records['records'] ) ) {

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
					$results = $found_records['records'];
					$more    = isset( $found_records['more'] ) ? $found_records['more'] : false;
				}

			} else {
				$result = apply_filters( 'dlm_dropdown_search_single', [], $type, $term );

				if ( ! empty( $result['records'] ) ) {
					$results = array_merge( $results, $result['records'] );
				}
				if ( isset( $result['more'] ) ) {
					$more = $result['more'];
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
					/** @var License $license */
					$text      = sprintf(
						'#%s',
						$licenseId
					);
					$results[] = array(
						'id'   => $licenseId,
						'text' => $text
					);
				}
			} // search for generator
			elseif($type === 'generator') {
				$generators = $this->searchGenerators( $term, $limit, $offset );

				if ( count( $generators ) < $limit ) {
					$more = false;
				}

				foreach ( $generators as $generator ) {
					$text      = sprintf(
						'#%d - %s',
						$generator['id'],
						$generator['name']
					);
					$results[] = array(
						'id'   => $generator['id'],
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

				$query = wc_get_products( [
					'page'   => $page,
					'limit'  => $limit,
					'search' => $term,
					'paginate' => true,
				] );


				foreach ( $query->products as $product ) {
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

				$more = $page < $query->max_num_pages;

			} else if ( ! empty( $searchable_post_types ) && in_array( $type, $searchable_post_types ) ) {

				$found_records = apply_filters( 'dlm_dropdown_search_post_type', null, $type, $term, $page, $limit );
				if ( is_null( $found_records ) || empty( $found_records['records'] ) ) {
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
					$results = $found_records['records'];
					$more    = isset( $found_records['more'] ) ? $found_records['more'] : false;
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
					'current' => $page,
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
	 * Searches the database for posts that match the given term.
	 *
	 * @param string $term The search term
	 * @param int $limit Maximum number of search results
	 * @param int $offset Search offset
	 *
	 * @return array
	 */
	private function searchGenerators( $term, $limit, $offset ) {
		global $wpdb;

		$tableName = $wpdb->prefix . DatabaseTable::GENERATORS;

		$sql = $wpdb->prepare( "
            SELECT
            		generators.id,
                	generators.name
            FROM
                {$tableName} as generators
            WHERE
                1=1
                AND (generators.name LIKE %s OR generators.id=%d)
            ORDER BY generators.id DESC
            LIMIT %d
            OFFSET %d
        ", "%" . $wpdb->esc_like( $term ) . "%", intval( $term ), $limit, $offset );

		return $wpdb->get_results( $sql, ARRAY_A );
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
