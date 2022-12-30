<?php
// Class: \SmartModExtLib\Tpl\AbstractTemplating
// [Smart.Framework.Modules - ABSTRACT / Templating]
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
//===================================================================================== ABSTRACT CLASS START
//=====================================================================================


/**
 * Abstract Inteface Templating
 * The extended object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE MAY NOT WORK as Expected !!!
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20221220
 * @package 	development:modules:TemplatingEngine
 *
 */
abstract class AbstractTemplating {

	// :: ABSTRACT CLASS

	private const TPL_PATH_VAR = 'Tpl_Path';


	//=====
	/**
	 * Must be re-implemented to return the real TPL Engine Version
	 * @return STRING the TPL Version String (ex: 'v.0.1')
	 */
	public static function getVersion() : string {
		//--
		return 'unknown.version';
		//--
	} //END FUNCTION
	//=====


	//=====
	public function __construct() {
		// constructor
	} //END FUNCTION
	//====


	//=====
	/**
	 * RETURN: the Rendered TPL String or Debug Array
	 */
	abstract public function renderFileTemplate(?string $file, ?array $arr_vars=[]) : string;
	//=====


	//=====
	/**
	 * RETURN: the Debug TPL String
	 */
	abstract public function getDebugInfo(?string $tpl) : string;
	//=====


	//=====
	/**
	 * UTILITY: fix array keys to be compliant with PHP variable names, but only at level 1 ; level 2..n must not be fixed as tkey can accessible in loops even if not compatible with PHP variable names
	 */
	final protected function fixArrayKeys(?array $y_arr, bool $y_allow_upper_letters) : array {
		//--
		if(!\is_array($y_arr)) { // fix bug if empty array / max nested level
			return [];
		} //end if
		//--
		$new_arr = [];
		//--
		foreach($y_arr as $key => $val) {
			$key = (string) \rtrim((string)\preg_replace('/[^0-9a-zA-Z_]/', '_', (string)$key), '_'); // dissalow ending in __ which is reserved here ; make safe variable name for PHP
			if(\SmartFrameworkSecurity::ValidateVariableName((string)$key, (bool)$y_allow_upper_letters)) {
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


	//=====
	final public function prepareNoSyntaxContent(?string $str) : string {
		//--
		return (string) \SmartMarkersTemplating::prepare_nosyntax_content((string)$str);
		//--
	} //END FUNCTION
	//=====


	//=====
	final protected function getTplPathVar() : string {
		//--
		return (string) self::TPL_PATH_VAR;
		//--
	} //END FUNCTION
	//=====


} //END ABSTRACT CLASS


//=====================================================================================
//===================================================================================== ABSTRACT CLASS END
//=====================================================================================


// end of php code
