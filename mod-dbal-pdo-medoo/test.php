<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: DBAL PDO Medoo Tests
// Route: ?page=dbal-pdo-medoo.test
// (c) 2006-2024 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, TASK, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$driver = $this->RequestVarGet('driver', '', 'string');
		//--
		$database = null;
		$options = null;
		$mode = '';
		switch((string)$driver) {
			case 'sqlite':
				$db_file = ':memory:'; // in-memory db
				$mode = 'memory';
				if(defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS')) {
					$db_file = 'tmp/test-medoo.sqlite'; // file db
					$mode = 'file';
				} //end if else
				$database = (new \SmartModExtLib\DbalPdoMedoo\DbalPDO())->setup('sqlite', [
					'db-file' => (string) $db_file,
				]);
				break;
			case 'pgsql':
				if(!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS')) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Database Test mode is disabled ...');
					return;
				} //end if
				$mode = 'server';
				$database = (new \SmartModExtLib\DbalPdoMedoo\DbalPDO())->setup('pgsql');
				break;
			case 'mysql':
				if(!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS')) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Database Test mode is disabled ...');
					return;
				} //end if
				$mode = 'server';
				$database = (new \SmartModExtLib\DbalPdoMedoo\DbalPDO())->setup('mysql');
				break;
			default:
				$this->PageViewSetErrorStatus(503, 'NOTICE: A database driver must be selected ...');
				return;
		} //end switch
		//--
		if(!is_object($database)) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Database object is NULL ...');
			return;
		} //end if
		//--
		$database->drop('account');
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
			'main' => '<h1 id="qunit-test-result">Medoo DB Driver DB['.Smart::escape_html((string)$driver.'/'.$mode).'] Test: '.Smart::escape_html((string)$status).'.</h1>'.
						'<h2>Selected Data:</h2><pre>'.Smart::escape_html((string)$data).'</pre><hr>'.
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


/**
 * Task Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppTaskController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in task.php

} //END CLASS


// end of php code
