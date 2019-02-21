<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: NetteLatte Templating Test Sample
// Route: ?/page/tpl-nette-latte.test (?page=tpl-nette-latte.test)
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
		$tpl = (string) $this->ControllerGetParam('module-view-path').'sample.latte.htm';
		$ptpl = (string) $this->ControllerGetParam('module-view-path').'sample-partial.latte.inc.htm';
		//--

		//--
		if((string)$op == 'viewsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<script type="text/javascript" src="modules/mod-js-components/views/js/highlightjs-extra/syntax/tpl/latte.js"></script>'.'<h1>nette/Latte Template Source:<br><i>'.Smart::escape_html($tpl).'</i></h1><hr><pre style="background:#FAFAFA; margin:0px; padding:0px; width:98vw; height:75vh; overflow:hidden;"><code class="latte" style="margin:0px; padding:0px; width:100%; height:100%; overflow:auto;">'.Smart::escape_html((string)SmartFileSystem::read((string)$tpl)).'</code></pre><hr><br>');
			return;
			//--
		} elseif((string)$op == 'viewpartialsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<script type="text/javascript" src="modules/mod-js-components/views/js/highlightjs-extra/syntax/tpl/latte.js"></script>'.'<h1>nette/Latte Sub-Template Source:<br><i>'.Smart::escape_html($ptpl).'</i></h1><hr><pre style="background:#FAFAFA; margin:0px; padding:0px; width:98vw; height:75vh; overflow:hidden;"><code class="latte" style="margin:0px; padding:0px; width:100%; height:100%; overflow:auto;">'.Smart::escape_html((string)SmartFileSystem::read((string)$ptpl)).'</code></pre><hr><br>');
			return;
			//--
		} //end if
		//--

		//--
		$data = [ // v.181222
			// ### variables are case insensitive in controllers on the 1st level ; the template will use all lowercase variables for this instance of Latte ; array keys that contain - and . will be replaced recursive by _ to make compliant with PHP variable names
			'Version' => (string) \SmartModExtLib\TplNetteLatte\Templating::getVersion(),
			'heLLo-.World' => '<h1>Demo: nette/Latte Templating as module for Smart.Framework</h1>',
			'navigatioN' => [
				array('href' => '#link1', 'caption' => 'Sample Link <1>'),
				array('href' => '#link2', 'caption' => 'Sample Link <2>'),
				array('href' => '#link3', 'caption' => 'Sample Link <3>')
			],
			'TBl' => [
				['a1' => '1.1', 'a2' => '1.2', 'a3' => '1.3'],
				['a1' => '2.1', 'a2' => '2.2', 'a3' => '2.3'],
				['a1' => '3.1', 'a2' => '3.2', 'a3' => '3.3']
			],
			'Tcount' => 3,
			'a' => 'Test-1',
			'B' => 'Test-2'
		];
		//--

		//--
		$res_time = (float) microtime(true);
		//--
		if(class_exists('SmartNetteLatteTemplating') AND (rand(0,1))) { // must enable require_once('modules/smart-extra-libs/autoload.php'); in modules/app/app-custom-bootstrap.inc.php
			if(class_exists('SmartTemplating') AND (rand(0,1))) {
				$this->PageViewSetVars([
					'title' => 'Sample netteLatte Templating (static, autodetect file extension)',
					'main' => (string) SmartTemplating::render_file_template(
						(string) $tpl, // the TPL view (syntax: netteLatte-TPL ; must contain '.latte.' in the file name)
						(array)  $data // the Variables array
					)
				]);
			} else {
				$this->PageViewSetVars([
					'title' => 'Sample netteLatte Templating (static)',
					'main' => (string) SmartNetteLatteTemplating::render_file_template(
						(string) $tpl, // the TPL view (syntax: netteLatte-TPL)
						(array)  $data // the Variables array
					)
				]);
			} //end if else
		} else {
			$this->PageViewSetVars([
				'title' => 'Sample netteLatte Templating',
				'main' => (string) (new \SmartModExtLib\TplNetteLatte\Templating())->render_file_template(
					(string) $tpl, // the TPL view (syntax: netteLatte-TPL)
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