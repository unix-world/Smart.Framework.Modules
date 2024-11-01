<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class ListBlockConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'ol', 'ul' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		return (string) "\n".$element->getValue()."\n";
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
