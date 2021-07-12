<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

defined('ABSPATH') || exit;

abstract class Singleton
{
    /**
     * @var $this[]
     */
    protected static $instance = array();

    /**
     * @return $this
     */
    public static function instance()
    {
        $class = get_called_class();

        if (!array_key_exists($class, self::$instance)) {
            self::$instance[$class] = new $class();
        }

        return self::$instance[$class];
    }
}