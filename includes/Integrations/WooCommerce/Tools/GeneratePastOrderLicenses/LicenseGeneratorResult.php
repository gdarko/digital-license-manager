<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Tools\GeneratePastOrderLicenses;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;

class LicenseGeneratorResult {
	/**
	 * The generated licenses
	 * @var array<License>
	 */
	public array $licenses = [];

	/**
	 * The total generated licenses
	 * @var int
	 */
	public int $total = 0;
}