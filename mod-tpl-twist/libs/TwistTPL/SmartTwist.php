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


	public static function create_idtxt(?string $str) { // id_txt: Id-Txt
		$str = (string) str_replace('_', '-', (string)$str);
		$str = (string) SmartUnicode::uc_words((string)$str);
		return (string) $str;
	} //END FUNCTION


	public static function create_slug(?string $str) {
		return (string) \Smart::create_slug((string)$str, false); // do not apply strtolower as it can be later combined with |lower flag
	} //END FUNCTION


	public static function create_htmid(?string $str) {
		return (string) \Smart::create_htmid((string)$str);
	} //END FUNCTION


	public static function create_jsvar(?string $str) {
		return (string) \Smart::create_jsvar((string)$str);
	} //END FUNCTION


	public static function escape_html(?string $str) {
		return (string) \Smart::escape_html((string)$str);
	} //END FUNCTION


	public static function escape_js(?string $str) {
		return (string) \Smart::escape_js((string)$str);
	} //END FUNCTION


	public static function escape_json(?string $json) {
		$json = (string) \Smart::json_encode(\Smart::json_decode($json, true), false, true, true); // it MUST be JSON with HTML-Safe Options.
		$json = (string) trim((string)$json);
		if((string)$json == '') {
			$json = 'null'; // ensure a minimal json as empty string if no expr !
		} //end if
		return (string) $json;
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


	public static function nl_2_br(?string $str) {
		return (string) \Smart::nl_2_br((string)$str);
	} //END FUNCTION


	public static function smart_list(?string $str) {
		return (string) \str_replace(['<', '>'], ['‹', '›'], (string)$str); // {{{SYNC-SMARTLIST-BRACKET-REPLACEMENTS}}}
	} //END FUNCTION


	public static function trim_whitespaces(?string $str) {
		return (string) \trim((string)$str);
	} //END FUNCTION


	public static function encode_bin2hex(?string $str) {
		return (string) \bin2hex((string)$str);
	} //END FUNCTION


	public static function encode_base64(?string $str) {
		return (string) \base64_encode((string)$str);
	} //END FUNCTION


	public static function hash_sha1(?string $str) {
		return (string) \sha1((string)$str);
	} //END FUNCTION


	public static function is_nscalar($val) {
		return (bool) \Smart::is_nscalar($val);
	} //END FUNCTION


	public static function is_array_or_nscalar($val) {
		return (bool) (\Smart::is_nscalar($val) || \is_array($val));
	} //END FUNCTION


	public static function str_len($str) {
		return (int) \SmartUnicode::str_len((string)$str);
	} //END FUNCTION


	public static function arr_size($arr) {
		return (int) \Smart::array_size($arr); // int
	} //END FUNCTION


	public static function arr_first($arr) {
		if(!\is_array($arr)) {
			return null;
		} //end if
		return \reset($arr); // mixed
	} //END FUNCTION


	public static function arr_last($arr) {
		if(!\is_array($arr)) {
			return null;
		} //end if
		return \end($arr); // mixed
	} //END FUNCTION


} //END CLASS

// #end
