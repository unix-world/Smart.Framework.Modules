<?php

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\SmartFixes;


class PreformattedConverter implements ConverterInterface {


	public function getSupportedTags(): array {
		//--
		return ['pre'];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		//-- fix by unixman
		$language = (string) \trim((string)($element->getAttribute('data-language') ?? ''));
		if(\strpos((string)$language, 'language-') === 0) {
			$language = (string) \substr((string)$language, 9);
		} //end if
		//--
		$code = (string) $element->getValue();
		$code = (string) SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-MKDW-CODE-FIX-SPECIALS}}}
		$spacer = "\n".' '."\n";
	//	$spacer = "\n".'\\'."\n";
	//	$spacer = "\n";
		return $spacer.'```'.($language ? $language : '')."\n" . (string)\trim((string)$code) . "\n".'```'.$spacer.'\\'."\n"; // if only one line and not double the code area will not pre-format (will loose indentation ...) ; and double spaces will be lost because what is described below !
		//--
	} //END FUNCTION


} //END CLASS


// #end
