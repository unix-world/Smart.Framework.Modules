<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / Math Parser
// Route: ?page=zzz-tests.test-math-parser
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
 * Index Controller
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

		//--
		if(!class_exists('\\PHPMathParser\\Math')) {
			require_once('modules/vendor/PHPMathParser/autoload.php');
		} //end if
		//--
		$math = new \PHPMathParser\Math();
		$expr = '(((((1 + 2 * ((3 + 4) * 5 + 6)) - 2) / 9) ^ 2) / 3 / 3) / 2 + 0.5';
		$answer = $math->evaluate((string)$expr); // int(5)
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: Math Parser',
			'main' => '<h1 id="qunit-test-result">Math Expression</h1><pre>'.Smart::escape_html($expr).'</pre><hr>'.'<h1>Evaluated Result</h1><pre>'.Smart::escape_html($answer).'</pre><hr>'.'<br><br>'
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


//end of php code
?>