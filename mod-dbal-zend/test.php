<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Zend Dbal Test Sample
// Route: ?/page/dbal-zend.test (?page=dbal-zend.test)
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

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
			$this->PageViewSetErrorStatus(503, 'ERROR: Zend/DBAL Test mode is disabled ...');
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
				$zconf = 'sqlite:tmp/test-zend-dbal.sqlite';
				break;
			case 'pgsql':
				if(Smart::array_size(Smart::get_from_config('pgsql')) <= 0) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Zend/DBAL PostgreSQL Config is Not Available ...');
					return;
				} //end if
				$zconf = 'pgsql:config';
				break;
			case 'mysql':
				if(Smart::array_size(Smart::get_from_config('mysqli')) <= 0) {
					$this->PageViewSetErrorStatus(503, 'ERROR: Zend/DBAL MySQL Config is Not Available ...');
					return;
				} //end if
				$zconf = 'mysqli:config';
				break;
			default:
				$this->PageViewSetErrorStatus(400, 'ERROR: Zend/DBAL Test: Invalid Driver Selected: `'.$driver.'`');
				return;
		} //end switch
		//--
		if((string)$zconf == '') {
			$this->PageViewSetErrorStatus(500, 'ERROR: Zend/DBAL Test: Invalid Config Selected ...');
			return;
		} //end if
		//--

		//--
		$db = new \SmartModExtLib\DbalZend\DbalPdo((string)$zconf);
		//--
		$adapter = $db->getConnection();
		//--

		//--
		$db->write_data('DROP TABLE IF EXISTS sf_zend_dbal_test', 'QUERY_MODE_EXECUTE');
		//--
		$table = new \Zend\Db\Sql\Ddl\CreateTable('sf_zend_dbal_test', false); // set second parameter to TRUE to create a TEMPORARY table
		$table->addColumn(new \Zend\Db\Sql\Ddl\Column\Integer('id'));
		$table->addConstraint(new \Zend\Db\Sql\Ddl\Constraint\PrimaryKey('id'));
		$table->addColumn(new \Zend\Db\Sql\Ddl\Column\Varchar('name', 100));
		$table->addColumn(new \Zend\Db\Sql\Ddl\Column\Text('descr'));
		$table->addColumn(new \Zend\Db\Sql\Ddl\Column\Integer('cnt'));
		$sql = new \Zend\Db\Sql\Sql($adapter);
		$adapter->query(
			$sql->getSqlStringForSqlObject($table),
			$adapter::QUERY_MODE_EXECUTE
		);
		//--

		//--
		$db->write_data('DELETE FROM sf_zend_dbal_test');
		//--
		$affected = $db->write_data(
			'INSERT INTO sf_zend_dbal_test (id, name, descr, cnt) VALUES (?, ?, ?, ?)',
			[1, 'Name 1', 'Descr 1', 0]
		);
		if($affected != 1) {
			throw new Exception('Failed to Add Record #1 ('.$affected.')');
			return;
		} //end if
		//--
		$affected = $db->write_data(
			'INSERT INTO sf_zend_dbal_test (id, name, descr, cnt) VALUES (?, ?, ?, ?)',
			[2, 'Name 2', 'Descr 2', 0]
		);
		if($affected != 1) {
			throw new Exception('Failed to Add Record #2');
			return;
		} //end if
		//--
		$affected = $db->write_data(
			'UPDATE sf_zend_dbal_test SET cnt = cnt + 1 WHERE id > ?',
			[0]
		);
		if($affected != 2) {
			throw new Exception('Failed to Update Records');
			return;
		} //end if
		//--

		//--
		$count = $db->count_data('SELECT COUNT(1) FROM sf_zend_dbal_test');
		if($count != 2) {
			throw new Exception('Invalid Records Count');
			return;
		} //end if
		//--

		//--
		$results = $db->read_adata(
			'SELECT * FROM sf_zend_dbal_test WHERE id > ?',
			[0]
		);
		if((Smart::array_size($results) != 2) OR (Smart::array_size($results[0]) != 4) OR (Smart::array_size($results[1]) != 4)) {
			throw new Exception('Invalid Records aRead');
			return;
		} //end if
		//--

		//--
		$results = $db->read_asdata(
			'SELECT * FROM sf_zend_dbal_test WHERE id > ? LIMIT 1 OFFSET 0',
			[1]
		);
		if(Smart::array_size($results) != 4) {
			throw new Exception('Invalid Records asRead');
			return;
		} //end if
		//--

		//--
		$sql = new \Zend\Db\Sql\Sql($adapter);
		$select = $sql->select();
		$select->from('sf_zend_dbal_test');
		$select->where(array('id' => 1));
		$sqlstr = (string) $sql->getSqlStringForSqlObject($select);
		$results = (array) $adapter->query($sqlstr, [])->toArray();
		if((Smart::array_size($results) != 1) OR (Smart::array_size($results[0]) != 4)) {
			throw new Exception('Invalid Records Zend/SQL');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Test: Zend DBAL',
			'main'  => '<h1 id="qunit-test-result">Test OK: Zend-DBAL/PDO-'.Smart::escape_html(strtoupper((string)$driver)).'.</h1><br><h2>Driver: '.Smart::escape_html($db->getDriver())
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


//end of php code
?>