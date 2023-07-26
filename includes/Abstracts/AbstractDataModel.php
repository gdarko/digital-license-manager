<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\DataModelInterface;
use TenQuality\WP\Database\Abstracts\DataModel;
use TenQuality\WP\Database\Traits\DataModelTrait;

abstract class AbstractDataModel extends DataModel implements DataModelInterface {

	use DataModelTrait;

	/**
	 * Get property
	 *
	 * @param $property
	 *
	 * @return mixed|null
	 */
	protected function get( $property ) {
		return isset( $this->attributes[ $property ] ) ? $this->attributes[ $property ] : null;
	}


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
	 * Cast the required attributes
	 *
	 * @param $attributes
	 *
	 * @return mixed
	 */
	private function cast( $attributes ) {

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

}