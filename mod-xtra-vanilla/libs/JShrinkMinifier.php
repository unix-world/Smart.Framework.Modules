<?php
// Class: \SmartModExtLib\Vanilla\JShrinkMinifier
// [Smart.Framework.Modules - Vanilla / Javascript Shrink Minifier]
// (c) 2006-2019 unix-world.org - all rights reserved

namespace SmartModExtLib\Vanilla;


//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * JShrink
 * A very basic Javascript Minifier
 *
 * modified by unixman (iradu@unix-world.org)
 *
 * <code>
 *
 * $jshrink = new \SmartModExtLib\Vanilla\JShrinkMinifier();
 * $js = $jshrink->lock((string)$input);
 * $tmp = (string) $jshrink->minifyJsCode($js, []);
 * $js = (string) ltrim((string)$tmp); // sometimes there's a leading new line, so we trim that out here.
 * $tmp = '';
 * $js = $jshrink->unlock($js);
 * $jshrink = null;
 * $output = (string) $js;
 * $js = ''; // cleanup
 * $jshrink = null; // free mem
 *
 * </code>
 *
 * @package Minify
 * @author Robert Hafner <tedivm@tedivm.com>
 * @license BSD License
 */

final class JShrinkMinifier {

	// ->
	// v.181225

	private $output = '';

	/**
	 * The input javascript to be minified.
	 *
	 * @var string
	 */
	private $input;

	/**
	 * The location of the character (in the input string) that is next to be
	 * processed.
	 *
	 * @var int
	 */
	private $index = 0;

	/**
	 * The first of the characters currently being looked at.
	 *
	 * @var string
	 */
	private $a = '';

	/**
	 * The next character being looked at (after a);
	 *
	 * @var string
	 */
	private $b = '';

	/**
	 * This character is only active when certain look ahead actions take place.
	 *
	 *  @var string
	 */
	private $c;

	/**
	 * Contains the options for the current minification process.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Contains the default options for minification. This array is merged with
	 * the one passed in by the user to create the request specific set of
	 * options (stored in the $options attribute).
	 *
	 * @var array
	 */
	private $defaultOptions = array('flaggedComments' => false);

	/**
	 * Contains lock ids which are used to replace certain code patterns and
	 * prevent them from being minified
	 *
	 * @var array
	 */
	private $locks = array();


	public function __construct() {
		//--
		// class constructor
		//--
	} //END FUNCTION


	/**
	 * Processes a javascript string and outputs only the required characters,
	 * stripping out all unneeded characters.
	 *
	 * @param string $js      The raw javascript to be minified
	 * @param array  $options Various runtime options in an associative array
	 */
	public function minifyJsCode($js, $options) {
		//--
		$this->initialize($js, $options);
		$this->loop();
		$this->clean();
		//--
		return (string) $this->output;
		//--
	} //END FUNCTION


	/**
	 * Replace patterns in the given string and store the replacement
	 *
	 * @param  string $js The string to lock
	 * @return bool
	 */
	public function lock($js) {
		//-- lock things like <code>"asd" + ++x;</code>
		//$lock = '"LOCK---'.crc32(time()).'"';
		$lock = '"LOCK---'.\sha1((string)\microtime()).'"';
		$js = (string) $js;
		$matches = array();
		\preg_match('/([+-])(\s+)([+-])/S', $js, $matches);
		if(empty($matches)) {
			return (string) $js;
		} //end if
		//--
		$this->locks[$lock] = $matches[2];
		//--
		$js = \preg_replace('/([+-])\s+([+-])/S', "$1{$lock}$2", $js);
		//--
		return (string) $js;
		//--
	} //END FUNCTION


	/**
	 * Replace "locks" with the original characters
	 *
	 * @param  string $js The string to unlock
	 * @return bool
	 */
	public function unlock($js) {
		//--
		if(empty($this->locks)) {
			return (string) $js;
		} //end if
		//--
		foreach($this->locks as $lock => $replacement) {
			$js = \str_replace($lock, $replacement, $js);
		} //end foreach
		//--
		return (string) $js;
		//--
	} //END FUNCTION


	/**
	 *  Initializes internal variables, normalizes new lines,
	 *
	 * @param string $js      The raw javascript to be minified
	 * @param array  $options Various runtime options in an associative array
	 */
	private function initialize($js, $options=array()) {
		//--
		if(!\is_array($options)) {
			$options = array();
		} //end if
		//--
		$this->options = \array_merge($this->defaultOptions, $options);
		//--
		$js = (string) $js;
		$js = \str_replace("\r\n", "\n", $js);
		$js = \str_replace('/**/', '', $js);
		$this->input = \str_replace("\r", "\n", $js);
		//--
		// We add a newline to the end of the script to make it easier to deal
		// with comments at the bottom of the script- this prevents the unclosed
		// comment error that can otherwise occur.
		$this->input .= (string) \PHP_EOL;
		//--
		// Populate "a" with a new line, "b" with the first character, before
		// entering the loop
		$this->a = "\n";
		$this->b = $this->getReal();
		//--
	} //END FUNCTION


