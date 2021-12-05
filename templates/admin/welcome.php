<?php
// Urls
$url_docs      = DLM_DOCUMENTATION_URL;
$url_purchase  = DLM_PURCHASE_URL;
$url_github    = DLM_GITHUB_URL;
$url_wordpress = DLM_WP_FORUM_URL;
?>

<div class="instructions dgv-instructions">
    <div class="dgv-instructions-card dgv-instructions-card-shadow">
        <div class="dgv-instructions-row dgv-instructions-header">
            <div class="dgv-instructions-colf">
                <p class="lead"><?php _e( 'Thanks for installing <strong class="green">Digital License Manager</strong>', 'digital-license-manager' ); ?></p>
                <p class="desc"><?php _e( 'Digital License Manager provides complete <strong class="underline">licensing</strong> solution for your software and as well as selling Licenses. The plugin goal is to provide consistent support and feature releases.', 'digital-license-manager' ); ?></p>
                <p class="desc"><?php _e( 'If you found this plugin <strong>useful</strong> for your business, we will greatly appreciate if you take a minute to <a target="_blank" title="Give this plugin a good five star rating :)" href="https://wordpress.org/support/plugin/digital-license-manager/reviews/#new-post">rate it. &#9733;&#9733;&#9733;&#9733;&#9733;</a>', 'digital-license-manager' ); ?></p>
                <p class="desc"><?php _e( sprintf( '<a target="_blank" class="button button-primary" title="Plugin Documentation" href="%s">Read Docs</a>', $url_docs ), 'digital-license-manager' ); ?></p>
            </div>
        </div>
        <div class="dgv-instructions-row dgv-instructions-mb-10">
            <div class="dgv-instructions-colf">
                <div class="dgv-instructions-extra">
                    <h4 class="navy"><?php _e( 'Found problem? Report it!', 'digital-license-manager' ); ?></h4>
                    <p style="margin-bottom: 0;">
						<?php _e( sprintf( 'If you found a bug or you want to report a problem please open a support ticket <a target="_blank" href="%s">here</a> or on <a target="_blank" href="%s">Github!</a>', $url_wordpress, $url_github ), 'digital-license-manager' ); ?>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-colf" style="padding-top:0;">
                <div class="dgv-instructions-extra">
                    <h4 style="margin-top:0;"
                        class="navy"><?php _e( 'Want more features? try the PRO version!', 'digital-license-manager' ); ?></h4>
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
        margin-bottom: 5px;

    }

    .instructions .dgv-instructions-card .dgv-instructions-header p.desc {
        font-size: 15px;
    }

    .instructions .dgv-instructions-row {
        float: left;
        width: 96%;
    }

    .instructions .dgv-instructions-colf {
        width: 100%;
        float: left;
        padding: 1%;
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

</style>
