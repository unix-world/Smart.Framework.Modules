<?php
// Class: \SmartModExtLib\TplNetteLatte\SmartNetteLatteTemplating
// [Smart.Framework.Modules - NetteLatte / Smart NetteLatteTemplating]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplNetteLatte;

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
 * Provides an easy to use connector for NetteLatte Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     \SmartModExtLib\TplNetteLatte\SmartNetteLatteTemplating::render_file_template(
 *         'modules/my-module-name/views/my-view.latte.htm',
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
 * @depends 	classes: Latte, \SmartModExtLib\TplNetteLatte\Templating
 * @version 	v.20200121
 * @package 	modules:TemplatingEngine
 *
 */
final class SmartNetteLatteTemplating implements \SmartModExtLib\Tpl\InterfaceSmartTemplating {

	// ::

	private static $engine = null;


	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			return '{# ERROR: '.__CLASS__.' ('.\SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl cannot be found ... #}';
		} //end if
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl-nette-latte')) {
			return '{# ERROR: '.__CLASS__.' ('.\SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-nette-latte cannot be found ... #}';
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
			self::$engine = new \SmartModExtLib\TplNetteLatte\Templating();
		} //end if
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
