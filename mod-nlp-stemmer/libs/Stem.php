<?php
// Class: \SmartModExtLib\NlpStemmer\Stem
// [Smart.Framework.Modules - NLP Stemmer]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\NlpStemmer;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


abstract class Stem implements \SmartModExtLib\NlpStemmer\Stemmer {


	protected static $vowels = [ 'a', 'e', 'i', 'o', 'u', 'y' ];

	/**
	 * helper, contains stringified list of vowels
	 * @var string
	 */
	protected $plainVowels;

	/**
	 * The word we are stemming
	 * @var string
	 */
	protected $word;

	/**
	 * The original word, use to check if word has been modified
	 * @var string
	 */
	protected $originalWord;

	/**
	 * RV value
	 * @var string
	 */
	protected $rv;

	/**
	 * RV index (based on the beginning of the word)
	 * @var integer
	 */
	protected $rvIndex;

	/**
	 * R1 value
	 * @var integer
	 */
	protected $r1;

	/**
	 * R1 index (based on the beginning of the word)
	 * @var int
	 */
	protected $r1Index;

	/**
	 * R2 value
	 * @var integer
	 */
	protected $r2;

	/**
	 * R2 index (based on the beginning of the word)
	 * @var int
	 */
	protected $r2Index;


	protected function inRv($position) {
		return ($position >= $this->rvIndex);
	} //END FUNCTION


	protected function inR1($position) {
		return ($position >= $this->r1Index);
	} //END FUNCTION



	protected function inR2($position) {
		return ($position >= $this->r2Index);
	} //END FUNCTION


	protected function searchIfInRv($suffixes) {
		return $this->search($suffixes, $this->rvIndex);
	} //END FUNCTION


	protected function searchIfInR1($suffixes) {
		return $this->search($suffixes, $this->r1Index);
	} //END FUNCTION


	protected function searchIfInR2($suffixes) {
		return $this->search($suffixes, $this->r2Index);
	} //END FUNCTION


	protected function search($suffixes, $offset=0) {
		$length = \SmartModExtLib\NlpStemmer\Utf8::strlen($this->word);
		if($offset > $length) {
			return false;
		} //end if
		foreach($suffixes as $suffixe) {
			if((($position = \SmartModExtLib\NlpStemmer\Utf8::strrpos($this->word, $suffixe, $offset)) !== false) && ((\SmartModExtLib\NlpStemmer\Utf8::strlen($suffixe)+$position) == $length)) {
				return $position;
			} //end if
		} //end foreach
		return false;
	} //END FUNCTION


	/**
	 * R1 is the region after the first non-vowel following a vowel, or the end of the word if there is no such non-vowel.
	 */
	protected function r1() {
		list($this->r1Index, $this->r1) = $this->rx($this->word);
	} //END FUNCTION


	/**
	 * R2 is the region after the first non-vowel following a vowel in R1, or the end of the word if there is no such non-vowel.
	 */
	protected function r2() {
		list($index, $value) = $this->rx($this->r1);
		$this->r2 = $value;
		$this->r2Index = $this->r1Index + $index;
	} //END FUNCTION


	/**
	 * Common function for R1 and R2
	 * Search the region after the first non-vowel following a vowel in $word, or the end of the word if there is no such non-vowel.
	 * R1 : $in = $this->word
	 * R2 : $in = R1
	 */
	protected function rx($in) {
		//--
		$length = \SmartModExtLib\NlpStemmer\Utf8::strlen($in);
		//-- defaults
		$value = '';
		$index = $length;
		//-- we search all vowels
		$vowels = array();
		for($i=0; $i<$length; $i++) {
			$letter = \SmartModExtLib\NlpStemmer\Utf8::substr($in, $i, 1);
			if(\in_array($letter, static::$vowels)) {
				$vowels[] = $i;
			} //end if
		} //end for
		//-- search the non-vowel following a vowel
		foreach($vowels as $position) {
			$after = $position + 1;
			$letter = \SmartModExtLib\NlpStemmer\Utf8::substr($in, $after, 1);
			if(!\in_array($letter, static::$vowels)) {
				$index = $after + 1;
				$value = \SmartModExtLib\NlpStemmer\Utf8::substr($in, ($after+1));
				break;
			} //end if
		} //end foreach
		//--
		return array($index, $value);
		//--
	} //END FUNCTION


	/**
	 * Used by spanish, italian, portuguese, etc (but not by french)
	 *
	 * If the second letter is a consonant, RV is the region after the next following vowel,
	 * or if the first two letters are vowels, RV is the region after the next consonant,
	 * and otherwise (consonant-vowel case) RV is the region after the third letter.
	 * But RV is the end of the word if these positions cannot be found.
	 */
	protected function rv() {
		//--
		$length = \SmartModExtLib\NlpStemmer\Utf8::strlen($this->word);
		$this->rv = '';
		$this->rvIndex = $length;
		if($length < 3) {
			return true;
		} //end if
		$first = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, 0, 1);
		$second = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, 1, 1);
		//-- If the second letter is a consonant, RV is the region after the next following vowel,
		if(!\in_array($second, static::$vowels)) {
			for($i=2; $i<$length; $i++) {
				$letter = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, $i, 1);
				if(\in_array($letter, static::$vowels)) {
					$this->rvIndex = $i + 1;
					$this->rv = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, ($i+1));
					return true;
				} //end if
			} //end for
		} //end if
		//-- or if the first two letters are vowels, RV is the region after the next consonant,
		if((\in_array($first, static::$vowels)) && (\in_array($second, static::$vowels))) {
			for($i=2; $i<$length; $i++) {
				$letter = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, $i, 1);
				if(!\in_array($letter, static::$vowels)) {
					$this->rvIndex = $i + 1;
					$this->rv = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, ($i+1));
					return true;
				} //end if
			} //end for
		} //end if
		//-- and otherwise (consonant-vowel case) RV is the region after the third letter.
		if((!\in_array($first, static::$vowels)) && (\in_array($second, static::$vowels))) {
			$this->rv = \SmartModExtLib\NlpStemmer\Utf8::substr($this->word, 3);
			$this->rvIndex = 3;
			return true;
		} //end if
		//--
	} //END FUNCTION


} //END CLASS


// #end

