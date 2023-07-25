<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

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
