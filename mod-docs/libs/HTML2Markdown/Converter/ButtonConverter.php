<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;

class ButtonConverter implements ConverterInterface {


	public function convert(ElementInterface $element): string {
		return '';
	}


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['button'];
	}


} //END CLASS

// #end
