<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\DataRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Traits\Singleton;
use IdeoLogix\DigitalLicenseManager\Utils\ArrayFormatter;
use IdeoLogix\DigitalLicenseManager\Database\QueryBuilder;

class AbstractDataRepository implements DataRepositoryInterface {

	use Singleton;

	/**
	 * The column name of the primary key
	 * @var string
	 */
	protected $primaryKey;

	/**
	 * The name of the database table
	 * @var string
	 */
	protected $dataTable;

	/**
	 * The model class name
	 * @var string
	 */
	protected $dataModel;

	/**
	 * The list of searchable columns
	 * @var array
	 */
	protected $searchable;

	/**
	 * Whether timestamps are supported or not (created_at, updated_at)
	 * @var bool
	 */
	protected $timestamps = true;

	/**
	 * Creates model instance
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	private function createModel( $data ) {
		$className = $this->dataModel;

		return new $className( (array) $data );
	}

	/**
	 * Create object in the database
	 *
	 * @param array $data
	 *
	 * @return AbstractDataModel
	 */
	public function create( $data ) {

		$data = $this->prepare( $data, 'create' );

		/* @var $model AbstractDataModel */
		$model = $this->createModel( $data );

		$model->save();

		return $model;
	}

	/**
	 * Create object in the database
	 *
	 * @param array $data
	 *
	 * @depreacted  1.5.0
	 *
	 * @return object
	 */
	public function insert( $data ) {
		return $this->create( $data );
	}

