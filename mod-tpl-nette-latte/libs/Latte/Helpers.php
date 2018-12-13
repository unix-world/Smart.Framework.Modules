<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 * (c) 2018 unix-world.org
 */

namespace Latte;

// contains fixes by unixman

/**
 * Latte helpers.
 * @internal
 */
class Helpers {


	/** @var array  empty (void) HTML elements */
	public static $emptyElements = [
		'img' => 1, 'hr' => 1, 'br' => 1, 'input' => 1, 'meta' => 1, 'area' => 1, 'embed' => 1, 'keygen' => 1, 'source' => 1, 'base' => 1,
		'col' => 1, 'link' => 1, 'param' => 1, 'basefont' => 1, 'frame' => 1, 'isindex' => 1, 'wbr' => 1, 'command' => 1, 'track' => 1,
	];


	// added by unixman
	public static function errGetLast($key) {
		//--
		$err = '';
		//--
		$arr = @error_get_last();
		if($key && is_array($arr)) {
			$err = (string) $arr[(string)$key];
		} //end if
		//--
		return (string) $err;
		//--
	} //END FUNCTION


	/**
	 * Checks callback.
	 * @return callable
	 */
	public static function checkCallback($callable) {
		//--
		if(!is_callable($callable, false, $text)) {
			throw new \InvalidArgumentException("Callback '$text' is not callable.");
		} //end if
		//--
		return $callable;
		//--
	} //END FUNCTION


	/**
	 * Finds the best suggestion.
	 * @return string|null
	 */
	public static function getSuggestion(array $items, $value) {
		//--
		$best = null;
		$min = (strlen($value) / 4 + 1) * 10 + 0.1;
		//--
		foreach(array_unique($items, SORT_REGULAR) as $item) {
			$item = is_object($item) ? $item->getName() : $item;
			if(($len = levenshtein($item, $value, 10, 11, 10)) > 0 && $len < $min) {
				$min = $len;
				$best = $item;
			} //end if
		} //end foreach
		//--
		return $best;
		//--
	} //END FUNCTION


	/**
	 * @return bool
	 */
	public static function removeFilter(&$modifier, $filter) {
		//--
		$modifier = preg_replace('#\|(' . $filter . ')\s?(?=\||\z)#i', '', $modifier, -1, $found);
		//--
		return (bool) $found;
		//--
	} //END FUNCTION


	/**
	 * Starts the $haystack string with the prefix $needle?
	 * @return bool
	 */
	public static function startsWith($haystack, $needle) {
		//--
		return strncmp($haystack, $needle, strlen($needle)) === 0;
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
