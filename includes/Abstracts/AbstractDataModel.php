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

use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\DataModelInterface;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use TenQuality\WP\Database\Abstracts\DataModel;
use TenQuality\WP\Database\Traits\DataModelTrait;

abstract class AbstractDataModel extends DataModel implements DataModelInterface {

	use DataModelTrait;

	/**
	 * Are timestamps supported?
	 * @var bool
	 */
	protected $timestamps;

	/**
	 * The appended timestamps
	 * @var array
	 */
	protected $appends = [];

	/**
	 * The model casts
	 * @var array
	 */
	protected $casts = [];


	/**
	 * Hidden from public
	 * @var array
	 */
	protected $hidden = [];

	/**
	 * Dates
	 * @var string
	 */
	public $created_at;
	public $updated_at;

	/**
	 * Override the main constructor
	 *
	 * @param $attributes
	 * @param $id
	 */
	public function __construct( $attributes = [], $id = null ) {

		$attributes = $this->cast( $attributes );

		parent::__construct( $attributes, $id );
	}

	/**
	 * Converts the current data to array
	 * @return array|void
	 */
	public function toArray() {
		$attributes = $this->attributes;

		foreach ( $this->appends as $key ) {
			if ( method_exists( $this, 'get' . ucfirst( $key ) . 'Alias' ) ) {
				$value              = call_user_func_array( [ &$this, 'get' . ucfirst( $key ) . 'Alias' ], [] );
				$attributes[ $key ] = $value;
			}
		}

		foreach ( $this->hidden as $key ) {
			if ( array_key_exists( $key, $attributes ) ) {
				unset( $attributes[ $key ] );
			}
		}

		return $attributes;
	}

	/**
	 * Cast the required attributes
	 *
	 * @param $attributes
	 *
	 * @return mixed
	 */
	protected function cast( $attributes ) {
		foreach ( $this->casts as $key => $type ) {
			if ( ! array_key_exists( $key, $attributes ) || ( empty( $attributes[ $key ] ) && '0' !== $attributes[ $key ] ) ) {
				continue;
			}
			switch ( $type ) {
				case 'int':
					$attributes[ $key ] = (int) $attributes[ $key ];
					break;
				case 'string':
					$attributes[ $key ] = (string) $attributes[ $key ];
					break;
				case 'json':
					if ( '[]' === $attributes[ $key ] ) {
						$attributes[ $key ] = [];
					} else {
						$attributes[ $key ] = JsonFormatter::decode( $attributes[ $key ], true );
					}
					break;
				case 'mixed':
					// If valid json, decode it. Otherwise return as it is.
					$attributes[ $key ] = JsonFormatter::decode( $attributes[ $key ], true );
					break;
			}
		}

		return $attributes;
	}

	/**
	 * Get property
	 *
	 * @param $property
	 *
	 * @return mixed|null
	 */
	public function get( $property ) {
		return isset( $this->attributes[ $property ] ) ? $this->attributes[ $property ] : null;
	}

	/**
	 * Returns decoded json
	 *
	 * @param $key
	 * @param $cached
	 *
	 * @return mixed
	 */
	protected function getJson( $key, $cached = true ) {
		return JsonFormatter::decode( $this->get( $key ), true );
	}

	/**
	 *
	 * Saves data attributes in database.
	 * Returns flag indicating if save process was successful.
	 *
	 * Note: Method has been overriden for the purpose of Digital License Manager to interpret 'null' values as database NULL
	 *
	 * @param bool $force_insert Flag that indicates if should insert regardless of ID.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 * @global object Wordpress Data base accessor.
	 *
	 */
	public function save( $force_insert = false ) {
		global $wpdb;

		if ( ! $force_insert && $this->{$this->primary_key} ) {
			// Update
			$success = $wpdb->update( $this->getTablenameAlias(), $this->getData( 'update' ), [ $this->primary_key => $this->attributes[ $this->primary_key ] ], $this->getDataFormat() );
			if ( $success ) {
				do_action( 'data_model_' . $this->table . '_updated', $this );
			}
		} else {

			// Insert
			$success                    = $wpdb->insert( $this->getTablenameAlias(), $this->getData( 'create' ), $this->getDataFormat() );
			$this->{$this->primary_key} = $wpdb->insert_id;
			$date                       = date( 'Y-m-d H:i:s' );
			$this->created_at           = $date;
			$this->updated_at           = $date;
			if ( $success ) {
				do_action( 'data_model_' . $this->table . '_inserted', $this );
			}
		}
		if ( $success ) {
			do_action( 'data_model_' . $this->table . '_save', $this );
		}

		return $success;
	}


	/**
	 * Guess the data format
	 * @return array
	 */
	/**
	 * Guess the data format
	 * @return array
	 */
	protected function getDataFormat() {
		$data   = $this->getData();
		$format = [];

		foreach ( $data as $key => $value ) {
			if ( is_null( $value ) || ! empty( $value ) && is_scalar( $value ) && 'null' === strtolower( $value ) ) {
				$format[] = null;
			} else if ( is_numeric( $value ) ) {
				if ( strpos( $value, '.' ) !== false ) {
					$format[] = '%f';
				} else {
					$format[] = '%d';
				}
			} else {
				$format[] = '%s';
			}
		}


		return $format;
	}

	/**
	 * Prepares the data
	 *
	 * @param $op
	 *
	 * @return array
	 */
	protected function getData( $op = 'create' ) {
		$protected = $this->protected_properties();

		$data = array_filter( $this->attributes, function ( $key ) use ( $protected ) {
			return ! in_array( $key, $protected );
		}, ARRAY_FILTER_USE_KEY );


		foreach ( $data as $key => $value ) {
			if ( is_scalar( $value ) && null !== $value && 'null' === strtolower( $value ) ) {
				$data[ $key ] = null;
			}
		}

		if ( $this->timestamps ) {
			$stamp = date( 'Y-m-d H:i:s' );
			switch ( $op ) {
				case 'update':
					$data['updated_at'] = $stamp;
					break;
				case 'create':
					$data['created_at'] = $stamp;
					break;
			}
		}

		return $data;
	}


}