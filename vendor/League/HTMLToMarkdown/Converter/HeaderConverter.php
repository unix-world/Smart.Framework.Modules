<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;


class HeaderConverter implements ConverterInterface, ConfigurationAwareInterface {

	public const STYLE_ATX    = 'atx';
//	public const STYLE_SETEXT = 'setext';

	/** @var Configuration */
	protected $config;


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		$level = (int) \substr($element->getTagName(), 1, 1);
	//	$style = $this->config->getOption('header_style', self::STYLE_SETEXT);
		if (\strlen($element->getValue()) === 0) {
			return "\n";
		}
		//-- #fix by unixman
	//	if (($level === 1 || $level === 2) && ! $element->isDescendantOf('blockquote') && $style === self::STYLE_SETEXT) {
	//		return $this->createSetextHeader($level, $element->getValue());
	//	}
		//-- fix headers inside UL/OL LI
		$css_style = '';
		if($element->isDescendantOf('li')) {
			//return (string) '**' . $element->getValue() . '**';
			$css_style = '{H:@style=display:inline-block}';
		}
		//-- #fix
		return $this->createAtxHeader($level, $element->getValue(), $css_style);
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
	} //END FUNCTION

/* feature disabled, it renders BAD in some situations ...
	private function createSetextHeader(int $level, string $content): string {
		$length    = \function_exists('mb_strlen') ? \mb_strlen($content, 'utf-8') : \strlen($content);
		$underline = $level === 1 ? '=' : '-';
		return $content . "\n" . \str_repeat($underline, $length) . "\n\n";
	} //END FUNCTION
*/

	private function createAtxHeader(int $level, string $content, ?string $css_style=null): string {
		$css_style = (string) \trim((string)$css_style);
		$prefix = \str_repeat('#', $level) . ' ';
	//	return $prefix . $content . "\n\n";
	//	return "\n".'\\'."\n".$prefix . $content . "\n \n";
		return "\n".' '."\n".$prefix . $content . ($css_style ? ' '.$css_style : '') . "\n \n";
	} //END FUNCTION


} //END CLASS


// #end
