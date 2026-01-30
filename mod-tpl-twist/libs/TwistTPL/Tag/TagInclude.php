<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL\Tag;

/**
 * Includes another, partial, template
 *
 * Example:
 *
 *     {% include 'foo' %}
 *
 *     Will include the template called 'foo'
 *
 *     {% include 'foo' with 'bar' %}
 *
 *     Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 *     {% include 'foo' for 'bar' %}
 *
 *     Will loop over all the values of bar, including the template foo, passing a variable called foo with each value of bar
 */
final class TagInclude extends \TwistTPL\AbstractTag {


	private $mainTemplateName;
	private $mainTemplateHash;
	private $mainTemplateDir;

	/**
	 * @var string The name of the template
	 */
	private $templateName;

	/**
	 * @var bool True if the variable is a collection
	 */
	private $collection;

	/**
	 * @var mixed The value to pass to the child template as the template name
	 */
	private $variable;

	/**
	 * @var \TwistTPL\Document The Document that represents the included template
	 */
	private $document = null;

	/**
	 * @var string The Source Hash
	 */
	protected $hash;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param \TwistTPL\AbstractInterfaceFileSystem $fileSystem
	 *
	 * @throws \Exception
	 */
	public function __construct(?string $markup, array &$tokens, ?\TwistTPL\AbstractInterfaceFileSystem $fileSystem=null) {
		//--
		$this->mainTemplateName = null;
		$this->mainTemplateHash = null;
		$this->mainTemplateDir = null;
		$arrTpls = (array) \TwistTPL\Twist::getRenderedTplRecords('tpl');
		if(\count($arrTpls) > 0) {
			$lastEntry = end($arrTpls);
			if(\is_array($lastEntry)) {
				if(isset($lastEntry['type']) && ($lastEntry['type'] === 'tpl')) {
					if(isset($lastEntry['name']) && isset($lastEntry['hash'])) {
						$this->mainTemplateName = (string) $lastEntry['name'];
						$this->mainTemplateHash = (string) $lastEntry['hash'];
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		if(((string)\trim((string)$this->mainTemplateName) == '') || (\strpos((string)$this->mainTemplateName, '/') === false)) {
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Path cannot be detected !');
			return;
		} //end if
		if((string)\trim((string)$this->mainTemplateHash) == '') {
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File have no Hash !');
			return;
		} //end if
		//--
		$dirOfMainTpl = (array) \explode('/', (string)$this->mainTemplateName);
		if(\count($dirOfMainTpl) < 3) {
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Path is Invalid (1) !');
			return;
		} //end if
		\array_pop($dirOfMainTpl); // remove last element aka file name from path
		if(\count($dirOfMainTpl) < 2) {
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Path is Invalid (2) !');
			return;
		} //end if
		$dirOfMainTpl = (string) \implode('/', (array)$dirOfMainTpl);
		$dirOfMainTpl = (string) \trim((string)$dirOfMainTpl);
		$dirOfMainTpl = (string) \trim((string)$dirOfMainTpl, '/');
		$dirOfMainTpl = (string) \trim((string)$dirOfMainTpl);
		if(((string)$dirOfMainTpl == '') OR \in_array((string)$dirOfMainTpl, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Path is Invalid (3): `'.$dirOfMainTpl.'`');
			return;
		} //end if
		//--
		$this->mainTemplateDir = (string) $dirOfMainTpl.'/'; // add the trailing slash
		if(((string)$this->mainTemplateDir == '') OR \in_array((string)$this->mainTemplateDir, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Path is Invalid (4): `'.$this->mainTemplateDir.'`');
			return;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$this->mainTemplateDir)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Path is Invalid (5): `'.$this->mainTemplateDir.'`');
			return;
		} //end if
		//--
		$regex = new \TwistTPL\Regexp('/("[^"]+"|\'[^\']+\'|[^\'"\s]+)(\s+(with|for)\s+('.\TwistTPL\Twist::get('QUOTED_FRAGMENT').'+))?/');
		//--
		if(!$regex->match($markup)) {
			throw new \Exception('Error in tag `include` - Valid syntax: include `[template]` (with|for) [object|collection]');
			return;
		} //end if
		//--
		$unquoted = (bool) ((strpos($regex->matches[1], '"') === false) && (strpos($regex->matches[1], "'") === false));
		//--
		$start = 1;
		$len = (int) ((int)strlen((string)$regex->matches[1]) - 2);
		if($unquoted) {
			$start = 0;
			$len = (int) strlen((string)$regex->matches[1]);
		} //end if
		//--
		$this->templateName = (string) \trim((string)\substr((string)$regex->matches[1], (int)$start, (int)$len));
		if((string)$this->templateName == '') {
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Name is Invalid (1): `'.$this->templateName.'`');
			return;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$this->templateName)) { // {{{SYNC-CHK-SAFE-PATH}}} ; ; can be a relative path if sub-tpl not necessary just a filename
			throw new \Exception(__METHOD__.' # Tag Include: Main Template File Name is Invalid (2): `'.$this->templateName.'`');
			return;
		} //end if
		//--
		if(isset($regex->matches[1])) {
			$this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] === 'for') : null;
			$this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
		} //end if
		//--
		$this->extractAttributes($markup);
		//--
		parent::__construct($markup, $tokens, $fileSystem);
		//--
	} //END FUNCTION


	/**
	 * Parses the tokens
	 *
	 * @param array $tokens
	 *
	 * @throws \Exception
	 */
	public function parse(array &$tokens) : void {
		//--
		$this->document = null;
		//--
		if($this->fileSystem === null) {
			throw new \Exception(__METHOD__.' # No file system');
			return;
		} //end if
		//-- read the source of the template and create a new sub document
		$source = (string) $this->fileSystem->readTemplateFile((string)$this->templateName, (string)$this->mainTemplateDir);
		if((string)$source == '') {
			throw new \Exception(__METHOD__.' # TPL Not Found for Include: '.(string)$this->mainTemplateDir.$this->templateName);
			return;
		} //end if
		//--
		$cache = \TwistTPL\Twist::getCache();
		if(!$cache) {
			$templateTokens = \TwistTPL\Twist::tokenize((string)$source, (string)$this->mainTemplateDir.$this->templateName, false); // tokens in this new document
			$this->document = new \TwistTPL\Document($templateTokens, $this->fileSystem);
			$this->document->setTwistVersion((string)\TwistTPL\Twist::VERSION); // stamp version, req. to validate back from cache
			$this->document->setTplIsRoot(false); // mark that this is a sub TPL
			$this->document->setTplFile((string)$this->mainTemplateDir.$this->templateName); // register the path if any of the current TPL (non-empty just for TPL files)
			return;
		} //end if
		//--
		$this->hash = (string) $this->subTplHash((string)$source, (string)$this->mainTemplateDir.$this->templateName); // {{{SYNC-TWIST-TPL-HASHING}}}
		//-- {{{SYNC-TWIST-TPL-OBJ-DOC-VALIDATE}}}
		$this->document = $cache->read($this->hash);
		if(!\is_object($this->document)) {
			$this->document = null; // must be object
		} // end if
		if($this->document instanceof \TwistTPL\Document) {
			if((string)$this->document->getMetaData('twistVersion') != (string)\TwistTPL\Twist::VERSION) {
				\trigger_error('#'.__METHOD__.'.WARNING# Wrong Object Version', \E_USER_NOTICE);
				$this->document = null; // invalid version
			} //end if
		} else {
			if(\is_object($this->document)) {
				\trigger_error('#'.__METHOD__.'.WARNING# Wrong Object Type', \E_USER_WARNING);
			} //end if
			$this->document = null; // wrong object type
		} //end if
		if(($this->document === null) || ($this->document->hasIncludes() === true)) {
			$templateTokens = \TwistTPL\Twist::tokenize((string)$source, (string)$this->mainTemplateDir.$this->templateName, false);
			$this->document = new \TwistTPL\Document($templateTokens, $this->fileSystem);
			$this->document->setTwistVersion((string)\TwistTPL\Twist::VERSION); // stamp version, req. to validate back from cache
			$this->document->setTplIsRoot(false); // mark that this is a sub TPL
			$this->document->setTplFile((string)$this->mainTemplateDir.$this->templateName); // register the path if any of the current TPL (non-empty just for TPL files)
			$cache->write($this->hash, $this->document);
		} //end if
		//-- #end sync
	} //END FUNCTION


	/**
	 * Check for cached includes; if there are - do not use cache
	 *
	 * @see \TwistTPL\Document::hasIncludes()
	 * @return boolean
	 */
	public function hasIncludes() {
		//--
		if($this->document->hasIncludes() === true) {
			return true;
		} //end if
		//--
		$source = (string) $this->fileSystem->readTemplateFile((string)$this->templateName, (string)$this->mainTemplateDir);
		if((string)$source == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# TPL Not Found for Include Test: '.(string)$this->mainTemplateDir.$this->templateName, \E_USER_WARNING);
			return false;
		} //end if
		//--
		$hash = (string) $this->subTplHash((string)$source, (string)$this->mainTemplateDir.$this->templateName); // {{{SYNC-TWIST-TPL-HASHING}}}
		//--
		if(((string)$this->hash === (string)$hash) AND (!!\TwistTPL\Twist::getCache()->exists((string)$hash))) {
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Renders the node
	 *
	 * @param \TwistTPL\Context $context
	 *
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		$result = '';
		$variable = $context->get($this->variable);
		$context->push();
		foreach($this->attributes as $key => $value) {
			$context->set($key, $context->get($value));
		} //end foreach
		if(($this->collection) && (\is_array($variable))) {
			foreach($variable as $kk => $item) {
				$context->set($this->templateName, $item); // does it need $this->mainTemplateDir as prefix ?
				$result .= (string) $this->document->render($context);
			} //end foreach
		} else {
			if(!\is_null($this->variable)) {
				$context->set($this->templateName, $variable); // does it need $this->mainTemplateDir as prefix ?
			} //end if
			$result .= (string) $this->document->render($context);
		} //end if else
		//--
		$context->pop();
		//--
		return (string) $result;
		//--
	} //END FUNCTION


	private function subTplHash(string $source, string $tplPath) : string { // {{{SYNC-TWIST-TPL-HASHING}}}
		//--
		return (string) \TwistTPL\Twist::tplHash((string)$source, (string)$tplPath, false); // root=false
		//--
	} //END FUNCTION


} //END CLASS


// #end
