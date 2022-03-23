<?php

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\SmartFixes;


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
