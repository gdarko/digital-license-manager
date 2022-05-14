<?php
/* @var string $logo */
/* @var string $license_product_name */
/* @var array $license_details */
?>

<div style="padding:60px 120px;">
	<?php if ( ! empty( $logo ) ): ?>
        <div style="margin-bottom: 35px;">
            <img alt="Logo" src="<?php echo esc_attr( $logo ); ?>" style="max-width: 400px;">
        </div>
	<?php endif; ?>
    <div style="border: 1px solid #ccc;padding:20px; width:800px; margin-top: 30px;">
        <h1 style="font-size: 32px;margin-top: 0; margin-bottom: 30px;">
			<?php esc_html_e( 'License Certificate', 'digital-license-manager' ); ?>
        </h1>
        <p style="font-size: 16px; margin-bottom: 20px; margin-top:0;">
			<?php printf( esc_html__( ' This document certifies the purchase of license key for <strong>%s</strong>.', 'digital-license-manager' ), esc_attr( $license_product_name ) ); ?>
        </p>
        <p style="font-size: 16px; margin-bottom: 20px; margin-top:0;">
			<?php esc_html_e( 'Details of the license can be accessed from your dashboard page.', 'digital-license-manger' ); ?>
        </p>
		<?php if ( ! empty( $license_details ) ): ?>
            <table style="margin-top:0; margin-bottom: 20px;font-size: 16px; border-spacing: 15px;">
                <tbody>
				<?php foreach ( $license_details as $name => $value ): ?>
                    <tr>
                        <th><?php echo esc_html( $name ); ?>:</th>
                        <td><?php echo esc_html( $value ); ?></td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
		<?php endif; ?>
        <p style="font-size: 16px; margin-bottom: 20px; margin-top:0;">
			<?php esc_html_e( 'Thanks for using our services. If you have any questions feel free to reach out and ask.', 'digital-license-manager' ); ?>
        </p>
    </div>
</div>
