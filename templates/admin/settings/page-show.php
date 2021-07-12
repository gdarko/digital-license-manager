<?php

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\ApiKey as ApiKeyResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Utils\Html;

defined( 'ABSPATH' ) || exit;

/** @var ApiKeyResourceModel $keyData */

?>

<h2><?php esc_html_e( 'Key details', 'digital-license-manager' ); ?></h2>
<hr class="wp-header-end">

<div class="postbox">
    <div class="inside">
		<?php if ( $keyData ): ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'dlm-api-key-update' ); ?>
                <table class="form-table">
                    <tbody>
                    <tr scope="row">
                        <th scope="row">
                            <label for="consumer_key"><?php esc_html_e( 'Consumer key', 'digital-license-manager' ); ?></label>
                        </th>
                        <td>
                            <input id="consumer_key" class="regular-text" name="consumer_key" type="text" value="<?php echo esc_attr( $consumerKey ); ?>" readonly="readonly">
                        </td>
                    </tr>
                    <tr scope="row">
                        <th scope="row">
                            <label for="consumer_secret"><?php esc_html_e( 'Consumer secret', 'digital-license-manager' ); ?></label>
                        </th>
                        <td>
                            <input id="consumer_secret" class="regular-text" name="consumer_secret" type="text" value="<?php echo esc_attr( $keyData->getConsumerSecret() ); ?>" readonly="readonly">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
		<?php else: ?>
            <div><?php esc_html_e( 'Nothing to see here...', 'digital-license-manager' ); ?></div>
		<?php endif; ?>
    </div>
</div>