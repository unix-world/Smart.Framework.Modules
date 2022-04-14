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
 * Twist for PHP.
 */
final class SmartTwist {

	// ::


	public static function escape_html(?string $str) {
		return (string) \Smart::escape_html((string)$str);
	} //END FUNCTION


	public static function escape_js(?string $str) {
		return (string) \Smart::escape_js((string)$str);
	} //END FUNCTION

	public static function escape_css(?string $str) {
		return (string) \Smart::escape_css((string)$str);
	} //END FUNCTION


	public static function escape_url(?string $str) {
		return (string) \Smart::escape_url((string)$str);
	} //END FUNCTION


	public static function uc_words(?string $str) {
		return (string) \SmartUnicode::uc_words((string)$str);
	} //END FUNCTION


	public static function uc_first(?string $str) {
		return (string) \SmartUnicode::uc_first((string)$str);
	} //END FUNCTION


	public static function str_toupper(?string $str) {
		return (string) \SmartUnicode::str_toupper((string)$str);
	} //END FUNCTION


	public static function str_tolower(?string $str) {
		return (string) \SmartUnicode::str_tolower((string)$str);
	} //END FUNCTION


	public static function is_nscalar($val) {
		return (bool) \Smart::is_nscalar($val);
	} //END FUNCTION


	public static function is_array_or_nscalar($val) {
		return (bool) (\Smart::is_nscalar($val) || \is_array($val));
	} //END FUNCTION


	public static function str_len($ytext) {
		return (int) \SmartUnicode::str_len((string)$ytext);
	} //END FUNCTION


} //END CLASS

// #end
