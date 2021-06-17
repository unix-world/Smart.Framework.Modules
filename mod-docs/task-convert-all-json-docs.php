<?php
// Controller: Docs/ConvertJson:ALL (from Optimized Safe HTML, ALL, QUnit, to Markdown)
// Route: ?page=docs.task-convert-all-json-docs
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'TASK');
define('SMART_APP_MODULE_AUTH', true);


final class SmartAppTaskController extends SmartAbstractAppController {

	private const CONVERSIONS_MAX_RUN_TIMEOUT = 2105; // {{{SYNC-DOCS-CONVERSIONS-TIMEOUT}}}


	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-qunit')) {
			$this->PageViewSetErrorStatus(500, ' # Mod QUnit is missing !');
			return false;
		} //end if
		if(!SmartAppInfo::TestIfModuleExists('vendor')) {
			$this->PageViewSetErrorStatus(500, ' # Vendor module is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-qunit/templates/');
		$this->PageViewSetCfg('template-file', 'template-qunit.htm');
		//--
		return true;
		//--
	} //END FUNCTION


	public function Run() {

		//--
		$arr_realms = [];
		//--
		if(SmartFileSysUtils::check_if_safe_path((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH)) {
			if(SmartFileSystem::is_type_dir((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH)) {
				$arr = (array) (new SmartGetFileSystem(true))->get_storage((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH, false);
				$arr_realms = (array) $arr['list-dirs'];
			} //end if
		} //end if
		//--

		//--
		$max_execution_time = (int) ((Smart::array_size($arr_realms) + 1) * (int)self::CONVERSIONS_MAX_RUN_TIMEOUT);
		//--
		ini_set('max_execution_time', (int)$max_execution_time);
		if((int)ini_get('max_execution_time') !== (int)$max_execution_time) {
			$this->err = 'Failed to set PHP.INI max_execution_time as: '.(int)$max_execution_time;
			return;
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' 	=> 'QUnit Engine :: Convert Docs in '.\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH,
			'semaphore' => 'Conversions Completed ...',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$this->ControllerGetParam('action').'.mtpl.js',
				[
					//--
					'TIMEOUT-TEST' 			=> (int)    self::CONVERSIONS_MAX_RUN_TIMEOUT,
					//--
					'CHARSET' 				=> (string) $this->ControllerGetParam('charset'),
					'APP-REALM' 			=> (string) $this->ControllerGetParam('app-realm'),
					'DEBUG-MODE' 			=> (string) ($this->IfDebug() ? 'yes' : 'no'),
					'LANG' 					=> (string) $this->ControllerGetParam('lang'),
					'MODULE-PATH' 			=> (string) $this->ControllerGetParam('module-path'),
					'SRV-SCRIPT' 			=> (string) $this->ControllerGetParam('url-script'),
					//--
					'PAGE' 					=> (string) $this->ControllerGetParam('module').'.task-convert-json-docs',
					//--
					'TASKS' 				=> (array)  $arr_realms,
					//--
				]
			)
		]);

	} //END FUNCTION


} //END CLASS


// end of php code
