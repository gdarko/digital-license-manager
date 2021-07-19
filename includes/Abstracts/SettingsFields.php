<?php


namespace IdeoLogix\DigitalLicenseManager\Abstracts;

/**
 * Trait SettingsFields
 * @package IdeoLogix\DigitalLicenseManager\Abstracts
 */
trait SettingsFields {

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

}