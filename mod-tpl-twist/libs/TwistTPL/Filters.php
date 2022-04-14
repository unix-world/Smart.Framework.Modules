<?php

// TODO: Dissalow Objects ; Allow Only nScalar and Arrays
// Check Twig Compatibility
// Add Smart Escapes ...

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
 * A selection of standard filters.
 */
final class Filters {

	// ::

	// {{{SYNC-TPL-TWIST-FILTER-NAMES}}}

	// TODO: finalize all filters here ...
	// IMPORTANT: syntax or syntaxhtml escaping is not quite possible under this kind of TPL systems {{ ... }}

// MTPL, TODO:
//		dec1..4
//		subtxt
//		substr


	/**
	 * Pseudo-filter: negates auto-added html (escape) filter, for the ESCAPE_HTML_BY_DEFAULT=yes situation !
	 * If ESCAPE_HTML_BY_DEFAULT is set to YES (as default is), will negate the |html filter (to be used when sending HTML Safe Code from PHP to Template only)
	 * If ESCAPE_HTML_BY_DEFAULT is set to NO it should not be used at all
	 * @param string $input
	 * @return string
	 */
	public static function filter__raw($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) $input;
	} //END FUNCTION


	public static function filter__bool($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		if($input) {
			return 'true';
		} //end if
		return 'false';
	} //END FUNCTION


	public static function filter__int($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) (int) $input;
	} //END FUNCTION


	public static function filter__num($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) (float) $input;
	} //END FUNCTION


	/**
	 * Replace each newline (\n) with a html break
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function filter__nl2br($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::nl_2_br((string)$input);
	} //END FUNCTION


	public static function filter__idtxt($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::create_idtxt((string)$input);
	} //END FUNCTION


	public static function filter__slug($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::create_slug((string)$input);
	} //END FUNCTION


	public static function filter__htmid($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::create_htmid((string)$input);
	} //END FUNCTION


	/**
	 * Escape a string for Safe Use in a HTML Context
	 * If ESCAPE_HTML_BY_DEFAULT is set to YES (as default is) this filter will be applied automatically, no need to use in this case
	 * If ESCAPE_HTML_BY_DEFAULT is set to NO it must be applied at the end for any variable that is sent from PHP to the HTML Template
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=no): <p title="{{ titleTextFromPHP |html }}">{{ SomeTextFromPHP |html }}</p><script>var jsVar = '{{ valueFromPHP |js |html }}';</script>
	 * @param string $input
	 * @return string
	 */
	public static function filter__html($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::escape_html((string)$input);
	} //END FUNCTION


	/**
	 * Escape a string for Safe Use in a HTML Javascript <script>...<script> (file.html) or Javascript only Context (file.js)
	 * It can be safe includded between single or double quotes
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=yes ; in a HTML Javscript context file.html): <script>var jsVar = '{{ valueFromPHP |js }}';</script>
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=yes ; in a Javscript only context file.js): 	var jsVar = '{{ valueFromPHP |raw |js }}';
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=no ;  in a HTML Javscript context file.html): <script>var jsVar = '{{ valueFromPHP |js |html }}';</script>
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=no ;  in a Javscript only context file.js): 	var jsVar = '{{ valueFromPHP |js }}';
	 * @param string $input
	 * @return string
	 */
	public static function filter__js($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::escape_js((string)$input);
	} //END FUNCTION


	public static function filter__json($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::escape_json((string)$input);
	} //END FUNCTION


	public static function filter__jsvar($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::create_jsvar((string)$input);
	} //END FUNCTION


	/**
	 * Escape a string for Safe Use in a HTML (file.html) or Javascript only Context (file.js)
	 * It can be safe includded between single or double quotes
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=yes ; in a HTML Javscript context file.html): <script>var jsUrl = 'https://myUrl?param={{ valueFromPHP |url |js }}';</script>
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=yes ; in a Javscript only context file.js): 	var jsUrl = 'https://myUrl?param={{ valueFromPHP |raw |url |js }}';
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=no ;  in a HTML Javscript context file.html): <script>var jsUrl = 'https://myUrl?param={{ valueFromPHP |url |js |html }}';</script>
	 * Ex.(ESCAPE_HTML_BY_DEFAULT=no ;  in a Javscript only context file.js): 	var jsUrl = 'https://myUrl?param={{ valueFromPHP |url |js }}';
	 * @param string $input
	 * @return string
	 */
	public static function filter__url($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::escape_url((string)$input);
	} //END FUNCTION


	/**
	 * Escape a string for Safe Use in a HTML Css <style>...</style> (file.html) or Css only Context (file.css)
	 * Ex: ( LIKE EXPLAINED IN THE SAMPLES FOR ABOVE METHODS ... )
	 * @param string $input
	 * @return string
	 */
	public static function filter__css($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::escape_css((string)$input);
	} //END FUNCTION


	/**
	 * Capitalize words in the input string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function filter__ucwords($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::uc_words((string)$input);
	} //END FUNCTION


	/**
	 * Capitalize first word in the input string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function filter__ucfirst($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::uc_first((string)$input);
	} //END FUNCTION


	/**
	 * Make the input string uppercase
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function filter__upper($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::str_toupper((string)$input);
	} //END FUNCTION


	/**
	 * Make the input string lowercase
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function filter__lower($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::str_tolower((string)$input);
	} //END FUNCTION


	public static function filter__trim($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::trim_whitespaces((string)$input);
	} //END FUNCTION


	public static function filter__smartlist($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::smart_list((string)$input);
	} //END FUNCTION


	public static function filter__hex($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::encode_bin2hex((string)$input);
	} //END FUNCTION


	public static function filter__b64($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::encode_base64((string)$input);
	} //END FUNCTION


	public static function filter__sha1($input) {
		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
			$input = null;
		} //end if
		return (string) \TwistTPL\SmartTwist::hash_sha1((string)$input);
	} //END FUNCTION


	//----------------


	public static function filter__arrsize($input) {
		return (int) \TwistTPL\SmartTwist::arr_size($input); // int
	} //END FUNCTION


	public static function filter__arrfirst($input) {
		return \TwistTPL\SmartTwist::arr_first($input); // mixed
	} //END FUNCTION


	public static function filter__arrlast($input) {
		return \TwistTPL\SmartTwist::arr_last($input); // mixed
	} //END FUNCTION


	//----------------


	/**
	 * Default, as a Value if empty or null
	 *
	 * @param string $input
	 * @param string $default_value
	 *
	 * @return string | mixed
	 */
	public static function filter__default($input, $default_value) {
		//--
	//	$isBlank = $input == '' || $input === false || $input === null;
		$isBlank = $input == '' || $input === null || empty($input);
		//--
		return $isBlank ? $default_value : $input;
		//--
	} //END FUNCTION


	//----------------

