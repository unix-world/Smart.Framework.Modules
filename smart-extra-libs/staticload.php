<?php
// [Smart.Framework.Modules - ExtraLibs StaticLoad]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//--
require_once('modules/smart-extra-libs/version.php'); 					// extra libs version
//--

//--
// StaticLoad Extra Libs from (Smart.Framework.Modules), v.20190103
//--
require_once('modules/smart-extra-libs/lib_curl_http_ftp_cli.php'); 	// curl http/ftp connector
//--
require_once('modules/smart-extra-libs/lib_langid_cli.php'); 			// langid client
//--
require_once('modules/smart-extra-libs/lib_db_ext_pgsql.php'); 			// pgsql extended db connector
require_once('modules/smart-extra-libs/lib_db_solr.php'); 				// solr db connector
//--
require_once('modules/smart-extra-libs/lib_charts.php'); 				// gd charts
//--
require_once('modules/smart-extra-libs/lib_templating_ext.php'); 		// extended templating
//--

// end of php code
?>