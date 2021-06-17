<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;

/**
 * A helper class to convert HTML to Markdown.
 *
 * @author Colin O'Dell <colinodell@gmail.com>
 * @author Nick Cernis <nick@cern.is>
 *
 * @link https://github.com/thephpleague/html-to-markdown/ Latest version on GitHub.
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class HtmlConverter implements HtmlConverterInterface {

	/** @var Environment */
	protected $environment;


	/**
	 * Constructor
	 *
	 * @param Environment|array<string, mixed> $options Environment object or configuration options
	 */
	public function __construct($options = []) {
		if ($options instanceof Environment) {
			$this->environment = $options;
		} elseif (\is_array($options)) {
			$defaults = [
				'header_style' => 'atx', // Set to 'atx' to output H1 and H2 headers as # Header1 and ## Header2 ; 'setext' mode disabled by unixman, it was performing bad in some situations when converting some nested html syntax
				'suppress_errors' => true, // Set to false to show warnings when loading malformed HTML
				'strip_tags' => true, // Set to true to strip tags that don't have markdown equivalents. N.B. Strips tags, not their content. Useful to clean MS Word HTML output.
				'strip_placeholder_links' => false, // Set to true to remove <a> that doesn't have href.
				'bold_style' => '**', // DEPRECATED: Set to '__' if you prefer the underlined style
				'italic_style' => '_', // DEPRECATED: Set to '_' if you prefer the underlined style
				'remove_nodes' => '', // space-separated list of dom nodes that should be removed. example: 'meta style script'
				'hard_break' => false, // Set to true to turn <br> into `\n` instead of `  \n`
				'list_item_style' => '-', // Set the default character for each <li> in a <ul>. Can be '-', '*', or '+'
				'preserve_comments' => false, // Set to true to preserve comments, or set to an array of strings to preserve specific comments
				'use_autolinks' => true, // Set to true to use simple link syntax if possible. Will always use []() if set to false
				'table_pipe_escape' => '\|', // Replacement string for pipe characters inside markdown table cells
				'table_caption_side' => 'top', // Set to 'top' or 'bottom' to show <caption> content before or after table, null to suppress
			];
			$this->environment = Environment::createDefaultEnvironment($defaults);
			$this->environment->getConfig()->merge($options);
		} //end if else
	} //END FUNCTION


	public function getEnvironment(): Environment {
		return $this->environment;
	} //END FUNCTION


	public function getConfig(): Configuration {
		return $this->environment->getConfig();
	} //END FUNCTION


	/**
	 * Convert
	 *
	 * @see HtmlConverter::convert
	 *
	 * @return string The Markdown version of the html
	 */
	public function __invoke(string $html): string {
		return $this->convert($html);
	} //END FUNCTION


	/**
	 * Convert
	 *
	 * Loads HTML and passes to getMarkdown()
	 *
	 * @return string The Markdown version of the html
	 *
	 * @throws \InvalidArgumentException|\RuntimeException
	 */
	public function convert(string $html): string {
		$html = (string) \preg_replace('/<!--(.|\s)*?-->/', '', (string)$html); // fix by unixman
		if(\trim($html) === '') {
			return '';
		}
		$document = $this->createDOMDocument($html);
		// Work on the entire DOM tree (including head and body)
		if (! ($root = $document->getElementsByTagName('html')->item(0))) {
			throw new \InvalidArgumentException('Invalid HTML was provided');
		}
		$rootElement = new Element($root);
		$this->convertChildren($rootElement);
		// Store the now-modified DOMDocument as a string
		$markdown = $document->saveHTML();
		if ($markdown === false) {
			throw new \RuntimeException('Unknown error occurred during HTML to Markdown conversion');
		}
		return $this->sanitize($markdown);
	} //END FUNCTION


	private function createDOMDocument(string $html): \DOMDocument {
		if ($this->getConfig()->getOption('suppress_errors')) {
			// Suppress conversion errors (from http://bit.ly/pCCRSX)
			\libxml_clear_errors();
			\libxml_use_internal_errors(true);
		}
		//-- #fix by unixman
		/*
		$document = new \DOMDocument();
		$document->loadHTML('<?xml encoding="UTF-8">' . $html); // Hack to load utf-8 HTML (from http://bit.ly/pVDyCt)
		$document->encoding = 'UTF-8';
		*/
		//--
		if(stripos($html, '<body') === false) {
			//--
			// THIS IS A FIX for Tidy and DomDocument as some version of these parsers fail if only body provided ...
			// Trick: the meta charset have not be supplied because if set to UTF-8 the DomDocument will decode all possible entities, includding &Prime; thus the fixback to &quot; is no more available to force &quot; instead of " when using DomDocument
			//--
			$html = (string) '<!DOCTYPE html>'."\n".'<html>'."\n".'<head>'."\n".'<title></title>'."\n".'</head>'."\n".'<body>'."\n".$html."\n".'</body>'."\n".'</html>'."\n";
			//--
		} //end if
		//--
		$dom = new \DOMDocument('5', (string)\SMART_FRAMEWORK_CHARSET);
		//--
		$dom->encoding = (string) \SMART_FRAMEWORK_CHARSET;
		$dom->strictErrorChecking = false; 	// do not throw errors
		$dom->preserveWhiteSpace = false; 	// set this to false in order to real format HTML ...
		$dom->formatOutput = false; 		// try to format pretty-print the code (will work just partial as the preserve white space is true ...)
		$dom->resolveExternals = false; 	// disable load external entities from a doctype declaration
		$dom->validateOnParse = false; 		// this must be explicit disabled as if set to true it may try to download the DTD and after to validate (insecure ...)
		$dom->recover = true; // trying to parse non-well formed documents, for HTML make sense but not for XML
	//	$dom->substituteEntities = false; 	// this attribute ir proprietary for LibXML, it does not make any difference ... still buggy with replacing &quot; with " (it's decoded value)
		//-- pre fixes
		$html = (string) \str_replace('&quot;', '&Prime;', (string)$html); // fix: DomDocument will decode the &quot; to ", thus substitute with &Prime; (&#8243;) which is a unicode verion of it ″ and restore back thereafter ; if there are any &Prime; already converting &Prime; to &quot; later is not a problem ...
		//--
		@$dom->loadHTML(
			(string) $html, // fix: in some versions of DomDocument or LibXML if not enclosed in a body container there are some strange behaviours when getting back the HTML code, so need this function: compose_html_document
			\LIBXML_ERR_WARNING | \LIBXML_NONET | \LIBXML_PARSEHUGE | \LIBXML_BIGLINES | \LIBXML_HTML_NODEFDTD // {{{SYNC-LIBXML-OPTIONS}}} ; important !!! do not use the buggy flag LIBXML_HTML_NOIMPLIED as it will mess up the tags, is not stable enough inside LibXML
		);
		//-- #end fix
		if ($this->getConfig()->getOption('suppress_errors')) {
			\libxml_clear_errors();
			\libxml_use_internal_errors(false);
		}
	//	return $document;
		return $dom; // fux by unixman
	} //END FUNCTION


	/**
	 * Convert Children
	 *
	 * Recursive function to drill into the DOM and convert each node into Markdown from the inside out.
	 *
	 * Finds children of each node and convert those to #text nodes containing their Markdown equivalent,
	 * starting with the innermost element and working up to the outermost element.
	 */
	private function convertChildren(ElementInterface $element): void {
		// links inside H1..H6
		if ($element->isDescendantOf(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) && $element->getTagName() === 'a') { // fix for jQuery # realm=jquery&key=16
			return;
		}
		// Don't convert HTML code inside <code> and <pre> blocks to Markdown - that should stay as HTML
		// except if the current node is a code tag, which needs to be converted by the CodeConverter.
		if ($element->isDescendantOf(['pre', 'code']) && $element->getTagName() !== 'code') {
			return;
		}
		//-- fix by unixman
		if(
			($element->isDescendantOf(['table']) && $element->getTagName() === 'table')
//			OR
//			($element->isDescendantOf(['td']) && $element->getTagName() !== 'code')
//			OR
//			($element->isDescendantOf(['th']) && $element->getTagName() !== 'code')
		) {
		//	$element->setFinalMarkdown((string)\Smart::striptags((string)$element->getValue())); // this is worst than without it, will loose some spaces within td/th ...
			return;
		}
		//--
		// Give converter a chance to inspect/modify the DOM before children are converted
		$converter = $this->environment->getConverterByTag($element->getTagName());
		if ($converter instanceof PreConverterInterface) {
			$converter->preConvert($element);
		}
		// If the node has children, convert those to Markdown first
		if ($element->hasChildren()) {
			foreach ($element->getChildren() as $child) {
				$this->convertChildren($child);
			}
		}
		// Now that child nodes have been converted, convert the original node
		$markdown = $this->convertToMarkdown($element);
		// Create a DOM text node containing the Markdown equivalent of the original node
		// Replace the old $node e.g. '<h3>Title</h3>' with the new $markdown_node e.g. '### Title'
		$element->setFinalMarkdown($markdown);
	} //END FUNCTION


	/**
	 * Convert to Markdown
	 *
	 * Converts an individual node into a #text node containing a string of its Markdown equivalent.
	 *
	 * Example: An <h3> node with text content of 'Title' becomes a text node with content of '### Title'
	 *
	 * @return string The converted HTML as Markdown
	 */
	protected function convertToMarkdown(ElementInterface $element): string {
		$tag = $element->getTagName();
		// Strip nodes named in remove_nodes
		$tagsToRemove = \explode(' ', $this->getConfig()->getOption('remove_nodes') ?? '');
		if (\in_array($tag, $tagsToRemove, true)) {
			return '';
		}
		$converter = $this->environment->getConverterByTag($tag);
		return $converter->convert($element);
	} //END FUNCTION


	protected function sanitize(string $markdown): string {
		$markdown = \html_entity_decode($markdown, ENT_QUOTES, 'UTF-8');
		$markdown = \preg_replace('/<!DOCTYPE [^>]+>/', '', $markdown); // Strip doctype declaration
		\assert($markdown !== null);
		$markdown = \trim($markdown); // Remove blank spaces at the beggining of the html
		/*
		 * Removing unwanted tags. Tags should be added to the array in the order they are expected.
		 * XML, html and body opening tags should be in that order. Same case with closing tags
		 */
		$unwanted = ['<?xml encoding="UTF-8">', '<html>', '</html>', '<body>', '</body>', '<head>', '</head>', '&#xD;'];
		foreach ($unwanted as $tag) {
			if (\strpos($tag, '/') === false) {
				// Opening tags
				if (\strpos($markdown, $tag) === 0) {
					$markdown = \substr($markdown, \strlen($tag));
				}
			} else {
				// Closing tags
				if (\strpos($markdown, $tag) === \strlen($markdown) - \strlen($tag)) {
					$markdown = \substr($markdown, 0, -\strlen($tag));
				}
			}
		} //end foreach
		//-- #fix by unixman
		$markdown = (string) \str_replace(' \\ ', ' ', (string)$markdown); // fix by unixman: this comes from malformed conversions of newline ... it was \n\\\n and get newline converted to space
		return \trim($markdown);
		//return \trim($markdown, "\n\r\0\x0B");
		//-- #end fix
	} //END FUNCTION


	/**
	 * Pass a series of key-value pairs in an array; these will be passed
	 * through the config and set.
	 * The advantage of this is that it can allow for static use (IE in Laravel).
	 * An example being:
	 *
	 * HtmlConverter::setOptions(['strip_tags' => true])->convert('<h1>test</h1>');
	 *
	 * @param array<string, mixed> $options
	 *
	 * @return $this
	 */
	public function setOptions(array $options) {
		$config = $this->getConfig();
		foreach ($options as $key => $option) {
			$config->setOption($key, $option);
		} //end foreach
		return $this;
	} //END FUNCTION


} //END CLASS


// #end
