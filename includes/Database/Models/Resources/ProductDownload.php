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

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceModelInterface;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;

class ProductDownload extends AbstractResourceModel implements ResourceModelInterface {

	protected $id;
	protected $license_id;
	protected $activation_id;
	protected $source;
	protected $ip_address;
	protected $user_agent;
	protected $meta_data;
	protected $created_at;
	protected $updated_at;

	/**
	 * LicenseActivation constructor.
	 *
	 * @param \stdClass $activation
	 */
	public function __construct( $activation ) {

		if ( ! $activation instanceof \stdClass ) {
			return;
		}

		$this->id            = (int) $activation->id;
		$this->license_id    = (int) $activation->license_id;
		$this->activation_id = (int) $activation->activation_id;
		$this->source        = $activation->source;
		$this->ip_address    = $activation->ip_address;
		$this->user_agent    = $activation->user_agent;
		$this->meta_data     = JsonFormatter::decode( $activation->meta_data, true );
		$this->created_at    = $activation->created_at;
		$this->updated_at    = $activation->updated_at;

	}

	public function getId() {
		return $this->id;
	}

	public function getLicenseId() {
		return $this->license_id;
	}

	public function getActivationId() {
		return $this->activation_id;
	}

	public function getSource() {
		return $this->source;
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

	public function setId( $id ) {
		$this->id = $id;
	}

	public function setLicenseId( $id ) {
		$this->license_id = $id;
	}

	public function setActivationId( $id ) {
		$this->activation_id = $id;
	}

	public function setSource( $src ) {
		$this->source = $src;
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

}
