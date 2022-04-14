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
 * The filter bank is where all registered filters are stored, and where filter invocation is handled
 * it supports a variety of different filter types; objects, class, and simple methods.
 */

final class Filterbank {


	private const FILTER_CLASS = '\\TwistTPL\\Filters';

	/**
	 * Reference to the current context object
	 *
	 * @var Context
	 */
	private $context;


	/**
	 * Constructor
	 *
	 * @param $context
	 */
	public function __construct(Context $context) {
		//--
		$this->context = $context;
		//--
	} //END FUNCTION


	/**
	 * Invokes the filter with the given name
	 *
	 * @param string $name The name of the filter
	 * @param string $value The value to filter
	 * @param array $args The additional arguments for the filter
	 *
	 * @return string
	 */
	public function invoke($name, $value, array $args=[]) {

		//-- {{{SYNC-TPL-TWIST-FILTER-NAMES}}}
		$name = (string) \strtolower((string)\trim((string)$name));
		$original_name = (string) $name;
		switch((string)$name) {
			case '':
				return;
			default:
				$name = 'filter__'.$name;
		} //end switch
		//-- #end sync

		\array_unshift($args, $value);

		// If we have a callback
		if(!\method_exists(self::FILTER_CLASS, $name)) {
			throw new \Exception('Invalid Filter Name: `'.$original_name.'`');
			return '';
		} //end if

		return \call_user_func_array([ self::FILTER_CLASS, $name ], $args); // mixed

	} //END FUNCTION

} //END CLASS

// #end
