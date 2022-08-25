<?php
// Class: \SmartModExtLib\Docs\OptimizationUtils
// (c) 2006-2021 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

namespace SmartModExtLib\Docs;

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
 * Class: Optimization Utils :: DevDocs
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20220331
 * @package 	Docs
 *
 */
final class OptimizationUtils {

	// ::

	public const THE_HDOCS_PATH = 'wpub/devdocs-html/';
	public const THE_DOCS_PATH = 'wpub/devdocs/';
	public const THE_DOCS_IDX_FILE = 'index.json';
	public const THE_DOCS_FILE = 'db.json';
	public const THE_DOCS_OPT_FILE = 'db.optimized.json'; // {{{SYNC-MOD-DOCS-OPT-FILE}}}
	public const THE_DOCS_MD_FILE = 'db-md.json'; // {{{SYNC-MOD-DOCS-MARKDOWN-FILE}}}

	private static $dbIndexes = [];


	private static function isDbIndexesInitialized(?string $realm) {
		//--
		$init_log = false;
		if((!\is_array(self::$dbIndexes)) OR (!\array_key_exists((string)$realm, (array)self::$dbIndexes)) OR (!\is_array(self::$dbIndexes[(string)$realm]))) {
			$init_log = true;
		} //end if
		//--
		return (bool) $init_log;
		//--
	} //END FUNCTION


	private static function getDbIndexes(?string $realm) {
		//--
		$realm = (string) \trim((string)$realm);
		if((string)$realm == '') {
			\Smart::log_warning(__METHOD__.' # Empty Indexes DB Realm');
			return [];
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$realm)) {
			\Smart::log_warning(__METHOD__.' # Invalid Indexes DB Realm (Unsafe): `'.$realm.'`');
			return [];
		} //end if
		//--
		if(!\is_array(self::$dbIndexes)) {
			self::$dbIndexes = [];
		} //end if
		if((\array_key_exists((string)$realm, (array)self::$dbIndexes)) AND (\is_array(self::$dbIndexes[(string)$realm]))) {
			return (array) self::$dbIndexes[(string)$realm];
		} //end if
		//--
		self::$dbIndexes[(string)$realm] = [];
		//--
		$indexdb = (string) \SmartFileSysUtils::add_dir_last_slash(self::THE_DOCS_PATH.$realm).\Smart::safe_filename((string)self::THE_DOCS_IDX_FILE);
		if(!\SmartFileSysUtils::check_if_safe_path((string)$indexdb)) {
			\Smart::log_warning(__METHOD__.' # Invalid Indexes DB Path (Unsafe): `'.$indexdb.'`');
			return [];
		} //end if
		if(!\SmartFileSystem::is_type_file((string)$indexdb)) {
			\Smart::log_warning(__METHOD__.' # Invalid Indexes DB Path (N/A): `'.$indexdb.'`');
			return [];
		} //end if
		//--
		$arr = \Smart::json_decode((string)\SmartFileSystem::read((string)$indexdb)); // mixed
		if(\Smart::array_size($arr) <= 0) {
			\Smart::log_warning(__METHOD__.' # Malformed Realm Indexes DB: `'.$indexdb.'`');
			return [];
		} //end if
		//--
		self::$dbIndexes[(string)$realm] = (array) $arr;
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	public static function renderDocMarkdown(?string $markdown_code, ?string $options='<validate:html:tidy>', ?string $relative_url_prefix='', bool $log_render_notices=true) : string {
		//--
		return (string) (new \SmartMarkdownToHTML(true, true, false, (string)$options, (string)$relative_url_prefix, (bool)$log_render_notices, null, false))->parse((string)$markdown_code); // C:0
		//--
	} //END FUNCTION


	public static function processHtml(?string $source, ?string $realm, ?string $idkey, ?string $prefix_url='') {
		//--
		$source = (string) \trim((string)$source);
		if((string)$source == '') {
			return '';
		} //end if
		//--
		$source = (string) self::fixHtml((string)$source, (string)$realm, (string)$idkey); // document must be re-validated with tidy, it makes some replacements
		$source = (string) (new \SmartHtmlParser((string)$source, true, 'any:required:tidy', false))->get_clean_html(); // prefer Tidy here, it is more safe for untrusted inputs ...
		//--
		return (array) self::validateSvgAndImagesCompressToWebp((string)$source, (string)$prefix_url);
		//--
	} //END FUNCTION


