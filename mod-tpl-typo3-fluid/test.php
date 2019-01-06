<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Typo3 Fluid Templating Test Sample
// Route: ?/page/tpl-typo3-fluid.test (?page=tpl-typo3-fluid.test)
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

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
		$op = $this->RequestVarGet('op', '', 'string');
		//--

		//--
		$tpl = (string) $this->ControllerGetParam('module-view-path').'sample.t3fluid.htm';
		$ptpl = (string) $this->ControllerGetParam('module-view-path').'sample-partial.t3fluid.inc.htm';
		//--

		//--
		if((string)$op == 'viewsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<h1>Typo3Fluid Template Source:<br><i>'.Smart::escape_html($tpl).'</i></h1><hr><pre style="background:#FAFAFA;"><code class="xml" style="width:96vw; height:75vh; overflow:auto;">'.Smart::escape_html((string)SmartFileSystem::read((string)$tpl)).'</code></pre><hr><br>');
			return;
			//--
		} elseif((string)$op == 'viewpartialsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<h1>Typo3Fluid Sub-Template Source:<br><i>'.Smart::escape_html($ptpl).'</i></h1><hr><pre style="background:#FAFAFA;"><code class="xml" style="width:96vw; height:75vh; overflow:auto;">'.Smart::escape_html((string)SmartFileSystem::read((string)$ptpl)).'</code></pre><hr><br>');
			return;
			//--
		} //end if
		//--

		//--
		// !!! all main templates must start / end with the section ID: Typo3FluidTpl
		//--
		$data = [ // v.181222
			// variables are case sensitive in Typo3Fluid ; array keys that contain - and . will be replaced recursive by _ to make compliant with PHP variable names
			'version' => (string) \SmartModExtLib\TplTypo3Fluid\Templating::getVersion(),
			'hello-.world' => '<h1>Demo: Typo3Fluid Templating as module for Smart.Framework</h1>',
			'navigation' => [
				[ 'href' => '#link1', 'caption' => 'Sample Link <1>' ],
				[ 'href' => '#link2', 'caption' => 'Sample Link <2>' ],
				[ 'href' => '#link3', 'caption' => 'Sample Link <3>' ]
			],
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
		if(class_exists('SmartTypo3FluidTemplating') AND (rand(0,1))) { // must enable require_once('modules/smart-extra-libs/autoload.php'); in modules/app/app-custom-bootstrap.inc.php
			if(class_exists('SmartTemplating') AND (rand(0,1))) {
				$this->PageViewSetVars([
					'title' => 'Sample Typo3Fluid Templating (static, autodetect file extension)',
					'main' => (string) SmartTemplating::render_file_template(
						(string) $tpl, // the TPL view (syntax: Typo3Fluid-TPL ; must contain '.t3fluid.' in the file name)
						(array)  $data // the Variables array
					)
				]);
			} else {
				$this->PageViewSetVars([
					'title' => 'Sample Typo3Fluid Templating (static)',
					'main' => (string) SmartTypo3FluidTemplating::render_file_template(
						(string) $tpl, // the TPL view (syntax: Typo3Fluid-TPL)
						(array)  $data // the Variables array
					)
				]);
			} //end if else
		} else {
			$this->PageViewSetVars([
				'title' => 'Sample Typo3Fluid Templating',
				'main' => (string) (new \SmartModExtLib\TplTypo3Fluid\Templating())->render_file_template(
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


//end of php code
?>