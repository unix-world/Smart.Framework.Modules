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
 * Performs an assignment of one variable to another
 *
 * Example:
 *
 *     {% set var = var %}
 *     {% set var = "hello" | upcase %}
 */
final class TagSet extends \TwistTPL\AbstractTag {

	/**
	 * @var string The variable to set (assign) from
	 */
	private $from = null;

	/**
	 * @var string The variable to set (assign) to
	 */
	private $to = null;


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
		$syntaxRegexp = new \TwistTPL\Regexp('/(\w+)\s*=\s*(.*)\s*/');
		//--
		if($syntaxRegexp->match($markup)) {
			$this->to = $syntaxRegexp->matches[1] ?? null;
			$syntaxRegexp->matches[2] = $syntaxRegexp->matches[2] ?? null;
			if($syntaxRegexp->matches[2]) {
				$this->from = new \TwistTPL\Variable($syntaxRegexp->matches[2], true);
			} //end if
			if(($this->to === null) OR ($this->from === null)) {
				throw new \Exception("Syntax Error in 'assign' (2) - Valid syntax: set [var] = [source]");
			} //end if
		} else {
			throw new \Exception("Syntax Error in 'assign' (1) - Valid syntax: set [var] = [source]");
		} //end if else
		//--
	} //END FUNCTION

	/**
	 * Renders the tag
	 *
	 * @param \TwistTPL\Context $context
	 *
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		$output = $this->from->render($context);
		$context->set($this->to, $output, true);
		//--
		return '';
		//--
	} //END FUNCTION


} //END CLASS

//#end
