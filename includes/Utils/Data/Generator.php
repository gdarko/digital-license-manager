<?php


namespace IdeoLogix\DigitalLicenseManager\Utils\Data;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use WP_Error;


class Generator {

	/**
	 * Returns a single generator from the database.
	 *
	 * @param int $generatorId
	 *
	 * @return GeneratorResourceModel|WP_Error
	 */
	public static function find( $generatorId ) {
		/** @var GeneratorResourceModel $generator */
		$generator = GeneratorResourceRepository::instance()->find( (int) $generatorId );

		if ( ! $generator ) {
			return new WP_Error( 'data_error', __( 'The generator could not be found.', 'digital-license-manager' ), array( 'code' => 404 ) );
		}

		return $generator;
	}

	/**
	 * Retrieves multiple generators by a query array.
	 *
	 * @param array $query Key/value pairs with the generator table column names as keys
	 *
	 * @return GeneratorResourceModel[]|WP_Error
	 */
	public static function get( $query = array() ) {
		/** @var GeneratorResourceModel[] $generators */
		$generators = empty( $query ) ? GeneratorResourceRepository::instance()->findAll() : GeneratorResourceRepository::instance()->findAllBy( $query );

		if ( ! $generators ) {
			return new WP_Error( 'data_error', __( 'No generators found for your query', 'digital-license-manager' ), array( 'code' => 404 ) );
		}

		return $generators;
	}

