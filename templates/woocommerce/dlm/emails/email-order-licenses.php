<?php
/**
 * The template which adds the license keys to the "order complete" email (HTML).
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/emails/email-order-licenses.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

defined( 'ABSPATH' ) || exit;

?>

<h2><?php esc_html_e( $heading ); ?></h2>

<?php do_action( 'dlm_myaccount_licenses_after_heading' ); ?>

<div style="margin-bottom: 40px;">
	<?php foreach ( $data as $row ): ?>
        <table class="td" cellspacing="0" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <thead>
            <tr>
                <th class="td" scope="col" style="text-align: left;" colspan="2">
                    <span><?php echo esc_html( $row['name'] ); ?></span>
                </th>
            </tr>
            </thead>
			<tbody>
			<?php
			/** @var License $license */
			foreach ( $row['keys'] as $license ):
				$decrypted = $license->getDecryptedLicenseKey();
				if ( is_wp_error( $decrypted ) ) {
					$decrypted = '';
				}
				?>
                <tr>
                    <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" colspan="<?php echo ( $license->getExpiresAt() ) ? '1' : '2'; ?>">
                        <code><?php echo esc_html( $decrypted ); ?></code>
                    </td>

					<?php if ( $license->getExpiresAt() ): ?><?php
						$date = wp_date( DateFormatter::getExpirationFormat(), strtotime( $license->getExpiresAt() ) );
						?>
                        <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                            <code><?php
								printf( '%s <strong>%s</strong>', $valid_until, $date );
								?></code>
                        </td>
					<?php endif; ?>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
	<?php endforeach; ?>
</div>
