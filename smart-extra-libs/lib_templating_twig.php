<?php
// [LIB - SmartFramework / ExtraLibs / Twig Templating]
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
 * Provides an easy to use connector for Twig Templating inside the Smart.Framework.
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
 * @version 	v.181211
 * @package 	Templating:Engines
 *
 */
final class SmartTwigTemplating {

	// ::

	private static $twig = null;


	public static function render_file_template($file, $arr_vars=array()) {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl-twig')) {
			return '{# ERROR: SmartTwigTemplating ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-twig cannot be found ... #}';
		} //end if
		//--
	//	if(self::$twig === null) {
		if((self::$twig === null) OR (\SmartFrameworkRuntime::ifDebug())) { // bug fix: on debug do not reuse the object
			self::$twig = new \SmartModExtLib\TplTwig\Templating();
		} //end if
		//--
		return (string) self::$twig->render_file_template((string)$file, (array)$arr_vars);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>