//
//	/**
//	 * Add one string to another
//	 *
//	 * @param string $input
//	 * @param string $string
//	 *
//	 * @return string
//	 */
//	public static function append($input, $string) {
//		if(!\TwistTPL\SmartTwist::is_nscalar($input)) { // arrays are not supported by this filter
//			$input = null;
//		} //end if
//		if(!\TwistTPL\SmartTwist::is_nscalar($string)) { // arrays are not supported by this filter
//			$string = null;
//		} //end if
//		return (string) $input.$string;
//	}
//
//	/**
//	 * Prepend a string to another
//	 *
//	 * @param string $input
//	 * @param string $string
//	 *
//	 * @return string
//	 */
//	public static function prepend($input, $string)
//	{
//		return $string . $input;
//	}
//
//	/**
//	 * @param mixed $input number
//	 *
//	 * @return int
//	 */
//	public static function ceil($input)
//	{
//		return (int) ceil((float)$input);
//	}
//
//	/**
//	 * @param mixed $input number
//	 *
//	 * @return int
//	 */
//	public static function floor($input)
//	{
//		return (int) floor((float)$input);
//	}
//
//	/**
//	 * Formats a date using strftime
//	 *
//	 * @param mixed $input
//	 * @param string $format
//	 *
//	 * @return string
//	 */
//	public static function date($input, $format)
//	{
//		if (!is_numeric($input)) {
//			$input = strtotime($input);
//		}
//
//		if ($format == 'r') {
//			return date($format, $input);
//		}
//
//		return strftime($format, $input);
//	}
//
//	/**
//	 * division
//	 *
//	 * @param float $input
//	 * @param float $operand
//	 *
//	 * @return float
//	 */
//	public static function divided_by($input, $operand) {
//		if((float)$operand == 0) {
//			return 'NAN'; // avoid division by zero
//		}
//		return (float)$input / (float)$operand;
//	}
//
//
//	/**
//	 * Joins elements of an array with a given character between them
//	 *
//	 * @param array|\Traversable $input
//	 * @param string $glue
//	 *
//	 * @return string
//	 */
//	public static function join($input, $glue = ' ')
//	{
//		if ($input instanceof \Traversable) {
//			$str = '';
//			foreach ($input as $elem) {
//				if ($str) {
//					$str .= $glue;
//				}
//				$str .= $elem;
//			}
//			return $str;
//		}
//		return is_array($input) ? implode($glue, $input) : $input;
//	}
//
//
//
//	/**
//	 * @param string $input
//	 *
//	 * @return string
//	 */
//	public static function lstrip($input)
//	{
//		return ltrim($input);
//	}
//
//
//
//	/**
//	 * addition
//	 *
//	 * @param float $input
//	 * @param float $operand
//	 *
//	 * @return float
//	 */
//	public static function plus($input, $operand)
//	{
//		return (float)$input + (float)$operand;
//	}
//
//
//	/**
//	 * subtraction
//	 *
//	 * @param float $input
//	 * @param float $operand
//	 *
//	 * @return float
//	 */
//	public static function minus($input, $operand)
//	{
//		return (float)$input - (float)$operand;
//	}
//
//
//	/**
//	 * modulo
//	 *
//	 * @param float $input
//	 * @param float $operand
//	 *
//	 * @return float
//	 */
//	public static function modulo($input, $operand)
//	{
//		return fmod((float)$input, (float)$operand);
//	}
//
//
//
//	/**
//	 * Remove a substring
//	 *
//	 * @param string $input
//	 * @param string $string
//	 *
//	 * @return string
//	 */
//	public static function remove($input, $string)
//	{
//		return str_replace($string, '', $input);
//	}
//
//
//	/**
//	 * Remove the first occurrences of a substring
//	 *
//	 * @param string $input
//	 * @param string $string
//	 *
//	 * @return string
//	 */
//	public static function remove_first($input, $string)
//	{
//		if (($pos = strpos($input, $string)) !== false) {
//			$input = substr_replace($input, '', $pos, strlen($string));
//		}
//
//		return $input;
//	}
//
//
//	/**
//	 * Replace occurrences of a string with another
//	 *
//	 * @param string $input
//	 * @param string $string
//	 * @param string $replacement
//	 *
//	 * @return string
//	 */
//	public static function replace($input, $string, $replacement = '')
//	{
//		return str_replace($string, $replacement, $input);
//	}
//
//
//	/**
//	 * Replace the first occurrences of a string with another
//	 *
//	 * @param string $input
//	 * @param string $string
//	 * @param string $replacement
//	 *
//	 * @return string
//	 */
//	public static function replace_first($input, $string, $replacement = '')
//	{
//		if (($pos = strpos($input, $string)) !== false) {
//			$input = substr_replace($input, $replacement, $pos, strlen($string));
//		}
//
//		return $input;
//	}
//
//
//	/**
//	 * Reverse the elements of an array
//	 *
//	 * @param array|\Traversable $input
//	 *
//	 * @return array
//	 */
//	public static function reverse($input)
//	{
//		return array_reverse($input);
//	}
//
//
//	/**
//	 * Round a number
//	 *
//	 * @param float $input
//	 * @param int $n precision
//	 *
//	 * @return float
//	 */
//	public static function round($input, $n = 0)
//	{
//		return round((float)$input, (int)$n);
//	}
//
//
//	/**
//	 * @param string $input
//	 *
//	 * @return string
//	 */
//	public static function rstrip($input)
//	{
//		return rtrim($input);
//	}
//
//
//	/**
//	 * Return the size of an array or of an string
//	 *
//	 * @param mixed $input
//	 * @throws \Exception
//	 * @return int
//	 */
//	public static function size($input)
//	{
//
//		if (is_array($input)) {
//			return count($input);
//		}
//
//		// only plain values and stringable objects left at this point
//		return strlen($input);
//	}
//
//
//	/**
//	 * @param array|\Iterator|string $input
//	 * @param int $offset
//	 * @param int $length
//	 *
//	 * @return array|\Iterator|string
//	 */
//	public static function slice($input, $offset, $length = null)
//	{
//		if (is_array($input)) {
//			$input = array_slice($input, $offset, $length);
//		} else { // if (is_string($input)) {
//			$input = mb_substr($input, $offset, $length);
//		}
//
//		return $input;
//	}
//
//
//	/**
//	 * Sort the elements of an array
//	 *
//	 * @param array $input
//	 * @param string $property use this property of an array element
//	 *
//	 * @return array
//	 */
//	public static function sort($input, $property = null)
//	{
//		if ($property === null) {
//			asort($input);
//		} else {
//			$first = reset($input);
//			if ($first !== false && is_array($first) && array_key_exists($property, $first)) {
//				uasort($input, function ($a, $b) use ($property) {
//					if ($a[$property] == $b[$property]) {
//						return 0;
//					}
//
//					return $a[$property] < $b[$property] ? -1 : 1;
//				});
//			}
//		}
//
//		return $input;
//	}
//
//	/**
//	 * Explicit string conversion.
//	 *
//	 * @param mixed $input
//	 *
//	 * @return string
//	 */
///*
//	public static function string($input)
//	{
//		return strval($input);
//	}
//*/
//
//	/**
//	 * Split input string into an array of substrings separated by given pattern.
//	 *
//	 * @param string $input
//	 * @param string $pattern
//	 *
//	 * @return array
//	 */
//	public static function split($input, $pattern)
//	{
//		return explode($pattern, $input);
//	}
//
//
//	/**
//	 * @param string $input
//	 *
//	 * @return string
//	 */
//	public static function strip($input)
//	{
//		return trim($input);
//	}
//
//
//	/**
//	 * Removes html tags from text
//	 *
//	 * @param string $input
//	 *
//	 * @return string
//	 */
//	public static function strip_html($input)
//	{
//		return is_string($input) ? strip_tags($input) : $input;
//	}
//
//
//	/**
//	 * Strip all newlines (\n, \r) from string
//	 *
//	 * @param string $input
//	 *
//	 * @return string
//	 */
//	public static function strip_newlines($input)
//	{
//		return is_string($input) ? str_replace(array(
//			"\n", "\r"
//		), '', $input) : $input;
//	}
//
//
//	/**
//	 * multiplication
//	 *
//	 * @param float $input
//	 * @param float $operand
//	 *
//	 * @return float
//	 */
//	public static function times($input, $operand)
//	{
//		return (float)$input * (float)$operand;
//	}
//
//
//	/**
//	 * Truncate a string down to x characters
//	 *
//	 * @param string $input
//	 * @param int $characters
//	 * @param string $ending string to append if truncated
//	 *
//	 * @return string
//	 */
//	public static function truncate($input, $characters = 100, $ending = '...')
//	{
//		if (is_string($input) || is_numeric($input)) {
//			if (strlen($input) > $characters) {
//				return mb_substr($input, 0, $characters) . $ending;
//			}
//		}
//
//		return $input;
//	}
//
//
//	/**
//	 * Truncate string down to x words
//	 *
//	 * @param string $input
//	 * @param int $words
//	 * @param string $ending string to append if truncated
//	 *
//	 * @return string
//	 */
//	public static function truncatewords($input, $words = 3, $ending = '...')
//	{
//		if (is_string($input)) {
//			$wordlist = explode(" ", $input);
//
//			if (count($wordlist) > $words) {
//				return implode(" ", array_slice($wordlist, 0, $words)) . $ending;
//			}
//		}
//
//		return $input;
//	}
//
//
//	/**
//	 * Remove duplicate elements from an array
//	 *
//	 * @param array|\Traversable $input
//	 *
//	 * @return array
//	 */
//	public static function uniq($input)
//	{
//		return array_unique($input);
//	}


} //END CLASS

// #end
