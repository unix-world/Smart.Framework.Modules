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
// - 2006-2013, Ivan Sagalaev (maniac@softwaremaniacs.org), highlight.js (original author)
// - 2013-2019, Geert Bergman (geert@scrivo.nl), highlight.php
// - 2014       Daniel Lynge, highlight.php (contributor)
// - 2019-2020  unixman (unix-world.org)
// License: BSD
//
//----------------------------------------------------------------------


/**
 * Class: \SmartModExtLib\HighlightSyntax\Highlighter
 * HighLight Code Syntax using PHP only, compatible with Highlight.Js
 *
 * @depends 	\stdClass, \Exception, \Smart, \SmartUnicode, \SmartModExtLib\HighlightSyntax\Languages, \SmartModExtLib\HighlightSyntax\Language
 * @version 	v.20241216
 * @package 	modules:HighlightSyntax
 *
 * <code>
 *
 * // sample usage:
 * $hl = (new \SmartModExtLib\HighlightSyntax\Highlighter())->highlight('xml', SmartFileSystem::read('a-file.html'));
 * echo '<pre><code class="hljs '.Smart::escape_html($hl->language).'">'.$hl->value.'</code></pre>';
 *
 * </code>
 *
 */
class Highlighter {


	private $options;

	private $modeBuffer = '';
	private $result = '';
	private $top = null;
	private $language = null;
	private $keywordCount = 0;
	private $relevance = 0;
	private $ignoreIllegals = false;
	private $continuations = null;


	/**
	 * Class Constructor
	 *
	 * @param BOOLEAN $register_all_languages :: *Optional* ; Default is TRUE ; If set to false the class will not pre-load all available languages and aliases will not be available so in this case exact language match must be indicated
	 */
	public function __construct($register_all_languages=true) {
		//--
		$this->options = array(
			'classPrefix' 	=> 'hljs-',
			'tabReplace' 	=> null,
			'useBR' 		=> false,
			'languages' 	=> null,
		);
		//--
		if($register_all_languages === true) {
			\SmartModExtLib\HighlightSyntax\Languages::registerLanguages();
		} //end if
		//--
	} //END FUNCTION


	/**
	 * Core highlighting function. Accepts a language name, or an alias, and a
	 * string with the code to highlight. Returns an object with the following properties:
	 * - relevance (int)
	 * - value (an HTML string with highlighting markup).
	 *
	 * @param STRING $language 			:: The code syntax type or an alias ; Ex: javascript
	 * @param STRING $code 				:: The code to be highlighted
	 * @param BOOLEAN $ignoreIllegals 	:: Setting to ignore or not illegal syntax ; Default is TRUE
	 * @param NULL $continuation 		:: This must be not used ; it is set automated to NULL when initiated and rewritten internally as reference
	 * @return OBJECT
	 */
	public function highlight($language, $code, $ignoreIllegals=true, $continuation=null) {
		//--
		$this->language = Languages::getLanguage($language);
		$this->language->compile();
		$this->top = $continuation ? $continuation : $this->language->mode;
		$this->continuations = array();
		$this->result = '';
		//--
		for($current = $this->top; $current != $this->language->mode; $current = $current->parent) {
			if($current->className) {
				$this->result = $this->buildSpan($current->className, '', true) . $this->result;
			} //end if
		} //end for
		//--
		$this->modeBuffer = '';
		$this->relevance = 0;
		$this->ignoreIllegals = $ignoreIllegals;
		//--
		$res = new \stdClass();
		$res->relevance = 0;
		$res->value = '';
		$res->language = '';
		//--
		try {
			//--
			$match = null;
			$count = 0;
			$index = 0;
			//--
			while($this->top && $this->top->terminators) {
				//--
				$test = @\preg_match((string)$this->top->terminators, (string)$code, $match, \PREG_OFFSET_CAPTURE, $index);
				//--
				if($test === false) {
					//--
					\Smart::log_warning('Invalid '.$this->language->name.' regExp '.\var_export($this->top->terminators, true));
					//--
					$res->language = 'text';
					$res->value = (string) \Smart::escape_html((string)$code);
					return $res;
					//--
				} elseif($test === 0) {
					//--
					break;
					//--
				} //end if else
				//--
				$count = $this->processLexeme(\substr((string)$code, $index, $match[0][1] - $index), $match[0][0]);
				//--
				$index = $match[0][1] + $count;
				//--
			} //end while
			//--
			$this->processLexeme(\substr((string)$code, $index));
			//--
			for($current = $this->top; isset($current->parent); $current=$current->parent) {
				if($current->className) {
					$this->result .= '</span>';
				} //end if
			} //end for
			//--
			$res->relevance = $this->relevance;
			$res->value = $this->replaceTabs($this->result);
			$res->language = $this->language->name;
			$res->top = $this->top;
			//--
			return $res;
			//--
		} catch(\Exception $e) {
			//--
			if($e->getCode() === 777) {
				//--
				$res->value = \Smart::escape_html($code);
				//--
				return $res;
				//--
			} //end if
			//--
			\Smart::log_warning(__METHOD__.' :: Catch Exception # '.$e);
			//--
		} //end try catch
		//--
	} //END FUNCTION


