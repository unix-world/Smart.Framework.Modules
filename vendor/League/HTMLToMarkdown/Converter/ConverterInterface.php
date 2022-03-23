<?php

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

interface ConverterInterface
{
	public function convert(ElementInterface $element): string;

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array;
}
