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

	/**
	 * Adds where statement.
	 * @since 1.0.0
	 *
	 * @global object $wpdb
	 *
	 * @param array $args Multiple where arguments.
	 *
	 * @return \TenQuality\WP\Database\QueryBuilder this for chaining.
	 */
	public function where( $args )
	{

		global $wpdb;
		foreach ( $args as $key => $value ) {
			// Options - set
			if ( is_array( $value ) && array_key_exists( 'wildcard', $value ) && !empty( $value['wildcard'] ) )
				$this->options['wildcard'] = trim( $value['wildcard'] );
			// Value
			$arg_value = is_array( $value ) && array_key_exists( 'value', $value ) ? $value['value'] : $value;
			if ( is_array( $value ) && array_key_exists( 'min', $value ) )
				$arg_value = $value['min'];
			$sanitize_callback = is_array( $value ) && array_key_exists( 'sanitize_callback', $value )
				? $value['sanitize_callback']
				: true;
			if ( $sanitize_callback
			     && $key !== 'raw'
			     && ( !is_array( $value ) || !array_key_exists( 'key', $value ) )
			)
				$arg_value = $this->sanitize_value( $sanitize_callback, $arg_value );


			$statement = $key === 'raw'
				? [$arg_value]
				: [
					$key,
					is_array( $value ) && isset( $value['operator'] ) ? strtoupper( $value['operator'] ) : ( $arg_value === null ? 'is' : '=' ),
					is_array( $value ) && array_key_exists( 'key', $value )
						? $value['key']
						: ( is_array( $arg_value )
						? ( '(\'' . implode( '\',\'', $arg_value ) . '\')' )
						: ( $arg_value === null
							? 'null'
							: $wpdb->prepare( ( !is_array( $value ) || !array_key_exists( 'force_string', $value ) || !$value['force_string'] ) && is_numeric( $arg_value ) ? '%d' : '%s' , $arg_value )
						)
					),
				];

			// Between?
			if ( is_array( $value ) && isset( $value['operator'] ) ) {
				$value['operator'] = strtoupper( $value['operator'] );
				if ( strpos( $value['operator'], 'BETWEEN' ) !== false ) {
					if ( array_key_exists( 'max', $value ) || array_key_exists( 'key_b', $value ) ) {
						if ( array_key_exists( 'max', $value ) )
							$arg_value = $value['max'];
						if ( array_key_exists( 'sanitize_callback2', $value ) )
							$sanitize_callback = $value['sanitize_callback2'];
						if ( $sanitize_callback && !array_key_exists( 'key_b', $value ) )
							$arg_value = $this->sanitize_value( $sanitize_callback, $arg_value );
						$statement[] = 'AND';
						$statement[] = array_key_exists( 'key_b', $value )
							? $value['key_b']
							: ( is_array( $arg_value )
								? ( '(\'' . implode( '\',\'', $arg_value ) . '\')' )
								: $wpdb->prepare( ( !array_key_exists( 'force_string', $value ) || !$value['force_string'] ) && is_numeric( $arg_value ) ? '%d' : '%s' , $arg_value )
							);
					} else {
						throw new Exception( '"max" or "key_b "parameter must be indicated when using the BETWEEN operator.', 10202 );
					}
				}
			}
			$this->builder['where'][] = [
				'joint'     => is_array( $value ) && isset( $value['joint'] ) ? $value['joint'] : 'AND',
				'condition' => $this->buildStatement($statement),
			];
			// Options - reset
			if ( is_array( $value ) && array_key_exists( 'wildcard', $value ) && !empty( $value['wildcard'] ) )
				$this->options['wildcard'] = $this->options['default_wildcard'];
		}
		return $this;
	}


	/**
	 * Build statement
	 * @param array $statement
	 *
	 * @return string
	 */
	private function buildStatement($statement) {
		$imploded = implode(' ', $statement);
		return str_replace(["'NOT NULL'", "'not null'", "'NULL'", "'null'"], ["NOT NULL", "not nulll", "NULL", "null"], $imploded);
	}


}