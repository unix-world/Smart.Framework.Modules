<?php
// Class: \SmartModExtLib\LangDetect\LanguageNgrams
// Ngrams Language Detection :: Smart.Framework Module Library
// (c) 2008-present unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\LangDetect;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Language Detection - Ngrams PHP
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
//	* SmartFileSystem::
//	* SmartFileSysUtils::
//======================================================


//==================================================================================================
//================================================================================================== START CLASS
//==================================================================================================


// This class is based on the following project: PHP LanguageDetection
// https://github.com/patrickschur/language-detection
// (c) 2016-2017 Patrick Schur
// License: MIT


/**
 * Class LanguageNgrams
 *
 */

/**
 * Class: LanguageNgrams - provides a Language Detection utility based on Text N-Grams that can be used to validate Language / GetLanguage Confidence for a text.
 *
 * <code>
 *
 * $langtest = (new \SmartModExtLib\LangDetect\LanguageNgrams())->getLanguageConfidence('Your Text to Check Goes Here ...');
 *
 * </code>
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		By default will use the 1-3-930 NGrams. To use extended NGrams (Ex: 1-4-15k) see the local code examples.
 *
 * @depends 	classes: Smart, SmartUnicode, SmartFileSysUtils
 * @version 	v.20221219
 * @package 	modules:LanguageDetection
 *
 */
final class LanguageNgrams {

	/**
	 * @var int
	 */
	private $minLength = 1; // default is 1


	/**
	 * @var int
	 */
	private $maxLength = 3; // default is 3


	/**
	 * @var int
	 */
	private $maxNgrams = 930; // default is 310, but inefficient, thus increase 3x


	/**
	 * @var array
	 */
	private $tokens = [];


