<?php

//declare(strict_types=1);

namespace HTML2Markdown\Converter;

use HTML2Markdown\SmartFixes;
use HTML2Markdown\AbstractConverterConfig;
use HTML2Markdown\ConverterInterface;
use HTML2Markdown\PreConverterInterface;
use HTML2Markdown\ElementInterface;


final class TableConverter extends AbstractConverterConfig implements ConverterInterface, PreConverterInterface {

	// OK

	private const TABLE_PIPE_ESCAPE = '\|';
	private const TBL_ALIGNMENTS = [
		'left' 		=> ':--',
		'right' 	=> '--:',
		'center' 	=> ':-:',
	];

	private $columnAlignments = [];
	private $caption = null;
	private $rownum = 0;
	private $cellnum = 0;
	private $maxcells = 0;
//	private $isInList = false;


	public function getSupportedTags() : array {
		//--
		return [ 'table', 'tr', 'th', 'td', 'thead', 'tbody', 'tfoot', 'colgroup', 'col', 'caption' ];
		//--
	} //END FUNCTION


	public function preConvert(ElementInterface $element): void {
		//--
		$tag = $element->getTagName();
		if($tag === 'table') {
			$this->columnAlignments = [];
			$this->caption = null;
			$this->rownum = 0;
			$this->cellnum = 0;
			$this->maxcells = 0;
		} //end if
		// Only table cells and caption are allowed to contain content.
		// Remove all text between other table elements.
		//-- # fix by unixman
		if(($tag === 'table') || ($tag === 'thead') || ($tag === 'tbody') || ($tag === 'tfoot')) {
			foreach($element->getChildren() as $child) {
				if($child->getTagName() === 'tr') {
					$this->rownum++;
				} //end if
			} //end foreach
		} //end if
		if($tag === 'tr') {
			$cells = 0;
			foreach ($element->getChildren() as $child) {
				if($child->getTagName() === 'th' || $child->getTagName() === 'td') {
					$cells++;
				} //end if
			} //end foreach
			$this->maxcells = max($this->maxcells, $cells);
		} //end if
		//-- #end fix
		if($tag === 'th' || $tag === 'td' || $tag === 'caption') {
			return;
		} //end if
		foreach($element->getChildren() as $child) {
			if($child->isText()) {
				$child->setFinalMarkdown('');
			} //end if
		} //end foreach
		//--
	} //END FUNCTION


