<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: GeoIP Test Sample
// Route: ?/page/geoip.test (?page=geoip.test)
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, TASK, SHARED

/*
define('SMART_GEOIPLOOKUP_BIN_PATH', '/path/to/geoiplookup');
define('SMART_GEOIPLOOKUP6_BIN_PATH', '/path/to/geoiplookup6');
*/

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if((!defined('SMART_GEOIPLOOKUP_BIN_PATH')) OR (!defined('SMART_GEOIPLOOKUP6_BIN_PATH'))) {
			$this->PageViewSetErrorStatus(503, 'NOTICE: Not Configured ...');
			return;
		} //end if
		//--

		//--
		$data = [
			'8.8.8.8' => null,
			'2001:4860:4860::8888' => null
		];
		//--
		foreach($data as $key => $val) {
			$data[(string)$key] = \SmartModExtLib\Geoip\GeoipLookup::getCountryCode($key);
		} //end foreach
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'GeoIP Test',
			'main' => '<h1>GeoIP Lookup</h1><pre>'.Smart::escape_html(SmartUtils::pretty_print_var($data)).'</pre>'
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php

} //END CLASS


/**
 * Task Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppTaskController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in task.php

} //END CLASS


// end of php code
