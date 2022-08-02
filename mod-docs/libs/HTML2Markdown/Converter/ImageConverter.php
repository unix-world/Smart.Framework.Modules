<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;

class ImageConverter implements ConverterInterface {


	public function convert(ElementInterface $element): string {
		$src   = $element->getAttribute('src');
		$alt   = $element->getAttribute('alt');
		$title = $element->getAttribute('title');
		if($title !== '') {
			// No newlines added. <img> should be in a block-level element.
		//	return '![' . $alt . '](' . $src . ' "' . $title . '")';
		//	return '![' . \str_replace(['[',']'], ['\\[','\\]'], (string)$alt) . '](' . \str_replace(['(', ')'], ['\\(', '\\)'], (string)$src) . ' "' . \str_replace(['(', ')', '"'], ['\\(', '\\)', "'"], (string)$title) . '")'; // fix by unixman
			return '![' . \strtr((string)$alt, (array)SmartFixes::FIX_ESCAPES_ENTITIES_RBRACKS) . '](' . \strtr((string)$src, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS) . ' "' . \strtr((string)$title, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS) . '")'; // fix by unixman
		}
	//	return '![' . $alt . '](' . $src . ')';
	//	return '![' . \str_replace(['[',']'], ['\\[','\\]'], (string)$alt) . '](' . \str_replace(['(', ')'], ['\\(', '\\)'], (string)$src) . ')'; // fix by unixman
		return '![' . \strtr((string)$alt, (array)SmartFixes::FIX_ESCAPES_ENTITIES_RBRACKS) . '](' . \strtr((string)$src, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS) . ')'; // fix by unixman
	}


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['img'];
	}


} //END CLASS

// #end
