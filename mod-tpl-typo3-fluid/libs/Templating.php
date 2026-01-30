<?php
// Class: \SmartModExtLib\TplTypo3Fluid\Templating
// [Smart.Framework.Modules - Typo3Fluid / Templating]
// (c) 2006-2022 unix-world.org - all rights reserved

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
 * Using this class directly in Smart.Framework context is not secure ; Use instead SmartTypo3FluidTemplating !
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
 * @version 	v.20260130
 * @package 	modules:TemplatingEngine
 *
 */
final class Templating extends \SmartModExtLib\Tpl\AbstractTemplating {

	// ->

	public const FLUID_VERSION = '5.0.3';

	private const VERSION_MAJOR = 5;
	private const VERSION_MINOR = 0;
	private const VERSION_EXTRA = 3;

	private $dir;
	private $t3fluid;
	private $t3fpaths;


	public static function getVersion() : string {
		//--
		return (string) self::FLUID_VERSION;
		//--
	} //END FUNCTION


	public function __construct() {
		//--
		$this->dir = './';
		//--
		$this->t3fluid = new \TYPO3Fluid\Fluid\View\TemplateView();
		$this->t3fpaths = $this->t3fluid->getRenderingContext()->getTemplatePaths();
		//--
		$the_t3fluid_cache_dir = (string) $this->smartSetupCacheDir();
		$this->t3fluid->getRenderingContext()->setCache(new \TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache((string)$the_t3fluid_cache_dir));
		//--
	} //END FUNCTION


	public function renderFileTemplate(?string $file, ?array $arr_vars=[]) : string {
		//--
		$rendered = '';
		try {
			$rendered = (string) $this->render_file_template((string)$file, (array)$arr_vars);
		} catch(\Exception $e) {
			\Smart::raise_error((string)$e->getMessage());
			return '';
		} //end try catch
		//--
		return (string) $rendered;
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
		return '<h1>Debug N/A for this TPL Engine ...</h1>';
		//--
	} //END FUNCTION


	//======= PRIVATES


	private function smartSetupCacheDir() : string {
		//--
		$the_t3fluid_cache_dir = 'tmp/cache/tpl-t3fluid/v'.(int)self::VERSION_MAJOR.'.'.(int)self::VERSION_MINOR.'/';
		//--
		if(\SmartEnvironment::isAdminArea() === true) {
			if(\SmartEnvironment::isTaskArea() === true) {
				$the_t3fluid_cache_dir .= 'tsk';
			} else {
				$the_t3fluid_cache_dir .= 'adm';
			} //end if else
		} else {
			$the_t3fluid_cache_dir .= 'idx';
		} //end if else
		if(!\SmartFileSysUtils::isDir((string)$the_t3fluid_cache_dir)) {
			if(!\SmartFileSystem::dir_create((string)$the_t3fluid_cache_dir, true)) {
				throw new \Exception('Typo3Fluid Templating / Initialize / Could not create the Cache Directory: '.$the_t3fluid_cache_dir);
				return '';
			} //end if
		} //end if
		//--
		return (string) $the_t3fluid_cache_dir;
		//--
	} //END FUNCTION


	private function render_file_template(string $file, array $arr_vars=[], bool $onlydebug=false) : null|string|array {
		//--
		if(!\SmartEnvironment::ifDebug()) {
			$onlydebug = false;
		} //end if
		//-- allow camelCase keys
		$arr_vars = (array) $this->fixArrayKeys((array)$arr_vars, true); // make keys compatible with PHP variable names, LOWER and UPPER (only 1st level, not nested)
		//--
		if((string)\trim((string)$file) == '') {
			throw new \Exception('Typo3Fluid Templating / Render File / The file name is Empty');
			return null;
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)$file)) {
			throw new \Exception('Typo3Fluid Templating / Render File / Invalid file Path');
			return null;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-typo3-fluid/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
		//--
		$dir_of_tpl = (string) \Smart::dir_name($file);
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
			throw new \Exception('Typo3Fluid Templating / Render File / Invalid tpl Path');
			return null;
		} //end if
		//--
		$arr_vars[(string)$this->getTplPathVar().'__'] = (string) $dir_of_tpl;
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
		if(!\SmartFileSysUtils::checkIfSafePath((string)$file)) {
			throw new \Exception('Typo3Fluid Templating / Render File / The file name / path contains invalid characters: '.$file);
			return null;
		} //end if
		//--
		if(!\is_file((string)$file)) {
			throw new \Exception('Typo3Fluid Templating / The Template file to render does not exists: '.$file);
			return null;
		} //end if
		//--
		/*
		foreach($arr_vars as $key => $val) {
			$this->t3fluid->assign((string)$key, $val);
		} //end foreach
		*/
		$this->t3fluid->assignMultiple((array)$arr_vars);
		$this->t3fpaths->setTemplatePathAndFilename((string)$file);
		//--
		$bench = \microtime(true);
		$rendered = (string) $this->t3fluid->renderSection(
			'Typo3FluidTpl', // sectionName,
			(array) $arr_vars, //array $variables,
			true // ignoreUnknown
		);
		$bench = \Smart::format_number_dec((float)(\microtime(true) - (float)$bench), 9, '.', '');
		//--
		if(\SmartEnvironment::ifDebug()) {
			if($onlydebug === true) {
				return []; // TO BE DONE ...
			} else {
				\SmartEnvironment::setDebugMsg('extra', 'TYPO3FLUID-TEMPLATING', [
					'title' => '[TPL-Parsing:Render.DONE] :: Typo3Fluid-TPL / Processing ; Time = '.$bench.' sec.',
					'data' => 'TPL Rendered Files: '.\Smart::array_size([ $file ]).' ; TPL Variables: '.\Smart::array_size($arr_vars)
				]);
				return (string) $rendered;
			} //end if else
		} else {
			return (string) $rendered;
		} //end if else
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
\spl_autoload_register(function(string $classname) : void {
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if(\str_starts_with((string)$classname, 'TYPO3Fluid\\Fluid\\') === false) { // if class name is starting with TYPO3Fluid\Fluid\
		return;
	} //end if
	//--
	$path = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-tpl-typo3-fluid/libs/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
	//--
	if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', (string)$path)) {
		return; // invalid path characters in path
	} //end if
	//--
	if(!\is_file((string)$path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once((string)$path.'.php');
	//--
}, true, false); // throw / append
//--


// end of php code
