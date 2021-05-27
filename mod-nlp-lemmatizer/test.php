<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Natural Language Processing Lemmatizer Sample
// Route: ?/page/nlp-lemmatizer.test (?page=nlp-lemmatizer.test)
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
		$keywords = [ 'equations', 'inequations', 'appears' ];
		$lemmas = [];
		$roots = [];
		foreach($keywords as $key => $val) {
			$lemmas[(string)$val] = (string) \SmartModExtLib\NlpLemmatizer\Lemmatizer::getLemma((string)$val);
		} //end foreach
		foreach($lemmas as $key => $val) {
			$tmp_root = (string) \SmartModExtLib\NlpLemmatizer\Lemmatizer::getWordsFromLemma((string)$val);
			if(strpos($tmp_root, ',') === false) {
				$roots[(string)$val] = (string) $tmp_root;
			} else {
				$roots[(string)$val] = (array) explode(',', (string)$tmp_root);
			} //end if else
		} //end foreach
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Sample Natural Language Processing: Lemmatizer',
			'main' => '<h1 id="qunit-test-result">Lemmatizer Algorithm Keywords Processor</h1>'.'<h3>Keywords: `<i>'.Smart::escape_html(SmartUtils::pretty_print_var($keywords)).'</i>`</h3>'.'<hr>'.'<pre><b>Lemmas:</b>&nbsp;'.Smart::escape_html(SmartUtils::pretty_print_var($lemmas)).'</pre>'.'<hr>'.'<pre><b>Roots:</b>&nbsp;'.Smart::escape_html(SmartUtils::pretty_print_var($roots)).'</pre>'
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
