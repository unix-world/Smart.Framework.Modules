<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class BlockquoteConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	private const BLOCKQUOTE_V1 = false;


	public function getSupportedTags() : array {
		//--
		return [ 'blockquote' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		// Contents should have already been converted to Markdown by this point,
		// so we just need to add between blockquote.
		//--
		$quoteContent = (string) \trim((string)$element->getValue());
		$quoteContent = (string) \str_replace(["\r\n", "\r"], "\n", (string)$quoteContent);
		if((string)\trim((string)$quoteContent) == '') {
			return '';
		} //end if
		//--
		$lines = (array) \explode("\n", (string)$quoteContent);
		$totalLines = (int) \count((array)$lines);
		//--
		$markdown = '';
		foreach($lines as $i => $line) {
			if(self::BLOCKQUOTE_V1 === true) {
				$markdown .= '> '.$line."\n";
			} else {
				$markdown .= $line."\n";
			} //end if else
			if((int)($i + 1) == (int)$totalLines) {
				$markdown .= "\n";
			} //end if
		} //end foreach
		//--
		if(self::BLOCKQUOTE_V1 === true) {
			return "\n".\trim((string)$markdown)."\n";
		} else {
			return "\n".'<<<'."\n".\trim((string)$markdown)."\n".'<<<'."\n";
		} //end if
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
