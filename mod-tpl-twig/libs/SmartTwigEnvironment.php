<?php
// Class: \SmartModExtLib\TplTwig\SmartTwigEnvironment
// [Smart.Framework.Modules - Twig / Environment for Smart.Framework]
// (c) 2006-present unix-world.org - all rights reserved

namespace SmartModExtLib\TplTwig;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Provides an advanced Environment for Twig Templating inside the Smart.Framework.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @depends 	extensions: PHP Ctype (optional) ; classes: \Smart, \SmartEnvironment, \SmartFileSysUtils, \SmartUtils, \Twig, \Symfony\Polyfill\Ctype\Ctype if PHP Ctype ext is N/A
 * @version 	v.20260128
 * @package 	modules:TemplatingEngine
 *
 */
final class SmartTwigEnvironment extends \Twig\Environment {

	// ->


	public function smartSetupCacheDir() : string {
		//--
		$the_twig_cache_dir = (string) 'tmp/cache/tpl-twig/v'.(int)self::MAJOR_VERSION.'.'.(int)self::MINOR_VERSION.'/';
		//--
		if(\defined('\\SMART_VENDOR_APP')) {
			$the_twig_cache_dir .= 'sfm';
		} else {
			if(\SmartEnvironment::isAdminArea() === true) {
				if(\SmartEnvironment::isTaskArea() === true) {
					$the_twig_cache_dir .= 'tsk';
				} else {
					$the_twig_cache_dir .= 'adm';
				} //end if else
			} else {
				$the_twig_cache_dir .= 'idx';
			} //end if else
		} //end if
		//--
		if(!\SmartFileSysUtils::isDir((string)$the_twig_cache_dir)) { // be independent of smart file system class, this module can be exported for vendoring
			if(\SmartFileSysUtils::createDir((string)$the_twig_cache_dir) !== true) {
				throw new \Exception('Twig Templating / Initialize / Could not create the Cache Directory: `'.$the_twig_cache_dir.'`');
				return '';
			} //end if
		} //end if
		//--
		return (string) $the_twig_cache_dir;
		//--
	} //END FUNCTION


