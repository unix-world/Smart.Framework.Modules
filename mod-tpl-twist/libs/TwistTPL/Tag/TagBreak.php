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
 * Break iteration of the current loop
 *
 * Example:
 *
 *     {% for i in (1..5) %}
 *       {% if i == 4 %}
 *         {% break %}
 *       {% endif %}
 *       {{ i }}
 *     {% endfor %}
 */
final class TagBreak extends \TwistTPL\AbstractTag {


	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		$context->registers['break'] = true;
		//--
		return '';
		//--
	} //END FUNCTION


} //END CLASS

// #end
