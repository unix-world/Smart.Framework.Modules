<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: PageBuilder/TestFrontend
// Route: ?page=page-builder.test-frontend&section=test-page
// Author: unix-world.org
// r.181031

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

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//-- test DB
		if(Smart::array_size($this->ConfigParamGet('pgsql')) <= 0) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Service Unavailable, Database not set ...');
			return;
		} //end if
		//--

		$section = $this->RequestVarGet('section', 'test-page', 'string');

		$this->renderBuilderPage(
			(string)$section,				// page ID
			'@',							// TPL Path
			'template-test-frontend.htm', 	// TPL File
			[ 'AREA.TOP', 'MAIN', 'AREA.FOOTER', 'TITLE', 'META-DESCRIPTION', 'META-KEYWORDS' ] // Allowed TPL Markers
		);

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