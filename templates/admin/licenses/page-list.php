<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2024  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-2024  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
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

defined('ABSPATH') || exit;
?>

<hr class="wp-header-end">

<h1 class="wp-heading-inline"><?php esc_html_e('Licenses', 'digital-license-manager'); ?></h1>
<a class="page-title-action" href="<?php echo esc_url($addLicenseUrl); ?>">
    <span><?php esc_html_e('Add new', 'digital-license-manager');?></span>
</a>
<a class="page-title-action" href="<?php echo esc_url($importLicenseUrl); ?>">
    <span><?php esc_html_e('Import', 'digital-license-manager');?></span>
</a>

<form method="post" id="dlm-license-table">
    <?php
        $licenses->prepare_items();
        $licenses->views();
        $licenses->search_box(esc_html__( 'Search licenses', 'digital-license-manager' ), 'license_key');
        $licenses->display();
    ?>
</form>
