<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class LinkConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags(): array {
		//--
		return [ 'a' ];
		//--
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
	//	} elseif(stripos((string)$href, 'mailto:') !== false) {
		} elseif(\stripos((string)$href, 'mailto:') === 0) {
			$isValid = false; // emails not supported in docs by default ... maybe make an option to support if enabled
		} //end if else
		if($isValid !== true) {
			return '';
		} //end if
		//--
		$href = (string) \strtr((string)$href, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS);
		$text = (string) \strtr((string)$text, (array)SmartFixes::FIX_ESCAPES_ENTITIES_RBRACKS);
		if((string)\trim((string)$text) == '') {
			if((string)\trim((string)$href) == '') {
				return ''; // no href, no text, discard
			} else {
				$text = (string) $href;
			} //end if
		} //end if
		if((string)\trim((string)$href) == '') {
			$href = '#-'; // this is a fix, without this the links with empty refs are broken on rendering
		} //end if
		//--
		$markdown = '';
		$markdown .= '['.$text.']';
		$markdown .= '('.$href;
		if((string)$title != '') {
			$markdown .= ' "'.\strtr((string)$title, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS).'"';
		} //end if
		$markdown .= ')';
		//--
		return (string) $markdown;
		//--
	} //END FUNCTION


} //END CLASS


// #end
