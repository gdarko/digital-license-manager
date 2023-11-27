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

abstract class AbstractTool {

	/**
	 * The instance id
	 * @var - integer
	 */
	protected $id;

	/**
	 * The identifier
	 * @var string
	 */
	protected $slug;

	/**
	 * The description
	 * @var string
	 */
	protected $description;

	/**
	 * Constructor
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Set tool data
	 * @param $key
	 * @param $value
	 *
	 * @return array
	 */
	public function setData( $key, $value ) {
		$data = $this->getData();
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		$data[ $key ] = $value;
		set_transient( $this->getDataKey(), $data, apply_filters( 'dlm_tool_data_expiration', 48 * HOUR_IN_SECONDS, $this ) );
		return $value;
	}

	/**
	 * Get tool data
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	public function getData( $key = null, $default = null ) {
		$data = get_transient( $this->getDataKey() );

		if ( is_null( $key ) ) {
			return $data;
		}

		return is_array( $data ) && isset( $data[ $key ] ) ? $key : $default;
	}

	/**
	 * Delete tool data
	 * @return void
	 */
	public function deleteData() {
		delete_transient( $this->getDataKey() );
	}

	/**
	 * Get the data key
	 * @return string
	 */
	public function getDataKey() {
		return 'dlm_tool_' . md5( $this->id );
	}

	/**
	 * Returns the view
	 * @return string
	 */
	abstract public function getView();

	/**
	 * Returns the tool steps
	 *
	 * eg:
	 *
	 *    [
	 *        1 => array( 'name' => 'Step 1', 'pages' => 3 ),
	 *        2 => array( 'name' => 'Step 2', 'pages' => 4 ),
	 *        3 => array( 'name' => 'Step 3', 'pages' => 5 ),
	 *        4 => array( 'name' => 'Step 4', 'pages' => 6 ),
	 *        5 => array( 'name' => 'Step 5', 'pages' => 7 )
	 *    ];
	 *
	 * @return array|\WP_Error
	 */
	abstract public function getSteps();

	/**
	 * Initializes the process
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function initProcess();

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param $page
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function doStep( $step, $page );


	/**
	 * Mark as complete (Not all tools needs this)
	 * @return void
	 */
	public function markAsComplete() {

	}

	/**
	 * Return the next step
	 *
	 * @param $step
	 * @param $page
	 *
	 * @return array|\WP_Error
	 */
	public function getNextStep( $step, $page ) {

		$step = is_null( $step ) ? 1 : (int) $step;
		$page = is_null( $page ) ? 1 : (int) $page;

		$steps = $this->getSteps();

		if ( ! is_array( $steps ) ) {
			return new \WP_Error( '500', 'Unable to determine next step', 'digital-license-manager' );
		}

		$total = 0;
		foreach ( $steps as $id => $stepp ) {
			$total += (int) $stepp['pages'];
		}

		$data = [
			'next_step' => 0,
			'next_page' => 0,
			'message'   => '',
			'total'     => $total,
			'current'   => 0,
			'percent'   => 0,
		];

		if ( ! isset( $steps[ $step ] ) ) {
			$data['next_step'] = - 1;
			$data['next_page'] = - 1;
			$data['message']   = __( 'Operation not initialized properly.', 'digital-license-manager' );

			return $data;
		} else {

			if ( ! isset( $steps[ $step ]['pages'] ) ) {
				return new \WP_Error( '500', __( 'Unable to determine next step', 'digital-license-manager' ) );
			}

			$next_page   = $page + 1;
			$total_pages = (int) $steps[ $step ]['pages'];

			if ( $page === $total_pages ) {
				$next_step         = isset( $steps[ $step + 1 ] ) ? $step + 1 : - 1;
				$data['next_step'] = $next_step;
				$data['next_page'] = isset( $steps[ $step + 1 ] ) ? 1 : - 1;
				$data['message']   = sprintf( __( 'Processing "%s" - Page: %d/%d', 'digital-license-manager' ), $steps[ $step ]['name'], $page, $total_pages );
			} else if ( $page < $total_pages ) {
				$next_step         = $step;
				$data['next_step'] = $next_step;
				$data['next_page'] = $next_page;
				$data['message']   = sprintf( __( 'Processing "%s" - Page: %d/%d', 'digital-license-manager' ), $steps[ $step ]['name'], $page, $total_pages );
			} else if ( isset( $steps[ $step + 1 ] ) ) {
				$next_page         = 1;
				$next_step         = $step + 1;
				$data['next_step'] = $next_step;
				$data['next_page'] = $next_page;
				$data['message']   = sprintf( __( 'Processing "%s" - Page: %d/%d', 'digital-license-manager' ), $steps[ $step ]['name'], $page, $total_pages );

			} else {
				$next_step         = - 1;
				$data['next_step'] = - 1;
				$data['next_page'] = - 1;
				$data['message']   = __( 'Operation complete.', 'digital-license-manager' );
			}


			$current = 0;
			foreach ( $steps as $i => $info ) {
				if ( $i < $step ) {
					$current += $info['pages'];
				} else if ( $i === $step ) {
					$current += $page;
				}
			}

			$data['percent'] = $current > 0 && $total > 0 ? round( $current / $total * 100, 2 ) : 0;

		}

		return $data;

	}

	/**
	 * Return the id identifier
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Return the id identifier
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Return the description
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
}
