<?php
// Class: \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating
// [Smart.Framework.Modules - Typo3Fluid / Smart Typo3FluidTemplating]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplTypo3Fluid;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


/**
 * Provides an easy to use connector for Typo3Fluid Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating::render_file_template(
 *         'modules/my-module-name/views/my-view.t3fluid.htm',
 *         [
 *             'someVar' => 'Hello World',
 *             'otherVar' => date('Y-m-d H:i:s')
 *         ]
 *     )
 * );
 *
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	classes: Typo3Fluid, \SmartModExtLib\TplTypo3Fluid\Templating
 * @version 	v.20191021
 * @package 	Templating:Engines
 *
 */
final class SmartTypo3FluidTemplating implements \SmartModExtLib\Tpl\InterfaceSmartTemplating {

	// ::

	private static $engine = null;


	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			return '{# ERROR: '.__CLASS__.' ('.\SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl cannot be found ... #}';
		} //end if
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl-typo3-fluid')) {
			return '{# ERROR: '.__CLASS__.' ('.\SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-typo3-fluid cannot be found ... #}';
		} //end if
		//--
		self::startEngine();
		//--
		return (string) self::$engine->render_file_template((string)$file, (array)$arr_vars, (bool)$onlydebug);
		//--
	} //END FUNCTION


	/**
	 * @access 		private
	 * @internal
	 */
	public static function debug($tpl) {
		//--
		return 'N/A';
		//--
	} //END FUNCTION


	//#####


	private static function startEngine() {
		//--
		if(self::$engine === null) {
			self::$engine = new \SmartModExtLib\TplTypo3Fluid\Templating();
		} //end if
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>