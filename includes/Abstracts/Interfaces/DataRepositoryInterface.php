<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces;

interface DataRepositoryInterface {

	/**
	 * Create object in the database
	 *
	 * @param array $data
	 *
	 * @return object
	 */
	public function create( $data );

	/**
	 * Find single object in the database
	 *
	 * @param array|int $where
	 *
	 * @return object
	 */
	public function find( $where );

	/**
	 * Get all the data from the database by specific parameters
	 *
	 * @param array $where
	 * @param null $sortBy
	 * @param null $sortDir
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public function get( $where = [], $sortBy = null, $sortDir = null, $offset = - 1, $limit = - 1 );

	/**
	 * Updates single object in the database
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return object
	 */
	public function update( $id, $data );

	/**
	 * Updates objects in the database by specific parameters
	 *
	 * @param array $where
	 * @param array $data
	 *
	 * @return object
	 */
	public function updateBy( $where, $data );

	/**
	 * Updates single object in the database
	 *
	 * @param array $where
	 * @param array $data
	 *
	 * @return object
	 */
	public function updateWhere( $where, $data );

	/**
	 * Deletes single object from the database
	 *
	 * @param array $ids
	 *
	 * @return false|int
	 */
	public function delete( $ids );

	/**
	 * Deletes objects from the databases by specific parameters
	 *
	 * @param array $where
	 *
	 * @return false|int
	 */
	public function deleteBy( $where );

	/**
	 * Counts rows in the database
	 * @return int
	 */
	public function count();

	/**
	 * Counts row in the database by specific parameters
	 *
	 * @param array $where
	 *
	 * @return int
	 */
	public function countBy( $where );

	/**
	 * Returns the table name
	 * @return string
	 */
	public function getTable();

}