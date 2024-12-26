<?php
// Class: \SmartModExtLib\TplTwist\SmartTwistTemplating
// [Smart.Framework.Modules - TwistTPL / Smart TwistTemplating]
// (c) 2006-2022 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplTwist;

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
 * Provides an easy to use connector for Twist Templating Engine inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     \SmartModExtLib\TplTwist\SmartTwistTemplating::render_file_template(
 *         'modules/my-module-name/views/my-view.twist.htm',
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
 * @depends 	extensions: PHP Ctype ; classes: TwistTPL, \SmartModExtLib\TplTwist\Templating
 * @version 	v.20221220
 * @package 	modules:TemplatingEngine
 *
 */
final class SmartTwistTemplating implements \SmartModExtLib\Tpl\InterfaceSmartTemplating {

	// ::

	private static $engine = null;


	public static function version() : string {
		//--
		return (string) \SmartModExtLib\TplTwist\Templating::getVersion();
		//--
	} //END FUNCTION


	public static function render_file_template(?string $file, ?array $arr_vars=[]) : string {
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			return '{# ERROR: '.__CLASS__.' :: The module mod-tpl cannot be found ... #}';
		} //end if
		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-tpl-twist')) {
			return '{# ERROR: '.__CLASS__.' :: The module mod-tpl-twist cannot be found ... #}';
		} //end if
		//--
		self::startEngine();
		//--
		$out = '';
		try {
			$out = (string) self::$engine->renderFileTemplate((string)$file, (array)$arr_vars);
		} catch(\Exception $e) {
			$out = '{### ERROR: Twist TPL Render Failed ###}';
			$errmsg = 'Twist Template Render Error ['.$file.'] '.$e->getMessage();
			$displayErrMsg = (string) $errmsg;
			if(\SmartEnvironment::ifDevMode() !== true) {
				$displayErrMsg = 'Twist-TPL Render Error (see errors log for details)';
			} //end if else
			\Smart::raise_error(
				(string) $errmsg,
				(string) $displayErrMsg // msg to display
			);
		} //end try catch
		//--
		return (string) self::$engine->prepareNoSyntaxContent((string)$out);
		//--
	} //END FUNCTION


	/**
	 * @access 		private
	 * @internal
	 */
	public static function debug(?string $tpl) : string {
		//--
		self::startEngine();
		//--
		$out = '';
		try {
			$out = (string) self::$engine->getDebugInfo((string)$tpl);
		} catch(\Exception $e) {
			$out = '{### ERROR: Twist TPL Debug Failed: '.$e->getMessage().' ###}';
		} //end try catch
		//--
		return (string) self::$engine->prepareNoSyntaxContent((string)$out);
		//--
	} //END FUNCTION


	//#####


	private static function startEngine() {
		//--
		if(self::$engine === null) {
	//	if((self::$engine === null) OR (\SmartEnvironment::ifDebug())) {
			self::$engine = new \SmartModExtLib\TplTwist\Templating();
		} //end if
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
