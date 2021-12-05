<?php


namespace IdeoLogix\DigitalLicenseManager\Controllers;


use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;

class Welcome {

	public function __construct() {
		add_action( 'wp', array( $this, 'init' ) );
	}

	public function doPopup() {

		NoticeFlasher::add('');


	}

}