	/**
	 * Class Constructor
	 *
	 * @param STRING 		$ngrams_path 		The path to the resources or NULL if only need to train
	 * @param ARRAY 		$lang 				The Array of languages to detect for OR an empty array to try detect all available languages
	 */
	public function __construct($ngrams_path='modules/mod-lang-detect/libs/data-1-3-930', $lang=[]) {

		if($ngrams_path === null) {
			return;
		} //end if

		$ngrams_path = \SmartFileSysUtils::addPathTrailingSlash((string)$ngrams_path);

		$lang = (array) $lang;
		if(\Smart::array_size($lang) <= 0) {
			$arr_fs = (array) (new \SmartGetFileSystem(true))->get_storage((string)$ngrams_path, false, false, '');
			if(\Smart::array_size($arr_fs['list-dirs']) > 0) {
				$lang = (array) $arr_fs['list-dirs'];
			} //end if
			$arr_fs = array();
		} //end if
		//print_r($lang); die();

		if(\Smart::array_size($lang) > 0) {
			for($i=0; $i<\Smart::array_size($lang); $i++) {
				$json_file = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$ngrams_path.\Smart::safe_filename((string)$lang[$i])).\Smart::safe_filename((string)$lang[$i]).'.json';
				if(is_file($json_file)) {
					$json = (string) \SmartFileSystem::read((string)$json_file);
					if((string)$json != '') {
						$json = \Smart::json_decode((string)$json);
						if(\Smart::array_size($json) > 0) {
							if(\Smart::array_size($json[(string)$lang[$i]]) > 0) {
								$this->tokens += (array) $json;
							} //end if
						} //end if
					} //end if
				} //end if
			} //end for
		} //end if
		//print_r($this->tokens); die();

	} //END FUNCTION


	/**
	 * Detects and Get the Language Confidence information for the best detected Language (from the available list) for a given text
	 *
	 * @param STRING 		$str				The text to be checked
	 * @return ARRAY 							The detection result: [ service-available, lang-id, confidence-score, error-message ]
	 *
	 */
	public function getLanguageConfidence($str) {

		$arr = (array) $this->detect((string)$str);

		$errmsg = '';
		if(\Smart::array_size($arr) <= 0) {
			$errmsg = 'Language Detection Failed to find Language Data ...';
		} //end if else

		$langid = '';
		$score = -1;
		foreach($arr as $key => $val) {
			$langid = (string) $key;
			$score = $val;
			break;
		} //end foreach

		return (array) [
			'service-available' => (bool)   true, // this is always TRUE here, it is not a remote but internal service
			'lang-id' 			=> (string) substr((string)strtolower((string)trim((string)$langid)), 0, 2),
			'confidence-score' 	=> (string) \Smart::format_number_dec((float)$score, 5, '.', ''),
			'error-message' 	=> (string) $errmsg
		];

	} //END FUNCTION


	/**
	 * Detects the language from a given text string
	 *
	 * @access 		private
	 * @internal
	 *
	 * @param STRING 		$str 			The text string to detect
	 * @return ARRAY 						The detection results
	 */
	public function detect($str) {

		//$arx = [];
		$ngrams = (array) $this->getNgrams($str);
		//print_r($ngrams);
		//print_r($this->tokens);
		$result = [];
		if(count($ngrams) > 0) {
			foreach($this->tokens as $lang => $value) {
				$index = 0;
				$sum = 0;
				$value = array_flip($value);
				foreach($ngrams as $k => $v) {
					if(isset($value[$v])) {
						$index++;
						$x = $index - $value[$v];
						//-- fix by unixman (make compatible with both PHP 5.x and 7.x): Prior to PHP 7.0, shifting integers by values greater than or equal to the system long integer width, or by negative numbers, results in undefined behavior. In other words, if you're using PHP 5.x, don't shift more than 31 bits on a 32-bit system, and don't shift more than 63 bits on 64-bit system.
						//$y = $x >> ((PHP_INT_SIZE * 8));
						$y = $x >> ((PHP_INT_SIZE * 8) - 1);
						//$arx[] = $index.' @ '.$x.' # '.$y;
						//-- # end fix
						$sum += ($x + $y) ^ $y;
						continue;
					} //end if
					$sum += $this->maxNgrams;
					++$index;
				} //end foreach
				$calc = ($sum / ($this->maxNgrams * $index));
				if($calc > 1) {
					$calc = 1;
				} elseif($calc < 0) {
					$calc = 0;
				} //end if else
				$result[$lang] = 1 - $calc;
			} //end foreach
			//-- fix by unixman: to have the same results on PHP 5.x and 7.x
			//arsort($result, SORT_NUMERIC); // reverse sort array, numeric
			array_multisort(array_values($result), SORT_DESC, array_keys($result), SORT_ASC, $result);
			//-- #end fix
		} //end if
		//print_r($arx);

		return (array) $result;

	} //END FUNCTION


	/**
	 * Train a language resource from a given text string
	 *
	 * @access 		private
	 * @internal
	 *
	 * @param STRING 		$str 			The text string to detect
	 * @return ARRAY 						The detection results
	 */
	public function train($lang, $str) {

		return (string) \Smart::json_encode(
			[
				(string) $lang => (array) $this->getNgrams((string)$str)
			], // array
			false, // no pretty print
			true, // unescaped unicode
			false // no need for HTML Safe
		);

	} //END FUNCTION


	/**
	 * Calculate Ngrams for a text string
	 *
	 * @access 		private
	 * @internal
	 *
	 * @param STRING 		$str 			The text string to calculate Ngrams for
	 * @return ARRAY 						The Ngrams calculations results
	 */
	public function getNgrams($str) {

		$str = (string) str_replace(
			[
				'_', // special character used by tokenize
				'~',
				'@',
				'#',
				'^',
				'&',
				'*',
				'+',
				'=',
				'(',
				')',
				'[',
				']',
				'{',
				'}',
				';',
				':',
				'"',
				'.',
				'<',
				'>',
				',',
				'/',
				'!',
				'?'
			],
			' ',
			(string) $str
		); // replace non-alphabet characters

		$str = (string) \SmartUnicode::str_tolower((string)$str); // make all lowercase

		$tokens = [];

		foreach($this->tokenize($str) as $k => $word) {
			$l = \SmartUnicode::str_len($word);
			for($i=$this->minLength; $i<=$this->maxLength; ++$i) {
				//-- fix by unixman: this is more efficient on PHP than passing by reference ...
			/*	for($j=0; ($i+$j-1) < $l; ++$j, ++$tmp) {
					$tmp =& $tokens[$i][(string)\SmartUnicode::sub_str($word, $j, $i)];
				} //end for */
				if((!isset($tokens[$i])) OR (!\is_array($tokens[$i]))) {
					$tokens[$i] = [];
				} //end if
				for($j=0; ($i+$j-1)<$l; ++$j) {
					$the_key = (string) \SmartUnicode::sub_str($word, $j, $i);
					if(!\array_key_exists((string)$the_key, $tokens[$i])) {
						$tokens[$i][(string)$the_key] = 0;
					} //end if
					$tokens[$i][(string)$the_key]++;
				} //end for
				//--
			} //end for
		} //end foreach
		//print_r($tokens);
		if(!count($tokens)) {
			return [];
		} //end if

		foreach($tokens as $i => $token) {
			$sum = array_sum($token);
			foreach($token as $j => $value) {
				$tokens[$i][$j] = \Smart::format_number_dec($value / $sum, 12, '.', '');
			} //end foreach
		} //end foreach

		$tkns = (array) $tokens;
		$tokens = array(); // free mem
		foreach($tkns as $key => $val) {
			if(is_array($val)) {
				//$tokens = array_merge((array)$tokens, (array)$val);
				$tokens += (array) $val; // use array union to avoid re-index numeric keys if any
			} //end if
		} //end foreach
		$tkns = array(); // free mem
		unset($tokens['_']); // the tokenizer word limit char itself must be unset

		//-- fix by unixman: to have the same results on PHP 5.x and 7.x
		//arsort($tokens, SORT_NUMERIC);
		array_multisort(array_values($tokens), SORT_DESC, array_keys($tokens), SORT_ASC, $tokens);
		//print_r($tokens); //die();
		//-- #end fix

		return (array) array_slice(
			array_keys($tokens),
			0,
			$this->maxNgrams
		);

	} //END FUNCTION


	/**
	 * Set the Ngrams Minimum Length
	 *
	 * @param INTEGER+ 		$minLength 		Min Ngrams Length: Default is 1
	 * @return VOID
	 */
	public function setMinLength($minLength) {

		$minLength = (int) $minLength;
		if($minLength <= 0 || $minLength >= $this->maxLength) {
			\Smart::log_warning(__METHOD__.': $minLength must be greater than zero and less than $this->maxLength.');
			return;
		} //end if

		$this->minLength = $minLength;

	} //END FUNCTION


	/**
	 * Set the Ngrams Maximum Length
	 *
	 * @param INTEGER+ 		$maxLength 		Max Ngrams Length: Default is 3
	 * @return VOID
	 */
	public function setMaxLength($maxLength) {

		$maxLength = (int) $maxLength;
		if($maxLength <= $this->minLength) {
			\Smart::log_warning(__METHOD__.': $maxLength must be greater than $this->minLength.');
			return;
		} //end if

		$this->maxLength = $maxLength;

	} //END FUNCTION


	/**
	 * Set the Maximum (significant) Ngrams
	 *
	 * @param INTEGER+ 		$maxNgrams 		Max Ngrams: Default is 310
	 * @return VOID
	 */
	public function setMaxNgrams($maxNgrams) {

		$maxNgrams = (int) $maxNgrams;
		if($maxNgrams <= 100) {
			\Smart::log_warning(__METHOD__.': $maxNgrams must be at least 100.');
			return;
		} //end if

		$this->maxNgrams = $maxNgrams;

	} //END FUNCTION


	/**
	 * Tokenize a text string
	 *
	 * @param STRING $str
	 * @return ARRAY
	 */
	private function tokenize($str) {

		$str = (string) $str;

		return (array) array_map(function ($word) {
				return "_{$word}_";
			},
			preg_split('/[^\pL]+(?<![\x27\x60\x{2019}])/u', (string)$str, -1, PREG_SPLIT_NO_EMPTY)
		);

	} //END FUNCTION


} //END CLASS


