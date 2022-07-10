<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;


class TextConverter implements ConverterInterface {


	public function getSupportedTags(): array {
		return ['#text'];
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		//--
		$markdown = (string) $element->getValue();
		//-- #FIX#: this is breaking some headings, need to preserve lines @ docs.import-json-docs&realm=css&key=9
//		$markdown = (string) \ltrim((string)$markdown, "\n"); // Remove leftover \n at the beginning of the line
//	//	$markdown = (string) \preg_replace('~\s+~', ' ', (string)$markdown); // Replace sequences of invisible characters with spaces
//		$markdown = (string) SmartFixes::normalizeSpaces((string)$markdown);
		//-- the above is fixed here
		$arr = (array) \explode("\n", (string)$markdown);
		for($i=0; $i<\count($arr); $i++) {
			$arr[$i] = (string) SmartFixes::normalizeSpaces((string)$arr[$i]); // do not trim ; will break spaces between words ; docs.import-json-docs&realm=css&key=0
		} //end for
		$markdown = (string) \implode("\n", (array)$arr);
		$arr = null;
		//-- #END#FIX#
		if((string)$markdown == '') { // DO NOT TRIM !!
			return '';
		} //end if
		//--
		$type = '';
		if(($parent = $element->getParent()) && $parent->getTagName() === 'div') {
			$type = 'div';
		} else { // {{{SYNC-MKDW-CONVERT-SKIP-ESCAPES}}}
		//	$markdown = (string) \preg_replace('~([*_\\[\\]\\\\])~', '\\\\$1', (string)$markdown); // Escape the following characters: '*', '_', '[', ']' and '\'
		} //end if
		if(!$element->isDescendantOf(['tr'])) {
		//	$markdown = (string) \preg_replace('~^#~', '\\\\#', (string)$markdown);
		} else { // {{{SYNC-MKDW-CONVERT-SKIP-ESCAPES}}}
			$type = 'table';
		} //end if
		//--
		if((string)$markdown == ' ') {
			$next = $element->getNext();
			if(!$next || $next->isBlock()) {
				$markdown = '';
			} //end if
		} //end if
		//--
		$markdown = (string) SmartFixes::escapeElementContent((string)$markdown, (string)$type); // this is the overall place for doing this ... no need somewhere else !
		//--
		return (string) SmartFixes::escapeHtml((string)$markdown);
		//--
	} //END FUNCTION


} //END CLASS

// #end
