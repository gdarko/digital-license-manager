<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2024  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-2024  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

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
