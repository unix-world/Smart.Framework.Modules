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
 * Allows output of Twist code on a page without being parsed.
 *
 * Example:
 *
 *     {% verbatim %}{{ 5 | plus: 6 }}{% endverbatim %} is equal to 11.
 *
 *     will return:
 *     {{ 5 | plus: 6 }} is equal to 11.
 */

final class TagVerbatim extends \TwistTPL\AbstractBlock {


	/**
	 * @param array $tokens
	 */
	public function parse(array &$tokens) : void {
		//--
		$tagRegexp = new \TwistTPL\Regexp('/^'.\TwistTPL\Twist::get('TAG_START').'\s*(\w+)\s*(.*)?'.\TwistTPL\Twist::get('TAG_END') . '$/');
		//--
		$this->nodelist = []; // reset
		//--
		while(count($tokens)) {
			//--
			$token = array_shift($tokens);
			//--
			if($tagRegexp->match($token)) {
				if($tagRegexp->matches[1] === $this->blockDelimiter()) { // if found the proper block delimiter just end parsing here and let the outer block proceed
					break;
				} //end if
			} //end if
			//--
			$this->nodelist[] = $token;
			//--
		} //end while
		//--
	} //END FUNCTION


} //END CLASS

// #end
