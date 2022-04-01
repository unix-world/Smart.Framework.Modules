<?php

namespace HTML2Markdown;

interface PreConverterInterface
{
	public function preConvert(ElementInterface $element): void;
}
