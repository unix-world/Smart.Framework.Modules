<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class HorizontalRuleConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'hr' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
	//	return '- - - -'."\n"."\n";
		return "\n".'- - - -'."\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK;
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