	public static function fixHtml(?string $source, ?string $realm, ?string $idkey) {
		//--
		$source = (string) \trim((string)$source);
		if((string)$source == '') {
			return '';
		} //end if
		//--
		$source = (string) (new \SmartHtmlParser((string)$source, false, 'any:required:tidy', false))->get_clean_html(); // do not use signature here ; // prefer Tidy here, it is more safe for untrusted inputs ...
		//--
		$realm = (array) explode('/', (string)$realm);
		$realm = (string) ($realm[0] ?? '');
		//--
		switch((string)$realm) {
			case 'vala': // TODO: put this outside of this method !
				//--
				$source = (string) \str_ireplace(
					[
						'<pre class="main_source">',
						'href="https://wiki.gnome.org/Projects/Valadoc"'
					],
					[
						'<pre data-language="language-vala">',
						'href="#Valadoc"'
					],
					(string) $source
				);
				//--
				break;
			default: // fix in-page links looking into the indexes ; there are different strategies implemented below because some links are only relative, others have ../backward/path ...
				//--
				$init_log = (bool) self::isDbIndexesInitialized((string)$realm);
				$arrIdx = (array) self::getDbIndexes((string)$realm);
				$arrIdx['entries'] = (array) ($arrIdx['entries'] ?? []);
				$log_broken_links = (string) 'tmp/logs/docs-optimize-json-broken-links-'.\Smart::safe_filename((string)$realm).'.log';
				//--
				if($init_log === true) {
					\SmartFileSystem::write((string)$log_broken_links, '####### Docs Optimize JSON: `'.$realm.'` ; Indexes: #'.(int)\Smart::array_size($arrIdx['entries'])."\n", 'w');
				} else {
					\SmartFileSystem::write((string)$log_broken_links, '#### Docs Optimize JSON: `'.$realm.'` ; Key: `'.$idkey.'`'."\n", 'a');
				} //end if
				//--
				$arr_idx_paths = [];
				if((int)\Smart::array_size($arrIdx['entries']) > 0) {
					foreach($arrIdx['entries'] as $key => $val) { // search as is
						if(\is_array($val)) {
							$tmp_idx_path = (string) trim((string)($val['path'] ?? null));
							if((string)$tmp_idx_path != '') {
								if(!\array_key_exists((string)$tmp_idx_path, (array)$arr_idx_paths)) {
									$arr_idx_paths[(string)$tmp_idx_path] = [];
								} //end if
								$arr_idx_paths[(string)$tmp_idx_path][] = (array) $val;
							} //end if
						} //end if
					} //end foreach
				} //end if
				//--
				if((int)\Smart::array_size($arr_idx_paths) > 0) {
					//--
					// \Smart::log_notice(print_r(\array_keys($arr_idx_paths),1));
					//--
					$matches = [];
					$pcre = \preg_match_all('/[\s]href[\s]?\=[\s]?"([^"]+)"/s', (string)$source, $matches, \PREG_PATTERN_ORDER, 0);
					if($pcre === false) {
						//--
						\Smart::log_warning(__METHOD__.'() # ERROR: '.SMART_FRAMEWORK_ERR_PCRE_SETTINGS);
						//--
					} else {
						//--
						$all_hrefs = (array) ((isset($matches[0]) && is_array($matches[0])) ? $matches[0] : []);
						$all_links = (array) ((isset($matches[1]) && is_array($matches[1])) ? $matches[1] : []);
						$max = (int) \max((int)\Smart::array_size($all_hrefs), (int)\Smart::array_size($all_links));
						$matches = null;
						//die(print_r($matches,1));
						if((int)$max > 0) {
							for($i=0; $i<$max; $i++) {
								$all_hrefs[$i] = (string) \trim((string)$all_hrefs[$i]);
								$all_links[$i] = (string) \trim((string)$all_links[$i]);
								$all_links[$i] = (string) \strtolower((string)$all_links[$i]); // some links are upper or camel case ; realm=perl&key=88
								$is_relative_link = false;
								if(
									(\stripos((string)\trim((string)$all_links[$i]), 'http://') !== 0)
									AND
									(\stripos((string)\trim((string)$all_links[$i]), 'https://') !== 0)
									AND
									(\strpos((string)\trim((string)$all_links[$i]), '//') !== 0)
								//	AND
								//	(\strpos((string)\trim((string)$all_links[$i]), '://') === false)
								) {
									$is_relative_link = true;
								} //end if
								if(\strpos((string)\trim((string)$all_links[$i]), '#') === 0) {
									$is_relative_link = false; // skip hash links
								} //end if
								if($is_relative_link === true) {
									if((int)\strpos((string)$all_links[$i], '#') > 0) {
										$all_links[$i] = (string) \substr((string)$all_links[$i], 0, (int)\strpos((string)$all_links[$i], '#'));
									} //end if
									if(((string)$all_hrefs[$i] != '') AND ((string)$all_links[$i] != '')) {
										$found = false;
										$tmp_apply_fixnum = 0;
										$tmp_fixed_key = '';
										$tmp_saved_key = '';
										$tmp_fixed_idkey = (string) $idkey;
										$tmp_fixed_key = (string) $all_links[$i];
										if(((string)$tmp_fixed_key == 'index') OR ((string)\substr((string)$tmp_fixed_key, -6, 6) == '/index')) { // can be also as /index ../index and line this
											$found = true; // index always exists
										} else {
											if((string)\substr((string)$tmp_fixed_key, 0, 3) == '../') { // backward links
												if((string)\substr((string)$tmp_fixed_key, 0, 12) == '../../../../') { // apply 5 times dirname
													$tmp_apply_fixnum = 5;
													$tmp_fixed_key = (string) \substr((string)$tmp_fixed_key, 12);
												} elseif((string)\substr((string)$tmp_fixed_key, 0, 9) == '../../../') { // apply 4 times dirname
													$tmp_apply_fixnum = 4;
													$tmp_fixed_key = (string) \substr((string)$tmp_fixed_key, 9);
												} elseif((string)\substr((string)$tmp_fixed_key, 0, 6) == '../../') { // apply 3 times dirname
													$tmp_apply_fixnum = 3;
													$tmp_fixed_key = (string) \substr((string)$tmp_fixed_key, 6);
												} else { // apply 2 times dirname
													$tmp_apply_fixnum = 2;
													$tmp_fixed_key = (string) \substr((string)$tmp_fixed_key, 3);
												} //end if else
												$tmp_fixed_key = (string) \trim((string)$tmp_fixed_key, '/.');
												$tmp_fixed_idkey = (string) $idkey;
												$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
												$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
												$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
												if((string)$tmp_fixed_idkey != '') {
													if((string)\substr((string)$tmp_fixed_key, 0, 12) == '../../../../') { // apply 4 times dirname
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
													} elseif((string)\substr((string)$tmp_fixed_key, 0, 9) == '../../../') { // apply 3 times dirname
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
													} elseif((string)\substr((string)$tmp_fixed_key, 0, 6) == '../../') { // apply 2 times dirname
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
													} else { // apply 1 times dirname
														if((string)$tmp_fixed_idkey != '') {
															$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
															$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														} //end if
													} //end if else
												} //end if
												$tmp_saved_key = (string) $tmp_fixed_key;
												if((string)$tmp_fixed_idkey != '') {
													$tmp_fixed_key = (string) $tmp_fixed_idkey.'/'.$tmp_fixed_key;
												} //end if
											} //end if
											$found = (bool) \array_key_exists((string)$tmp_fixed_key, (array)$arr_idx_paths);
											if($found !== true) { // sometimes going backward is not enough, the above fix may not work due to wrong links !
												if($tmp_saved_key != '') {
													$found = (bool) \array_key_exists((string)$tmp_saved_key, (array)$arr_idx_paths);
													if($found === true) {
														$tmp_apply_fixnum = (int) ($tmp_apply_fixnum * 10);
														$tmp_fixed_key = (string) $tmp_saved_key;
													} //end if
												} //end if
											} //end if
											if($found !== true) { // try with the current realm as prefix
												$tmp_apply_fixnum = 1;
												$tmp_fixed_key = (string) \trim((string)$all_links[$i], '/.'); // eliminate prefix/suffix ../ or others like
												if((string)$tmp_fixed_key == '') {
													$tmp_fixed_key = (string) $all_links[$i];
												} //end if
												$tmp_fixed_idkey = (string) $idkey;
												if(strpos((string)$tmp_fixed_idkey, '/') !== false) { // relative links
													$arr_expl = (array) \explode('/', (string)$tmp_fixed_idkey);
												//	for($j=0; $j<\Smart::array_size($arr_expl)+1; $j++) {
													for($j=0; $j<\Smart::array_size($arr_expl); $j++) {
														$tmp_apply_fixnum = (int) ($tmp_apply_fixnum * 10) + $j;
														if((string)$tmp_fixed_idkey != '') {
															$found = (bool) \array_key_exists((string)$tmp_fixed_idkey.'/'.$tmp_fixed_key, (array)$arr_idx_paths);
														} //end if
														if($found === true) {
															$tmp_fixed_key = (string) $tmp_fixed_idkey.'/'.$tmp_fixed_key;
														//	\SmartFileSystem::write((string)$log_broken_links, '*** Testing Link Found for key `'.$tmp_fixed_key.'`: `'.$tmp_fixed_idkey.'`'."\n", 'a');
															break;
														} else {
														//	\SmartFileSystem::write((string)$log_broken_links, '!!! Testing Link NOT Found for key `'.$tmp_fixed_key.'`: `'.(((string)$tmp_fixed_idkey != '') ? $tmp_fixed_idkey.'/' : '').$tmp_fixed_key.'`'."\n", 'a');
														} //end if
														$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
														$tmp_fixed_idkey = (string) \dirname((string)$tmp_fixed_idkey);
														$tmp_fixed_idkey = (string) \trim((string)$tmp_fixed_idkey, '/.');
													} //end for
													$arr_expl = null;
												} else {
													$found = (bool) \array_key_exists((string)$tmp_fixed_idkey.'/'.$tmp_fixed_key, (array)$arr_idx_paths);
												} //end if
											} //end if
										} //end if else
										if($found !== true) {
											$replace_link = (string) $all_links[$i];
											if(stripos((string)$all_links[$i], 'mailto:') === 0) {
												$replace_link = 'mailto:';
											} elseif(stripos((string)$all_links[$i], 'ftp://') === 0) {
												$replace_link = 'ftp:';
											} elseif(stripos((string)$all_links[$i], 'ftps://') === 0) {
												$replace_link = 'ftps:';
											} elseif(stripos((string)$all_links[$i], 'sftp://') === 0) {
												$replace_link = 'sftp:';
											} elseif(stripos((string)$all_links[$i], 'ssh://') === 0) {
												$replace_link = 'ssh:';
											} elseif(stripos((string)$all_links[$i], 'smtp://') === 0) {
												$replace_link = 'smtp:';
											} elseif(stripos((string)$all_links[$i], 'vnc://') === 0) {
												$replace_link = 'vnc:';
											} elseif(stripos((string)$all_links[$i], 'news://') === 0) {
												$replace_link = 'news:';
											} elseif(stripos((string)$all_links[$i], 'rtsp://') === 0) {
												$replace_link = 'rtsp:';
											} elseif(stripos((string)$all_links[$i], 'rtmp://') === 0) {
												$replace_link = 'rtmp:';
											} elseif(stripos((string)$all_links[$i], 'rtp://') === 0) {
												$replace_link = 'rtp:';
											} elseif(stripos((string)$all_links[$i], 'hls://') === 0) {
												$replace_link = 'hls:';
											} elseif(stripos((string)$all_links[$i], 'srt://') === 0) {
												$replace_link = 'srt:';
											} elseif(stripos((string)$all_links[$i], 'mss://') === 0) {
												$replace_link = 'mss:';
											} elseif(stripos((string)$all_links[$i], 'wss://') === 0) {
												$replace_link = 'wss:';
											} elseif(stripos((string)$all_links[$i], 'ws://') === 0) {
												$replace_link = 'ws:';
											} elseif(stripos((string)$all_links[$i], 'chrome://') === 0) {
												$replace_link = 'chromium:';
											} elseif(stripos((string)$all_links[$i], 'about://') === 0) {
												$replace_link = 'firefox:';
											} elseif(stripos((string)$all_links[$i], 'file://') === 0) {
												$replace_link = 'file:';
											} elseif(stripos((string)$all_links[$i], '://') === 0) {
												$replace_link = 'unknown:';
											} elseif(stripos((string)$all_links[$i], '/contributors.txt') !== false) {
												$replace_link = 'contributors:txt';
											} //end if
											$source = (string) \strtr((string)$source, [ (string)$all_hrefs[$i] => 'href="'.'#'.$replace_link.'" data-docs-smart-fix="bk"' ]);
											\SmartFileSystem::write((string)$log_broken_links, 'Broken Link Found `'.$all_links[$i].'`: Try-Fix#'.(int)$tmp_apply_fixnum.' `'.$tmp_fixed_key.'` commented as `'.'#'.$replace_link.'`'."\n", 'a');
										} else { // found ; fixed found
											if((int)$tmp_apply_fixnum > 0) {
												$source = (string) \strtr((string)$source, [ (string)$all_hrefs[$i] => 'href="'.$tmp_fixed_key.'" data-docs-smart-fix="fl:'.(int)$tmp_apply_fixnum.'"' ]);
											//	\SmartFileSystem::write((string)$log_broken_links, 'OK, Fixed Link Found (Fix#'.(int)$tmp_apply_fixnum.'): `'.$all_links[$i].'` as: `'.$tmp_fixed_key.'`'."\n", 'a');
											} else {
												if((string)$all_links[$i] != (string)$tmp_fixed_key) {
													$source = (string) \strtr((string)$source, [ (string)$all_hrefs[$i] => 'href="'.'#'.$all_links[$i].'" data-docs-smart-fix="err"' ]);
													\SmartFileSystem::write((string)$log_broken_links, 'ERROR, Link Found: `'.$all_links[$i].'` as `'.$tmp_fixed_key.'`'."\n", 'a');
												} else {
												//	\SmartFileSystem::write((string)$log_broken_links, 'OK, Link Found: `'.$tmp_fixed_key.'`'."\n", 'a');
												} //end if else
											} //end if
										} //end if else
									} //end if
								} //end if
							} //end for
						} //end if
						//--
					} //end if else
					//--
				} //end if else
				//--
				\SmartFileSystem::write((string)$log_broken_links, '### END'."\n", 'a');
				//--
		} //end switch
		//--
		if((string)$realm != 'vala') { // all:json-docs
			//-- this is a general fix for all json docs
			$source = (string) \str_ireplace( // this must be after standardization from above ; document must be re-validated with tidy after these replacements
				[
					'<a href="#" class="show-all">Show all</a>',
				],
				[
					'',
				],
				(string) $source // other replacements like '</span|div|p><' with '</span|div|p> <' must be done by turndown, it must be done by the converter ...
			);
			//--
			$fixes_aside_regex_file = (string) self::THE_DOCS_PATH.\Smart::safe_filename((string)$realm).'/fix-remove-aside-regex--@all.txt';
			if(\SmartFileSystem::is_type_file((string)$fixes_aside_regex_file)) {
				$fix_regex = (string) \trim((string)\SmartFileSystem::read((string)$fixes_aside_regex_file));
				if((string)$fix_regex != '') {
					$source = (string) \preg_replace((string)$fix_regex, '<br><br><!-- remove aside regex -->', (string)$source);
				} //end if
			} //end if
			//--
			$fixes_file = (string) self::THE_DOCS_PATH.\Smart::safe_filename((string)$realm).'/fix-append--'.\Smart::safe_filename((string)\Smart::create_slug((string)$idkey)).'.htm';
			if(\SmartFileSystem::is_type_file((string)$fixes_file)) {
				$fix_content = (string) \trim((string)\SmartFileSystem::read((string)$fixes_file));
				if((string)$fix_content != '') {
					$source .= (string) "\n".$fix_content;
				} //end if
				$fix_content = null;
			} //end if
			//--
		} //end if
		//--
		return (string) $source;
		//--
	} //END FUNCTION


