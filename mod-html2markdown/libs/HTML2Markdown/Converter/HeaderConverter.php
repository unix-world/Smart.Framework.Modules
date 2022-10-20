<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class HeaderConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		$level = (int) \substr($element->getTagName(), 1, 1);
		if((int)\strlen($element->getValue()) <= 0) {
			return "\n";
		} //end if
		//-- fix headers inside UL/OL LI
		$ext_defs = [];
	//	if($element->isDescendantOf('li')) {
	//		//return (string) '**' . $element->getValue() . '**';
	//		$ext_defs[] = '@style=display:inline-block'; // '{H:@style=display:inline-block}';
	//	} //end if
		$the_id = (string) SmartFixes::createHtmid((string)$element->getAttribute('id'));
		if((string)$the_id != '') {
			$ext_defs[] = '@id='.$the_id;
		} //end if
		$the_classes = (string) SmartFixes::createHtmSafeClassName((string)$element->getAttribute('class'));
		if((string)$the_classes != '') {
			$ext_defs[] = (string) '@class='.$the_classes;
		} //end if
		if((int)\count($ext_defs) > 0) {
			$ext_defs = (string) '{H: '.\implode(' ', (array)$ext_defs).'}';
		} else {
			$ext_defs = '';
		} //end if else
		//-- #fix
		return (string) $this->createAtxHeader((int)$level, (string)$element->getValue(), (string)$ext_defs);
		//--
	} //END FUNCTION


	private function createAtxHeader(int $level, string $content, ?string $ext_defs=null): string {
		//--
		$ext_defs = (string) \trim((string)$ext_defs);
		$prefix = (string) \str_repeat('#', $level).' ';
		//--
	//	return $prefix.$content."\n\n";
	//	return "\n".' '."\n".$prefix.$content.($ext_defs ? ' '.$ext_defs : '')."\n \n";
	//	return "\n".'\\'."\n".$prefix.$content.($ext_defs ? ' '.$ext_defs : '')."\n".'\\'."\n";
		return "\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK."\n".$prefix.$content.($ext_defs ? ' '.$ext_defs : '')."\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK."\n";
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
