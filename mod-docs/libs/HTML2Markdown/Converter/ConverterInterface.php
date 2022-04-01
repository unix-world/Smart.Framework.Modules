<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;

interface ConverterInterface
{
	public function convert(ElementInterface $element): string;

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array;
}
