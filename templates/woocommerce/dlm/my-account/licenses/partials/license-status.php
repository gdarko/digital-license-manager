<?php
/**
 * Copyright (C) 2025 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2025 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for the overview of all customer licenses, across all orders, inside "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/my-account/licenses/partials/license-status.php
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 *
 * Default variables
 *
 * @var $license  \IdeoLogix\DigitalLicenseManager\Database\Models\License
 */

use IdeoLogix\DigitalLicenseManager\Enums\LicensePublicStatus;
use IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper;

echo wp_kses( LicensePublicStatus::toHtml( $license, [
	'inline' => isset( $inline ) ? $inline : false
] ), SanitizeHelper::ksesAllowedHtmlTags() );
