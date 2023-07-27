<?php

namespace IdeoLogix\DigitalLicenseManager\Database;

use TenQuality\WP\Database\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder {

	/**
	 * Adds set statement (for update).
	 *
	 * Note: Modified line 39 to wrap the key in `..`
	 *
	 * @param array $args Multiple where arguments.
	 *
	 * @return \TenQuality\WP\Database\QueryBuilder this for chaining.
	 * @since 1.0.12
	 *
	 * @global object $wpdb
	 *
	 */
	public function set( $args ) {

		global $wpdb;
		foreach ( $args as $key => $value ) {
			// Value
			$arg_value         = is_array( $value ) && array_key_exists( 'value', $value ) ? $value['value'] : $value;
			$sanitize_callback = is_array( $value ) && array_key_exists( 'sanitize_callback', $value )
				? $value['sanitize_callback']
				: true;
			if ( $sanitize_callback
			     && $key !== 'raw'
			     && ( ! is_array( $value ) || ! array_key_exists( 'raw', $value ) )
			) {
				$arg_value = $this->sanitize_value( $sanitize_callback, $arg_value );
			}
			$statement              = $key === 'raw'
				? [ $arg_value ]
				: [
					sprintf( '`%s`', $key ),
					'=',
					is_array( $value ) && array_key_exists( 'raw', $value )
						? $value['raw']
						: ( is_array( $arg_value )
						? ( '\'' . implode( ',', $arg_value ) . '\'' )
						: ( $arg_value === null
							? 'null'
							: $wpdb->prepare( ( ! is_array( $value ) || ! array_key_exists( 'force_string', $value ) || ! $value['force_string'] ) && is_numeric( $arg_value ) ? '%d' : '%s', $arg_value )
						)
					),
				];
			$this->builder['set'][] = implode( ' ', $statement );
		}

		var_dump($this->builder['set']);
		die;

		return $this;
	}

	/**
	 * Sanitize value.
	 *
	 * @param string|bool $callback Sanitize callback.
	 * @param mixed $value
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	private function sanitize_value( $callback, $value ) {
		if ( $callback === true ) {
			$callback = ( is_numeric( $value ) && strpos( $value, '.' ) !== false )
				? 'floatval'
				: ( is_numeric( $value )
					? 'intval'
					: ( is_string( $value )
						? 'sanitize_text_field'
						: null
					)
				);
		}
		if ( $callback && strpos( $callback, '_builder' ) !== false ) {
			$callback = [ &$this, $callback ];
		}
		if ( is_array( $value ) ) {
			for ( $i = count( $value ) - 1; $i >= 0; -- $i ) {
				$value[ $i ] = $this->sanitize_value( true, $value[ $i ] );
			}
		}

		return $callback && is_callable( $callback ) ? call_user_func_array( $callback, [ $value ] ) : $value;
	}


}