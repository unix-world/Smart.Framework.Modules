<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;

class SvgConverter implements ConverterInterface {


	// TODO ...
	public function convert(ElementInterface $element): string {
		return '';
	}


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['svg'];
	}


} //END CLASS

// #end
