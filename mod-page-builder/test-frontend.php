<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: PageBuilder/TestFrontend
// Route: ?page=page-builder.test-frontend&section=test-page
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT S EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'INDEX');

/**
 * Index Sample Controller
 *
 * @ignore
 *
 */
final class SmartAppIndexController extends \SmartModExtLib\PageBuilder\AbstractFrontendController {

	// r.20190303

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		$section = $this->RequestVarGet('section', 'test-page', 'string');
		if((string)$section == 'test-page') {
			if(!$this->checkIfPageOrSegmentExist('test-page')) {
				$this->PageViewSetErrorStatus(404, 'PageBuilder SampleData Not Found ...');
				return;
			} //end if
		} //end if

		$this->renderBuilderPage(
			(string)$section,				// page ID
			'@',							// TPL Path
			'template-test-frontend.htm', 	// TPL File
			[ 'AREA.TOP', 'MAIN', 'AREA.FOOTER', 'TITLE', 'META-DESCRIPTION', 'META-KEYWORDS' ] // Allowed TPL Markers
		);
		$this->PageViewSetVar('title', 'Sample PageBuilder Frontend Page', false); // fallback title

		$test_segments = (array) $this->getListOfSegmentsByArea('%'); // just for test ...
	//	print_r($test_segments); die();

		//-- INTERNAL DEBUG
		/*
		$arr = $this->PageViewGetVars();
		$this->PageViewResetVars();
		$hdrs = $this->PageViewGetRawHeaders();
		$cfgs = $this->PageViewGetCfgs();
		$this->PageViewResetCfgs();
		$this->PageViewResetRawHeaders();
		$this->PageViewSetCfg('rawpage', true);
		$this->PageViewSetVars([
			'main' => '<h1>PageBuilder / Test Frontend (Cached='.\Smart::escape_html($this->PageCacheisActive()).')</h1>'.'<pre>'.\Smart::escape_html(print_r($cfgs,1)).\Smart::escape_html(print_r($hdrs,1)).\Smart::escape_html(print_r($arr,1)).'</pre>'
		]);
		unset($cfgs);
		unset($hdrs);
		unset($arr);
		*/
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>