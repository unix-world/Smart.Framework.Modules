<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Twitter Api Test Sample
// Route: ?/page/sm-twitter.test (?page=sm-twitter.test)
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

/**
 * Index Controller :: v.181002
 *
 * First Test the Js-Api: modules/mod-sm-twitter/views/js/demo/sample.html (need to set the {AppId} and {AppSecret} in modules/mod-sm-twitter/views/js/demo/setup.js)
 * Test PHP-Api (run on localhost): 	?/page/sm-twitter.test/app_id/{AppId}/app_secret/{AppSecret}
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
		$app_id = $this->RequestVarGet('app_id', '', 'string'); // Twitter Consumer Key
		$app_secret = $this->RequestVarGet('app_secret', '', 'string'); // Twitter Consumer Secret
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
		$this->PageViewSetVar('title', 'Sample Twitter CB Api');
		//--

		//--
		$twitt = new \SmartModExtLib\SmTwitter\TwitterApi(
			(string) $app_id, // consumer key
			(string) $app_secret // consumer secret
		);
		$user_data = (array) $twitt->getUserData();
		//--
		if($twitt->validateUserData() !== true) {
			$this->PageViewSetVars([
				'main' => '<h1>Not Logged in with Twitter ...</h1>'.Smart::escape_html($twitt->getLastError())
			]);
		} else {
			$this->PageViewSetVars([
				'main' => '<h1>Authenticated Twitter User Data</h1><pre>'.Smart::escape_html(print_r($user_data,1)).'</pre>'
			]);
		} //end if else
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 *
 * Test PHP-Api (run on localhost): 	admin.php?/page/sm-twitter.test/app_id/{the-app-id-goes-here}/app_secret/{the-app-secret-goes-here}
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php

} //END CLASS


//end of php code
?>