<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Natural Language Processing Rake Sample
// Route: ?/page/nlp-rake.test (?page=nlp-rake.test)
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
		$text = 'Criteria of compatibility (this-is-a-test) linear\'s of a system of linear Diophantine equations, strict inequations, and nonstrict inequations are considered. Upper bounds for components of a minimal set of solutions and algorithms of construction of minimal generating sets of solutions for all types of systems are given.';
		//--
		$keywords = (new \SmartModExtLib\NlpRake\Rake())->extract($text);
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Sample Natural Language Processing: Rake',
			'main' => '<h1 id="qunit-test-result">Rake Algorithm Keywords Extractor</h1>'.'<h3>Text: `<i>'.Smart::escape_html($text).'</i>`</h3>'.'<hr>'.'<pre><b>Keywords:</b>&nbsp;'.Smart::escape_html(SmartUtils::pretty_print_var($keywords)).'</pre>'
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
