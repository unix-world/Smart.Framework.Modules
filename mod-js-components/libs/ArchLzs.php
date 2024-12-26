<?php
// Class: \SmartModExtLib\JsComponents\ArchLzs
// [Smart.Framework.Modules - JsComponents / LZS Archiver]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\JsComponents;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - LZS Archiver / Unarchiver
// DEPENDS:
//	* Smart::
//	* SmartHashCrypto::
// DEPENDS-EXT: MBString
//======================================================

// [REGEX-SAFE-OK]


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// fixes by unixman (see below)
// based on LZString v.1.3.6 (a free LZ based compression algorithm)
// this is intended for on-the-fly archive/unarchive not for storing (where ZLib is a better option)
// it compatible with Smart.Framework/JS/SmartJS_Archiver_LZS
// License: BSD
// (c) 2013-2020 unix-world.org : optimizations, fixes, unicode safe
// Original work by Tobias Neeb <tobias.neeb@gmail.com>

/**
 * Class: ArchLzs - Compress or Decompress a LZS archive.
 * This is very slow with large strings ... extremely slow !!!
 * This is why the max (hardcoded) length of the string it can compress/decompress is 4096 bytes
 * The purpose of this class is to compress/decompress cookies that can be shared also with Javascript
 * The Javascript version of this class is available in modules/mod-js-components/views/js/arch-lzs/arch-lzs.js
 *
 * <code>
 * // Usage example:
 * $myString = 'Some string to archive as LZS';
 * $archString = \SmartModExtLib\JsComponents\ArchLzs::compressToBase64($myString); // archive the string
 * $unarchString = \SmartModExtLib\JsComponents\ArchLzs::decompressFromBase64($archString); // unarchive it back
 * if((string)$unarchString !== (string)$myString) { // Test: check if unarchive is the same as archive
 *     @http_response_code(500);
 *     die('LZS Archive test Failed !');
 * } //end if
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	extensions: PHP MBString ; classes: Smart, SmartHashCrypto
 * @version 	v.20200121
 * @package 	modules:Archivers
 *
 */

final class ArchLzs {

	// ::


	//================================================================
	private static $keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	//================================================================


