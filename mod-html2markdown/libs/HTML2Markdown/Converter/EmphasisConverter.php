<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class EmphasisConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	protected $config;


	public function getSupportedTags(): array {
		//--
		return ['em', 'i', 'strong', 'b'];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		//--
		$tag   = (string) $this->getNormTag($element);
		$value = (string) $element->getValue();
		//--
		if(!\trim((string)$value)) {
			return (string) $value;
		} //end if
		//--
		if((string)$tag == 'em') {
			$style = (string) $this->getConfig('italic_style', (string)SmartFixes::MKDW_TAG_ITALIC);
		} else {
			$style = (string) $this->getConfig('bold_style', (string)SmartFixes::MKDW_TAG_BOLD);
		} //end if else
		//--
		$prefix = \ltrim($value) !== $value ? ' ' : '';
		$suffix = \rtrim($value) !== $value ? ' ' : '';
		//--
		$preStyle = (string) $style; // fix by unixman
		$postStyle = (string) $style; // fix by unixman
		//--
		return (string) $prefix.$preStyle.$value.$postStyle.$suffix; // fix by unixman: test with: realm=php&key=7076 ... (missing space between words: `WarningThis function is currently not documented`);
		//--
	} //END FUNCTION


	private function getNormTag(?ElementInterface $element): string {
		//--
		if($element !== null && !$element->isText()) {
			$tag = (string) \strtolower((string)\trim((string)$element->getTagName()));
			if(((string)$tag == 'i') || ((string)$tag == 'em')) {
				return 'em';
			} elseif(($tag === 'b') || ($tag === 'strong')) {
				return 'strong';
			} //end if else
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION

} //END CLASS

// #end
