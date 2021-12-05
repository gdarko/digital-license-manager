<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use stdClass;

defined( 'ABSPATH' ) || exit;

/**
 * Class LicenseMeta
 * @package IdeoLogix\DigitalLicenseManager\Database\Models\Resources
 */
class LicenseMeta {
	/**
	 * @var int
	 */
	protected $meta_id;

	/**
	 * @var int
	 */
	protected $license_id;

	/**
	 * @var string
	 */
	protected $meta_key;

	/**
	 * @var mixed
	 */
	protected $meta_value;

	/**
	 * License constructor.
	 *
	 * @param stdClass $licenseMeta
	 */
	public function __construct( $licenseMeta ) {
		if ( ! $licenseMeta instanceof stdClass ) {
			return;
		}

		$this->meta_id    = (int) $licenseMeta->meta_id;
		$this->license_id = (int) $licenseMeta->license_id;
		$this->meta_key   = $licenseMeta->meta_key;
		$this->meta_value = JsonFormatter::decode( $licenseMeta->meta_value, true );
	}

	/**
	 * @return int
	 */
	public function getMetaId() {
		return $this->meta_id;
	}

	/**
	 * @param int $meta_id
	 */
	public function setMetaId( $meta_id ) {
		$this->meta_id = $meta_id;
	}

	/**
	 * @return int
	 */
	public function getLicenseId() {
		return $this->license_id;
	}

	/**
	 * @param int $license_id
	 */
	public function setLicenseId( $license_id ) {
		$this->license_id = $license_id;
	}

	/**
	 * @return string
	 */
	public function getMetaKey() {
		return $this->meta_key;
	}

	/**
	 * @param string $meta_key
	 */
	public function setMetaKey( $meta_key ) {
		$this->meta_key = $meta_key;
	}

	/**
	 * @return mixed
	 */
	public function getMetaValue() {
		return $this->meta_value;
	}

	/**
	 * @param mixed $meta_value
	 */
	public function setMetaValue( $meta_value ) {
		$this->meta_value = $meta_value;
	}
}
