<?php
// Class: \SmartModExtLib\TplTypo3Fluid\Templating
// [Smart.Framework.Modules - Typo3Fluid / Templating]
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
 * Provides connector for TYPO3 Fluid Templating inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     (new \SmartModExtLib\TplTypo3Fluid\Templating())->render_file_template(
 *         'modules/my-module-name/views/myView.t3fluid.htm',
 *         [
 *             'someVar' => 'Hello World',
 *             'otherVar' => date('Y-m-d H:i:s')
 *         ]
 *     )
 * );
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @access 		PUBLIC
 * @depends 	extensions: classes: TYPO3Fluid
 * @version 	v.20210428
 * @package 	modules:TemplatingEngine
 *
 */
final class Templating extends \SmartModExtLib\Tpl\AbstractTemplating {

	// ->

	const FLUID_VERSION = 'master@20210318';

	private $dir;
	private $t3fluid;
	private $t3fpaths;


	public static function getVersion() {
		//--
		return (string) self::FLUID_VERSION;
		//--
	} //END FUNCTION


	public function __construct() {
		//--
		$this->dir = './';
		//--
		$this->t3fluid = new \TYPO3Fluid\Fluid\View\TemplateView();
		$this->t3fpaths = $this->t3fluid->getTemplatePaths();
		//--
		$the_t3fluid_cache_dir = (string) $this->smartSetupCacheDir();
		$this->t3fluid->setCache(new \TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache($the_t3fluid_cache_dir));
		//--
	} //END FUNCTION


	public function render_file_template($file, $arr_vars=array(), $onlydebug=false) {
		//--
		if($onlydebug !== true) {
			$onlydebug = false;
		} //end else
		if(!\SmartFrameworkRegistry::ifDebug()) {
			$onlydebug = false;
		} //end if
		//--
		if(!\is_array($arr_vars)) {
			$arr_vars = array();
		} //end if
		// allow camelCase keys
		$arr_vars = (array) $this->fix_array_keys($arr_vars, true); // make keys compatible with PHP variable names, LOWER and UPPER (only 1st level, not nested)
		//--
		if((string)\trim((string)$file) == '') {
			throw new \Exception('Typo3Fluid Templating / Render File / The file name is Empty');
			return;
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \Exception('Typo3Fluid Templating / Render File / Invalid file Path');
			return;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-typo3-fluid/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
		//--
		$dir_of_tpl = (string) \Smart::dir_name($file);
		if((string)$dir_of_tpl != '') {
			if(!\SmartFileSysUtils::check_if_safe_path($dir_of_tpl)) {
				$dir_of_tpl = (string) $invalid_dir; // fix if unsafe
			} //end if
			$dir_of_tpl = (string) \SmartFileSysUtils::add_dir_last_slash((string)$dir_of_tpl);
			if(!\SmartFileSysUtils::check_if_safe_path($dir_of_tpl)) {
				$dir_of_tpl = (string) $invalid_dir.'/'; // fix if unsafe
			} //end if
		} else {
			$dir_of_tpl = (string) $invalid_dir.'/'; // fix if empty
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($dir_of_tpl)) {
			throw new \Exception('Typo3Fluid Templating / Render File / Invalid tpl Path');
			return;
		} //end if
		//--
		$arr_vars['Tpl_Dir__'] = (string) $dir_of_tpl;
		//--
		$this->t3fpaths->setTemplateRootPaths([
			(string) $dir_of_tpl
		]);
		$this->t3fpaths->setLayoutRootPaths([
			(string) $dir_of_tpl
		]);
		$this->t3fpaths->setPartialRootPaths([
			(string) $dir_of_tpl
		]);
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \Exception('Typo3Fluid Templating / Render File / The file name / path contains invalid characters: '.$file);
			return;
		} //end if
		//--
		if(!\is_file($file)) {
			throw new \Exception('Typo3Fluid Templating / The Template file to render does not exists: '.$file);
			return;
		} //end if
		//--
	/*	foreach($arr_vars as $key => $val) {
			$this->t3fluid->assign((string)$key, $val);
		} //end foreach */
		$this->t3fluid->assignMultiple((array)$arr_vars);
		$this->t3fpaths->setTemplatePathAndFilename((string)$file);
		//--
		return (string) $this->t3fluid->renderSection(
			'Typo3FluidTpl', // sectionName,
			(array) $arr_vars, //array $variables,
			true // ignoreUnknown
		);
		//--
	} //END FUNCTION


	private function smartSetupCacheDir() {
		//--
		if(\SmartFrameworkRegistry::isAdminArea() === true) {
			if(\SmartFrameworkRegistry::isTaskArea() === true) {
				$the_t3fluid_cache_dir = 'tmp/cache/typo3fluid#tsk';
			} else {
				$the_t3fluid_cache_dir = 'tmp/cache/typo3fluid#adm';
			} //end if else
		} else {
			$the_t3fluid_cache_dir = 'tmp/cache/typo3fluid#idx';
		} //end if else
		if(!\SmartFileSystem::is_type_dir((string)$the_t3fluid_cache_dir)) {
			if(!\SmartFileSystem::dir_create((string)$the_t3fluid_cache_dir, true)) {
				throw new \Exception('Typo3Fluid Templating / Initialize / Could not create the Cache Directory: '.$the_t3fluid_cache_dir);
				return '';
			} //end if
		} //end if
		//--
		return (string) $the_t3fluid_cache_dir;
		//--
	} //END FUNCTION


	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function debug($tpl) {
		//--
		if(!\SmartFrameworkRegistry::ifDebug()) {
			return '';
		} //end if
		//--
		if((string)\trim((string)$tpl) == '') {
			return '';
		} //end if
		//--
		return '<h1>Debug N/A for this TPL Engine ...</h1>';
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//--
/**
 *
 * @access 		private
 * @internal
 *
 */
function autoload__TYPO3FluidTemplating_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 17) !== 'TYPO3Fluid\\Fluid\\') { // if class name is not starting with Typo3Fluid
		return;
	} //end if
	//--
	$path = 'modules/mod-tpl-typo3-fluid/libs/'.\str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
	//--
	if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in path
	} //end if
	//--
	if(!\is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//--
\spl_autoload_register('\\SmartModExtLib\\TplTypo3Fluid\\autoload__TYPO3FluidTemplating_SFM', true, false); // throw / append
//--


// end of php code
