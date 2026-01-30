<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Typo3 Fluid Templating Test Sample r.20260128
// Route: ?/page/tpl-typo3-fluid.test (?page=tpl-typo3-fluid.test)
// (c) 2006-2021 unix-world.org - all rights reserved

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


	public function Initialize() {
		//--
		// this is pre-run
		//--
		$this->PageViewSetCfg('template-path', 'default');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
	} //END FUNCTION


	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: TPL module (mod-tpl) is missing ...');
			return;
		} //end if
		//--

		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--

		//--
		$stpl = (string) 'modules/mod-tpl/views/templating-highlight-syntax.mtpl.htm';
		//--
		$tpl = (string) $this->ControllerGetParam('module-view-path').'sample.t3fluid.htm';
		$ptpl = (string) $this->ControllerGetParam('module-view-path').'sample-partial.t3fluid.inc.htm';
		//--

		//--
		if((string)$op == 'viewsource') {
			//--
			$this->PageViewSetVar(
				'main',
				(string) SmartMarkersTemplating::render_file_template(
					(string) $stpl,
					[
						'@SUB-TEMPLATES@' => [
							'%the-tpl%|html' => (string) $tpl
						],
						'HTML-HIGHLIGHT' 	=> (string) SmartViewHtmlHelpers::html_jsload_hilitecodesyntax('body', 'light'),
						'TPL-PATH' 			=> (string) $tpl,
						'TPL-SYNTAX' 		=> 'xml',
						'TPL-TYPE' 			=> 'Typo3Fluid Template'
					]
				)
			);
			return;
			//--
		} elseif((string)$op == 'viewpartialsource') {
			//--
			$this->PageViewSetVar(
				'main',
				(string) SmartMarkersTemplating::render_file_template(
					(string) $stpl,
					[
						'@SUB-TEMPLATES@' => [
							'%the-tpl%|html' => (string) $ptpl
						],
						'HTML-HIGHLIGHT' 	=> (string) SmartViewHtmlHelpers::html_jsload_hilitecodesyntax('body', 'light'),
						'TPL-PATH' 			=> (string) $ptpl,
						'TPL-SYNTAX' 		=> 'xml',
						'TPL-TYPE' 			=> 'Typo3Fluid Sub-Template'
					]
				)
			);
			return;
			//--
		} //end if
		//--

		//--
		// !!! all templates (but not sub-templates) must start / end with the section ID: Typo3FluidTpl !!!
		//--
		$data = [ // v.20260128
			// variables are case sensitive in Typo3Fluid ; array keys that contain - and . will be replaced recursive by _ to make compliant with PHP variable names
			'version' => (string) \SmartModExtLib\TplTypo3Fluid\Templating::getVersion(),
			'UrlReload' 		=> '?/page/tpl-typo3-fluid.test',
			'UrlViewSource' 	=> '?/page/tpl-typo3-fluid.test/op/viewsource',
			'UrlViewSubSource' 	=> '?/page/tpl-typo3-fluid.test/op/viewpartialsource',
			'hello-.world' => '<h1>Demo: Typo3Fluid Templating as module for Smart.Framework</h1>',
			'navigation' => [
				[ 'href' => '#link1', 'caption' => 'Sample Link <1>' ],
				[ 'href' => '#link2', 'caption' => 'Sample Link <2>' ],
				[ 'href' => '#link3', 'caption' => 'Sample Link <3>' ]
			],
			'date_time' => (string) date('Y-m-d H:i:s O')."\t"."'".date('T')."'",
			'tbl' => [
				['a1' => '1.1', 'a2' => '1.2', 'a3' => '1.3'],
				['a1' => '2.1', 'a2' => '2.2', 'a3' => '2.3'],
				['a1' => '3.1', 'a2' => '3.2', 'a3' => '3.3']
			],
			'tcount' => 3,
			'a' => 'Test-1',
			'b' => 'Test-2'
		];
		//--

		//--
		$res_time = (float) microtime(true);
		//--
		if(class_exists('SmartTemplating') AND (Smart::random_number(0,1))) { // must enable require_once('modules/smart-extra-libs/autoload.php'); in modules/app/app-custom-bootstrap.inc.php
			$this->PageViewSetVars([
				'title' => 'Sample Typo3Fluid Templating (autodetect file extension)',
				'main' => (string) SmartTemplating::render_file_template(
					(string) $tpl, // the TPL view (syntax: Typo3Fluid-TPL ; must contain '.t3fluid.' in the file name)
					(array)  $data // the Variables array
				)
			]);
		} else {
			$this->PageViewSetVars([
				'title' => 'Sample Typo3Fluid Templating',
				'main' => (string) \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating::render_file_template(
					(string) $tpl, // the TPL view (syntax: Typo3Fluid-TPL)
					(array)  $data // the Variables array
				)
			]);
		} //end if else
		//--
		$this->PageViewSetVar('aside', '<div style="background:#333333; color:#ffffff; position:fixed; right:5px; top:10px; padding:3px;">RenderTime:&nbsp;'.Smart::format_number_dec((float)(microtime(true) - (float)$res_time), 7).'&nbsp;s</div>');
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
