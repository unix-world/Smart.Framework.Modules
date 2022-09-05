<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;

class MathConverter implements ConverterInterface {

	// at the moment cannot parse: <math>...</math> but only annotation inside math # realm=javascript&key=347

	public function convert(ElementInterface $element): string {
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
		}
	}


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['math', 'mrow', 'annotation'];
	}


} //END CLASS

// #end
