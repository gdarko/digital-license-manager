<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceModelInterface;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use stdClass;

defined( 'ABSPATH' ) || exit;

class ApiKey extends AbstractResourceModel implements ResourceModelInterface {
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $permissions;

	/**
	 * @var array|null
	 */
	protected $endpoints;

	/**
	 * @var string
	 */
	protected $consumer_key;

	/**
	 * @var string
	 */
	protected $consumer_secret;

	/**
	 * @var string
	 */
	protected $nonces;

	/**
	 * @var string
	 */
	protected $truncated_key;

	/**
	 * @var string
	 */
	protected $lastAccess;

	/**
	 * ApiKey constructor.
	 *
	 * @param stdClass|null $apiKey
	 */
	public function __construct( $apiKey = null ) {
		if ( ! $apiKey instanceof stdClass ) {
			return;
		}

		$this->id              = $apiKey->id;
		$this->user_id         = $apiKey->user_id;
		$this->description     = $apiKey->description;
		$this->permissions     = $apiKey->permissions;
		$this->endpoints       = JsonFormatter::decode( $apiKey->endpoints, true );
		$this->consumer_key    = $apiKey->consumer_key;
		$this->consumer_secret = $apiKey->consumer_secret;
		$this->nonces          = $apiKey->nonces;
		$this->truncated_key   = $apiKey->truncated_key;
		$this->lastAccess      = $apiKey->last_access;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getPermissions() {
		return $this->permissions;
	}

	/**
	 * @param string $permissions
	 */
	public function setPermissions( $permissions ) {
		$this->permissions = $permissions;
	}

	/**
	 * @return mixed|string
	 */
	public function getEndpoints() {
		return $this->endpoints;
	}

	/**
	 * @param string $endpoints
	 */
	public function setEndpoints( $endpoints ) {
		$this->endpoints = $endpoints;
	}


	/**
	 * @return string
	 */
	public function getConsumerKey() {
		return $this->consumer_key;
	}

	/**
	 * @param string $consumer_key
	 */
	public function setConsumerKey( $consumer_key ) {
		$this->consumer_key = $consumer_key;
	}

	/**
	 * @return string
	 */
	public function getConsumerSecret() {
		return $this->consumer_secret;
	}

	/**
	 * @param string $consumer_secret
	 */
	public function setConsumerSecret( $consumer_secret ) {
		$this->consumer_secret = $consumer_secret;
	}

	/**
	 * @return string
	 */
	public function getNonces() {
		return $this->nonces;
	}

	/**
	 * @param string $nonces
	 */
	public function setNonces( $nonces ) {
		$this->nonces = $nonces;
	}

	/**
	 * @return string
	 */
	public function getTruncatedKey() {
		return $this->truncated_key;
	}

	/**
	 * @param string $truncated_key
	 */
	public function setTruncatedKey( $truncated_key ) {
		$this->truncated_key = $truncated_key;
	}

	/**
	 * @return string
	 */
	public function getLastAccess() {
		return $this->lastAccess;
	}

	/**
	 * @param string $lastAccess
	 */
	public function setLastAccess( $lastAccess ) {
		$this->lastAccess = $lastAccess;
	}
}
