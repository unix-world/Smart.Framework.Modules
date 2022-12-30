<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Laminas Dbal Test Sample
// Route: ?/page/dbal-laminas.test (?page=dbal-laminas.test)
// (c) 2006-2022 unix-world.org - all rights reserved

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
class SmartAppIndexController extends SmartAbstractAppController { // v.20221227

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if((!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS')) OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Laminas/DBAL Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$driver = $this->RequestVarGet('driver', '', 'string');
		//--

		//--
		$zconf = '';
		switch((string)$driver) {
			case 'sqlite':
				if(Smart::array_size(Smart::get_from_config('sqlite')) <= 0) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Laminas/DBAL SQlite Config is Not Available ...');
					return;
				} //end if
				$zconf = 'sqlite:tmp/test-laminas-dbal.sqlite';
				break;
			case 'pgsql':
				if(Smart::array_size(Smart::get_from_config('pgsql')) <= 0) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Laminas/DBAL PostgreSQL Config is Not Available ...');
					return;
				} //end if
				$zconf = 'pgsql:config';
				break;
			case 'mysql':
				if(Smart::array_size(Smart::get_from_config('mysqli')) <= 0) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Laminas/DBAL MySQL Config is Not Available ...');
					return;
				} //end if
				$zconf = 'mysql:config';
				break;
			default:
				$this->PageViewSetErrorStatus(400, 'ERROR: Laminas/DBAL Test: Invalid Driver Selected: `'.$driver.'`');
				return;
		} //end switch
		//--
		if((string)$zconf == '') {
			$this->PageViewSetErrorStatus(500, 'ERROR: Laminas/DBAL Test: Invalid Config Selected ...');
			return;
		} //end if
		//--

		//--
		$db = new \SmartModExtLib\DbalLaminas\DbalPdo((string)$zconf);
		$isFatalErrMode = $db->getFatalErrMode();
		$db->setFatalErrMode(false);
		$drv = $db->getDriver();
		$platform = $db->getPlatform();
		$adapter = $db->getConnection();
		//--

		//--
		if(SmartEnvironment::ifDevMode() === true) {
			$db->enableProfiling();
		} //end if
		//--

		//--
		$db->queryExecute('DROP TABLE IF EXISTS sf_laminas_dbal_test');
		//--
		$table = new \Laminas\Db\Sql\Ddl\CreateTable('sf_laminas_dbal_test', false); // set second parameter to TRUE to create a TEMPORARY table
		$table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Integer('id'));
		$table->addConstraint(new \Laminas\Db\Sql\Ddl\Constraint\PrimaryKey('id'));
		$table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Varchar('name', 100));
		$table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Text('descr'));
		$table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Integer('cnt'));
		/* is better to use the following not this to handle debug registration of this query ; if using this would require more complicate steps to debug queries using a profiler
		$sql = new \Laminas\Db\Sql\Sql($adapter);
		$adapter->query(
			$sql->buildSqlString($table),
			$adapter::QUERY_MODE_EXECUTE
		);
		*/
		$sql = $db->getSqlBuilder();
		$db->queryExecute((string) $sql->buildSqlString($table));
		//--

		//--
		$db->queryExecute('BEGIN');
		$db->queryWrite('DELETE FROM sf_laminas_dbal_test');
		$db->queryExecute('COMMIT'); // $db->queryWrite('ROLLBACK');
		//--
		$affected = $db->queryWrite(
			'INSERT INTO sf_laminas_dbal_test (id, name, descr, cnt) VALUES (?, ?, ?, ?)',
			[1, 'Name 1', 'Descr 1', 0]
		);
		if($affected != 1) {
			throw new Exception('Failed to Add Record #1 ('.$affected.')');
			return;
		} //end if
		//--
		$affected = $db->queryWrite(
			'INSERT INTO sf_laminas_dbal_test (id, name, descr, cnt) VALUES (?, ?, ?, ?)',
			[2, 'Name 2', 'Descr 2', 0]
		);
		if($affected != 1) {
			throw new Exception('Failed to Add Record #2');
			return;
		} //end if
		//--
		$affected = $db->queryWrite(
			'UPDATE sf_laminas_dbal_test SET cnt = cnt + 1 WHERE id > ?',
			[0]
		);
		if($affected != 2) {
			throw new Exception('Failed to Update Records');
			return;
		} //end if
		//--

		//--
		$count = $db->queryCountRecords('SELECT COUNT(1) FROM sf_laminas_dbal_test');
		if($count != 2) {
			throw new Exception('Invalid Records queryCountRecords');
			return;
		} //end if
		//--

		//--
		$results = $db->queryReadAsListMultiRecords(
			'SELECT * FROM sf_laminas_dbal_test WHERE id > :id',
			['id' => 0]
		);
		if(Smart::array_size($results) != 8) {
			throw new Exception('Invalid Records queryReadAsListMultiRecords');
			return;
		} //end if
		//--

		//--
		$results = $db->queryReadMultiRecords(
			'SELECT * FROM sf_laminas_dbal_test WHERE id > :id',
			['id' => 0]
		);
		if((Smart::array_size($results) != 2) OR (Smart::array_size($results[0]) != 4) OR (Smart::array_size($results[1]) != 4)) {
			throw new Exception('Invalid Records queryReadMultiRecords');
			return;
		} //end if
		//--

		//--
		$results = $db->queryReadSingleRecord(
			'SELECT * FROM sf_laminas_dbal_test WHERE id > ? LIMIT 1 OFFSET 0',
			[1]
		);
		if(Smart::array_size($results) != 4) {
			throw new Exception('Invalid Records queryReadSingleRecord');
			return;
		} //end if
		//--

		//--
	//	$sql = new \Laminas\Db\Sql\Sql($adapter);
		$sql = $db->getSqlBuilder();
		$select = $sql->select();
		$select->from('sf_laminas_dbal_test');
		$select->where(array('id' => 1));
		$sqlstr = (string) $sql->buildSqlString($select);
	//	$results = (array) $adapter->query($sqlstr, [])->toArray(); // is better to use the following not this to handle debug registration of this query ; if using this would require more complicate steps to debug queries using a profiler
		$results = (array) $db->queryReadMultiRecords((string)$sqlstr);
		if((Smart::array_size($results) != 1) OR (Smart::array_size($results[0]) != 4)) {
			throw new Exception('Invalid Records Laminas/SQL');
			return;
		} //end if
		//--

		//--
		if(SmartEnvironment::ifDevMode() === true) {
			$profile_queries = (array) $db->getProfilingData(); // this can be used to show all driver queries if will use the $adapter->query() instead of $db->count/read*/write methods
		//	print_r($profile_queries); die();
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Test: Laminas DBAL',
			'main'  => '<h1 id="qunit-test-result">Test OK: Laminas-DBAL/PDO-'.Smart::escape_html(strtoupper((string)$driver)).'.</h1><br><h2>Driver: '.Smart::escape_html($db->getDriver())
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
