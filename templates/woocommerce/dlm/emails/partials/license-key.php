<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for displaying single license key in My Account area
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/emails/partials/license-key.php
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

$markup = apply_filters( 'dlm_emails_license_key_markup', null, $license );

?>

<?php if ( ! empty( $markup ) ): ?>
	<?php echo wp_kses( $markup, \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags() ); ?>
<?php else: ?>
	<code><?php echo esc_html( $license->getDecryptedLicenseKey() ); ?></code>
<?php endif; ?>

