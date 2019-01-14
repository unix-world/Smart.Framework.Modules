<?php
// Class: \SmartModExtLib\PageBuilder\AbstractFrontendController
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModExtLib\PageBuilder;

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
 * Class: AbstractFrontendController - Abstract Frontend Controller, provides the Abstract Definitions to create PageBuilder (Frontend) Controllers.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		extend frontend controllers from this one
 *
 * @access 		PUBLIC
 *
 * @version 	v.20190114
 * @package 	PageBuilder
 *
 */
abstract class AbstractFrontendController extends \SmartAbstractAppController {


	protected $max_depth 		= 2; 						// 0=page, 1=segment, 2=sub-segment
	protected $cache_time 		= 3600; 					// cache time in seconds

	private $crr_lang 			= '';						// current language

	private $page_markers 		= [];						// extra markers to allow be direct set in template except MAIN (and others as: TITLE, META-DESCRIPTION, META-KEYWORDS)
	private $regex_tpl_marker 	= '/^[A-Z0-9_\-\.@]+$/'; 	// regex for tpl markers
	private $regex_marker 		= '/^[A-Z0-9_\-\.]+$/'; 	// regex for internal markers

	private $auth_required 		= 0; 						// 0: no auth ; if > 0, will req. auth
	private $recursion_control 	= 0; 						// initialize
	private $current_page 		= []; 						// array of page load
	private $page_params 		= [];						// array of important page params to pass from controller to plugins

	private $page_is_cached 	= false; 					// true if found in pcache
	private $segments_cached 	= []; 						// registers cached segments

	private $render_done 		= false; 					// internal flag to avoid re-render
	private $rendered_segments 	= []; 						// register rendered segments

	private $debug 				= false; 					// internal debug


	//=====
	final public function renderBuilderPage($page_id, $tpl_path, $tpl_file, $markers) { // (OUTPUTS: HTML)

		//--
		$this->max_depth = $this->fixRenderMaxDepth($this->max_depth);
		//--

		//--
		$this->crr_lang = (string) \SmartTextTranslations::getLanguage();
		if((string)$this->crr_lang == (string)\SmartTextTranslations::getDefaultLanguage()) {
			$this->crr_lang = ''; // fix to avoid query translations on default language
		} //end if
		//--

		//--
		$page_id = (string) trim((string)$page_id);
		//--
		if(((string)$page_id == '') OR (substr((string)$page_id, 0, 1) == '#')) {
			$this->PageViewSetErrorStatus(404, 'NOTICE: Empty / Invalid PageBuilder Page ID to Render ...');
			return;
		} //end if
		//--

		//-- must test after seing if the page ID is valid
		if($this->render_done !== false) {
			\Smart::raise_error(
				'PageBuilder: The abstract controller '.__CLASS__.' dissalow rendering multiple pages per controller'
			);
			die();
			return;
		} //end if
		//--
		$this->render_done = true; // flag: dissalow multiple page renders per controller
		//--

		//--
		$this->PageViewSetCfg('template-path', (string)$tpl_path);
		$this->PageViewSetCfg('template-file', (string)$tpl_file);
		//--
		$this->page_markers = (array) $this->fixAllowedTemplateMarkers($markers);
		//--

		//--
		$arr = array();
		//--
		$the_pcache_key = (string) $page_id.'@'.\SmartTextTranslations::getLanguage(); // .'__d'.(int)$this->max_depth.'__m-'.sha1((string)implode(';', $this->page_markers))
		//--
		if($this->PageCacheisActive()) {
			//$arr = (array) $this->PageGetFromCache(
			$pcache_arr = (array) $this->PageGetFromCache(
				'smart-pg-builder',
				$this->PageCacheSafeKey((string)$the_pcache_key)
			); // get arr vars structure from pcache
			//print_r($pcache_arr); die();
			if(\Smart::array_size($pcache_arr) > 0) {
				if((is_array($pcache_arr['headers'])) && (is_array($pcache_arr['configs'])) && (is_array($pcache_arr['vars']))) { // if valid cache (test ... there must be the 3 sub-arrays as exported previous in pcache)
				//	$this->PageViewResetRawHeaders();
					$this->PageViewSetRawHeaders((array)$pcache_arr['headers']);
					$this->PageViewSetCfgs((array)$pcache_arr['configs']);
					$arr = (array) $pcache_arr['vars'];
					//print_r($arr);
					$this->page_is_cached = true;
				} //end if
			} //end if
			$pcache_arr = array();
		} //end if
		//--
		$is_ok = true;
		//--
		if($this->page_is_cached !== true) {
			$arr = (array) $this->loadSegmentOrPage((string)$page_id, 'page'); // get arr vars structure from db
		} //end if
		if((int)$this->PageViewGetStatusCode() >= 400) {
			if(in_array((int)$this->PageViewGetStatusCode(), (array)\SmartFrameworkRuntime::getHttpStatusCodesERR())) {
				$is_ok = false;
			} //end if
		} //end if
		if(\Smart::array_size($arr) <= 0) {
			$is_ok = false;
			\Smart::log_warning('PageBuilder: Invalid Page Load Data: No Array / Empty Array');
			$this->PageViewSetErrorStatus(500, 'WARNING: Invalid PageBuilder Page Load Data');
		} //end if
		//--
		if(($this->page_is_cached !== true) AND ($this->PageCacheisActive())) {
			//--
			$result_pcached = (bool) $this->PageSetInCache(
				'smart-pg-builder',
				$this->PageCacheSafeKey((string)$the_pcache_key),
				[
					'headers' 	=> (array) $this->PageViewGetRawHeaders(),
					'configs' 	=> (array) $this->PageViewGetCfgs(),
					'vars' 		=> (array) $arr
				], // this will het the full array with all page vars and configs
				(int) $this->fixPCacheTime($this->cache_time)
			); // save arr vars structure to pcache
			//--
			if($result_pcached === true) {
				$this->page_is_cached = true;
			} //end if
			//--
		} //end if
		//--
		if($this->IfDebug()) {
			$this->SetDebugData('Page ['.(string)$page_id.'] Pre-Render Data', $arr);
		} //end if
		//-- check if OK
		if($is_ok !== true) {
			return; // may be 404 or a different non 200 status code
		} //end if
		//-- check auth
		if($arr['auth'] > 0) { // if auth > 0, req. to be logged in
			if(\SmartAuth::check_login() !== true) {
				$this->PageViewSetErrorStatus(403, 'Authentication Required');
				return; // auth required
			} //end if
		} //end if
		//-- render the page
		$arr = (array) $this->doRenderPage($page_id, $arr);
		//--
		$this->PageViewSetVars((array)$arr);
		$arr = array(); // free mem
		//--

	} //END FUNCTION
	//=====


