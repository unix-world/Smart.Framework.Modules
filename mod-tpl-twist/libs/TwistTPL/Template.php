<?php

// fixes by unixman

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL;

/**
 * The Template class.
 *
 * Example:
 *
 *     $tpl = new \TwistTPL\Template();
 *     $tpl->parseFile('file.twist.htm', 'views/');
 *     $tpl->render([ 'foo'=>1, 'bar'=>2 ]);
 */
final class Template {

	/**
	 * @var \TwistTPL\Document The root of the node tree
	 */
	private $root = null;

	/**
	 * @var AbstractInterfaceFileSystem The file system to use for includes
	 */
	private $fileSystem = null;

	/**
	 * @var array Globally included filters
	 */
	private $filters = [];


	/**
	 * Constructor
	 *
	 * @param ?string $path
	 * @param ?array $cache
	 *
	 */
	public function __construct(?string $path=null, ?array $cache=null) {
		//--
		$this->fileSystem = null;
		if((string)$path != '') {
			$this->fileSystem = new \TwistTPL\FileSystem\LocalFileSystem((string)$path); // TODO: check here for a valid path !
		} //end if
		//--
		if(\is_array($cache) && ((int)\count($cache) == 3) && isset($cache['cache']) && ($cache['cache'] === 'file') && isset($cache['cache:path']) && isset($cache['root:path'])) {
			\TwistTPL\Twist::setCache((array)$cache);
		} //end if
		//--
	} //END FUNCTION


	/**
	 * Parses the given template file
	 * !!! Important !!! Never allow Rendering a string template, but just file ; If string template is re-rendered without special syntax escaping could lead to severe syntax injections ... which cannot actually be proper escaped because of the common syntax that this kind of TPL systems are using {{ ... }}
	 *
	 * @param string $templateFile
	 * @param string $templatePath
	 * @throws \Exception
	 * @return \TwistTPL\Template | null
	 */
	public function parseFile(string $templateFile, string $templatePath) : ?\TwistTPL\Template {
		if(!$this->fileSystem) {
			throw new \Exception('Could not load a template without an initialized file system');
			return null;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_FILE_NAME, (string)$templateFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception('Invalid TPL File: `'.$templateFile.'`');
			return null;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templatePath)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception('Invalid TPL Path: `'.$templatePath.'`');
			return null;
		} //end if
		return (object) $this->parseTpl(
			(string) $this->fileSystem->readTemplateFile((string)$templateFile, (string)$templatePath),
			(string) $templatePath.$templateFile
		);
	} //END FUNCTION


	/**
	 * Renders the current template
	 *
	 * @param array $assigns an array of values for the template
	 * @param array $registers additional registers for the template
	 *
	 * @return string
	 */
	public function render(array $assigns=[], array $registers=[]) : string {
		//--
		$context = new \TwistTPL\Context($assigns, $registers);
		//--
		foreach($this->filters as $key => $filter) {
			if(\is_array($filter)) {
				$context->addFilters(...$filter); // array unpack a callback saved as second argument
			} else {
				$context->addFilters($filter);
			} //end if else
		} //end foreach
		//--
		return (string) $this->root->render($context);
		//--
	} //END FUNCTION


	/**
	 * Register the filter
	 *
	 * @param string $filter
	 *
	 * @return Boolean
	 */
	public function registerFilter($filter, ?callable $callback=null) : bool { // unixman: added nullable type (PHP 8.4 fix)
		//--
		if($callback) {
			$this->filters[] = [ $filter, $callback ]; // store callback for later use
			return true;
		} else {
			$this->filters[] = $filter;
			return true;
		} //end if else
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * @return \TwistTPL\Document | null
	 */
	public function getRoot() : ?\TwistTPL\Document {
		//--
		return $this->root;
		//--
	} //END FUNCTION


	//======= [PRIVATES]


	/**
	 * Parses the given source string
	 *
	 * @param string $source
	 *
	 * @return \TwistTPL\Template
	 */
	private function parseTpl(string $source, string $tplPath) : \TwistTPL\Template {
		//--
		$source = (string) \preg_replace('/\{\#.*?\#\}/s', '', (string)$source); // remove Twig style {# comments #}
		//--
		$this->root = null;
		$cache = \TwistTPL\Twist::getCache();
		if(!\is_object($cache)) {
			return $this->parseDoTpl((string)$source, (string)$tplPath);
		} //end if
		$hash = (string) \TwistTPL\Twist::tplHash((string)$source, (string)$tplPath, true); // {{{SYNC-TWIST-TPL-HASHING}}}
		//-- {{{SYNC-TWIST-TPL-OBJ-DOC-VALIDATE}}}
		$this->root = $cache->read((string)$hash); // object or null
		if(!\is_object($this->root)) {
			$this->root = null; // must be object
		} // end if
		if($this->root instanceof \TwistTPL\Document) {
			if((string)$this->root->getMetaData('twistVersion') != (string)\TwistTPL\Twist::VERSION) {
				\trigger_error('#'.__METHOD__.'.WARNING# Wrong Object Version', \E_USER_NOTICE);
				$this->root = null; // invalid version
			} //end if
		} else {
			if(\is_object($this->root)) {
				\trigger_error('#'.__METHOD__.'.WARNING# Wrong Object Type', \E_USER_WARNING);
			} //end if
			$this->root = null; // wrong object type
		} //end if
		if(($this->root === null) || ($this->root->hasIncludes() === true)) { // if no cached version exists, or if it checks for includes
			$this->parseDoTpl((string)$source, (string)$tplPath);
			if(\is_object($this->root)) {
				if($this->root instanceof \TwistTPL\Document) {
					$cache->write((string)$hash, (object)$this->root);
				} //end if
			} //end if
		} //end if
		//-- #end sync
		return $this;
		//--
	} //END FUNCTION


	/**
	 * Do Parses the given source string regardless of caching
	 *
	 * @param string $source
	 *
	 * @return Template
	 */
	private function parseDoTpl(string $source, string $tplPath) : \TwistTPL\Template {
		//--
		$tokens = (array) \TwistTPL\Twist::tokenize((string)$source, (string)$tplPath, true);
		//--
		$this->root = new \TwistTPL\Document($tokens, $this->fileSystem);
		$this->root->setTwistVersion((string)\TwistTPL\Twist::VERSION); // stamp version, req. to validate back from cache
		$this->root->setTplIsRoot(true); // mark that this is a main TPL
		$this->root->setTplFile((string)$tplPath); // register the path if any of the current TPL (non-empty just for TPL files)
		//--
		return $this;
		//--
	} //END FUNCTION


} //END CLASS


// #end
