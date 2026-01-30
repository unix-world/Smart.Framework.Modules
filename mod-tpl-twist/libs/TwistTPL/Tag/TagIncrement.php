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
 * Used to increment a counter into a template
 *
 * Example:
 *
 *     {% increment value %}
 *
 * @author Viorel Dram
 */
final class TagIncrement extends \TwistTPL\AbstractTag {
	/**
	 * Name of the variable to increment
	 *
	 * @var string
	 */
	private $toIncrement = null;


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
			$this->toIncrement = (string) \trim((string)($syntax->matches[0] ?? null));
			if((string)$this->toIncrement == '') {
				throw new \Exception('Syntax Error in `increment` (2) - Valid syntax: increment [var]');
				return;
			} //end if
		} else {
			throw new \Exception('Syntax Error in `increment` (1) - Valid syntax: increment [var]');
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
		//-- If the value is not set in the environment check to see if it exists in the context, and if not set it to -1
		if(!isset($context->environments[0][$this->toIncrement])) {
			//-- check for a context value
			$fromContext = $context->get($this->toIncrement);
			//-- already have a value in the context
			$context->environments[0][$this->toIncrement] = (null !== $fromContext) ? $fromContext : -1;
			//--
		} //end if
		//-- increment the value
		$context->environments[0][$this->toIncrement]++;
		//--
		return '';
		//--
	} //END FUNCTION


} //END CLASS

// #end
