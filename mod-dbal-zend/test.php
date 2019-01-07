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
		$configs = [];
		//--
		$configs['zend-dbal']['pdo_sqlite'] = Smart::get_from_config('zend-dbal.pdo_sqlite');
		if(Smart::array_size($configs['zend-dbal']['pdo_sqlite']) <= 0) {
			$configs['zend-dbal']['pdo_sqlite']					= array();
			$configs['zend-dbal']['pdo_sqlite']['driver'] 		= 'PDO_SQLITE';
			$configs['zend-dbal']['pdo_sqlite']['database'] 	= 'tmp/zend-test.sqlite';
		} //end if
		//--
		$configs['zend-dbal']['pdo_pgsql'] = Smart::get_from_config('zend-dbal.pdo_pgsql');
		if(Smart::array_size($configs['zend-dbal']['pdo_pgsql']) <= 0) {
			$cfg_pgsql = Smart::get_from_config('pgsql');
			$configs['zend-dbal']['pdo_pgsql']						= array();
			if(Smart::array_size($cfg_pgsql) > 0) {
				$configs['zend-dbal']['pdo_pgsql']['driver'] 		= 'PDO_PGSQL';
				$configs['zend-dbal']['pdo_pgsql']['database'] 		= (string) $cfg_pgsql['dbname'];
				$configs['zend-dbal']['pdo_pgsql']['host'] 			= (string) $cfg_pgsql['server-host'];
				$configs['zend-dbal']['pdo_pgsql']['port'] 			= (int)    $cfg_pgsql['server-port'];
				$configs['zend-dbal']['pdo_pgsql']['username'] 		= (string) $cfg_pgsql['username'];
				$configs['zend-dbal']['pdo_pgsql']['password'] 		= (string) base64_decode((string)$cfg_pgsql['password']);
			} else {
				$configs['zend-dbal']['pdo_pgsql']['database'] 		= 'smart_framework';
				$configs['zend-dbal']['pdo_pgsql']['host'] 			= '127.0.0.1';
				$configs['zend-dbal']['pdo_pgsql']['port'] 			= 5432;
				$configs['zend-dbal']['pdo_pgsql']['username'] 		= 'pgsql';
				$configs['zend-dbal']['pdo_pgsql']['password'] 		= 'pgsql';
			} //end if else
		} //end if
		//--
		$configs['zend-dbal']['pdo_mysql'] = Smart::get_from_config('zend-dbal.pdo_mysql');
		if(Smart::array_size($configs['zend-dbal']['pdo_mysql']) <= 0) {
			$cfg_mysqli = Smart::get_from_config('mysqli');
			$configs['zend-dbal']['pdo_mysql']						= array();
			if(Smart::array_size($cfg_mysqli) > 0) {
				$configs['zend-dbal']['pdo_mysql']['driver'] 		= 'PDO_MYSQL';
				$configs['zend-dbal']['pdo_mysql']['database'] 		= (string) $cfg_mysqli['dbname'];
				$configs['zend-dbal']['pdo_mysql']['host'] 			= (string) $cfg_mysqli['server-host'];
				$configs['zend-dbal']['pdo_mysql']['port'] 			= (int)    $cfg_mysqli['server-port'];
				$configs['zend-dbal']['pdo_mysql']['username'] 		= (string) $cfg_mysqli['username'];
				$configs['zend-dbal']['pdo_mysql']['password'] 		= (string) base64_decode((string)$cfg_mysqli['password']);
			} //end if else
		} //end if
		//--

		//--
		$zconf = [];
		switch((string)$driver) {
			case 'sqlite':
				if(Smart::array_size($configs['zend-dbal']['pdo_sqlite']) <= 0) {
					$this->PageViewSetErrorStatus(500, 'ERROR: Zend/DBAL Test: Invalid Config: SQLite');
					return;
				} //end if
				$zconf = (array) $configs['zend-dbal']['pdo_sqlite'];
				break;
			case 'pgsql':
				if(Smart::array_size($configs['zend-dbal']['pdo_pgsql']) <= 0) {
					$this->PageViewSetErrorStatus(500, 'ERROR: Zend/DBAL Test: Invalid Config: PostgreSQL');
					return;
				} //end if
				$zconf = (array) $configs['zend-dbal']['pdo_pgsql'];
				break;
			case 'mysql':
				if(Smart::array_size($configs['zend-dbal']['pdo_mysql']) <= 0) {
					$this->PageViewSetErrorStatus(500, 'ERROR: Zend/DBAL Test: Invalid Config: MySQL');
					return;
				} //end if
				$zconf = (array) $configs['zend-dbal']['pdo_mysql'];
				break;
			default:
				$this->PageViewSetErrorStatus(400, 'ERROR: Zend/DBAL Test: Invalid Driver Selected: `'.$driver.'`');
				return;
		} //end switch
		//--

		//--
		if(Smart::array_size($zconf) <= 0) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Zend/DBAL Test: Invalid Config Detected ...');
			return;
		} //end if
		//--

		//--
		$db = new \SmartModExtLib\DbalZend\DbalPdo((array)$zconf);
		//--
		$adapter = $db->getConnection();
		//--

		//--
		$db->write_data('DROP TABLE IF EXISTS sf_zend_dbal_test', 'QUERY_MODE_EXECUTE');
		//--
		$table = new \Zend\Db\Sql\Ddl\CreateTable('sf_zend_dbal_test', true);
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