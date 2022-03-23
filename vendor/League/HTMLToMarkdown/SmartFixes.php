<?php

namespace League\HTMLToMarkdown;

final class SmartFixes {


	public static function getCharset() : string {
		//--
		return (string) \SMART_FRAMEWORK_CHARSET;
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
		return (string) strtr((string)$content, (array)$arr);
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
