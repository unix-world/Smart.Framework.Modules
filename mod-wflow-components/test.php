<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Wflow Components Test Sample
// Route: ?/page/wflow-components.test (?page=wflow-components.test)
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
		$run = $this->RequestVarGet('run', '', 'string');
		//--
		switch((string)$run) {
			case 'texteditor': // display text editor component
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = '<h1>Test TextEditor Component</h1>';
				$main .= '<script type="text/javascript">'.SmartComponents::js_code_init_away_page().'</script>';
				$main .= SmartComponents::html_jsload_editarea(); // codemirror is optional for CKEditor, but if found, will use it ;)
				$main .= \SmartModExtLib\WflowComponents\TextEditor::html_jsload_texteditarea('?/page/wflow-components.test/run/texteditor-fm');
				$main .= \SmartModExtLib\WflowComponents\TextEditor::html_js_texteditarea('test_html_area', 'test_html_area', '', '920px', '470px', true);
				$main .= '<button class="ux-button" style="margin-top:10px;" onClick="alert($(\'#test_html_area\').val());">Get HTML Source</button>';
				//--
				break;
			case 'texteditor-fm': // display media gallery for text editor component
				$arr = [
					'lib/core/img/app/server.svg',
					'lib/core/img/app/session.svg'
				];
				$main = '<h1>Test TextEditor Component - Media Gallery</h1>';
				for($i=0; $i<Smart::array_size($arr); $i++) {
					$main .= '<img src="'.Smart::escape_html($arr[$i]).'" onClick="'.Smart::escape_html(\SmartModExtLib\WflowComponents\TextEditor::html_js_texteditarea_fm_callback($arr[$i], true)).'" style="cursor:pointer;">';
				} //end for
				break;
			default:
				//--
				$main = '<h1>Test: Workflow Components (Smart.Framework.Modules)</h1><br><a class="ux-button" href="?/page/wflow-components.test/run/texteditor">Text Editor</a>';
				//--
		} //end switch
		//--

		//--
		$this->PageViewSetVars(array(
			'title' 	=> 'Test Mod Wflow Components',
			'main' 		=> (string) $main
		));
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
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS

//end of php code
?>