	//===== PRIVATES


	private function testRe($re, $lexeme) {
		//--
		if(!$re) {
			return false;
		} //end if
		//--
		$test = @\preg_match((string)$re, (string)$lexeme, $match, \PREG_OFFSET_CAPTURE);
		if($test === false) {
			throw new \Exception(
				'Invalid regexp: '.\var_export($re, true),
				801
			);
			return false;
		} //end if
		//--
		return (bool) (\count($match) && ((string)$match[0][1] == '0'));
		//--
	} //END FUNCTION


	private function escapeRe($value) {
		//--
		return (string) \sprintf('/%s/m', \preg_quote((string)$value));
		//--
	} //END FUNCTION


	private function subMode($lexeme, $mode) {
		//--
		for($i=0; $i<\count($mode->contains); ++$i) {
			//--
			if($this->testRe($mode->contains[$i]->beginRe, $lexeme)) {
				//--
				if($mode->contains[$i]->endSameAsBegin) {
					//--
					$matches = array();
					\preg_match((string)$mode->contains[$i]->beginRe, (string)$lexeme, $matches);
					//--
					$mode->contains[$i]->endRe = $this->escapeRe($matches[0]);
					//--
				} //end if
				//--
				return $mode->contains[$i];
				//--
			} //end if
			//--
		} //end for
		//--
	} //END FUNCTION


	private function endOfMode($mode, $lexeme) {
		//--
		if($this->testRe($mode->endRe, $lexeme)) {
			//--
			while($mode->endsParent && $mode->parent) {
				$mode = $mode->parent;
			} //end while
			//--
			return $mode;
			//--
		} //end if
		//--
		if($mode->endsWithParent) {
			//--
			return $this->endOfMode($mode->parent, $lexeme);
			//--
		} //end if
		//--
	} //END FUNCTION


	private function isIllegal($lexeme, $mode) {
		//--
		return (bool) (!$this->ignoreIllegals && $this->testRe($mode->illegalRe, $lexeme));
		//--
	} //END FUNCTION


	private function keywordMatch($mode, $match) {
		//--
		$kwd = $this->language->caseInsensitive ? \SmartUnicode::str_tolower((string)$match[0]) : (string)$match[0];
		//--
		return isset($mode->keywords[$kwd]) ? $mode->keywords[$kwd] : null;
		//--
	} //END FUNCTION


	private function buildSpan($classname, $insideSpan, $leaveOpen=false, $noPrefix=false) {
		//--
		$classPrefix = $noPrefix ? '' : $this->options['classPrefix'];
		$openSpan = '<span class="'.\Smart::escape_html($classPrefix);
		$closeSpan = $leaveOpen ? '' : '</span>';
		$openSpan .= $classname.'">';
		//--
		if(!$classname) {
			return $insideSpan;
		} //end if
		//--
		return (string) $openSpan.$insideSpan.$closeSpan;
		//--
	} //END FUNCTION


	private function processKeywords() {
		//--
		if(empty($this->top->keywords)) {
			return \Smart::escape_html($this->modeBuffer);
		} //end if
		//--
		$result = '';
		$lastIndex = 0;
		//--
		/* TODO: when using the crystal language file on django and twig code
		 * the values of $this->top->lexemesRe can become '' (empty).
		 * Check if this behaviour is consistent with highlight.js.
		 */
		//--
		if($this->top->lexemesRe) {
			//--
			while(\preg_match((string)$this->top->lexemesRe, (string)$this->modeBuffer, $match, \PREG_OFFSET_CAPTURE, $lastIndex)) {
				//--
				$result .= \Smart::escape_html(\substr($this->modeBuffer, $lastIndex, $match[0][1] - $lastIndex));
				$keyword_match = $this->keywordMatch($this->top, $match[0]);
				//--
				if($keyword_match) {
					$this->relevance += $keyword_match[1];
					$result .= $this->buildSpan($keyword_match[0], \Smart::escape_html($match[0][0]));
				} else {
					$result .= \Smart::escape_html($match[0][0]);
				} //end if else
				//--
				$lastIndex = strlen($match[0][0]) + $match[0][1];
				//--
			} //end while
			//--
		} //end if
		//--
		return (string) $result.\Smart::escape_html(\substr($this->modeBuffer, $lastIndex));
		//--
	} //END FUNCTION


