<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL;

/**
 * Base class for blocks.
 */
abstract class AbstractBlock extends \TwistTPL\AbstractTag {

	private const TAG_PREFIX = '\\TwistTPL\\Tag\\Tag';

	/**
	 * @var AbstractTag[]|Variable[]|string[]
	 */
	protected $nodelist = [];

	/**
	 * Whenever next token should be ltrim'med.
	 *
	 * @var bool
	 */
	protected static $trimWhitespace = false;


	/**
	 * @return array
	 */
	public function getNodelist() : array {
		if(!\is_array($this->nodelist)) {
			$this->nodelist = [];
		} //end if
		return (array) $this->nodelist;
	} //END FUNCTION


	/**
	 * Parses the given tokens
	 *
	 * @param array $tokens
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function parse(array &$tokens) : void {
		//--
		$startRegexp = new \TwistTPL\Regexp('/^'.\TwistTPL\Twist::get('TAG_START').'/');
		$tagRegexp = new \TwistTPL\Regexp('/^'.\TwistTPL\Twist::get('TAG_START').\TwistTPL\Twist::get('WHITESPACE_CONTROL').'?\s*(\w+)\s*(.*?)'.\TwistTPL\Twist::get('WHITESPACE_CONTROL').'?'.\TwistTPL\Twist::get('TAG_END').'$/s');
		$variableStartRegexp = new \TwistTPL\Regexp('/^'.\TwistTPL\Twist::get('VARIABLE_START').'/');
		//--
		$this->nodelist = []; // reset
		while(\count($tokens)) {
			$token = \array_shift($tokens);
			if($startRegexp->match($token)) {
				$this->whitespaceHandler($token);
				if($tagRegexp->match($token)) {
					// If we found the proper block delimitor just end parsing here and let the outer block proceed
					if($tagRegexp->matches[1] === $this->blockDelimiter()) {
						$this->endTag();
						return;
					} //end if
					$tagName = null;
					$tagName = (string) self::TAG_PREFIX.\ucwords($tagRegexp->matches[1]);
					$tagName = (\class_exists($tagName) === true) ? $tagName : null;
					if($tagName !== null) {
						$this->nodelist[] = new $tagName($tagRegexp->matches[2], $tokens, $this->fileSystem);
						if($tagRegexp->matches[1] === 'extends') {
							return;
						} //end if
					} else {
						$this->unknownTag($tagRegexp->matches[1], $tagRegexp->matches[2], $tokens);
					} //end if else
				} else {
					throw new \Exception("Tag $token was not properly terminated (won't match $tagRegexp)");
					return;
				} //end if else
			} elseif($variableStartRegexp->match($token)) {
				$this->whitespaceHandler($token);
				$this->nodelist[] = $this->createVariable($token);
			} else {
				if(self::$trimWhitespace) {
					$token = (string) \ltrim((string)$token); // This is neither a tag or a variable, proceed with an ltrim
				} //end if
				self::$trimWhitespace = false;
				$this->nodelist[] = $token;
			} //end if else
		} //end while
		//--
		$this->assertMissingDelimitation();
		//--
	} //END FUNCTION


	/**
	 * Handle the whitespace.
	 *
	 * @param string $token
	 */
	protected function whitespaceHandler(?string $token) : void {
		//-- This assumes that TAG_START is always '{%', and a whitespace control indicator is exactly one character long, on a third position.
		if((string)\mb_substr((string)$token, 2, 1) == (string)\TwistTPL\Twist::get('WHITESPACE_CONTROL')) {
			$previousToken = \end($this->nodelist);
			if(\is_string($previousToken)) { // this can also be a tag or a variable
				$this->nodelist[key($this->nodelist)] = (string) \rtrim((string)$previousToken);
			} //end if
		} //end if
		//-- This assumes that TAG_END is always '%}', and a whitespace control indicator is exactly one character long, on a third position from the end.
		self::$trimWhitespace = (bool) ((string)\mb_substr((string)$token, -3, 1) == (string)\TwistTPL\Twist::get('WHITESPACE_CONTROL'));
		//--
	} //END FUNCTION


	/**
	 * Render the block.
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		return (string) $this->renderAll((array)$this->nodelist, $context);
		//--
	} //END FUNCTION


	/**
	 * Renders all the given nodelist's nodes
	 *
	 * @param array $list
	 * @param Context $context
	 *
	 * @return string
	 */
	protected function renderAll(array $list, \TwistTPL\Context $context) : string {
		//--
		$result = '';
		//--
		foreach($list as $kk => $token) {
			if(\is_object($token) && \method_exists($token, 'render')) {
				$value = (string) $token->render($context);
			} else {
				$value = (string) $token;
			} //end if else
			if(\is_array($value)) {
				$value = (string) \htmlspecialchars((string)\print_r($value, true));
			} //end if
			$result .= (string) $value;
			if(isset($context->registers['break'])) {
				break;
			} //end if
			if(isset($context->registers['continue'])) {
				break;
			} //end if
		} //end foreach
		//--
		return (string) $result;
		//--
	} //END FUNCTION


	/**
	 * An action to execute when the end tag is reached
	 */
	protected function endTag() : void {
		// Do nothing by default
	} //END FUNCTION


	/**
	 * Handler for unknown tags
	 *
	 * @param string $tag
	 * @param string $params
	 * @param array $tokens
	 *
	 * @throws \Exception
	 */
	protected function unknownTag(?string $tag, ?string $params, array $tokens) : void {
		switch((string)$tag) {
			case 'else':
				throw new \Exception($this->blockName() . " does not expect else tag");
			case 'end':
				throw new \Exception("'end' is not a valid delimiter for " . $this->blockName() . " tags. Use " . $this->blockDelimiter());
			default:
				throw new \Exception("Unknown tag $tag");
		} //end switch
	} //END FUNCTION


	/**
	 * This method is called at the end of parsing, and will throw an error unless
	 * this method is subclassed, like it is for Document
	 *
	 * @throws \Exception
	 * @return bool
	 */
	protected function assertMissingDelimitation() : void {
		throw new \Exception($this->blockName().' tag was never closed');
	} //END FUNCTION


	/**
	 * Returns the string that delimits the end of the block
	 *
	 * @return string
	 */
	protected function blockDelimiter() : string {
		return (string) 'end'.$this->blockName();
	} //END FUNCTION


	/**
	 * Returns the name of the block
	 *
	 * @return string
	 */
	private function blockName() : string {
		$reflection = new \ReflectionClass($this);
		return (string) \str_replace('tag', '', (string)\strtolower((string)$reflection->getShortName()));
	} //END FUNCTION


	/**
	 * Create a variable for the given token
	 *
	 * @param string $token
	 *
	 * @throws \Exception
	 * @return Variable
	 */
	private function createVariable(?string $token) : ?\TwistTPL\Variable {
		//--
		$variableRegexp = new \TwistTPL\Regexp('/^'.\TwistTPL\Twist::get('VARIABLE_START').\TwistTPL\Twist::get('WHITESPACE_CONTROL').'?(.*?)'.\TwistTPL\Twist::get('WHITESPACE_CONTROL').'?'.\TwistTPL\Twist::get('VARIABLE_END').'$/s');
		//--
		if($variableRegexp->match($token)) {
			return new \TwistTPL\Variable($variableRegexp->matches[1]);
		} //end if
		//--
		throw new \Exception("Variable $token was not properly terminated");
		return null;
		//--
	} //END FUNCTION


} //END CLASS

// #end
