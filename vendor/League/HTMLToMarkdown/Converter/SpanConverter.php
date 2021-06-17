<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;


class SpanConverter implements ConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		if ($this->config->getOption('strip_tags', false)) { // this condition is tricky, actually it is: if strip tags is TRUE
		//	return $element->getValue() . "\n\n";
			return (string) ' '.\League\HTMLToMarkdown\SmartFixes::stripTags((string)$element->getChildrenAsString()).' ';
		}
		return \html_entity_decode($element->getChildrenAsString());
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['span'];
	} //END FUNCTION


} //END CLASS


// #end
