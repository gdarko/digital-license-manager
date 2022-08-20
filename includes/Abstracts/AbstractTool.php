<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

abstract class AbstractTool {

	/**
	 * The identifier
	 * @var string
	 */
	protected $id;

	/**
	 * The description
	 * @var string
	 */
	protected $description;

	/**
	 * Returns the view
	 * @return string
	 */
	abstract public function getView();

	/**
	 * Returns the migrator steps
	 *
	 * eg:
	 *
	 *    [
	 *        1 => array( 'name' => 'Licenses', 'pages' => 3 ),
	 *        2 => array( 'name' => 'Generators', 'pages' => 4 ),
	 *        3 => array( 'name' => 'API Keys', 'pages' => 5 ),
	 *        4 => array( 'name' => 'Products', 'pages' => 6 ),
	 *        5 => array( 'name' => 'Orders', 'pages' => 7 )
	 *    ];
	 *
	 * @param null $identifier
	 *
	 * @return array|\WP_Error
	 */
	abstract public function getSteps( $identifier = null );

	/**
	 * Initializes the process
	 *
	 * @param null $identifier
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function initProcess( $identifier = null );

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param $page
	 * @param null $identifier
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function doStep( $step, $page, $identifier = null );


	/**
	 * Check availability
	 *
	 * @param null $identifier
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function checkAvailability( $identifier = null );


	/**
	 * Return the next step
	 *
	 * @param $step
	 * @param $page
	 * @param null $identifier
	 *
	 * @return array|\WP_Error
	 */
	public function getNextStep( $step, $page, $identifier = null ) {

		$step = is_null( $step ) ? 1 : (int) $step;
		$page = is_null( $page ) ? 1 : (int) $page;

		$steps = $this->getSteps( $identifier );

		if ( ! is_array( $steps ) ) {
			return new \WP_Error( '500', 'Unable to determine next step', 'digital-license-manager' );
		}

		$total = 0;
		foreach ( $steps as $id => $stepp ) {
			$total += (int) $stepp['pages'];
		}

		$data = [
			'next_step' => 0,
			'next_page' => 0,
			'message'   => '',
			'total'     => $total,
			'current'   => 0,
			'percent'   => 0,
		];

		if ( ! isset( $steps[ $step ] ) ) {
			$data['next_step'] = - 1;
			$data['next_page'] = - 1;
			$data['message']   = __( 'Operation not initialized properly.', 'digital-license-manager' );

			return $data;
		} else {

			if ( ! isset( $steps[ $step ]['pages'] ) ) {
				return new \WP_Error( '500', __( 'Unable to determine next step', 'digital-license-manager' ) );
			}

			$next_page   = $page + 1;
			$total_pages = (int) $steps[ $step ]['pages'];

			if ( $next_page <= $total_pages ) {
				$next_step         = $step;
				$data['next_step'] = $next_step;
				$data['next_page'] = $next_page;
				$data['message']   = sprintf( __( 'Processing %s (%d/%d)', 'digital-license-manager' ), $steps[ $next_step ]['name'], $next_page, $steps[ $next_step ]['pages'] );
			} else if ( isset( $steps[ $step + 1 ] ) ) {
				$next_page         = 1;
				$next_step         = $step + 1;
				$data['next_step'] = $next_step;
				$data['next_page'] = $next_page;
				$data['message']   = sprintf( __( 'Processing %s (%d/%d)', 'digital-license-manager' ), $steps[ $next_step ]['name'], $next_page, $steps[ $next_step ]['pages'] );

			} else {
				$data['next_step'] = - 1;
				$data['next_page'] = - 1;
				$data['message']   = __( 'Operation complete.', 'digital-license-manager' );
			}

			$current = 0;
			foreach ( $steps as $i => $info ) {
				if ( $i < $step ) {
					$current += $info['pages'];
				} else if ( $i === $step ) {
					$current += $page;
				}
			}
			$data['percent'] = $current > 0 ? round( $current / $total * 100, 2 ) : 0;

		}

		return $data;

	}

	/**
	 * Return the id identifier
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Return the description
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
}
