<?php

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool;

defined( 'ABSPATH' ) || exit;
/* @var string[] $tools */

foreach ( $tools as $tool_id ) {
	/* @var AbstractTool[] $tool_id */
	$tool = new $tool_id;
	echo $tool->getView();
}
