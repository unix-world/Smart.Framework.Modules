<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Lang Detect Test Sample
// Route: ?/page/lang-detect.test (?page=lang-detect.test)
// (c) 2008-present unix-world.org - all rights reserved

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
		$mode = $this->RequestVarGet('mode', 'default', 'string');
		//--

		//--
		if((string)$mode == 'enhanced') {
			//-- use enhanced but slower 1-4-15k
			$text = SmartFileSystem::read($this->ControllerGetParam('module-path').'libs/data-1-4-15k/en/en.txt');
			$lndet = new \SmartModExtLib\LangDetect\LanguageNgrams($this->ControllerGetParam('module-path').'libs/data-1-4-15k');
			$lndet->setMaxNgrams(15000);
			$lndet->setMinLength(1);
			$lndet->setMaxLength(4);
			$minscore = 0.99;
			//--
		} else {
			//-- use default, 1-3-930
			$text = SmartFileSystem::read($this->ControllerGetParam('module-path').'libs/data-1-3-930/en/en.txt');
			$lndet = new \SmartModExtLib\LangDetect\LanguageNgrams();
			$minscore = 0.98;
			//--
		} //end if else
		//--

		//--
		//$arr = $lndet->detect($text);
		$arr = $lndet->getLanguageConfidence($text);
		//--

		//--
		$result = 'Test FAILED: Language Detection (nGrams: '.$mode.') ! (expected to detect ENGLISH Language) ...';
		if(is_array($arr)) {
			if((string)$arr['error-message'] == '') {
				if((string)$arr['lang-id'] == 'en') {
					if((float)$arr['confidence-score'] > (float)$minscore) {
						$result = 'Test OK: Language Detection (nGrams: '.$mode.').';
					} //end if
				} //end if
			} //end if
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Sample Language Detection: nGrams',
			'main' => '<h1 id="qunit-test-result">'.Smart::escape_html($result).'</h1><h3>Test is expecting: Language=en ; MinScore='.(float)$minscore.'</h3><pre>Result: '.Smart::escape_html(SmartUtils::pretty_print_var($arr)).'</pre>'.'<hr>'.'<pre>'.Smart::escape_html($text).'</pre>'
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