	//================================================================
	/**
	 * Compress a string to LZS + Base64
	 *
	 * @param		STRING		$input		The uncompressed string
	 * @return		STRING		The Base64 LZS Compressed String
	 *
	 */
	public static function compressToBase64($input) {
		//--
		$input = (string) $input;
		//--
		if((string)\trim((string)$input) == '') {
			return '';
		} //end if
		//--
		$input = (string) self::compressRawLZS($input);
		//--
		$output = '';
		//--
		$chr1 = false;
		$chr2 = false;
		$chr3 = false;
		$enc1 = false;
		$enc2 = false;
		$enc3 = false;
		$enc4 = false;
		//--
		$strlen = (int) \SmartUnicode::str_len($input); // must be unicode strlen !
		//--
		$i = 0;
		//--
		while($i < ($strlen * 2)) {
			//-- var_dump('-------'.$i.'<'.($strlen*2).'-------');
			if(($i % 2) === 0) {
				//--
				$chr1 = self::charCodeAt($input, (int)($i/2)) >> 8;
				$chr2 = self::charCodeAt($input, (int)($i/2)) & 255;
				//--
				if((($i/2)+1) < $strlen) {
					$chr3 = self::charCodeAt($input, (int)(($i/2)+1)) >> 8;
				} else {
					$chr3 = false;
				} //end if else
				//--
			} else {
				//--
				$chr1 = self::charCodeAt($input, (int)(($i-1)/2)) & 255;
				//--
				if((($i+1)/2) < $strlen) {
					$chr2 = self::charCodeAt($input, (int)(($i+1)/2)) >> 8;
					$chr3 = self::charCodeAt($input, (int)(($i+1)/2)) & 255;
				} else  {
					$chr2 = false;
					$chr3 = false;
				} //end if else
				//--
			} //end if else
			//--
			$i += 3;
			//--
			$enc1 = $chr1 >> 2;
			$enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
			$enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
			$enc4 = $chr3 & 63;
			//--
			if($chr2 === false) {
				$enc3 = 64;
				$enc4 = 64;
			} elseif($chr3 === false) {
				$enc4 = 64;
			} //end if else
			//--
			// \var_dump(array(
			//	$chr1,
			//	$chr2,
			//	$chr3,
			//	'-',
			//	$enc1.' = '.self::$keyStr{$enc1},
			//	$enc2.' = '.self::$keyStr{$enc2},
			//	$enc3.' = '.self::$keyStr{$enc3},
			//	$enc4.' = '.self::$keyStr{$enc4}
			//));
			//--
			$output .= (string) self::$keyStr[$enc1].self::$keyStr[$enc2].self::$keyStr[$enc3].self::$keyStr[$enc4];
			//--
		} //end while
		//--
		return (string) $output;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Decompress a Base64 + LZS compressed string
	 *
	 * @param		STRING		$input		The Base64 LZS Compressed String
	 * @return		STRING		The uncompressed string
	 *
	 */
	public static function decompressFromBase64($input) {
		//--
		$input = (string) $input;
		//--
		if((string)\trim((string)$input) == '') {
			return '';
		} //end if
		//--
		$output = '';
		//--
		$ol = 0;
		$output_ = NULL;
		$chr1 = NULL;
		$chr2 = NULL;
		$chr3 = NULL;
		$enc1 = NULL;
		$enc2 = NULL;
		$enc3 = NULL;
		$enc4 = NULL;
		//--
		$input = (string) \trim((string)\preg_replace('/[^A-Za-z0-9\+\/\=]/', '', (string)$input));
		if((string)$input == '') {
			return '';
		} //end if
		//--
		$i=0;
		while($i < \strlen($input)) { // no unicode strlen !
			//-- unixman: fix to avoid the strpos(): Empty needle (v.190115)
			$needle = '';
			//--
			$enc1 = false;
			$enc2 = false;
			$enc3 = false;
			$enc4 = false;
			//--
			$needle = (string) $input[$i++];
			if((string)$needle != '') {
				$enc1 = \strpos(self::$keyStr, $needle);
			} //end if
			$needle = (string) $input[$i++];
			if((string)$needle != '') {
				$enc2 = \strpos(self::$keyStr, $needle);
			} //end if
			$needle = (string) $input[$i++];
			if((string)$needle != '') {
				$enc3 = \strpos(self::$keyStr, $needle);
			} //end if
			$needle = (string) $input[$i++];
			if((string)$needle != '') {
				$enc4 = \strpos(self::$keyStr, $needle);
			} //end if
			//--
			$needle = '';
			//--
			$chr1 = ($enc1 << 2) | ($enc2 >> 4);
			$chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
			$chr3 = (($enc3 & 3) << 6) | $enc4;
			//--
			if(($ol % 2) == 0) {
				//--
				$output_ = $chr1 << 8;
				//--
				if($enc3 != 64) {
					//--
					$output .= self::fromCharCode($output_ | $chr2);
					//--
				} //end if
				//--
				if($enc4 != 64) {
					//--
					$output_ = $chr3 << 8;
					//--
				} //end if
				//--
			} else {
				//--
				$output = $output.self::fromCharCode($output_ | $chr1);
				//--
				if($enc3 != 64) {
					$output_ = $chr2 << 8;
				} //end if
				//--
				if($enc4 != 64) {
					//--
					$output .= self::fromCharCode($output_ | $chr3);
					//--
				} //end if
				//--
			} //end if else
			//--
			$ol += 3;
			//--
		} //end while
		//--
		return (string) self::decompressRawLZS($output);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Compress RAW LZS
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function compressRawLZS($uncompressed) {
		//--
		if((string)\trim((string)$uncompressed) == '') {
			return '';
		} //end if
		//--
		if(strlen((string)$uncompressed) > 4096) {
			\Smart::log_warning(__CLASS__.' # Compressing a string with a length of more than 4096 bytes is not supported');
			return '';
		} //end if
		//--
		$arch = (string) \strtoupper((string)\bin2hex((string)$uncompressed));
		//--
		return (string) self::RawDeflate($arch.'#CHECKSUM-SHA1#'.\SmartHashCrypto::sha1($arch)); // add sha1 checksum
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Decompress RAW LZS
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function decompressRawLZS($compressed) {
		//--
		if((string)\trim((string)$compressed) == '') {
			return '';
		} //end if
		//--
		if(strlen((string)$compressed) > 4096) {
			\Smart::log_warning(__CLASS__.' # Decompressing a string with a length of more than 4096 bytes is not supported');
			return '';
		} //end if
		//--
		$unarch = (string) \trim((string)self::RawInflate((string)$compressed));
		//-- checksum verification
		$arr = (array) \explode('#CHECKSUM-SHA1#', $unarch);
		$unarch 	= (string) \trim((string)(isset($arr[0]) ? $arr[0] : ''));
		$checksum 	= (string) \trim((string)(isset($arr[1]) ? $arr[1] : ''));
		//--
		if((string)\SmartHashCrypto::sha1($unarch) != (string)$checksum) {
			\Smart::log_notice(__METHOD__.'() :: Checksum Failed');
			return ''; // string is corrupted, avoid to return
		} //end if
		//--
		return (string) @\hex2bin(\strtolower($unarch));
		//--
	} //END FUNCTION
	//================================================================


	//############ PRIVATES


	//================================================================
	private static function fromCharCode() {
		//--
		$args = \func_get_args();
		//--
		return \array_reduce(\func_get_args(), function($a, $b){ $a .= self::utf8_chr($b); return $a; }); // mixed: array or null
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function utf8_chr($u) {
		//--
		return (string) \SmartUnicode::convert_charset('&#'.\intval($u).';', 'HTML-ENTITIES', (string)\SMART_FRAMEWORK_CHARSET);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function charCodeAt($str, $num) {
		//--
		return self::utf8_ord(self::utf8_charAt($str, $num)); // mixed
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function utf8_ord($ch) {
		//--
		$len = \strlen($ch);
		//--
		if($len <= 0) {
			return false;
		} //end if
		//--
		$h = \ord($ch[0]);
		//--
		if($h <= 0x7F) {
			return $h;
		} //end if
		if($h < 0xC2) {
			return false;
		} //end if
		if($h <= 0xDF && $len>1) {
			return ($h & 0x1F) <<  6 | (\ord($ch[1]) & 0x3F);
		} //end if
		if($h <= 0xEF && $len>2) {
			return ($h & 0x0F) << 12 | (\ord($ch[1]) & 0x3F) << 6 | (\ord($ch[2]) & 0x3F);
		} //end if
		if($h <= 0xF4 && $len>3) {
			return ($h & 0x0F) << 18 | (\ord($ch[1]) & 0x3F) << 12 | (\ord($ch[2]) & 0x3F) << 6 | (\ord($ch[3]) & 0x3F);
		} //end if
		//--
		return false; // mixed
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function utf8_charAt($str, $num) {
		//--
		return (string) \SmartUnicode::sub_str($str, $num, 1);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function writeBit($value, ArchLzsObjData $data) {
		//--
		$data->val = ($data->val << 1) | $value;
		//--
		if($data->position == 15) {
			//--
			$data->position = 0;
			$data->str .= self::fromCharCode($data->val);
			$data->val = 0;
			//--
		} else {
			//--
			$data->position++;
			//--
		} //end if else
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function writeBits($numbits, $value, ArchLzsObjData $data) {
		//--
		if(\is_string($value)) {
			//--
			$value = self::charCodeAt($value, 0);
			//--
		} //end if
		//--
		for($i = 0; $i < $numbits; $i++) {
			//--
			self::writeBit($value & 1, $data);
			//--
			$value = $value >> 1;
			//--
		} //end for
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function decrementEnlargeIn(ArchLzsObjContext $context) {
		//--
		$context->enlargeIn--;
		//--
		if($context->enlargeIn === 0) {
			$context->enlargeIn = \pow(2, $context->numBits);
			$context->numBits++;
		} //end if
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function produceW(ArchLzsObjContext $context) {
		//--
		if(\array_key_exists($context->w, $context->dictionaryToCreate)) {
			//--
			if(self::charCodeAt($context->w, 0) < 256) {
				self::writeBits($context->numBits, 0, $context->data);
				self::writeBits(8, self::utf8_charAt($context->w, 0), $context->data);
			} else {
				self::writeBits($context->numBits, 1, $context->data);
				self::writeBits(16, self::utf8_charAt($context->w, 0), $context->data);
			} //end if
			//--
			self::decrementEnlargeIn($context);
			//--
			unset($context->dictionaryToCreate[$context->w]);
			//--
		} else {
			//--
			self::writeBits($context->numBits, $context->dictionary[$context->w], $context->data);
			//--
		} //end if else
		//--
		self::decrementEnlargeIn($context);
		//--
		return $context; // object
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function RawDeflate($uncompressed) {
		//--
		$uncompressed = (string) $uncompressed;
		//--
		$context = new ArchLzsObjContext();
		//--
		for($i = 0; $i < \strlen($uncompressed); $i++) {
			//--
			$context->c = self::utf8_charAt($uncompressed, $i);
			//--
			if(!\array_key_exists($context->c, $context->dictionary)) {
				$context->dictionary[$context->c] = $context->dictSize++;
				$context->dictionaryToCreate[$context->c] = true;
			} //end if
			//--
			$context->wc = $context->w.$context->c;
			//--
			if(\array_key_exists($context->wc, $context->dictionary)) {
				$context->w = $context->wc;
			} else {
				self::produceW($context);
				$context->dictionary[$context->wc] = $context->dictSize++;
				$context->w = $context->c;
			} //end if else
			//--
		} //end for
		//--
		if($context->w !== '') {
			self::produceW($context);
		} //end if
		//--
		self::writeBits($context->numBits, 2, $context->data);
		//--
		$safe = 0;
		//--
		while(true) {
			//--
			$context->data->val = $context->data->val << 1;
			//--
			if($context->data->position == 15) {
				//--
				$context->data->str .= self::fromCharCode($context->data->val);
				//--
				break;
				//--
			} //end if
			//--
			$context->data->position++;
			//--
		} //end while
		//--
		return (string) $context->data->str;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function readBit(ArchLzsObjData $data) {
		//--
		$res = $data->val & $data->position;
		//--
		$data->position >>= 1;
		//--
		if($data->position == 0) {
			//--
			$data->position = 32768;
			$data->val = self::charCodeAt($data->str, $data->index++);
			//--
		} //end if
		//-- data.val = (data.val << 1); // this was not enabled in the original
		return $res > 0 ? 1 : 0; // 0/1
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function readBits($numBits, ArchLzsObjData $data) {
		//--
		$res = 0;
		//--
		$maxpower = \pow(2, $numBits);
		//--
		$power = 1;
		//--
		while($power != $maxpower) {
			//--
			$res |= self::readBit($data) * $power;
			//--
			$power <<= 1;
			//--
		} //end while
		//--
		return $res; // mixed
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function RawInflate($compressed) {
		//--
		$compressed = (string) $compressed;
		//--
		$dictionary = array(
			0 => 0,
			1 => 1,
			2 => 2
		);
		//--
		$next = null;
		$enlargeIn = 4;
		$dictSize = 4;
		$numBits = 3;
		$entry = '';
		$result = '';
		$w = null;
		$c = null;
		$errorCount = 0;
		$literal = null;
		$data = new ArchLzsObjData();
		//--
		$data->str = $compressed;
		$data->val = self::charCodeAt($compressed, 0);
		$data->position = 32768;
		$data->index = 1;
		//--
		switch(self::readBits(2, $data)) {
			case 0:
				$c = self::fromCharCode(self::readBits(8, $data));
				break;
			case 1:
				$c = self::fromCharCode(self::readBits(16, $data));
				break;
			case 2:
				return '';
		} //end switch
		//--
		$dictionary[3] = $c;
		$w = $result = $c;
		//--
		while(true) {
			//-- # fix by unixman (this portion was added) based on JS version to avoid Loop Hard Limit
			if($data->index > \strlen($data->str)) {
				return '';
			} //end if
			//-- #end fix
			$c = self::readBits($numBits, $data);
			//--
			switch($c) {
				case 0:
					//--
					/* fixed above: unixman, and no more necessary because actually this limits the archive size to 10k
					if($errorCount++ > 10000) { // this is perhaps no more necessary because is catched above with the fix (unixman)
						\Smart::log_warning('ERROR: Archiver/LZS/Decompress: Decode Loop Hard Limit (10.000) ...');
						return ''; // null (not null to sync with fixes from javascript)
					} //end if
					*/
					//--
					$c = self::fromCharCode(self::readBits(8, $data));
					//--
					$dictionary[$dictSize++] = $c;
					$c = $dictSize-1;
					$enlargeIn--;
					//--
					break;
				case 1:
					//--
					$c = self::fromCharCode(self::readBits(16, $data));
					//--
					$dictionary[$dictSize++] = $c;
					$c = $dictSize-1;
					$enlargeIn--;
					//--
					break;
				case 2:
					//--
					return $result;
					//--
			} //end switch
			//--
			if($enlargeIn === 0) {
				$enlargeIn = \pow(2, $numBits);
				$numBits++;
			} //end if
			//--
			if(\array_key_exists($c, $dictionary) && $dictionary[$c] !== false) {
				//--
				$entry = $dictionary[$c];
				//--
			} else {
				//--
				if($c === $dictSize) {
					//--
					$entry = $w.self::utf8_charAt($w, 0);
					//--
				} else {
					//--
					// \Smart::log_notice('ERROR: Archiver/LZS/Decompress: $c != $dictSize ('.$c.','.$dictSize.')');
					return null;
					//--
				} //end if else
				//--
			} //end if else
			//--
			$result .= $entry;
			//-- Add w+entry[0] to the dictionary.
			$dictionary[$dictSize++] = (string) $w.''.self::utf8_charAt($entry, 0);
			//--
			$enlargeIn--;
			//--
			$w = $entry;
			//--
			if($enlargeIn == 0) {
				$enlargeIn = \pow(2, $numBits);
				$numBits++;
			} //end if
			//--
		} //end while
		//--
		return $result;
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class LZS Archiver Obj Context
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20200121
 *
 */
final class ArchLzsObjContext {
	//--
	// ->
	//--
	public $c = '';
	public $w = '';
	public $wc = '';
	public $enlargeIn = 2;
	public $dictSize = 3;
	public $numBits = 2;
	public $data;
	public $dictionary = array();
	public $dictionaryToCreate = array();
	//--
	public function __construct() {
		//--
		$this->data = new ArchLzsObjData();
		//--
	} //END FUNCTION
	//--
} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class LZS Archiver Obj Data
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20200121
 *
 */
final class ArchLzsObjData {
	//--
	// ->
	//--
	public $str;
	public $val;
	public $position = 0;
	public $index = 1;
	//--
	public function __construct() {
		// nothing here ...
	} //END FUNCTION
	//--
} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
