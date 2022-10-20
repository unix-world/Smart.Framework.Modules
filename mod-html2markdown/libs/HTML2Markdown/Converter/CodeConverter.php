<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class CodeConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'code' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
	//	$language = '';
		$language = (string) \trim((string)($element->getAttribute('data-language') ?? ''));
		//-- Checking for language class on the code block
		$classes = (string) $element->getAttribute('class');
		if((string)$classes != '') {
			$classes = (array) \explode(' ', (string)$classes); // Since tags can have more than one class, we need to find the one that starts with 'language-'
			foreach($classes as $kk => $class) {
				$class = (string) \trim((string)$class);
				if((string)$class != '') {
					$class = (string) \strtolower((string)$class);
					if(\strpos((string)$class, 'language-') !== false) { // Found one, save it as the selected language and stop looping over the classes.
						$language = (string) \trim((string)\str_replace('language-', '', (string)$class));
						break;
					} //end if
				} //end if
			} //end foreach
		} //end if
		if(!\preg_match('/[a-z0-9\-]{1,255}/', (string)$language)) { // {{{SYNC-HTML2MKDW-VALIDATE-CODE-SYNTAX}}}
			$language = '';
		} //end if
		//--
		$markdown = '';
		//-- #fix by unixman
		$code = (string) $element->getValue();
		$code = (string) SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-MKDW-CODE-FIX-SPECIALS}}}
		//-- ! QUESTION: escape '```' => '\\`\\`\\`' inside code ?
		if($element->isDescendantOf(['pre'])) {
			$markdown .= "\n".$code."\n";
		} else {
			if($this->shouldBeBlock($element, $code)) {
			//	$spacer = "\n".'\\'."\n";
				$spacer = "\n".' '."\n";
			//	$hspacer = "\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK."\n";
				$hspacer = "\n";
				$markdown .= (string) $hspacer.'```'.$language."\n".\trim((string)$code)."\n".'```'.$spacer.$hspacer; //-- IMPORTANT: because the original internal mechanism of HTML2Markdown will add the markdown to the DOM will loose trailing and pre newlines, more than one ... force using a backslash as this
			} elseif($element->isDescendantOf(['a'])) { // code inside links is not extracted due to limitations in rendering ...
				if(\preg_match('/\[|\]|\(|\)|"/', (string)$code)) {
					$markdown .= '```'.'?URL@ENC:'.\str_replace(["\r\n", "\r", "\n"], ' ', (string)SmartFixes::escapeUrl((string)$code)).':URL@ENC?'.'```'; // inline code need to be escaped, especially if occurs inside links ...
				} else {
					$markdown .= '```'.\str_replace(["\r\n", "\r", "\n"], ' ', (string)SmartFixes::escapeElementContent((string)$code, 'code')).'```'; // inline code need to be escaped, especially if occurs inside links ...
				} //end if else
			} else {
			//	$markdown .= '```'.\str_replace(["\r\n", "\r", "\n"], ' ', (string)$code).'```';
				$markdown .= ' '.'```'.SmartFixes::normalizeWhiteSpaces((string)$code).'```'.' '; // {{{SYNC-HTML2MKDW-ARROUND-SPACES}}}
			} //end if else
		} //end if else
		//-- #fix
		return (string) $markdown;
		//--
	} //END FUNCTION


	private function shouldBeBlock(ElementInterface $element, string $code): bool {
		//--
		$parent = $element->getParent();
		//--
		if($parent !== null && ((string)$parent->getTagName() == 'pre')) {
			return true;
		} //end if
		//--
	//	return (bool) \preg_match('/[^\s]` `/', $code) === 1;
	//	return false;
		return (bool) (\strpos((string)$code, "\n") !== false);
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
