<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Docs/Welcome
// Route: ?page=docs.welcome
// (c) 2008-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'INDEX');


final class SmartAppIndexController extends SmartAbstractAppController {


	public function Initialize() {

		//--
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-docs.htm'); // the default template
		//--

		//--
		$this->PageViewSetVars([
			'header' => SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'@header.mtpl.htm',
				[
				]
			),
			'footer' => SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'@footer.mtpl.htm',
				[
					'year' => date('Y')
				]
			),
		]);
		//--

	} //END FUNCTION


	public function Run() {

		//--
		//$display = $this->RequestVarGet('display', '', 'string');
		//--

		//--
		$semaphores = [];
		//--
		$semaphores[] = 'theme:dark';
		$semaphores[] = 'skip:js-ui';
	//	$semaphores[] = 'load:searchterm-highlight-js';
		$semaphores[] = 'load:code-highlight-js';
	//	$semaphores[] = 'skip:unveil-js';
		//--

		//--
		$this->PageViewSetVars([
			'semaphore' 		=> (string) Smart::array_to_list($semaphores),
			'title' 			=> 'Smart.Framework Documentation',
			'seo-description' 	=> '',
			'seo-keywords' 		=> '',
			'seo-summary' 		=> '',
			'aside' 			=> '',
			'main' 				=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$this->ControllerGetParam('action').'.mtpl.htm',
				[
					//--

					//--
				]
			)
		]);
		//--

	} //END FUNCTION


} //END CLASS


// end of php code
