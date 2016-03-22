<?php
// Controller: ModJsComponents/Test
// Route: ?/page/js-components.test (?page=js-components.test)
// Author: unix-world.org
// r.2016-02-24

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
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

		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--
		switch((string)$op) {
			case 'ckeditor':
			default:
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = '<h1>Advanced WYSIWYG EDITOR</h1>';
				$main .= SmartComponents::js_init_away_page();
				$main .= SmartComponents::js_init_editarea(); // codemirror is optional for CKEditor, but if found, will use it ;)
				$main .= \SmartModExtLib\JsComponents\ExtraJsComponents::js_init_html_area();
				$main .= \SmartModExtLib\JsComponents\ExtraJsComponents::js_draw_html_area('test_html_area', 'test_html_area', '', '920px', '470px', true);
				$main .= '<button class="ux-button" onClick="alert($(\'#test_html_area\').val());">Get HTML Source</button>';
				//--
				break;
		} //end switch
		//--

		//--
		$this->PageViewSetVars(array(
			'title' => 'Test Mod Js Components',
			'main' => $main
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