<?php


namespace IdeoLogix\DigitalLicenseManager\Core\Services;

use IdeoLogix\DigitalLicenseManager\Core\Generators\StandardGenerator;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ServiceInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use WP_Error;

class GeneratorsService implements ServiceInterface {

	/**
	 * Find a single item from the database.
	 *
	 * @param mixed
	 *
	 * @return AbstractResourceModel|GeneratorResourceModel|\WP_Error
	 */
	public function find( $id ) {
		/** @var GeneratorResourceModel $generator */
		$generator = GeneratorResourceRepository::instance()->find( (int) $id );

		if ( ! $generator ) {
			return new WP_Error( 'data_error', __( 'The generator could not be found.', 'digital-license-manager' ), array( 'code' => 404 ) );
		}

		return $generator;
	}

	/**
	 * Retrieves single item from the database by ID
	 *
	 * @param $id
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function findById( $id ) {
		return $this->find( $id );
	}

	/**
	 * Retrieves multiple items by a query array.
	 *
	 * @param array $query Key/value pairs with the generator table column names as keys
	 *
	 * @return AbstractResourceModel[]|GeneratorResourceModel[]|WP_Error
	 */
	public function get( $query = array() ) {
		/** @var GeneratorResourceModel[] $generators */
		$generators = empty( $query ) ? GeneratorResourceRepository::instance()->findAll() : GeneratorResourceRepository::instance()->findAllBy( $query );

		if ( ! $generators ) {
			return new WP_Error( 'data_error', __( 'No generators found for your query', 'digital-license-manager' ), array( 'code' => 404 ) );
		}

		return $generators;
	}


