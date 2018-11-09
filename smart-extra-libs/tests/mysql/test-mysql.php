<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: MySQL Test
// Route: ?/page/tests.test-mysql (?page=tests.test-mysql)
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

		global $configs;

		//-- dissalow run this sample if not test mode enabled
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('smart-extra-libs')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: SmartExtraLibs is missing from modules ...');
			return;
		} //end if
		//--
		if(!class_exists('SmartMysqliDb')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: SmartExtraLibs is not loaded ...');
			return;
		} //end if
		//--

		//-- MySQL related configuration of Default SQL Server (add this in etc/config.php)
		if(Smart::array_size($configs['mysqli']) <= 0) { // try some default settings ...
			$configs['mysqli']['type'] 			= 'mariadb'; 								// mysql / mariadb / percona
			$configs['mysqli']['server-host'] 	= '127.0.0.1';								// database host (default is 127.0.0.1)
			$configs['mysqli']['server-port']	= '3306';									// database port (default is 5432)
			$configs['mysqli']['dbname']		= 'smart_framework';						// database name
			$configs['mysqli']['username']		= 'root';									// sql server user name
			$configs['mysqli']['password']		= base64_encode('root');					// sql server Base64-Encoded password for that user name B64
			$configs['mysqli']['timeout']		= 30;										// connection timeout (how many seconds to wait for a valid MySQL Connection)
			$configs['mysqli']['slowtime']		= 0.0050; 									// 0.0025 .. 0.0090 slow query time (for debugging)
			$configs['mysqli']['transact']		= 'REPEATABLE READ';						// Default Transaction Level: 'REPEATABLE READ' | 'READ COMMITTED' | '' to leave it as default
		} //end if
		//--

		//--
		$this->PageViewSetCfg('template-path', 'default');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--

		//$mysql = new SmartMysqliExtDb($configs['mysqli']);
		//$mysql->read_asdata('SHOW VARIABLES');
		//unset($mysql);

		SmartMysqliDb::read_data('SELECT VERSION()');

		$mysql2 = new SmartMysqliExtDb($configs['mysqli']);
		$mysql2->read_asdata('SELECT 1 + 2');
		$mysql2->read_adata('SELECT 1 + 2');
		$mysql2->read_data('SELECT 1 + 2');
		$mysql2->count_data('SELECT 1 + 2');
		$mysql2->write_data('SELECT 1 + 2');
		unset($mysql2);

		SmartMysqliDb::read_asdata('SELECT VERSION()');

		//--
		$this->PageViewSetVar(
			'title',
			'MySQL Samples'
		);
		//--
		$this->PageViewSetVars([
			'main' => '<div align="center"><h1>MySQLi Tests Done</h1><h3>turn on debug to see test results ...</h3><br><img src="modules/smart-extra-libs/img/mysql-logo.svg"></div>'
		]);
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

	// this will clone the IndexAppModule to run exactly the same action in admin.php
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS

//end of php code
?>