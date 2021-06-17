<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;


class CodeConverter implements ConverterInterface {

	public function convert(ElementInterface $element): string {
		$language = '';
		// Checking for language class on the code block
		$classes = $element->getAttribute('class');
		if ($classes) {
			// Since tags can have more than one class, we need to find the one that starts with 'language-'
			$classes = \explode(' ', $classes);
			foreach ($classes as $class) {
				if (\strpos($class, 'language-') !== false) {
					// Found one, save it as the selected language and stop looping over the classes.
					$language = \str_replace('language-', '', $class);
					break;
				}
			}
		}
		$markdown = '';
	//	$code     = \html_entity_decode($element->getChildrenAsString());
		$code = (string) \Smart::striptags((string)$element->getChildrenAsString());
		// In order to remove the code tags we need to search for them and, in the case of the opening tag
		// use a regular expression to find the tag and the other attributes it might have
		$code = \preg_replace('/<code\b[^>]*>/', '', $code);
		\assert($code !== null);
		$code = \str_replace('</code>', '', $code);
		//-- #fix by unixman
		$code = (string) \League\HTMLToMarkdown\SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-CODE-FIX-SPECIALS-MARKDOWN}}}
		//-- #fix by unixman
		// Checking if it's a code block or span
	//	if ($this->shouldBeBlock($element, $code)) {
	//		// Code block detected, newlines will be added in parent
	//		$markdown .= '```' . $language . "\n" . $code . "\n" . '```';
	//	} else {
	//		// One line of code, wrapping it on one backtick, removing new lines
	//		$markdown .= '`' . \preg_replace('/\r\n|\r|\n/', '', $code) . '`';
	//	}
		if($element->isDescendantOf(['pre'])) {
			$markdown .= "\n" . $code . "\n";
		} else {
			if ($this->shouldBeBlock($element, $code)) {
				$spacer = "\n".'\\'."\n";
			//	$spacer = "\n".' '."\n";
				$markdown .= $spacer.'```' . $language . "\n" . \trim((string)$code) . "\n" . '```'.$spacer; //-- IMPORTANT: because the internal mechanism of League HTML will add the markdown to the DOM will loose trailing and pre newlines, more than one ... force using a backslash as this
			} else {
			//	$markdown .= '```' . \preg_replace('/\r\n|\r|\n/', ' ', $code) . '```'; // using this with inline code breaks tables if | ```code1``` | ```code2``` |
				$markdown .= '`' . \preg_replace('/\r\n|\r|\n|\t/', ' ', $code) . '`';
			}
		}
		//-- #fix
		return $markdown;
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['code'];
	} //END FUNCTION


	private function shouldBeBlock(ElementInterface $element, string $code): bool {
		$parent = $element->getParent();
		if ($parent !== null && $parent->getTagName() === 'pre') {
			return true;
		} //end if
		return \preg_match('/[^\s]` `/', $code) === 1;
	} //END FUNCTION


} //END CLASS


// #end
