<?php

namespace HTML2Markdown;

final class SmartFixes {

	//-- {{{SYNC-SPECIAL-CHARACTER-MKDW-CONVERTER}}}
	public const SPECIAL_CHAR_NEWLINE_MARK = "\u{2051}"; // unicode ⁑
	public const SPECIAL_CHAR_NEWLINE_REPL = '&#8273;'; // restore as html entity
	//-- #end {{{SYNC-SPECIAL-CHARACTER-MKDW-CONVERTER}}}

	public const PATTERN_INLINE_CODE = '/[`]{3}.*?[`]{3}/s'; // Inline Code ; sync with lib markdown v2

	public const FIX_ESCAPES_ENTITIES_VBRACKS = [
		'\\<' 	=> '&lt;',
		'\\>' 	=> '&gt;',
		'<' 	=> '&lt;',
		'>' 	=> '&gt;',
	];

	public const FIX_ESCAPES_ENTITIES_RBRACKS = [
		'\\[' 	=> '&lbrack;',
		'\\]' 	=> '&rbrack;',
		'[' 	=> '&lbrack;',
		']' 	=> '&rbrack;',
	];

	public const FIX_ESCAPES_ENTITIES_BRACKS = [
		'\\"' 	=> '&quot;',
		'\\(' 	=> '&lpar;',
		'\\)' 	=> '&rpar;',
		'"' 	=> '&quot;',
		'(' 	=> '&lpar;',
		')' 	=> '&rpar;',
	];

	public const FIX_BACK_ESCAPES_GENERAL = [
		'\\*' 	=> '*',
	];


	public static function getCharset() : string {
		//--
		return (string) \SMART_FRAMEWORK_CHARSET;
		//--
	} //END FUNCTION


	public static function createHtmid(?string $code) : string {
		//--
		return (string) \Smart::create_htmid((string)$code);
		//--
	} //END FUNCTION


	public static function normalizeSpaces(?string $code) : string {
		//--
		return (string) \Smart::normalize_spaces((string)$code);
		//--
	} //END FUNCTION


	public static function decodeHtmlEntity(?string $code) : string {
		//--
		return (string) \html_entity_decode((string)$code, \ENT_QUOTES | \ENT_HTML401, (string)\SMART_FRAMEWORK_CHARSET); // | \ENT_SUBSTITUTE
		//--
	} //END FUNCTION


	public static function stripTags(?string $html): string {
		//--
		return (string) \Smart::striptags((string)$html, 'yes', 'no');
		//--
	} //END FUNCTION


	public static function escapeHtml(?string $code): string {
		//--
		return (string) \Smart::escape_html((string)$code);
		//--
	} //END FUNCTION


	public static function escapeUrl(?string $code): string {
		//--
		return (string) \Smart::escape_url((string)$code);
		//--
	} //END FUNCTION


	public static function escapeCodeElementContent(?string $code): string {
		//--
		return (string) \str_replace( // {{{SYNC-MKDW-CODE-FIX-SPECIALS}}}
			[
				'\\`\\`\\`',
				'```'
			],
			[
				'∖`∖`∖`', // '∖' here is the utf-8 #8726 (a special backslash)
				'\\`\\`\\`'
			],
			(string) $code
		);
		//--
	} //END FUNCTION


	public static function escapeElementContent(?string $content, ?string $type): string {
		//--
		$arr = (array) \array_flip((array)\SmartMarkdownToHTML::ESCAPINGS_REPLACEMENTS);
		unset($arr['\\']);
		//--
		switch((string)$type) { // {{{SYNC-MKDW-CONVERT-SKIP-ESCAPES}}}
			case 'div':
			//	unset($arr['[']);
			//	unset($arr[']']);
			//	unset($arr['(']);
			//	unset($arr[')']);
				break;
			case 'table':
				unset($arr['#']);
				break;
		} //end switch
		//--
		return (string) \strtr((string)$content, (array)$arr);
		//--
	} //END FUNCTION


	public static function logNotice(?string $where, ?string $text) : void {
		//--
		\Smart::log_notice((string)$where.' # '.$text);
		//--
	} //END FUNCTION


	public static function logWarning(?string $where, ?string $text) : void {
		//--
		\Smart::log_warning((string)$where.' # '.$text);
		//--
	} //END FUNCTION


} //END CLASS


// #end
