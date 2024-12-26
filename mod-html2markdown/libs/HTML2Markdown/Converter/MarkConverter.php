<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class MarkConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'mark' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		$value = (string) $element->getValue();
		//--
		$prefix = \ltrim($value) !== $value ? ' ' : '';
		$suffix = \rtrim($value) !== $value ? ' ' : '';
		//--
		return (string) $prefix.'``'.$value.'``'.$suffix;
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
