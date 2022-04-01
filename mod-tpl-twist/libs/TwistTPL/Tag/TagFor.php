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
 * Loops over an array, assigning the current value to a given variable
 *
 * Example:
 *
 *     {%for item in array%} {{item}} {%endfor%}
 *
 *     With an array of 1, 2, 3, 4, will return 1 2 3 4
 *
 *     or
 *
 *     {%for i in (1..10)%} {{i}} {%endfor%}
 *     {%for i in (1..variable)%} {{i}} {%endfor%}
 *
 */
final class TagFor extends \TwistTPL\AbstractBlock {
	/**
	 * @var array The collection to loop over
	 */
	private $collectionName;

	/**
	 * @var string The variable name to assign collection elements to
	 */
	private $variableName;

	/**
	 * @var string The name of the loop, which is a compound of the collection and variable names
	 */
	private $name;

	/**
	 * @var string The type of the loop (collection or digit)
	 */
	private $type = 'collection';


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
		parent::__construct($markup, $tokens, $fileSystem);
		//--
		$syntaxRegexp = new \TwistTPL\Regexp('/(\w+)\s+in\s+('.\TwistTPL\Twist::get('VARIABLE_NAME').')/');
		//--
		if($syntaxRegexp->match($markup)) {
			$this->variableName = $syntaxRegexp->matches[1];
			$this->collectionName = $syntaxRegexp->matches[2];
			$this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
			$this->extractAttributes($markup);
		} else {
			$syntaxRegexp = new \TwistTPL\Regexp('/(\w+)\s+in\s+\((\d+|'.\TwistTPL\Twist::get('VARIABLE_NAME').')\s*\.\.\s*(\d+|'.\TwistTPL\Twist::get('VARIABLE_NAME').')\)/');
			if($syntaxRegexp->match($markup)) {
				$this->type = 'digit';
				$this->variableName = $syntaxRegexp->matches[1];
				$this->start = $syntaxRegexp->matches[2];
				$this->collectionName = $syntaxRegexp->matches[3];
				$this->name = $syntaxRegexp->matches[1].'-digit';
				$this->extractAttributes($markup);
			} else {
				throw new \Exception("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
				return;
			} //end if else
		} //end if else
	} //END FUNCTION


	/**
	 * Renders the tag
	 *
	 * @param \TwistTPL\Context $context
	 *
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		if(!isset($context->registers['for'])) {
			$context->registers['for'] = [];
		} //end if
		//--
		if($this->type === 'digit') {
			return (string) $this->renderDigit($context);
		} //end if
		//--
		return (string) $this->renderCollection($context); // the default
		//--
	} //END FUNCTION


	private function renderCollection(\TwistTPL\Context $context) : string {
		//--
		$collection = $context->get($this->collectionName);
		//--
		if($collection instanceof \Generator && !$collection->valid()) {
			return '';
		} //end if
		//--
		if($collection instanceof \Traversable) {
			$collection = \iterator_to_array($collection);
		} //end if
		//--
		if(\is_null($collection) || !\is_array($collection) || \count($collection) === 0) {
			return '';
		} //end if
		//--
		$range = [ 0, \count($collection) ];
		//--
		if(isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
			$offset = 0;
			if(isset($this->attributes['offset'])) {
				$offset = ($this->attributes['offset'] === 'continue') ? $context->registers['for'][$this->name] : $context->get($this->attributes['offset']);
			} //end if
			$limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;
			$rangeEnd = $limit ? $limit : \count($collection) - $offset;
			$range = array($offset, $rangeEnd);
			$context->registers['for'][$this->name] = $rangeEnd + $offset;
		} //end if
		//--
		$result = '';
		$segment = \array_slice($collection, $range[0], $range[1]);
		if(!\count($segment)) {
			return '';
		} //end if
		//--
		$context->push();
		$length = count($segment);
		//--
		$index = 0;
		foreach($segment as $key => $item) {
			//--
			$value = \is_numeric($key) ? $item : array($key, $item);
			//--
			$context->set($this->variableName, $value);
			//--
		//	$context->set('forloop', array(
			$context->set('loop', array( // fix by unixman !
				'name' 		=> $this->name,
				'length' 	=> (int) $length,
				'index' 	=> (int) $index + 1,
				'index0' 	=> (int) $index,
				'rindex' 	=> (int) $length - $index,
				'rindex0' 	=> (int) $length - $index - 1,
				'first' 	=> (int) ($index == 0),
				'last' 		=> (int) ($index == $length - 1)
			));
			//--
			$result .= $this->renderAll($this->nodelist, $context);
			//--
			$index++;
			//--
			if(isset($context->registers['break'])) {
				unset($context->registers['break']);
				break;
			} //end if
			if(isset($context->registers['continue'])) {
				unset($context->registers['continue']);
			} //end if
		} //end foreach
		//--
		$context->pop();
		//--
		return (string) $result;
		//--
	} //END FUNCTION


	private function renderDigit(\TwistTPL\Context $context) : string {
		//--
		$start = $this->start;
		if(!\is_int($this->start)) {
			$start = $context->get($this->start);
		} //end if
		//--
		$end = $this->collectionName;
		if(!\is_int($this->collectionName)) {
			$end = $context->get($this->collectionName);
		} //end if
		//--
		$range = array($start, $end);
		//--
		$context->push();
		$result = '';
		$index = 0;
		$length = $range[1] - $range[0];
		for($i=(int)$range[0]; $i<=(int)$range[1]; $i++) {
			//--
			$context->set($this->variableName, $i);
			//--
		//	$context->set('forloop', array(
			$context->set('loop', array( // fix by unixman !
				'name'		=> $this->name,
				'length'	=> (int) $length,
				'index'		=> (int) $index + 1,
				'index0'	=> (int) $index,
				'rindex'	=> (int) $length - $index,
				'rindex0'	=> (int) $length - $index - 1,
				'first'		=> (int) ($index == 0),
				'last'		=> (int) ($index == $length - 1)
			));
			//--
			$result .= $this->renderAll($this->nodelist, $context);
			//--
			$index++;
			//--
			if(isset($context->registers['break'])) {
				unset($context->registers['break']);
				break;
			} //end if
			if(isset($context->registers['continue'])) {
				unset($context->registers['continue']);
			} //end if
			//--
		} //end for
		//--
		$context->pop();
		//--
		return (string) $result;
		//--
	} //END FUNCTION


} //END CLASS

// #end
