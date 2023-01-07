<?php

namespace IdeoLogix\DigitalLicenseManager\Core\Generators;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractGenerator;

use WP_Error;

class StandardGenerator extends AbstractGenerator {

	/**
	 * Generate list of licenses needed.
	 *
	 * @param $amount - Needed amount of licenses
	 * @param array $licenses - List of existing licenses
	 *
	 * @return WP_Error|array
	 */
	public function generate( $amount, $licenses = [] ) {
		// check if it's possible to create as many combinations using the input args
		$uniqueCharacters = count( array_unique( str_split( $this->generator->getCharset() ) ) );
		$maxPossibleKeys  = pow( $uniqueCharacters, $this->generator->getChunks() * $this->generator->getChunkLength() );

		if ( $amount > $maxPossibleKeys ) {
			return new WP_Error( 'data_error', __( 'It\'s not possible to generate that many keys with the given parameters, there are not enough combinations. Please review your inputs.', 'digital-license-manager' ), array( 'code' => 422 ) );
		}

		// Generate the license strings
		for ( $i = 0; $i < $amount; $i ++ ) {
			$licenses[] = $this->generate_licenses(
				$this->generator->getCharset(),
				$this->generator->getChunks(),
				$this->generator->getChunkLength(),
				$this->generator->getSeparator(),
				$this->generator->getPrefix(),
				$this->generator->getSuffix()
			);
		}

		// Remove duplicate entries from the array
		$licenses = array_unique( $licenses );

		// check if any licenses have been removed
		if ( count( $licenses ) < $amount ) {
			// regenerate removed license keys, repeat until there are no duplicates
			while ( count( $licenses ) < $amount ) {
				$licenses = $this->generate( ( $amount - count( $licenses ) ), $licenses );
			}
		}

		// Reindex and return the array
		return array_values( $licenses );
	}

	/**
	 * The algorithm for generating licenses based on the generator options
	 *
	 * @param $charset
	 * @param $chunks
	 * @param $chunkLength
	 * @param $separator
	 * @param $prefix
	 * @param $suffix
	 *
	 * @return string
	 */
	private static function generate_licenses( $charset, $chunks, $chunkLength, $separator, $prefix, $suffix ) {

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
}