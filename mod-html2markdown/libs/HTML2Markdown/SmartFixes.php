<?php

namespace HTML2Markdown;

final class SmartFixes {

	public const MKDW_TAG_BOLD = '**';
	public const MKDW_TAG_ITALIC = '==';
	public const MKDW_TAG_LI = '-';
	public const MKDW_TAG_LI_ALT = '+';

	//-- {{{SYNC-SPECIAL-CHARACTER-MKDW-CONVERTER}}}
	public const SPECIAL_CHAR_NEWLINE_MARK = "\u{2051}"; // unicode ⁑
	public const SPECIAL_CHAR_NEWLINE_REPL = '&#8273;'; // restore as html entity
	//-- #end {{{SYNC-SPECIAL-CHARACTER-MKDW-CONVERTER}}}

	public const PATTERN_INLINE_CODE = '/[`]{3}.*?[`]{3}/s'; // Inline Code ; sync with lib markdown v2

//	public const FIX_ESCAPES_INLINE_SYNTAX = [
//		'*' => '\\*', // strong
//		'=' => '\\=', // em
//		'~' => '\\~', // strike
//		'_' => '\\_', // underline
//		'-' => '\\-',
//		'+' => '\\+',
//		'!' => '\\!',
//		'^' => '\\^',
//		',' => '\\,', // inline quote
//		'$' => '\\$', // can be used for math
//		'?' => '\\?', // inline term def, ; cannot use dt/dd
//		'`' => '\\`', //
//	];

	public const FIX_ESCAPES_ENTITIES_VBRACKS = [
		'\\<' 	=> '&lt;',
		'\\>' 	=> '&gt;',
		'<' 	=> '&lt;',
		'>' 	=> '&gt;',
	];

	public const FIX_ESCAPES_ENTITIES_RBRACKS = [ // ex for links: a
		'\\[' 	=> '&lbrack;',
		'\\]' 	=> '&rbrack;',
		'[' 	=> '&lbrack;',
		']' 	=> '&rbrack;',
	];

	public const FIX_ESCAPES_ENTITIES_BRACKS = [ // ex for links: a
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


	public static function createHtmSafeClassName(?string $code) : string {
		//--
		$code = (string) self::normalizeWhiteSpaces((string)\trim((string)$code));
		$arr = (array) \explode(' ', (string)$code);
		$code = '';
		$classes = [];
		foreach($arr as $key => $val) {
			$val = (string) \trim((string)$val);
			if((string)$val != '') {
				$val = (string) \Smart::create_htmid((string)$val);
				if((string)$val != '') {
					$cls = 'h2m_'.$val;
					if(!\in_array((string)$cls, (array)$classes)) {
						$classes[] = (string) $cls;
					} //end if
				} //end if
			} //end if
		} //end foreach
		//--
		return (string) \trim((string)\implode('$', (array)$classes));
		//--
	} //END FUNCTION


	public static function normalizeWhiteSpaces(?string $code) : string {
		//--
		return (string) \Smart::normalize_spaces((string)$code);
		//--
	} //END FUNCTION


	public static function normalizeNewLines(?string $code) : string { // renamed from normalizeSpaces
		//--
		$code = (string) \str_replace([ "\r\n", "\r" ], "\n", (string)$code);
		$code = (string) str_replace([ "\t", "\x0B", "\0", "\f" ], ' ', (string)$code);
		return (string) $code;
		//--
	} //END FUNCTION


	public static function normalizeMultiConsecutiveEmptyLines(?string $code) : string {
		//--
		$code = (string) self::revertSpecials((string)$code);
		$code = (string) preg_replace('/^\s*[\n]{1,}/m', '', (string)$code); // fix: replace multiple consecutive lines that may also contain before optional leading spaces
		$code = (string) preg_replace('/[^\S\r\n]+$/m', '', (string)$code); // remove trailing spaces on each line
		//--
		return (string) $code;
		//--
	} //END FUNCTION


	public static function revertSpecials(?string $code) {
		//--
		return (string) \strtr((string)$code, [
			(string) "\n".self::SPECIAL_CHAR_NEWLINE_MARK => "\n", 	// handle newline enforce clear newline
			(string)  ' '.self::SPECIAL_CHAR_NEWLINE_MARK => '', 	// like above but can happen after newline to space conversions
			(string)      self::SPECIAL_CHAR_NEWLINE_MARK => '',  	// final fix (prior this has been converted to the coresponding html entity)
		]);
		//--
	} //END FUNCTION


	public static function decodeHtmlEntity(?string $code) : string {
		//--
		return (string) \html_entity_decode((string)$code, \ENT_QUOTES | \ENT_HTML401, (string)\SMART_FRAMEWORK_CHARSET); // | \ENT_SUBSTITUTE
		//--
	} //END FUNCTION


	public static function stripTags(?string $html): string {
		//--
		return (string) \Smart::stripTags((string)$html, true, false);
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


	public static function escapeElementContent(?string $content, ?string $type=''): string {
		//--
		$type = (string) \strtolower((string)\trim((string)$type));
		//--
		$arr = (array) \array_flip((array)\SmartMarkdownToHTML::ESCAPINGS_REPLACEMENTS);
		unset($arr['\\']);
		//--
		switch((string)$type) { // {{{SYNC-MKDW-CONVERT-SKIP-ESCAPES}}}
			case 'table':
				unset($arr['#']);
				break;
			default:
				// nothing special
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
