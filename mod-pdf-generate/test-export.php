<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Test Samples
// Route: ?/page/pdf-generate.test-export (?page=pdf-generate.test-export)
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
		if(!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS') OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS !== true)) {
			$this->PageViewSetErrorStatus(503, 'NOTICE: PDF Test mode is not active ...');
			return;
		} //end if
		//--

		//--
		if((!\SmartModExtLib\PdfGenerate\HtmlToPdfExport::is_active()) AND (!\SmartModExtLib\PdfGenerate\HtmlUrlToPdfExport::is_active())) {
			$this->PageViewSetErrorStatus(500, 'NOTICE: PDF Exporters are not active. They must be set in configs ...');
			return;
		} //end if
		//--
		$action = $this->RequestVarGet('action', '', 'string');
		//--
		$html = (string) (new SmartMarkdownToHTML(true, false, true, null, null, true, null, true))->parse((string)SmartFileSystem::read('README.md')); // generate HTML from markdown ; C:1
		$html = (string) '<img src="modules/mod-pdf-generate/views/img/pdfexport/pdf-logo.svg" alt="pdf-logo-svg" width="52" height="64" align="right">'.'<img src="modules/mod-pdf-generate/views/img/pdfexport/pdf-logo.png" alt="pdf-logo-png" width="52" height="64" align="left"><div style="height:96px;">&nbsp;</div>'.$html;
		$html = (string) '<!DOCTYPE html>'."\n".'<html>'."\n".'<head><meta charset="UTF-8"><title>Sample HTML</title></head>'."\n".'<body>'."\n".$html.'</body>'."\n".'</html>'."\n";
		//--
		if((string)$action == 'html') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetVar(
				'main',
				(string) $html
			);
			return;
			//--
		} elseif((string)$action == 'pdf') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', \SmartModExtLib\PdfGenerate\PdfUtils::pdf_mime_header());
			$this->PageViewSetCfg('rawdisp', \SmartModExtLib\PdfGenerate\PdfUtils::pdf_disposition_header('sample-'.time().'.pdf', 'inline'));
			$this->PageViewSetVar(
				'main',
				(string) \SmartModExtLib\PdfGenerate\HtmlToPdfExport::generate(
					(string) $html, // the HTML code is converted to PDF
					'wide'
				)
			);
			return;
		} elseif((string)$action == 'pdf-url') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', \SmartModExtLib\PdfGenerate\PdfUtils::pdf_mime_header());
			$this->PageViewSetCfg('rawdisp', \SmartModExtLib\PdfGenerate\PdfUtils::pdf_disposition_header('sample-'.time().'.pdf', 'inline'));
			$this->PageViewSetVar(
				'main',
				(string) \SmartModExtLib\PdfGenerate\HtmlUrlToPdfExport::generate(
					(string) SmartUtils::get_server_current_url().SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&action=html', // the HTML URL is converted to PDF
					'wide'
				)
			);
			return;
		} //end if
		//--
		$this->PageViewSetVars([
			'title' => 'Sample PDF Export',
			'main' => '<h1>PDF Generate / Export Demo</h1>'.
				'<br><a class="ux-button" href="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&action=html'.'" data-smart="open.modal">The Sample HTML (used to generate the PDF)</a>'.
				'<br><a class="ux-button ux-button-primary" href="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&action=pdf'.'" data-smart="open.modal">Click here to generate a PDF from HTML String (requires HTMLDoc and RSvg)</a>'.
				'<br><a class="ux-button ux-button-primary" href="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&action=pdf-url'.'" data-smart="open.modal">Click here to generate a PDF from HTML Page Loaded via URL (requires WkHtmlToPdf, this example does not work on areas with Authentication as admin/task)</a>',
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller (optional)
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