	public function convert(ElementInterface $element) : string { // strip tags behaviour only
		//--
		$value = (string) $element->getValue();
		//--
		switch((string)$element->getTagName()) {
			case 'table':
				//--
				$this->rownum = 0;
				$this->cellnum = 0;
				$this->maxcells = 0;
				$this->columnAlignments = [];
				//--
				/* test by unixman
				if($element->isDescendantOf(['table'])) {
					return (string) SmartFixes::stripTags((string)$value);
				} //end if
				*/
				//--
				if($this->caption) {
					$side = 'top'; // or 'bottom' ; to show <caption> content before or after table, null to suppress
					if((string)$side == 'top') {
						$value = (string) $this->caption."\n".$value;
					} elseif((string)$side == 'bottom') {
						$value .= (string) $this->caption;
					} //end if else
					$this->caption = null;
				} //end if
				//--
				return (string) $value."\n";
				//--
			case 'caption': // TODO ...
				//--
				$this->caption = (string) \trim((string)$value);
				//--
				return '';
				//--
			case 'tr':
				$value .= "|\n";
			//	if($this->columnAlignments !== null) { // below will be set to null, in this case !
				if(\is_array($this->columnAlignments)) { // below will be set to null, in this case !
					//-- #fix by unixman to fill the head mark cells with max cells of the table
					if(\count($this->columnAlignments) < $this->maxcells) {
						for($i=\count($this->columnAlignments); $i<$this->maxcells; $i++) {
							$this->columnAlignments[] = '---';
						} //end for
					} //end if
					//-- #fix
					$value .= '|'.\implode('|', (array)$this->columnAlignments)."|\n";
					$this->columnAlignments = null;
					//--
				} //end if
				//--
				return (string) $value;
				//--
			case 'th':
			case 'td':
				//-- #fix by unixman ; TODO: find a real fix for rowspan ...
				$rowspan = (string) $element->getAttribute('rowspan');
				if((string)$rowspan != '') {
					if($element->getTagName() != 'th') { // on TH should not ... on TD, is really complicated, stop here and eliminate that cell
						return '';
					} //end if
				} //end if
				$colspan = (int) ($element->getAttribute('colspan') ?? 0);
				if((int)$colspan > 0) {
					$colspan = (string) ' {T: @colspan='.(int)$colspan.'} ';
				} else {
					$colspan = '';
				} //end if else
				//-- #fix
				$this->cellnum++;
			//	if($this->columnAlignments !== null) {
				if(\is_array($this->columnAlignments)) {
					$align = (string) $element->getAttribute('align');
					$this->columnAlignments[] = (string) (self::TBL_ALIGNMENTS[(string)$align] ?? '---');
				} //end if
				//-- #fix by unixman
				$tblsep = '';
				$tblstyles = '';
				if((int)$this->cellnum == 1) {
				//	$tblsep = "\n:::\n:::\n";
				//	$tblsep = "\n".'\\'."\n"; // this was prev, ok
				//	$tblsep = "\n".' '."\n";
					$tblsep = "\n".SmartFixes::SPECIAL_CHAR_NEWLINE_MARK."\n";
					if((int)$this->rownum < 2) {
						$tblstyles = '{!DEF!=AUTO-WIDTH;ALIGN-HEAD-LEFT;ALIGN-LEFT;NO-TABLE-HEAD;.bordered;.stripped;.doc-table;.max-cells-'.(int)$this->maxcells.';.max-rows-'.(int)$this->rownum.';}';
					} else {
						$tblstyles = '{!DEF!=AUTO-WIDTH;ALIGN-HEAD-CENTER;ALIGN-AUTO;.bordered;.stripped;.doc-table;.max-cells-'.(int)$this->maxcells.';.max-rows-'.(int)$this->rownum.';}';
					} //end if
				} //end if
				//-- #fix
				$uuidHash = (string) \sha1((string)$value);
				$value = (string) \str_replace("\n", ' ', (string)$value);
				$arr_inline_code = (array) $this->getDataInlineCodes($value);
				foreach($arr_inline_code as $key => $val) { // extract code in table cells # fix: | inside inline code must NOT be escaped below
					$value = (string) strtr($value, [ (string)$val => '^^^^^^^'.$uuidHash.':::::::'.$uuidHash.'{'.rawurlencode((string)$key).'}'.'$$$$$$$' ]);
				} //end foreach
			//	$value = (string) \str_replace('|', (string)self::TABLE_PIPE_ESCAPE, (string)$value);
				$value = (string) \strtr((string)$value, [ '|' => (string)self::TABLE_PIPE_ESCAPE ]);
				foreach($arr_inline_code as $key => $val) { // restore code in table cells
					$value = (string) strtr($value, [ '^^^^^^^'.$uuidHash.':::::::'.$uuidHash.'{'.rawurlencode((string)$key).'}'.'$$$$$$$' => (string)$val ]);
				} //end foreach
				$arr_inline_code = null;
				$uuidHash = null;
				//-- #fix by unixman
			//	$value = (string) \preg_replace('/^( )*(\#){1,6}( )(.*)/m', '**${4}**', (string)$value); // fix by unixman: transform H1..H6 inside a table cell with bold, as inline headings are not supported in markdown ... yet ... # https://github.com/mysticmind/reversemarkdown-net/issues/44
				//-- #fix
				return (string) $tblsep.'|'.$tblstyles.' '.\trim((string)$value).$colspan.' '; // includes fixes by unixman
				//--
			case 'thead':
			case 'tbody':
			case 'tfoot':
			case 'colgroup':
			case 'col':
				//--
				return (string) $value;
				//--
			default:
				//--
				return '';
				//--
		} //end switch
		//--
		return '';
		//--
	} //END FUNCTION


	private function getDataInlineCodes(?string $text) : array { // Inline Code
		//--
		$matches = array();
		$pcre = \preg_match_all((string)SmartFixes::PATTERN_INLINE_CODE, (string)$text, $matches, \PREG_PATTERN_ORDER, 0);
		if($pcre === false) {
			return [];
		} //end if
		//--
		return (array) ((isset($matches[0]) && is_array($matches[0])) ? $matches[0] : []);
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
