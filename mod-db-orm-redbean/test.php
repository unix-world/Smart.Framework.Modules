<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Readbean ORM Test Sample
// Route: ?/page/db-orm-redbean.test (?page=db-orm-redbean.test)
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

use \SmartModExtLib\DbOrmRedbean\ORM as R;

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
			$this->PageViewSetErrorStatus(503, 'ERROR: RedBean-ORM Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$driver = $this->RequestVarGet('driver', '', 'string');
		//--
		$setup = null;
		//--
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
					$setup = R::setup('mysqli:config');
				} //end if
				break;
			default:
				// no setup !
		} //end switch
		//--
		if(!$setup) {
			$this->PageViewSetErrorStatus(500, 'ERROR: RedBean-ORM Test: Invalid Config Detected ...');
			return;
		} //end if
		//--
		R::freeze(false); // enable create schema on need
		$book = R::dispense('book');
		if(R::count('book') >= 5) {
			R::count('book', ' id > ? ', [0]);
			$test = R::getAll('SELECT * from book WHERE id > ?', [0]);
		//	print_r($test); die();
		} else {
			$book->title = 'Mr.';
			$book->author = 'Test RedBean';
			$id = R::store($book);
		} //end if else
		R::freeze(true); // disable create schema on need (restore as default)
		R::close();
		//--
		$this->PageViewSetVars([
			'title' => 'Test: RedBean ORM (an easy to use ORM for Smart.Framework)',
			'main'  => '<h1 id="qunit-test-result">Test OK: RedBean-ORM/'.Smart::escape_html(strtoupper((string)$driver)).'.</h1><br><h2>Driver: '.Smart::escape_html((string)$driver)
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