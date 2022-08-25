<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;

class ListBlockConverter implements ConverterInterface
{
	public function convert(ElementInterface $element): string
	{
	//	return $element->getValue() . "\n";
		return $element->getValue() . "\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK; // fix: the new markdown parser needs a separate empty line to stop counting as list block
	}

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array
	{
		return ['ol', 'ul'];
	}
}