	public function smartDebugGetLoadedTemplates(?string $mode) : array {
		//--
		if(!\SmartEnvironment::ifDebug()) {
			return [];
		} //end if
		//--
		switch((string)$mode) {
			case 'set':
			case 'get':
				break;
			default:
				return [];
		} //end switch
		//--
		$the_twig_cache_dir = (string) $this->smartSetupCacheDir();
		if((string)$the_twig_cache_dir != '') {
			$the_twig_cache_dir = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$the_twig_cache_dir);
		} //end if
		//--
		if(!\method_exists($this, 'smart__getLoadedTemplates')) {
			\Smart::log_warning('Twig Profiler for Smart.Framework requires a custom method to be implemented in the \\Twig\\Environment class: `protected function smart__getLoadedTemplates() { return $this->loadedTemplates; }` ...');
			return [];
		} //end if
		$arr = (array) $this->smart__getLoadedTemplates();
		$dbg_arr = [
			'sub-tpls' => [],
			'tpl-vars' => []
		];
		$optim_msg = [];
		foreach($arr as $key => $val) {
			if($key) {
				if(\is_object($val)) {
					//--
					$hash_key = (string) \hash((string)(\PHP_VERSION_ID < 80100 ? 'sha256' : 'xxh128'), (string)$key, false); // :: sync with \Twig\Environment->getTemplateClass()
					$real_cache_file = (string) $the_twig_cache_dir.\SmartFileSysUtils::addPathTrailingSlash((string)\substr((string)$hash_key, 0, 2)).$hash_key.'.php';
					//--
					$tpl_path = (string) $val->getTemplateName();
					$tpl_vars = (array) $this->smartGetRequiredKeys((string)$tpl_path);
					$dbg_arr['sub-tpls'][(string)$key] = [
						'tpl' 		=> (string) $tpl_path,
						'cache' 	=> (string) $real_cache_file
					];
					$dbg_arr['tpl-vars'] = (array) \array_merge($dbg_arr['tpl-vars'], (array)$tpl_vars);
					//--
					if((string)$mode != 'get') {
						if(!\is_file((string)\SmartFileSysUtils::getSmartFsRootPath().$real_cache_file)) { // be independent of smart file system class, this module can be exported for vendoring
							$is_optimal = false;
							$msg_optimal = 'Twig Cache File Not Found: '.$real_cache_file;
							$rds_optimal = 0;
						} else {
							$is_optimal = true;
							$msg_optimal = 'OK';
							$rds_optimal = 1;
						} //end if else
						$action = (string) \SmartUtils::get_server_current_script();
						$action .= '?page=tpl-twig.debug&tpl=';
						$optim_msg[] = [
							'optimal' 	=> (bool)   $is_optimal,
							'value' 	=> (int)    $rds_optimal,
							'key' 		=> (string) $tpl_path,
							'msg' 		=> (string) $msg_optimal,
							'action' 	=> (string) $action,
						];
						\SmartEnvironment::setDebugMsg('extra', 'TWIG-TEMPLATING', [
							'title' => '[TPL-ReadFileTemplate-From-FS] :: Twig-TPL / File-Read: '.$tpl_path.' ;',
							'data' => 'Content SubStr[0-'.(int)$this->smartGetdebugTplLength().']: '."\n".\Smart::text_cut_by_limit((string)\SmartFileSysUtils::readStaticFile((string)$tpl_path), (int)$this->smartGetdebugTplLength(), true, '[...]')
						]);
					} //end if
					//--
				} //end if
			} //end if
		} //end foreach
		$tmp_vars = (array) $dbg_arr['tpl-vars'];
		$dbg_arr['tpl-vars'] = array();
		foreach($tmp_vars as $key => $val) {
			if((string)\trim((string)$key) != '') {
				if(!\array_key_exists((string)$key, $dbg_arr['tpl-vars'])) {
					$dbg_arr['tpl-vars'][(string)$key] = 0;
				} //end if
				$dbg_arr['tpl-vars'][(string)$key] += (int)$val;
			} //end if
		} //end foreach
		$tmp_vars = array();
		\ksort($dbg_arr['tpl-vars']);
		//--
		if((string)$mode != 'get') {
			\SmartEnvironment::setDebugMsg('optimizations', '*TWIG-TPL-CLASSES:OPTIMIZATION-HINTS*', [
				'title' => 'SmartTwigEnvironment // Optimization Hints @ Number of FS Reads for rendering current Template incl. Sub-Templates ; Test if Cache File exists',
				'data' => (array) $optim_msg
			]);
		} //end if
		//--
		return (array) $dbg_arr;
		//--
	} //END FUNCTION


	private function smartGetRequiredKeys(?string $tplName) : array {
		//--
		$source = $this->getLoader()->getSourceContext((string)$tplName);
		$tokens = $this->tokenize($source);
	//	$parsed = (new \Twig\Parser($this))->parse($tokens); // {{{SYNC-TWIG-SMARTFRAMEWORK-DEBUG-BUG}}} if using this must re-init twig engine on every parse
		$parsed = $this->parse($tokens); // this fix seems to work for above bug ...
		//--
		return (array) $this->smartCollectNodes($parsed);
		//--
	} //END FUNCTION


	private function smartCollectNodes($nodes, $collected=null) : array {
		//--
		if(!\is_array($collected)) {
			$collected = [];
		} //end if
		//--
		foreach($nodes as $k => $node) {
			$childNodes = $node->getIterator()->getArrayCopy();
			if(!empty($childNodes)) {
				$collected = (array) $this->smartCollectNodes($childNodes, $collected); // recursion
			} elseif($node instanceof \Twig\Node\Expression\NameExpression) {
				$name = $node->getAttribute('name');
			//	if(!$node->getAttribute('always_defined')) { // internal twig defined variables
				$collected[(string)$name] = ($collected[(string)$name] ?? 0) + 1; // get real usage
			//	} //end if
			} //end if else
		} //end foreach
		//--
		return (array) $collected;
		//--
	} //END FUNCTION


	private function smartGetdebugTplLength() : int {
		//--
		$len = 255;
		if(\defined('\\SMART_SOFTWARE_MKTPL_DEBUG_LEN')) {
			if((int)\SMART_SOFTWARE_MKTPL_DEBUG_LEN >= 255) {
				if((int)\SMART_SOFTWARE_MKTPL_DEBUG_LEN <= 524280) {
					$len = (int) \SMART_SOFTWARE_MKTPL_DEBUG_LEN;
				} //end if
			} //end if
		} //end if
		$len = (int) \Smart::format_number_int((int)$len, '+');
		//--
		return (int) $len;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
