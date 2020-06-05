<?php
// Class: \SmartModExtLib\Tpl\AbstractTemplating
// [Smart.Framework.Modules - ABSTRACT / Templating]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Tpl;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== ABSTRACT CLASS START
//=====================================================================================


/**
 * Abstract Inteface Templating
 * The extended object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE MAY NOT WORK as Expected !!!
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20200121
 * @package 	development:modules:TemplatingEngine
 *
 */
abstract class AbstractTemplating {

	// :: ABSTRACT CLASS


	//=====
	public function __construct() {
		// constructor
	} //END FUNCTION
	//====


	//=====
	/**
	 * RETURN: the Rendered TPL String
	 */
	abstract public function render_file_template($file, $arr_vars=array(), $onlydebug=false);
	//=====


	//=====
	/**
	 * RETURN: the Debug TPL String
	 */
	abstract public function debug($tpl);
	//=====


	//=====
	/**
	 * Must be re-implemented to return the real TPL version
	 * @return STRING the TPL Version String (ex: 'v.0.1')
	 */
	public static function getVersion() {
		//--
		return 'unknown.version';
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * UTILITY: fix array keys to be compliant with PHP variable names, but only at level 1 ; level 2..n must not be fixed as tkey can accessible in loops even if not compatible with PHP variable names
	 */
	final protected function fix_array_keys($y_arr, $y_allow_upper_camelcase) {
		//--
		if(!\is_array($y_arr)) { // fix bug if empty array / max nested level
			return $y_arr; // mixed
		} //end if
		//--
		$new_arr = [];
		//--
		foreach($y_arr as $key => $val) {
			$key = (string) \rtrim((string)\preg_replace('/[^0-9a-zA-Z_]/', '_', (string)$key), '_'); // dissalow ending in __ which is reserved here ; make safe variable name for PHP
			if(\SmartFrameworkSecurity::ValidateVariableName((string)$key, (bool)$y_allow_upper_camelcase)) {
				if(\is_array($val)) {
					$new_arr[(string)$key] = (array) $val;
				} else {
					$new_arr[(string)$key] = $val; // mixed
				} //end if
			} //end if else
		} //end foreach
		//--
		return (array) $new_arr;
		//--
	} //END FUNCTION
	//=====


} //END ABSTRACT CLASS


//=====================================================================================
//===================================================================================== ABSTRACT CLASS END
//=====================================================================================


// end of php code
