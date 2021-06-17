<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;

final class SmartFixes {


	public static function stripTags(?string $html): string {
		//--
		return (string) \Smart::striptags((string)$html);
		//--
	} //END FUNCTION


	public static function escapeCodeElementContent(?string $code): string {
		//--
		return (string) \str_replace( // {{{SYNC-CODE-FIX-SPECIALS-MARKDOWN}}}
			[
				'\\',
				'`'
			],
			[
				'∖', // this is utf-8, 8726, a special backslash
				'\\`'
			],
			(string) $code
		);
		//--
	} //END FUNCTION


	public static function escapeElementContent(?string $content): string {
		//--
		$content = \str_replace('\\', '\\\\', (string)$content);
		$arr = 	[ // these replacements are based on turndown.js ; adapted + modified by unixman ...
		//	'/\\/' 			=> '\\\\', // this gets error on PHP, it is replaced above using str replace
			'/\*/'			=> '\\*',
			'/^-/' 			=> '\\-',
			'/^\+ /' 		=> '\\+ ',
			'/^(\=\+)/' 	=> '\\\$1',
			'/^(\#{1,6}) /' => '\\\$1 ',
			'/`/' 			=> '\\`',
			'/^~~~/' 		=> '\\~~~',
			'/\(/' 			=> '\\(', // added by unixman
			'/\)/'			=> '\\)', // added by unixman
			'/\[/'			=> '\\[',
			'/\]/'			=> '\\]',
			'/\{/'			=> '\\{', // added by unixman
			'/\}/'			=> '\\}', // added by unixman
			'/^\>/'			=> '\\>', // this is very special, only if starts a line ... the opening < must not be escaped !
			'/_/' 			=> '\\_',
			'/^(\d+)\. /' 	=> '\$1\\. ',
			'/\|/' 			=> '\\|', // added by unixman, to support tables !
			'/^\:\:\:/'		=> '\\:::', // added by unixman to support the syntax of pandoc style divs as in smart framework
		];
		$content = (string) \preg_replace(
			(array) \array_keys((array)$arr),
			(array) \array_values((array)$arr),
			(string) $content
		);
		return (string) $content;
	} //END FUNCTION

} //END CLASS


// #end
