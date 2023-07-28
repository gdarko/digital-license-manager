<?php

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
		return $this->attributes;
	}

	/**
	 * Cast the required attributes
	 *
	 * @param $attributes
	 *
	 * @return mixed
	 */
	protected function cast( $attributes ) {

		$allowed = [ $this->primary_key ];

		foreach ( $attributes as $key => $value ) {

			if ( ! in_array( $key, $allowed ) ) {
				continue;
			}

			if ( is_numeric( $value ) ) {
				if ( strpos( $value, '.' ) !== false ) {
					$value = doubleval( $value );
				} else {
					$value = intval( $value );
				}
			}

			$attributes[ $key ] = $value;
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
		static $cache = [];
		if ( $cached ) {
			if ( ! isset( $cache[ $key ] ) ) {
				$cache[ $key ] = JsonFormatter::decode( $this->attributes[ $key ], true );
			}

			return $cache[ $key ];
		} else {
			return JsonFormatter::decode( $this->get( $key ), true );
		}
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
			$success = $wpdb->update( $this->getTablenameAlias(), $this->getData('update'), [ $this->primary_key => $this->attributes[ $this->primary_key ] ], $this->getDataFormat() );
			if ( $success ) {
				do_action( 'data_model_' . $this->table . '_updated', $this );
			}
		} else {
			// Insert
			$success                    = $wpdb->insert( $this->getTablenameAlias(), $this->getData('create'), $this->getDataFormat() );
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
	protected function getDataFormat() {
		$data   = $this->getData();
		$format = [];

		foreach ( $data as $key => $value ) {
			if ( is_null( $value ) || 'null' === strtolower( $value ) ) {
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
			if ( null !== $value && 'null' === strtolower( $value ) ) {
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