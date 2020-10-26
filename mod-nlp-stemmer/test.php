<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Natural Language Processing Stemmer Sample
// Route: ?/page/nlp-stemmer.test (?page=nlp-stemmer.test)
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
		$keywords = [ 'equations', 'inequations', 'appears' ];
		$stems = [];
		$roots = [];
		foreach($keywords as $key => $val) {
			$stems[(string)$val] = (string) (new \SmartModExtLib\NlpStemmer\English())->stem((string)$val);
		} //end foreach
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Sample Natural Language Processing: Stemmer',
			'main' => '<h1 id="qunit-test-result">Stemmer Algorithm Keywords Processor</h1>'.'<h3>Keywords: `<i>'.Smart::escape_html(SmartUtils::pretty_print_var($keywords)).'</i>`</h3>'.'<hr>'.'<pre><b>Stems:</b>&nbsp;'.Smart::escape_html(SmartUtils::pretty_print_var($stems)).'</pre>'
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


// end of php code
