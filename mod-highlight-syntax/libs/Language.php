<?php
// PHP Syntax Highlight for Smart.Framework
// Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\HighlightSyntax;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//----------------------------------------------------------------------
// This class is based on scrivo/highlight.php v.9.15.10.0
// https://github.com/scrivo/highlight.php
//
// Copyright (c)
// - 2006-2013, Ivan Sagalaev (maniacsoftwaremaniacs.org), highlight.js (original author)
// - 2013-2019, Geert Bergman (geertscrivo.nl), highlight.php
// - 2014       Daniel Lynge, highlight.php (contributor)
// - 2019-2020  unixman (unix-world.org)
// License: BSD
//
//----------------------------------------------------------------------

// [PHP8]

/**
 * Class: \SmartModExtLib\HighlightSyntax\Language
 *
 * @access 		private
 * @internal
 *
 * @depends 	\stdClass, \Smart, \SmartUnicode, SmartFileSysUtils, \SmartFileSystem, \SmartModExtLib\HighlightSyntax\JsonRef
 * @version 	v.20241216
 * @package 	modules:HighlightSyntax
 *
 */
class Language {

	// ->


	public $disableAutodetect = false;
	public $caseInsensitive = false;
	public $aliases = null;
	public $name = null;
	public $mode = null;

	public function __construct($lang) {
		//--
		$filePath = (string) \Smart::safe_pathname(\SmartModExtLib\HighlightSyntax\Languages::DIR_OF_LANGUAGES.\Smart::safe_filename($lang, '-').'.json');
		//--
		if(!\SmartFileSystem::is_type_file($filePath)) {
			\Smart::raise_error(__METHOD__.' :: Data File Not Found for language: '.$lang.' :: '.$filePath);
			return null;
		} //end if
		if(!\SmartFileSystem::have_access_read($filePath)) {
			\Smart::raise_error(__METHOD__.' :: Cannot Read Data File for language: '.$lang.' :: '.$filePath);
			return null;
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)$filePath)) {
			\Smart::raise_error(__METHOD__.' :: Invalid or Unsafe File Path for language: '.$lang.' :: '.$filePath);
			return;
		} //end if
		//--
		$json = (string) \SmartFileSystem::read($filePath);
		//--
		$this->mode = \Smart::json_decode($json, false); // return object
		$this->name = (string) $lang;
		$this->aliases = isset($this->mode->aliases) ? $this->mode->aliases : null;
		$this->caseInsensitive = isset($this->mode->case_insensitive) ? $this->mode->case_insensitive : false;
		$this->disableAutodetect = isset($this->mode->disableAutodetect) ? $this->mode->disableAutodetect : false;
		//--
	} //END FUNCTION


	public function compile() {
		//--
		if(!isset($this->mode->compiled)) {
			//--
			$jr = new \SmartModExtLib\HighlightSyntax\JsonRef();
			$this->mode = $jr->process($this->mode);
			$jr = null;
			//--
			$this->compileMode($this->mode);
			//--
		} //end if
		//--
	} //END FUNCTION


	private function complete(&$e) {
		//--
		if(!isset($e)) {
			$e = new \stdClass();
		} //end if
		//--
		$patch = array(
			'begin' => true,
			'end' => true,
			'lexemes' => true,
			'illegal' => true,
		);
		//--
		$def = array(
			'begin' => '',
			'beginRe' => '',
			'beginKeywords' => '',
			'excludeBegin' => '',
			'returnBegin' => '',
			'end' => '',
			'endRe' => '',
			'endSameAsBegin' => '',
			'endsParent' => '',
			'endsWithParent' => '',
			'excludeEnd' => '',
			'returnEnd' => '',
			'starts' => '',
			'terminators' => '',
			'terminatorEnd' => '',
			'lexemes' => '',
			'lexemesRe' => '',
			'illegal' => '',
			'illegalRe' => '',
			'className' => '',
			'contains' => array(),
			'keywords' => null,
			'subLanguage' => null,
			'subLanguageMode' => '',
			'compiled' => false,
			'relevance' => 1,
			'skip' => false,
		);
		//--
		foreach($patch as $k => $v) {
			if(isset($e->$k)) {
				$e->$k = (string) \str_replace('\\/', '/', (string)$e->$k);
				$e->$k = (string) \str_replace('/', '\\/', (string)$e->$k);
			} //end if
		} //end foreach
		//--
		foreach($def as $k => $v) {
			if(!isset($e->$k) && \is_object($e)) {
				$e->$k = $v;
			} //end if
		} //end foreach
		//--
	} //END FUNCTION


	private function langRe($value, $global = false) {
		//-- PCRE allows us to change the definition of 'new line.' The `(*ANYCRLF)` matches `\r`, `\n`, and `\r\n` for `$` https://www.pcre.org/original/doc/html/pcrepattern.html
		return '/(*ANYCRLF)'.$value.'/um'.($this->caseInsensitive ? 'i' : '');
		//--
	} //END FUNCTION


	private function processKeyWords($kw) {
		//--
		if(\is_string($kw)) {
			//--
			if($this->caseInsensitive) {
				$kw = (string) \SmartUnicode::str_tolower((string)$kw);
			} //end if
			//--
			$kw = array('keyword' => (array)\explode(' ', (string)$kw));
			//--
		} elseif(\is_array($kw) || \is_object($kw)) {
			//--
			foreach($kw as $cls => $vl) {
				if(!\is_array($vl)) {
					if($this->caseInsensitive) {
						$vl = (string) \SmartUnicode::str_tolower((string)$vl);
					} //end if
					$kw->$cls = (array) \explode(' ', (string)$vl);
				} //end if
			} //end foreach
			//--
		} //end if else
		//--
		return $kw;
		//--
	} //END FUNCTION


	private function inherit() {
		//--
		$result = new \stdClass();
		$objects = (array) \func_get_args();
		$parent = (array) \array_shift($objects);
		//--
		foreach($parent as $key => $value) {
			$result->{$key} = $value;
		} //end foreach
		//--
		foreach($objects as $object) {
			foreach ($object as $key => $value) {
				$result->{$key} = $value;
			} //end foreach
		} //end foreach
		//--
		return $result;
		//--
	} //END FUNCTION


	private function expandMode($mode) {
		//--
		if(isset($mode->variants) && !isset($mode->cachedVariants)) {
			$mode->cachedVariants = array();
			foreach($mode->variants as $variant) {
				$mode->cachedVariants[] = $this->inherit($mode, array('variants' => null), $variant);
			} //end foreach
		} //end if
		//--
		if(isset($mode->cachedVariants)) {
			return $mode->cachedVariants;
		} //end if
		//--
		if(isset($mode->endsWithParent) && $mode->endsWithParent) {
			return array($this->inherit($mode));
		} //end if
		//--
		return array($mode);
		//--
	} //END FUNCTION


	/**
	 * joinRe logically computes regexps.join(separator), but fixes the
	 * backreferences so they continue to match.
	 *
	 * @param array  $regexps
	 * @param string $separator
	 *
	 * @return string
	 */
	private function joinRe($regexps, $separator) {
		//--
		// backreferenceRe matches an open parenthesis or backreference. To avoid
		// an incorrect parse, it additionally matches the following:
		// - [...] elements, where the meaning of parentheses and escapes change
		// - other escape sequences, so we do not misparse escape sequences as
		//   interesting elements
		// - non-matching or lookahead parentheses, which do not capture. These
		//   follow the '(' with a '?'.
		//--
		$backreferenceRe = '#\[(?:[^\\\\\]]|\\\.)*\]|\(\??|\\\([1-9][0-9]*)|\\\.#';
		$numCaptures = 0;
		$ret = '';
		//--
		$strLen = (int) \count($regexps);
		for($i=0; $i<$strLen; ++$i) {
			$offset = $numCaptures;
			$re = $regexps[$i];
			if($i > 0) {
				$ret .= $separator;
			} //end if
			while((int)\strlen($re) > 0) {
				$matches = array();
				$matchFound = \preg_match($backreferenceRe, $re, $matches, \PREG_OFFSET_CAPTURE);
				if($matchFound === 0) {
					$ret .= (string) $re;
					break;
				} //end if
				//-- PHP aliases to match the JS naming conventions
				$match = $matches[0];
				$index = $match[1];
				//--
				$ret .= (string) \substr((string)$re, 0, (int)$index);
				$re = (string) \substr((string)$re, (int)((int)$index + (int)\strlen($match[0])));
				if((string)\substr((string)$match[0], 0, 1) === '\\' && isset($matches[1])) {
					//-- Adjust the backreference.
					$ret .= '\\'.\strval(\intval($matches[1][0]) + $offset);
					//--
				} else {
					//--
					$ret .= (string) $match[0];
					if((string)$match[0] == '(') {
						++$numCaptures;
					} //end if
					//--
				} //end if else
				//--
			} //end while
			//--
		} //end for
		//--
		return $ret;
		//--
	} //END FUNCTION


	private function compileMode($mode, $parent=null) {
		//--
		if(isset($mode->compiled)) {
			return;
		} //end if
		//--
		$this->complete($mode);
		$mode->compiled = true;
		//--
		$mode->keywords = $mode->keywords ? $mode->keywords : $mode->beginKeywords;
		//--
		/* Note: JsonRef method creates different references as those in the
		 * original source files. Two modes may refer to the same keywords
		 * set, so only testing if the mode has keywords is not enough: the
		 * mode's keywords might be compiled already, so it is necessary
		 * to do an 'is_array' check.
		 */
		if($mode->keywords && !\is_array($mode->keywords)) {
			//--
			$compiledKeywords = array();
			//--
			$mode->lexemesRe = $this->langRe($mode->lexemes ? $mode->lexemes : '\w+', true);
			//--
			foreach($this->processKeyWords($mode->keywords) as $clsNm => $dat) {
				if(!\is_array($dat)) {
					$dat = array($dat);
				} //end if
				foreach($dat as $kw) {
					$pair = (array) \explode('|', (string)$kw);
					if(!\array_key_exists(0, $pair)) {
						$pair[0] = null;
					} //end if
					if(!\array_key_exists(1, $pair)) {
						$pair[1] = null;
					} //end if
					$compiledKeywords[$pair[0]] = array($clsNm, isset($pair[1]) ? \intval($pair[1]) : 1);
				} //end foreach
			} //end foreach
			//--
			$mode->keywords = $compiledKeywords;
			//--
		} //end if
		//--
		if($parent) {
			if($mode->beginKeywords) {
				$mode->begin = '\\b('.\implode('|', (array)\explode(' ', (string)$mode->beginKeywords)).')\\b';
			} //end if
			if(!$mode->begin) {
				$mode->begin = '\B|\b';
			} //end if
			$mode->beginRe = $this->langRe($mode->begin);
			if($mode->endSameAsBegin) {
				$mode->end = $mode->begin;
			} //end if
			if(!$mode->end && !$mode->endsWithParent) {
				$mode->end = '\B|\b';
			} //end if
			if($mode->end) {
				$mode->endRe = $this->langRe($mode->end);
			} //end if
			$mode->terminatorEnd = $mode->end;
			if($mode->endsWithParent && $parent->terminatorEnd) {
				$mode->terminatorEnd .= ($mode->end ? '|' : '').$parent->terminatorEnd;
			} //end if
		} //end if
		//--
		if($mode->illegal) {
			$mode->illegalRe = $this->langRe($mode->illegal);
		} //end if
		//--
		$expandedContains = array();
		foreach($mode->contains as $c) {
			$expandedContains = (array) \array_merge($expandedContains, $this->expandMode(
				$c === 'self' ? $mode : $c
			));
		} //end foreach
		//--
		$mode->contains = $expandedContains;
		//--
		for($i=0; $i<count($mode->contains); ++$i) {
			$this->compileMode($mode->contains[$i], $mode);
		} //end for
		//--
		if($mode->starts) {
			$this->compileMode($mode->starts, $parent);
		} //end if
		//--
		$terminators = array();
		//--
		for($i=0; $i<count($mode->contains); ++$i) {
			$terminators[] = $mode->contains[$i]->beginKeywords
				? '\.?(?:'.$mode->contains[$i]->begin.')\.?'
				: $mode->contains[$i]->begin;
		} //end for
		//--
		if($mode->terminatorEnd) {
			$terminators[] = $mode->terminatorEnd;
		} //end if
		if($mode->illegal) {
			$terminators[] = $mode->illegal;
		} //end if
		//--
		$mode->terminators = \count($terminators) ? $this->langRe($this->joinRe($terminators, '|'), true) : null;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
