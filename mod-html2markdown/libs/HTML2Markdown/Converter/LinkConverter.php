<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


class LinkConverter extends AbstractConverterConfig implements ConverterInterface {


	public function getSupportedTags(): array {
		return [ 'a' ];
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		//--
		$href  = (string) \trim((string)$element->getAttribute('href'));
		$title = (string) \trim((string)$element->getAttribute('title'));
	//	$text  = (string) \trim((string)$element->getValue(), "\t\n\r\0\x0B");
		$text  = (string) \trim((string)$element->getValue(), "\0\x0B");
		//--
		$isValid = true;
		if(\strpos((string)$href, ' ') !== false) {
			$isValid = false; // invalid link, can't contain spaces !
		} elseif(stripos((string)$href, 'mailto:') !== false) {
			$isValid = false; // emails not supported in docs by default ... maybe make an option to support if enabled
		} //end if else
		if($isValid !== true) {
			return '';
		} //end if
		//--
	//	$markdown = '['.$text.']('.$href.' "'.$title.'")';
		$markdown = '';
		$markdown .= '['.\strtr((string)$text, (array)SmartFixes::FIX_ESCAPES_ENTITIES_RBRACKS).']';
		$markdown .= '('.\strtr((string)$href, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS);
		if((string)$title != '') {
			$markdown .= ' "'.\strtr((string)$title, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS).'"';
		} //end if
		$markdown .= ')';
		//--
//if(strpos($element->getValue(), '<') === false) { // does not contain tags
//print_r($element->getValue()); die();
//print_r($element->getChildren()); die();
//		if(!$element->hasChildren()) {
//die('ah');
//			$markdown = (string) SmartFixes::escapeElementContent((string)$markdown, 'a');
//		} //end if
		//--
		return (string) $markdown;
		//--
	} //END FUNCTION


} //END CLASS


// #end
