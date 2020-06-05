<?php
// Class: \SmartModExtLib\TplTwig\SmartTwigTemplating
// [Smart.Framework.Modules - Twig / Smart TwigTemplating]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplTwig;

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
 * Provides an easy to use connector for Twig Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     \SmartModExtLib\TplTwig\SmartTwigTemplating::render_file_template(
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
 * @depends 	extensions: PHP Ctype (optional) ; classes: \SmartModExtLib\Tpl\InterfaceSmartTemplating, \Twig, \Symfony\Polyfill\Ctype\Ctype if PHP Ctype ext is N/A
 * @version 	v.20200121
 * @package 	modules:TemplatingEngine
 *
 */
final class SmartTwigTemplating implements \SmartModExtLib\Tpl\InterfaceSmartTemplating {

	// ::

	private static $engine = null;


	public static function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			return '{# ERROR: '.__CLASS__.' ('.\SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl cannot be found ... #}';
		} //end if
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl-twig')) {
			return '{# ERROR: '.__CLASS__.' ('.\SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl-twig cannot be found ... #}';
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
		self::startEngine();
		//--
		return (string) self::$engine->debug($tpl);
		//--
	} //END FUNCTION


	//#####


	private static function startEngine() {
		//--
		if(self::$engine === null) {
	//	if((self::$engine === null) OR (\SmartFrameworkRuntime::ifDebug())) { // {{{SYNC-TWIG-SMARTFRAMEWORK-DEBUG-BUG}}} # BugFix: if debug do not reuse the Twig object to avoid crash on Twig's internal bug: 'Call to a member function getCurrent() on null' in mod-tpl-twig/libs/Twig/Parser.php ; this bug occurs when includding a twig sub-tpl in a {% for ... %} {% include Tpl_Dir__ ~ 'some-partial.twig.htm' %} {% endfor %}
			self::$engine = new \SmartModExtLib\TplTwig\Templating();
		} //end if
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
