<?php
// Class: \SmartModExtLib\Barcodes\SmartBarcodes2D
// [Smart.Framework.Modules - Barcodes 2D]
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Barcodes;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// BarCodes 2D: QRCode, Aztec, DataMatrix and PDF417
// DEPENDS: Smart.Framework
//======================================================

//--
if(!defined('SMART_FRAMEWORK_BARCODE_2D_MODE')) {
	define('SMART_FRAMEWORK_BARCODE_2D_MODE', 'qrcode'); // use qrcode as default 2D barcode
} //end if
if(!defined('SMART_FRAMEWORK_BARCODE_2D_OPTS')) {
	define('SMART_FRAMEWORK_BARCODE_2D_OPTS', 'L'); // default use the low level correction mode for 2D barcodes
} //end if
//--

// [REGEX-SAFE-OK]

//======================================================
// BarCodes 2D: QRCode, Aztec, DataMatrix (SemaCode), PDF417
// License: GPLv3
// (c) 2015-2020 unix-world.org
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartBarcodes2D - Generates 2D BarCodes: QRCode, Aztec, DataMatrix (SemaCode), PDF417.
 *
 * @license: GPLv3
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	Smart.Framework
 * @version 	v.20211127
 * @package 	modules:Barcodes
 *
 */
final class SmartBarcodes2D {

	// ::


