<?php


namespace IdeoLogix\DigitalLicenseManager\Abstracts;

/**
 * Trait SettingsFields
 * @package IdeoLogix\DigitalLicenseManager\Abstracts
 */
trait SettingsFieldsTrait {

	/**
	 * Sanitize settings, cast to array if not already.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function sanitizeArray( $settings ) {
		if ( $settings === null ) {
			$settings = array();
		}

		do_action( 'dlm_settings_sanitized', $settings );

		return $settings;
	}


	/**
	 * Render the default checkbox option
	 *
	 * @param $args
	 */
	public function fieldCheckbox( $args ) {

		$key     = isset( $args['key'] ) ? $args['key'] : ''; // database key.
		$field   = isset( $args['field'] ) ? $args['field'] : ''; // field name/id.
		$value   = isset( $args['value'] ) && (bool) $args['value']; // field name/id.
		$label   = isset( $args['label'] ) ? $args['label'] : '';
		$explain = isset( $args['explain'] ) ? $args['explain'] : '';

		$html = '<fieldset>';
		$html .= sprintf( '<label for="%s">', $field );
		$html .= sprintf(
			'<input id="%s" type="checkbox" name="%s[%s]" value="1" %s/>',
			$key,
			$key,
			$field,
			checked( true, $value, false )
		);
		$html .= sprintf( '<span>%s</span>', $label );
		$html .= '</label>';
		$html .= sprintf( '<p class="description">%s</p>', $explain );
		$html .= '</fieldset>';

		echo $html;

	}

	/**
	 * Render the image upload field
	 * @return void
	 */
	public function fieldImageUpload($args) {

		$key         = isset( $args['key'] ) ? $args['key'] : ''; // database key.
		$field       = isset( $args['field'] ) ? $args['field'] : ''; // field name/id.
		$value       = isset( $args['value'] ) ? $args['value'] : null; // field name/id.
		$label       = isset( $args['label'] ) ? $args['label'] : '';
		$explain     = isset( $args['explain'] ) ? $args['explain'] : '';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : DLM_PLUGIN_URL . 'assets/img/logo-placeholder.jpg';

		$html = '<fieldset>';
		$html .= sprintf( '<label for="%s">%s</label>', $field, $label );
		$html .= $this->fieldImageUploadMarkup( $key, $field, $value, $placeholder );
		$html .= sprintf( '<p class="description">%s</p>', $explain );
		$html .= '</fieldset>';

		echo $html;

	}

	/**
     * Upload field markup
	 * @param $key
	 * @param $media_id
	 * @param $placeholder
	 *
	 * @return false|string
	 */
	function fieldImageUploadMarkup( $key, $field, $media_id, $placeholder = '' ) {

		if ( ! empty( $media_id ) && is_numeric( $media_id ) ) {
			$current_src = wp_get_attachment_image_src( $media_id, 'large' );
			$current_src = $current_src[0];
		} else {
			$current_src = $placeholder;
			$media_id    = '';
		}
        ob_start();
		?>
		<div class="dlm-field-upload" data-show-attachment-preview="1">
			<img class="dlm-field-placeholder" data-src="<?php echo $placeholder; ?>" src="<?php echo $current_src; ?>" alt="File"/>
			<div class="dlm-field-submit">
                <?php echo sprintf('<input id="%s" type="hidden" name="%s[%s]" value="%s"/>', $key, $key, $field, $media_id); ?>
				<button type="submit" class="dlm-field-upload-button button"><?php _e('Upload', 'digital-license-manager'); ?></button>
				<button type="submit" class="dlm-field-remove-button button">&times;</button>
			</div>
		</div>
		<?php
        return ob_get_clean();
	}

	/**
	 * Renders a text input field.
	 *
	 * @param array $args {
	 *     Optional arguments.
	 *
	 *     @type string $explain A description of the setting.
	 *     @type string $field   The setting identifier in the option group.
	 *     @type string $key     The option group name.
	 *     @type int    $size    The size, in characters, of the text field. Defaults to 20.
	 *     @type string $value   The setting's value.
	 * }
	 */
	public function fieldText( $args ) {

		$args  = wp_parse_args(
			$args,
			array(
				'explain' => '', // Should be HTML escaped.
				'field'   => '',
				'key'     => '',
				'size'    => '20',
				'value'   => '',
			)
		);
		$html  = sprintf(
			'<input type="text" id="%s" name="%s[%s]" value="%s" size="%s">',
			$args['field'],
			$args['key'],
			$args['field'],
			esc_attr( $args['value'] ),
			$args['size']
		);
		$html .= "<p class='description'>{$args['explain']}</p>";
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}
}
