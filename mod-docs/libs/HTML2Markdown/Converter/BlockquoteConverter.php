<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;

class BlockquoteConverter implements ConverterInterface
{
	public function convert(ElementInterface $element): string
	{
		// Contents should have already been converted to Markdown by this point,
		// so we just need to add between blockquote.

		// fixed by unixman
		$v1 = false;

		$quoteContent = (string) \trim((string)$element->getValue());
		$quoteContent = (string) \str_replace(["\r\n", "\r"], "\n", (string)$quoteContent);
		if((string)$quoteContent == '') {
			return '';
		} //end if

		$lines = (array) \explode("\n", (string)$quoteContent);
		$totalLines = \count($lines);

		$markdown = '';
		foreach($lines as $i => $line) {
			if($v1) {
				$markdown .= '> '.$line."\n";
			} else {
				$markdown .= $line."\n";
			}
			if((int)($i + 1) == (int)$totalLines) {
				$markdown .= "\n";
			}
		}

		if($v1) {
			return "\n".\trim((string)$markdown)."\n";
		} else {
			return "\n".'<<<'."\n".\trim((string)$markdown)."\n".'<<<'."\n";
		}
	}

	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array
	{
		return ['blockquote'];
	}
}
