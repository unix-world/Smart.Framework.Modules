<?php
// Class: \SmartModExtLib\TplTwist\Templating
// [Smart.Framework.Modules - TwistTPL / Templating]
// (c) 2006-present unix-world.org - all rights reserved

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
 * @depends 	extensions: classes: Smart, SmartFileSysUtils, SmartEnvironment, SmartUtils, TwistTPL
 * @version 	v.20260130
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


	public function __construct(string $root='modules/', bool $useCache=true) {
		//--
		$root = (string) \trim((string)$root);
		if((string)$root == '') {
			\Smart::raise_error(__METHOD__.' # Empty Relative TPL Root Dir Path');
			return;
		} //end if
		if(str_ends_with((string)$root, '/') !== true) {
			\Smart::raise_error(__METHOD__.' # Relative TPL Root Dir Path must end with a `/` slash: `'.$root.'`');
			return;
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$root) !== 1) {
			\Smart::raise_error(__METHOD__.' # Invalid Relative TPL Root Dir Path: `'.$root.'`');
			return;
		} //end if
		//--
		$this->dir = (string) $root;
		//--
		$cache = null;
		$tpl_cache_path = (string) $this->getCachePath();
		//--
		if($useCache !== false) {
			if(\SmartFileSysUtils::checkIfSafePath((string)$tpl_cache_path) === 1) {
				if(\SmartFileSysUtils::pathExists((string)$tpl_cache_path) !== true) {
					\SmartFileSysUtils::createDir((string)$tpl_cache_path);
				} //end if
				if(\SmartFileSysUtils::isDir((string)$tpl_cache_path, true) === true) { // use caching
					$cache = [ 'root:path' => (string)$this->dir, 'cache' => 'file', 'cache:path' => (string)$tpl_cache_path ];
				} else {
					\Smart::log_warning(__METHOD__.' # FAILED to setup the Cache Path: `'.$tpl_cache_path.'`');
				} //end if
			} else {
				\Smart::log_warning(__METHOD__.' # Invalid Cache Path: `'.$tpl_cache_path.'`');
			} //end if else
		} //end if
		//--
		$this->tpl = new \TwistTPL\Template((string)$this->dir, $cache);
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
		$content = '';
		//--
		$dbg_tpl = (array) \SmartDebugProfiler::read_tpl_file_for_debug((string)$tpl);
		//--
		if(
			((string)\trim((string)($dbg_tpl['dbg-file-name'] ?? null)) != '')
			AND
			(\SmartFileSysUtils::checkIfSafePath((string)($dbg_tpl['dbg-file-name'] ?? null)) === 1)
			AND
			(\SmartFileSysUtils::isFile((string)($dbg_tpl['dbg-file-name'] ?? null), true) === true) // be independent of smart file system class, this module can be exported for vendoring
			AND
			((string)($dbg_tpl['dbg-file-contents'] ?? null) != '')
		) {
			//--
			$hash = (string) \sha1((string)$dbg_tpl['dbg-file-name']);
			//--
			$dbgarr = [];
			//--
			$dbgarr['sub-tpls'] = (array) $this->render_file_template((string)$dbg_tpl['dbg-file-name'], [], true); // catch exception, variables arr is empty
			//--
			$dbgarr['tpl-vars'] = [];
			$dbgarr['tpl-if-vars'] = [];
			$dbgarr['tpl-loop-vars'] = [];
			$tknArr = (array) \TwistTPL\Twist::tokenize((string)$dbg_tpl['dbg-file-contents'], '', null);
			for($i=0; $i<\count($tknArr); $i++) {
				//--
				$tknArr[$i] = (string) \trim((string)$tknArr[$i]);
				//--
				if(strpos((string)$tknArr[$i], '{{') === 0) { // variable
					$tknArr[$i] = (string) \trim((string)$tknArr[$i], '{}');
					$tknArr[$i] = (string) \trim((string)$tknArr[$i]);
					if(strpos((string)$tknArr[$i], '|') !== false) {
						$tknArr[$i] = (array) \explode('|', (string)$tknArr[$i], 2);
						$tknArr[$i] = (string) \trim((string)($tknArr[$i][0] ?? null));
					} //end if
					if(!isset($dbgarr['tpl-vars'][(string)$tknArr[$i]])) {
						$dbgarr['tpl-vars'][(string)$tknArr[$i]] = 0;
					} //end if
					$dbgarr['tpl-vars'][(string)$tknArr[$i]]++;
				} elseif(strpos((string)$tknArr[$i], '{%') === 0) { // if/for/comment
					$tknArr[$i] = (string) \trim((string)$tknArr[$i], '{%}');
					$tknArr[$i] = (string) \trim((string)$tknArr[$i]);
					$tknArr[$i] = (string) \preg_replace('/\s+/', ' ', (string)$tknArr[$i]); // replace many spaces with one space
					$tknArr[$i] = (array) \explode(' ', (string)$tknArr[$i]);
					switch((string)\strtolower((string)($tknArr[$i][0] ?? null))) {
						case 'if':
							$tknArr[$i] = (string) \trim((string)($tknArr[$i][1] ?? null));
							if((string)$tknArr[$i] != '') {
								if(strpos((string)$tknArr[$i], '|') !== false) {
									$tknArr[$i] = (array) \explode('|', (string)$tknArr[$i], 2);
									$tknArr[$i] = (string) \trim((string)($tknArr[$i][0] ?? null));
								} //end if
								if(!isset($dbgarr['tpl-if-vars'][(string)$tknArr[$i]])) {
									$dbgarr['tpl-if-vars'][(string)$tknArr[$i]] = 0;
								} //end if
								$dbgarr['tpl-if-vars'][(string)$tknArr[$i]]++;
							} //end if
							break;
						case 'for':
							$tknArr[$i] = (string) \trim((string)($tknArr[$i][3] ?? null));
							if((string)$tknArr[$i] != '') {
								if(strpos((string)$tknArr[$i], '|') !== false) {
									$tknArr[$i] = (array) \explode('|', (string)$tknArr[$i], 2);
									$tknArr[$i] = (string) \trim((string)($tknArr[$i][0] ?? null));
								} //end if
								if(!isset($dbgarr['tpl-loop-vars'][(string)$tknArr[$i]])) {
									$dbgarr['tpl-loop-vars'][(string)$tknArr[$i]] = 0;
								} //end if
								$dbgarr['tpl-loop-vars'][(string)$tknArr[$i]]++;
							} //end if
							break;
						default:
							$tknArr[$i] = ''; // n/a
					} //end switch
				} //end if else
				//--
			} //end for
			//--
			$tbl_vars = ''; // init
			//-- pre-render vars
			$tbl_vars .= '<table id="'.'__twist__template__debug-tplvars_'.\Smart::escape_html((string)$hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="450" style="font-size:0.750em!important; float:left; margin-right:25px; margin-bottom:25px;">';
			$tbl_vars .= '<tr align="center"><th>{{ [RENDER] Variables }}</th><th>#&nbsp;('.(int)\Smart::array_size($dbgarr['tpl-vars']).')</th></tr>';
			if(\Smart::array_size($dbgarr['tpl-vars']) > 0) {
				foreach((array)$dbgarr['tpl-vars'] as $key => $val) {
					if((string)\substr((string)$key, 0, 1) != '_') {
						$tbl_vars .= '<tr><td align="left">';
						if((string)substr((string)$key, -2, 2) == '__') {
							$tbl_vars .= '<i>';
						} //end if
						$tbl_vars .= (string) \Smart::escape_html((string)$key);
						if((string)substr((string)$key, -2, 2) == '__') {
							$tbl_vars .= '</i>';
						} //end if
						$tbl_vars .= '</td>';
						$tbl_vars .= '<td align="right">';
						$tbl_vars .= (string) \Smart::escape_html((string)$val);
						$tbl_vars .= '</td></tr>';
					} //end if
				} //end foreach
				foreach((array)$dbgarr['tpl-vars'] as $key => $val) {
					if((string)\substr((string)$key, 0, 1) == '_') {
						$tbl_vars .= '<tr><td align="left">';
						$tbl_vars .= '<span style="color:#778899!important;">';
						$tbl_vars .= (string) \Smart::escape_html((string)$key);
						$tbl_vars .= '</span>';
						$tbl_vars .= '</td>';
						$tbl_vars .= '<td align="right">';
						$tbl_vars .= (string) \Smart::escape_html((string)$val);
						$tbl_vars .= '</td></tr>';
					} //end if
				} //end foreach
			} //end if
			$tbl_vars .= '</table>';
			//-- pre-render if vars
			$tbl_vars .= '<table id="'.'__twist__template__debug-tplvars_'.\Smart::escape_html((string)$hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="450" style="font-size:0.750em!important; float:left; margin-right:25px; margin-bottom:25px;">';
			$tbl_vars .= '<tr align="center"><th>{% [IF] conditional Variables %}</th><th>#&nbsp;('.(int)\Smart::array_size($dbgarr['tpl-if-vars']).')</th></tr>';
			if(\Smart::array_size($dbgarr['tpl-if-vars']) > 0) {
				foreach((array)$dbgarr['tpl-if-vars'] as $key => $val) {
					if((string)\substr((string)$key, 0, 1) != '_') {
						$tbl_vars .= '<tr><td align="left">';
						if((string)substr((string)$key, -2, 2) == '__') {
							$tbl_vars .= '<i>';
						} //end if
						$tbl_vars .= (string) \Smart::escape_html((string)$key);
						if((string)substr((string)$key, -2, 2) == '__') {
							$tbl_vars .= '</i>';
						} //end if
						$tbl_vars .= '</td>';
						$tbl_vars .= '<td align="right">';
						$tbl_vars .= (string) \Smart::escape_html((string)$val);
						$tbl_vars .= '</td></tr>';
					} //end if
				} //end foreach
				foreach((array)$dbgarr['tpl-if-vars'] as $key => $val) {
					if((string)\substr((string)$key, 0, 1) == '_') {
						$tbl_vars .= '<tr><td align="left">';
						$tbl_vars .= '<span style="color:#778899!important;">';
						$tbl_vars .= (string) \Smart::escape_html((string)$key);
						$tbl_vars .= '</span>';
						$tbl_vars .= '</td>';
						$tbl_vars .= '<td align="right">';
						$tbl_vars .= (string) \Smart::escape_html((string)$val);
						$tbl_vars .= '</td></tr>';
					} //end if
				} //end foreach
			} //end if
			$tbl_vars .= '</table>';
			//-- pre-render for (loop) vars
			$tbl_vars .= '<table id="'.'__twist__template__debug-tplvars_'.\Smart::escape_html((string)$hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="450" style="font-size:0.750em!important; float:left; margin-right:25px; margin-bottom:25px;">';
			$tbl_vars .= '<tr align="center"><th>{% [FOR] loop Variables %}</th><th>#&nbsp;('.(int)\Smart::array_size($dbgarr['tpl-loop-vars']).')</th></tr>';
			if(\Smart::array_size($dbgarr['tpl-loop-vars']) > 0) {
				foreach((array)$dbgarr['tpl-loop-vars'] as $key => $val) {
					if((string)\substr((string)$key, 0, 1) != '_') {
						$tbl_vars .= '<tr><td align="left">';
						if((string)substr((string)$key, -2, 2) == '__') {
							$tbl_vars .= '<i>';
						} //end if
						$tbl_vars .= (string) \Smart::escape_html((string)$key);
						if((string)substr((string)$key, -2, 2) == '__') {
							$tbl_vars .= '</i>';
						} //end if
						$tbl_vars .= '</td>';
						$tbl_vars .= '<td align="right">';
						$tbl_vars .= (string) \Smart::escape_html((string)$val);
						$tbl_vars .= '</td></tr>';
					} //end if
				} //end foreach
				foreach((array)$dbgarr['tpl-loop-vars'] as $key => $val) {
					if((string)\substr((string)$key, 0, 1) == '_') {
						$tbl_vars .= '<tr><td align="left">';
						$tbl_vars .= '<span style="color:#778899!important;">';
						$tbl_vars .= (string) \Smart::escape_html((string)$key);
						$tbl_vars .= '</span>';
						$tbl_vars .= '</td>';
						$tbl_vars .= '<td align="right">';
						$tbl_vars .= (string) \Smart::escape_html((string)$val);
						$tbl_vars .= '</td></tr>';
					} //end if
				} //end foreach
			} //end if
			$tbl_vars .= '</table>';
			//-- reserved for main tpl
			$twchemain = '';
			$haveSubTpls = 0;
			//-- pre-render subs
			$tbl_subs = '<table id="'.'__twist__template__debug-ldsubtpls_'.\Smart::escape_html((string)$hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="875" style="font-size:0.750em!important; float:left; margin-right:25px; margin-bottom:25px;">';
			$tbl_subs .= '<tr align="center"><th>{% [INCLUDE] Sub-Templates %}<br><small>*** All Loaded Sub-Templates are listed below ***</small></th></tr>';
			$tbl_subs .= '<tr><td align="center">';
			$tbl_subs .= '<table cellspacing="0" cellpadding="4" style="font-size:1em!important;">';
			if(\Smart::array_size($dbgarr['sub-tpls']) > 0) {
				foreach((array)$dbgarr['sub-tpls'] as $key => $val) {
					if(\is_array($val)) {
						if((string)($val['name'] ?? null) != (string)$dbg_tpl['dbg-file-name']) { // these are sub-classes
							$haveSubTpls++;
							$tbl_subs .= '<tr><td align="left">';
							$tbl_subs .= '<span style="font-size:1.15em!important;"><b><i>Twist-SubTPL Source File:</i></b> '.\Smart::escape_html((string)($val['name'] ?? null)).'</span><br>';
							$tbl_subs .= '<span style="color:#778899"><b><i>Twist-SubTPL Cache File:</i></b> '.\Smart::escape_html((string)$this->getCachePath().'TwistTPL_'.($val['hash'] ?? null).'.json').'</span><br>';
							$tbl_subs .= '</td></tr>';
						} else { // this is main class
							$twchemain = (string) $this->getCachePath().'TwistTPL_'.($val['hash'] ?? null).'.json';
						} //end if
					} //end if
				} //end foreach
			} //end if
			if((int)$haveSubTpls <= 0) {
				$tbl_subs .= '<tr><td align="left">';
				$tbl_subs .= '<center><span style="font-size:1.15em!important;">N/A</span></center>';
				$tbl_subs .= '</td></tr>';
			} //end if
			$tbl_subs .= '</table>';
			$tbl_subs .= '</td></tr>';
			if(\Smart::array_size($dbgarr['sub-tpls']) > 0) {
				$tbl_subs .= '<tr align="center"><th>Twist-TPL.View<br><small>[[ Render Tree ]]</small></th></tr>';
				$tbl_subs .= '<tr><td align="center">';
				$tbl_subs .= '<table cellspacing="0" cellpadding="4" style="font-size:1em!important;">';
				foreach((array)$dbgarr['sub-tpls'] as $key => $val) {
					if(\is_array($val)) {
						$tbl_subs .= '<tr><td align="left">';
						if((string)($val['name'] ?? null) != (string)$dbg_tpl['dbg-file-name']) {
							$tbl_subs .= '<span style="font-size:1.15em!important;">&nbsp;&nbsp;&nbsp;&boxur;&nbsp;'.\Smart::escape_html((string)($val['name'] ?? null)).' :: <i>'.\Smart::escape_html((string)($val['type'] ?? null)).'</i>'.'</span><br>';
						} else {
							$tbl_subs .= '<span style="font-size:1.15em!important;">&boxur;&nbsp;<b>'.\Smart::escape_html((string)($val['name'] ?? null)).'</b>'.' :: <b><i>'.\Smart::escape_html((string)($val['type'] ?? null)).'</i></b>'.'</span><br>';
						} //end if else
						$tbl_subs .= '</td></tr>';
					} //end if
				} //end foreach
				$tbl_subs .= '</table>';
				$tbl_subs .= '</td></tr>';
			} //end if
			$tbl_subs .= '<tr align="center"><th>Twist-TPL.Engine<br><small># Version #</small></th></tr>';
			$tbl_subs .= '<tr><td align="left">';
			$tbl_subs .= '<center><span style="font-size:1.15em!important;">v.'.\Smart::escape_html((string)\TwistTPL\Twist::VERSION).'</span></center>';
			$tbl_subs .= '</td></tr>';
			$tbl_subs .= '</table>';
			//-- inits
			$content = '<!-- START: Twist-TPL Debug Analysis @ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).' # -->'."\n";
			$content .= '<div align="left">';
			$content .= '<h3 style="display:inline;background:#666699;color:#FFFFFF;padding:3px;">Twist-TPL Debug Analysis</h3>';
			$content .= '<br><h4 style="display:inline;"><i>Twist-TPL Source File:</i> '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).'</h4>';
			$content .= '<br><h5 style="display:inline; color:#778899;"><i>Twist-TPL Cache File:</i> '.\Smart::escape_html((string)$twchemain).'</h5>';
			$content .= '<hr>';
			//-- start table
			$content .= '<table width="99%">';
			$content .= '<tr valign="top" align="center">';
			//-- tpl vars
			$content .= '<td align="left">';
			$content .= (string) $tbl_vars;
			$tbl_vars = ''; // clear
			$content .= '</td>';
			//-- loaded sub-tpls
			$content .= '<td align="left">';
			$content .= (string) $tbl_subs;
			$tbl_subs = '';
			$content .= '</td>';
			//-- end table
			$content .= '</tr></table><hr>';
			//--
			//$content .= '<hr><pre>'.\Smart::escape_html(print_r((array)$dbgarr,1)).'</pre><hr>';
			//-- source highlight
			$content .= (string) \SmartViewHtmlHelpers::html_jsload_hilitecodesyntax('div#tpl-twist-display-for-highlight', 'light').'</div><h2 style="display:inline;background:#666699;color:#FFFFFF;padding:3px;">Twist-TPL Source</h2><div id="tpl-twist-display-for-highlight"><pre id="'.'__twist__template__debug-tpl_'.\Smart::escape_html((string)$hash).'"><code class="debug-tpl" data-syntax="twist">'.\Smart::escape_html((string)$dbg_tpl['dbg-file-contents']).'</code></pre></div><hr>'."\n";
			//-- ending
			$content .= '<!-- #END: Twist-TPL Debug Analysis @ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).' -->';
			//--


			//--
		} elseif((string)\trim((string)$dbg_tpl['dbg-file-name']) == '') {
			//--
			$content = '<h1>WARNING: Empty Twist-TPL Template to Debug</h1>';
			//--
		} else {
			//--
			$content = '<h1>WARNING: Invalid Twist TPL Template to Debug: '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).'</h1>';
			//--
		} //end if else
		//--
		$dbg_tpl = [];
		//--
		return (string) $content;
		//--
	} //END FUNCTION


	//======= PRIVATES


	private function render_file_template(string $file, array $arr_vars=[], bool $onlydebug=false) : null|string|array { // mixed output: string or (onlydebug) array
		//--
		if(!\SmartEnvironment::ifDebug()) {
			$onlydebug = false;
		} //end if
		//-- allow camelCase keys
		$arr_vars = (array) $this->fixArrayKeys((array)$arr_vars, true); // make keys compatible with PHP variable names, LOWER and UPPER (only 1st level, not nested)
		//--
		if((string)\trim((string)$file) == '') {
			throw new \Exception('Twist Templating / Render File / The file name is Empty');
			return null;
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$file) !== 1) {
			throw new \Exception('Twist Templating / Render File / Invalid file Path');
			return null;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-twist/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
		//--
		$arr_tpl_parts = (array) \Smart::path_info($file);
		//--
		$dir_of_tpl   = (string) $arr_tpl_parts['dirname'];
		$the_tpl_file = (string) $arr_tpl_parts['basename'];
		//--
		if((string)$dir_of_tpl != '') {
			if(\SmartFileSysUtils::checkIfSafePath((string)$dir_of_tpl) !== 1) {
				$dir_of_tpl = (string) $invalid_dir; // fix if unsafe
			} //end if
			$dir_of_tpl = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$dir_of_tpl);
			if(\SmartFileSysUtils::checkIfSafePath((string)$dir_of_tpl) !== 1) {
				$dir_of_tpl = (string) $invalid_dir.'/'; // fix if unsafe
			} //end if
		} else {
			$dir_of_tpl = (string) $invalid_dir.'/'; // fix if empty
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$dir_of_tpl) !== 1) {
			throw new \Exception('Twist Templating / Render File / Invalid TPL Dir Path');
			return null;
		} //end if
		if(\SmartFileSysUtils::checkIfSafeFileOrDirName((string)$the_tpl_file) !== 1) {
			throw new \Exception('Twist Templating / Render File / Invalid TPL File Name');
			return null;
		} //end if
		//--
		$arr_vars[(string)$this->getTplPathVar().'__'] = (string) $dir_of_tpl; // this is the only tpl variable that will be case sensitive
		//--
		if(\SmartFileSysUtils::checkIfSafePath((string)$file) !== 1) {
			throw new \Exception('Twist Templating / Render File / The file name / path contains invalid characters: '.$file);
			return null;
		} //end if
		//--
		if(\SmartFileSysUtils::isFile((string)$file, true) !== true) { // use caching
			throw new \Exception('Twist Templating / The Template file to render does not exists: '.$file);
			return null;
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
			return null;
		} //end if
		//--
		$bench = 0;
		if(\SmartEnvironment::ifDebug()) {
			$bench = \microtime(true);
		} //end if
		//--
		try {
			$rendered = (string) $this->tpl->render((array)$arr_vars); // TODO: add cache gc()
		} catch(\Exception $e) {
			if($onlydebug !== true) {
				throw new \Exception((string)$e->getMessage());
			} //end if
		} //end try catch
		//--
		if(\SmartEnvironment::ifDebug()) {
			//--
			$bench = \Smart::format_number_dec((float)(\microtime(true) - (float)$bench), 9, '.', '');
			//--
			$arrTpls = (array) \array_values((array)\array_merge((array)\TwistTPL\Twist::getRenderedTplRecords('tpl'), \TwistTPL\Twist::getRenderedTplRecords('sub-tpl')));
			//--
			//$arrTpls = \TwistTPL\Twist::getCache(); print_r($arrTpls); die();
			//--
			$optim_msg = [];
			for($i=0; $i<\count($arrTpls); $i++) {
				//--
				$tpl_path = (string) \strval($arrTpls[$i]['name'] ?? null);
				$is_optimal = true;
				$msg_optimal = 'OK';
				$rds_optimal = 1;
				//--
				$action = (string) \SmartUtils::get_server_current_script();
				$action .= '?page=tpl-twist.debug&tpl=';
				$optim_msg[] = [
					'optimal' 	=> (bool)   $is_optimal,
					'value' 	=> (int)    $rds_optimal,
					'key' 		=> (string) $tpl_path,
					'msg' 		=> (string) $msg_optimal,
					'action' 	=> (string) $action,
				];
				\SmartEnvironment::setDebugMsg('extra', 'TWIST-TEMPLATING', [
					'title' => '[TPL-ReadFileTemplate-From-FS] :: Twist-TPL / File-Read: '.$tpl_path.' ;',
					'data' => 'Content SubStr[0-'.(int)$this->smartGetdebugTplLength().']: '."\n".\Smart::text_cut_by_limit((string)\SmartFileSysUtils::readStaticFile((string)$tpl_path), (int)$this->smartGetdebugTplLength(), true, '[...]')
				]);
				//--
			} //end for
			\SmartEnvironment::setDebugMsg('optimizations', '*TWIST-TPL-CLASSES:OPTIMIZATION-HINTS*', [
				'title' => 'SmartTwistTemplating // Optimization Hints @ Number of FS Reads for rendering current Template incl. Sub-Templates ; Test if Cache File exists',
				'data' => (array) $optim_msg
			]);
			//--
			if($onlydebug === true) {
				return (array) $arrTpls;
			} else {
				\SmartEnvironment::setDebugMsg('extra', 'TWIST-TEMPLATING', [
					'title' => '[TPL-Parsing:Render.DONE] :: Twist-TPL / Processing ; Time = '.$bench.' sec.',
					'data' => 'TPL Rendered Files: '.\Smart::array_size($arrTpls).' ; TPL Variables: '.\Smart::array_size($arr_vars)
				]);
				return (string) $rendered;
			} //end if else
			//--
		} //end if
		//--
		return (string) $rendered;
		//--
	} //END FUNCTION


	private function getCachePath() : string {
		//--
		$tpl_cache_path = (string) 'tmp/cache/tpl-twist/v'.(int)\TwistTPL\Twist::MAJOR_VERSION.'.'.(int)\TwistTPL\Twist::MINOR_VERSION.'.'.(int)\TwistTPL\Twist::EXTRA_VERSION.'/'.\SmartHashCrypto::crc32b((string)$this->dir, true).'-'.\SmartHashCrypto::crc32b((string)\strrev((string)$this->dir), true).'/';
		//--
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
		return (string) \SmartFileSysUtils::addPathTrailingSlash((string)$tpl_cache_path);
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


//--
/**
 *
 * @access 		private
 * @internal
 *
 */
\spl_autoload_register(function(string $classname) : void {
	//--
	if((\strpos((string)$classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', (string)$classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(\str_starts_with((string)$classname, 'TwistTPL\\') === false) { // if class name is starting with TwistTPL\
		return;
	} //end if
	//--
	$path = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-tpl-twist/libs/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
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
