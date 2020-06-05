<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Facebook Api Test Sample
// Route: ?/page/sm-facebook.test (?page=sm-facebook.test)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

/**
 * Index Controller :: v.20200121
 *
 * First Test the Js-Api: 				modules/mod-sm-facebook/views/js/demo/sample.html?app_id={the-app-id-goes-here}
 * Test PHP-Api (run on localhost): 	?/page/sm-facebook.test/app_id/{the-app-id-goes-here}/app_secret/{the-app-secret-goes-here}
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//-- SETTINGS: test with localhost domain !!
		$app_id = $this->RequestVarGet('app_id', '', 'string'); // Facebook App Id
		$app_secret = $this->RequestVarGet('app_secret', '', 'string'); // Facebook App Secret
		//--

		//--
		if((string)$app_id == '') {
			$this->PageViewSetErrorStatus(400, 'Empty AppId');
			return;
		} //end if
		//--
		if((string)$app_secret == '') {
			$this->PageViewSetErrorStatus(400, 'Empty AppSecret');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetVar('title', 'Sample Facebook SDK Api');
		//--

		//--
		$fb = new \SmartModExtLib\SmFacebook\FacebookApi(
			(string)$app_id, // app id
			(string)$app_secret // app secret
		);
		$user_data = (array) $fb->getUserData();
		//--
		if($fb->validateUserData() !== true) {
			$this->PageViewSetVars([
				'main' => '<h1>Not Logged in with Facebook ...</h1>'.'<br>'.Smart::escape_html($fb->getLastError())
			]);
		} else {
			$this->PageViewSetVars([
				'main' => '<h1>Authenticated Facebook User Data</h1><pre>'.Smart::escape_html(print_r($user_data,1)).'</pre>'
			]);
		} //end if else
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 *
 * First Test the Js-Api: 				modules/mod-sm-facebook/views/js/demo/sample.html?app_id={the-app-id-goes-here}
 * Test PHP-Api (run on localhost): 	admin.php?/page/sm-facebook.test/app_id/{the-app-id-goes-here}/app_secret/{the-app-secret-goes-here}
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php

} //END CLASS


// end of php code
