<?php

namespace HTML2Markdown\Converter;

use HTML2Markdown\Configuration;
use HTML2Markdown\ConfigurationAwareInterface;
use HTML2Markdown\ElementInterface;
use HTML2Markdown\SmartFixes;


class HardBreakConverter implements ConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		//--
	//  $return = $this->config->getOption('hard_break') ? "\n" : "  \n";
	//	$return = "\n".'\\'."\n"; // fix by unixman
		$return = "\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK."\n"; // fix by unixman
		//--
		$next = $element->getNext();
		if ($next) {
			$nextValue = $next->getValue();
			if ($nextValue) {
			//	if (\in_array(\substr($nextValue, 0, 2), ['- ', '* ', '+ '], true)) { // this must be also for other elements inside li not just for next li sibling ; also can be ordered list !
					$parent = $element->getParent();
					if ($parent && $parent->getTagName() === 'li') {
						$return .= '\\'; // must be this to keep element in li not double newline !!
					}
			//	}
			}
		}
		//--
		return $return;
		//--
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['br'];
	} //END FUNCTION


} //END CLASS


// #end
