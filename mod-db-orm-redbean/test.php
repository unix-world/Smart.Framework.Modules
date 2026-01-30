<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Readbean ORM Test Sample
// Route: ?page=db-orm-redbean.test&driver=sqlite|pgsql|mysql
// (c) 2006-present unix-world.org - all rights reserved

use \SmartModExtLib\DbOrmRedbean\ORM as R;

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

	// r.20260130

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if((!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS')) OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: RedBean-ORM Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$driver = $this->RequestVarGet('driver', '', 'string');
		//--
		$setup = null;
		switch((string)$driver) {
			case 'sqlite':
				$setup = R::setup('sqlite:tmp/test-readbean-orm.sqlite');
				break;
			case 'pgsql':
				if(Smart::array_size(Smart::get_from_config('pgsql')) > 0) {
					$setup = R::setup('pgsql:config');
				} //end if
				break;
			case 'mysql':
				if(Smart::array_size(Smart::get_from_config('mysqli')) > 0) {
					$setup = R::setup('mysql:config');
				} //end if
				break;
			default:
				// no setup !
		} //end switch
		//--
		if(!$setup) {
			$this->PageViewSetErrorStatus(500, 'ERROR: RedBean-ORM Test: Invalid Request Detected ...');
			return;
		} //end if
		$pdo = $setup->getDatabaseAdapter()->getDatabase()->getPDO();
		//--
		R::freeze(false); // enable create schema on need
		$tblname = 'readbeantest';
		$table = R::dispense((string)$tblname);
		$records = [];
		if(R::count((string)$tblname) >= 5) {
			R::count((string)$tblname, ' id > ? ', [0]);
			$records = R::getAll('SELECT * from '.$tblname.' WHERE id > ?', [0]);
		//	print_r($test); die();
		} else {
			$table->title = 'Mr.';
			$table->author = 'Test RedBean';
			$id = R::store($table);
		} //end if else
		R::freeze(true); // disable create schema on need (restore as default)
		R::close();
		//--
		$infoFromPdo = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME).' v.'.$pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
		//--
		$this->PageViewSetVars([
			'title' => 'Test: RedBean ORM (an easy to use ORM for Smart.Framework)',
			'main'  => '<h1 id="qunit-test-result">Test OK: RedBean-ORM/'.Smart::escape_html((string)strtoupper((string)$driver)).'.</h1><br><h2>Driver: `'.Smart::escape_html((string)$driver).'`<br>PDO DB Version Info: `'.Smart::escape_html((string)$infoFromPdo).'`<hr>Records (will display only after 5 inserts, refresh the page 5 times): <pre>'.Smart::escape_html((string)SmartUtils::pretty_print_var($records, 0, true)).'</pre>',
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
