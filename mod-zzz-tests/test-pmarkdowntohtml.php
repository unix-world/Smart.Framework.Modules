<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / PMarkdown2Html
// Route: ?page=zzz-tests.test-pmarkdowntohtml
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
		if(!class_exists('\\SmartPMarkdownToHTML')) {
			if(!is_file('modules/smart-extra-libs/lib_pmarkdown.php')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: Cannot Load SmartPMarkdownToHTML ...');
				return;
			} //end if
			require_once('modules/smart-extra-libs/lib_pmarkdown.php');
		} //end if
		//--

		//--
		$semaphores = [];
		$semaphores[] = 'load:code-highlight-js';
	//	$semaphores[] = 'theme:light'; // {{{SYNC-DEMO-UI-THEME}}}
		$validate = true;
		$main = '';
		$main .= '<h1 style="background: #4d4028; color:#FFFFFF;">PMarkdown Syntax Render Test</h1><hr>';
		$main .= (new SmartPMarkdownToHTML(true, true, true, false, (bool)$validate))->parse((string)SmartFileSystem::read($this->ControllerGetParam('module-view-path').'pmarkdown-test.md'));
		$main .= '<hr>';
		$main .= '<h5 id="qunit-test-result">Test OK: PHP PMarkdown Render.</h5>';
		//--

		//--
		$this->PageViewSetVars([
			'semaphore' => (string) Smart::array_to_list($semaphores),
			'title' => 'ZZZ Tests: PMarkdown2Html',
			'main' => (string) $main,
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
