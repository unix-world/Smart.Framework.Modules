<?php
// Class: \SmartModExtLib\TplNetteLatte\Templating
// [Smart.Framework.Modules - (Nette) Latte / Templating]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplNetteLatte;

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
 * Provides connector for Nette Latte Templating inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     (new \SmartModExtLib\TplNetteLatte\Templating())->render_file_template(
 *         'modules/my-module-name/views/myView.latte.htm',
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
 * @depends 	extensions: classes: NetteLatte
 * @version 	v.20200121
 * @package 	modules:TemplatingEngine
 *
 */
final class Templating extends \SmartModExtLib\Tpl\AbstractTemplating {

	// ->

	private $dir;
	private $latte;


	public static function getVersion() {
		//--
		return (string) \Latte\Engine::VERSION;
		//--
	} //END FUNCTION


	public function __construct() {
		//--
		$this->dir = './';
		//--
		$this->latte = new \Latte\Engine();
		$this->latte->setAutoRefresh(true); // regenerates the cache every time the template is changed
		//--
		$the_nlatte_cache_dir = (string) $this->smartSetupCacheDir();
		$this->latte->setTempDirectory((string)$the_nlatte_cache_dir);
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
		if(!\is_array($arr_vars)) {
			$arr_vars = array();
		} //end if
		$arr_vars = (array) \array_change_key_case((array)$arr_vars, \CASE_LOWER); // make all keys lower (only 1st level, not nested)
		$arr_vars = (array) $this->fix_array_keys($arr_vars, false); // make keys compatible with PHP variable names, LOWER only (only 1st level, not nested)
		//--
		if((string)\trim((string)$file) == '') {
			throw new \Exception('NetteLatte Templating / Render File / The file name is Empty');
			return;
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \Exception('NetteLatte Templating / Render File / Invalid file Path');
			return;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-nette-latte/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
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
			throw new \Exception('NetteLatte Templating / Render File / Invalid tpl Path');
			return;
		} //end if
		//--
		$arr_vars['Tpl_Dir__'] = (string) $dir_of_tpl; // this is the only tpl variable that will be case sensitive
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \Exception('NetteLatte Templating / Render File / The file name / path contains invalid characters: '.$file);
			return;
		} //end if
		//--
		if(!\is_file($file)) {
			throw new \Exception('NetteLatte Templating / The Template file to render does not exists: '.$file);
			return;
		} //end if
		//--
		return (string) $this->latte->renderToString((string)$file, (array)$arr_vars);
		//--
	} //END FUNCTION


	private function smartSetupCacheDir() {
		//--
		if(\SMART_FRAMEWORK_ADMIN_AREA === true) {
			$the_latte_cache_dir = 'tmp/cache/nlatte#adm';
		} else {
			$the_latte_cache_dir = 'tmp/cache/nlatte#idx';
		} //end if else
		if(!\SmartFileSystem::is_type_dir((string)$the_latte_cache_dir)) {
			if(!\SmartFileSystem::dir_create((string)$the_latte_cache_dir, true)) {
				throw new \Exception('NetteLatte Templating / Initialize / Could not create the Cache Directory: '.$the_latte_cache_dir);
				return '';
			} //end if
		} //end if
		//--
		return (string) $the_latte_cache_dir;
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
		if(!\SmartFrameworkRuntime::ifDebug()) {
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
function autoload__NetteLatteTemplating_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 6) !== 'Latte\\') { // if class name is not starting with Latte
		return;
	} //end if
	//--
	$class_map = [
		'Latte\\CompileException' 			=> 'exceptions',
		'Latte\\Compiler' 					=> 'Compiler/Compiler',
		'Latte\\Engine' 					=> 'Engine',
		'Latte\\Helpers' 					=> 'Helpers',
		'Latte\\HtmlNode' 					=> 'Compiler/HtmlNode',
		'Latte\\ILoader' 					=> 'ILoader',
		'Latte\\IMacro' 					=> 'IMacro',
		'Latte\\Loaders\\FileLoader' 		=> 'Loaders/FileLoader',
		'Latte\\Loaders\\StringLoader' 		=> 'Loaders/StringLoader',
		'Latte\\MacroNode' 					=> 'Compiler/MacroNode',
		'Latte\\Macros\\BlockMacros' 		=> 'Macros/BlockMacros',
		'Latte\\Macros\\CoreMacros' 		=> 'Macros/CoreMacros',
		'Latte\\Macros\\MacroSet' 			=> 'Macros/MacroSet',
		'Latte\\MacroTokens' 				=> 'Compiler/MacroTokens',
		'Latte\\Parser' 					=> 'Compiler/Parser',
		'Latte\\PhpHelpers' 				=> 'Compiler/PhpHelpers',
		'Latte\\PhpWriter' 					=> 'Compiler/PhpWriter',
		'Latte\\RegexpException' 			=> 'exceptions',
		'Latte\\Runtime\\CachingIterator' 	=> 'Runtime/CachingIterator',
		'Latte\\Runtime\\FilterExecutor' 	=> 'Runtime/FilterExecutor',
		'Latte\\Runtime\\FilterInfo' 		=> 'Runtime/FilterInfo',
		'Latte\\Runtime\\Filters' 			=> 'Runtime/Filters',
		'Latte\\Runtime\\Html' 				=> 'Runtime/Html',
		'Latte\\Runtime\\IHtmlString' 		=> 'Runtime/IHtmlString',
		'Latte\\Runtime\\ISnippetBridge' 	=> 'Runtime/ISnippetBridge',
		'Latte\\Runtime\\SnippetDriver' 	=> 'Runtime/SnippetDriver',
		'Latte\\Runtime\\Template' 			=> 'Runtime/Template',
		'Latte\\RuntimeException' 			=> 'exceptions',
		'Latte\\Strict' 					=> 'Strict',
		'Latte\\Token' 						=> 'Compiler/Token',
		'Latte\\TokenIterator' 				=> 'Compiler/TokenIterator',
		'Latte\\Tokenizer' 					=> 'Compiler/Tokenizer',
	];
	//--
	if((string)$class_map[(string)$classname] != '') {
		if(!\is_file('modules/mod-tpl-nette-latte/libs/Latte/'.$class_map[(string)$classname].'.php')) {
			return; // file does not exists
		} //end if
		require_once('modules/mod-tpl-nette-latte/libs/Latte/'.$class_map[(string)$classname].'.php');
	} //end if
	//--
} //END FUNCTION
//--
\spl_autoload_register('\\SmartModExtLib\\TplNetteLatte\\autoload__NetteLatteTemplating_SFM', true, false); // throw / append
//--


// end of php code
