<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class DefaultConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	// This is the fallback for any undefined tag(s) handler ; both block and inline elements may fall here
	public const DEFAULT_CONVERTER = '_default';


	/**
	 * @return string[]
	 */
	public function getSupportedTags() : array {
		//--
		return [ (string)self::DEFAULT_CONVERTER ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		// TODO: here some element types which are not supported ex: SVG may be discarded by using
		// $tag = (string) \strtolower((string)\trim((string)$element->getTagName())); // with a switch
		//--
	//	return (string) SmartFixes::stripTags((string)$element->getChildrenAsString()); // fix by unixman
		//-- NEED TO BE SURROUNDED BY SPACES !!!! {{{SYNC-HTML2MKDW-ARROUND-SPACES}}} ; add left/right spaces which fixes a required space which is lost between word `<span>Test</span>` from `wordTest` to `word Test`
	//	$value = (string) $element->getValue();
	//	$prefix = \ltrim($value) !== $value ? ' ' : '';
	//	$suffix = \rtrim($value) !== $value ? ' ' : '';
		$prefix = '';
		$suffix = '';
		//--
		return (string) $prefix.SmartFixes::stripTags((string)$element->getChildrenAsString()).$suffix; // fix by unixman
		//--
	} //END FUNCTION


} //END CLASS

// #end
