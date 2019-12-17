<?php
// Controller: Docs/Welcome
// Route: ?page=docs.welcome
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

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
		//$action = $this->RequestVarGet('action', '', 'string');
		//--

		//--
		$this->PageViewSetVars([
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


//end of php code
?>