	//=====
	final public function getRenderedBuilderSegmentCode($segment_id) { // (OUTPUTS: HTML)

		// CHECK: $this->rendered_segments[]

		//--
		$this->max_depth = $this->fixRenderMaxDepth($this->max_depth);
		//--

		//--
		$this->crr_lang = (string) \SmartTextTranslations::getLanguage();
		if((string)$this->crr_lang == (string)\SmartTextTranslations::getDefaultLanguage()) {
			$this->crr_lang = ''; // fix to avoid query translations on default language
		} //end if
		//--

		//--
		$segment_id = (string) trim((string)$segment_id);
		//--
		if(((string)$segment_id == '') OR (substr((string)$segment_id, 0, 1) != '#')) {
			$this->PageViewSetErrorStatus(500, 'WARNING: Empty / Invalid PageBuilder Segment ID to Render ...');
			return '';
		} //end if
		//--

		//-- must test after seing if the segment ID is valid
		if($this->rendered_segments[(string)$segment_id] > 0) {
			\Smart::raise_error(
				'PageBuilder: The abstract controller '.__CLASS__.' dissalow rendering multiple times a segment ['.$segment_id.'] per controller'
			);
			die();
			return;
		} //end if
		//--
		$this->rendered_segments[(string)$segment_id]++; // flag: dissalow renders the same segment multiple times per controller
		//--

		//--
		$arr = array();
		//--
		$the_pcache_key = (string) $segment_id.'@'.\SmartTextTranslations::getLanguage(); // .'__d'.(int)$this->max_depth
		//--
		if($this->PageCacheisActive()) {
			//$arr = (array) $this->PageGetFromCache(
			$pcache_arr = (array) $this->PageGetFromCache(
				'smart-pg-builder',
				$this->PageCacheSafeKey((string)$the_pcache_key)
			); // get arr vars structure from pcache
			//print_r($pcache_arr); die();
			if(\Smart::array_size($pcache_arr) > 0) {
				if(is_array($pcache_arr['vars'])) { // if valid cache (test ... there must be the 3 sub-arrays as exported previous in pcache)
					$arr = (array) $pcache_arr['vars'];
					//print_r($arr);
					$this->segments_cached[(string)$segment_id]++;
				} //end if
			} //end if
			$pcache_arr = array();
		} //end if
		//--
		if($this->segments_cached[(string)$segment_id] <= 0) {
			$arr = (array) $this->loadSegmentOrPage((string)$segment_id, 'segment'); // get arr vars structure from db
		} //end if
		if(\Smart::array_size($arr) <= 0) {
			$arr = array();
		} //end if
		//--
		if(($this->segments_cached[(string)$segment_id] <= 0) AND ($this->PageCacheisActive())) {
			//--
			$result_pcached = (bool) $this->PageSetInCache(
				'smart-pg-builder',
				$this->PageCacheSafeKey((string)$the_pcache_key),
				[
					'vars' 		=> (array) $arr
				], // this will het the full array with all page vars and configs
				(int) $this->fixPCacheTime($this->cache_time)
			); // save arr vars structure to pcache
			//--
			if($result_pcached === true) {
				$this->segments_cached[(string)$segment_id]++;
			} //end if
			//--
		} //end if
		//--
		if($this->IfDebug()) {
			$this->SetDebugData('Segment ['.(string)$segment_id.'] Pre-Render Data', $arr);
		} //end if
		//-- chk err
		if((int)$this->PageViewGetStatusCode() >= 400) {
			if(in_array((int)$this->PageViewGetStatusCode(), (array)\SmartFrameworkRuntime::getHttpStatusCodesERR())) {
				return '';
			} //end if
		} //end if
		//-- render the page
		$arr = (array) $this->doRenderSegment($segment_id, $arr);
		//print_r($arr); die();
		//--
	/*	if((string)trim((string)$arr['code']) == '') {
			$this->PageViewSetErrorStatus(500, 'WARNING: Empty PageBuilder Segment Code to Render ...');
			return '';
		} //end if */
		//--
		return (string) $arr['code'];
		//--

	} //END FUNCTION
	//=====


	//===== !!! this feature have to be provided as separate since a segment cannot be rendered more than once per controller !!!
	final public function renderSegmentMarkers($segment_code, $markers) { // (OUTPUTS: HTML)
		//--
		if((\Smart::array_size($markers) > 0) AND (strpos((string)$segment_code, '{{=#') !== false)) { // if we provide express markers for replacing
			//--
			$segment_code = (string) str_replace( // Safe Fix: comment out any of: [####*####] [%%%%*%%%%] [@@@@*@@@@]
				[
					'[####',
					'####]',
					'[%%%%',
					'%%%%]',
					'[@@@@',
					'@@@@]'
				],
				[
					'(-####',
					'####-)',
					'(-%%%%',
					'%%%%-)',
					'(-@@@@',
					'@@@@-)'
				],
				(string) $segment_code
			);
			//--
			$segment_code = (string) str_replace( // Pre-Render: replace {{=#MARKER|escapings#=}} with [####MARKER|escapings####]
				[
					'{{=#',
					'#=}}'
				],
				[
					'[####',
					'####]'
				],
				(string) $segment_code
			);
			//--
			$segment_code = (string) \SmartMarkersTemplating::render_template((string)$segment_code, (array)$markers, 'yes');
			//--
		} //end if
		//--
		return (string) $segment_code;
		//--
	} //END FUNCTION
	//=====


	//=====
	final public function checkIfPageOrSegmentExist($y_id) {
		//--
		return (bool) \SmartModDataModel\PageBuilder\PageBuilderFrontend::checkIfPageOrSegmentExist((string)$y_id);
		//--
	} //END FUNCTION
	//=====


	//=====
	final public function getListOfSegmentsByArea($y_area) {
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getListOfSegmentsByArea((string)$y_area);
		//--
	} //END FUNCTION
	//=====


	//##### [ PRIVATES ] #####


	//=====
	private function fixRenderMaxDepth($maxdepth) {
		//--
		$maxdepth = (int) $maxdepth;
		if($maxdepth <= 0) {
			$maxdepth = 1;
		} elseif($maxdepth > 3) {
			$maxdepth = 3;
		} //end if
		//--
		return (int) $maxdepth;
		//--
	} //END FUNCTION
	//=====