	private function processSubLanguage() {
		//--
		try {
			//--
			$hl = new \SmartModExtLib\HighlightSyntax\Highlighter(false); // no need to re-register, they are cached in the static class
			//--
			$explicit = \is_string($this->top->subLanguage);
			//--
			if($explicit && !\in_array($this->top->subLanguage, Languages::getLanguages())) {
				//--
				Languages::registerLanguage($this->top->subLanguage);
				//--
				if($explicit && !\in_array($this->top->subLanguage, Languages::getLanguages())) {
					return \Smart::escape_html($this->modeBuffer);
				} //end if
				//--
			} //end if
			//--
			if($explicit) {
				//--
				$res = $hl->highlight(
					$this->top->subLanguage,
					$this->modeBuffer,
					true,
					isset($this->continuations[$this->top->subLanguage]) ? $this->continuations[$this->top->subLanguage] : null
				);
				//--
			} else {
				//--
				return \Smart::escape_html($this->modeBuffer);
				//--
			} //end if else
			//--
			// Counting embedded language score towards the host language may
			// be disabled with zeroing the containing mode relevance. Usecase
			// in point is Markdown that allows XML everywhere and makes every
			// XML snippet to have a much larger Markdown score.
			//--
			if($this->top->relevance > 0) {
				$this->relevance += $res->relevance;
			} //end if
			//--
			if($explicit) {
				$this->continuations[$this->top->subLanguage] = $res->top;
			} //end if
			//--
			return $this->buildSpan($res->language, $res->value, false, true);
			//--
		} catch(\Exception $e) {
			//--
			\Smart::log_warning(__METHOD__.' :: Catch Exception # '.$e);
			//--
			return \Smart::escape_html($this->modeBuffer);
			//--
		} //end try catch
		//--
	} //END FUNCTION
	//--


	private function processBuffer() {
		//--
		if(\is_object($this->top) && $this->top->subLanguage) {
			$this->result .= $this->processSubLanguage();
		} else {
			$this->result .= $this->processKeywords();
		} //end if else
		//--
		$this->modeBuffer = '';
		//--
	} //END FUNCTION


	private function startNewMode($mode) {
		//--
		$this->result .= $mode->className ? $this->buildSpan($mode->className, '', true) : '';
		//--
		$t = clone $mode;
		$t->parent = $this->top;
		$this->top = $t;
		//--
	} //END FUNCTION


	private function processLexeme($buffer, $lexeme=null) {
		//--
		$this->modeBuffer .= (string) $buffer;
		//--
		if($lexeme === null) {
			$this->processBuffer();
			return 0;
		} //end if
		//--
		$new_mode = $this->subMode($lexeme, $this->top);
		//--
		if($new_mode) {
			//--
			if($new_mode->skip) {
				$this->modeBuffer .= (string) $lexeme;
			} else {
				if($new_mode->excludeBegin) {
					$this->modeBuffer .= (string) $lexeme;
				} //end if
				$this->processBuffer();
				if(!$new_mode->returnBegin && !$new_mode->excludeBegin) {
					$this->modeBuffer = (string) $lexeme;
				} //end if
			} //end if else
			//--
			$this->startNewMode($new_mode, $lexeme);
			//--
			return $new_mode->returnBegin ? 0 : \strlen((string)$lexeme);
			//--
		} //end if
		//--
		$end_mode = $this->endOfMode($this->top, $lexeme);
		if($end_mode) {
			$origin = $this->top;
			if($origin->skip) {
				$this->modeBuffer .= (string) $lexeme;
			} else {
				if(!($origin->returnEnd || $origin->excludeEnd)) {
					$this->modeBuffer .= (string) $lexeme;
				} //end if
				$this->processBuffer();
				if($origin->excludeEnd) {
					$this->modeBuffer = (string) $lexeme;
				}
			} //end if else
			//--
			do {
				//--
				if($this->top->className) {
					$this->result .= '</span>';
				} //end if
				if(!$this->top->skip && !$this->top->subLanguage) {
					$this->relevance += $this->top->relevance;
				} //end if
				//--
				$this->top = $this->top->parent;
				//--
			} while($this->top != $end_mode->parent);
			//--
			if($end_mode->starts) {
				//--
				if($end_mode->endSameAsBegin) {
					$end_mode->starts->endRe = $end_mode->endRe;
				} //end if
				//--
				$this->startNewMode($end_mode->starts, '');
				//--
			} //end if
			//--
			return $origin->returnEnd ? 0 : \strlen((string)$lexeme);
			//--
		} //end if
		//--
		if($this->isIllegal($lexeme, $this->top)) {
			$className = $this->top->className ? $this->top->className : 'unnamed';
			throw new \Exception(
				'Illegal lexeme `'.$lexeme.'` for mode `'.$className.'`',
				777
			);
		} //end if
		//--
		// Parser should not reach this point as all types of lexemes should
		// be caught earlier, but if it does due to some bug make sure it
		// advances at least one character forward to prevent infinite looping.
		$this->modeBuffer .= (string) $lexeme;
		$l = (int) \strlen((string)$lexeme);
		//--
		return $l ? $l : 1;
		//--
	} //END FUNCTION


	/**
	 * Replace tabs for something more usable.
	 */
	private function replaceTabs($code) {
		//--
		if($this->options['tabReplace'] !== null) {
			return (string) \str_replace("\t", $this->options['tabReplace'], (string)$code);
		} //end if
		//--
		return (string) $code;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
