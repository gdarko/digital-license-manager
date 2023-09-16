<?php
/* @var $expires */
/* @var $expiresF */
/* @var string $status */
/* @var $licenseKey */
/* @var $response */
/* @var $colorClass */

?>

<div class="dlm-block-licenses-check--results dlm-block-licenses-check--results-<?php echo $colorClass; ?>">
    <p><?php echo sprintf( __( 'The license is %s. Expiry date: %s', 'digital-license-manager' ), '<strong>'.$status.'</strong>', '<strong>'.$expiresF.'</strong>' ); ?></p>
    <span class="dlm-block-licenses-check--results-close" onclick="this.parentNode.remove();">&times;</span>
</div>
