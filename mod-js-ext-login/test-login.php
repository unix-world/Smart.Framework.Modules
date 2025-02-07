<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Test Samples
// Route: ?/page/js-ext-login.test-login (?page=js-ext-login.test-login)
// (c) 2006-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, TASK, SHARED


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

		//--
		if($this->IsAjaxRequest() !== true) {
			$this->PageViewSetErrorStatus(403, 'ERROR: Direct request is disabled ...');
			return;
		} //end if
		//--
		$type = (string) trim((string)$this->RequestVarGet('type', '', 'string'));
		$validateUrl = (string) trim((string)$this->RequestVarGet('validateUrl', '', 'string'));
		//--
		$ok = false;
		//--
		if(
			((string)$validateUrl != '')
			AND
			(
				((string)$type === 'facebook-api')
				AND
				(strpos((string)$validateUrl, 'https://graph.facebook.com/') === 0)
			)
		) {
			$ok = true;
		} //end if
		//--
		if(!$ok) {
			$this->PageViewSetErrorStatus(400, 'The Request is Invalid ...');
			return;
		} //end if
		//--
		$browser = new SmartHttpClient();
	//	$browser->useragent = 'curl/7.88.1';
		$browser->rawheaders = [
			'Content-Type' => 'application/json',
		];
		$browser->securemode = true; // enable SSL/TLS Strict Secure Mode by default
		$arr = (array) $browser->browse_url((string)$validateUrl, 'GET');
		$browser = null;
		$data = [];
		if((int)$arr['result'] == 1) {
			if((int)$arr['code'] == 200) {
				$arr['content'] = (string) trim((string)$arr['content']);
				if((string)$arr['content'] != '') {
					$data = Smart::json_decode((string)$arr['content']);
					if(!is_array($data)) {
						$data = [];
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		$this->PageViewSetCfg('rawpage', true);
		$this->PageViewSetCfg('rawmime', 'application/json');
		$this->PageViewSetVar(
			'main',
			(string) Smart::json_encode([
				'validateData' => (array)  $data,
				'validateUrl'  => (string) $validateUrl,
			])
		);
		//--
		return 202;
		//--

	} //END FUNCTION

} //END CLASS


// end of php code
