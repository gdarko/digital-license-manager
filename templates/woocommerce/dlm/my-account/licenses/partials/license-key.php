<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for displaying single license key in My Account area
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/my-account/licenses/partials/license-key.php
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 *
 * Default variables
 *
 * @var License $license
 */

defined( 'ABSPATH' ) || exit;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;

$markup = apply_filters( 'dlm_myaccount_license_key_markup', null, $license );

?>

<div class="dlm-myaccount-license-key">
    <?php if ( ! empty( $markup ) ): ?>
        <?php echo wp_kses( $markup, \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags() ); ?>
    <?php else: ?>
        <span class="dlm-myaccount-license-key-action dlm-myaccount-license-key-copy" title="<?php _e( 'Copy to clipboard', 'digital-license-manager' ); ?>"><?php echo esc_html( $license->getDecryptedLicenseKey() ); ?></span>
    <?php endif; ?>
</div>