	//=====
	private function fixPCacheTime($time) {
		//--
		$time = (int) $time;
		//--
		if($time < (60 * 1)) {
			$time = (int) 60 * 1; // min: 1 minute
		} elseif($time > (60 * 60 * 24 * 366)) {
			$time = (int) 60 * 60 * 24 * 366; // max: 366 days
		} //end if
		//--
		return (int) $time;
		//--
	} //END FUNCTION
	//=====


	//=====
	private function fixAllowedTemplateMarkers($markers) {
		//--
		if(!is_array($markers)) {
			$markers = array();
		} //end if
		//--
		if(\Smart::array_type_test($markers) != 1) { // must be non-associative array
			$markers = array();
		} //end if
		//--
		$tmp_arr = (array) $markers;
		$markers = [];
		//--
		for($i=0; $i<\Smart::array_size($tmp_arr); $i++) {
			$tmp_arr[$i] = (string) trim((string)$tmp_arr[$i]);
			if((string)$tmp_arr[$i] != '') {
				if(preg_match((string)$this->regex_marker, (string)$tmp_arr[$i])) {
					if(!in_array((string)$tmp_arr[$i], [ 'MAIN' ])) {
						$markers[] = 'TEMPLATE@'.$tmp_arr[$i];
					} //end if
				} //end if
			} //end if
		} //end foreach
		//--
		$markers = (array) \Smart::array_sort((array)$markers, 'sort');
		//--
		return (array) $markers;
		//--
	} //END FUNCTION
	//=====


