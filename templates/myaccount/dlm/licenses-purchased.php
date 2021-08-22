<?php
/**
 * The template for the purchased license keys inside "My account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dlm/licenses-purchased.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 2.0.0
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;

defined( 'ABSPATH' ) || exit; ?>

<h2><?php esc_html_e( $heading ); ?></h2>

<?php do_action( 'dlm_myaccount_licenses_after_heading' ); ?>

<?php foreach ( $data as $productId => $row ): ?>
    <table class="shop_table">
        <tbody>
        <thead>
        <tr>
            <th colspan="3"><?php echo esc_html( $row['name'] ); ?></th>
        </tr>
        </thead>
		<?php
		/** @var LicenseResourceModel $license */
		foreach ( $row['keys'] as $license ):

			$decrypted = $license->getDecryptedLicenseKey();
			if ( is_wp_error( $decrypted ) ) {
				$decrypted = '';
			}
			$actions = apply_filters( 'dlm_myaccount_licenses_keys_row_actions', array(), $license, $decrypted, $data );
			ksort( $actions );
			?>
            <tr>
                <td colspan="<?php echo ( $license->getExpiresAt() ) ? '1' : '2'; ?>">
                    <span class="dlm-myaccount-license-key"><?php echo esc_html( $decrypted ); ?></span>
                </td>
				<?php if ( $license->getExpiresAt() ): ?>

                    <?php
					try {
						$date = new DateTime( $license->getExpiresAt() );
					} catch ( Exception $e ) {
						$date = null;
					}
					?>
                    <td>
                        <span class="dlm-myaccount-license-key"><?php printf( '%s <strong>%s</strong>', $valid_until, $date ? $date->format( $date_format ) : 'N/A' ); ?></span>
                    </td>
                    <td class="license-key-actions">
						<?php
						foreach ( $actions as $key => $action ) {
							$href     = isset( $action['href'] ) ? $action['href'] : '';
							$cssClass = isset( $action['class'] ) ? $action['class'] : '';
							$text     = isset( $action['text'] ) ? $action['text'] : '';
							echo sprintf( '<a href="%s" class="%s">%s</a>', $href, $cssClass, $text );
						}
						?>
                    </td>
				<?php endif; ?>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

