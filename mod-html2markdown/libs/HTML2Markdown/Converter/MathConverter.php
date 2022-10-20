<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class MathConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'math', 'mrow', 'annotation' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		switch((string)$element->getTagName()) {
			case 'annotation':
				$code = (string) $element->getValue();
				$code = (string) SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-MKDW-CODE-FIX-SPECIALS}}}
				$spacer = "\n".'\\'."\n";
				$hspacer = "\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK."\n";
				return $hspacer.'::: .math-latex'."\n".\trim((string)$code)."\n".':::'.$spacer.$hspacer;
				break;
			case 'math':
				return (string) $element->getValue();
				break;
			default:
				return '';
		} //end switch
		//--
		return '';
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
