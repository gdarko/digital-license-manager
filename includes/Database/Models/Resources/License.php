<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation as LicenseActivationResourcesModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation as LicenseActivationResourcesRepository;

use IdeoLogix\DigitalLicenseManager\Abstracts\ResourceModel as AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\Model as ModelInterface;
use DateTime;
use DateTimeZone;
use stdClass;

defined( 'ABSPATH' ) || exit;

/**
 * Class License
 * @package IdeoLogix\DigitalLicenseManager\Database\Models\Resources
 */
class License extends AbstractResourceModel implements ModelInterface {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $order_id;

	/**
	 * @var int
	 */
	protected $product_id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var string
	 */
	protected $license_key;

	/**
	 * @var string
	 */
	protected $hash;

	/**
	 * @var string
	 */
	protected $expires_at;

	/**
	 * @var int
	 */
	protected $valid_for;

	/**
	 * @var int
	 */
	protected $source;

	/**
	 * @var int
	 */
	protected $status;

	/**
	 * @var int
	 */
	protected $times_activated;

	/**
	 * @var int
	 */
	protected $activations_limit;

	/**
	 * Is license expired?
	 * @var int
	 */
	protected $is_expired;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var int
	 */
	protected $created_by;

	/**
	 * @var string
	 */
	protected $updated_at;

	/**
	 * @var int
	 */
	protected $updated_by;

	/**
	 * License constructor.
	 *
	 * @param stdClass $license
	 */
	public function __construct( $license ) {
		if ( ! $license instanceof stdClass ) {
			return;
		}

		$this->id                = $license->id === null ? null : (int) $license->id;
		$this->order_id          = $license->order_id === null ? null : (int) $license->order_id;
		$this->product_id        = $license->product_id === null ? null : (int) $license->product_id;
		$this->user_id           = $license->user_id === null ? null : (int) $license->user_id;
		$this->license_key       = $license->license_key;
		$this->hash              = $license->hash;
		$this->expires_at        = $license->expires_at;
		$this->valid_for         = $license->valid_for === null ? null : (int) $license->valid_for;
		$this->source            = $license->source === null ? null : (int) $license->source;
		$this->status            = $license->status === null ? null : (int) $license->status;
		$this->times_activated   = $this->getTimesActivated();
		$this->activations_limit = $license->activations_limit === null ? null : (int) $license->activations_limit;
		$this->is_expired        = $this->isExpired();
		$this->created_at        = $license->created_at;
		$this->created_by        = $license->created_by === null ? null : (int) $license->created_by;
		$this->updated_at        = $license->updated_at;
		$this->updated_by        = $license->updated_by === null ? null : (int) $license->updated_by;
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
	public function getOrderId() {
		return $this->order_id;
	}

	/**
	 * @param int $order_id
	 */
	public function setOrderId( $order_id ) {
		$this->order_id = $order_id;
	}

	/**
	 * @return int
	 */
	public function getProductId() {
		return $this->product_id;
	}

	/**
	 * @param int $product_id
	 */
	public function setProductId( $product_id ) {
		$this->product_id = $product_id;
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
	public function getLicenseKey() {
		return $this->license_key;
	}

	/**
	 * @param string $license_key
	 */
	public function setLicenseKey( $license_key ) {
		$this->license_key = $license_key;
	}

	/**
	 * Returns the decrypted license key.
	 * @return string|\WP_Error
	 */
	public function getDecryptedLicenseKey() {
		return CryptoHelper::decrypt( $this->license_key );
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash( $hash ) {
		$this->hash = $hash;
	}

	/**
	 * @return string
	 */
	public function getExpiresAt() {
		return $this->expires_at;
	}

	/**
	 * @param string $expires_at
	 */
	public function setExpiresAt( $expires_at ) {
		$this->expires_at = $expires_at;
	}

	/**
	 * @return int
	 */
	public function getValidFor() {
		return $this->valid_for;
	}

	/**
	 * @param int $valid_for
	 */
	public function setValidFor( $valid_for ) {
		$this->valid_for = $valid_for;
	}

	/**
	 * @return int
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * @param int $source
	 */
	public function setSource( $source ) {
		$this->source = $source;
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus( $status ) {
		$this->status = $status;
	}

	/**
	 * @return int
	 */
	public function getTimesActivated() {

		if ( is_null( $this->times_activated ) ) {
			$this->times_activated = $this->getActivationsCount( array(
				'active' => true,
			) );
		}

		return $this->times_activated;
	}

	/**
	 * @param int $times_activated
	 */
	public function setTimesActivated( $times_activated ) {
		$this->times_activated = $times_activated;
	}

	/**
	 * @return int
	 */
	public function getActivationsLimit() {
		return $this->activations_limit;
	}

	/**
	 * @param int $activations_limit
	 */
	public function setActivationsLimit( $activations_limit ) {
		$this->activations_limit = $activations_limit;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}

	/**
	 * @param string $created_at
	 */
	public function setCreatedAt( $created_at ) {
		$this->created_at = $created_at;
	}

	/**
	 * @return int
	 */
	public function getCreatedBy() {
		return $this->created_by;
	}

	/**
	 * @param int $created_by
	 */
	public function setCreatedBy( $created_by ) {
		$this->created_by = $created_by;
	}

	/**
	 * @return string
	 */
	public function getUpdatedAt() {
		return $this->updated_at;
	}

	/**
	 * @param string $updated_at
	 */
	public function setUpdatedAt( $updated_at ) {
		$this->updated_at = $updated_at;
	}

	/**
	 * @return int
	 */
	public function getUpdatedBy() {
		return $this->updated_by;
	}

	/**
	 * @param int $updated_by
	 */
	public function setUpdatedBy( $updated_by ) {
		$this->updated_by = $updated_by;
	}

	/**
	 * Is license expired?
	 * @return bool
	 */
	public function isExpired() {

		$expires_at = $this->getExpiresAt();

		if ( is_null( $expires_at ) ) {
			return false;
		}

		try {
			$dateExpiresAt = new DateTime( $expires_at );
			$dateNow       = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		} catch ( \Exception $e ) {
			return false;
		}

		return $dateNow > $dateExpiresAt;
	}

	/**
	 * Returns the activations
	 * @return bool|LicenseActivationResourcesModel[]
	 */
	public function getActivations( $query = array() ) {

		$params = array(
			'license_id' => $this->getId(),
		);

		if ( isset( $query['active'] ) && $query['active'] ) {
			$params['deactivated_at'] = 'IS NULL';
		}

		return LicenseActivationResourcesRepository::instance()->findAllBy( $params );
	}

	/**
	 * Returns the activations
	 * @return int
	 */
	public function getActivationsCount( $query = array() ) {

		$params = array(
			'license_id' => $this->getId(),
		);

		if ( isset( $query['active'] ) && $query['active'] ) {
			$params['deactivated_at'] = 'IS NULL';
		}

		return LicenseActivationResourcesRepository::instance()->countBy( $params );
	}
}