	/**
	 * Find single object in the database
	 *
	 * @param $where
	 *
	 * @return object
	 */
	public function find( $where ) {

		$result = null;

		try {
			$where  = is_numeric( $where ) ? [ $this->primaryKey => $where ] : $where;
			$result = $this->queryBuilder()->from( $this->dataTable )->where( $where )->first();
		} catch ( \Exception $e ) {
		}

		if ( ! empty( $result ) ) {
			$result = $this->createModel( $result );
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Find single object in the database by specific parameters
	 *
	 * @param array $where
	 *
	 * @depreacted  1.5.0
	 *
	 * @return object
	 */
	public function findBy( $where ) {

		return $this->find( $where );
	}

	/**
	 * Get all the data from the database
	 * @depreacted 1.5.0
	 * @return array
	 */
	public function findAll() {
		return $this->get();
	}

	/**
	 * Get all the data from the database by specific parameters
	 *
	 * @depreacted 1.5.0
	 *
	 * @param array $where
	 * @param null $sortBy
	 * @param null $sortDir
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public function findAllBy( $where, $sortBy = null, $sortDir = 'DESC', $offset = - 1, $limit = - 1 ) {
		return $this->get( $where, $sortBy, $sortDir, $offset, $limit );
	}

	/**
	 * Get all the data from the database by specific parameters
	 *
	 * @param array $where
	 * @param null $sortBy
	 * @param null $sortDir
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return AbstractDataModel[]
	 */
	public function get( $where = [], $sortBy = null, $sortDir = 'DESC', $offset = - 1, $limit = - 1 ) {

		try {
			if ( count( $where ) > 0 ) {
				$where = self::buildWhere( $where );
			}

			$builder = $this->buildQuery( $where, $sortBy, $sortDir, $offset, $limit );

			$result = $builder->get();

		} catch ( \Exception $e ) {
			$result = null;
		}

		$newResults = [];
		if ( ! empty( $result ) ) {
			foreach ( $result as $item ) {
				$newResults[] = $this->createModel( $item );
			}
		}

		return $newResults;
	}

	/**
	 * Updates single object in the database
	 *
	 * @param mixed $where
	 * @param array $data
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function updateWhere( $where, $data ) {
		if ( empty( $where ) || empty( $data ) ) {
			return 0;
		}

		$where = $this->buildWhere( $where );
		try {
			$data   = $this->prepare( $data, 'update' );
			$result = $this->buildQuery( $where )->set( $data )->update();
		} catch ( \Exception $e ) {
			$result = 0;
		}

		return $result;
	}

	/**
	 * Updates single object in the database
	 *
	 * @param $id
	 * @param array $data
	 *
	 * @return int|object
	 */
	public function update( $id, $data ) {

		$existing = $this->find( $id );
		$changes  = 0;

		if ( ! $existing ) {
			return false;
		}

		foreach ( $data as $key => $value ) {
			if ( $existing->attributes[ $key ] != $value ) {
				$changes ++;
			}
		}

		if ( ! $changes ) {
			return $this->find( $id );
		}

		try {
			$updated = $this->updateWhere( $id, $data );
		} catch ( \Exception $e ) {
			$updated = false;
		}

		if ( $updated ) {
			return $this->find( $id );
		}

		return $updated;

	}

	/**
	 * Updates objects in the database by specific parameters
	 *
	 * @param array $where
	 * @param array $data
	 *
	 * @return int|bool
	 * @throws \Exception
	 */
	public function updateBy( $where, $data ) {
		$updated = $this->updateWhere( $where, $data );

		return $updated === 0 ? 1 : $updated; // if zero rows are affected, count it as updated.
	}

	/**
	 * Deletes single object from the database
	 *
	 * @param array|scalar $where
	 *
	 * @return false|int
	 */
	public function delete( $where ) {

		if ( empty( $where ) ) {
			return false;
		}

		try {
			$builder = $this->buildQuery( [] );
			$where   = $this->buildWhere( $where );

			return $builder->where( $where )->delete();

		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Deletes objects from the databases by specific parameters
	 *
	 * @param array $where
	 *
	 * @return false|int
	 */
	public function deleteBy( $where ) {
		return $this->delete( $where );
	}

	/**
	 * Counts rows in the database
	 * @return int
	 */
	public function count( $where = [] ) {
		try {
			return $this->buildQuery( $where )->count();
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Counts row in the database by specific parameters
	 *
	 * @param array $where
	 *
	 * @depreacted 1.5.0
	 *
	 * @return int
	 */
	public function countBy( $where ) {
		return $this->count( $where );
	}

	/**
	 * Returns the table name
	 * @return string
	 */
	public function getTable() {
		global $wpdb;

		return sprintf( '%s%s', $wpdb->prefix, $this->dataTable );
	}

	/**
	 * Returns the query builder
	 * @return QueryBuilder
	 */
	public function queryBuilder() {
		return new QueryBuilder( null );
	}

	/**
	 * Build the query using the query builder
	 *
	 * @param $where
	 * @param $sortBy
	 * @param $sortDir
	 * @param $offset
	 * @param $limit
	 *
	 * @return QueryBuilder
	 * @throws \Exception
	 */
	public function buildQuery( $where = [], $sortBy = null, $sortDir = 'DESC', $offset = - 1, $limit = - 1 ) {

		if ( empty( $sortBy ) ) {
			$sortBy = $this->primaryKey;
		}

		$builder = $this->queryBuilder()->from( $this->dataTable );

		if ( isset( $where['search'] ) ) {
			$builder = $builder->keywords( $where['search'], $this->searchable );
			unset( $where['search'] );
		}

		if ( ! empty( $where ) ) {
			$builder = $builder->where( $where );
		}

		$builder = $builder->order_by( $sortBy, $sortDir );

		if ( - 1 < $offset ) {
			$builder = $builder->offset( $offset );
		}
		if ( - 1 < $limit ) {
			$builder = $builder->limit( $limit );
		}

		return $builder;
	}

	/**
	 * Builds where query
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public function buildWhere( $query ) {

		if ( is_numeric( $query ) ) {
			$where = [ $this->primaryKey => intval( $query ) ];
		} else if ( ArrayFormatter::isList( $query ) ) {
			$where = [
				$this->primaryKey => [
					'operator' => 'IN',
					'value'    => $query,
				]
			];
		} else {

			$where = [];
			foreach ( $query as $key => $value ) {
				if ( is_array( $value ) && ArrayFormatter::isList( $value ) ) {
					$where[ $key ] = [
						'operator' => 'IN',
						'value'    => $value,
					];
				} else {
					$where[ $key ] = $value;
				}
			}
		}

		return $where;
	}

	/**
	 * Prepare the data
	 *
	 * @param $data
	 * @param string $type
	 *
	 * @return mixed
	 */
	private function prepare( $data, $type = 'create' ) {
		foreach ( $data as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				$data[ $key ] = json_encode( $value );
			}
		}

		if ( $this->timestamps ) {
			$timestampKey          = $type === 'create' ? 'created_at' : 'updated_at';
			$data[ $timestampKey ] = date( 'Y-m-d H:i:s' );
		}

		return $data;
	}
}