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
?>

<h3><?php esc_html_e( 'Help / Support', 'digital-license-manager' ); ?></h3>

<h4><?php esc_html_e( 'If you have a question...', 'digital-license-manager' ); ?></h4>
<p><?php esc_html_e( 'Get in touch with us and will be more than happy to help you and possibly discover a room for improvement along the way.', 'digital-license-manager' ); ?></p>
<p>
    <a class="button button-small button-primary" href="<?php echo esc_url( DLM_DOCUMENTATION_URL ); ?>" target="_blank"><?php esc_html_e( 'Read Docs', 'digital-license-manager' ); ?></a>
    <a class="button button-small button-secondary" href="mailto:info@codeverve.com"><?php esc_html_e( 'Get in touch', 'digital-license-manager' ); ?></a>
</p>

<hr/>

<h4><?php esc_html_e( 'If you found bug or issue...', 'digital-license-manager' ); ?></h4>
<p><?php esc_html_e( 'Get in touch with us, do not hesitate to do that.', 'digital-license-manager' ); ?></p>
<p><?php esc_html_e( 'We strive to make this plugin as useful/stable as possible and we need people to report any issues/bugs.', 'digital-license-manager' ); ?></p>
<p>
    <a class="button button-small button-secondary" target="_blank" href="<?php echo esc_url( DLM_GITHUB_URL ); ?>"><?php esc_html_e( 'Report on Github', 'digital-license-manager' ); ?></a>
    <a class="button button-small button-secondary" target="_blank" href="<?php echo esc_url( DLM_WP_FORUM_URL ); ?>"><?php esc_html_e( 'Report on WP.org', 'digital-license-manager' ); ?></a>
</p>

<hr/>

<h4><?php esc_html_e( 'If you have an idea / feature request...', 'digital-license-manager' ); ?></h4>
<p><?php esc_html_e( 'We appreciate suggestions/ideas/feature requests, this is what makes the plugin better. Do not hesitate.', 'digital-license-manager' ); ?></p>
<p><?php esc_html_e( 'We are looking to implement any ideas that make sense and will be useful in this plugin after our evaluation.', 'digital-license-manager' ); ?></p>

<p>
    <a class="button button-small button-secondary" target="_blank" href="<?php echo esc_url( DLM_GITHUB_URL ); ?>"><?php esc_html_e( 'Suggest on Github', 'digital-license-manager' ); ?></a>
    <a class="button button-small button-secondary" target="_blank" href="<?php echo esc_url( DLM_WP_FORUM_URL ); ?>"><?php esc_html_e( 'Suggest on WP.org', 'digital-license-manager' ); ?></a>
</p>

<br/>

<h3><?php esc_html_e( 'About the Plugin', 'digital-license-manager' ); ?></h3>

<p><?php esc_html_e( 'Digital License Manager is a software licensing plugin that focuses on new features, stability and regular updates.', 'digital-license-manager' ); ?></p>
<p><?php esc_html_e( 'This plugin is early fork of the "License Manager for WooCommerce", initially we used it on the site codeverve.com, however because of lack of support and missing features we decided to create our own version and later released in public.', 'digital-license-manager' ); ?></p>
<p><?php esc_html_e( 'Nowadays, the forked code has been almost completely rewritten to enable us to support the plugin, remove "code smell" and add new features.', 'digital-license-manager' ); ?></p>
<p><?php esc_html_e( 'Some notable changes: Better license activation tracking and download tracking (for upcoming Reporting features), flexible permissions per rest api credentials (not global), Improved UI, API, Business logic.', 'digital-license-manager' ); ?></p>
<p><?php echo esc_html( sprintf( __( '&copy; %s CodeVerve.com / Darko Gjorgjijoski', 'digital-license-manager' ), gmdate( 'Y' ) ) ); ?></p>




