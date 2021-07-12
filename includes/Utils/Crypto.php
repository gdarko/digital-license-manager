<?php

namespace IdeoLogix\DigitalLicenseManager\Utils;

use IdeoLogix\DigitalLicenseManager\Abstracts\Singleton;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto as DefuseCrypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class Crypto extends Singleton {

	/**
	 * The defuse key file name.
	 */
	const DEFUSE_FILE = 'defuse.txt';

	/**
	 * The secret file name.
	 */
	const SECRET_FILE = 'secret.txt';

	/**
	 * Folder name inside the wp_contents directory where the cryptographic secrets are stored.
	 */
	const PLUGIN_SLUG = 'dlm-files';

	/**
	 * The defuse key file content.
	 *
	 * @var string
	 */
	private $keyAscii;

	/**
	 * The hashing key
	 *
	 * @var string
	 */
	private $keySecret;

	/**
	 * Directory path to the plugin folder inside wp-content/uploads.
	 *
	 * @var string
	 */
	private $uploads_dir;

	/**
	 * Setup Constructor.
	 */
	public function __construct() {
		$uploads = wp_upload_dir( null, false );

		$this->uploads_dir = $uploads['basedir'] . '/dlm-files/';
		$this->setDefuse();
		$this->setSecret();

	}

	/**
	 * Sets the defuse encryption key.
	 */
	private function setDefuse() {
		/* When the cryptographic secrets are loaded into these constants, no other files are needed */
		if ( defined( 'DLM_PLUGIN_DEFUSE' ) ) {
			$this->keyAscii = DLM_PLUGIN_DEFUSE;

			return;
		}

		if ( file_exists( $this->uploads_dir . self::DEFUSE_FILE ) ) {
			$this->keyAscii = file_get_contents( $this->uploads_dir . self::DEFUSE_FILE );
		}
	}

	/**
	 * Sets the cryptographic secret.
	 */
	private function setSecret() {
		/* When the cryptographic secrets are loaded into these constants, no other files are needed */
		if ( defined( 'DLM_PLUGIN_SECRET' ) ) {
			$this->keySecret = DLM_PLUGIN_SECRET;

			return;
		}

		if ( file_exists( $this->uploads_dir . self::SECRET_FILE ) ) {
			$this->keySecret = file_get_contents( $this->uploads_dir . self::SECRET_FILE );
		}
	}

	/**
	 * Load the defuse key from the plugin folder.
	 *
	 * @return Key|string
	 * @throws EnvironmentIsBrokenException
	 *
	 * @throws BadFormatException
	 */
	private function loadEncryptionKeyFromConfig() {
		if ( ! $this->keyAscii ) {
			return '';
		}

		return Key::loadFromAsciiSafeString( $this->keyAscii );
	}

	/**
	 * Encrypt a string and return the encrypted cipher text.
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws EnvironmentIsBrokenException
	 *
	 * @throws BadFormatException
	 */
	public function encryptValue( $value ) {
		return DefuseCrypto::encrypt( $value, $this->loadEncryptionKeyFromConfig() );
	}

	/**
	 * Decrypt a cipher and return the decrypted value.
	 *
	 * @param string $cipher
	 *
	 * @return string
	 * @throws EnvironmentIsBrokenException
	 *
	 * @throws BadFormatException
	 */
	public function decryptCipher( $cipher ) {
		if ( ! $cipher ) {
			return '';
		}

		try {
			return DefuseCrypto::decrypt( $cipher, $this->loadEncryptionKeyFromConfig() );
		} catch ( WrongKeyOrModifiedCiphertextException $ex ) {
			// An attack! Either the wrong key was loaded, or the cipher text has changed since it was created -- either
			// corrupted in the database or intentionally modified by someone trying to carry out an attack.
		}

		return null;
	}

	/**
	 * Hashes the given string using the HMAC-SHA256 method.
	 *
	 * @param string $value
	 *
	 * @return false|string
	 */
	public function hashValue( $value ) {
		return hash_hmac( 'sha256', $value, $this->keySecret );
	}

	/**
	 * Encrypt a string and return the encrypted cipher text.
	 *
	 * @param $value
	 *
	 * @return string|WP_Error
	 *
	 */
	public static function encrypt( $value ) {
		try {
			return self::instance()->encryptValue( $value );
		} catch ( \Exception $e ) {
			return ( new WP_Error( 'server_error', sprintf( __( 'Unable to encrypt value: %s', 'digital-license-manager' ), $e->getMessage() ), array( 'code' => 500 ) ) );
		}
	}

	/**
	 * Decrypt a cipher and return the decrypted value.
	 *
	 * @param $cipher
	 *
	 * @return string|WP_Error
	 */
	public static function decrypt( $cipher ) {
		try {
			return self::instance()->decryptCipher( $cipher );
		} catch ( \Exception $e ) {
			return ( new WP_Error( 'server_error', sprintf( __( 'Unable to decrypt value: %s', 'digital-license-manager' ), $e->getMessage() ), array( 'code' => 500 ) ) );
		}
	}

	/**
	 * Hashes the given string using the HMAC-SHA256 method.
	 *
	 * @param string $value
	 *
	 * @return false|string
	 */
	public static function hash( $value ) {
		return self::instance()->hashValue( $value );
	}
}