<?php
// Class: \SmartModDataModel\TranslRepo\PgDbTranslRepo
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModDataModel\TranslRepo;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
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
