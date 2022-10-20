<?php

//declare(strict_types=1);

namespace HTML2Markdown;

use HTML2Markdown\SmartFixes;

use HTML2Markdown\Converter\DefaultConverter;

use HTML2Markdown\Converter\HardBreakConverter;
use HTML2Markdown\Converter\HeaderConverter;
use HTML2Markdown\Converter\DivConverter;
use HTML2Markdown\Converter\ParagraphConverter;
use HTML2Markdown\Converter\EmphasisConverter;
use HTML2Markdown\Converter\UnderlineConverter;
use HTML2Markdown\Converter\StrikeConverter;
use HTML2Markdown\Converter\QuoteConverter;
use HTML2Markdown\Converter\MarkConverter;
use HTML2Markdown\Converter\CiteConverter;
use HTML2Markdown\Converter\VarConverter;
use HTML2Markdown\Converter\DelConverter;
use HTML2Markdown\Converter\InsConverter;
use HTML2Markdown\Converter\SubConverter;
use HTML2Markdown\Converter\SupConverter;
use HTML2Markdown\Converter\LinkConverter;
use HTML2Markdown\Converter\ListBlockConverter;
use HTML2Markdown\Converter\ListItemConverter;
use HTML2Markdown\Converter\PreformattedConverter;
use HTML2Markdown\Converter\CodeConverter;
use HTML2Markdown\Converter\BlockquoteConverter;
use HTML2Markdown\Converter\TableConverter;
use HTML2Markdown\Converter\HorizontalRuleConverter;
use HTML2Markdown\Converter\ImageConverter;
use HTML2Markdown\Converter\MathConverter;
use HTML2Markdown\Converter\ButtonConverter;
use HTML2Markdown\Converter\CommentConverter;

use HTML2Markdown\Converter\TextConverter;

