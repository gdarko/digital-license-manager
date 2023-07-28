<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

/**
 * Class Singleton
 * @package IdeoLogix\DigitalLicenseManager\Abstracts
 *
 * @depreacted 1.5.0 - Deprecated in favor of Traits/Singleton
 */
class Singleton {

	/**
	 * The instance object
	 * @var self
	 */
	private static $instances = [];

	/**
	 * @return $this
	 */
	public static function instance()
	{
		$calledClass = get_called_class();

		if ( ! isset( self::$instances[ $calledClass ] ) ) {
			self::$instances[ $calledClass ] = new $calledClass();
		}

		return self::$instances[ $calledClass ];
	}
}