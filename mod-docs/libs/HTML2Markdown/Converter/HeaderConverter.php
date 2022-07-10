<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;


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
		$ext_defs = [];
		if($element->isDescendantOf('li')) {
			//return (string) '**' . $element->getValue() . '**';
			$ext_defs[] = '@style=display:inline-block'; // '{H:@style=display:inline-block}';
		} //end if
		$the_id = (string) SmartFixes::createHtmid((string)$element->getAttribute('id'));
		if($the_id) {
			$ext_defs[] = '@id='.$the_id;
		} //end if
		if(\count($ext_defs) > 0) {
			$ext_defs = (string) '{H:'.\implode(' ', (array)$ext_defs).'}';
		} else {
			$ext_defs = '';
		} //end if else
		//-- #fix
		return $this->createAtxHeader($level, $element->getValue(), (string)$ext_defs);
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

	private function createAtxHeader(int $level, string $content, ?string $ext_defs=null): string {
		$ext_defs = (string) \trim((string)$ext_defs);
		$prefix = \str_repeat('#', $level) . ' ';
	//	return $prefix . $content . "\n\n";
	//	return "\n".'\\'."\n".$prefix . $content . "\n \n";
		return "\n".' '."\n".$prefix.$content.($ext_defs ? ' '.$ext_defs : '')."\n \n";
	} //END FUNCTION


} //END CLASS


// #end
