<?php
// Class: \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating
// [Smart.Framework.Modules - Typo3Fluid / Smart Typo3FluidTemplating]
// (c) 2006-2021 unix-world.org - all rights reserved

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
 * @version 	v.20221220
 * @package 	modules:TemplatingEngine
 *
 */
final class SmartTypo3FluidTemplating implements \SmartModExtLib\Tpl\InterfaceSmartTemplating {

	// ::

	private static $engine = null;


	public static function version() : string {
		//--
		return (string) \SmartModExtLib\TplTypo3Fluid\Templating::getVersion();
		//--
	} //END FUNCTION


	public static function render_file_template(?string $file, ?array $arr_vars=[]) : string {
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
		$out = '';
		try {
			$out = (string) self::$engine->renderFileTemplate((string)$file, (array)$arr_vars);
		} catch(\Exception $e) {
			$out = '{### ERROR: Typo3Fluid TPL Render Failed ###}';
			\Smart::raise_error(
				'Typo3Fluid Template Render Error ['.$file.'] '.$e->getMessage(),
				'Typo3Fluid-TPL Render Error (see errors log for details)' // msg to display
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


// end of php code
