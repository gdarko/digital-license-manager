<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces;

defined('ABSPATH') || exit();

interface ResourceModelInterface
{
    /**
     * @return array
     */
    public function toArray();
}
