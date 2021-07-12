<?php

use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;

defined( 'ABSPATH' ) || exit;

?>

<h1>
    <span><?php esc_html_e( 'REST API', 'digital-license-manager' ); ?></span>
    <a class="add-new-h2" href="<?php echo esc_url( admin_url( sprintf( 'admin.php?page=%s&tab=rest_api&create_key=1', PageSlug::SETTINGS ) ) ); ?>">
        <span><?php esc_html_e( 'Add key', 'digital-license-manager' ); ?></span>
    </a>
</h1>
<hr class="wp-header-end">

<form method="post">
	<?php
	$keys->prepare_items();
	$keys->views();
	$keys->search_box( __( 'Search key', 'digital-license-manager' ), 'key' );
	$keys->display();
	?>
</form>
