<?php
// [@[#[!SF.DEV-ONLY!]#]@]
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
 * Class: Optimization Utils
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20210812
 * @package 	Docs
 *
 */
final class OptimizationUtils {

	// ::

	public const THE_HDOCS_PATH = 'wpub/devdocs-html/';
	public const THE_DOCS_PATH = 'wpub/devdocs/';
	public const THE_DOCS_FILE = 'db.json';
	public const THE_DOCS_OPT_FILE = 'db.optimized.json'; // {{{SYNC-MOD-DOCS-OPT-FILE}}}
	public const THE_DOCS_MD_FILE = 'db-md.json'; // {{{SYNC-MOD-DOCS-MARKDOWN-FILE}}}
	public const THE_DOCS_IDX_FILE = 'index.json';


	public static function convertHtml2Markdown(?string $source) {
		//--
		if(!\class_exists('\\League\\HTMLToMarkdown\\HtmlConverter')) {
			if(!\is_file('modules/vendor/League/autoload.php')) {
				\SmartFrameworkRuntime::Raise500Error('ERROR: Cannot Load League/HTMLToMarkdown/HtmlConverter ...');
				return '';
			} //end if
			require_once('modules/vendor/League/autoload.php');
		} //end if
		//--
		$out = '';
//		try {
			$out = (string) (new \League\HTMLToMarkdown\HtmlConverter([ 'header_style' => 'atx', 'strip_tags' => true, 'strip_placeholder_links' => true, 'preserve_comments' => false ]))->convert((string)$source);
//		} catch(\Exception $e) {
//			$out = '';
//		} //end try catch
		//--
		return (string) $out;
		//--
	} //END FUNCTION


	public static function processHtml(?string $source, ?string $realm, ?string $prefix_url='') {
		//--
		$source = (string) \trim((string)$source);
		if((string)$source == '') {
			return '';
		} //end if
		//--
		$source = (string) \SmartModExtLib\Docs\OptimizationUtils::fixHtml((string)$source, (string)$realm); // document must be re-validated with tidy, it makes some replacements
		$source = (string) (new \SmartHtmlParser((string)$source, true, 2, false))->get_clean_html(); // prefer Tidy here, it is more safe for untrusted inputs ...
		//--
		return (array) \SmartModExtLib\Docs\OptimizationUtils::validateSvgAndImagesCompressToWebp((string)$source, (string)$prefix_url);
		//--
	} //END FUNCTION


	public static function fixHtml(?string $source, ?string $realm) {
		//--
		$source = (string) \trim((string)$source);
		if((string)$source == '') {
			return '';
		} //end if
		//--
		$source = (string) (new \SmartHtmlParser((string)$source, false, 2, false))->get_clean_html(); // do not use signature here ; // prefer Tidy here, it is more safe for untrusted inputs ...
		//--
		$realm = (array) explode('/', (string)$realm);
		$realm = (string) ($realm[0] ?? '');
		//--
		switch((string)$realm) {
			case 'vala':
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
				break;
			case 'json-docs':
			default:
				$source = (string) \str_ireplace( // this must be after standardization from above ; document must be re-validated with tidy after these replacements
					[
						'<a href="#" class="show-all">Show all</a>',
					],
					[
						'',
					],
					(string) $source // other replacements like '</span|div|p><' with '</span|div|p> <' must be done by turndown, it must be done by the converter ...
				);
		} //end switch
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
			$source = (string) (new \SmartHtmlParser((string)$source, true, 2, false))->get_clean_html();
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
