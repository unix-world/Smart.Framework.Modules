<?php
// Class: \SmartModExtLib\Tpl\InterfaceSmartTemplating
// [Smart.Framework.Modules - INTERFACE / SmartTemplating]
// (c) 2006-2022 unix-world.org - all rights reserved

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
 * @version 	v.20260128
 * @package 	development:modules:TemplatingEngine
 *
 */
interface InterfaceSmartTemplating {

	// :: INTERFACE


	//=====
	public static function version() : string;
	//=====


	//=====
	public static function render_file_template(?string $file, ?array $arr_vars=[]) : string;
	//=====


	//=====
	public static function debug(?string $tpl) : string;
	//=====


	//=====
//	private static function startEngine();
	//=====


} //END INTERFACE


//=====================================================================================
//===================================================================================== INTERFACE END
//=====================================================================================


// end of php code
