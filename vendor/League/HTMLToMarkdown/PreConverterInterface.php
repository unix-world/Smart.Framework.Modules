<?php

namespace League\HTMLToMarkdown;

interface PreConverterInterface
{
	public function preConvert(ElementInterface $element): void;
}