	/**
	 * Deletes generators from the database.
	 *
	 * @param int|int[] $id A single generator ID, or an array of generator IDs
	 *
	 * @return bool|WP_Error
	 */
	public static function delete( $id ) {
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
	 * Create generator in the database and enforce validation
	 *
	 * @param array $params
	 *
	 * @return bool|AbstractResourceModel|GeneratorResourceModel|WP_Error
	 */
	public static function create( $params = array() ) {

		// Validate request.
		if ( empty( $params['name'] ) || ! is_string( $params['name'] ) ) {
			return new WP_Error( 'data_error', __( 'Generator name is missing.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( empty( $params['charset'] ) || ! is_string( $params['charset'] ) ) {
			return new WP_Error( 'data_error', __( 'The charset is invalid.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( empty( $params['chunks'] ) || ! is_numeric( $params['chunks'] ) ) {
			return new WP_Error( 'data_error', __( 'Only integer values allowed for chunks.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( $params['chunks'] < 0 || $params['chunks'] > 100 ) {
			return new WP_Error( 'data_error', __( 'Chunks should be between 1 and 99', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( empty( $params['chunk_length'] ) || ! is_numeric( $params['chunk_length'] ) ) {
			return new WP_Error( 'data_error', __( 'Only integer values allowed for chunk length.', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		if ( $params['chunk_length'] < 2 || $params['chunk_length'] > 100 ) {
			return new WP_Error( 'data_error', __( 'Chunk length should be between 2 and 99', 'digital-license-manager' ), array( 'code' => '422' ) );
		}

		$expiresIn = null;
		if ( ! empty( $params['expires_in'] ) ) {
			if ( is_numeric( $params['expires_in'] ) && $params['expires_in'] > 1 ) {
				$expiresIn = absint( $params['expires_in'] );
			} else {
				return new WP_Error( 'data_error', __( 'Expires in should be numeric and positive value larger than 1', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		$maxActivations = null;
		if ( ! empty( $params['activations_limit'] ) ) {
			if ( is_numeric( $params['activations_limit'] ) && $params['activations_limit'] > 0 ) {
				$maxActivations = absint( $params['activations_limit'] );
			} else {
				return new WP_Error( 'data_error', __( 'Time activated max should be numeric and positive value larger than 0', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		// Save the generator.
		$generator = GeneratorResourceRepository::instance()->insert(
			array(
				'name'              => sanitize_text_field( $params['name'] ),
				'charset'           => sanitize_text_field( $params['charset'] ),
				'chunks'            => absint( $params['chunks'] ),
				'chunk_length'      => absint( $params['chunk_length'] ),
				'activations_limit' => $maxActivations,
				'separator'         => isset( $params['separator'] ) ? sanitize_text_field( $params['separator'] ) : null,
				'prefix'            => isset( $params['prefix'] ) ? sanitize_text_field( $params['prefix'] ) : null,
				'suffix'            => isset( $params['suffix'] ) ? sanitize_text_field( $params['suffix'] ) : null,
				'expires_in'        => $expiresIn
			)
		);

		if ( ! $generator ) {
			return new WP_Error( 'server_error', __( 'The generator could not be created.', 'digital-license-manager' ), array( 'code' => 500 ) );
		}


		return $generator;
	}

	/**
	 * Create generator in the database and enforce validation
	 *
	 * @param $id
	 * @param $params
	 *
	 * @return bool|AbstractResourceModel|GeneratorResourceModel|WP_Error
	 */
	public static function update( $id, $params ) {

		$updateData = array();

		// Validate id
		$generator = self::find( $id );
		if ( is_wp_error( $generator ) ) {
			return $generator;
		}

		// Validate request.
		if ( array_key_exists( 'name', $params ) ) {
			if ( ! empty( $params['name'] ) ) {
				$updateData['name'] = sanitize_text_field( $params['name'] );
			} else {
				return new WP_Error( 'data_error', __( 'Name can not be empty.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'charset', $params ) ) {
			if ( ! empty( $params['charset'] ) ) {
				$updateData['charset'] = sanitize_text_field( $params['charset'] );
			} else {
				return new WP_Error( 'data_error', __( 'Charset can not be empty.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'chunks', $params ) ) {
			if ( is_numeric( $params['chunks'] ) && $params['chunks'] > 0 && $params['chunks'] < 100 ) {
				$updateData['chunks'] = (int) $params['chunks'];
			} else {
				return new WP_Error( 'data_error', __( 'Only integer values between 1 and 99 are allowed for chunks.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'chunk_length', $params ) ) {
			if ( is_numeric( $params['chunk_length'] ) && $params['chunk_length'] > 0 && $params['chunk_length'] < 10 ) {
				$updateData['chunk_length'] = (int) $params['chunk_length'];
			} else {
				return new WP_Error( 'data_error', __( 'Only integer values between 1 and 99 are allowed for chunk_length.', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'activations_limit', $params ) ) {
			if ( is_numeric( $params['activations_limit'] ) && $params['activations_limit'] > 0 ) {
				$updateData['activations_limit'] = (int) $params['activations_limit'];
			} else {
				return new WP_Error( 'data_error', __( 'Activations Limit should positive integer value larger than 0', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}

		if ( array_key_exists( 'expires_in', $params ) ) {
			if ( is_numeric( $params['expires_in'] ) && $params['expires_in'] > 1 ) {
				$updateData['expires_in'] = (int) $params['expires_in'];
			} else {
				return new WP_Error( 'data_error', __( 'Expires In shoudld be positive integer value larger than 1 that represents number of days', 'digital-license-manager' ), array( 'code' => '422' ) );
			}
		}


		if ( array_key_exists( 'separator', $params ) ) {
			if ( ! empty( $params['separator'] ) ) {
				if ( 1 === strlen( $params['separator'] ) ) {
					$updateData['separator'] = $params['separator'];
				} else {
					return new WP_Error( 'data_error', __( 'Separator should be only one character', 'digital-license-manager' ), array( 'code' => '422' ) );
				}
			}
		}

		if ( array_key_exists( 'prefix', $params ) ) {
			if ( ! empty( $params['prefix'] ) ) {
				$updateData['prefix'] = sanitize_text_field( $params['prefix'] );
			}
		}

		if ( array_key_exists( 'suffix', $params ) ) {
			if ( ! empty( $params['suffix'] ) ) {
				$updateData['suffix'] = sanitize_text_field( $params['suffix'] );
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
	 * Generate a single license string
	 *
	 * @param string $charset Character map from which the license will be generated
	 * @param int $chunks Number of chunks
	 * @param int $chunkLength The length of an individual chunk
	 * @param string $separator Separator used
	 * @param string $prefix Prefix used
	 * @param string $suffix Suffix used
	 *
	 * @return string
	 */
	private static function generateLicenseString( $charset, $chunks, $chunkLength, $separator, $prefix, $suffix ) {

		$charsetLength = strlen( $charset );
		$licenseString = $prefix;

		// loop through the chunks
		for ( $i = 0; $i < $chunks; $i ++ ) {
			// add n random characters from $charset to chunk, where n = $chunkLength
			for ( $j = 0; $j < $chunkLength; $j ++ ) {
				$licenseString .= $charset[ rand( 0, $charsetLength - 1 ) ];
			}
			// do not add the separator on the last iteration
			if ( $i < $chunks - 1 ) {
				$licenseString .= $separator;
			}
		}

		$licenseString .= $suffix;

		return $licenseString;
	}

	/**
	 * Bulk create license keys, if possible for given parameters.
	 *
	 * @param int $amount Number of license keys to be generated
	 * @param GeneratorResourceModel $generator Generator used for the license keys
	 * @param array $licenses Number of license keys to be generated
	 *
	 * @return array|WP_Error
	 */
	public static function generateLicenseKeys( $amount, $generator, $licenses = array() ) {

		// check if it's possible to create as many combinations using the input args
		$uniqueCharacters = count( array_unique( str_split( $generator->getCharset() ) ) );
		$maxPossibleKeys  = pow( $uniqueCharacters, $generator->getChunks() * $generator->getChunkLength() );

		if ( $amount > $maxPossibleKeys ) {
			return new WP_Error( 'data_error', __( 'It\'s not possible to generate that many keys with the given parameters, there are not enough combinations. Please review your inputs.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		// Generate the license strings
		for ( $i = 0; $i < $amount; $i ++ ) {
			$licenses[] = self::generateLicenseString(
				$generator->getCharset(),
				$generator->getChunks(),
				$generator->getChunkLength(),
				$generator->getSeparator(),
				$generator->getPrefix(),
				$generator->getSuffix()
			);
		}

		// Remove duplicate entries from the array
		$licenses = array_unique( $licenses );

		// check if any licenses have been removed
		if ( count( $licenses ) < $amount ) {
			// regenerate removed license keys, repeat until there are no duplicates
			while ( count( $licenses ) < $amount ) {
				$licenses = self::generateLicenseKeys( ( $amount - count( $licenses ) ), $generator, $licenses );
			}
		}

		// Reindex and return the array
		return array_values( $licenses );
	}

}
