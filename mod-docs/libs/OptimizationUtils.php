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
 * @version 	v.20210601
 * @package 	Docs
 *
 */
final class OptimizationUtils {

	// ::

	public static function validateSvgAndImagesCompressToWebp(?string $source) {
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
		$original_checksum = (string) \SmartHashCrypto::sha384((string)$source);
		$original_size = (int) \strlen((string)$source);
		//--
		$arr_imgs = (new \SmartHtmlParser((string)$source))->get_tags('img'); // {{{SYNC-CHECK-ROBOT-TRUST-IMG-LINKS}}}
		//--
		if((int)\Smart::array_size($arr_imgs) > 0) {
			for($i=0; $i<\Smart::array_size($arr_imgs); $i++) {
				//--
				$all_processed_num++;
				//--
				if(\is_array($arr_imgs[$i])) {
					if(isset($arr_imgs[$i]['src'])) {
						//--
						$tmp_img = (string) \trim((string)$arr_imgs[$i]['src']);
						$tmp_size = (int) \strlen((string)$tmp_img);
						//--
						if(
							(\stripos((string)$tmp_img, 'data:image/png;base64,') === 0) OR 	// 22
							(\stripos((string)$tmp_img, 'data:image/gif;base64,') === 0) OR 	// 22
							(\stripos((string)$tmp_img, 'data:image/jpg;base64,') === 0) OR 	// 22
							(\stripos((string)$tmp_img, 'data:image/jpeg;base64,') === 0) 		// 23
						) {
							$prefix_len = 22;
							if(\stripos((string)$tmp_img, 'data:image/jpeg;base64,') === 0) {
								$prefix_len = 23;
							} //end if
							$tmp_img = (string) \trim((string)\substr((string)$tmp_img, (int)$prefix_len));
							if((string)$tmp_img != '') {
								$tmp_img = (string) \base64_decode((string)$tmp_img);
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
											if($imgd->getStatusOk() === true) {
												//--
												$img_export_type = 'webp'; // test: &realm=d3-4&key=36
												$tmp_img = (string) $imgd->getImageData((string)$img_export_type, (0.2 * 100), 9);
												$tmp_img = 'data:image/'.$img_export_type.';base64,'.\base64_encode((string)$tmp_img);
												//--
												$source = (string) \str_replace(
													'src="'.(string)$arr_imgs[$i]['src'].'"',
													'src="'.(string)$tmp_img.'"',
													(string) $source
												);
												//--
												$img_processed_num++;
												//--
											//	echo $tmp_size.'/'.\strlen($tmp_img).'<hr>';
											//	die((string)'<img src="'.\Smart::escape_html($tmp_img).'">');
												//--
											} //end if
										} //end if
									} //end if
									//--
									$img_ext = null; // free mem
									$imgd = null; // free mem
									//--
								} //end if
							} //end if
						} elseif( // test: &realm=rust&key=4
							(\stripos((string)$tmp_img, 'data:image/svg+xml;base64,') === 0) OR 	// 26
							(\stripos((string)$tmp_img, 'data:image/svg+xml,') === 0) 				// 19
						) {
							if(\stripos((string)$tmp_img, 'data:image/svg+xml;base64,') === 0) {
								$prefix_len = 26;
								$tmp_img = (string) \trim((string)\substr((string)$tmp_img, (int)$prefix_len));
								if((string)$tmp_img != '') {
									$tmp_img = (string) \base64_decode((string)$tmp_img);
								} //end if
							} elseif(\stripos((string)$tmp_img, 'data:image/svg+xml,') === 0) {
								$prefix_len = 19;
								$tmp_img = (string) \trim((string)\substr((string)$tmp_img, (int)$prefix_len));
								if((string)$tmp_img != '') {
									$tmp_img = (string) \trim((string)\urldecode((string)$tmp_img)); // use url decode instead of rawurldecode ; will do the job of rawurldecode + will decode also + as spaces
								} //end if
							} else {
								$tmp_img = ''; // invalidate svg, req. for the next step
							} //end if
							if((string)$tmp_img != '') {
								if((\stripos((string)$tmp_img, '<svg') !== false) AND (\stripos((string)$tmp_img, '</svg>') !== false)) { // {{{SYNC VALIDATE SVG}}}
									$tmp_img = (string) \str_replace( // adjust font size {{{SYNC-DOCS-SVG-FONT-ADJUST}}} ; test: realm=rust&key=16
										[
											'font-family=',
											'font-size=',
										],
										[
											'font-family="\'IBM Plex Sans\'" data-original-font=',
											'font-size-adjust="0.5"'.' '.'font-size=',
										],
										(string)$tmp_img
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
										//--
										$svgs_processed_num++;
										//--
									} //end if
								} else {
									$tmp_img = ''; // invalid svg !
								} //end if else
							} //end if
						} elseif(\stripos((string)$tmp_img, 'data:image/') !== false) {
							//--
							$source = (string) \str_replace(
								'src="'.(string)$arr_imgs[$i]['src'].'"',
								'src="data:,"',
								(string) $source
							);
							//--
							$invalid_processed_num++;
							//--
						} else {
							//--
							$urls_disabled[] = (string) \substr((string)$arr_imgs[$i]['src'], 0, 255).(\strlen((string)$arr_imgs[$i]['src']) > 255 ? '...' : '');
							//--
						} //end if
						//--
						$tmp_img = null; // free mem
						$tmp_size = 0; // free mem
						//--
					} //end if
				} //end if
				//--
				$arr_imgs[$i] = null; // free mem
				//--
			} //end for
		} //end if
		//--
		$post_processing_checksum = (string) \SmartHashCrypto::sha384((string)$source);
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
