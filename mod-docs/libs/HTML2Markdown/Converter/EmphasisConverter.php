<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;

class EmphasisConverter implements ConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;


	protected function getNormTag(?ElementInterface $element): string {
		if ($element !== null && ! $element->isText()) {
			$tag = $element->getTagName();
			if ($tag === 'i' || $tag === 'em') {
				return 'em';
			}

			if ($tag === 'b' || $tag === 'strong') {
				return 'strong';
			}
		}

		return '';
	} //END FUNCTION


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION

	public function convert(ElementInterface $element): string {
		$tag   = $this->getNormTag($element);
		$value = $element->getValue();

		if (! \trim($value)) {
			return $value;
		}

		if ($tag === 'em') {
			$style = $this->config->getOption('italic_style');
		} else {
			$style = $this->config->getOption('bold_style');
		}

		$prefix = \ltrim($value) !== $value ? ' ' : '';
		$suffix = \rtrim($value) !== $value ? ' ' : '';

		/* If this node is immediately preceded or followed by one of the same type don't emit
		 * the start or end $style, respectively. This prevents <em>foo</em><em>bar</em> from
		 * being converted to *foo**bar* which is incorrect. We want *foobar* instead.
		 */
		$preStyle  = $this->getNormTag($element->getPreviousSibling()) === $tag ? '' : $style;
		$postStyle = $this->getNormTag($element->getNextSibling()) === $tag ? '' : $style;

	//	return $prefix . $preStyle . \trim($value) . $postStyle . $suffix;
		return $prefix . $preStyle . \trim($value) . $postStyle . $suffix . ' '; // fix by unixman ; see realm=php&key=7390 ... (missing space between words: `WarningThis function is currently not documented`);

	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['em', 'i', 'strong', 'b'];
	} //END FUNCTION


} //END CLASS
