<?php

namespace IdeoLogix\DigitalLicenseManager\Core\Services;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ServiceInterface;
use WP_Error;

class ApiKeysService implements ServiceInterface {

	/**
	 * Find a single item from the database.
	 *
	 * @param mixed $id
	 *
	 * @return AbstractResourceModel|\WP_Error
	 */
	public function find( $id ) {
		// TODO: Implement find() method.
	}

	/**
	 * Retrieves single item from the database by ID
	 *
	 * @param $id
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function findById( $id ) {
		// TODO: Implement findById() method.
	}

	/**
	 * Retrieves multiple items by a query array.
	 *
	 * @param array $query
	 *
	 * @return AbstractResourceModel[]|WP_Error
	 */
	public function get( $query = array() ) {
		// TODO: Implement get() method.
	}

	/**
	 * Creates a new entry to the database
	 *
	 * @param array $data
	 *
	 * @return AbstractResourceModel|\WP_Error
	 */
	public function create( $data = array() ) {
		// TODO: Implement create() method.
	}

	/**
	 * Updates specific entry in the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function update( $id, $data = array() ) {
		// TODO: Implement update() method.
	}

	/**
	 * Deletes specific entry from the database
	 *
	 * @param int|int[] $id
	 *
	 * @return bool|WP_Error
	 */
	public function delete( $id ) {
		// TODO: Implement delete() method.
	}

	/**
	 * Return's the API keys possible permissions
	 * @return array
	 */
	public function get_permissions() {
		return apply_filters( 'dlm_rest_api_permissions', array(
			'read'       => __( 'Read', 'digital-license-manager' ),
			'write'      => __( 'Write', 'digital-license-manager' ),
			'read_write' => __( 'Read & Write', 'digital-license-manager' ),
		) );
	}
}