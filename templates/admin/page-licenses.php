<?php defined( 'ABSPATH' ) || exit; ?>

    <div class="wrap dlm">
		<?php
		if ( $action === 'list' || $action === 'delete'
		) {
			include_once( 'licenses/page-list.php' );
		} elseif ( $action === 'add' ) {
			if ( current_user_can( 'dlm_create_licenses' ) ) {
				include_once( 'licenses/page-add.php' );
			} else {
				wp_die( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			}
		} elseif ( $action === 'import' ) {
			if ( current_user_can( 'dlm_create_licenses' ) ) {
				include_once( 'licenses/page-import.php' );
			} else {
				wp_die( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			}
		} elseif ( $action === 'edit' ) {
			if ( current_user_can( 'dlm_edit_licenses' ) ) {
				include_once( 'licenses/page-edit.php' );
			} else {
				wp_die( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			}
		}
		?>
    </div>

<?php include( DLM_TEMPLATES_DIR . 'admin/licenses/modals/export.php' ); ?>