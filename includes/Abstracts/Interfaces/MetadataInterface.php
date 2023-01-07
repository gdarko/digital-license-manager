<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces;

interface MetadataInterface {

	/**
	 * Add metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @return mixed|bool
	 */
	public function addMeta( $id, $key, $value );

	/**
	 * Returns metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $single
	 *
	 * @return mixed|mixed[]|bool
	 */
	public function getMeta( $id, $key, $single = false );


	/**
	 * Update metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 * @param $previousValue
	 *
	 * @return bool
	 */
	public function updateMeta( $id, $key, $value, $previousValue = null );


	/**
	 * Delete metadata for specific record
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function deleteMEta( $id, $key, $value = null );

}