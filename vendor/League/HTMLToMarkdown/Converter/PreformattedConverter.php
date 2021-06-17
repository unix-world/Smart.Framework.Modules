<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;


class PreformattedConverter implements ConverterInterface {

	public function convert(ElementInterface $element): string {

		//-- fix by unixman
		$language = (string) \trim((string)($element->getAttribute('data-language') ?? ''));
		if(\strpos((string)$language, 'language-') === 0) {
			$language = (string) \substr((string)$language, 9);
		} //end if
		\assert(\is_string($element->getValue()));

		//-- #fix by unixman
		$code = (string) $element->getValue();
		$code = (string) \League\HTMLToMarkdown\SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-CODE-FIX-SPECIALS-MARKDOWN}}}
		$spacer = "\n".'\\'."\n";
	//	$spacer = "\n".' '."\n";
		return $spacer.'```'.($language ? $language : '')."\n" . (string)\trim((string)$code) . "\n".'```'.$spacer; // if only one line and not double the code area will not pre-format (will loose indentation ...) ; and double spaces will be lost because what is described below !
		//-- IMPORTANT: because the internal mechanism of League HTML will add the markdown to the DOM will loose trailing and pre newlines, more than one ... force using a backslash as above

		/*
		$preContent = \html_entity_decode($element->getChildrenAsString());
		$preContent = \str_replace(['<pre>', '</pre>'], '', $preContent);
		 //Checking for the code tag.
		 //Usually pre tags are used along with code tags. This conditional will check for already converted code tags,
		 //which use backticks, and if those backticks are at the beginning and at the end of the string it means
		 //there's no more information to convert.
		$firstBacktick = \strpos(\trim($preContent), '`');
		$lastBacktick  = \strrpos(\trim($preContent), '`');
		if($firstBacktick === 0 && $lastBacktick === \strlen(\trim($preContent)) - 1) {
			return $preContent . "\n\n";
		}
		// If the execution reaches this point it means it's just a pre tag, with no code tag nested
		// Empty lines are a special case
		if($preContent === '') {
			return "```\n```\n\n";
		}
		// Normalizing new lines
		$preContent = \preg_replace('/\r\n|\r|\n/', "\n", $preContent);
		\assert(\is_string($preContent));
		// Ensure there's a newline at the end
		if(\strrpos($preContent, "\n") !== \strlen($preContent) - \strlen("\n")) {
			$preContent .= "\n";
		}
		// Use three backticks
		return "```\n" . $preContent . "```\n\n";
		*/
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		//--
		return ['pre'];
		//--
	} //END FUNCTION


} //END CLASS


// #end
