<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\PreConverterInterface;


class TableConverter implements ConverterInterface, PreConverterInterface, ConfigurationAwareInterface {

	/** @var Configuration */
	protected $config;


	/** @var array<string, string> */
	private static $alignments = [
		'left' => ':--',
		'right' => '--:',
		'center' => ':-:',
	];

	/** @var array<int, string>|null */
	private $columnAlignments = [];

	/** @var string|null */
	private $caption = null;

	private $rownum = 0;
	private $cellnum = 0;
	private $maxcells = 0;


	public function setConfig(Configuration $config): void {
		$this->config = $config;
	} //END FUNCTION


	public function preConvert(ElementInterface $element): void {
		$tag = $element->getTagName();
		// Only table cells and caption are allowed to contain content.
		// Remove all text between other table elements.
		//-- # fix by unixman
		if(($tag === 'table') || ($tag === 'thead') || ($tag === 'tbody') || ($tag === 'tfoot')) {
			foreach($element->getChildren() as $child) {
				if($child->getTagName() === 'tr') {
					$this->rownum++;
				}
			}
		}
		if($tag === 'tr') {
			$cells = 0;
			foreach ($element->getChildren() as $child) {
				if($child->getTagName() === 'th' || $child->getTagName() === 'td') {
					$cells++;
				}
			}
			$this->maxcells = max($this->maxcells, $cells);
		}
		//-- #end fix
		if ($tag === 'th' || $tag === 'td' || $tag === 'caption') {
			return;
		}
		foreach ($element->getChildren() as $child) {
			if ($child->isText()) {
				$child->setFinalMarkdown('');
			}
		}
	} //END FUNCTION


	public function convert(ElementInterface $element): string {
		$value = $element->getValue();
		switch ($element->getTagName()) {
			case 'table':
				$this->rownum = 0;
				$this->cellnum = 0;
				$this->maxcells = 0;
				$this->columnAlignments = [];
				/* test by unixman
				if($element->isDescendantOf(['table'])) {
					return (string) \Smart::striptags((string)$value);
				}
				*/
				if ($this->caption) {
					$side = $this->config->getOption('table_caption_side');
					if ($side === 'top') {
						$value = $this->caption . "\n" . $value;
					} elseif ($side === 'bottom') {
						$value .= $this->caption;
					}
					$this->caption = null;
				}
				return $value . "\n";
			case 'caption':
				$this->caption = \trim($value);
				return '';
			case 'tr':
				$value .= "|\n";
				if ($this->columnAlignments !== null) {
					//-- #fix by unixman to fill the head mark cells with max cells of the table
					if(count($this->columnAlignments) < $this->maxcells) {
						for($i=count($this->columnAlignments); $i<$this->maxcells; $i++) {
							$this->columnAlignments[] = '---';
						}
					}
					//-- #fix
					$value .= '|' . \implode('|', $this->columnAlignments) . "|\n";
					$this->columnAlignments = null;
				}
				return $value;
			case 'th':
			case 'td':
				//-- #fix by unixman
				$rowspan = $element->getAttribute('rowspan');
				if($rowspan) {
					if($element->getTagName() != 'th') { // on TH should not ... on TD, is really complicated, stop here and eliminate the cell
						return '';
					}
				}
				$colspan = (int) ($element->getAttribute('colspan') ?? 0);
				if($colspan > 0) {
					$colspan = (string) ' {T: @colspan='.(int)$colspan.'} ';
				} else {
					$colspan = '';
				}
				//-- #fix
				$this->cellnum++;
				if ($this->columnAlignments !== null) {
					$align = $element->getAttribute('align');
					$this->columnAlignments[] = self::$alignments[$align] ?? '---';
				}
				//-- #fix by unixman
				$tblsep = '';
				$tblstyles = '';
				if($this->cellnum === 1) {
				//	$tblsep = "\n:::\n:::\n";
					$tblsep = "\n".'\\'."\n";
				//	$tblsep = "\n".' '."\n";
					if($this->rownum < 2) {
						$tblstyles = '{!DEF!=AUTO-WIDTH;ALIGN-HEAD-LEFT;ALIGN-LEFT;NO-TABLE-HEAD;.bordered;.stripped;.doc-table;.max-cells-'.(int)$this->maxcells.';.max-rows-'.(int)$this->rownum.';}';
					} else {
						$tblstyles = '{!DEF!=AUTO-WIDTH;ALIGN-HEAD-CENTER;ALIGN-AUTO;.bordered;.stripped;.doc-table;.max-cells-'.(int)$this->maxcells.';.max-rows-'.(int)$this->rownum.';}';
					}
				}
				//-- #fix
				$value = \str_replace("\n", ' ', $value);
				$value = \str_replace('|', $this->config->getOption('table_pipe_escape') ?? '\|', $value);
				//-- #fix by unixman
				$value = (string) \preg_replace('/^( )*(\#){1,6}( )(.*)/m', '**${4}**', $value); // fix by unixman: transform H1..H6 inside a table cell with bold, as inline headings are not supported in markdown ... yet ... # https://github.com/mysticmind/reversemarkdown-net/issues/44
				//-- #fix
				return (string) $tblsep . '|' .$tblstyles . ' ' . \trim((string)$value) . $colspan . ' '; // includes fixes by unixman
			case 'thead':
			case 'tbody':
			case 'tfoot':
			case 'colgroup':
			case 'col':
				return $value;
			default:
				return '';
		} //end switch
	} //END FUNCTION


	/**
	 * @return string[]
	 */
	public function getSupportedTags(): array {
		return ['table', 'tr', 'th', 'td', 'thead', 'tbody', 'tfoot', 'colgroup', 'col', 'caption'];
	} //END FUNCTION


} //END CLASS


// #end