	/**
	 * The primary action occurs here. This function loops through the input string,
	 * outputting anything that's relevant and discarding anything that is not.
	 */
	private function loop() {
		//--
		while($this->a !== false && !\is_null($this->a) && $this->a !== '') {
			//--
			switch($this->a) {
				//-- new lines
				case "\n":
					//-- if the next line is something that can't stand alone preserve the newline
					if(\strpos('(-+[@', $this->b) !== false) {
						$this->output .= (string) $this->a;
						$this->saveString();
						break;
					} //end if
					//-- if B is a space we skip the rest of the switch block and go down to the string/regex check below, resetting $this->b with getReal
					if($this->b === ' ') {
						break;
					} //end if
					//--
				//-- otherwise we treat the newline like a space
				case ' ':
					//--
					if($this->isAlphaNumeric($this->b)) {
						$this->output .= (string) $this->a;
					} //end if
					//--
					$this->saveString();
					break;
					//--
				default:
					//--
					switch($this->b) {
						//--
						case "\n":
							if(strpos('}])+-"\'', $this->a) !== false) {
								$this->output .= (string) $this->a;
								$this->saveString();
								break;
							} else {
								if($this->isAlphaNumeric($this->a)) {
									$this->output .= (string) $this->a;
									$this->saveString();
								} //end if
							} //end if else
							break;
						//--
						case ' ':
							if(!$this->isAlphaNumeric($this->a)) {
								break;
							} //end if
						//--
						default:
							//-- check for some regex that breaks stuff
							if($this->a === '/' && ($this->b === '\'' || $this->b === '"')) {
								$this->saveRegex();
								continue 3; // continue 3 is for while (continue 2 is for parent switch)
							} //end if
							//--
							$this->output .= (string) $this->a;
							$this->saveString();
							break;
							//--
					} //end switch
					//--
			} //end switch
			//-- do reg check of doom
			$this->b = $this->getReal();
			//--
			if(((string)$this->b == '/' && \strpos('(,=:[!&|?', $this->a) !== false)) {
				$this->saveRegex();
			} //end if
			//--
		} //end while
		//--
	} //END FUNCTION


	/**
	 * Resets attributes that do not need to be stored between requests so that
	 * the next request is ready to go. Another reason for this is to make sure
	 * the variables are cleared and are not taking up memory.
	 */
	private function clean() {
		//--
		unset($this->input);
		$this->index = 0;
		$this->a = $this->b = '';
		unset($this->c);
		unset($this->options);
		//--
	} //END FUNCTION


	/**
	 * Returns the next string for processing based off of the current index.
	 *
	 * @return string
	 */
	private function getChar() {
		//-- check to see if we had anything in the look ahead buffer and use that.
		if(isset($this->c)) {
			//--
			$char = $this->c;
			unset($this->c);
			//--
		} else { // otherwise we start pulling from the input
			//--
			$char = \substr($this->input, $this->index, 1);
			//-- if the next character doesn't exist return false.
			if(isset($char) && $char === false) {
				return false;
			} //end if
			//-- otherwise increment the pointer and use this char.
			$this->index++;
			//--
		} //end if else
		//-- normalize all whitespace except for the newline character into a standard space.
		if($char !== "\n" && ord($char) < 32) {
			return ' ';
		} //end if
		//--
		return $char;
		//--
	} //END FUNCTION


	/**
	 * This function gets the next "real" character. It is essentially a wrapper
	 * around the getChar function that skips comments. This has significant
	 * performance benefits as the skipping is done using native functions (ie,
	 * c code) rather than in script php.
	 *
	 *
	 * @return string            Next 'real' character to be processed.
	 * @throws Exception
	 */
	private function getReal() {
		//--
		$startIndex = $this->index;
		$char = $this->getChar();
		//-- check to see if we're potentially in a comment
		if($char !== '/') {
			return $char;
		} //end if
		//--
		$this->c = $this->getChar();
		//--
		if($this->c === '/') {
			$this->processOneLineComments($startIndex);
			return $this->getReal();
		} elseif($this->c === '*') {
			$this->processMultiLineComments($startIndex);
			return $this->getReal();
		} //end if else
		//--
		return $char;
		//--
	} //END FUNCTION


	/**
	 * Removed one line comments, with the exception of some very specific types of
	 * conditional comments.
	 *
	 * @param  int  $startIndex The index point where "getReal" function started
	 * @return void
	 */
	private function processOneLineComments($startIndex) {
		//--
		$thirdCommentString = \substr($this->input, $this->index, 1);
		//-- kill rest of line
		$this->getNext("\n");
		//--
		unset($this->c);
		//--
		if((string)$thirdCommentString == '@') {
			$endPoint = $this->index - $startIndex;
			$this->c = "\n".\substr($this->input, $startIndex, $endPoint);
		} //end if
		//--
	} //END FUNCTION


