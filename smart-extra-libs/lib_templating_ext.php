<?php
// [LIB - SmartFramework / ExtraLibs / Extended Templating]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Provides an easy to use connector for All Templating Engines available inside the Smart.Framework at once.
 * Depending upon the file name to render will choose like this:
 * some-file.mtpl.htm 		: SmartMarkersTemplating 	(Markers-TPL syntax)
 * some-file.latte.htm 		: SmartNetteLatteTemplating (NetteLatte-TPL syntax)
 * some-file.twig.htm 		: SmartTwigTemplating 		(Twig-TPL syntax)
 * some-file.t3fluid.htm 	: SmartTypo3FluidTemplating (Typo3Fluid-TPL syntax)
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     SmartTemplating::render_file_template(
 *         'modules/my-module-name/views/my-view.(mtpl|latte|twig|t3fluid).htm',
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
 * @depends 	classes: SmartMarkersTemplating, SmartNetteLatteTemplating, SmartTwigTemplating, SmartTypo3FluidTemplating
 * @version 	v.181217
 * @package 	Templating:Engines
 *
 */
final class SmartTemplating {

	// ::


	public static function render_file_template($file, $arr_vars=array(), $options=[]) {
		//--
		if(!is_array($options)) {
			$options = array();
		} //end if
		//--
		if(strpos((string)$file, '.mtpl.') !== false) { // markers TPL
			//--
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(string) $options['use-caching'] // no / yes
			);
			//--
		} elseif(strpos((string)$file, '.latte.') !== false) { // netteLatte TPL
			//--
			return (string) SmartNetteLatteTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false / true
			);
			//--
		} elseif(strpos((string)$file, '.twig.') !== false) { // Twig TPL
			//--
			return (string) SmartTwigTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false / true
			);
			//--
		} elseif(strpos((string)$file, '.t3fluid.') !== false) { // Typo3Fluid TPL
			//--
			return (string) SmartTypo3FluidTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false/true
			);
			//--
		} else { // ERROR
			//--
			return '{# ERROR: '.__CLASS__.' ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: Cannot determine the Templating Engine type to use for the file: '.@basename($file).' ... #}';
			//--
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Provides an easy to use connector for NetteLatte Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     SmartNetteLatteTemplating::render_file_template(
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
 * @version 	v.181217
 * @package 	Templating:Engines
 *
 */
final class SmartNetteLatteTemplating {

	// ::

	private static $engine = null;


	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl-nette-latte')) {
			return '{# ERROR: '.__CLASS__.' ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-nette-latte cannot be found ... #}';
		} //end if
		//--
		if(self::$engine === null) {
			self::$engine = new \SmartModExtLib\TplNetteLatte\Templating();
		} //end if
		//--
		return (string) self::$engine->render_file_template((string)$file, (array)$arr_vars, (bool)$onlydebug);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Provides an easy to use connector for Twig Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     SmartTwigTemplating::render_file_template(
 *         'modules/my-module-name/views/my-view.twig.htm',
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
 * @depends 	classes: Twig, \SmartModExtLib\TplTwig\Templating
 * @version 	v.181217
 * @package 	Templating:Engines
 *
 */
final class SmartTwigTemplating {

	// ::

	private static $engine = null;


	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl-twig')) {
			return '{# ERROR: '.__CLASS__.' ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-twig cannot be found ... #}';
		} //end if
		//--
	//	if(self::$engine === null) {
		if((self::$engine === null) OR (\SmartFrameworkRuntime::ifDebug())) { // bug fix: on debug do not reuse the object on Twig
			self::$engine = new \SmartModExtLib\TplTwig\Templating();
		} //end if
		//--
		return (string) self::$engine->render_file_template((string)$file, (array)$arr_vars, (bool)$onlydebug);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Provides an easy to use connector for Typo3Fluid Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     SmartTypo3FluidTemplating::render_file_template(
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
 * @depends 	classes: Latte, \SmartModExtLib\TplTypo3Fluid\Templating
 * @version 	v.181217
 * @package 	Templating:Engines
 *
 */
final class SmartTypo3FluidTemplating {

	// ::

	private static $engine = null;


	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl-typo3-fluid')) {
			return '{# ERROR: '.__CLASS__.' ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-typo3-fluid cannot be found ... #}';
		} //end if
		//--
		if(self::$engine === null) {
			self::$engine = new \SmartModExtLib\TplTypo3Fluid\Templating();
		} //end if
		//--
		return (string) self::$engine->render_file_template((string)$file, (array)$arr_vars, (bool)$onlydebug);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>