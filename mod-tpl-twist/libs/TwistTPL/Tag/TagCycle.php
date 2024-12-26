<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL\Tag;


/**
 * Cycles between a list of values; calls to the tag will return each value in turn
 *
 * Example:
 *     {%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}
 *
 *     this will return:
 *     one two one
 *
 *     Cycles can also be named, to differentiate between multiple cycle with the same values:
 *     {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}
 *
 *     will return
 *     one one two two
 */
final class TagCycle extends \TwistTPL\AbstractTag {

	/**
	 * @var string The name of the cycle; if none is given one is created using the value list
	 */
	private $name;

	/**
	 * @var variables[] The variables to cycle between
	 */
	private $variables = [];


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param \TwistTPL\AbstractInterfaceFileSystem $fileSystem
	 *
	 * @throws \Exception
	 */
	public function __construct(?string $markup, array &$tokens, ?\TwistTPL\AbstractInterfaceFileSystem $fileSystem=null) {
		$simpleSyntax = new \TwistTPL\Regexp("/".\TwistTPL\Twist::get('QUOTED_FRAGMENT')."/");
		$namedSyntax = new \TwistTPL\Regexp("/(".\TwistTPL\Twist::get('QUOTED_FRAGMENT').")\s*\:\s*(.*)/");
		if($namedSyntax->match($markup)) {
			$this->variables = (array) $this->variablesFromString((string)($namedSyntax->matches[2] ?? null));
			$this->name = (string) ($namedSyntax->matches[1] ?? null);
		} elseif ($simpleSyntax->match($markup)) {
			$this->variables = (array) $this->variablesFromString($markup);
			$this->name = "'".\implode((array)$this->variables)."'";
		} else {
			throw new \Exception("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
			return;
		} //end if else
	} //END FUNCTION


	/**
	 * Renders the tag
	 *
	 * @var \TwistTPL\Context $context
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		$context->push();
		//--
		$key = $context->get($this->name);
		//--
		if(isset($context->registers['cycle'][$key])) {
			$iteration = $context->registers['cycle'][$key];
		} else {
			$iteration = 0;
		} //end if else
		//--
		$result = $context->get($this->variables[$iteration]);
		//--
		$iteration += 1;
		//--
		if($iteration >= count($this->variables)) {
			$iteration = 0;
		} //end if
		//--
		$context->registers['cycle'][$key] = $iteration;
		//--
		$context->pop();
		//--
		return (string) $result;
		//--
	} //END FUNCTION


	/**
	 * Extract variables from a string of markup
	 *
	 * @param string $markup
	 *
	 * @return array;
	 */
	private function variablesFromString(?string $markup) : array {
		//--
		$regexp = new \TwistTPL\Regexp('/\s*('.\TwistTPL\Twist::get('QUOTED_FRAGMENT').')\s*/');
		//--
		$parts = (array) \explode(',', (string)$markup);
		//--
		$result = array();
		foreach($parts as $kk => $part) {
			$regexp->match($part);
			if(!empty($regexp->matches[1])) {
				$result[] = $regexp->matches[1];
			} //end if
		} //end foreach
		//--
		return (array) $result;
		//--
	} //END FUNCTION


} //END CLASS

// #end
