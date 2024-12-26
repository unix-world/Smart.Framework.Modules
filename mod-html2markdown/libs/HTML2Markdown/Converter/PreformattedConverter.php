<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class PreformattedConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	public function getSupportedTags() : array {
		//--
		return [ 'pre' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//-- fix by unixman
		$mkdwtag = '~~~';
		$language = (string) \trim((string)($element->getAttribute('data-language') ?? ''));
		if(\strpos((string)$language, 'language-') === 0) {
			$language = (string) \trim((string)\substr((string)$language, 9));
		} else {
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
						} elseif(\strpos((string)$class, 'lang-') !== false) {
							$language = (string) \trim((string)\str_replace('lang-', '', (string)$class));
							break;
						} //end if
					} //end if
				} //end foreach
			} //end if
		} //end if
		//--
		if(!\preg_match('/[a-z0-9\-]{1,255}/', (string)$language)) { // {{{SYNC-HTML2MKDW-VALIDATE-CODE-SYNTAX}}}
			$language = '';
		} //end if
		$is_code = false;
		if((string)$language != '') {
			$mkdwtag = '```';
			$is_code = true;
		} //end if
		//-- // {{{SYNC-HTML2MKDW-CONVERT-PRE}}}
		$code = (string) $element->getValue();
		if($is_code === true) {
			$code = (string) SmartFixes::escapeCodeElementContent((string)$code); // {{{SYNC-MKDW-CODE-FIX-SPECIALS}}}
		} //end if
		$spacer = "\n".' '."\n";
	//	$spacer = "\n";
		//-- use pre wrap as ~~~~ instead of ~~~
		return (string) $spacer.$mkdwtag.($language ? $language : '')."\n" . (string)\trim((string)$code) . "\n".$mkdwtag.$spacer."\n"; // if only one line and not double the code area will not pre-format (will loose indentation ...) ; and double spaces will be lost because what is described below !
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
