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
 * Creates a comment; everything inside will be ignored
 *
 * Example:
 *
 *     {% comment %} This will be ignored {% endcomment %}
 */
final class TagComment extends \TwistTPL\AbstractBlock {


	/**
	 * Renders the block
	 *
	 * @param Context $context
	 *
	 * @return string empty string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		return '';
		//--
	} //END FUNCTION


} //END CLASS

// #end