	//=====
	// load settings segment
	private function loadSegmentSettingsOnly($id) {

		//--
		$id = (string) trim((string)$id);
		//--

		//--
		$arr = (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getSegment((string)$id, (string)$this->crr_lang);
		//--
		if((string)$arr['id'] == '') {
			\Smart::log_warning('PageBuilder: WARNING: (500) @ '.'Invalid Settings Segment: '.$id.' in Page: '.implode(';', $this->current_page)); // log warning, this is internal, by page settings
			return array();
		} //end if
		//--

		//--
		$yaml = (string) base64_decode((string)$arr['data']);
		//--
		if((string)$yaml != '') {
			$yaml = (array) (new \SmartYamlConverter())->parse((string)$yaml);
		} else {
			$yaml = array();
		} //end if
		//--
		if($this->debug === true) {
			if($this->IfDebug()) {
				$this->SetDebugData('Settings Segment ['.(string)$id.'] Runtime Data', $yaml);
			} //end if
		} //end if
		//--

		//-- fixes
		if(!is_array($yaml)) {
			$yaml = array();
		} //end if
		if(!is_array($yaml['SETTINGS'])) {
			$yaml['SETTINGS'] = array();
		} //end if
		//--

		//--
		return (array) $yaml['SETTINGS'];
		//--

	} //END FUNCTION
	//=====


	//=====
	// load a text value from YAML Data
	private function loadValue($id, $syntax, $arr) {

		//--
		$uid = (string) 'val@'.\Smart::uuid_10_num().'-'.sha1((string)print_r($arr,1));
		//--
		if((string)$syntax == 'html') {
			$syntax = 'html';
			$arr['mode'] = 'html';
			$arr['id'] = (string) trim((string)$arr['id']); // trim
			if((string)$arr['id'] != '') {
				$arr['id'] = (string) \SmartModExtLib\PageBuilder\Utils::fixSafeCode((string)$arr['id']); // {{{SYNC-PAGEBUILDER-HTML-SAFETY}}} avoid PHP code + cleanup XHTML tag style
			} //end if
		} elseif((string)$syntax == 'markdown') {
			$syntax = 'markdown';
			$arr['mode'] = 'markdown:rendered';
			$arr['id'] = (string) trim((string)$arr['id']); // trim
			if((string)$arr['id'] != '') {
				$arr['id'] = (string) \SmartModExtLib\PageBuilder\Utils::renderMarkdown((string)$arr['id']); // render as markdown
			} //end if
		} else {
			$syntax = 'text';
			$arr['mode'] = 'text:rendered';
			$arr['id'] = (string) trim((string)$arr['id']); // trim
			if((string)$arr['id'] != '') {
				$arr['id'] = (string) \Smart::escape_html((string)$arr['id']); // escape text to HTML
			} //end if
		} //end if else
		//--
		$out_arr = [
			'id' 	=> (string) $uid,
			'type' 	=> 'value', // preserve type
			'auth' 	=> 0, // n/a
			'mode' 	=> (string) $arr['mode'],
			'name' 	=> (string) $id.' :: '.strtoupper((string)$syntax).' :: '.$uid,
			'code' 	=> (string) $arr['id']
		];
		//--

		//--
		return (array) $out_arr;
		//--

	} //END FUNCTION
	//=====


	//=====
	// load page or segment ; page is level -1 ; segment is higher level
	// the execution of this method is pcached thus it never returns to re-render if pcached
	private function loadSegmentOrPage($id, $type, $level=-1) {

		//--
		$this->recursion_control = (int) max($this->recursion_control, $level);
		//--
		if((int)$level >= (int)$this->max_depth) { // fix: needs >= instead of > to comply with page/sub/sub
			\Smart::raise_error(
				'PageBuilder: The maximum Page Recursion Level overflow on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] # Level: '.(int)$level.' of max '.(int)$this->max_depth,
				'Too much recursion detected for this Page'
			);
			die();
			return array();
		} //end if
		//--
		$level = (int) $level + 1;
		//--

		//--
		$id = (string) trim((string)$id);
		//--

		//--
		$data_arr = array();
		//--
		$data_arr['id'] = (string) $id;
		//--

		//--
		switch((string)$type) {
			case 'page':
				$this->current_page[] = (string)$id;
				break;
			case 'segment':
				break;
			default:
				\Smart::raise_error(
					'PageBuilder: Invalid Page Load Type on Page/Segment: ['.(string)$id.'] # Type: '.$type,
					'Invalid Page Load Type'
				);
				die();
				return array();
		} //end if
		//--
		$data_arr['type'] = (string) $type;
		//--

		//--
		$is_settings_segment = false;
		//--
		if((string)$type == 'segment') {
			//--
			$arr = (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getSegment((string)$id, (string)$this->crr_lang);
			//--
			if((string)$arr['id'] == '') {
				$this->PageViewSetErrorStatus(500, 'Invalid PageBuilder Page Segment');
				\Smart::log_warning('PageBuilder: WARNING: (500) @ '.'Invalid Segment: '.$id.' in Page: '.implode(';', $this->current_page)); // log warning, this is internal, by page settings
				return (array) $data_arr;
			} //end if
			//--
			$data_arr['auth'] = 0;
			//--
			if((string)$arr['mode'] == 'settings') {
				$is_settings_segment = true;
			} //end if
			//--
		} else { // page
			//--
			$arr = (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getPage((string)$id, (string)$this->crr_lang);
			//--
			if((string)$arr['id'] == '') {
				$this->PageViewSetErrorStatus(404, 'Invalid PageBuilder Page');
				// log no warning as this is external, by request
				return (array) $data_arr;
			} //end if
			//--
			$this->auth_required += (int) $arr['auth'];
			$data_arr['auth'] = (int) $this->auth_required;
			//--
		} //end if
		//--

		//--
		$data_arr['mode']  = (string) $arr['mode'];
		$data_arr['name'] = (string) $arr['name'];
		//--
		if($is_settings_segment === true) {
			//--
			$data_arr['layout'] = '';
			//--
			$data_arr['code'] = '';
			//--
		} else {
			//--
			if((string)$type == 'segment') {
				$data_arr['layout'] = '';
			} else {
				$data_arr['layout'] = (string) $arr['layout']; // no html escape on this as it is a file
			} //end if else
			//--
			$data_arr['code'] = (string) base64_decode((string)$arr['code']);
			if((string)$data_arr['mode'] == 'raw') { // FIX: RAW Pages might have the code empty if need to output from a plugin and to avoid inject spaces ...
				if((string)trim((string)$data_arr['code']) == '') {
					if((string)$type == 'segment') {
						$data_arr['code'] = '';
					} else { // a raw page cannot be blank at all
						$data_arr['code'] = '{{:RAW:}}'; // otherwise a raw page can have html/text with markers as normal pages
					} //end if else
				} else {
					if((string)$type == 'segment') {
						$data_arr['code'] = (string) \Smart::escape_html((string)$data_arr['code']); // raw segment is text
					} //end if else
				} //end if
			} elseif((string)$data_arr['mode'] == 'text') {
				//Smart::log_warning('rendering text on ID='.$arr['id']);
				$data_arr['mode'] = 'text:rendered';
				if((string)trim((string)$data_arr['code']) != '') {
					$data_arr['code'] = (string) \Smart::escape_html((string)$data_arr['code']);
				} //end if
			} elseif((string)$data_arr['mode'] == 'markdown') {
				//Smart::log_warning('rendering markdown on ID='.$arr['id']);
				$data_arr['mode'] = 'markdown:rendered';
				if((string)trim((string)$data_arr['code']) != '') {
					$data_arr['code'] = (string) \SmartModExtLib\PageBuilder\Utils::renderMarkdown((string)$data_arr['code']);
				} //end if
			} elseif((string)$data_arr['mode'] == 'html') {
				$data_arr['mode'] = 'html:safe';
				if((string)trim((string)$data_arr['code']) != '') {
					$data_arr['code'] = (string) \SmartModExtLib\PageBuilder\Utils::fixSafeCode((string)$data_arr['code']); // {{{SYNC-PAGEBUILDER-HTML-SAFETY}}} avoid PHP code + cleanup XHTML tag style
				} //end if
			} //end if
			//--
		} //end if else
		//--

		//--
		$yaml = (string) base64_decode((string)$arr['data']);
		//--
		if((string)trim((string)$yaml) != '') {
			$yaml = (array) (new \SmartYamlConverter())->parse((string)$yaml);
		} else {
			$yaml = array();
		} //end if
		//--
		if($this->debug === true) {
			if($this->IfDebug()) {
				$this->SetDebugData('Page / Segment ['.(string)$id.'] Runtime Data', $yaml);
			} //end if
		} //end if
		//--

		//-- pre-parse
		$preparse_arr = [];
		if(is_array($yaml['RENDER'])) {
			foreach((array)$yaml['RENDER'] as $key => $val) {
				$key = (string) strtoupper((string)trim((string)$key));
				if(((string)$key != '') AND (\Smart::array_size($val) > 0)) {
					$preparse_arr[(string)$key] = [];
					foreach((array)$val as $k => $v) {
						$k = (string) trim((string)$k);
						if((strpos((string)$k, 'content') === 0) AND (\Smart::array_size($v) > 0)) {
							if(((string)$v['type'] === 'value') OR ((string)$v['type'] === 'segment') OR ((string)$v['type'] === 'plugin')) {
								$preparse_arr[(string)$key][] = [(string)$k => $v];
							} else {
								\Smart::raise_error(
									'PageBuilder: Invalid Data Structure (1.2) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key.'/'.(string)$k,
									'Invalid Data Structure detected for this Page'
								);
								die();
								return array();
							} //end if
						} else {
							\Smart::raise_error(
								'PageBuilder: Invalid Data Structure (1.1) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key.'/'.(string)$k,
								'Invalid Data Structure detected for this Page'
							);
							die();
							return array();
						} //end if
					} //end foreach
				} else {
					\Smart::raise_error(
						'PageBuilder: Invalid Data Structure (1.0) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key,
						'Invalid Data Structure detected for this Page'
					);
					die();
					return array();
				} //end if
			} //end foreach
		} //end if
		//--
		$props_arr = [];
		if((string)$type == 'segment') {
			//--
			unset($data_arr['layout']);
			//--
		} elseif((string)$data_arr['mode'] == 'raw') { // {{{SYNC-PAGEBUILDER-RAWPAGE-SAFETY}}}
			//--
			unset($data_arr['layout']);
			//--
			$props_arr['rawmime'] = ''; // default to: text/html (protected against PHP code injection)
			$props_arr['rawdisp'] = ''; // default to: inline
			//--
			if(is_array($yaml['PROPS'])) { // PROPS [ FileName, Disposition ]
				//--
				$tmp_arr_props = (array) array_change_key_case((array)$yaml['PROPS'], CASE_LOWER);
				//--
				if((string)$tmp_arr_props['filename'] != '') {
					//--
					$mime_type = (array) \SmartFileSysUtils::mime_eval((string)$tmp_arr_props['filename'], (string)$tmp_arr_props['disposition']);
					$mime_disp = (string) $mime_type[1];
					$mime_type = (string) $mime_type[0];
					//--
					switch((string)$mime_type) { // for RAW Pages allow only certain mime types
						case 'text/html':
							$props_arr['rawmime'] = ''; // default to: text/html (protected against PHP code injection)
							$props_arr['rawdisp'] = ''; // default to: inline
							break;
						case 'text/css':
						case 'application/javascript':
						case 'application/json':
						case 'application/xml':
						case 'text/plain':
						case 'image/svg+xml':
						case 'message/rfc822':
						case 'text/calendar':
						case 'text/x-vcard':
						case 'text/x-vcalendar':
						case 'text/ldif':
						case 'application/pgp-signature':
						case 'text/csv':
							$props_arr['rawmime'] = (string) $mime_type;
							$props_arr['rawdisp'] = (string) $mime_disp;
							break;
						default: // force
							$props_arr['rawmime'] = 'text/plain';
							$props_arr['rawdisp'] = 'inline';
					} //end switch
					//--
					$mime_type = null; // free mem
					$mime_disp = null; // free mem
					//--
				} //end if
				//--
			} //end if
			//--
			$data_arr['props'] = (array) $props_arr;
			//--
		} //end if
		//--

		//-- parse
		$data_arr['render'] = [];
		//--
		foreach($preparse_arr as $key => $val) {
			//--
			$arr_item = [];
			//--
			$key = (string) trim((string)$key);
			//--
			if(((string)$key != '') AND (\Smart::array_type_test($val) == 1)) {
				//--
				for($i=0; $i<\Smart::array_size($val); $i++) {
					//--
					if(is_array($val[$i])) {
						//--
						foreach($val[$i] as $k => $v) {
							//--
							if(\Smart::array_size($v) > 0) {
								//--
								$v['id'] = (string) trim((string)$v['id']);
								//--
								if((string)$v['id'] != '') { // must have a valid ID, the type[plugin/segment] is tested in pre-parse phase
									//--
									$arr_tmp_item = [
										'type' 		=> (string) $v['type'],
										'id' 		=> (string) $v['id']
									];
									//--
									if((string)$v['type'] == 'value') {
										//--
										$arr_tmp_item = (array) $this->loadValue((string)$id, (string)$v['config'], (array)$arr_tmp_item);
										//--
									} elseif((string)$v['type'] == 'segment') {
										//--
										$arr_tmp_item['id'] = '#'.$arr_tmp_item['id'];
										//--
										if((string)$arr_tmp_item['id'] == (string)$id) {
											\Smart::raise_error(
												'PageBuilder: Page Self Circular Reference detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for referenced segment: '.$arr_tmp_item['id'],
												'Circular self reference detected for this Page'
											);
											die();
											return array();
										} //end if
										//--
										$arr_tmp_item = (array) $this->loadSegmentOrPage((string)$arr_tmp_item['id'], 'segment', $level);
										//--
									} elseif((string)$v['type'] == 'plugin') {
										//-- config is available just for plugin
										if(is_array($v['config'])) {
											$arr_tmp_item['config'] = (array) $v['config'];
										} elseif((string)$v['config'] != '') {
											$arr_tmp_item['config:settings-segment'] = (string)'#'.$v['config'];
											$arr_tmp_item['config'] = (array) $this->loadSegmentSettingsOnly((string)'#'.$v['config']);
										} else {
											$arr_tmp_item['config'] = array();
										} //end if else
										//--
									} else {
										//--
										\Smart::raise_error(
											'PageBuilder: Unknown Data Type ('.(string)$v['type'].') in Runtime detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.']',
											'Unknown Data Type in Runtime detected for this Page'
										);
										die();
										return array();
										//--
									} //end if
									//--
									$arr_item[] = (array) $arr_tmp_item;
									//--
									$arr_tmp_item = [];
									//--
								} else {
									//--
									\Smart::raise_error(
										'PageBuilder: Invalid Data Structure (2.3) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key.'/'.(string)$k,
										'Invalid Data Structure detected for this Page'
									);
									die();
									return array();
									//--
								} //end if
								//--
							} else {
								//--
								\Smart::raise_error(
									'PageBuilder: Invalid Data Structure (2.2) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key.'/'.(string)$k,
									'Invalid Data Structure detected for this Page'
								);
								die();
								return array();
								//--
							} //end if
							//--
						} //end foreach
						//--
					} else {
						//--
						\Smart::raise_error(
							'PageBuilder: Invalid Data Structure (2.1) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key,
							'Invalid Data Structure detected for this Page'
						);
						die();
						return array();
						//--
					} //end if else
					//--
				} //end for
				//--
			} else {
				//--
				\Smart::raise_error(
					'PageBuilder: Invalid Data Structure (2.0) detected on Page/Segment: ['.implode(';', $this->current_page).'/'.(string)$id.'] for key: '.(string)$key,
					'Invalid Data Structure detected for this Page'
				);
				die();
				return array();
				//--
			} //end if
			//--
			if(\Smart::array_size($arr_item) > 0) {
				//--
				$data_arr['render'][(string)$key] = (array) $arr_item;
				//--
			} //end if
			//--
		} //end foreach
		//--

		//-- cleanup
		/*
		unset($preparse_arr);
		unset($arr_item);
		unset($arr_tmp_item);
		unset($key);
		unset($val);
		unset($k);
		unset($v);
		unset($i);
		*/
		//--

		//--
		return (array) $data_arr;
		//--

	} //END FUNCTION
	//=====


	//=====
	private function doRenderPage($id, $data_arr) {

		//--
		return (array) $this->doRenderObject($id, $data_arr, -1); // pages MUST START AT -1 !!! {{{SYNC-PAGEBUILDER-RENDER-LEVELS}}}
		//--

	} //END FUNCTION
	//=====


	//=====
	private function doRenderSegment($id, $data_arr) {

		//--
		return (array) $this->doRenderObject($id, $data_arr, 0); // segments MUST START AT 0 !!! {{{SYNC-PAGEBUILDER-RENDER-LEVELS}}}
		//--

	} //END FUNCTION
	//=====


	//=====
	private function doRenderObject($id, $data_arr, $level) {

		// TODO: ? escape for markers: js, html ... ?

		//--
		$level = (int) ((int)$level + 1); // must increment at start (pages default start at: -1 ; segments default start at : 0) {{{SYNC-PAGEBUILDER-RENDER-LEVELS}}}
		//--

		//--
		if($level === 0) {
			if(\SmartModExtLib\PageBuilder\Utils::allowPages() !== true) {
				$this->PageViewSetErrorStatus(503, 'PageBuilder: Page Objects are Disabled ... Only Segments are Allowed');
				return array();
			} //end if
		} //end if
		//--

		//--
		if((int)$this->PageViewGetStatusCode() >= 400) {
			return array(); // skip on first err to preserve the last status code
		} //end if
		//--

		//--
		if(!is_array($data_arr)) {
			$this->PageViewSetErrorStatus(500, 'PageBuilder: Empty Page Data Format on Page/Segment');
			\Smart::log_warning('PageBuilder: Empty Page Data Format on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
			return array();
		} //end if
		//--
		if(!is_array($data_arr['render'])) {
			$this->PageViewSetErrorStatus(500, 'Invalid Page Render Data on Page/Segment');
			\Smart::log_warning('PageBuilder: Invalid Page Render Data on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
			return array();
		} //end if
		//--

		//--
		$is_raw_page = false;
		//--
		if((string)$data_arr['mode'] == 'settings') {
			//--
			$data_arr['code'] = ''; // clear ; this is n/a on a settings page
			//--
		} elseif((string)$data_arr['mode'] == 'raw') {
			//--
			if($level === 0) { // {{{SYNC-PAGEBUILDER-RENDER-LEVELS}}} (Level Zero is just for Pages, not for segments) ;
				//--
				$is_raw_page = true; // {{{SYNC-PAGEBUILDER-RAWPAGE-SAFETY}}}
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				if(\Smart::array_size($data_arr['props']) > 0) { // for RAW Page this is mandatory
					//--
					if((string)$data_arr['props']['rawmime'] != '') {
						$this->PageViewSetCfg('rawmime', (string)$data_arr['props']['rawmime']);
					} else { // text/html : to avoid security risk, escape all PHP code
						$data_arr['code'] = (string) \SmartModExtLib\PageBuilder\Utils::fixSafeCode((string)$data_arr['code']); // {{{SYNC-PAGEBUILDER-HTML-SAFETY}}} avoid PHP code + cleanup XHTML tag style
						$data_arr['props']['rawdisp'] = ''; // in this case do not use ...
					} //end if
					//--
					if((string)$data_arr['props']['rawdisp'] != '') {
						$this->PageViewSetCfg('rawdisp', (string)$data_arr['props']['rawdisp']);
					} //end if
					//--
				} else {
					$this->PageViewSetErrorStatus(500, 'Invalid Raw Page Data Props on Page/Segment');
					\Smart::log_warning('PageBuilder: Invalid Raw Page Data Props on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
					return array();
				} //end if
				//--
			} else { // do not allow RAW Page at higher levels than zero
				//--
				$this->PageViewSetErrorStatus(500, 'Invalid Raw Page/Segment at Level ['.$level.']');
				\Smart::log_warning('PageBuilder: Invalid Raw Page/Segment at Level ['.$level.']: '.(string)$id.' ; Level: '.(int)$level);
				return array();
				//--
			} //end if
			//--
		} //end if
		//--

		//--
		if($level === 0) { // {{{SYNC-PAGEBUILDER-RENDER-LEVELS}}} (Level Zero is just for Pages, not for segments)
			//--
			if($is_raw_page === true) {
				$data_arr['smart-markers'] = [
					'MAIN' 				=> ''
				];
			} else {
				if((string)trim((string)$data_arr['layout']) != '') {
					$this->PageViewSetCfg('template-file', (string)$data_arr['layout']);
				} //end if
				$data_arr['smart-markers'] = [
					'MAIN' 				=> '',
					'TITLE' 			=> '',
					'META-DESCRIPTION' 	=> '',
					'META-KEYWORDS' 	=> ''
				];
			} //end if else
			//--
		} else {
			//--
			if(substr((string)$data_arr['id'], 0, 1) != '#') { // on levels 1+ allow just segments !!!
				$this->PageViewSetErrorStatus(500, 'Invalid Segment to Render on Level: '.(int)$level);
				\Smart::log_warning('PageBuilder: Invalid Segment to Render on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
				return array();
			} //end if
			//--
		} //end if
		//--

	//	print_r($data_arr); die(); // aaa

		//--
		$arr_replacements = [];
		//--
		foreach($data_arr['render'] as $key => $val) {
			//--
			if(\Smart::array_type_test($val) == 1) {
				//--
				for($i=0; $i<\Smart::array_size($val); $i++) {
					//--
					$plugin_obj 			= null; // reset each cycle
					$plugin_raw_heads 		= null; // reset each cycle
					$plugin_page_settings 	= null; // reset each cycle
					$plugin_exec 			= null; // reset each cycle
					$plugin_status 			= null; // reset each cycle
					//--
					if(((string)$key != '') AND (preg_match((string)$this->regex_tpl_marker, (string)$key))) {
						//--
						if((string)$val[$i]['type'] == 'plugin') { // INFO: each template must provide it's content (already cached or not) and the pcache key suffixes
							//--
							$plugin_id 		= (string) $val[$i]['id'];
							$plugin_cfg 	= (array)  $val[$i]['config'];
							//--
							$plugin_part_d = (string) trim((string)\Smart::safe_filename((string)\Smart::dir_name((string)$plugin_id)));
							$plugin_part_f = (string) trim((string)\Smart::safe_filename((string)\Smart::base_name((string)$plugin_id)));
							//--
							$plugin_path = '';
							$plugin_class = '';
							//--
							if(((string)$plugin_part_d != '') AND ((string)$plugin_part_f != '')) {
								//--
								$plugin_path 	= (string) \Smart::safe_pathname((string)'modules/mod-'.$plugin_part_d.'/plugins/'.$plugin_part_f.'.php');
								$plugin_class 	= (string) 'PageBuilderFrontendPlugin'.\SmartModExtLib\PageBuilder\Utils::composePluginClassName($plugin_part_d).\SmartModExtLib\PageBuilder\Utils::composePluginClassName($plugin_part_f);
								//--
								if(((string)$plugin_path != '') AND (\SmartFileSysUtils::check_if_safe_path((string)$plugin_path)) AND (\SmartFileSystem::is_type_file((string)$plugin_path))) {
									//--
									require_once((string)$plugin_path);
									//--
									if(((string)$plugin_class != 'PageBuilderFrontendPlugin') AND (class_exists((string)$plugin_class))) {
										//--
										if(is_subclass_of((string)$plugin_class, '\\SmartModExtLib\\PageBuilder\\AbstractFrontendPlugin')) {
											//--
											$plugin_obj = new $plugin_class('index', $this->ControllerGetParam('module-path'), $this->ControllerGetParam('url-script'), $this->ControllerGetParam('url-path'), $this->ControllerGetParam('url-addr'), $this->ControllerGetParam('url-page'), $this->ControllerGetParam('controller'));
											$plugin_obj->initPlugin((array)$plugin_cfg); // initialize before run !
											//--
											$plugin_obj->Initialize(); // pre-run
											$plugin_status = (int) $plugin_obj->Run(); // run
											$plugin_obj->ShutDown(); // post run
											//--
											$plugin_raw_heads = (array) $plugin_obj->PageViewGetRawHeaders();
											if(\Smart::array_size($plugin_raw_heads) > 0) {
												$this->PageViewSetRawHeaders((array)$plugin_raw_heads);
											} //end if
											//--
											$plugin_page_settings = (array) $plugin_obj->PageViewGetCfgs();
											//--
											$plugin_exec = (array) $plugin_obj->PageViewGetVars();
											//Smart::log_notice(print_r($plugin_exec,1));
											//--
											if(array_key_exists('status-code', $plugin_page_settings)) {
												$plugin_page_settings['status-code'] = (int) $plugin_page_settings['status-code']; // this rewrites what the Run() function returns, which is very OK as this is authoritative !
												if(!in_array((int)$plugin_page_settings['status-code'], (array)\SmartFrameworkRuntime::getHttpStatusCodesALL())) {
													\Smart::log_notice('PageBuilder: Render Template ERROR: Wrong HTTP Status Code (Set='.(int)$plugin_page_settings['status-code'].') in: ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.'PLUGIN'.') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
													$plugin_page_settings['status-code'] = 200;
												} //end if
											} else {
												$plugin_page_settings['status-code'] = 200;
												if((int)$plugin_status > 0) {
													if(!in_array((int)$plugin_status, (array)\SmartFrameworkRuntime::getHttpStatusCodesALL())) {
														\Smart::log_notice('PageBuilder: Render Template ERROR: Wrong HTTP Status Code (Return='.(int)$plugin_status.') in: ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.'PLUGIN'.') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
													} else {
														$plugin_page_settings['status-code'] = (int) $plugin_status;
													} //end if
												} //end if
											} //end if
											if((int)$plugin_page_settings['status-code'] >= 400) {
												//--
												if((int)$this->PageViewGetStatusCode() < (int)$plugin_page_settings['status-code']) {
													$this->PageViewSetErrorStatus((int)$plugin_page_settings['status-code'], (string)$plugin_page_settings['error']);
												} //end if
												//--
												$plugin_exec['meta-title'] = ''; 		// reset
												$plugin_exec['meta-description'] = ''; 	// reset
												$plugin_exec['meta-keywords'] = ''; 	// reset
												$plugin_exec['content'] = ''; 			// reset
												//--
											} elseif(((int)$plugin_page_settings['status-code'] == 301) OR ((int)$plugin_page_settings['status-code'] == 302)) {
												//--
												if((string)$plugin_page_settings['redirect-url'] != '') {
													//--
													if((int)$this->PageViewGetStatusCode() < (int)$plugin_page_settings['status-code']) {
														$this->PageViewSetRedirectUrl((string)$plugin_page_settings['redirect-url'], (int)$plugin_page_settings['status-code']);
													} //end if
													//--
												} //end if
												//--
											} else { // 2xx
												//--
												if((int)$this->PageViewGetStatusCode() < (int)$plugin_page_settings['status-code']) {
													$this->PageViewSetOkStatus((int)$plugin_page_settings['status-code']);
												} //end if
												//--
											} //end if
											//-- rawpage, rawmime, rawdisp
											if(isset($plugin_page_settings['rawpage'])) {
												$plugin_page_settings['rawpage'] = (string) strtolower((string)$plugin_page_settings['rawpage']);
												if((string)$plugin_page_settings['rawpage'] == 'yes') {
													$this->PageViewSetCfg('rawpage', true);
												} //end if
											} //end if
											if((string)$plugin_page_settings['rawpage'] != 'yes') {
												$plugin_page_settings['rawpage'] = '';
											} //end if
											if((string)$plugin_page_settings['rawpage'] == 'yes') {
												if(isset($plugin_page_settings['rawmime'])) {
													$plugin_page_settings['rawmime'] = (string) trim((string)$plugin_page_settings['rawmime']);
													if((string)$plugin_page_settings['rawmime'] != '') {
														$this->PageViewSetCfg('rawmime', (string)$plugin_page_settings['rawmime']);
													} //end if
												} //end if else
											} //end if
											if((string)$plugin_page_settings['rawpage'] == 'yes') {
												if(isset($plugin_page_settings['rawdisp'])) {
													$plugin_page_settings['rawdisp'] = (string) trim((string)$plugin_page_settings['rawdisp']);
													if((string)$plugin_page_settings['rawdisp'] != '') {
														$this->PageViewSetCfg('rawdisp', (string)$plugin_page_settings['rawdisp']);
													} //end if
												} //end if else
											} //end if
											//-- expires, modified
											if((int)$plugin_page_settings['expires'] > 0) {
												$this->PageViewSetCfg('expires', (int)$plugin_page_settings['expires']);
												$this->PageViewSetCfg('modified', (int)$plugin_page_settings['modified']);
											} //end if
											//--
											if((string)$plugin_exec['meta-title'] != '') {
												$data_arr['@meta-title'] = (string) $plugin_exec['meta-title'];
											} //end if
											if((string)$plugin_exec['meta-description'] != '') {
												$data_arr['@meta-description'] = (string) $plugin_exec['meta-description'];
											} //end if
											if((string)$plugin_exec['meta-keywords'] != '') {
												$data_arr['@meta-keywords'] = (string) $plugin_exec['meta-keywords'];
											} //end if
											//--
											if(($level === 0) AND (strpos((string)$key, 'TEMPLATE@') === 0) AND (in_array((string)$key, (array)$this->page_markers))) { // ((string)$key != 'TEMPLATE@MAIN')) { // allow TEMPLATE@*(!MAIN) just on main page (level=0)
												//-- don't replace these markers, they are template markers
												$data_arr['smart-markers'][(string)substr((string)$key, strlen('TEMPLATE@'))] .= (string) $plugin_exec['content']; // append is mandatory here else will not render correctly more than one sub-segment/plugin
												//--
											} elseif(preg_match((string)$this->regex_marker, (string)$key)) {
												//--
												if(strpos((string)$data_arr['code'], '{{:'.(string)$key) !== false) {
													//-- replace these markers, they are page markers
													$arr_replacements['{{:'.(string)$key.':}}'] .= (string) $plugin_exec['content']; // OK: always append
													//--
												} else {
													//--
													\Smart::log_notice('PageBuilder: Render Template ERROR: Unused Render Marker: ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.'PLUGIN'.') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
													//--
												} //end if
												//--
											} else {
												//--
												$this->PageViewSetErrorStatus(500, 'PageBuilder: Render Template ERROR: Invalid Render Marker (3)');
												\Smart::log_warning('PageBuilder: Render Template ERROR: Invalid Render Marker (3): ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.'PLUGIN'.') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
												//--
											} //end if else
										} else {
											//--
											$this->PageViewSetErrorStatus(500, 'Plugin Class is Invalid');
											\Smart::log_warning('PageBuilder: Plugin Class is Invalid ['.$plugin_id.']: '.$plugin_class);
											//--
										} //end if else
										//--
									} else {
										//--
										$this->PageViewSetErrorStatus(500, 'PageBuilder: Plugin Class is Missing');
										\Smart::log_warning('PageBuilder: Plugin Class is Missing ['.$plugin_id.']: '.$plugin_class);
										//--
									} //end if else
								} else {
									//--
									$this->PageViewSetErrorStatus(500, 'PageBuilder: Plugin is Missing');
									\Smart::log_warning('PageBuilder: Plugin is Missing ['.$plugin_id.']: '.$plugin_path);
									//--
								} //end if else
							} else {
								//--
								$this->PageViewSetErrorStatus(500, 'PageBuilder: Invalid Plugin');
								\Smart::log_warning('PageBuilder: Invalid Plugin: '.$plugin_id);
								//--
							} //end if else
							//--
						} else { // page / segment
							//--
							if(is_array($val[$i]['render'])) {
								$val[$i] = (array) $this->doRenderObject($id, $val[$i], $level);
							} //end if
							//--
							if((string)$val[$i]['mode'] == 'settings') {
								//--
								$this->PageViewSetErrorStatus(500, 'PageBuilder: Render Template ERROR: Settings Segment Pages cannot be used for rendering context');
								\Smart::log_warning('PageBuilder: Render Template ERROR: Settings Segment Pages cannot be used for rendering context: ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.$val[$i]['mode'].') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
								//--
							} else {
								//--
								if(($level === 0) AND (strpos((string)$key, 'TEMPLATE@') === 0) AND (in_array((string)$key, (array)$this->page_markers))) { // ((string)$key != 'TEMPLATE@MAIN')) { // allow TEMPLATE@*(!MAIN) just on main page (level=0)
									//-- don't replace these markers, they are template markers
									$data_arr['smart-markers'][(string)substr((string)$key, strlen('TEMPLATE@'))] .= (string) $val[$i]['code']; // append is mandatory here else will not render correctly more than one sub-segment/plugin
									//--
								} elseif(preg_match((string)$this->regex_marker, (string)$key)) {
									//--
									if(strpos((string)$data_arr['code'], '{{:'.(string)$key) !== false) {
										//-- replace these markers, they are page markers
										//$arr_replacements['{{:'.(string)$key.':}}'] .= '<!-- Segment['.(int)$i.']: '.Smart::escape_html((string)$key).' -->';
										$arr_replacements['{{:'.(string)$key.':}}'] .= (string) $val[$i]['code']; // OK: always append
										//$arr_replacements['{{:'.(string)$key.':}}'] .= '<!-- /Segment['.(int)$i.']: '.Smart::escape_html((string)$key).' -->';
										//--
									} else {
										//--
										\Smart::log_notice('PageBuilder: Render Template ERROR: Unused Render Marker: ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.$val[$i]['mode'].') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
										//--
									} //end if
									//--
								} else {
									//--
									$this->PageViewSetErrorStatus(500, 'PageBuilder: Render Template ERROR: Invalid Render Marker (2)');
									\Smart::log_warning('PageBuilder: Render Template ERROR: Invalid Render Marker (2): ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.$val[$i]['mode'].') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
									//--
								} //end if else
								//--
							} //end if else
							//--
						} //end if else
						//--
					} else {
						//--
						$this->PageViewSetErrorStatus(500, 'PageBuilder: Render Template ERROR: Invalid Render Marker (1)');
						\Smart::log_warning('PageBuilder: Render Template ERROR: Invalid Render Marker (1): ['.(string)$key.'] @ '.(string)$data_arr['id'].'/'.(string)$val[$i]['id'].' ('.(string)$val[$i]['type'].'/'.$val[$i]['mode'].') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
						//--
					} //end if else
					//--
				} //end for
				//--
			} else {
				//--
				$this->PageViewSetErrorStatus(500, 'PageBuilder: Render Template ERROR: Invalid Render Data Type');
				\Smart::log_warning('PageBuilder: Render Template ERROR: Invalid Render Data Type: ['.(string)$key.' @ '.(string)$data_arr['id'].' ('.(string)$val[$i]['type'].'/'.$val[$i]['mode'].') on Page/Segment: '.(string)$id.' ; Level: '.(int)$level);
				//--
			} //end if
			//--
		} //end foreach
		//--
		if(\Smart::array_size($arr_replacements) > 0) {
			$data_arr['code'] = (string) strtr((string)$data_arr['code'], (array)$arr_replacements); // since strtr treats strings as a sequence of bytes, and since UTF-8 and other multibyte encodings use - by definition - more than one byte for at least some characters, the unicode strings is likely to have problems. Fix: use the associative array as 2nd param to specify the mapping instead of using it with 3 params ; using strtr() for str replace with no recursion instead of str_replace() which goes with recursion over already replaced parts and is not safe in this context
		} //end if
		//--

		//--
		unset($arr_replacements);
		unset($data_arr['render']);
		//--
		if($level === 0) {
			//--
			$data_arr['smart-markers']['MAIN'] = (string) $data_arr['code'];
			unset($data_arr['code']);
			//--
		} //end if
		//--

		//-- manage meta from plugins
		if(in_array('TITLE', (array)$this->page_markers)) {
			if((string)$data_arr['@meta-title'] != '') {
				$data_arr['smart-markers']['TITLE'] = (string) $data_arr['@meta-title'];
			} //end if
		} //end if
		unset($data_arr['@meta-title']);
		//--
		if(in_array('META-DESCRIPTION', (array)$this->page_markers)) {
			if((string)$data_arr['@meta-description'] != '') {
				$data_arr['smart-markers']['META-DESCRIPTION'] = (string) $data_arr['@meta-description'];
			} //end if
		} //end if
		unset($data_arr['@meta-description']);
		//--
		if(in_array('META-KEYWORDS', (array)$this->page_markers)) {
			if((string)$data_arr['@meta-keywords'] != '') {
				$data_arr['smart-markers']['META-KEYWORDS'] = (string) $data_arr['@meta-keywords'];
			} //end if
		} //end if
		unset($data_arr['@meta-keywords']);
		//--

		//--
		if($level === 0) {
			return (array) $data_arr['smart-markers'];
		} else {
			return (array) $data_arr;
		} //end if else
		//--

	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>