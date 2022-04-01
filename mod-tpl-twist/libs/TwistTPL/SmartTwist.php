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

	private const SPECIAL_SOFT_HYPHEN = "\u{00AD}"; // {{{SYNC-ESCAPE-BRACKET-SYNTAX-TWIST-TWIG}}}


	public static function escapeSyntax(?string $str, bool $safe=true) : string {
		//-- {{{SYNC-ESCAPE-BRACKET-SYNTAX-TWIST-TWIG}}}
		if($safe === false) { // use this with care ... the html entities may be converted back if post processed by DOM/Tidy ... which may lead to possible insecure injections
			return (string) \strtr(
				(string)\SmartMarkersTemplating::prepare_nosyntax_html_template((string)$str),
				[
					'{{' => '&lbrace;&lbrace;',
					'}}' => '&rbrace;&rbrace;',
					'{%' => '&lbrace;&percnt;',
					'%}' => '&percnt;&rbrace;',
					'{#' => '&lbrace;&num;',
					'#}' => '&num;&rbrace;',
				]
			);
		} else {
			return (string) \strtr(
				(string)\SmartMarkersTemplating::prepare_nosyntax_content((string)$str),
				[
					'{{' => '{'.self::SPECIAL_SOFT_HYPHEN.'{',
					'}}' => '}'.self::SPECIAL_SOFT_HYPHEN.'}',
					'{%' => '{'.self::SPECIAL_SOFT_HYPHEN.'%',
					'%}' => '%'.self::SPECIAL_SOFT_HYPHEN.'}',
					'{#' => '{'.self::SPECIAL_SOFT_HYPHEN.'#',
					'#}' => '#'.self::SPECIAL_SOFT_HYPHEN.'}',
				]
			);
		} //end if else
		//--
	} //END FUNCTION


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
