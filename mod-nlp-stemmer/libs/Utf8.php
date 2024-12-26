<?php
// Class: \SmartModExtLib\NlpStemmer\Utf8
// [Smart.Framework.Modules - NLP Stemmer]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\NlpStemmer;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * UTF8 helper functions
 *
 * @license    LGPL (http://www.gnu.org/copyleft/lesser.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @package Stato
 * @subpackage view
 *
 * Modified by unixman :: depends on MbString
 *
 */

final class Utf8 {

	/**
	 * UTF-8 lookup table for lower case accented letters
	 *
	 * This lookuptable defines replacements for accented characters from the ASCII-7
	 * range. This are lower case letters only.
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see    utf8_deaccent()
	 */
	private static $utf8_lower_accents = array(
		'à' => 'a', 'ô' => 'o', 'd' => 'd', '?' => 'f', 'ë' => 'e', 'š' => 's', 'o' => 'o',
		'ß' => 'ss', 'a' => 'a', 'r' => 'r', '?' => 't', 'n' => 'n', 'a' => 'a', 'k' => 'k',
		's' => 's', '?' => 'y', 'n' => 'n', 'l' => 'l', 'h' => 'h', '?' => 'p', 'ó' => 'o',
		'ú' => 'u', 'e' => 'e', 'é' => 'e', 'ç' => 'c', '?' => 'w', 'c' => 'c', 'õ' => 'o',
		'?' => 's', 'ø' => 'o', 'g' => 'g', 't' => 't', '?' => 's', 'e' => 'e', 'c' => 'c',
		's' => 's', 'î' => 'i', 'u' => 'u', 'c' => 'c', 'e' => 'e', 'w' => 'w', '?' => 't',
		'u' => 'u', 'c' => 'c', 'ö' => 'oe', 'è' => 'e', 'y' => 'y', 'a' => 'a', 'l' => 'l',
		'u' => 'u', 'u' => 'u', 's' => 's', 'g' => 'g', 'l' => 'l', 'ƒ' => 'f', 'ž' => 'z',
		'?' => 'w', '?' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', '?' => 'd', 't' => 't',
		'r' => 'r', 'ä' => 'ae', 'í' => 'i', 'r' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o',
		'e' => 'e', 'ñ' => 'n', 'n' => 'n', 'h' => 'h', 'g' => 'g', 'd' => 'd', 'j' => 'j',
		'ÿ' => 'y', 'u' => 'u', 'u' => 'u', 'u' => 'u', 't' => 't', 'ý' => 'y', 'o' => 'o',
		'â' => 'a', 'l' => 'l', '?' => 'w', 'z' => 'z', 'i' => 'i', 'ã' => 'a', 'g' => 'g',
		'?' => 'm', 'o' => 'o', 'i' => 'i', 'ù' => 'u', 'i' => 'i', 'z' => 'z', 'á' => 'a',
		'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',
	);

	/**
	 * UTF-8 lookup table for upper case accented letters
	 *
	 * This lookuptable defines replacements for accented characters from the ASCII-7
	 * range. This are upper case letters only.
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see    utf8_deaccent()
	 */
	private static $utf8_upper_accents = array(
		'À' => 'A', 'Ô' => 'O', 'D' => 'D', '?' => 'F', 'Ë' => 'E', 'Š' => 'S', 'O' => 'O',
		'A' => 'A', 'R' => 'R', '?' => 'T', 'N' => 'N', 'A' => 'A', 'K' => 'K',
		'S' => 'S', '?' => 'Y', 'N' => 'N', 'L' => 'L', 'H' => 'H', '?' => 'P', 'Ó' => 'O',
		'Ú' => 'U', 'E' => 'E', 'É' => 'E', 'Ç' => 'C', '?' => 'W', 'C' => 'C', 'Õ' => 'O',
		'?' => 'S', 'Ø' => 'O', 'G' => 'G', 'T' => 'T', '?' => 'S', 'E' => 'E', 'C' => 'C',
		'S' => 'S', 'Î' => 'I', 'U' => 'U', 'C' => 'C', 'E' => 'E', 'W' => 'W', '?' => 'T',
		'U' => 'U', 'C' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Y' => 'Y', 'A' => 'A', 'L' => 'L',
		'U' => 'U', 'U' => 'U', 'S' => 'S', 'G' => 'G', 'L' => 'L', 'ƒ' => 'F', 'Ž' => 'Z',
		'?' => 'W', '?' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', '?' => 'D', 'T' => 'T',
		'R' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'R' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O',
		'E' => 'E', 'Ñ' => 'N', 'N' => 'N', 'H' => 'H', 'G' => 'G', 'Ð' => 'D', 'J' => 'J',
		'Ÿ' => 'Y', 'U' => 'U', 'U' => 'U', 'U' => 'U', 'T' => 'T', 'Ý' => 'Y', 'O' => 'O',
		'Â' => 'A', 'L' => 'L', '?' => 'W', 'Z' => 'Z', 'I' => 'I', 'Ã' => 'A', 'G' => 'G',
		'?' => 'M', 'O' => 'O', 'I' => 'I', 'Ù' => 'U', 'I' => 'I', 'Z' => 'Z', 'Á' => 'A',
		'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae',
	);

	/**
	 * UTF-8 Case lookup table
	 *
	 * This lookuptable defines the upper case letters to their correspponding
	 * lower case letter in UTF-8
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 */
	private static $utf8_lower_to_upper = array(
		0x0061=>0x0041, 0x03C6=>0x03A6, 0x0163=>0x0162, 0x00E5=>0x00C5, 0x0062=>0x0042,
		0x013A=>0x0139, 0x00E1=>0x00C1, 0x0142=>0x0141, 0x03CD=>0x038E, 0x0101=>0x0100,
		0x0491=>0x0490, 0x03B4=>0x0394, 0x015B=>0x015A, 0x0064=>0x0044, 0x03B3=>0x0393,
		0x00F4=>0x00D4, 0x044A=>0x042A, 0x0439=>0x0419, 0x0113=>0x0112, 0x043C=>0x041C,
		0x015F=>0x015E, 0x0144=>0x0143, 0x00EE=>0x00CE, 0x045E=>0x040E, 0x044F=>0x042F,
		0x03BA=>0x039A, 0x0155=>0x0154, 0x0069=>0x0049, 0x0073=>0x0053, 0x1E1F=>0x1E1E,
		0x0135=>0x0134, 0x0447=>0x0427, 0x03C0=>0x03A0, 0x0438=>0x0418, 0x00F3=>0x00D3,
		0x0440=>0x0420, 0x0454=>0x0404, 0x0435=>0x0415, 0x0449=>0x0429, 0x014B=>0x014A,
		0x0431=>0x0411, 0x0459=>0x0409, 0x1E03=>0x1E02, 0x00F6=>0x00D6, 0x00F9=>0x00D9,
		0x006E=>0x004E, 0x0451=>0x0401, 0x03C4=>0x03A4, 0x0443=>0x0423, 0x015D=>0x015C,
		0x0453=>0x0403, 0x03C8=>0x03A8, 0x0159=>0x0158, 0x0067=>0x0047, 0x00E4=>0x00C4,
		0x03AC=>0x0386, 0x03AE=>0x0389, 0x0167=>0x0166, 0x03BE=>0x039E, 0x0165=>0x0164,
		0x0117=>0x0116, 0x0109=>0x0108, 0x0076=>0x0056, 0x00FE=>0x00DE, 0x0157=>0x0156,
		0x00FA=>0x00DA, 0x1E61=>0x1E60, 0x1E83=>0x1E82, 0x00E2=>0x00C2, 0x0119=>0x0118,
		0x0146=>0x0145, 0x0070=>0x0050, 0x0151=>0x0150, 0x044E=>0x042E, 0x0129=>0x0128,
		0x03C7=>0x03A7, 0x013E=>0x013D, 0x0442=>0x0422, 0x007A=>0x005A, 0x0448=>0x0428,
		0x03C1=>0x03A1, 0x1E81=>0x1E80, 0x016D=>0x016C, 0x00F5=>0x00D5, 0x0075=>0x0055,
		0x0177=>0x0176, 0x00FC=>0x00DC, 0x1E57=>0x1E56, 0x03C3=>0x03A3, 0x043A=>0x041A,
		0x006D=>0x004D, 0x016B=>0x016A, 0x0171=>0x0170, 0x0444=>0x0424, 0x00EC=>0x00CC,
		0x0169=>0x0168, 0x03BF=>0x039F, 0x006B=>0x004B, 0x00F2=>0x00D2, 0x00E0=>0x00C0,
		0x0434=>0x0414, 0x03C9=>0x03A9, 0x1E6B=>0x1E6A, 0x00E3=>0x00C3, 0x044D=>0x042D,
		0x0436=>0x0416, 0x01A1=>0x01A0, 0x010D=>0x010C, 0x011D=>0x011C, 0x00F0=>0x00D0,
		0x013C=>0x013B, 0x045F=>0x040F, 0x045A=>0x040A, 0x00E8=>0x00C8, 0x03C5=>0x03A5,
		0x0066=>0x0046, 0x00FD=>0x00DD, 0x0063=>0x0043, 0x021B=>0x021A, 0x00EA=>0x00CA,
		0x03B9=>0x0399, 0x017A=>0x0179, 0x00EF=>0x00CF, 0x01B0=>0x01AF, 0x0065=>0x0045,
		0x03BB=>0x039B, 0x03B8=>0x0398, 0x03BC=>0x039C, 0x045C=>0x040C, 0x043F=>0x041F,
		0x044C=>0x042C, 0x00FE=>0x00DE, 0x00F0=>0x00D0, 0x1EF3=>0x1EF2, 0x0068=>0x0048,
		0x00EB=>0x00CB, 0x0111=>0x0110, 0x0433=>0x0413, 0x012F=>0x012E, 0x00E6=>0x00C6,
		0x0078=>0x0058, 0x0161=>0x0160, 0x016F=>0x016E, 0x03B1=>0x0391, 0x0457=>0x0407,
		0x0173=>0x0172, 0x00FF=>0x0178, 0x006F=>0x004F, 0x043B=>0x041B, 0x03B5=>0x0395,
		0x0445=>0x0425, 0x0121=>0x0120, 0x017E=>0x017D, 0x017C=>0x017B, 0x03B6=>0x0396,
		0x03B2=>0x0392, 0x03AD=>0x0388, 0x1E85=>0x1E84, 0x0175=>0x0174, 0x0071=>0x0051,
		0x0437=>0x0417, 0x1E0B=>0x1E0A, 0x0148=>0x0147, 0x0105=>0x0104, 0x0458=>0x0408,
		0x014D=>0x014C, 0x00ED=>0x00CD, 0x0079=>0x0059, 0x010B=>0x010A, 0x03CE=>0x038F,
		0x0072=>0x0052, 0x0430=>0x0410, 0x0455=>0x0405, 0x0452=>0x0402, 0x0127=>0x0126,
		0x0137=>0x0136, 0x012B=>0x012A, 0x03AF=>0x038A, 0x044B=>0x042B, 0x006C=>0x004C,
		0x03B7=>0x0397, 0x0125=>0x0124, 0x0219=>0x0218, 0x00FB=>0x00DB, 0x011F=>0x011E,
		0x043E=>0x041E, 0x1E41=>0x1E40, 0x03BD=>0x039D, 0x0107=>0x0106, 0x03CB=>0x03AB,
		0x0446=>0x0426, 0x00FE=>0x00DE, 0x00E7=>0x00C7, 0x03CA=>0x03AA, 0x0441=>0x0421,
		0x0432=>0x0412, 0x010F=>0x010E, 0x00F8=>0x00D8, 0x0077=>0x0057, 0x011B=>0x011A,
		0x0074=>0x0054, 0x006A=>0x004A, 0x045B=>0x040B, 0x0456=>0x0406, 0x0103=>0x0102,
		0x03BB=>0x039B, 0x00F1=>0x00D1, 0x043D=>0x041D, 0x03CC=>0x038C, 0x00E9=>0x00C9,
		0x00F0=>0x00D0, 0x0457=>0x0407, 0x0123=>0x0122,
	);


	/**
	 * Checks if a string contains 7bit ASCII only
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 */
	public static function is_ascii(string $str) {
		for($i=0; $i<\strlen($str); $i++){
			if(\ord($str[$i]) > 127) return false;
		} //end for
		return true;
	} //END FUNCTION


	/**
	 * Strips all highbyte chars
	 *
	 * Returns a pure ASCII7 string
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 */
	public static function strip(string $str) {
		$ascii = '';
		for($i=0; $i<\strlen($str); $i++){
			if(\ord($str[$i]) < 128){
				$ascii .= $str[$i];
			} //end if
		} //end for
		return $ascii;
	} //END FUNCTION


	/**
	 * Tries to detect if a string is in Unicode encoding
	 *
	 * @author <bmorel@ssi.fr>
	 * @link   http://www.php.net/manual/en/function.utf8-encode.php
	 */
	public static function check(string $str) {
		for($i=0; $i<\strlen($str); $i++) {
			if(\ord($str[$i]) < 0x80) continue; # 0bbbbbbb
			elseif((\ord($str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
			elseif((\ord($str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
			elseif((\ord($str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
			elseif((\ord($str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
			elseif((\ord($str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
			else return false; # Does not match any model
			for($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
				if((++$i == \strlen($str)) || ((\ord($str[$i]) & 0xC0) != 0x80)) {
					return false;
				} //end if
			} //end for
		} //end for
		return true;
	} //END FUNCTION


	/**
	 * Unicode aware replacement for strlen()
	 *
	 * utf8_decode() converts characters that are not in ISO-8859-1
	 * to '?', which, for the purpose of counting, is alright - It's
	 * even faster than mb_strlen.
	 *
	 * @author <chernyshevsky at hotmail dot com>
	 * @see    strlen()
	 * @see    utf8_decode()
	 */
	public static function strlen(string $string) {
	//	return \strlen(\utf8_decode($string)); // utf8_decode() is deprecated since PHP 8.2
		return \strlen(\mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8')); // fix by unixman
	} //END FUNCTION


	/**
	 * Unicode aware replacement for substr()
	 *
	 * @author lmak at NOSPAM dot iti dot gr
	 * @link   http://www.php.net/manual/en/function.substr.php
	 * @see    substr()
	 */
	public static function substr(string $str, int $start, $length=null) {
		if($length != null) {
			return (string) \mb_substr((string)$str, (int)$start, (int)$length);
		} else {
			return (string) \mb_substr((string)$str, (int)$start);
		} //end if else
	} //END FUNCTION


	/**
	 * Unicode aware replacement for substr_replace()
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see    substr_replace()
	 */
	public static function substr_replace($string, $replacement, $start , $length=null) {
		return \substr_replace($string, $replacement, $start , $length);
	} //END FUNCTION


	/**
	 * Unicode aware replacement for strreplace()
	 *
	 * @todo   support PHP5 count (fourth arg)
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 * @see    strreplace();
	 */
	public static function str_replace($s, $r, $str) {
		return \str_replace($s, $r, $str);
	} //END FUNCTION


	/**
	 * This is a unicode aware replacement for strtolower()
	 *
	 * Uses mb_string extension if available
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see    strtolower()
	 * @see    utf8_strtoupper()
	 */
	public static function strtolower($string) {
		return \mb_strtolower($string,'utf-8');
	} //END FUNCTION


	/**
	 * This is a unicode aware replacement for strtoupper()
	 *
	 * Uses mb_string extension if available
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see    strtoupper()
	 * @see    utf8_strtoupper()
	 */
	public static function strtoupper($string) {
		return \mb_strtoupper($string,'utf-8');
	} //END FUNCTION


	/**
	 * Replace accented UTF-8 characters by unaccented ASCII-7 equivalents
	 *
	 * Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
	 * letters. Default is to deaccent both cases ($case = 0)
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 */
	public static function deaccent($string,$case=0) {
		if($case <= 0){
			//global $utf8_lower_accents;
			$string = \str_replace(\array_keys(self::$utf8_lower_accents), \array_values(self::$utf8_lower_accents), $string);
		} //end if
		if($case >= 0){
			//global $utf8_upper_accents;
			$string = \str_replace(\array_keys(self::$utf8_upper_accents), \array_values(self::$utf8_upper_accents), $string);
		} //end if
		return $string;
	} //END FUNCTION


	/**
	 * This is an Unicode aware replacement for strpos
	 *
	 * Uses mb_string extension if available
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 * @see    strpos()
	 */
	public static function strpos($haystack, $needle, $offset=0) {
		return \mb_strpos($haystack, $needle, $offset, 'utf-8');
	} //END FUNCTION


	/**
	 * This is an Unicode aware replacement for strrpos
	 *
	 * Uses mb_string extension if available
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 * @see    strpos()
	 */
	public static function strrpos($haystack, $needle, $offset=0) {
		return \mb_strrpos($haystack, $needle, $offset, 'utf-8');
	} //END FUNCTION


	/**
	 * This function returns any UTF-8 encoded text as a list of
	 * Unicode values:
	 *
	 * @author Scott Michael Reynen <scott@randomchaos.com>
	 * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
	 * @see    unicode_to_utf8()
	 */
	public static function utf8_to_unicode(&$str) {
		$unicode = array();
		$values = array();
		$looking_for = 1;
		for($i = 0; $i < \strlen($str); $i++ ) {
			$this_value = \ord($str[$i]);
			if($this_value < 128) {
				$unicode[] = $this_value;
			} else {
				if(count($values) == 0) {
					$looking_for = ($this_value < 224 ) ? 2 : 3;
				} //end if
				$values[] = $this_value;
				if(count($values) == $looking_for) {
					$number = ($looking_for == 3) ? (($values[0] % 16 ) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64) : (($values[0] % 32) * 64) + ($values[1] % 64);
					$unicode[] = $number;
					$values = array();
					$looking_for = 1;
				} //end if
			} //end if else
		} //end for
		return $unicode;
	} //END FUNCTION


	/**
	 * This function converts a Unicode array back to its UTF-8 representation
	 *
	 * @author Scott Michael Reynen <scott@randomchaos.com>
	 * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
	 * @see    utf8_to_unicode()
	 */
	public static function unicode_to_utf8( &$str ) {
		if(!\is_array($str)) return '';
		$utf8 = '';
		foreach($str as $kk => $unicode) {
			if($unicode < 128) {
				$utf8.= \chr($unicode);
			} elseif($unicode < 2048) {
				$utf8.= \chr( 192 +  ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= \chr( 128 + ( $unicode % 64 ) );
			} else {
				$utf8.= \chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
				$utf8.= \chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= \chr( 128 + ( $unicode % 64 ) );
			} //end if else
		} //end foreach
		return $utf8;
	} //END FUNCTION


} //END CLASS

// #end

