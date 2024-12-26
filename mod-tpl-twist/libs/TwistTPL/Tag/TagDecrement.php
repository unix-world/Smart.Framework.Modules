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
 * Used to decrement a counter into a template
 *
 * Example:
 *
 *     {% decrement value %}
 *
 * @author Viorel Dram
 */
final class TagDecrement extends \TwistTPL\AbstractTag {
	/**
	 * Name of the variable to decrement
	 *
	 * @var int
	 */
	private $toDecrement = null;


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
		//--
		$syntax = new \TwistTPL\Regexp('/('.\TwistTPL\Twist::get('VARIABLE_NAME').')/');
		//--
		if($syntax->match($markup)) {
			$this->toDecrement = (string) \trim((string)($syntax->matches[0] ?? null));
			if((string)$this->toDecrement == '') {
				throw new \Exception("Syntax Error in 'decrement' (2) - Valid syntax: decrement [var]");
				return;
			} //end if
		} else {
			throw new \Exception("Syntax Error in 'decrement' (1) - Valid syntax: decrement [var]");
			return;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Renders the tag
	 *
	 * @param \TwistTPL\Context $context
	 *
	 * @return string|void
	 */
	public function render(\TwistTPL\Context $context) : string {
		//-- if the value is not set in the environment check to see if it exists in the context, and if not set it to 0
		if(!isset($context->environments[0][$this->toDecrement])) {
			//-- check for a context value
			$fromContext = $context->get($this->toDecrement);
			//-- already have a value in the context
			$context->environments[0][$this->toDecrement] = (null !== $fromContext) ? $fromContext : 0;
			//--
		} //end if
		//-- decrement the value
		$context->environments[0][$this->toDecrement]--;
		//--
		return '';
		//--
	} //END FUNCTION


} //END CLASS

// #end
