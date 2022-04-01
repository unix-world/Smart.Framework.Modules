<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;

class ListBlockConverter implements ConverterInterface
{
	public function convert(ElementInterface $element): string
	{
		return $element->getValue() . "\n";
	}

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array
	{
		return ['ol', 'ul'];
	}
}
