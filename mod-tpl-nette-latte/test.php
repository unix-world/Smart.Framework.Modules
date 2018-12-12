<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: NetteLatte Templating Test Sample
// Route: ?/page/tpl-nette-latte.test (?page=tpl-nette-latte.test)
// Author: unix-world.org
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

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
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<script type="text/javascript" src="modules/mod-js-components/views/js/highlightjs-extra/syntax/tpl/latte.js"></script>'.'<h1>nette/Latte Template Source:<br><i>'.Smart::escape_html($tpl).'</i></h1><hr><pre style="background:#FAFAFA;"><code class="latte" style="width:96vw; height:75vh; overflow:auto;">'.Smart::escape_html((string)SmartFileSystem::read((string)$tpl)).'</code></pre><hr><br>');
			return;
			//--
		} elseif((string)$op == 'viewpartialsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<script type="text/javascript" src="modules/mod-js-components/views/js/highlightjs-extra/syntax/tpl/latte.js"></script>'.'<h1>nette/Latte Sub-Template Source:<br><i>'.Smart::escape_html($ptpl).'</i></h1><hr><pre style="background:#FAFAFA;"><code class="latte" style="width:96vw; height:75vh; overflow:auto;">'.Smart::escape_html((string)SmartFileSystem::read((string)$ptpl)).'</code></pre><hr><br>');
			return;
			//--
		} //end if
		//--

		//--
		$data = [
			'version' => (string) \SmartModExtLib\TplNetteLatte\Templating::getVersion(),
			'hello' => '<h1>Demo: nette/Latte Templating as module for Smart.Framework</h1>',
			'navigation' => [
				array('href' => '#link1', 'caption' => 'Sample Link <1>'),
				array('href' => '#link2', 'caption' => 'Sample Link <2>'),
				array('href' => '#link3', 'caption' => 'Sample Link <3>')
			],
			'tbl' => [
				['a1' => '1.1', 'a2' => '1.2', 'a3' => '1.3'],
				['a1' => '2.1', 'a2' => '2.2', 'a3' => '2.3'],
				['a1' => '3.1', 'a2' => '3.2', 'a3' => '3.3']
			],
			'a' 		=> 'Test-1',
			'b' 		=> 'Test-2'
		];
		//--

		//--
		$res_time = (float) microtime(true);
		$this->PageViewSetVars([
			'title' => 'Sample nette/Latte Templating',
			'main' => (string) (new \SmartModExtLib\TplNetteLatte\Templating())->render_file_template(
				(string) $tpl,
				(array)  $data
			),
			'aside' => '<div style="background:#333333; color:#ffffff; position:fixed; right:5px; top:10px; padding:3px;">RenderTime:&nbsp;'.Smart::format_number_dec((float)(microtime(true) - (float)$res_time), 7).'&nbsp;s</div>'
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


//end of php code
?>