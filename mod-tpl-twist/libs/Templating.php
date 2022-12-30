<?php
// Class: \SmartModExtLib\TplTwist\Templating
// [Smart.Framework.Modules - TwistTPL / Templating]
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
 * Provides connector for (PHP) TwistTPL Templating inside the Smart.Framework.
 * Using this class directly in Smart.Framework context is not secure ; Use instead SmartTwistTemplating !
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     (new \SmartModExtLib\TplTwist\Templating())->renderFileTemplate(
 *         'modules/my-module-name/views/myView.twist.htm',
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
 * @depends 	extensions: classes: TwistTPL
 * @version 	v.20221220
 * @package 	modules:TemplatingEngine
 *
 */
final class Templating extends \SmartModExtLib\Tpl\AbstractTemplating {

	// ->

	private $dir;
	private $tpl;


	public static function getVersion() : string {
		//--
		return (string) \TwistTPL\Twist::VERSION;
		//--
	} //END FUNCTION


	public function __construct() {
		//--
		$this->dir = 'modules/';
		//--
		$tpl_cache_path = 'tmp/cache/tpl-twist/v'.(int)\TwistTPL\Twist::MAJOR_VERSION.'.'.(int)\TwistTPL\Twist::MINOR_VERSION.'/';
		if(\SmartEnvironment::isAdminArea() === true) {
			if(\SmartEnvironment::isTaskArea() === true) {
				$tpl_cache_path .= 'tsk';
			} else {
				$tpl_cache_path .= 'adm';
			} //end if else
		} else {
			$tpl_cache_path .= 'idx';
		} //end if else
		//--
		$cache = null;
		//--
		if((string)$tpl_cache_path != '') {
			$tpl_cache_path = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$tpl_cache_path);
			if(\SmartFileSysUtils::checkIfSafePath((string)$tpl_cache_path)) {
				if(!\SmartFileSystem::path_exists((string)$tpl_cache_path)) {
					\SmartFileSystem::dir_create((string)$tpl_cache_path, true);
				} //end if
				if(\SmartFileSystem::is_type_dir((string)$tpl_cache_path)) {
					$cache = [ 'cache' => 'file', 'cache:path' => (string)$tpl_cache_path ];
				} else {
					\Smart::log_warning(__METHOD__.' # FAILED to setup the Cache Path: `'.$tpl_cache_path.'`');
				} //end if
			} //end if
		} //end if
		//--
		$this->tpl = new \TwistTPL\Template((string)$this->dir, $cache);
		//--
	} //END FUNCTION


	public function renderFileTemplate(?string $file, ?array $arr_vars=[]) : string {
		//--
		return (string) $this->render_file_template((string)$file, (array)$arr_vars, false);
		//--
	} //END FUNCTION


	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function getDebugInfo(?string $tpl) : string {
		//--
		if(!\SmartEnvironment::ifDebug()) {
			return '';
		} //end if
		//--
		if((string)\trim((string)$tpl) == '') {
			return '';
		} //end if
		//--
		$content = '';
		// $dbgarr = (array) $this->render_file_template((string)$file, (array)$arr_vars, true);
		//--
		return (string) $content;
		//--
	} //END FUNCTION


	//======= PRIVATES


	private function render_file_template(string $file, array $arr_vars=array(), bool $onlydebug=false) { // mixed output: string or (onlydebug) array
		//--
		if($onlydebug !== true) {
			$onlydebug = false;
		} //end else
		if(!\SmartEnvironment::ifDebug()) {
			$onlydebug = false;
		} //end if
		//--
		if(!\is_array($arr_vars)) {
			$arr_vars = array();
		} //end if
		//-- allow camelCase keys
		$arr_vars = (array) $this->fixArrayKeys($arr_vars, true); // make keys compatible with PHP variable names, LOWER and UPPER (only 1st level, not nested)
		//--
		if((string)\trim((string)$file) == '') {
			throw new \Exception('Twist Templating / Render File / The file name is Empty');
			return;
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)$file)) {
			throw new \Exception('Twist Templating / Render File / Invalid file Path');
			return;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-twist/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
		//--
		$arr_tpl_parts = (array) \Smart::path_info($file);
		//--
		$dir_of_tpl = (string) $arr_tpl_parts['dirname'];
		$the_tpl_file = (string) $arr_tpl_parts['basename'];
		//--
		if((string)$dir_of_tpl != '') {
			if(!\SmartFileSysUtils::checkIfSafePath((string)$dir_of_tpl)) {
				$dir_of_tpl = (string) $invalid_dir; // fix if unsafe
			} //end if
			$dir_of_tpl = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$dir_of_tpl);
			if(!\SmartFileSysUtils::checkIfSafePath((string)$dir_of_tpl)) {
				$dir_of_tpl = (string) $invalid_dir.'/'; // fix if unsafe
			} //end if
		} else {
			$dir_of_tpl = (string) $invalid_dir.'/'; // fix if empty
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)$dir_of_tpl)) {
			throw new \Exception('Twist Templating / Render File / Invalid TPL Dir Path');
			return;
		} //end if
		if(!\SmartFileSysUtils::checkIfSafeFileOrDirName((string)$the_tpl_file)) {
			throw new \Exception('Twist Templating / Render File / Invalid TPL File Name');
			return;
		} //end if
		//--
		$arr_vars[(string)$this->getTplPathVar().'__'] = (string) $dir_of_tpl; // this is the only tpl variable that will be case sensitive
		//--
		if(!\SmartFileSysUtils::checkIfSafePath((string)$file)) {
			throw new \Exception('Twist Templating / Render File / The file name / path contains invalid characters: '.$file);
			return;
		} //end if
		//--
		if(!\is_file((string)$file)) {
			throw new \Exception('Twist Templating / The Template file to render does not exists: '.$file);
			return;
		} //end if
		//--
		if(\SmartEnvironment::ifDebug()) {
			$bench = \microtime(true);
			$pmu = 0;
			if(\function_exists('\\memory_get_peak_usage')) {
				$pmu = (int) @\memory_get_peak_usage(false);
			} //end if
		} //end if
		//--
		$template = $this->tpl->parseFile((string)$the_tpl_file, (string)$dir_of_tpl);
		if(!is_object($template)) {
			throw new \Exception('Twist Templating / The Template file to parse returned not an object: '.$file);
			return;
		} //end if
		$rendered = (string) $this->tpl->render((array)$arr_vars);
		//--
		if(\SmartEnvironment::ifDebug()) {
			//--
			// TODO: add cache gc()
			//--
		} //end if
		//--
// print_r(\TwistTPL\Twist::getRenderedTplRecords());
		return (string) $rendered;
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
function autoload__TwistTemplating_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 9) !== 'TwistTPL\\') { // if class name is not starting with TwistTPL
		return;
	} //end if
	//--
	$path = 'modules/mod-tpl-twist/libs/'.\str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
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
\spl_autoload_register('\\SmartModExtLib\\TplTwist\\autoload__TwistTemplating_SFM', true, false); // throw / append
//--


// end of php code
