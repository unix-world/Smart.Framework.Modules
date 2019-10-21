<?php
// Class: \SmartModExtLib\Tpl\InterfaceSmartTemplating
// [Smart.Framework.Modules - INTERFACE / SmartTemplating]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Tpl;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== INTERFACE START
//=====================================================================================


/**
 * Abstract Inteface Smart Templating
 * The extended object MUST NOT CONTAIN OTHER PUBLIC FUNCTIONS BECAUSE MAY NOT WORK as Expected !!!
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20191021
 *
 */
interface InterfaceSmartTemplating {

	// :: INTERFACE


	//=====
	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false);
	//=====


	//=====
	public static function debug($tpl);
	//=====


	//=====
//	private static function startEngine();
	//=====


} //END INTERFACE


//=====================================================================================
//===================================================================================== INTERFACE END
//=====================================================================================


//end of php code
?>