	public static function validateSvgAndImagesCompressToWebp(?string $source, ?string $prefix_url='') {
		//--
		$out = [
			'source' 			=> '',
			'all-imgs-and-svgs' => 0,
			'imgs' 				=> 0,
			'svgs' 				=> 0,
			'invalid-data-urls' => 0,
			'urls-disabled' 	=> [],
		];
		//--
		if((string)\trim((string)$source) == '') {
			return (array) $out;
		} //end if
		//--
		$all_processed_num = 0;
		$svgs_processed_num = 0;
		$img_processed_num = 0;
		$invalid_processed_num = 0;
		$urls_disabled = [];
		$original_checksum = (string) \SmartHashCrypto::sha512((string)$source);
		$original_size = (int) \strlen((string)$source);
		//--
		$arr_imgs = (new \SmartHtmlParser((string)$source))->get_tags('img'); // {{{SYNC-CHECK-ROBOT-TRUST-IMG-LINKS}}}
		//--
		if((int)\Smart::array_size($arr_imgs) > 0) {
			for($i=0; $i<\Smart::array_size($arr_imgs); $i++) {
				//--
				if((\is_array($arr_imgs[$i])) AND (isset($arr_imgs[$i]['src']))) {
					//--
					$tmp_replaced = false;
					$tmp_img = (string) \trim((string)$arr_imgs[$i]['src']);
					$tmp_size = (int) \strlen((string)$tmp_img);
					//--
					$is_empty = false;
					if(((string)$tmp_img == '') OR ((string)$tmp_img == 'data:,')) { // fixes for: `browser_support_tables&id=srcset` | `sqlite&id=whynotgit`, have an empty img tag or empty data with no source ...
						$is_empty = true;
					} //end if
					//--
					$tmp_arr = array();
					if($is_empty !== true) {
						//--
						if((string)$prefix_url != '') {
							if((strpos((string)$tmp_img, 'data:') !== 0) AND (strpos((string)$tmp_img, 'http:') !== 0) AND (strpos((string)$tmp_img, 'https:') !== 0)) {
								$tmp_img = (string) $prefix_url.$tmp_img;
							} //end if
						} //end if
						//--
						$all_processed_num++;
						//--
						$tmp_arr = (array) \SmartRobot::load_url_img_content((string)$tmp_img);
						//--
						if(((int)$tmp_arr['result'] == 1) AND ((int)$tmp_arr['code'] == 200) AND ((string)$tmp_arr['content'] != '')) {
							//--
							if(\in_array((string)$tmp_arr['extension'], [ '.gif', '.png', '.jpg', '.webp' ])) { // test: `realm=d3-4&id=d3-geo-projection` ; `realm=sqlite&id=whynotgit`
								//--
								$tmp_img = (string) \trim((string)$tmp_arr['content']);
								if((string)$tmp_img != '') {
									//--
									$imgd = new \SmartImageGdProcess((string)$tmp_img);
									$img_ext = (string) $imgd->getImageType();
									//--
									if(
										((string)$img_ext == 'png') OR
										((string)$img_ext == 'gif') OR
										((string)$img_ext == 'jpg') OR
										((string)$img_ext == 'webp')
									) {
										if($imgd->resizeImage(480, 480, false, 2, [255, 255, 255])) {
											//--
											if($imgd->getStatusOk() === true) {
												//--
												$img_export_type = 'webp';
												$tmp_img = (string) $imgd->getImageData((string)$img_export_type, (0.25 * 100), 9);
												$tmp_img = 'data:image/'.$img_export_type.';base64,'.\base64_encode((string)$tmp_img);
												//--
												$source = (string) \str_replace(
													'src="'.(string)$arr_imgs[$i]['src'].'"',
													'src="'.(string)$tmp_img.'"',
													(string) $source
												);
												$tmp_replaced = true;
												//--
												$img_processed_num++;
												//--
											//	echo $tmp_size.'/'.\strlen($tmp_img).'<hr>';
											//	die((string)'<img src="'.\Smart::escape_html($tmp_img).'">');
												//--
											} else {
												//--
												$invalid_processed_num++; // perhaps should be ignored ...
												//--
											} //end if else
											//--
										} else {
											//--
											$invalid_processed_num++; // perhaps should be ignored ...
											//--
										} //end if else
									} //end if
									//--
									$img_ext = null; // free mem
									$imgd = null; // free mem
									//--
								} //end if
								//--
							} elseif((string)$tmp_arr['extension'] == '.svg') { // test: &realm=rust&key=4
								//--
								$tmp_img = (string) \trim((string)$tmp_arr['content']);
								if((string)$tmp_img != '') {
									if((\stripos((string)$tmp_img, '<svg') !== false) AND (\stripos((string)$tmp_img, '</svg>') !== false)) { // {{{SYNC VALIDATE SVG}}}
										$tmp_img = (string) \str_replace( // adjust font size {{{SYNC-DOCS-SVG-FONT-ADJUST}}} ; test: `realm=rust&id=book/ch04-01-what-is-ownership`
											[
												'font-family=',
												'font-size=',
											],
											[
												'font-family="\'IBM Plex Sans\'" data-original-font=',
												'font-size-adjust="0.5"'.' '.'font-size=',
											],
											(string) $tmp_img
										);
										$tmp_img .= "\n".'<!-- Font-Adjust: S.F. Docs -->';
										$tmp_img = (new \SmartXmlParser())->format((string)$tmp_img, false, false, false, true); // avoid injection of other content than XML, remove the XML header
										if((string)$tmp_img != '') {
											//--
											$tmp_img = 'data:image/svg+xml,'.\rawurlencode((string)$tmp_img); // re-encode as urlencode not base64 the SVGs
											//--
											$source = (string) \str_replace(
												'src="'.(string)$arr_imgs[$i]['src'].'"',
												'src="'.(string)$tmp_img.'"',
												(string) $source
											);
											$tmp_replaced = true;
											//--
											$svgs_processed_num++;
											//--
										} //end if
									} else {
										//--
										$invalid_processed_num++; // perhaps should be ignored ...
										//--
									} //end if else
								} //end if
								//--
							} //end if else
							//--
						} elseif((string)$tmp_arr['mode'] == 'embedded') {
							//--
							$invalid_processed_num++;
							//--
						} else {
							//--
							$urls_disabled[] = (string) \substr((string)$arr_imgs[$i]['src'], 0, 255).(\strlen((string)$arr_imgs[$i]['src']) > 255 ? '...' : '');
							//--
						} //end if else
						//--
						$tmp_arr = null; // free mem
						//--
					} //end if
					//--
					if($tmp_replaced !== true) {
						//--
						$source = (string) \str_replace(
							'src="'.(string)$arr_imgs[$i]['src'].'"',
							'src="data:,"',
							(string) $source
						);
						//--
					} //end if
					//--
					$tmp_img = null; // free mem
					$tmp_size = 0; // free mem
					//--
				} //end if
				//--
				$arr_imgs[$i] = null; // free mem
				//--
			} //end for
		} //end if
		//--
		$post_processing_checksum = (string) \SmartHashCrypto::sha512((string)$source);
		//--
		$source = '<!-- ['.\Smart::escape_html((string)__FUNCTION__.': ALL#'.(int)$all_processed_num.' ; IMG#'.(int)$img_processed_num.' ; SVG#'.(int)$svgs_processed_num.' ; INVALID#'.(int)$invalid_processed_num.' ; DISABLED#'.(int)\Smart::array_size($urls_disabled).' :: '.' OriginalSize='.(int)$original_size.' ; CurrentSize='.(int)\strlen((string)$source)).'] -->'."\n".$source;
		//--
		if((string)$post_processing_checksum != (string)$original_checksum) {
			$source = (string) (new \SmartHtmlParser((string)$source, true, 'any:required:tidy', false))->get_clean_html();
		} //end if
		//--
		$out['urls-disabled'] 		= (array) $urls_disabled;
		$out['invalid-data-urls'] 	= (int) $invalid_processed_num;
		$out['svgs'] 				= (int) $svgs_processed_num;
		$out['imgs'] 				= (int) $img_processed_num;
		$out['all-imgs-and-svgs'] 	= (int) $all_processed_num;
		$out['source'] 				= (string) $source;
		//--
		return (array) $out;
		//--
	} //END FUNCTION

} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
