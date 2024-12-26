<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Highlight Syntax Test Sample
// Route: ?/page/highlight-syntax.test (?page=highlight-syntax.test)
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
		$mode = $this->RequestVarGet('mode', '', 'string');
		//--

		//--
		if((string)$mode == 'js') {
			$file = $this->ControllerGetParam('module-view-path').'js/jshighlight/highlight.js';
			$syntax = 'js'; // javascript
		} else {
			$file = $this->ControllerGetParam('module-view-path').'js/jshighlight/demo/sample-net.html';
			$syntax = 'html'; // xml
		} //end if else
		//--

		//--
		$hl = (new \SmartModExtLib\HighlightSyntax\Highlighter())->highlight((string)$syntax, (string)SmartFileSystem::read((string)$file));
		//--
		$this->PageViewSetVars([
			'title' => 'Sample Server-Side Syntax Highlight',
			'main' => '<h1 id="qunit-test-result">Syntax Highlight (Server-Side): '.strtoupper((string)$syntax).'.</h1>'.
			'<link rel="stylesheet" type="text/css" href="modules/mod-highlight-syntax/views/css/atelier-sulphurpool-dark.css">'.
			'<pre><code class="hljs syntax '.Smart::escape_html($hl->language).'" title="SYNTAX: '.$syntax.' @ '.Smart::escape_html((string)$hl->language).'">'.SmartMarkersTemplating::prepare_nosyntax_html_template((string)$hl->value).'</code></pre>'
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
