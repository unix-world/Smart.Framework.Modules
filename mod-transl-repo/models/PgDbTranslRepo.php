<?php
// Class: \SmartModDataModel\TranslRepo\PgDbTranslRepo
// (c) 2019-2021 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

namespace SmartModDataModel\TranslRepo;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//-- Model: PgSQL / TranslRepo [PHP8]


class PgDbTranslRepo extends \SmartAbstractPgsqlExtDb {

	// ->

	final public function __construct() {
		//--
		$this->initConnection('pgsql-transl-repo');
		//--
	} //END FUNCTION

} //END CLASS


// end of php code
