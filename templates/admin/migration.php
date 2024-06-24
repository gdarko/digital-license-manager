<h2><?php esc_html_e( 'Digital License Manager', 'digital-license-manager' ); ?></h2>
<p>
	<?php esc_html_e( 'We detected that you used <strong>License Manager for WooCommerce</strong> previously. If you are looking to migrate to <strong>Digital License Manager</strong>, we built a tool specifically for this.', 'digital-license-manager' ); ?>
</p>
<p>
	<?php esc_html_e( 'The tool allows you to automate your migration process. It will convert license data, generator data, activation data, rest api keys, product settings, plugin settings, and much more.', 'digital-license-manager' ); ?>
</p>
<p>
	<?php esc_html_e( 'Additionally, we have compatibility layer which once enabled will create the same REST API url structure as License Manager for WooCommerce for backwards compatibility.', 'digital-license-manager' ); ?>
</p>
<p><?php esc_html_e( 'Once you finish with the migration, you can deactivate <strong>License Manager for WooCommerce</strong> safely.', 'digital-license-manager' ); ?></p>
<p style="margin-top: 10px; margin-bottom: 15px;">
    <a href="<?php echo esc_url( admin_url('admin.php?page=dlm_settings&tab=tools') ); ?>" class="button button-primary"><?php esc_html_e('Migrate now', 'digital-license-manager'); ?></a>
    <a href="<?php echo esc_url( trailingslashit(DLM_DOCUMENTATION_URL).'migration/migrate-from-license-manager-for-woocommerce/' ); ?>" target="_blank" class="button button-secondary"><?php esc_html_e('Read more', 'digital-license-manager'); ?></a>
</p>