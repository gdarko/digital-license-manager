<?php

namespace IdeoLogix\DigitalLicenseManager\Core\Defs;

use IgniteKit\WP\DeactivateFeedback\Reason;
use IgniteKit\WP\DeactivateFeedback\Deactivatable as BaseDeactivatable;

class Deactivatable extends BaseDeactivatable {

	public function __construct() {

		$this->id       = 'dlm';
		$this->name     = 'Digital License Manager';
		$this->slug     = 'digital-license-manager';
		$this->basename = DLM_PLUGIN_BASENAME;
		$this->reasons  = [
			new Reason( [
				'id'    => 1,
				'title' => __( 'I no longer need the plugin', 'digital-license-manager' ),
				'help'  => '',
				'input' => false,
			] ),
			new Reason( [
				'id'                => 2,
				'title'             => __( 'I found a better plugin', 'digital-license-manager' ),
				'help'              => '',
				'input'             => true,
				'input_placeholder' => __( 'Please share which plugin', 'digital-license-manager' )
			] ),
			new Reason( [
				'id'    => 3,
				'title' => __( 'I couldn\'t get the plugin to work', 'digital-license-manager' ),
				'help'  => '',
				'input' => false,
			] ),
			new Reason( [
				'id'    => 3,
				'title' => __( 'It\'s a temporary deactivation', 'digital-license-manager' ),
				'help'  => '',
				'input' => false,
			] ),
			new Reason( [
				'id'    => 3,
				'title' => __( 'Other', 'digital-license-manager' ),
				'help'  => '',
				'input' => true,
			] ),
		];
		$this->endpoint = 'https://codeverve.com/wp-json/v1/plugin-deactivated';

	}

}