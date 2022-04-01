<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;


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
