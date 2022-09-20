<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Html2markdown / Test
// Route: ?page=html2markdown.test
// (c) 2006-2022 unix-world.org - all rights reserved

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
		$html = (string) SmartMarkersTemplating::render_file_template($this->ControllerGetParam('module-view-path').'tests/test-html2markdown.mtpl.htm',[]);
		$markdown = (string) \SmartModExtLib\Html2markdown\SmartHTML2Markdown::convert((string)$html);
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'HTML2Markdown Test',
			'main' => '<h1 id="qunit-test-result">HTML2Markdown Test: OK</h1><h2>Converted Markdown (v2)</h2><pre>'.Smart::escape_html((string)$markdown).'</pre><hr>'.'<h2>Original HTML</h2><pre>'.Smart::escape_html($html).'</pre><hr>'.'<br><br>'
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
