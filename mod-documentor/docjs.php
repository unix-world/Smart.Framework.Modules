<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/DocJs (display, save)
// Route: admin.php?page=documentor.docjs{&cls=SomeClass&ref={&action=save{&mode=multi}}}
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'ADMIN'); // INDEX, ADMIN, SHARED
define('SMART_APP_MODULE_AUTH', true); // if set to TRUE requires auth always


final class SmartAppAdminController extends SmartAbstractAppController {


	public function Initialize() {

		//--
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-documentor.htm'); // the default template
		//--

		//--
		$this->PageViewSetVars([
			//--
			'fonts-path' 		=> (string) $this->ControllerGetParam('module-path').'fonts/',
			'logo-img' 			=> (string) 'lib/framework/img/sf-logo.svg',
			'year' 				=> (string) date('Y'),
			//--
			'title' 			=> (string) 'Documentation',
			'heading-title' 	=> (string) 'PHP Documentation',
			'seo-description'	=> (string) 'Smart.Framework Documentation',
			'seo-keywords'		=> (string) 'php, smart, framework, documentor',
			'seo-summary' 		=> (string) 'Smart.Framework, a PHP / Javascript Framework for Web',
			'url-index' 		=> ''
			//--
		]);
		//--

	} //END FUNCTION


	public function Run() {

		//--
		if($this->IfDebug()) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Documentor cannot be used when Debug is ON ...'); // results are unpredictable ...
			return;
		} //end if
		//--


		//--
		$js = (string) SmartFileSystem::read('lib/js/framework/smart-framework.js');
		$re = '/(\/\*\*)(.*)(\*\/)/sU';
		$matches = array();
		preg_match_all((string)$re, (string)$js, $matches, PREG_SET_ORDER);
		$arr = array();
		for($i=0; $i<Smart::array_size($matches); $i++) {
			$arr[] = (string) $matches[$i][2];
		} //end for
		$matches = array();
		print_r($arr); die();
		//--

	} //END FUNCTION


} //END CLASS


//end of php code
?>