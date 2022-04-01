<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;

class HorizontalRuleConverter implements ConverterInterface
{
	public function convert(ElementInterface $element): string
	{
		return "- - - -\n\n";
	}

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array
	{
		return ['hr'];
	}
}
