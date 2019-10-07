<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * PageBuilder Plugin
 *
 * @ignore
 *
 */
final class PageBuilderFrontendPluginPageBuilderTest2 extends \SmartModExtLib\PageBuilder\AbstractFrontendPlugin {

	// r.20191002

	public function Run() {
		//--
		$section = $this->RequestVarGet('section', '', 'string');
		//--
		$this->PageViewSetVars([
//			'meta-title' 	=> 'Page Builder : Test Plugin #2',
			'content'		=> '<div>this is Plugin2 ['.Smart::escape_html($this->ControllerGetParam('module-path').' @ plugins/'.$this->getPluginName()).'] called by ['.Smart::escape_html($this->getPluginCallerModulePath().' @ '.$this->ControllerGetParam('controller')).'] Test ['.date('Y-m-d H:i:s').'] ... @ running live (non-cached) on page-section ['.Smart::escape_html($section).'] ... the cache content of plugins must be managed separately: <pre>'.Smart::escape_html(print_r($this->getPluginConfig(),1)).'</pre></div>'
		]);
		//--
	}//END FUNCTION


	public function ShutDown() {
		// *** optional*** can be redefined in a plugin
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>