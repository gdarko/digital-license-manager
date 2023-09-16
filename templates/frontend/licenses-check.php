<?php

/**
 * The licenses check form
 */

/* @var bool $emailRequired */

$licenseKey = apply_filters( 'dlm_block_licenses_table_key', null );
?>


<div class="dlm-block-licenses-check">

    <div class="dlm-block-licenses-check-results"></div>

    <form id="dlm-licenses-check">
		<?php if ( isset( $emailRequired ) && $emailRequired && ! is_user_logged_in() ): ?>
            <div class="dlm-form-row">
                <label for="email"><?php _e( 'Owner Email', 'digital-license-manager' ); ?></label>
                <input type="text"
                       id="email"
                       name="email"
                       class="dlm-form-control"/>
            </div>
            <input type="hidden" name="echeck" id="echeck" value="1"/>
		<?php endif; ?>
        <div class="dlm-form-row">
            <label for="licenseKey"><?php _e( 'License Key', 'digital-license-manager' ); ?></label>
            <input type="text"
                   id="licenseKey"
                   name="licenseKey"
                   value="<?php echo esc_attr( $licenseKey ); ?>"
                   class="dlm-form-control"/>
        </div>
        <button type="submit"><?php _e( 'Submit', 'digital-license-manager' ); ?></button>
    </form>
</div>