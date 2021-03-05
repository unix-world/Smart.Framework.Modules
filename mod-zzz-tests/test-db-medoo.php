<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / DB@Medoo
// Route: ?page=zzz-tests.test-db-medoo
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

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
		if((!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS')) OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS !== true)) {
			$db_file = ':memory:'; // in-memory db
			$mode = 'memory';
		} else {
			$db_file = 'tmp/test-medoo.sqlite'; // file db
			if(SmartFileSystem::is_type_file($db_file)) {
				SmartFileSystem::delete($db_file);
			} //end if
			$mode = 'file';
		} //end if
		//--

		//--
		if(!class_exists('\\Medoo\\Medoo')) {
			if(!is_file('modules/vendor/Medoo/autoload.php')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: Cannot Load Medoo/Medoo ...');
				return;
			} //end if
			require_once('modules/vendor/Medoo/autoload.php');
		} //end if
		//--
		$database = new \Medoo\Medoo([
			'database_type' => 'sqlite',
			'database_file' => (string) $db_file
		]);
		$database->create('account', [
			'id' => [
				'INT',
				'NOT NULL',
				'PRIMARY KEY'
			],
			'user_name' => [
				'VARCHAR(50)',
				'NOT NULL'
			],
			'email' => [
				'VARCHAR(96)',
				'NOT NULL'
			]
		]);
		$database->insert('account', [
			'id' => 1,
			'user_name' => 'foo',
			'email' => 'foo@bar.com'
		]);
		$data1 = $database->select('account', [
			'id',
			'user_name',
			'email'
		], [
			'user_name' => 'foo'
		]);
		$data2 = $database->query(
			'SELECT id, user_name, email FROM account WHERE user_name = :user_name',
			[
				'user_name' => 'foo'
			]
		)->fetchAll(PDO::FETCH_ASSOC);
		//--
		$status = 'OK';
		if($data1 !== $data2) {
			$status = 'ERR';
		} //end if
		$data = (string) SmartUtils::pretty_print_var($data2); // convert object to array
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: DB@Medoo',
			'main' => '<h1 id="qunit-test-result">Medoo DB Driver Test: '.Smart::escape_html($status).'</h1>'.
						'<h2>DB@'.Smart::escape_html($mode).' :: Select Data:</h2><pre>'.Smart::escape_html($data).'</pre><hr>'.
						'<br><br>'
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


// end of php code
