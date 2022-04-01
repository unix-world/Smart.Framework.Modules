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
 * A selection of custom filters.
 */
class CustomFilters {

	/**
	 * Sort an array by key.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public static function sort_key(array $input) : array {
		\ksort($input);
		return (array) $input;
	} //END FUNCTION


} //END CLASS

// #end
