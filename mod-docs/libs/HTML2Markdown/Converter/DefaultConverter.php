<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;

class DefaultConverter implements ConverterInterface, ConfigurationAwareInterface
{
	public const DEFAULT_CONVERTER = '_default';

	/** @var Configuration */
	protected $config;

	public function setConfig(Configuration $config): void
	{
		$this->config = $config;
	}

	public function convert(ElementInterface $element): string
	{
		//-- #fix by unixman
		// If strip_tags is false (the default), preserve tags that don't have Markdown equivalents,
		// such as <span> nodes on their own. C14N() canonicalizes the node to a string.
		// See: http://www.php.net/manual/en/domnode.c14n.php
		if ($this->config->getOption('strip_tags', false)) { // this condition is tricky, actually it is: if strip tags is TRUE
		//	return $element->getValue();
			return (string) SmartFixes::stripTags((string)$element->getChildrenAsString());
		}
	//	$markdown = \html_entity_decode($element->getChildrenAsString());
		$markdown = (string) SmartFixes::decodeHtmlEntity((string)$element->getChildrenAsString()); // fix by unixman
		//-- #fix

		// Tables are only handled here if TableConverter is not used
		if ($element->getTagName() === 'table') {
			$markdown .= "\n\n";
		}

		return (string) $markdown;
	}

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array
	{
		return [self::DEFAULT_CONVERTER];
	}
}
