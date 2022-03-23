<?php

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\SmartFixes;


class CodeConverter implements ConverterInterface {


	public function getSupportedTags(): array {
		//--
		return ['code'];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		//--
		$language = '';
		//-- Checking for language class on the code block
		$classes = (string) $element->getAttribute('class');
		if((string)$classes != '') {
			$classes = (array) \explode(' ', (string)$classes); // Since tags can have more than one class, we need to find the one that starts with 'language-'
			foreach($classes as $kk => $class) {
				$class = (string) \trim((string)$class);
				if((string)$class != '') {
					$class = (string) \strtolower((string)$class);
					if(\strpos((string)$class, 'language-') !== false) { // Found one, save it as the selected language and stop looping over the classes.
						$language = (string) \str_replace('language-', '', (string)$class);
						break;
					} //end if
				} //end if
			} //end foreach
		} //end if
		$markdown = '';
		//-- #fix by unixman
		$code = (string) $element->getValue();
		$code = (string) SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-MKDW-CODE-FIX-SPECIALS}}}
		//--
		if($element->isDescendantOf(['pre'])) {
			$markdown .= "\n".$code."\n";
		} else {
			if($this->shouldBeBlock($element, $code)) {
				$spacer = "\n".'\\'."\n";
			//	$spacer = "\n".' '."\n";
				$markdown .= (string) $spacer.'```'.$language."\n".\trim((string)$code)."\n".'```'.$spacer; //-- IMPORTANT: because the internal mechanism of League HTML will add the markdown to the DOM will loose trailing and pre newlines, more than one ... force using a backslash as this
			} else {
			//	$markdown .= '```'.\preg_replace('/\r\n|\r|\n/', ' ', (string)$code).'```';
				$markdown .= '```'.\str_replace(["\r\n", "\r", "\n"], ' ', (string)$code).'```';
			} //end if else
		} //end if else
		//-- #fix
		return (string) $markdown;
		//--
	} //END FUNCTION


	private function shouldBeBlock(ElementInterface $element, string $code): bool {
		$parent = $element->getParent();
		if ($parent !== null && $parent->getTagName() === 'pre') {
			return true;
		} //end if
	//	return \preg_match('/[^\s]` `/', $code) === 1;
		return false;
	} //END FUNCTION


} //END CLASS


// #end