/***** Sample Usage:

//--
//===== DETECT
//--
$lndet = new \SmartModExtLib\LangDetect\LanguageNgrams();
$lndet->setMaxNgrams(20000);
$arr = $lndet->detect(SmartFileSystem::read('ngrams-res/en/en.txt'));
print_r($arr); die();
//--

//--
//===== TRAIN
//--
$ngrams_path = 'modules/mod-lang-detect/libs/data-1-4-15k';
//--
$ngrams_path = \SmartFileSysUtils::addPathTrailingSlash((string)$ngrams_path);
$jsons = array();
$lang = array();
$arr_fs = (array) (new \SmartGetFileSystem(true))->get_storage((string)$ngrams_path, false, false, '');
if(\Smart::array_size($arr_fs['list-dirs']) > 0) {
	$lang = (array) $arr_fs['list-dirs'];
} //end if
$arr_fs = array();
//--
if(\Smart::array_size($lang) > 0) {
	for($i=0; $i<\Smart::array_size($lang); $i++) {
		//--
		$txt_file  = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$ngrams_path.\Smart::safe_filename((string)$lang[$i])).\Smart::safe_filename((string)$lang[$i]).'.txt';
		$json_file = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$ngrams_path.\Smart::safe_filename((string)$lang[$i])).\Smart::safe_filename((string)$lang[$i]).'.json';
		//--
		$lndet = new \SmartModExtLib\LangDetect\LanguageNgrams(null);
		$lndet->setMaxNgrams(15000);
		$lndet->setMinLength(1);
		$lndet->setMaxLength(4);
		$json_data = (string) $lndet->train((string)$lang[$i], \SmartFileSystem::read($txt_file));
		//--
		\SmartFileSystem::write($json_file, $json_data);
		$jsons[] = $json_file;
		//--
	} //end for
} //end if
//--
die('Training DONE for: '.$ngrams_path.'<pre>'.print_r($jsons,1).'</pre>');
//--

*****/


//==================================================================================================
//================================================================================================== START END
//==================================================================================================


// end of php code
