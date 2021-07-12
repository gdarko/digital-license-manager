<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\ResourceModel as AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\Model as ModelInterface;
use stdClass;

defined( 'ABSPATH' ) || exit;

/**
 * Class Generator
 * @package IdeoLogix\DigitalLicenseManager\Database\Models\Resources
 */
class Generator extends AbstractResourceModel implements ModelInterface {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $charset;

	/**
	 * @var int
	 */
	protected $chunks;

	/**
	 * @var int
	 */
	protected $chunk_length;

	/**
	 * @var int
	 */
	protected $activations_limit;

	/**
	 * @var string
	 */
	protected $separator;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var string
	 */
	protected $suffix;

	/**
	 * @var int
	 */
	protected $expires_in;

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
	 * Generator constructor.
	 *
	 * @param stdClass $generator
	 */
	public function __construct( $generator ) {
		if ( ! $generator instanceof stdClass ) {
			return;
		}

		$this->id                = (int) $generator->id;
		$this->name              = $generator->name;
		$this->charset           = $generator->charset;
		$this->chunks            = (int) $generator->chunks;
		$this->chunk_length      = (int) $generator->chunk_length;
		$this->activations_limit = $generator->activations_limit;
		$this->separator         = $generator->separator;
		$this->prefix            = $generator->prefix;
		$this->suffix            = $generator->suffix;
		$this->expires_in        = $generator->expires_in;
		$this->created_at        = $generator->created_at;
		$this->created_by        = $generator->created_by;
		$this->updated_at        = $generator->updated_at;
		$this->updated_by        = $generator->updated_by;
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
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * @param string $charset
	 */
	public function setCharset( $charset ) {
		$this->charset = $charset;
	}

	/**
	 * @return int
	 */
	public function getChunks() {
		return $this->chunks;
	}

	/**
	 * @param int $chunks
	 */
	public function setChunks( $chunks ) {
		$this->chunks = $chunks;
	}

	/**
	 * @return int
	 */
	public function getChunkLength() {
		return $this->chunk_length;
	}

	/**
	 * @param int $chunk_length
	 */
	public function setChunkLength( $chunk_length ) {
		$this->chunk_length = $chunk_length;
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
	public function getSeparator() {
		return $this->separator;
	}

	/**
	 * @param string $separator
	 */
	public function setSeparator( $separator ) {
		$this->separator = $separator;
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getSuffix() {
		return $this->suffix;
	}

	/**
	 * @param string $suffix
	 */
	public function setSuffix( $suffix ) {
		$this->suffix = $suffix;
	}

	/**
	 * @return int
	 */
	public function getExpiresIn() {
		return $this->expires_in;
	}

	/**
	 * @param int $expires_in
	 */
	public function setExpiresIn( $expires_in ) {
		$this->expires_in = $expires_in;
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
}

