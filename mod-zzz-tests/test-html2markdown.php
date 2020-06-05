<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / Html2Markdown
// Route: ?page=zzz-tests.test-html2markdown
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
		if(!class_exists('\\League\\HTMLToMarkdown\\HtmlConverter')) {
			if(!is_file('modules/vendor/League/autoload.php')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: Cannot Load League/HTMLToMarkdown/HtmlConverter ...');
				return;
			} //end if
			require_once('modules/vendor/League/autoload.php');
		} //end if
		//--
		$html = (string) SmartMarkersTemplating::render_file_template($this->ControllerGetParam('module-view-path').'test-html2markdown.mtpl.htm',[]);
		$converter = new \League\HTMLToMarkdown\HtmlConverter([ 'strip_tags' => true ]);
		$markdown = $converter->convert($html);
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: Html2Markdown',
			'main' => '<h1 id="qunit-test-result">HTML2Markdown Test: OK</h1><h2>Converted Markdown</h2><pre>'.Smart::escape_html($markdown).'</pre><hr>'.'<h2>Original HTML</h2><pre>'.Smart::escape_html($html).'</pre><hr>'.'<br><br>'
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
