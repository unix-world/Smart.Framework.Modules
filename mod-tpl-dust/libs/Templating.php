<?php
// Class: \SmartModExtLib\TplDust\Templating
// [Smart.Framework.Modules - Dust / Templating]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplDust;

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
 * Provides connector for (PHP) Dust Templating inside the Smart.Framework.
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     (new \SmartModExtLib\TplDust\Templating())->render_file_template(
 *         'modules/my-module-name/views/myView.dust.htm',
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
 * @depends 	extensions: PHP Ctype ; classes: Dust
 * @version 	v.20191129
 * @package 	modules:TemplatingEngine
 *
 */
final class Templating extends \SmartModExtLib\Tpl\AbstractTemplating {

	// ->

	private $dir;
	private $dust;


	public static function getVersion() {
		//--
		return (string) \Dust\Dust::VERSION;
		//--
	} //END FUNCTION


	public function __construct() {
		//--
		if(!\function_exists('\\ctype_alnum')) {
			\Smart::raise_error(__METHOD__.'() # PHP Ctype extension is required but not found ...');
			return;
		} //end if
		//--
		$this->dir = 'modules/';
		//--
		$this->dust = new \Dust\Dust($this->dir);
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
			throw new \Exception('Dust Templating / Render File / The file name is Empty');
			return;
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \Exception('Dust Templating / Render File / Invalid file Path');
			return;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-dust/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
		//--
		$arr_tpl_parts = (array) \Smart::path_info($file);
		//--
		$dir_of_tpl = (string) $arr_tpl_parts['dirname'];
		$the_tpl_file = (string) $arr_tpl_parts['basename'];
		//--
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
			throw new \Exception('Dust Templating / Render File / Invalid TPL Dir Path');
			return;
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_file_or_dir_name($the_tpl_file)) {
			throw new \Exception('Dust Templating / Render File / Invalid TPL File Name');
			return;
		} //end if
		//--
		$arr_vars['Tpl_Dir__'] = (string) $dir_of_tpl; // this is the only tpl variable that will be case sensitive
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \Exception('Dust Templating / Render File / The file name / path contains invalid characters: '.$file);
			return;
		} //end if
		//--
		if(!\is_file($file)) {
			throw new \Exception('Dust Templating / The Template file to render does not exists: '.$file);
			return;
		} //end if
		//--
		if(\SmartFrameworkRuntime::ifDebug()) {
			$bench = \microtime(true);
			$pmu = 0;
			if(\function_exists('\\memory_get_peak_usage')) {
				$pmu = (int) @\memory_get_peak_usage(false);
			} //end if
		} //end if
		//--
		$template = $this->dust->compileFile((string)$the_tpl_file, (string)$dir_of_tpl);
		if(!$template) {
			throw new \Exception('Dust Templating / Failed to render: '.$file);
			return;
		} //end if
		$rendered = (string) $this->dust->renderTemplate($template, (array)$arr_vars);
		//--
		if(\SmartFrameworkRuntime::ifDebug()) {
			//--
			$optim_msg = [];
			foreach((array)$this->dust->getFsRdRpls() as $key => $val) {
				if($val > 1) {
					$is_optimal = false;
					$msg_optimal = 'Dust File is read more than once';
					$rds_optimal = 0;
				} else {
					$is_optimal = true;
					$msg_optimal = 'OK';
					$rds_optimal = 1;
				} //end if else
				$optim_msg[] = [
					'optimal' 	=> (bool)   $is_optimal,
					'value' 	=> (int)    $rds_optimal,
					'key' 		=> (string) $key,
					'msg' 		=> (string) $msg_optimal,
					'action' 	=> (string) \SmartUtils::get_server_current_script().'?page=tpl-dust.debug&tpl='
				];
			} //end foreach
			\SmartFrameworkRegistry::setDebugMsg('optimizations', '*DUST-TPL-CLASSES:OPTIMIZATION-HINTS*', [
				'title' => 'DustTemplating // Optimization Hints @ Number of FS Reads for rendering current Template incl. Sub-Templates ; Test if Cache File exists',
				'data' => (array) $optim_msg
			]);
			$optim_msg = null;
			//--
			$bench = \Smart::format_number_dec((float)(\microtime(true) - (float)$bench), 9, '.', '');
			if(\function_exists('\\memory_get_peak_usage')) {
				$pmu = ((int)@\memory_get_peak_usage(false) - (int)$pmu);
			} //end if
			//--
			$dbginf = (array) $this->dust->getTemplates();
			$dbgarr = [];
			$dbgarr['dbg-file-name'] = '';
			$dbgarr['render-time'] = $bench; // sec.
			$dbgarr['render-mem'] = $pmu; // bytes
			$dbgarr['tpl-vars'] = [];
			$dbgarr['sub-tpls'] = [];
			$found = 0;
			foreach($dbginf as $key => $val) {
				if((string)$key != '') {
					if((string)$val->filePath != '') {
						if((string)$the_tpl_file == (string)$key) {
							if((string)$val->filePath == (string)$file) {
								if(!$found) {
									$dbgarr['dbg-file-name'] = (string) $val->filePath;
									$found++;
								} //end if
							} //end if
						} //end if
						$dbgarr = (array) $this->dbgParseDustObjBody($dir_of_tpl, $dbgarr, $key, $val);
					} //end if
				} //end if
			} //end foreach
			//--
			//die('Found:'.$found);
			//echo'<pre>'.\Smart::escape_html(print_r($this->dust,1)).'</pre>'; die();
			//--
			if($onlydebug) {
				return (array) $dbgarr;
			} else {
				\SmartFrameworkRegistry::setDebugMsg('extra', 'DUST-TEMPLATING', [
					'title' => '[TPL-Parsing:Render.DONE] :: Dust-TPL / Processing ; Time = '.$bench.' sec.',
					'data' => 'TPL Rendered Files: '.(1+\Smart::array_size($dbgarr['sub-tpls'])).' ; TPL Variables: '.\Smart::array_size($dbgarr['tpl-vars'])
				]);
			} //end if else
			//--
		} //end if
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
		$content = '';
		//--
		$dbg_tpl = (array) \SmartDebugProfiler::read_tpl_file_for_debug((string)$tpl);
		//--
		if((\SmartFileSystem::is_type_file($dbg_tpl['dbg-file-name'])) AND ((string)$dbg_tpl['dbg-file-contents'] != '')) {
			//-- the hash
			$hash = (string) \sha1((string)$dbg_tpl['dbg-file-name']);
			//-- get arr dbg data
			$dbgarr = (array) $this->render_file_template((string)$dbg_tpl['dbg-file-name'], [], true); // need to render before get dbg
			//-- pre-render vars
			$tbl_vars = '<table id="'.'__dust__template__debug-tplvars_'.\Smart::escape_html($hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="500" style="font-size:0.750em!important;">';
			$tbl_vars .= '<tr align="center"><th>{ Dust-TPL variables usage, incl. Sub-TPLs }</th><th>#&nbsp;('.(int)\Smart::array_size($dbgarr['tpl-vars']).')</th></tr>';
			if(\Smart::array_size($dbgarr['tpl-vars']) > 0) {
				foreach((array)$dbgarr['tpl-vars'] as $key => $val) {
					if((\strpos((string)$key, '$') !== 0) AND (\strpos((string)$key, '.$') === false)) {
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
					if((\strpos((string)$key, '$') === 0) OR (\strpos((string)$key, '.$') !== false)) {
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
			//-- pre-render subs
			$tbl_subs = '<table id="'.'__dust__template__debug-ldsubtpls_'.\Smart::escape_html($hash).'" class="ux-table ux-table-striped" cellspacing="0" cellpadding="4" style="font-size:0.750em!important;">';
			$tbl_subs .= '<tr align="center"><th>{&gt; SUB-TEMPLATES:INCLUDE /}<br><small>*** Level-1 Only Loaded Sub-Templates are listed below ***</small></th></tr>';
			if(\Smart::array_size($dbgarr['sub-tpls']) > 0) {
				foreach((array)$dbgarr['sub-tpls'] as $key => $val) {
					$tbl_subs .= '<tr><td align="left">';
					$tbl_subs .= '<span style="font-size:1.15em!important;"><b><i>Dust-SubTPL Source File:</i></b> '.\Smart::escape_html((string)$val).'</span><br>';
					$tbl_subs .= '<span style="color:#778899"><b><i>Dust-SubTPL Cache File:</i></b> php://memory/dustTPL_'.\Smart::escape_html(\sha1((string)$key)).'</span><br>';
					$tbl_subs .= '<span style="color:#666699"><b><i>Twig-SubTPL PHP-Class:</i></b> \\Dust\\Ast\\Body__'.\Smart::escape_html(\sha1((string)$key)).'{}'.'</span><br>';
					$tbl_subs .= '</td></tr>';
				} //end foreach
			} //end if
			$tbl_subs .= '<tr><td align="left">';
			$tbl_subs .= '<hr>';
			$tbl_subs .= '<pre>'.'Dust-TPL.View'."\n".'└ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']);
			if(\Smart::array_size($dbgarr['sub-tpls']) > 0) {
				foreach((array)$dbgarr['sub-tpls'] as $key => $val) {
					$tbl_subs .= "\n".'  └ '.\Smart::escape_html((string)$val);
				} //end foreach
			} //end if
			$tbl_subs .= '</pre>';
			$tbl_subs .= '<hr>';
			$tbl_subs .= 'Compile&nbsp;Time:&nbsp;'.\Smart::escape_html((string)\Smart::format_number_dec((float)$dbgarr['render-time'], 9, '.', '')).'&nbsp;seconds<br>';
			$tbl_subs .= 'Memory&nbsp;Usage:&nbsp;'.\Smart::escape_html((string)(int)$dbgarr['render-mem']).'&nbsp;bytes<br>';
			$tbl_subs .= '</td></tr>';
			$tbl_subs .= '</table>';
			//-- inits
			$content = '<!-- START: Dust-TPL Debug Analysis @ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).' # -->'."\n";
			$content .= '<div align="left">';
			$content .= '<h3 style="display:inline;background:#003366;color:#FFFFFF;padding:3px;">Dust-TPL Debug Analysis</h3>';
			$content .= '<br><h4 style="display:inline;"><i>Dust-TPL Source File:</i> '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).'</h4>';
			$content .= '<br><h5 style="display:inline; color:#778899;"><i>Twig-TPL Cache File:</i> php://memory/dustTPL_'.\Smart::escape_html($hash).'</h5>';
			$content .= '<br><h5 style="display:inline; color:#666699;"><i>Twig-TPL PHP-Class:</i> \\Dust\\Ast\\Body__'.\Smart::escape_html($hash).'{}</h5>';
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
			$content .= (string) \SmartViewHtmlHelpers::html_jsload_highlightsyntax('div#tpl-dust-display-for-highlight',['web','tpl']).'<script type="text/javascript" src="modules/mod-tpl/views/js/highlightjs-extra/syntax/tpl/dust.js"></script>'.'</div><h2 style="display:inline;background:#003366;color:#FFFFFF;padding:3px;">Dust-TPL Source</h2><div id="tpl-dust-display-for-highlight"><pre id="'.'__dust__template__debug-tpl_'.\Smart::escape_html(\sha1((string)$dbg_tpl['dbg-file-name'])).'"><code class="dust">'.\Smart::escape_html($dbg_tpl['dbg-file-contents']).'</code></pre></div><hr>'."\n";
			//-- ending
			$content .= '<!-- #END: Dust-TPL Debug Analysis @ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).' -->';
			//--
		} elseif((string)\trim((string)$dbg_tpl['dbg-file-name']) == '') {
			//--
			$content = '<h1>WARNING: Empty Dust-TPL Template to Debug</h1>';
			//--
		} else {
			//--
			$content = '<h1>WARNING: Invalid Dust TPL Template to Debug: '.\Smart::escape_html($dbg_tpl['dbg-file-name']).'</h1>';
			//--
		} //end if else
		//--
		$dbg_tpl = array();
		//--
		return (string) $content;
		//--
	} //END FUNCTION


	//#####


	private function dbgParseDustObjBody($basePath, $dbgarr, $key, $val, $prefix='') {
		//--
		if(!\is_array($dbgarr)) {
			$dbgarr = array();
		} //end if
		//--
		if(\is_a($val, '\\Dust\\Ast\\Body')) {
			if(\Smart::array_size($val->parts) > 0) {
				for($i=0; $i<\Smart::array_size($val->parts); $i++) {
					if(\is_a($val->parts[$i], '\\Dust\\Ast\\Reference')) {
						if(\is_a($val->parts[$i]->identifier, '\\Dust\\Ast\\Identifier')) {
							$dbgarr['tpl-vars'][(string)$prefix.$val->parts[$i]->identifier->key] += 1;
						} //end if
					} elseif(\is_a($val->parts[$i], '\\Dust\Ast\Section')) {
						if((string)$val->parts[$i]->type == '#') {
							if(\is_a($val->parts[$i]->identifier, '\\Dust\\Ast\\Identifier')) {
								$prefix = (string) $prefix.$val->parts[$i]->identifier->key.'.';
							} //end if
						} elseif((string)$val->parts[$i]->type != '@') {
							if(\is_a($val->parts[$i]->identifier, '\\Dust\\Ast\\Identifier')) {
								$dbgarr['tpl-vars'][(string)$prefix.$val->parts[$i]->identifier->key] += 1;
							} //end if
						} //end if else
						if(\is_a($val->parts[$i]->body, '\\Dust\\Ast\\Body')) {
							$dbgarr = (array) $this->dbgParseDustObjBody($basePath, $dbgarr, $key, $val->parts[$i]->body, $prefix);
						} //end if
						if(\strpos($prefix, '.') !== false) {
							$prefix = (array) \explode('.', (string)$prefix);
							\array_pop($prefix); // pop last empty element
							\array_pop($prefix); // pop last real prefix
							$prefix = (string) implode('.', (array)$prefix);
						} //end if else
					} elseif(\is_a($val->parts[$i], '\\Dust\\Ast\\Partial')) {
						if(\is_a($val->parts[$i]->inline, '\\Dust\\Ast\\Inline')) {
							if(\Smart::array_size($val->parts[$i]->inline->parts) > 0) {
								if(\is_a($val->parts[$i]->inline->parts[0], '\\Dust\\Ast\\InlineLiteral')) {
									if(!\in_array((string)$basePath.$val->parts[$i]->inline->parts[0]->value, (array)$dbgarr['sub-tpls'])) {
										$dbgarr['sub-tpls'][] = (string) $basePath.$val->parts[$i]->inline->parts[0]->value;
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end for
			} //end if
		} //end if
		//--
		return (array) $dbgarr;
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
function autoload__DustTemplating_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 5) !== 'Dust\\') { // if class name is not starting with Dust
		return;
	} //end if
	//--
	$path = 'modules/mod-tpl-dust/libs/'.\str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
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
\spl_autoload_register('\\SmartModExtLib\\TplDust\\autoload__DustTemplating_SFM', true, false); // throw / append
//--


//end of php code
?>