/**
 * A helper class to convert HTML to Markdown.
 * @version 5.1.0 # refactored by unixman v.20220917.0118
 * @author Colin O'Dell <colinodell@gmail.com>
 * @author Nick Cernis <nick@cern.is>
 * @link https://github.com/thephpleague/html-to-markdown/ Latest version on GitHub.
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
final class HtmlConverter {

	private $options = [
		'suppress_errors' 			=> true, 							// Set to false to show warnings when loading malformed HTML
		'hard_break' 				=> false, 							// Set to true to turn <br> into `\n` instead of `  \n`
		'preserve_comments' 		=> false, 							// Set to true to preserve comments, or set to an array of strings to preserve specific comments
		'table_pipe_escape' 		=> '\|', 							// Replacement string for pipe characters inside markdown table cells
		'table_caption_side' 		=> 'top', 							// Set to 'top' or 'bottom' to show <caption> content before or after table, null to suppress
		//-- unixman changed
		'remove_nodes' 				=> [ 'svg' ], 						// array list of dom nodes that should be removed ; example: [ 'meta', 'style', 'script' ]
		'header_style' 				=> 'atx', 							// Set to 'atx' to output H1 and H2 headers as # Header1 and ## Header2
		'bold_style' 				=> SmartFixes::MKDW_TAG_BOLD, 		// v2 bold
		'italic_style' 				=> SmartFixes::MKDW_TAG_ITALIC, 	// v2 emphasys
		'list_item_style' 			=> SmartFixes::MKDW_TAG_LI, 		// Set the default character for each <li> in a <ul>. Can be '-', '*', or '+'
		'list_item_style_alternate' => SmartFixes::MKDW_TAG_LI_ALT, 	// alternate list item style
	];

	private const UNWANTED_TAGS = [ '<?xml encoding="UTF-8">', '<html>', '</html>', '<body>', '</body>', '<head>', '</head>', '&#xD;' ];

	private $converters = []; // internal use


	/**
	 * Constructor
	 *
	 * @param array<string, mixed> $options configuration list array
	 */
	public function __construct(?array $options=[]) {
		//-- unixman
		if(\is_array($options)) {
			if(\array_key_exists('suppress_errors', (array)$options)) {
				$this->options['suppress_errors'] = (bool) $options['suppress_errors'];
			} //end if
			if(\array_key_exists('preserve_comments', (array)$options)) {
				$this->options['preserve_comments'] = (bool) $options['preserve_comments'];
			} //end if
			if(\array_key_exists('hard_break', (array)$options)) {
				$this->options['hard_break'] = (bool) $options['hard_break'];
			} //end if
			if(\array_key_exists('remove_nodes', (array)$options)) {
				if(\is_array($options['remove_nodes'])) {
					$this->options['remove_nodes'] = [];
					foreach($options['remove_nodes'] as $key => $val) {
						$val = (string) \strtolower((string)\trim((string)$val));
						if((string)$val != '') {
							if(!\in_array((string)$val, (array)$this->options['remove_nodes'])) { // avoid duplicates
								if(\preg_match('/^[a-z]+$/', (string)$val)) { // must be a tag name
									$this->options['remove_nodes'][] = (string) $val;
								} //end if
							} //end if
						} //end if
					} //end foreach
				} //end if
			} //end if
		} //end if
		//--
		$this->addConverter(new DefaultConverter((array)$this->options)); // this is the fallback for elements that have no converter
		//--
		$this->addConverter(new HardBreakConverter((array)$this->options));
		$this->addConverter(new HeaderConverter((array)$this->options));
		$this->addConverter(new DivConverter((array)$this->options));
		$this->addConverter(new ParagraphConverter((array)$this->options));
		$this->addConverter(new EmphasisConverter((array)$this->options));
		$this->addConverter(new UnderlineConverter((array)$this->options));
		$this->addConverter(new StrikeConverter((array)$this->options));
		$this->addConverter(new QuoteConverter((array)$this->options));
		$this->addConverter(new MarkConverter((array)$this->options));
		$this->addConverter(new CiteConverter((array)$this->options));
		$this->addConverter(new VarConverter((array)$this->options));
		$this->addConverter(new DelConverter((array)$this->options));
		$this->addConverter(new InsConverter((array)$this->options));
		$this->addConverter(new SubConverter((array)$this->options));
		$this->addConverter(new SupConverter((array)$this->options));
		$this->addConverter(new LinkConverter((array)$this->options));
		$this->addConverter(new ListBlockConverter((array)$this->options));
		$this->addConverter(new ListItemConverter((array)$this->options));
		$this->addConverter(new PreformattedConverter((array)$this->options));
		$this->addConverter(new CodeConverter((array)$this->options));
		$this->addConverter(new BlockquoteConverter((array)$this->options));
		$this->addConverter(new TableConverter((array)$this->options));
		$this->addConverter(new HorizontalRuleConverter((array)$this->options));
		$this->addConverter(new ImageConverter((array)$this->options));
		$this->addConverter(new MathConverter((array)$this->options));
		$this->addConverter(new ButtonConverter((array)$this->options));
		$this->addConverter(new CommentConverter((array)$this->options));
		//--
		$this->addConverter(new TextConverter((array)$this->options)); // this is the final #text converter !
		//--
	} //END FUNCTION


	/**
	 * Convert
	 * Loads HTML and passes to getMarkdown()
	 * @return string The Markdown version of the html
	 * @throws \Exception
	 */
	public function convert(?string $html): string {
		//--
		$html = (string) \preg_replace('/<!--(.|\s)*?-->/', '', (string)$html); // fix by unixman
		//-- fix leading spaces before tags in a line ... this breaks LI and others
	//	$html = \str_replace("\t", '    ', (string)$html); // fix for preserve leading spaces in code : TODO
		$html = (string) SmartFixes::normalizeNewLines((string)$html);
		$arr = (array) explode("\n", (string)$html);
		$html = [];
		foreach($arr as $idx => $line) {
			if(\strpos((string)\ltrim((string)$line), '<') === 0) {
				$line = (string) \ltrim((string)$line);
			} //end if
			$html[] = (string) $line;
		} //end foreach
		$html = (string) \implode("\n", (array)$html);
	//	$html = (string) SmartFixes::normalizeMultiConsecutiveEmptyLines((string)$html);
		//--
		$html = (string) \trim((string)$html);
		if((string)$html == '') {
			return '';
		} //end if
		//--
		$document = $this->createDOMDocument((string)$html);
		//-- work on the entire DOM tree (including head and body)
		if(!($root = $document->getElementsByTagName('html')->item(0))) {
			SmartFixes::logWarning((string)__METHOD__, 'Invalid HTML was provided');
			return '';
		} //end if
		//--
		$rootElement = new Element($root);
		$this->convertChildren($rootElement);
		//--
		$markdown = $document->saveHTML(); // mixed ; Store the now-modified DOMDocument as a string
		if($markdown === false) {
			SmartFixes::logWarning((string)__METHOD__, 'Unknown error occurred during HTML to Markdown conversion');
			return '';
		} //end if
		//--
		return (string) $this->sanitize((string)$markdown);
		//--
	} //END FUNCTION


	private function createDOMDocument(string $html): \DOMDocument { // rewrite by unixman
		//--
		if(!!$this->options['suppress_errors']) {
			\libxml_clear_errors();
			\libxml_use_internal_errors(true);
		} //end if
		//--
		if(\stripos($html, '<body') === false) {
			//--
			// THIS IS A FIX for Tidy and DomDocument as some version of these parsers fail if only body provided ...
			// Trick: the meta charset have not be supplied because if set to UTF-8 the DomDocument will decode all possible entities, includding &Prime; thus the fixback to &quot; is no more available to force &quot; instead of " when using DomDocument
			//--
			$html = (string) '<!DOCTYPE html>'."\n".'<html>'."\n".'<head>'."\n".'<title></title>'."\n".'</head>'."\n".'<body>'."\n".$html."\n".'</body>'."\n".'</html>'."\n";
			//--
		} //end if
		//--
		$dom = new \DOMDocument('5', (string)SmartFixes::getCharset());
		$html = '<?xml encoding="'.(string)SmartFixes::getCharset().'">'."\n".$html; // hack to load utf-8 HTML (from http://bit.ly/pVDyCt)
		//--
		$dom->encoding 				= (string) 	SmartFixes::getCharset();
		$dom->strictErrorChecking 	= false; 	// do not log errors
		$dom->preserveWhiteSpace 	= false; 	// set this to false in order to real format HTML ...
		$dom->formatOutput 			= false; 	// try to format pretty-print the code (will work just partial as the preserve white space is true ...)
		$dom->resolveExternals 		= false; 	// disable load external entities from a doctype declaration
		$dom->validateOnParse 		= false; 	// this must be explicit disabled as if set to true it may try to download the DTD and after to validate (insecure ...)
		$dom->recover 				= true; 	// trying to parse non-well formed documents, for HTML make sense but not for XML
	//	$dom->substituteEntities = false; 		// this attribute ir proprietary for LibXML, it does not make any difference ... still buggy with replacing &quot; with " (it's decoded value)
		//-- pre fixes
		$html = (string) \strtr((string)$html, [
			(string)SmartFixes::SPECIAL_CHAR_NEWLINE_MARK 	=> (string) SmartFixes::SPECIAL_CHAR_NEWLINE_REPL, // fix special character
			'&quot;' 										=> '&Prime;', // fix: DomDocument will decode the &quot; to ", thus substitute with &Prime; (&#8243;) which is a unicode verion of it ″ and restore back thereafter ; if there are any &Prime; already converting &Prime; to &quot; later is not a problem ...
		]);
		//--
		@$dom->loadHTML(
			(string) $html, // fix: in some versions of DomDocument or LibXML if not enclosed in a body container there are some strange behaviours when getting back the HTML code, so need this function: compose_html_document
			\LIBXML_ERR_WARNING | \LIBXML_NONET | \LIBXML_PARSEHUGE | \LIBXML_BIGLINES | \LIBXML_HTML_NODEFDTD // {{{SYNC-LIBXML-OPTIONS}}} ; important !!! do not use the buggy flag LIBXML_HTML_NOIMPLIED as it will mess up the tags, is not stable enough inside LibXML
		);
		//--
		if(!!$this->options['suppress_errors']) {
			\libxml_clear_errors();
			\libxml_use_internal_errors(false);
		} //end if
		//--
		return $dom; // object
		//--
	} //END FUNCTION


	/**
	 * Convert Children
	 * Recursive function to drill into the DOM and convert each node into Markdown from the inside out.
	 * Finds children of each node and convert those to #text nodes containing their Markdown equivalent,
	 * starting with the innermost element and working up to the outermost element.
	 */
	private function convertChildren(ElementInterface $element): void {

		//--
		$tag = (string) $element->getTagName();
		//--

		//-- add fix by unixman: fix for jQuery # realm=jquery&key=16
		if($element->isDescendantOf(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) && ((string)$tag == 'a')) {
		//	return;
		} //end if
		//-- #fix

		//--
		// Don't convert HTML code inside <code> and <pre> blocks to Markdown - that should stay as HTML
		// except if the current node is a code tag, which needs to be converted by the CodeConverter.
		if($element->isDescendantOf(['pre', 'code']) && $element->getTagName() !== 'code') {
			return;
		} //end if
		//--

		//-- add fix by unixman: don't re-escape inside math latex expressions
		if($element->isDescendantOf(['annotation'])) {
			return;
		} //end if
		//-- #fix

		//-- fix by unixman for tables
		if(
			($element->isDescendantOf(['table']) && ($element->getTagName() === 'table'))
		//	||
		//	($element->isDescendantOf(['td']) && ($element->getTagName() !== 'code'))
		//	||
		//	($element->isDescendantOf(['th']) && ($element->getTagName() !== 'code'))
		) {
		//	$element->setFinalMarkdown((string)SmartFixes::stripTags((string)$element->getValue())); // this is worst than without it, will loose some spaces within td/th ...
			return;
		} //end if
		//-- #fix

		//-- give to converter a chance to inspect / modify the DOM before children are converted
		$converter = $this->getConverterByTag($element->getTagName());
		if($converter instanceof PreConverterInterface) {
			$converter->preConvert($element);
		} //end if
		//--

		//-- if the node has children, convert those to markdown first
		if($element->hasChildren()) {
			foreach($element->getChildren() as $child) {
				$this->convertChildren($child);
			} //end foreach
		} //end if
		//--

		//-- now the child nodes have been converted, convert the original node
		$markdown = (string) $this->convertToMarkdown($element);
		//--

		//--
		if(((string)$tag == 'ul') || ((string)$tag == 'ol')) {
			$markdown = (string) SmartFixes::normalizeMultiConsecutiveEmptyLines($markdown);
			$marr = (array) \explode("\n", (string)$markdown);
			$markdown = [];
			foreach($marr as $ky => $ln) {
				if(
					((\strpos((string)$ln, '-') === 0) && ((string)\trim((string)$ln) == '-'))
					OR
					((\strpos((string)$ln, '+') === 0) && ((string)\trim((string)$ln) == '+'))
					OR
					((\strpos((string)$ln, '*') === 0) && ((string)\trim((string)$ln) == '*'))
					OR
					((\strpos((string)$ln, '1.') === 0) && ((string)\trim((string)$ln) == '1.')) // {{{SYNC-HTML2MKDW-FIX-OL}}} ; use just 1. not 1. 2. 3. because here is too complicated ...
				) {
					$ln .= ' &nbsp;';
				} //end if
				$markdown[] = (string) $ln;
			} //end foreach
			$marr = null;
			$markdown = (string) \implode("\n", (array)$markdown);
			$markdown = (string) "\n".$markdown."\n";
		} //end if
		//--

		//--
		// Create a DOM text node containing the Markdown equivalent of the original node
		// Replace the old $node e.g. '<h3>Title</h3>' with the new $markdown_node e.g. '### Title'
		$element->setFinalMarkdown($markdown);
		//--

	} //END FUNCTION


	/**
	 * Convert to Markdown
	 * Converts an individual node into a #text node containing a string of its Markdown equivalent.
	 * Example: An <h3> node with text content of 'Title' becomes a text node with content of '### Title'
	 * @return string The converted HTML as Markdown
	 */
	private function convertToMarkdown(ElementInterface $element): string {
		//--
		$tag = (string) \strtolower((string)\trim((string)$element->getTagName()));
		//-- strip nodes named in remove_nodes
		$tagsToRemove = (array) ($this->options['remove_nodes'] ?? null);
		if(\in_array((string)$tag, (array)$tagsToRemove, true)) {
			return '';
		} //end if
		//--
		$converter = $this->getConverterByTag($tag);
		if(!\is_object($converter)) { // fix by unixman: Call to a member function convert() on null
			return '';
		} //end if
		//--
		// ? TO DO HERE ESCAPE MODES ?
		//--
		return (string) $converter->convert($element);
		//--
	} //END FUNCTION


	private function sanitize(string $markdown): string {
		//--
	//	$markdown = \html_entity_decode($markdown, ENT_QUOTES, 'UTF-8');
		$markdown = (string) SmartFixes::decodeHtmlEntity((string)$markdown); // fix by unixman
		//--
		$markdown = (string) \preg_replace('/<!DOCTYPE [^>]+>/', '', $markdown); // Strip doctype declaration
		$markdown = (string) \trim((string)$markdown); // Remove blank spaces at the begining of the html
		if((string)$markdown == '') { // fix by unixman
			return '';
		} //end if
		//--
		// Removing unwanted tags. Tags should be added to the array in the order they are expected.
		// XML, html and body opening tags should be in that order. Same case with closing tags
		foreach (self::UNWANTED_TAGS as $kk => $tag) {
			if(\strpos((string)$tag, '/') === false) {
				if(\strpos($markdown, $tag) === 0) { // Opening tags
					$markdown = \substr($markdown, \strlen($tag));
				} //end if
			} else {
				if(\strpos($markdown, $tag) === \strlen($markdown) - \strlen($tag)) { // Closing tags
					$markdown = \substr($markdown, 0, -\strlen($tag));
				} //end if
			} //end if else
		} //end foreach
		//-- fix by unixman
		$markdown = (string) SmartFixes::revertSpecials((string)$markdown);
		$markdown = (string) \strtr((string)$markdown, [
			'&Prime;' 	=> '"', // fix back &quot; (from DOM)
			'″' 		=> '"', // fix back &quot; (from DOM)
		]);
		//-- #fix
	//	return (string) \trim((string)$markdown, "\n\r\0\x0B");
		return (string) \trim((string)$markdown);
		//--
	} //END FUNCTION


	private function getConverterByTag(string $tag): ?ConverterInterface {
		//--
		if(isset($this->converters[(string)$tag])) {
			return $this->converters[(string)$tag];
		} //end if
		//--
		return $this->converters[DefaultConverter::DEFAULT_CONVERTER];
		//--
	} //END FUNCTION


	private function addConverter(ConverterInterface $converter): void {
		//--
		if(!\is_subclass_of($converter, '\\HTML2Markdown\\AbstractConverterConfig')) {
			SmartFixes::logNotice((string)__METHOD__, 'Invalid Converter Class: `'.\get_class($converter).'`'); // fix by unixman
			return;
		} //end if
		//--
		foreach($converter->getSupportedTags() as $tag) {
			$tag = (string) \trim((string)$tag);
			if((string)$tag != '') {
				$this->converters[(string)$tag] = $converter;
			} //end if
		} //end foreach
		//--
	} //END FUNCTION


} //END CLASS

// #end
