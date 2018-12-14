<?php
// TYPO3 Fluid Templating for Smart.Framework
// Module Library
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplTypo3Fluid;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
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
 * @access 		PUBLIC
 * @depends 	extensions: classes: TYPO3Fluid
 * @version 	v.181213
 * @package 	Templating:Engines
 *
 */
final class Templating {

	// ->

	const FLUID_VERSION = 'master@181208';

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
		if(!\SmartFrameworkRuntime::ifDebug()) {
			$onlydebug = false;
		} //end if
		//--
		if(!is_array($arr_vars)) {
			$arr_vars = array();
		} //end if
		// allow camelCase keys
		$arr_vars = (array) self::fix_array_keys($arr_vars); // (recursive) replace - and . in array keys with _
		//--
		if((string)trim((string)$file) == '') {
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
		if(!is_file($file)) {
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
		if(SMART_FRAMEWORK_ADMIN_AREA === true) {
			$the_t3fluid_cache_dir = 'tmp/cache/typo3fluid#adm';
		} else {
			$the_t3fluid_cache_dir = 'tmp/cache/typo3fluid#idx';
		} //end if else
		if(!\SmartFileSystem::is_type_dir((string)$the_t3fluid_cache_dir)) {
			if(!\SmartFileSystem::dir_create((string)$the_t3fluid_cache_dir, true)) {
				throw new \Exception('Typo3Fluid Templating / Initialize / Could not create the cache directory: '.$the_t3fluid_cache_dir);
				return '';
			} //end if
		} //end if
		//--
		return (string) $the_t3fluid_cache_dir;
		//--
	} //END FUNCTION


	private static function fix_array_keys($y_arr) { // v.191213 :: make array keys compatible with Markers-TPL
		//--
		if(!is_array($y_arr)) { // fix bug if empty array / max nested level
			return $y_arr; // mixed
		} //end if
		//--
		$new_arr = [];
		//--
		foreach($y_arr as $key => $val) {
			$key = (string) rtrim((string)str_replace(['-', '.'], '_', (string)$key), '_'); // dissalow ending in __ which is reserved here ; also the markers TPL keys can contain: /^[A-Z0-9_\-\.]+$/ ; thus replace - and . with _ to fix (are not supposed to be allowed here ...)
			if(((string)$key != '') AND (preg_match('/^[a-zA-Z0-9_]+$/', (string)$key))) {
				if(is_array($val)) {
					$new_arr[(string)$key] = self::fix_array_keys((array)$val);
				} else {
					$new_arr[(string)$key] = $val; // mixed
				} //end if
			} //end if else
		} //end foreach
		//--
		return $new_arr; // mixed
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
	if(strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)substr((string)$classname, 0, 17) !== 'TYPO3Fluid\\Fluid\\') { // if class name is not starting with Typo3Fluid
		return;
	} //end if
	//--
	$path = 'modules/mod-tpl-typo3-fluid/libs/'.str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
	//--
	if(!preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in path
	} //end if
	//--
	if(!is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//--
spl_autoload_register('\\SmartModExtLib\\TplTypo3Fluid\\autoload__TYPO3FluidTemplating_SFM', true, false); // throw / append
//--


//end of php code
?>