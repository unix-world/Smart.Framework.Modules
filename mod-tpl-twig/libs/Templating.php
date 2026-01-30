<?php
// Class: \SmartModExtLib\TplTwig\Templating
// [Smart.Framework.Modules - Twig / Templating]
// (c) 2006-present unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\TplTwig;

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
 * Provides connector for Twig Templating inside the Smart.Framework.
 * Using this class directly in Smart.Framework context is not secure ; Use instead SmartTwigTemplating !
 *
 * <code>
 *
 * // Sample: use this code in a controller of Smart.Framework (after you install the Smart.Framework.Modules)
 * $this->PageViewSetVar(
 *     'main',
 *     (new \SmartModExtLib\TplTwig\Templating())->renderFileTemplate(
 *         'modules/my-module-name/views/myView.twig.htm',
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
 * @depends 	extensions: PHP Ctype (optional) ; classes: \SmartModExtLib\Tpl\AbstractTemplating, \SmartModExtLib\TplTwig\SmartTwigEnvironment, \Twig, \Symfony\Polyfill\Ctype\Ctype if PHP Ctype ext is N/A
 * @version 	v.20260130
 * @package 	modules:TemplatingEngine
 *
 */
final class Templating extends \SmartModExtLib\Tpl\AbstractTemplating {

	// ->

	private $dir;
	private $twig;
	private $twprof;


	public static function getVersion() : string {
		//--
		return (string) \Twig\Environment::VERSION;
		//--
	} //END FUNCTION


	public function __construct() {
		//--
		$this->dir = (string) \trim((string)\SmartFileSysUtils::getSmartFsRootPath()); // sf.vendoring
		if((string)$this->dir == '') {
			$this->dir = './'; // Smart.Framework
		} //end if
		//--
		$this->twig = new \SmartModExtLib\TplTwig\SmartTwigEnvironment(
			new \Twig\Loader\FilesystemLoader([ (string)$this->dir ]),
			[
				'charset' 			=> (string) \SMART_FRAMEWORK_CHARSET,
				'autoescape' 		=> 'html', // default escaping strategy ; other escaping strategies: js
				'optimizations' 	=> -1,
				'strict_variables' 	=> false,
				'debug' 			=> false,
				'cache' 			=> false,
				'auto_reload' 		=> true
			]
		);
		//--
		$the_twig_cache_dir = (string) \SmartFileSysUtils::getSmartFsRootPath().$this->twig->smartSetupCacheDir();
		//--
		if(\SmartEnvironment::ifDebug()) {
			//--
			$this->twprof = new \Twig\Profiler\Profile('main', \Twig\Profiler\Profile::ROOT, 'Twig-TPL.View');
			$this->twig->addExtension(new \Twig\Extension\ProfilerExtension($this->twprof));
			//--
			$this->twig->addExtension(new \Twig\Extension\DebugExtension());
			$this->twig->enableDebug(); // advanced debugging
			//--
		} else {
			//--
			$this->twig->disableDebug();
			//--
		} //end if else
		//--
		//$this->twig->setCache(false);
		$this->twig->setCache((string)$the_twig_cache_dir);
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
			(\SmartFileSysUtils::checkIfSafePath((string)($dbg_tpl['dbg-file-name'] ?? null)))
			AND
			(\is_file((string)($dbg_tpl['dbg-file-name'] ?? null))) // be independent of smart file system class, this module can be exported for vendoring
			AND
			((string)($dbg_tpl['dbg-file-contents'] ?? null) != '')
		) {
			//-- the hash
			$hash = (string) \sha1((string)$dbg_tpl['dbg-file-name']);
			//-- get arr dbg data
			$dbgarr = (array) $this->render_file_template((string)$dbg_tpl['dbg-file-name'], [], true); // need to render before get dbg
			//-- pre-render vars
			$tbl_vars = '<table id="'.'__twig__template__debug-tplvars_'.\Smart::escape_html((string)$hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="500" style="font-size:0.750em!important; float:left; margin-right:25px; margin-bottom:25px;">';
			$tbl_vars .= '<tr align="center"><th>{{ Twig-TPL variables usage, incl. Sub-TPLs }}</th><th>#&nbsp;('.(int)\Smart::array_size($dbgarr['tpl-vars']).')</th></tr>';
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
			//-- reserved class for main
			$twchemain = '';
			$twclsmain = '';
			//-- pre-render subs
			$tbl_subs = '<table id="'.'__twig__template__debug-ldsubtpls_'.\Smart::escape_html((string)$hash).'" class="debug-table debug-table-striped" cellspacing="0" cellpadding="4" width="850" style="font-size:0.750em!important; float:left; margin-right:25px; margin-bottom:25px;">';
			$tbl_subs .= '<tr align="center"><th>{% SUB-TEMPLATES:INCLUDE %}<br><small>*** All Loaded Sub-Templates are listed below ***</small></th></tr>';
			if(\Smart::array_size($dbgarr['sub-tpls']) > 0) {
				foreach((array)$dbgarr['sub-tpls'] as $key => $val) {
					if(\is_array($val)) {
						if((string)$val['tpl'] != (string)$dbg_tpl['dbg-file-name']) { // these are sub-classes
							$tbl_subs .= '<tr><td align="left">';
							$tbl_subs .= '<span style="font-size:1.15em!important;"><b><i>Twig-SubTPL Source File:</i></b> '.\Smart::escape_html((string)$val['tpl']).'</span><br>';
							$tbl_subs .= '<span style="color:#778899"><b><i>Twig-SubTPL Cache File:</i></b> '.\Smart::escape_html((string)$val['cache']).'</span><br>';
							$tbl_subs .= '<span style="color:#666699"><b><i>Twig-SubTPL PHP-Class:</i></b> '.\Smart::escape_html((string)$key).'{}'.'</span><br>';
							$tbl_subs .= '</td></tr>';
						} else { // this is main class
							$twchemain = (string) $val['cache'];
							$twclsmain = (string) $key.'{}';
						} //end if
					} //end if
				} //end foreach
			} //end if
			if(\is_object($this->twprof)) {
				$tbl_subs .= '<tr><td align="left">';
				$dumper = new \Twig\Profiler\Dumper\TextDumper();
				$tbl_subs .= '<hr><pre>'.\Smart::escape_html((string)$dumper->dump($this->twprof)).'</pre><hr>';
				$tbl_subs .= 'PHP&nbsp;Compile&nbsp;Time:&nbsp;'.\Smart::escape_html((string)\Smart::format_number_dec((float)$this->twprof->getDuration(), 9, '.', '')).'&nbsp;seconds<br>';
				$tbl_subs .= 'PHP&nbsp;Memory&nbsp;Usage:&nbsp;'.\Smart::escape_html((string)(int)$this->twprof->getPeakMemoryUsage()).'&nbsp;bytes<br>';
				$tbl_subs .= '<hr><b>Twig&nbsp;'.\Smart::escape_html('v.'.(int)\Twig\Environment::MAJOR_VERSION).'</b>&nbsp;::&nbsp;version&nbsp;'.\Smart::escape_html((string)$this->getVersion()).'<br>';
				$tbl_subs .= '</td></tr>';
			} //end if
			$tbl_subs .= '</table>';
			//-- inits
			$content = '<!-- START: Twig-TPL Debug Analysis @ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).' # -->'."\n";
			$content .= '<div align="left">';
			$content .= '<h3 style="display:inline;background:#003366;color:#FFFFFF;padding:3px;">Twig-TPL Debug Analysis</h3>';
			$content .= '<br><h4 style="display:inline;"><i>Twig-TPL Source File:</i> '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).'</h4>';
			$content .= '<br><h5 style="display:inline; color:#778899;"><i>Twig-TPL Cache File:</i> '.\Smart::escape_html((string)$twchemain).'</h5>';
			$content .= '<br><h5 style="display:inline; color:#666699;"><i>Twig-TPL PHP-Class:</i> '.\Smart::escape_html((string)$twclsmain).'</h5>';
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
			$content .= (string) \SmartViewHtmlHelpers::html_jsload_hilitecodesyntax('div#tpl-twig-display-for-highlight', 'light').'</div><h2 style="display:inline;background:#003366;color:#FFFFFF;padding:3px;">Twig-TPL Source</h2><div id="tpl-twig-display-for-highlight"><pre id="'.'__twig__template__debug-tpl_'.\Smart::escape_html((string)$hash).'"><code class="debug-tpl" data-syntax="twig">'.\Smart::escape_html((string)$dbg_tpl['dbg-file-contents']).'</code></pre></div><hr>'."\n";
			//-- ending
			$content .= '<!-- #END: Twig-TPL Debug Analysis @ '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).' -->';
			//--
		} elseif((string)\trim((string)$dbg_tpl['dbg-file-name']) == '') {
			//--
			$content = '<h1>WARNING: Empty Twig-TPL Template to Debug</h1>';
			//--
		} else {
			//--
			$content = '<h1>WARNING: Invalid Twig TPL Template to Debug: '.\Smart::escape_html((string)$dbg_tpl['dbg-file-name']).'</h1>';
			//--
		} //end if else
		//--
		$dbg_tpl = [];
		//--
		return (string) $content;
		//--
	} //END FUNCTION


	//======= PRIVATES


	private function render_file_template(string $file, array $arr_vars=[], bool $onlydebug=false) : null|string|array {
		//--
		if(!\SmartEnvironment::ifDebug()) {
			$onlydebug = false;
		} //end if
		//-- allow camelCase keys
		$arr_vars = (array) $this->fixArrayKeys((array)$arr_vars, true); // make keys compatible with PHP variable names, LOWER and UPPER (only 1st level, not nested)
		//--
		if((string)\trim((string)$file) == '') {
			throw new \Exception('Twig Templating / Render File / The file name is Empty');
			return null;
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)$file)) {
			throw new \Exception('Twig Templating / Render File / Invalid file Path');
			return null;
		} //end if
		//--
		$invalid_dir = 'modules/mod-tpl-twig/views/INVALID-PATH'; // this path cannot be empty as templates cannot be located in the app's root !!!
		//--
		$dir_of_tpl = (string) \Smart::dir_name((string)$file);
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
			throw new \Exception('Twig Templating / Render File / Invalid tpl Path');
			return null;
		} //end if
		//--
		$arr_vars[(string)$this->getTplPathVar().'__'] = (string) $dir_of_tpl;
		//--
		if(!\SmartFileSysUtils::checkIfSafePath((string)$file)) {
			throw new \Exception('Twig Templating / Render File / The file name / path contains invalid characters: '.$file);
			return null;
		} //end if
		//--
		if(!\is_file((string)$this->dir.$file)) {
			throw new \Exception('Twig Templating / The Template file to render does not exists: '.$file);
			return null;
		} //end if
		//--
		if(\SmartEnvironment::ifDebug()) {
			$bench = \microtime(true);
			$tpl = (object) $this->twig->load((string)$file);
			$out = (string) $tpl->render((array)$arr_vars);
			$bench = \Smart::format_number_dec((float)(\microtime(true) - (float)$bench), 9, '.', '');
			if($onlydebug === true) {
				return (array) $this->twig->smartDebugGetLoadedTemplates('get');
			} else {
				$dbgarr = (array) $this->twig->smartDebugGetLoadedTemplates('set');
				\SmartEnvironment::setDebugMsg('extra', 'TWIG-TEMPLATING', [
					'title' => '[TPL-Parsing:Render.DONE] :: Twig-TPL / Processing ; Time = '.$bench.' sec.',
					'data' => 'TPL Rendered Files: '.\Smart::array_size($dbgarr['sub-tpls']).' ; TPL Variables: '.\Smart::array_size($dbgarr['tpl-vars'])
				]);
				return (string) $out;
			} //end if else
		} else {
			return (string) $this->twig->render((string)$file, (array)$arr_vars);
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
	if((\strpos((string)$classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', (string)$classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(
		(str_starts_with((string)$classname, 'Symfony\\Polyfill\\') === false) // must start with with Symfony\Polyfill\
		AND
		(str_starts_with((string)$classname, 'Twig\\') === false) // must start with Twig\
	) { // must start with this namespaces only
		return;
	} //end if
	//--
	$path = '';
	if(\str_starts_with((string)$classname, 'Symfony\\Polyfill\\') === true) { // if class name is starting with Symfony\Polyfill\
		//--
		$path = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-tpl-twig/libs/polyfill/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
		//--
	} elseif(\str_starts_with((string)$classname, 'Twig\\') === true) { // if class name is starting with Twig\
		//--
		$path = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-tpl-twig/libs/twig/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
		//--
	} //end if
	if((string)$path == '') {
		return;
	} //end if
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

//--
if(!\function_exists('\\ctype_alnum')) {
	require_once(\SmartFileSysUtils::getSmartFsRootPath().'modules/mod-tpl-twig/libs/polyfill/Symfony/Polyfill/Ctype/Ctype-bootstrap.php');
} //end if
//--
//if(!\function_exists('\\trigger_deprecation')) {
	require_once(\SmartFileSysUtils::getSmartFsRootPath().'modules/mod-tpl-twig/libs/deprecation-contracts/function.php');
//} //end if
//--

// end of php code
