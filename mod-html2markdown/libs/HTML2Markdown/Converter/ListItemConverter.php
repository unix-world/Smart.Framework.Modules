<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\ElementInterface;


final class ListItemConverter extends AbstractConverterConfig implements ConverterInterface {

	// OK

	private $listItemStyle;


	public function getSupportedTags() : array {
		//--
		return [ 'li' ];
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		$parent = $element->getParent();
		//--
		$listType = (string) !!$parent ? $parent->getTagName() : 'ul'; // If parent is an ol, use numbers, otherwise, use dashes
		//--
		$level = $element->getListItemLevel(); // Add spaces to start for nested list items
		//--
	//	$value = (string) \trim((string)\implode("\n".'    ', (array)\explode("\n", (string)\trim((string)$element->getValue()))));
		$value = (string) \trim((string)\implode("\n",        (array)\explode("\n", (string)\trim((string)$element->getValue()))));
		//--
		$prefix = "\n";
		$suffix = "\n";
		if(((int)$level > 0) && ((int)$element->getSiblingPosition() == 1)) {
		//	$prefix = "\n"; // If list item is the first in a nested list, add a newline before it
		} //end if
		//--
		$markdown = '';
		//--
		if((string)$listType == 'ol') {
			/*
			if(!!$parent && ($start = \intval($parent->getAttribute('start')))) {
				$number = $start + $element->getSiblingPosition() - 1;
			} else {
				$number = $element->getSiblingPosition();
			} //end if else
			*/
			$number = '1'; // {{{SYNC-HTML2MKDW-FIX-OL}}} ; use just 1. not 1. 2. 3. because where this sync target gets too complicated ...
			$markdown = (string) $number.'. '.$value; // . "\n";
	//	} elseif((string)$listType == 'ul') {
		} else { // ul
			$listItemStyle          = $this->getConfig('list_item_style',           SmartFixes::MKDW_TAG_LI);
			$listItemStyleAlternate = $this->getConfig('list_item_style_alternate', SmartFixes::MKDW_TAG_LI_ALT);
			if(!isset($this->listItemStyle)) {
				$this->listItemStyle = $listItemStyleAlternate ?: $listItemStyle;
			} //end if
			if($listItemStyleAlternate && ($level === 0) && ((int)$element->getSiblingPosition() == 1)) {
				$this->listItemStyle = ($this->listItemStyle === $listItemStyle) ? $listItemStyleAlternate : $listItemStyle;
			} //end if
			$markdown = (string) $this->listItemStyle.' '.$value; // . "\n";
		} //end if else
		//--
		return (string) $prefix.$markdown.$suffix;
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