	/**
	 * Creates a new entry to the database
	 *
	 * @param array $data
	 *
	 * @return AbstractResourceModel|GeneratorResourceModel|\WP_Error
	 */
	public function create( $data = array() ) {

		// Validate request.
		if ( empty( $data['name'] ) || ! is_string( $data['name'] ) ) {
			return new WP_Error( 'data_error', __( 'Generator name is missing.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( empty( $data['charset'] ) || ! is_string( $data['charset'] ) ) {
			return new WP_Error( 'data_error', __( 'The charset is invalid.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( empty( $data['chunks'] ) || ! is_numeric( $data['chunks'] ) ) {
			return new WP_Error( 'data_error', __( 'Only integer values allowed for chunks.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( $data['chunks'] < 0 || $data['chunks'] > 100 ) {
			return new WP_Error( 'data_error', __( 'Chunks should be between 1 and 99', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( empty( $data['chunk_length'] ) || ! is_numeric( $data['chunk_length'] ) ) {
			return new WP_Error( 'data_error', __( 'Only integer values allowed for chunk length.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( $data['chunk_length'] < 2 || $data['chunk_length'] > 100 ) {
			return new WP_Error( 'data_error', __( 'Chunk length should be between 2 and 99', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		$expiresIn = null;
		if ( ! empty( $data['expires_in'] ) ) {
			if ( is_numeric( $data['expires_in'] ) && $data['expires_in'] > 1 ) {
				$expiresIn = absint( $data['expires_in'] );
			} else {
				return new WP_Error( 'data_error', __( 'Expires in should be numeric and positive value larger than 1', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		$maxActivations = null;
		if ( ! empty( $data['activations_limit'] ) ) {
			if ( is_numeric( $data['activations_limit'] ) && $data['activations_limit'] > 0 ) {
				$maxActivations = absint( $data['activations_limit'] );
			} else {
				return new WP_Error( 'data_error', __( 'Time activated max should be numeric and positive value larger than 0', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		// Save the generator.
		$generator = GeneratorResourceRepository::instance()->insert(
			array(
				'name'              => sanitize_text_field( $data['name'] ),
				'charset'           => sanitize_text_field( $data['charset'] ),
				'chunks'            => absint( $data['chunks'] ),
				'chunk_length'      => absint( $data['chunk_length'] ),
				'activations_limit' => $maxActivations,
				'separator'         => isset( $data['separator'] ) ? sanitize_text_field( $data['separator'] ) : null,
				'prefix'            => isset( $data['prefix'] ) ? sanitize_text_field( $data['prefix'] ) : null,
				'suffix'            => isset( $data['suffix'] ) ? sanitize_text_field( $data['suffix'] ) : null,
				'expires_in'        => $expiresIn
			)
		);

		if ( ! $generator ) {
			return new WP_Error( 'server_error', __( 'The generator could not be created.', 'digital-license-manager' ), array( 'code' => 500 ) );
		}


		return $generator;
	}

	/**
	 * Updates specific entry in the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return AbstractResourceModel|GeneratorResourceModel|WP_Error
	 */
	public function update( $id, $data = [] ) {

		$updateData = array();

		// Validate id
		$generator = $this->findById( $id );
		if ( is_wp_error( $generator ) ) {
			return $generator;
		}

		// Validate request.
		if ( array_key_exists( 'name', $data ) ) {
			if ( ! empty( $data['name'] ) ) {
				$updateData['name'] = sanitize_text_field( $data['name'] );
			} else {
				return new WP_Error( 'data_error', __( 'Name can not be empty.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'charset', $data ) ) {
			if ( ! empty( $data['charset'] ) ) {
				$updateData['charset'] = sanitize_text_field( $data['charset'] );
			} else {
				return new WP_Error( 'data_error', __( 'Charset can not be empty.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'chunks', $data ) ) {
			if ( is_numeric( $data['chunks'] ) && $data['chunks'] > 0 && $data['chunks'] < 100 ) {
				$updateData['chunks'] = (int) $data['chunks'];
			} else {
				return new WP_Error( 'data_error', __( 'Only integer values between 1 and 99 are allowed for chunks.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'chunk_length', $data ) ) {
			if ( is_numeric( $data['chunk_length'] ) && $data['chunk_length'] > 0 && $data['chunk_length'] < 10 ) {
				$updateData['chunk_length'] = (int) $data['chunk_length'];
			} else {
				return new WP_Error( 'data_error', __( 'Only integer values between 1 and 99 are allowed for chunk_length.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'activations_limit', $data ) ) {
			if ( is_numeric( $data['activations_limit'] ) && $data['activations_limit'] > 0 ) {
				$updateData['activations_limit'] = (int) $data['activations_limit'];
			} else {
				return new WP_Error( 'data_error', __( 'Activations Limit should positive integer value larger than 0', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'expires_in', $data ) ) {
			if ( is_numeric( $data['expires_in'] ) && $data['expires_in'] > 1 ) {
				$updateData['expires_in'] = (int) $data['expires_in'];
			} else {
				return new WP_Error( 'data_error', __( 'Expires In shoudld be positive integer value larger than 1 that represents number of days', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}


		if ( array_key_exists( 'separator', $data ) ) {
			if ( ! empty( $data['separator'] ) ) {
				if ( 1 === strlen( $data['separator'] ) ) {
					$updateData['separator'] = $data['separator'];
				} else {
					return new WP_Error( 'data_error', __( 'Separator should be only one character', 'digital-license-manager' ), array( 'code' => '422' ) );
				}
			}
		}

		if ( array_key_exists( 'prefix', $data ) ) {
			if ( ! empty( $data['prefix'] ) ) {
				$updateData['prefix'] = sanitize_text_field( $data['prefix'] );
			}
		}

		if ( array_key_exists( 'suffix', $data ) ) {
			if ( ! empty( $data['suffix'] ) ) {
				$updateData['suffix'] = sanitize_text_field( $data['suffix'] );
			}
		}

		// Update the generator.
		$generator = GeneratorResourceRepository::instance()->update( $id, $updateData );

		if ( ! $generator ) {
			return new WP_Error( 'server_error', __( 'The generator could not be created.', 'digital-license-manager' ), array( 'code' => 500 ) );
		}

		return $generator;

	}

	/**
	 * Deletes specific entry from the database
	 *
	 * @param int|int[] $id
	 *
	 * @return bool|WP_Error
	 */
	public function delete( $id ) {
		if ( ! is_array( $id ) ) {
			$id = (array) $id;
		}

		/** @var GeneratorResourceModel $generator */
		$generator = GeneratorResourceRepository::instance()->delete( $id );

		if ( ! $generator ) {
			return new WP_Error( 'server_error', __( 'The generator(s) could not be deleted.', 'digital-license-manager' ), array( 'code' => 500 ) );
		}

		return true;
	}


	/**
	 * Returns the available generator implementation utility class for generating licenses
	 *
	 * @param GeneratorResourceModel $generator
	 * @param \WC_Order|null $order
	 * @param \WC_Product|null $product
	 *
	 * @return StandardGenerator|mixed
	 */
	public function getGeneratorUtilityInstance( $generator, $order = null, $product = null ) {

		/**
		 * Determines the generator PHP class, this class should implement AbstractGenerator.
		 *
		 * @param $className
		 * @param $generator
		 * @param $order
		 * @param $product
		 */
		$className = apply_filters( 'dlm_generator_class', StandardGenerator::class, $generator, $order, $product );
		if ( ! class_exists( $className ) ) {
			$className = StandardGenerator::class;
		}

		return ( new $className( $generator ) );

	}

	/**
	 * Bulk create license keys, if possible for given parameters.
	 *
	 * @param int $amount Number of license keys to be generated
	 * @param GeneratorResourceModel $generator Generator used for the license keys
	 * @param array $licenses Number of license keys to be generated
	 * @param \WC_Order|null $order
	 * @param \WC_Product|null $product
	 *
	 * @return array|WP_Error
	 */
	public function generateLicenses( $amount, $generator, $licenses = array(), $order = null, $product = null ) {

		$generatorInstance = $this->getGeneratorUtilityInstance( $generator, $order, $product );

		return $generatorInstance->generate( $amount, $licenses );
	}


}
