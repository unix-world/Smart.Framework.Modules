<?php
// [LIB - Smart.Framework.Modules / ExtraLibs / Extended Templating]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.7.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// [PHP8]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Provides an easy to use connector for All available Templating Engines inside the Smart.Framework at once.
 *
 * Depending upon the file name extension will choose to render as:
 *
 * some-file.mtpl.htm 		: SmartMarkersTemplating 									(Marker-TPL syntax: .mtpl.)
 *
 * some-file.dust.htm 		: \SmartModExtLib\TplDust\SmartDustTemplating 				(Dust-TPL syntax: .dust.)
 *
 * some-file.latte.htm 		: \SmartModExtLib\TplNetteLatte\SmartNetteLatteTemplating 	(NetteLatte-TPL syntax: .latte.)
 *
 * some-file.twig.htm 		: \SmartModExtLib\TplTwig\SmartTwigTemplating 				(Twig-TPL syntax: .twig.)
 *
 * some-file.t3fluid.htm 	: \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating 	(Typo3Fluid-TPL syntax: .t3fluid.)
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     SmartTemplating::render_file_template(
 *         'modules/my-module-name/views/my-view.(mtpl|dust|latte|twig|t3fluid).htm',
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
 * @depends 	classes: SmartMarkersTemplating, \SmartModExtLib\TplDust\SmartDustTemplating, \SmartModExtLib\TplNetteLatte\SmartNetteLatteTemplating, \SmartModExtLib\TplTwig\SmartTwigTemplating, \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating
 * @version 	v.20210303
 * @package 	extralibs:TemplatingEngine
 *
 */
final class SmartTemplating {

	// ::


	public static function render_file_template($file, $arr_vars=array(), $options=[]) {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			return '{# ERROR: '.__CLASS__.' ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: The module mod-tpl cannot be found ... #}';
		} //end if
		//--
		if(!is_array($options)) {
			$options = array();
		} //end if
		if(!array_key_exists('use-caching', $options)) {
			$options['use-caching'] = null;
		} //end if
		if(!array_key_exists('only-debug', $options)) {
			$options['only-debug'] = null;
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
		} elseif(strpos((string)$file, '.dust.') !== false) { // dust TPL
			//--
			return (string) \SmartModExtLib\TplDust\SmartDustTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false / true
			);
			//--
		} elseif(strpos((string)$file, '.latte.') !== false) { // netteLatte TPL
			//--
			return (string) \SmartModExtLib\TplNetteLatte\SmartNetteLatteTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false / true
			);
			//--
		} elseif(strpos((string)$file, '.twig.') !== false) { // Twig TPL
			//--
			return (string) \SmartModExtLib\TplTwig\SmartTwigTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false / true
			);
			//--
		} elseif(strpos((string)$file, '.t3fluid.') !== false) { // Typo3Fluid TPL
			//--
			return (string) \SmartModExtLib\TplTypo3Fluid\SmartTypo3FluidTemplating::render_file_template(
				(string) $file,
				(array)  $arr_vars,
				(bool)   $options['only-debug'] // false/true
			);
			//--
		} else { // ERROR
			//--
			return '{# ERROR: '.__CLASS__.' ('.SMART_APP_MODULES_EXTRALIBS_VER.') :: Cannot determine the Templating Engine type to use for the file: '.Smart::base_name($file).' ... #}';
			//--
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
