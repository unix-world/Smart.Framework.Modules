<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / Math Parser
// Route: ?page=zzz-tests.test-math-parser
// (c) 2006-2021 unix-world.org - all rights reserved

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
		if(!class_exists('\\PHPMathParser\\Math')) {
			if(!is_file('modules/vendor/PHPMathParser/autoload.php')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: Cannot Load PHPMathParser/Math ...');
				return;
			} //end if
			require_once('modules/vendor/PHPMathParser/autoload.php');
		} //end if
		//--
		$math = new \PHPMathParser\Math();
		$expr = '(((((1 + 2 * ((3 + 4) * 5 + 6)) - 2) / 9) ^ 2) / 3 / 3) / 2 + 0.5';
		$answer = $math->evaluate((string)$expr); // int(5)
		//--
		if($answer == 5) {
			$test_answer = 'OK';
		} else {
			$test_answer = 'FAIL';
		} //end if else
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: Math Parser',
			'main' => '<h1 id="qunit-test-result">Math Expression Parser Test: '.Smart::escape_html($test_answer).'</h1><h2>Math Expression:</h2><pre>'.Smart::escape_html($expr).'</pre><hr>'.'<h2>Evaluated Result</h2><pre>'.Smart::escape_html($answer).'</pre><hr>'.'<br><br>'
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
