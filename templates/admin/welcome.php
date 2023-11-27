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

// Urls
use IdeoLogix\DigitalLicenseManager\Tools\Migration\Migrators\LMFW;

$url_docs      = DLM_DOCUMENTATION_URL;
$url_purchase  = DLM_PURCHASE_URL;
$url_github    = DLM_GITHUB_URL;
$url_wordpress = DLM_WP_FORUM_URL;
$url_migration = trailingslashit(DLM_DOCUMENTATION_URL) . 'migration/migrate-from-license-manager-for-woocommerce/';
?>

<div class="instructions dgv-instructions">
    <div class="dgv-instructions-card dgv-instructions-card-shadow">
        <div class="dgv-instructions-row dgv-instructions-header">
            <div class="dgv-instructions-colf">
                <p class="lead"><?php _e( 'Thanks for installing <strong class="green">Digital License Manager</strong>', 'digital-license-manager' ); ?></p>
                <p class="desc"><?php _e( 'Digital License Manager is a WordPress plugin that allows you to <strong>sell</strong> your licence keys through WooCommerce.', 'digital-license-manager' ); ?></p>
                <p class="desc"><?php _e( 'The plugin supports various features like email notifications, separate activations table, pdf certifications, import/export, WooCommerce HPOS and more.' ); ?></p>
                <p class="desc"><?php _e( 'If you found this plugin <strong>useful</strong> for your business, we will greatly appreciate if you take a minute to <a target="_blank" title="Give this plugin a good five star rating :)" href="https://wordpress.org/support/plugin/digital-license-manager/reviews/#new-post">rate it. &#9733;&#9733;&#9733;&#9733;&#9733;</a>', 'digital-license-manager' ); ?></p>
                <p class="desc"><?php _e( sprintf( '<a target="_blank" class="button button-primary" title="Plugin Documentation" href="%s">Documentation</a>', $url_docs ), 'digital-license-manager' ); ?></p>
            </div>
        </div>
        <div class="dgv-instructions-row dgv-instructions-mb-10">
			<?php if ( LMFW::isUsed() ): ?>
                <div class="dgv-instructions-colf dgv-highlighted">
                    <div class="dgv-instructions-extra">
                        <h4 class="navy"><?php _e( 'Looking to migrate from License Manager for WooCommerce?', 'digital-license-manager' ); ?></h4>
                        <p style="margin-bottom: 0;">
							<?php _e( sprintf( 'We <u>noticed</u> that you used <strong>License Manager for WooCommerce</strong> previously. If you want to migrate you data, <a target="_blank" href="%s">read more</a>.', $url_migration ), 'digital-license-manager' ); ?>
                        </p>
                    </div>
                </div>
			<?php endif; ?>
            <div class="dgv-instructions-colf">
                <div class="dgv-instructions-extra">
                    <h4 class="navy"><?php _e( 'Found problem? Report it!', 'digital-license-manager' ); ?></h4>
                    <p style="margin-bottom: 0;">
						<?php _e( sprintf( 'If you found a bug or you want to report a problem please open a support ticket <a target="_blank" href="%s">here</a> or on <a target="_blank" href="%s">Github</a>.', $url_wordpress, $url_github ), 'digital-license-manager' ); ?>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-colf">
                <div class="dgv-instructions-extra">
                    <h4 style="margin-top:0;"
                        class="navy"><?php _e( 'Need more features? try the PRO version!', 'digital-license-manager' ); ?></h4>
                    <p>
						<?php _e( sprintf( 'If you need some additional functionality like WooCommerce Subscriptions integration, Software/Release management and distribution through the REST API and more, try the <a target="_blank" href="%s">PRO version.</a>', $url_purchase ), 'digital-license-manager' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    /**
 * Instructions
 */
    .dgv-instructions {
        width: 100%;
        max-width: 98.5%;
    }

    .instructions .dgv-instructions-card {
        background: #fff;
        display: inline-block;
        position: relative;
        width: 100%;
        max-width: 100%;
    }

    .instructions .dgv-instructions-card .dgv-instructions-header {
        border-bottom: 1px solid #e3e3e3;
        padding-bottom: 10px;
    }

    .instructions .dgv-instructions-card .dgv-instructions-header p.lead {
        font-size: 23px;
        color: #1a1a1a;
        margin-bottom: 8px;

    }

    .instructions .dgv-instructions-card .dgv-instructions-header p.desc {
        font-size: 15px;
    }

    .instructions .dgv-instructions-row {
        float: left;
        width: 96%;
    }

    .instructions .dgv-instructions-colf {
        padding: 1%;
    }

    .instructions .dgv-instructions-colf:first-child {
        margin-top: 5px;
    }

    .instructions .dgv-instructions-colf.dgv-highlighted {
        background-color: #dfffea;
        border-radius: 5px;
        border: 2px solid #bdd3cf;
    }

    .instructions .dgv-instructions-col3 {
        width: 31%;
        float: left;
        padding: 1%;
    }

    .instructions .dgv-instructions-col4 {
        width: 23%;
        float: left;
        padding: 1%;
    }

    .instructions h4 {
        margin-top: 5px;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .dgv-notice-dismiss {
        text-decoration: none;
    }

    @media (max-width: 767px) {
        .instructions .dgv-instructions-col3 {
            width: 100%;
        }

        .instructions .dgv-instructions-col4 {
            width: 100%;
        }
    }

    .instructions .green {
        color: #1abc9c;
    }

    .instructions .navy {
        color: #2f4154;
        font-size: 17px;
    }

    .instructions .dgv-instructions-row.dgv-instructions-header .dgv-instructions-colf {
        padding-bottom: 0;
    }

    .instructions .dgv-instructions-link {
        background-color: #2f4154;
        padding: 10px;
        min-width: 80px;
        margin-top: 10px;
        margin-bottom: 10px;
        color: #1abc9c;
        font-size: 1rem;
    }

    .instructions .dgv-instructions-link:hover, .instructions .dgv-instructions-link:active, .instructions .dgv-instructions-link:focus {
        outline: none;
        opacity: 0.8;
    }

    .instructions a.dgv-instructions-link {
        text-transform: none;
        text-decoration: none;
    }

    .instructions.is-dismissible {
        padding-right: 0 !important;
    }

    .dg-notice.notice-custom:before {
        content: '';
        position: absolute;
        left: -4px;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, #1abc9c 0%, #11725e 100%)
    }

</style>
