<?php
// Controller: OAuth2 Manager
// Route: ?/page/oauth2.manager (?page=oauth2.manager)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'INDEX'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 * @version v.20210402
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		$this->PageViewSetCfg('rawpage', true);
		$this->PageViewSetCfg('rawmime', 'text/plain');
		$this->PageViewSetCfg('rawdisp', 'inline');

		$code = (string) trim((string)$this->RequestVarGet('code', '', 'string'));
		if((string)$code == '') {
			$this->PageViewSetErrorStatus(400, 'No OAUTH2 code has been provided ...');
			return;
		} //end if

		$this->PageViewSetVar('main', 'Code='.$code);

	} //END FUNCTION

} //END CLASS


// end of php code
