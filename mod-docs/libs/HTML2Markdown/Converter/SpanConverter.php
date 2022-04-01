<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;


class SpanConverter implements ConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;

	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function getSupportedTags(): array {
		return ['span', 'p'];
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		if ($this->config->getOption('strip_tags', false)) { // this condition is tricky, actually it is: if strip tags is TRUE
			return (string) ' '.SmartFixes::stripTags((string)$element->getChildrenAsString()).' ';
		} //end if
		return (string) SmartFixes::decodeHtmlEntity((string)$element->getChildrenAsString()); // fix by unixman
	} //END FUNCTION


} //END CLASS


// #end
