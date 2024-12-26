<?php
// Class: \SmartModExtLib\Barcodes\SmartBarcodes1D
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
// BarCodes 1D: EAN/UPC, Code128, Code93, Code39, RMS
// License: GPLv3
//======================================================

//--
if(!defined('SMART_FRAMEWORK_BARCODE_1D_MODE')) {
	define('SMART_FRAMEWORK_BARCODE_1D_MODE', '128'); // use code 128 as default 1D barcode
} //end if
//--

// [REGEX-SAFE-OK]


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartBarcodes1D - Generates 1D BarCodes: EAN/UPC, 128 B, 93 E+, 39 E, KIX.
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
final class SmartBarcodes1D {

	// ::


	/**
	 * Function: Generate a 1D Barcode: EAN/UPC, 128 B, 93 E+, 39 E, KIX, RMS
	 *
	 * @param STRING 	$y_code 			The code for the BarCode Generator
	 * @param ENUM 		$y_type				The BarCode Type: 128 / 93 / 39 / KIX / RMS
	 * @param ENUM 		$y_format			The Barcode format: html, html-png, png, html-svg, svg
	 * @param INTEGER+ 	$y_size				The Scale-Size for Barcode (1..4)
	 * @param INTEGER+	$y_height			The Height in pixels for the Barcode
	 * @param HEXCOLOR	$y_color			The Hexadecimal Color for the Barcode Bars ; default is Black = #000000
	 * @param BOOLEAN	$y_display_text		If TRUE will display the Code below of BarCode Bars ; default is FALSE
	 * @param INTEGER	$y_cachetime		If > 0 will cache it for this number of seconds ; if zero will never expire ; if < 0 will use no cache
	 *
	 * @return MIXED	By Type Selection: 	HTML Code / PNG Image / SVG Code
	 *
	 */
	public static function getBarcode($y_code, $y_type, $y_format, $y_size, $y_height, $y_color='#000000', $y_display_text=false, $y_cachetime=-1) {
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
		$y_height = (int) $y_height;
		if($y_height < 1) {
			$y_height = 10;
		} elseif($y_height > 550) {
			$y_height = 550;
		} //end if else
		//--
		$y_color = (string) \strtoupper((string)$y_color);
		if(!\preg_match('/^\#([A-F0-9]{6})$/', (string)$y_color)) {
			$y_color = '#000000';
		} //end if
		//--
		switch((string)$y_type) {
			case '128': // 128 B (Extended)
				$barcode_type = '128B';
				$c__typ = 1;
				break;
			case '93': // 93 Extended +Checksum
				$barcode_type = '93E+';
				$c__typ = 2;
				break;
			case '39': // 39 Extended
				$barcode_type = '39E';
				$c__typ = 3;
				break;
			case 'KIX': // RMS KIX (Variant TNT) :: max 11 chars :: This needs a height that divides by 3
				$barcode_type = 'KIX';
				$c__typ = 4;
				break;
			case 'RMS': // RMS CBC (Variant RMS) :: max 11 chars :: This needs a height that divides by 3
				$barcode_type = 'CBC';
				$c__typ = 5;
				break;
			case 'EANUPC': // EAN-13 / UPC-A :: max 13 characters, numeric only
				$barcode_type = 'EANUPC';
				$c__typ = 0;
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
		if($y_display_text === true) {
			$barcode_show_text = 'T';
		} else {
			$barcode_show_text = 'X';
		} //end if else
		//--
		$y_cachetime = (int) $y_cachetime;
		//--

		//--
		$realm = 'barcode1d';
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
				$cache_realm = (string) $cache_handler::safeKey($realm.'-T'.$c__typ.'-F'.$c__fmt.'-S'.(int)$y_size.'-H'.(int)$y_height.'-Q'.\trim((string)$y_color,'#').'-'.$barcode_show_text);
				$cache_uuid  = (string) $cache_handler::safeKey(substr((string)\Smart::safe_filename($y_code), 0, 25).'-'.\sha1($realm.'://'.$barcode_type.'/'.$barcode_format.'/'.(int)$y_size.'/'.(int)$y_height.'/'.$y_color.'/'.$barcode_show_text.'/'.$y_code));
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
			case '128B':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode1D128($y_code, 'B'))->getBarcodeArray();
				break;
			case '93E+':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode1D93($y_code, true, true))->getBarcodeArray();
				break;
			case '39E':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode1D39($y_code, true, false))->getBarcodeArray();
				break;
			case 'KIX':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode1DRMS4CC($y_code, 'KIX'))->getBarcodeArray();
				break;
			case 'CBC':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode1DRMS4CC($y_code, 'CBC'))->getBarcodeArray();
				break;
			case 'EANUPC':
				$arr_barcode = (new \SmartModExtLib\Barcodes\Barcode1DEANUPC($y_code))->getBarcodeArray();
				break;
			default:
				// if invalid type is logged before
				return '';
		} //end switch
		//--
		switch((string)$y_format) {
			case 'html':
				$out = '<!-- '.\Smart::escape_html(\strtoupper($barcode_type).' ('.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.') :: '.\date('YmdHis')).' -->'.'<div title="'.\Smart::escape_html($y_code).'">'.self::getBarcodeHTML($arr_barcode, $y_size, $y_height, $y_color, $y_display_text).'</div>'.'<!-- #END :: '.\Smart::escape_html(\strtoupper($barcode_type)).' -->';
				break;
			case 'html-png': // html img embedded png
				$out = '<!-- '.\Smart::escape_html(\strtoupper($barcode_type).' ('.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.') :: '.\date('YmdHis')).' -->'.'<div title="'.\Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLPNG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text).'</div>'.'<!-- #END :: '.\Smart::escape_html(\strtoupper($barcode_type)).' -->';
				break;
			case 'png': // raw png
				$out = self::getBarcodePNG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text); // needs header image/png on output
				break;
			case 'html-svg':
				$out = '<!-- '.\Smart::escape_html(\strtoupper($barcode_type).' ('.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.') :: '.\date('YmdHis')).' -->'.'<div title="'.\Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLSVG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text).'</div>'.'<!-- #END :: '.\Smart::escape_html(\strtoupper($barcode_type)).' -->';
				break;
			case 'svg':
				$out = self::getBarcodeSVG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text); // needs header image/svg on output
				break;
			default:
				// if invalid format is logged before
				return '';
		} //end switch
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
	public static function getBarcodeHTML($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!\is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$microtime = \microtime(true);
		//--
		$html = '';
		$html .= "\n".'<!-- Barcode1D / HTML -->';
		$html .= '<table border="0" cellspacing="0" cellpadding="0">';
		$html .= '<tr valign="top"><td align="center" style="font-size:1px; font-family="monospace">';
		$html .= '<table border="0" cellspacing="0" cellpadding="0" style="border-style:hidden; border-collapse:collapse;">';
		//-- print bars
		for($r=0; $r<$barcode_arr['maxh']; $r++) {
			//--
			$bh = \round(($h / $barcode_arr['maxh']), 3);
			//--
			$html .= "\n".'<tr height="'.$bh.'" style="height:'.$bh.'px;">';
			//--
			for($c=0; $c<$barcode_arr['maxw']; $c++) {
				//--
				$bw = \round(($barcode_arr['bcode'][$c]['w'] * $w), 3);
				//--
				if(($barcode_arr['bcode'][$c]['t']) AND ($r >= $barcode_arr['bcode'][$c]['p']) AND ($r < ($barcode_arr['bcode'][$c]['h'] + $barcode_arr['bcode'][$c]['p']))) {
					// draw a vertical bar
					$html .= '<td bgcolor="'.$color.'" width="'.$bw.'" height="'.$bh.'" style="font-size:1px;width:'.$bw.'px;height:'.$bh.'px;"></td>';
				} elseif($bw > 0) {
					$html .= '<td bgcolor="#FFFFFF" width="'.$bw.'" height="'.$bh.'" style="font-size:1px;width:'.$bw.'px;height:'.$bh.'px;"></td>';
				} //end if
				//--
			} //end for
			//--
			$html .= '</tr>';
			//--
		} //end for
		//--
		$html .= "\n".'</table>';
		$html .= '</td></tr>';
		if($display_text) {
			$html .= '<tr valign="top"><td align="center" style="font-size:10px; font-family="monospace">';
			$html .= '<font size="1" color="'.$color.'">'.\Smart::escape_html(\implode(' ', \str_split(\trim((string)$barcode_arr['code'])))).'</font>';
			$html .= '</td></tr>';
		} //end if
		$html .= '</table>';
		$html .= '<!-- END :: Barcode1D ['.(\microtime(true) - $microtime).'] -->'."\n";
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
	public static function getBarcodeEmbeddedHTMLSVG($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!\is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$microtime = \microtime(true);
		//--
		$html = '';
		$html .= "\n".'<!-- Barcode1D / SVG -->';
		$html .= '<img src="data:image/svg+xml;base64,'.\Smart::escape_html(\base64_encode(self::getBarcodeSVG($barcode_arr, $w, $h, $color, $display_text))).'" alt="BarCode1D-SVG">';
		$html .= '<!-- END :: Barcode1D ['.(\microtime(true) - $microtime).'] -->'."\n";
		//--
		return (string) $html;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as SVG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeSVG($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!\is_array($barcode_arr)) {
			return '<svg width="100" height="10"><text x="0" y="10" fill="#FF0000" font-size="9" font-family="monospace">[ INVALID BARCODE ]</text></svg>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$svg = '';
		//--
		if($display_text) {
			$textheight = 11;
			$codetext = "\n".'<text x="'.\round(\round(($barcode_arr['maxw'] * $w), 3)/2).'" y="'.($h + $textheight - 1).'" fill="'.$color.'" text-anchor="middle" font-size="10" font-family="monospace">'.\Smart::escape_html(\implode(' ', \str_split(\trim((string)$barcode_arr['code'])))).'</text>';
		} else {
			$textheight = 0;
			$codetext = '';
		} //end if else
		//--
		$svg .= '<'.'?'.'xml version="1.0" encoding="UTF-8" standalone="no" '.'?'.'>'."\n";
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
		$svg .= '<svg width="'.\round(($barcode_arr['maxw'] * $w), 3).'" height="'.($h + $textheight).'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
		$svg .= "\t".'<desc>'.\Smart::escape_html($barcode_arr['code']).'</desc>'."\n";
		$svg .= "\t".'<rect fill="#FFFFFF" x="0" y="0" width="'.\round(($barcode_arr['maxw'] * $w), 3).'" height="'.($h + $textheight).'" />'."\n";
		$svg .= "\t".'<g id="bars" fill="'.$color.'" stroke="none">'."\n";
		//-- print bars
		$x = 0;
		//--
		foreach($barcode_arr['bcode'] as $k => $v) {
			//--
			$bw = \round(($v['w'] * $w), 3);
			$bh = \round(($v['h'] * $h / $barcode_arr['maxh']), 3);
			//--
			if($v['t']) {
				//--
				$y = \round(($v['p'] * $h / $barcode_arr['maxh']), 3);
				//-- draw a vertical bar
				$svg .= "\t\t".'<rect x="'.$x.'" y="'.$y.'" width="'.$bw.'" height="'.$bh.'" />'."\n";
				//--
			} //end if
			//--
			$x += $bw;
			//--
		} //end foreach
		//--
		$svg .= "\t".'</g>'."\n";
		$svg .= $codetext;
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
	public static function getBarcodeEmbeddedHTMLPNG($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!\is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$microtime = \microtime(true);
		//--
		$html = '';
		$html .= "\n".'<!-- Barcode1D / PNG -->';
		$html .= '<img src="data:image/png;base64,'.\Smart::escape_html(\base64_encode(self::getBarcodePNG($barcode_arr, $w, $h, $color, $display_text))).'" alt="BarCode1D-PNG">';
		$html .= '<!-- END :: Barcode1D ['.(\microtime(true) - $microtime).'] -->'."\n";
		//--
		return (string) $html;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as PNG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodePNG($barcode_arr, $w=2, $h=30, $color=array(0,0,0), $display_text=false) {
		//--
		if(!\is_array($color)) {
			$color = (string) $color;
			$color = \trim(\str_replace('#', '', (string)$color));
			$color = array(\hexdec(\substr($color, 0, 2)), \hexdec(\substr($color, 2, 2)), \hexdec(\substr($color, 4, 2)));
		} //end if
		//--
		if(!\is_array($barcode_arr)) {
			//--
			$width = 100;
			$height = 10;
			//--
			$png = @\imagecreate($width, $height);
			$bgcol = @\imagecolorallocate($png, 250, 250, 250);
			$fgcol = @\imagecolorallocate($png, 255, 0, 0);
			@\imagestring($png, 1, 1, 1, "[ INVALID BARCODE ]", $fgcol);
			//--
		} else {
			//--
			$w = self::conformW($w);
			$h = self::conformH($h);
			//-- calculate image size
			$width = ($barcode_arr['maxw'] * $w);
			$height = $h;
			//--
			//$codetext = implode(' ', str_split(trim((string)$barcode_arr['code'])));
			$codetext = \trim($barcode_arr['code']);
			$fontnum = 2;
			$fontwidth = @\imagefontwidth($fontnum);
			$fontheight = @\imagefontheight($fontnum);
			$centerloc_text = (int) (($width) / 2) - (($fontwidth * \strlen($codetext)) / 2);
			if($display_text) {
				$textheight = $fontheight;
			} else {
				$textheight = 0;
			} //end if else
			//--
			$png = @\imagecreate($width, ($height + $textheight));
			$bgcol = @\imagecolorallocate($png, 255, 255, 255);
			$fgcol = @\imagecolorallocate($png, $color[0], $color[1], $color[2]);
			//-- print bars
			$x = 0;
			//-- for each row
			foreach($barcode_arr['bcode'] as $k => $v) {
				//--
				$bw = \round(($v['w'] * $w), 3);
				$bh = \round(($v['h'] * $h / $barcode_arr['maxh']), 3);
				//--
				if($v['t']) {
					//--
					$y = \round(($v['p'] * $h / $barcode_arr['maxh']), 3);
					//--
					@\imagefilledrectangle($png, (int)$x, (int)$y, (int)($x + $bw - 1), (int)($y + $bh - 1), $fgcol); // draw a vertical bar
					//--
				} //end if
				//--
				$x += $bw;
				//--
			} //end foreach
			//--
			if($display_text) {
				@\imagestring($png, $fontnum, $centerloc_text, $height, $codetext, $fgcol);
			} //end if
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


	private static function conformW($w) {
		//-- w must be between 1 and 16
		$w = (int) $w;
		if($w < 1) {
			$w = 1;
		} //end if
		if($w > 16) {
			$w = 16;
		} //end if
		//--
		return (int) $w;
		//--
	} //END FUNCTION


	private static function conformH($h) {
		//-- h must divide by 3
		$h = (int) $h;
		if($h < 9) {
			$h = 9;
		} //end if
		if($h > 243) {
			$h = 243;
		} //end if
		//--
		return (int) $h;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



// end of php code
