<?php

//declare(strict_types=1);

namespace HTML2Markdown;

use HTML2Markdown\ElementInterface;

interface ConverterInterface {

	public function convert(ElementInterface $element): string;

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array;

} //END INTERFACE

// #end
