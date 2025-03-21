<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for the "Download" button of the license certificate functionality in "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/myaccount/licenses/partials/single-certificate-button.php
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
 * @var \IdeoLogix\DigitalLicenseManager\Database\Models\License $license
 */
?>

<form id="dlm-license-certificate-download" action="<?php echo esc_url( \IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\MyAccount::getProcessingEndpointUrl() ); ?>" method="POST" class="dlm-list-inline-mb-0">
    <input type="hidden" name="dlm_action" value="license_certificate_download">
    <input type="hidden" name="dlm_nonce" value="<?php echo wp_create_nonce('dlm_account'); ?>">
    <input type="hidden" name="license" value="<?php echo esc_attr($license->getDecryptedLicenseKey()); ?>">
    <button type="submit" class="woocommerce-button button dlm-button" name="license_certificate_download" value="1">
        <span class="dlm-icon-file-pdf"></span> <?php esc_html_e( 'Download', 'digital-license-manager' ); ?>
    </button>
</form>
