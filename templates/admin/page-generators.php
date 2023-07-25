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

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap dlm">
	<?php
	if ( $action === 'list'
	     || $action === 'delete'
	) {
		include_once( 'generators/page-list.php' );
	} elseif ( $action === 'add' ) {
		if ( current_user_can( 'dlm_create_generators' ) ) {
			include_once( 'generators/page-add.php' );
		} else {
			wp_die( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
		}
	} elseif ( $action === 'edit' ) {
		if ( current_user_can( 'dlm_edit_generators' ) ) {
			include_once( 'generators/page-edit.php' );
		} else {
			wp_die( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
		}
	} elseif ( $action === 'generate' ) {
		if ( current_user_can( 'dlm_create_licenses' ) ) {
			include_once( 'generators/page-generate.php' );
		} else {
			wp_die( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
		}
	}
	?>
</div>