<?php
/* @var \IdeoLogix\DigitalLicenseManager\Database\Models\License[] $records */


use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

$show_actions = apply_filters( 'dlm_block_license_table_actions', false, $records );

?>

<table>
    <thead>
    <tr>
        <th><?php _e( 'License', 'digital-license-manager' ); ?></th>
		<?php do_action( 'dlm_block_license_table_after_license_column_head' ); ?>
        <th><?php _e( 'Status', 'digital-license-manager' ); ?></th>
        <th><?php _e( 'Expires At', 'digital-license-manager' ); ?></th>
		<?php if ( $show_actions ): ?>
            <th><?php _e( 'Actions', 'digital-license-manager' ); ?></th>
		<?php endif; ?>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $records as $record ): ?>
        <tr>
            <td>
				<?php
				try {
					echo apply_filters( 'dlm_license_key_markup', $record->getDecryptedLicenseKey(), $record );
				} catch ( \Exception $e ) {
					echo __( 'Unknown', 'digital-license-manager' );
				}
				?>
            </td>
			<?php do_action( 'dlm_block_license_table_after_license_column_body', $records ); ?>
            <td>
				<?php
				$status = apply_filters( 'dlm_block_license_table_status', null, $records );
				if ( null === $status ) {
					$status = \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::statusToHtml( $record->getStatus(), ['style' => 'inline'] );
				}
				echo $status;
				?>
            </td>
            <td>
				<?php echo DateFormatter::toHtml( $record->getExpiresAt(), [ 'expires' => true ] ); ?>
            </td>
			<?php if ( $show_actions ): ?>
                <td><?php do_action( 'dlm_block_license_table_actions_content', $record ); ?></td>
			<?php endif; ?>
        </tr>
	<?php endforeach; ?>
    </tbody>
</table>