	/**
	 * Skips multiline comments where appropriate, and includes them where needed.
	 * Conditional comments and "license" style blocks are preserved.
	 *
	 * @param  int               $startIndex The index point where "getReal" function started
	 * @return void
	 * @throws Exception Unclosed comments will throw an error
	 */
	private function processMultiLineComments($startIndex) {
		//--
		$this->getChar(); // current C
		$thirdCommentString = $this->getChar();
		//-- kill everything up to the next */ if it's there
		if($this->getNext('*/')) {
			//--
			$this->getChar(); // get *
			$this->getChar(); // get /
			$char = $this->getChar(); // get next real character
			//-- now we reinsert conditional comments and YUI-style licensing comments
			if(($this->options['flaggedComments'] && $thirdCommentString === '!') || ($thirdCommentString === '@')) {
				//-- if conditional comments or flagged comments are not the first thing in the script we need to add to output a and fill it with a space before moving on.
				if($startIndex > 0) {
					//--
					$this->output .= (string) $this->a;
					$this->a = " ";
					//-- if the comment started on a new line we let it stay on the new line
					if($this->input[($startIndex - 1)] === "\n") {
						$this->output .= (string) "\n";
					} //end if
					//--
				} //end if
				//--
				$endPoint = ($this->index - 1) - $startIndex;
				$this->output .= (string) \substr($this->input, $startIndex, $endPoint);
				//--
				$this->c = $char;
				//--
				return;
				//--
			} //end if
			//--
		} else {
			//--
			$char = false;
			//--
		} //end if else
		//--
		if($char === false) {
			throw new \Exception('Unclosed multiline comment at position: '.($this->index - 2));
		} //end if
		//-- if we're here c is part of the comment and therefore tossed
		$this->c = $char;
		//--
	} //END FUNCTION


	/**
	 * Pushes the index ahead to the next instance of the supplied string. If it
	 * is found the first character of the string is returned and the index is set
	 * to it's position.
	 *
	 * @param  string       $string
	 * @return string|false Returns the first character of the string or false.
	 */
	private function getNext($string) {
		//-- find the next occurrence of "string" after the current position.
		$pos = \strpos($this->input, $string, $this->index);
		//-- if it's not there return false.
		if($pos === false) {
			return false;
		} //end if
		//-- adjust position of index to jump ahead to the asked for string
		$this->index = $pos;
		//-- return the first character of that string.
		return \substr($this->input, $this->index, 1);
		//--
	} //END FUNCTION


	/**
	 * When a javascript string is detected this function crawls for the end of
	 * it and saves the whole string.
	 *
	 * @throws Exception Unclosed strings will throw an error
	 */
	private function saveString() {
		//--
		$startpos = $this->index;
		//-- saveString is always called after a gets cleared, so we push b into that spot.
		$this->a = $this->b;
		//-- if this isn't a string we don't need to do anything.
		if($this->a !== "'" && $this->a !== '"') {
			return;
		} //end if
		//-- string type is the quote used, " or '
		$stringType = $this->a;
		//-- output out that starting quote
		$this->output .= (string) $this->a;
		//--loop until the string is done
		while(true) {
			//-- grab the very next character and load it into a
			$this->a = $this->getChar();
			//--
			switch ($this->a) {
				//--
				// If the string opener (single or double quote) is used
				// output it and break out of the while loop-
				// The string is finished!
				case $stringType:
					break 2;
				//--
				// New lines in strings without line delimiters are bad- actual
				// new lines will be represented by the string \n and not the actual
				// character, so those will be treated just fine using the switch
				// block below.
				case "\n":
					throw new \Exception('Unclosed string at position: '.$startpos);
					break;
				//--
				// Escaped characters get picked up here. If it's an escaped new line it's not really needed
				case '\\':
					//--
					// a is a slash. We want to keep it, and the next character,
					// unless it's a new line. New lines as actual strings will be
					// preserved, but escaped new lines should be reduced.
					$this->b = $this->getChar();
					//--
					// If b is a new line we discard a and b and restart the loop.
					if($this->b === "\n") {
						break;
					} //end if
					//--
					// output out the escaped character and restart the loop.
					$this->output .= (string) $this->a.$this->b;
					break;
					//--
				//--
				// Since we're not dealing with any special cases we simply
				// output the character and continue our loop.
				default:
					$this->output .= (string) $this->a;
				//--
			} //end switch
			//--
		} //end while
		//--
	} //END FUNCTION


	/**
	 * When a regular expression is detected this function crawls for the end of
	 * it and saves the whole regex.
	 *
	 * @throws Exception Unclosed regex will throw an error
	 */
	private function saveRegex() {
		//--
		$this->output .= (string) $this->a.$this->b;
		//--
		while(($this->a = $this->getChar()) !== false) {
			//--
			if($this->a === '/') {
				break;
			} //end if
			if($this->a === '\\') {
				$this->output .= (string) $this->a;
				$this->a = $this->getChar();
			} //end if
			if($this->a === "\n") {
				throw new \Exception('Unclosed regex pattern at position: '.$this->index);
			} //end if
			$this->output .= (string) $this->a;
		} //end while
		//--
		$this->b = $this->getReal();
		//--
	} //END FUNCTION


	/**
	 * Checks to see if a character is alphanumeric.
	 *
	 * @param  string $char Just one character
	 * @return bool
	 */
	private function isAlphaNumeric($char) {
		//--
		if((\preg_match('/^[\w\$\pL]$/', $char) === 1) || ((string)$char == '/')) {
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
