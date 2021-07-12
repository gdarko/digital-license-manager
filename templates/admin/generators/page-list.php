<?php

use IdeoLogix\DigitalLicenseManager\ListTables\Generators;

defined( 'ABSPATH' ) || exit;

/**
 * @var string $addGeneratorUrl
 * @var string $generateKeysUrl
 * @var Generators $generators
 */

?>

<h1 class="wp-heading-inline"><?php esc_html_e( 'Generators', 'digital-license-manager' ); ?></h1>
<?php if ( current_user_can( 'dlm_create_generators' ) ): ?>
    <a href="<?php echo esc_url( $addGeneratorUrl ); ?>" class="page-title-action">
        <span><?php esc_html_e( 'Add new', 'digital-license-manager' ); ?></span>
    </a><a href="<?php echo esc_url( $generateKeysUrl ); ?>" class="page-title-action">
        <span><?php esc_html_e( 'Generate', 'digital-license-manager' ); ?></span>
    </a>
<?php endif; ?>

<hr class="wp-header-end">

<form method="post">
	<?php
	$generators->prepare_items();
	$generators->display();
	?>
</form>
