<?php

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\SmartFixes;


class DivConverter implements ConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		if($this->config->getOption('strip_tags', false)) { // this condition is tricky, actually it is: if strip tags is TRUE
			return (string) "\n\n".SmartFixes::stripTags((string)$element->getChildrenAsString())."\n";
		} //end if
		return (string) "\n\n".SmartFixes::decodeHtmlEntity((string)$element->getChildrenAsString())."\n"; // fix by unixman
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['div', 'dt', 'dd'];
	} //END FUNCTION


} //END CLASS


// #end
