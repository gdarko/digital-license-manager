<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use WP_Error;

interface ServiceInterface {

	/**
	 * Find a single item from the database.
	 *
	 * @param mixed $id
	 *
	 * @return AbstractResourceModel|\WP_Error
	 */
	public function find( $id );


	/**
	 * Retrieves single item from the database by ID
	 *
	 * @param $id
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function findById( $id );

	/**
	 * Retrieves multiple items by a query array.
	 *
	 * @param array $query
	 *
	 * @return AbstractResourceModel[]|WP_Error
	 */
	public function get( $query = array() );


	/**
	 * Creates a new entry to the database
	 *
	 * @param array $data
	 *
	 * @return AbstractResourceModel|\WP_Error
	 */
	public function create( $data = array() );


	/**
	 * Updates specific entry in the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function update( $id, $data = array() );


	/**
	 * Deletes specific entry from the database
	 *
	 * @param int|int[] $id
	 *
	 * @return bool|WP_Error
	 */
	public function delete( $id );


}