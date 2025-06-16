<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for the license certificate generated from in "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/myaccount/licenses/partials/single-certificate.php
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 *
 * Default variables
 *
 * @var string $title
 * @var string $logo
 * @var string $license_product_name
 * @var array $license_details
 */
?>

<div style="padding:60px 90px;">
	<?php if ( ! empty( $logo ) ): ?>
        <div style="margin-bottom: 35px;">
            <img alt="Logo" src="<?php echo esc_attr( $logo ); ?>" style="max-width: 300px;" width="<?php echo esc_attr( apply_filters( 'dlm_license_certification_logo_width', 300 ) ); ?>">
        </div>
	<?php else: ?>
        <h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
    <div style="border: 1px dashed #aaaaaa;padding:20px; width:860px; margin-top: 30px;">
        <h1 style="font-size: 32px;margin-top: 0; margin-bottom: 30px;">
			<?php echo apply_filters( 'dlm_license_certification_title', esc_html__( 'License Certificate', 'digital-license-manager' ) ); ?>
        </h1>
        <p style="font-size: 16px; margin-bottom: 20px; margin-top:0;">
			<?php esc_html_e( 'This document certifies the purchase of license key for: ', 'digital-license-manager' ); ?>
            <strong><?php echo esc_html( wp_strip_all_tags( $license_product_name ) ); ?></strong>
        </p>
        <p style="font-size: 16px; margin-bottom: 20px; margin-top:0;">
			<?php esc_html_e( 'Details of the license can be accessed from your dashboard page.', 'digital-license-manger' ); ?>
        </p>
		<?php if ( ! empty( $license_details ) ): ?>
            <table style="margin-top:0; margin-bottom: 20px;font-size: 16px; border-spacing: 15px; border: 1px solid #aaaaaa; border-collapse: collapse; width: 100%;">
                <tbody>
				<?php foreach ( $license_details as $key => $detail ): ?>
                    <tr>
                        <th style="padding:5px; border: 1px solid black; border-collapse: collapse; width:180px;"><?php echo esc_html( $detail['title'] ); ?></th>
						<?php if ( $key === 'product_url' ): ?>
                            <td style="padding:5px; border: 1px solid black; border-collapse: collapse; width:570px;">
                                <a target="_blank" href="<?php echo esc_url( $detail['value'] ); ?>"><?php esc_html_e( 'Click to View', 'digital-license-manager' ); ?></a>
                            </td>
						<?php else: ?>
                            <td style="padding:5px; border: 1px solid black; border-collapse: collapse; width:570px;"><?php echo esc_html( wp_strip_all_tags( $detail['value'] ) ); ?></td>
						<?php endif; ?>
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
