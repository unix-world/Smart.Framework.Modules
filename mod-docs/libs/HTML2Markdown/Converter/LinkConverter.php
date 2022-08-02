<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;


class LinkConverter implements ConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		$href  = (string) $element->getAttribute('href');
		$title = (string) $element->getAttribute('title');
		$text  = (string) \trim((string)$element->getValue(), "\0\x0B"); // \trim((string)$element->getValue(), "\t\n\r\0\x0B"); // in links if there is a <br> will remain as \, so trim only specials

		if((string)$title != '') {
		//	$markdown = '[' . \str_replace(['[',']'], ['\\[','\\]'], (string)$text) . '](' . \str_replace(['(', ')'], ['\\(', '\\)'], (string)$href) . ' "' . \str_replace(['(', ')', '"'], ['\\(', '\\)', "'"], (string)$title) . '")';
			$markdown = '[' . \strtr((string)$text, (array)SmartFixes::FIX_ESCAPES_ENTITIES_RBRACKS) . '](' . \strtr((string)$href, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS) . ' "' . \strtr((string)$title, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS) . '")';
		} elseif ($href === $text && $this->isValidAutolink($href)) {
		//	$markdown = '<' . \str_replace(['<', '>'], ['\\<', '\\>'], (string)$href) . '>';
			$markdown = '<' . \strtr((string)$href, (array)SmartFixes::FIX_ESCAPES_ENTITIES_VBRACKS) . '>';
		} elseif ($href === 'mailto:' . $text && $this->isValidEmail($text)) {
		//	$markdown = '<' . \str_replace(['<', '>'], ['\\<', '\\>'], (string)$text) . '>';
			$markdown = '<' . \strtr((string)$text, (array)SmartFixes::FIX_ESCAPES_ENTITIES_VBRACKS) . '>';
		} else {
		//	if(\stristr($href, ' ')) {
			if(\strpos($href, ' ') !== false) {
			//	$href = '<' . \str_replace(['<', '>'], ['\\<', '\\>'], (string)$href) . '>';
				$href = '<' . \strtr((string)$href, (array)SmartFixes::FIX_ESCAPES_ENTITIES_VBRACKS) . '>';
			}
		//	$markdown = '[' . \str_replace(['[',']'], ['\\[','\\]'], (string)$text) . '](' . \str_replace(['(', ')'], ['\\(', '\\)'], (string)$href) . ')';
			$markdown = '[' . \strtr((string)$text, (array)SmartFixes::FIX_ESCAPES_ENTITIES_RBRACKS) . '](' . \strtr((string)$href, (array)SmartFixes::FIX_ESCAPES_ENTITIES_BRACKS) . ')';
		}

		if (! $href) {
			if ($this->shouldStrip()) {
				$markdown = (string) $text;
			} else {
			//	$markdown = \html_entity_decode($element->getChildrenAsString());
				$markdown = (string) SmartFixes::decodeHtmlEntity((string)$element->getChildrenAsString()); // fix by unixman
			}
		}

		return (string) $markdown;
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['a'];
	} //END FUNCTION


	private function isValidAutolink(string $href): bool {
		$useAutolinks = $this->config->getOption('use_autolinks');
		return $useAutolinks && (\preg_match('/^[A-Za-z][A-Za-z0-9.+-]{1,31}:[^<>\x00-\x20]*/i', $href) === 1);
	} //END FUNCTION


	private function isValidEmail(string $email): bool {
		// Email validation is messy business, but this should cover most cases
		return \filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	} //END FUNCTION


	private function shouldStrip(): bool {
		return $this->config->getOption('strip_placeholder_links') ?? false;
	} //END FUNCTION


} //END CLASS


// #end
