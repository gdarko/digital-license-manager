<?php


use IdeoLogix\DigitalLicenseManager\ListTables\Activations;

defined( 'ABSPATH' ) || exit;

/**
 * @var Activations $activations
 */
?>

<h1 class="wp-heading-inline"><?php esc_html_e( 'Activations', 'digital-license-manager' ); ?></h1>

<hr class="wp-header-end">

<form method="post">
	<?php
	$activations->prepare_items();
	$activations->views();
	$activations->search_box(__( 'Search activations', 'digital-license-manager' ), 'license_key');
	$activations->display();
	?>
</form>
