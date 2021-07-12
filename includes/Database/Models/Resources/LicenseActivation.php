<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\ResourceModel as AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\Model as ModelInterface;

use \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseRepository;
use IdeoLogix\DigitalLicenseManager\Utils\Json;

/**
 * Class LicenseActivation
 * @package IdeoLogix\DigitalLicenseManager\Database\Models\Resources
 */
class LicenseActivation extends AbstractResourceModel implements ModelInterface {

	protected $id;
	protected $token;
	protected $license_id;
	protected $label;
	protected $source;
	protected $ip_address;
	protected $user_agent;
	protected $meta_data;
	protected $created_at;
	protected $updated_at;
	protected $deactivated_at;

	/**
	 * LicenseActivation constructor.
	 *
	 * @param \stdClass $activation
	 */
	public function __construct( $activation ) {

		if ( ! $activation instanceof \stdClass ) {
			return;
		}

		$this->id             = (int) $activation->id;
		$this->token          = $activation->token;
		$this->license_id     = (int) $activation->license_id;
		$this->label          = $activation->label;
		$this->source         = (int) $activation->source;
		$this->ip_address     = $activation->ip_address;
		$this->user_agent     = $activation->user_agent;
		$this->meta_data      = Json::decode( $activation->meta_data, true );
		$this->created_at     = $activation->created_at;
		$this->updated_at     = $activation->updated_at;
		$this->deactivated_at = $activation->deactivated_at;

	}

	public function getId() {
		return $this->id;
	}

	public function getLicenseId() {
		return $this->license_id;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	public function getSource() {
		return $this->source;
	}

	public function getToken() {
		return $this->token;
	}

	public function getIpAddress() {
		return $this->ip_address;
	}

	public function getUserAgent() {
		return $this->user_agent;
	}

	public function getMetaData() {
		return $this->meta_data;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}

	public function getDeactivatedAt() {
		return $this->deactivated_at;
	}

	public function setId( $id ) {
		$this->id = $id;
	}

	public function setLicenseId( $id ) {
		$this->license_id = $id;
	}

	public function setLabel( $label ) {
		$this->label = $label;
	}

	public function setSource( $id ) {
		$this->source = $id;
	}

	public function setToken( $id ) {
		$this->token = $id;
	}

	public function setIpAddress( $ip ) {
		$this->ip_address = $ip;
	}

	public function setUserAgent( $ua ) {
		$this->user_agent = $ua;
	}

	public function setMetaData( $data ) {
		$this->meta_data = $data;
	}

	public function setCreatedAt( $date ) {
		$this->created_at = $date;
	}

	public function setUpdatedAt( $date ) {
		$this->updated_at = $date;
	}

	public function setDeactivatedAt( $date ) {
		$this->deactivated_at = $date;
	}

	/**
	 * @return bool|AbstractResourceModel|License
	 */
	public function getLicense() {
		return LicenseRepository::instance()->findBy( array(
			'id' => $this->license_id
		) );
	}


}