	/**
	 * Function: Generate a 2D Barcode: QRCode, DataMatrix (SemaCode), PDF417
	 *
	 * @param STRING 	$y_code 			The code for the BarCode Generator
	 * @param ENUM 		$y_type				The BarCode Type: qrcode / aztec / semacode / pdf417 (the pdf417 req. PHP BCMath Extension))
	 * @param ENUM 		$y_format			The Barcode format: html, html-png, png, html-svg, svg
	 * @param INTEGER+ 	$y_size				The Scale-Size for Barcode (1..4)
	 * @param HEXCOLOR	$y_color			The Hexadecimal Color for the Barcode Pixels ; default is Black = #000000
	 * @param MIXED		$y_extraoptions		Extra Options: for QRCode = Quality [L, M, Q, H] L as default ; for PDF417 a Ratio Integer between 1 and 17
	 * @param INTEGER	$y_cachetime		If > 0 will cache it for this number of seconds ; if zero will never expire ; if < 0 will use no cache
	 *
	 * @return MIXED	By Type Selection: 	HTML Code / PNG Image / SVG Code
	 *
	 */
	public static function getBarcode($y_code, $y_type, $y_format, $y_size, $y_color='#000000', $y_extraoptions='', $y_cachetime=-1) {
		//--
		if((string)\trim((string)$y_code) == '') {
			\Smart::log_warning(__METHOD__.' # Empty Code');
			return '';
		} //end if
		//--
		$y_size = (int) $y_size;
		if($y_size < 1) {
			$y_size = 1;
		} elseif($y_size > 4) {
			$y_size = 4;
		} //end if
		//--
		$y_color = (string) \strtoupper((string)$y_color);
		if(!\preg_match('/^\#([A-F0-9]{6})$/', (string)$y_color)) {
			$y_color = '#000000';
		} //end if
		//--
		switch((string)$y_type) {
			case 'qrcode':
				$c__typ = 1;
				switch((string)$y_extraoptions) {
					case 'H':
						$y_extraoptions = 'H';
						break;
					case 'Q':
						$y_extraoptions = 'Q';
						break;
					case 'M':
						$y_extraoptions = 'M';
						break;
					case 'L':
					default:
						$y_extraoptions = 'L';
				} //end switch
				$barcode_type = 'qrcode';
				break;
			case 'aztec':
				$c__typ = 2;
				$y_extraoptions = '';
				$barcode_type = 'aztec';
				break;
			case 'semacode':
				$c__typ = 3;
				$y_extraoptions = '';
				$barcode_type = 'semacode';
				break;
			case 'pdf417':
				$c__typ = 0;
				$y_extraoptions = (int) $y_extraoptions;
				if($y_extraoptions <= 0) {
					$y_extraoptions = 1;
				} //end if
				if($y_extraoptions > 17) {
					$y_extraoptions = 17;
				} //end if
				$barcode_type = 'pdf417';
				break;
			default:
				\Smart::log_warning(__METHOD__.' # Invalid Barcode Type: '.$y_type);
				return '';
		} //end switch
		//--
		switch((string)$y_format) {
			case 'html':
				$barcode_format = '.htm';
				$c__fmt = 1;
				break;
			case 'html-png':
				$barcode_format = '.png.htm';
				$c__fmt = 2;
				break;
			case 'png':
				$barcode_format = '.png';
				$c__fmt = 3;
				break;
			case 'html-svg':
				$barcode_format = '.svg.htm';
				$c__fmt = 4;
				break;
			case 'svg':
				$barcode_format = '.svg';
				$c__fmt = 0;
				break;
			default:
				\Smart::log_warning(__METHOD__.' # Invalid Barcode Format: '.$y_format);
				return '';
		} //end switch
		//--
		$y_cachetime = (int) $y_cachetime;
		//--

		//--
		$realm = 'barcode2d';
		$cache_handler = null;
		//--
		if((int)$y_cachetime >= 0) { // if allow caching, try to load from cache
			//--
			if(\SmartDbaPersistentCache::isActive()) {
				$cache_handler = '\\SmartDbaPersistentCache';
			} elseif(\SmartSQlitePersistentCache::isActive()) {
				$cache_handler = '\\SmartSQlitePersistentCache';
			} //end if else
			//--
			if($cache_handler) { // and of course if DBA or SQLite PCache is Available/Active
				//--
				$cache_realm = (string) $cache_handler::safeKey($realm.'-T'.$c__typ.'-F'.$c__fmt.'-S'.(int)$y_size.'-E'.$y_extraoptions.'-Q'.\trim((string)$y_color,'#'));
				$cache_uuid  = (string) $cache_handler::safeKey(substr((string)\Smart::safe_filename($y_code), 0, 25).'-'.\sha1($realm.'://'.$barcode_type.'/'.$barcode_format.'/'.(int)$y_size.'/'.$y_extraoptions.'/'.$y_color.'/'.$y_code));
				//--
				$out = $cache_handler::getKey( // mixed
					(string) $cache_realm, 	// realm
					(string) $cache_uuid 	// key
				);
				//--
				if((string)$out != '') {
					return (string) $cache_handler::varUncompress($out); // if found in cache return it
				} else {
					$out = '';
				} //end if else
				//--
			} //end if
			//--
		} //end if
		//--
		switch((string)$barcode_type) {
			case 'qrcode':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode2DQRCode($y_code, $y_extraoptions))->getBarcodeArray();
				break;
			case 'aztec':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode2DAztec($y_code))->getBarcodeArray();
				break;
			case 'semacode':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode2DSemacodeDataMatrix($y_code))->getBarcodeArray();
				break;
			case 'pdf417':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode2DPdf417($y_code, $y_extraoptions, -1))->getBarcodeArray();
				break;
			default:
				// if invalid type is logged before
				return '';
		} //end switch
		//--
		switch((string)$y_format) {
			case 'html':
				$out = '<!-- '.\Smart::escape_html(\strtoupper($barcode_type).' ('.$y_size.'/'.$y_color.') ['.$y_extraoptions.']'.' :: '.\date('YmdHis')).' -->'.'<div title="'.\Smart::escape_html($y_code).'">'.self::getBarcodeHTML($arr_barcode, $y_size, $y_color).'</div>'.'<!-- #END :: '.\Smart::escape_html(\strtoupper($barcode_type)).' -->';
				break;
			case 'html-png': // html img embedded png
				$out = '<!-- '.\Smart::escape_html(\strtoupper($barcode_type).' ('.$y_size.'/'.$y_color.') ['.$y_extraoptions.']'.' :: '.\date('YmdHis')).' -->'.'<div title="'.\Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLPNG($arr_barcode, $y_size, $y_color).'</div>'.'<!-- #END :: '.\Smart::escape_html(\strtoupper($barcode_type)).' -->';
				break;
			case 'png': // raw png
				$out = self::getBarcodePNG($arr_barcode, $y_size, $y_color); // needs header image/png on output
				break;
			case 'html-svg':
				$out = '<!-- '.\Smart::escape_html(\strtoupper($barcode_type).' ('.$y_size.'/'.$y_color.') ['.$y_extraoptions.']'.' :: '.\date('YmdHis')).' -->'.'<div title="'.\Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLSVG($arr_barcode, $y_size, $y_color).'</div>'.'<!-- #END :: '.\Smart::escape_html(\strtoupper($barcode_type)).' -->';
				break;
			case 'svg':
				$out = self::getBarcodeSVG($arr_barcode, $y_size, $y_color); // needs header image/svg on output
				break;
			default:
				// if invalid format is logged before
				return '';
		} //end switch
		//--

		//--
		if((int)$y_cachetime >= 0) { // if allow caching, try to save in cache
			//--
			if($cache_handler) { // and of course if DBA or SQLite PCache is Available/Active
				//--
				$cache_handler::setKey(
					(string) $cache_realm, 							// realm
					(string) $cache_uuid, 							// key
					(string) $cache_handler::varCompress($out), 	// content
					(int)    $y_cachetime 							// expire time
				);
				//--
			} //end if
			//--
		} //end if
		//--

		//--
		return (string) $out;
		//--

	} //END FUNCTION


	/**
	 * Function: Get BarCode as HTML
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeHTML($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!\is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		//--
		$z = self::conformZ($z);
		//--
		$microtime = \microtime(true);
		//--
		$html = '';
		//--
		$html .= "\n".'<!-- Barcode2D / HTML --><table border="0" cellspacing="0" cellpadding="0" style="border-style:hidden; border-collapse:collapse;">';
		//-- print barcode elements
		for($r=0; $r<$barcode_arr['num_rows']; $r++) {
			//--
			$html .= "\n".'<tr height="'.$z.'" style="height:'.$z.'px;">';
			//-- for each column
			for($c=0; $c<$barcode_arr['num_cols']; $c++) {
				//--
				if($barcode_arr['bcode'][$r][$c] == 1) {
					$html .= '<td bgcolor="'.$color.'" width="'.$z.'" height="'.$z.'" style="font-size:1px;width:'.$z.'px;height:'.$z.'px;"></td>';
				} else {
					$html .= '<td bgcolor="#FFFFFF" width="'.$z.'" height="'.$z.'" style="font-size:1px;width:'.$z.'px;height:'.$z.'px;"></td>';
				} //end if
				//--
			} //end for
			//--
			$html .= '</tr>';
			//--
		} //end for
		//--
		$html .= "\n".'</table><!-- END :: Barcode2D ['.(\microtime(true) - $microtime).'] -->'."\n";
		//--
		return (string) $html;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as SVG embedded in HTML
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeEmbeddedHTMLSVG($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!\is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		//--
		$z = self::conformZ($z);
		//--
		$microtime = \microtime(true);
		//--
		return "\n".'<!-- Barcode2D / SVG -->'.'<img src="data:image/svg+xml;base64,'.\Smart::escape_html(\base64_encode(self::getBarcodeSVG($barcode_arr, $z, $color))).'" alt="BarCode2D-SVG">'.'<!-- END :: Barcode2D ['.(\microtime(true) - $microtime).'] -->'."\n";
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as SVG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeSVG($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!\is_array($barcode_arr)) {
			return '<svg width="100" height="10"><text x="0" y="10" fill="#FF0000" font-size="9" font-family="monospace">[ INVALID BARCODE ]</text></svg>';
		} //end if
		//--
		$z = self::conformZ($z);
		//--
		$svg = '';
		//--
		$svg .= '<'.'?'.'xml version="1.0" encoding="UTF-8" standalone="no"'.' ?'.'>'."\n";
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
		$svg .= '<svg width="'.\round(($barcode_arr['num_cols'] * $z), 3).'" height="'.\round(($barcode_arr['num_rows'] * $z), 3).'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
		$svg .= "\t".'<desc>'.\Smart::escape_html($barcode_arr['code']).'</desc>'."\n";
		$svg .= "\t".'<rect fill="#FFFFFF" x="0" y="0" width="'.\round(($barcode_arr['num_cols'] * $z), 3).'" height="'.\round(($barcode_arr['num_rows'] * $z), 3).'" />'."\n";
		$svg .= "\t".'<g id="elements" fill="'.$color.'" stroke="none">'."\n";
		//-- print barcode elements
		$y = 0;
		//-- for each row
		for($r=0; $r<$barcode_arr['num_rows']; ++$r) {
			//--
			$x = 0;
			//-- for each column
			for($c=0; $c<$barcode_arr['num_cols']; ++$c) {
				//--
				if($barcode_arr['bcode'][$r][$c] == 1) {
					//-- draw a single barcode cell
					$svg .= "\t\t".'<rect x="'.$x.'" y="'.$y.'" width="'.$z.'" height="'.$z.'" />'."\n";
					//--
				} //end if
				//--
				$x += $z;
				//--
			} //end for
			//--
			$y += $z;
			//--
		} //end for
		//--
		$svg .= "\t".'</g>'."\n";
		$svg .= '</svg>'."\n";
		//--
		return (string) $svg;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as PNG embedded in HTML
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeEmbeddedHTMLPNG($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!\is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		//--
		$z = self::conformZ($z);
		//--
		$microtime = \microtime(true);
		//--
		return "\n".'<!-- Barcode2D / PNG -->'.'<img src="data:image/png;base64,'.\Smart::escape_html(\base64_encode(self::getBarcodePNG($barcode_arr, $z, $color))).'" alt="BarCode2D-PNG">'.'<!-- END :: Barcode2D ['.(\microtime(true) - $microtime).'] -->'."\n";
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as PNG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodePNG($barcode_arr, $z=3, $color=array(0,0,0)) {
		//--
		if(!\is_array($color)) {
			$color = (string) $color;
			$color = \trim(\str_replace('#', '', $color));
			$color = array(\hexdec(\substr($color, 0, 2)), \hexdec(\substr($color, 2, 2)), \hexdec(\substr($color, 4, 2)));
		} //end if
		//--
		if(!\is_array($barcode_arr)) {
			//--
			\Smart::log_notice('Invalid Barcode2D PNG Data: Not Array !');
			//--
			$width = 125;
			$height = 10;
			//--
			$png = @\imagecreate($width, $height);
			$bgcol = @\imagecolorallocate($png, 250, 250, 250);
			$fgcol = @\imagecolorallocate($png, 255, 0, 0);
			@\imagestring($png, 1, 1, 1, "[ INVALID BARCODE (1) ]", $fgcol);
			//--
		} else {
			//--
			$z = self::conformZ($z);
			//-- calculate image size
			$the_width = ($barcode_arr['num_cols'] * $z);
			$the_height = ($barcode_arr['num_rows'] * $z);
			//--
			$png = null;
			if(($the_width > 0) AND ($the_height > 0)) {
				$png = @\imagecreate($the_width, $the_height);
			} //end if
			//--
			if(!$png) {
				//--
				\Smart::log_notice('Invalid Barcode2D PNG Dimensions: '."\n".'Code='.$barcode_arr['code']."\n".'Cols='.$barcode_arr['num_cols'].' ; Rows='.$barcode_arr['num_rows']);
				//--
				$width = 125;
				$height = 10;
				//--
				$png = @\imagecreate($width, $height);
				$bgcol = @\imagecolorallocate($png, 250, 250, 250);
				$fgcol = @\imagecolorallocate($png, 255, 0, 0);
				@\imagestring($png, 1, 1, 1, "[ INVALID BARCODE (2) ]", $fgcol);
				//--
			} else {
				//--
				$bgcol = @\imagecolorallocate($png, 255, 255, 255);
				$fgcol = @\imagecolorallocate($png, $color[0], $color[1], $color[2]);
				//-- print barcode elements
				$y = 0;
				//-- for each row
				for($r = 0; $r < $barcode_arr['num_rows']; ++$r) {
					//--
					$x = 0;
					//-- for each column
					for($c = 0; $c < $barcode_arr['num_cols']; ++$c) {
						//--
						if($barcode_arr['bcode'][$r][$c] == 1) {
							//-- draw a single barcode cell
							@\imagefilledrectangle($png, $x, $y, ($x + $z - 1), ($y + $z - 1), $fgcol);
							//--
						} //end if
						//--
						$x += $z;
						//--
					} //end for
					//--
					$y += $z;
					//--
				} //end for
				//--
			} //end if else
			//--
		} //end if else
		//--
		\ob_start();
		@\imagepng($png); // barcodes are speed oriented ! for 2 color png the zlib default compression level (6) is enough and increasing to 9 makes no diff in size ; more, if adding PNG_ALL_FILTERS will increase the size
		$imagedata = \ob_get_clean();
		@\imagedestroy($png);
		$png = null;
		//--
		return (string) $imagedata;
		//--
	} //END FUNCTION


	private static function conformZ($z) {
		//-- z must be between 1 and 16
		$z = (int) $z;
		if($z < 1) {
			$z = 1;
		} //end if
		if($z > 16) {
			$z = 16;
		} //end if
		//--
		return (int) $z;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
