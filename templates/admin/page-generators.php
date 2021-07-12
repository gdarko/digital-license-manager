<?php defined( 'ABSPATH' ) || exit; ?>

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