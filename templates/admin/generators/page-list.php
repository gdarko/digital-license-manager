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
	$generators->search_box( __( 'Search key', 'digital-license-manager' ), 'key' );
	$generators->display();
	?>
</form>
