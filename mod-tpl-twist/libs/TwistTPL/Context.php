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
 * Context keeps the variable stack and resolves variables, as well as keywords.
 */
final class Context {

	/**
	 * Global scopes
	 *
	 * @var array
	 */
	public $environments = array();

	/**
	 * Registers for non-variable state data
	 *
	 * @var array
	 */
	public $registers;

	/**
	 * Local scopes
	 *
	 * @var array
	 */
	protected $assigns;

	/**
	 * The filterbank holds all the filters
	 *
	 * @var Filterbank
	 */
	protected $filterbank;


	/**
	 * Constructor
	 *
	 * @param array $assigns
	 * @param array $registers
	 */
	public function __construct(array $assigns=[], array $registers=[]) {
		$this->assigns = [ $assigns ];
		$this->registers = $registers;
		$this->filterbank = new \TwistTPL\Filterbank($this);
		// first empty array serves as source for overrides, e.g. as in TagDecrement
		$this->environments = [ [], [] ];
	//	$this->environments[1] = $_SERVER; // $this->environments[1] was reserved for $_SERVER, but can be mapped to somthing else !
	} //END FUNCTION


	/**
	 * Add a filter to the context
	 *
	 * @param mixed $filter
	 */
	public function addFilters($filter, ?callable $callback=null) { // unixman: added nullable type (PHP 8.4 fix)
		$this->filterbank->addFilter($filter, $callback);
	} //END FUNCTION


	/**
	 * Invoke the filter that matches given name
	 *
	 * @param string $name The name of the filter
	 * @param mixed $value The value to filter
	 * @param array $args Additional arguments for the filter
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function invoke($name, $value, array $args=[]) {
		try {
			return $this->filterbank->invoke($name, $value, $args);
		} catch(\TypeError $typeError) {
			throw new \Exception($typeError->getMessage(), 0, $typeError);
		} //end try catch
	} //END FUNCTION


	/**
	 * Merges the given assigns into the current assigns
	 *
	 * @param array $newAssigns
	 */
	public function merge($newAssigns) : bool {
		$this->assigns[0] = \array_merge($this->assigns[0], $newAssigns);
		return true;
	} //END FUNCTION


	/**
	 * Push new local scope on the stack.
	 *
	 * @return bool
	 */
	public function push() : bool {
		\array_unshift($this->assigns, array());
		return true;
	} //END FUNCTION


	/**
	 * Pops the current scope from the stack.
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function pop() : bool {
		if(\count($this->assigns) == 1) {
			throw new \Exception('No elements to pop');
			return false;
		} //end if
		\array_shift($this->assigns);
		return true;
	} //END FUNCTION


	/**
	 * Replaces []
	 *
	 * @param string
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return $this->resolve($key);
	} //END FUNCTION


	/**
	 * Replaces []=
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $global
	 */
	public function set($key, $value, $global=false) {
		if($global) {
			for($i=0; $i<\count($this->assigns); $i++) {
				$this->assigns[$i][$key] = $value;
			} //end for
		} else {
			$this->assigns[0][$key] = $value;
		} //end if else
	} //END FUNCTION


	/**
	 * Returns true if the given key will properly resolve
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasKey($key) {
		return (!\is_null($this->resolve($key)));
	} //END FUNCTION


	/**
	 * Resolve a key by either returning the appropriate literal or by looking up the appropriate variable
	 *
	 * Test for empty has been moved to interpret condition, in Decision
	 *
	 * @param string $key
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	private function resolve($key) {
		//--
	//	if(\is_array($key)) {
		if(!\TwistTPL\SmartTwist::is_nscalar($key)) {
			throw new \Exception('Cannot resolve non-scalar as key'); // This should not happen ...
			return null;
		} //end if
		//--
		if(\is_null($key) || ($key === 'null')) {
			return null;
		} //end if
		//--
		if(($key === true) OR ($key === 'true')) {
			return true;
		} //end if
		//--
		if(($key === false) OR ($key === 'false')) {
			return false;
		} //end if
		//--
		$key = (string) $key;
		//--
		if(\preg_match('/^\'(.*)\'$/', $key, $matches)) {
			return (string) ($matches[1] ?? null);
		} //end if
		//--
		if(\preg_match('/^"(.*)"$/', $key, $matches)) {
			return (string) ($matches[1] ?? null);
		} //end if
		//--
		if(\preg_match('/^(-?\d+)$/', $key, $matches)) {
			return (string) ($matches[1] ?? null);
		} //end if
		//--
		if(\preg_match('/^(-?\d[\d\.]+)$/', $key, $matches)) {
			return (string) ($matches[1] ?? null);
		} //end if
		//--
		return $this->variable($key); // mixed
		//--
	} //END FUNCTION


	/**
	 * Fetches the current key in all the scopes
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	private function fetch($key) {
		//-- TagDecrement depends on environments being checked before assigns
		foreach($this->environments as $environment) {
			if(\array_key_exists($key, $environment)) {
				return $environment[$key];
			} //end if
		} //end foreach
		//--
		foreach($this->assigns as $scope) {
			if(\array_key_exists($key, $scope)) {
				$obj = $scope[$key];
				return $obj;
			} //end if
		} //end foreach
		//--
		throw new \Exception(__METHOD__.' # TPL Variable was not set: `'.$key.'`');
		return null;
		//--
	} //END FUNCTION


	/**
	 * Resolved the namespaced queries gracefully.
	 *
	 * @param string $key
	 *
	 * @see Decision::stringValue
	 * @see AbstractBlock::renderAll
	 *
	 * @return mixed
	 */
	private function variable($key) {
		//-- Support numeric and variable array indexes
		if(\preg_match('|\[[0-9]+\]|', $key)) {
			$key = \preg_replace('|\[([0-9]+)\]|', '.$1', $key);
		} elseif(\preg_match('|\[[0-9a-z._]+\]|', $key, $matches)) {
			$index = $this->get(\str_replace([ '[', ']' ], '', $matches[0]));
			if(strlen($index)) {
			//	$key = \preg_replace('|\[([0-9a-z._]+)\]|', '.'.$index, $key); // UNSAFE ; use instead preg replace callback
				$key = (string) \preg_replace_callback('|\[([0-9a-z._]+)\]|', function($matches) use($index) {
					return (string) '.'.$index;
				}, (string)$key);
			} //end if
		} // end if else
		//--
		$parts = (array) \explode((string)\TwistTPL\Twist::get('VARIABLE_ATTRIBUTE_SEPARATOR'), (string)$key);
		$val = $this->fetch(\array_shift($parts));
		//--
		while(\count($parts) > 0) {
			//-- safety check #1
			if(!\TwistTPL\SmartTwist::is_array_or_nscalar($val)) {
				return null;
			} //end if
			//--
			if(\is_null($val)) {
				return null;
			} //end if
			//--
			$nextPartName = \array_shift($parts);
			//--
			if(\is_string($val)) {
				if($nextPartName === 'size') { // if the last part of the context variable is .size we return the string length
				//	return \mb_strlen($val);
					return \TwistTPL\SmartTwist::str_len((string)$val);
				} //end if
				return null; // no other special properties for strings, yet
			} //end if
			//--
			if(\is_array($val)) {
				//--
				if((int)\count($parts) == 0) {
					//--
					if(($nextPartName === 'size') && (!\array_key_exists('size', $val))) { 			// if the last part of the context variable is .size we just return the count
						return (int) \TwistTPL\SmartTwist::arr_size($val); // int
					} elseif(($nextPartName === 'first') && (!\array_key_exists('first', $val))) { 	// if the last part of the context variable is .first we return the first array element
						return \TwistTPL\SmartTwist::arr_first($val); // mixed
					} elseif(($nextPartName === 'last') && (!\array_key_exists('last', $val))) { 	// if the last part of the context variable is .last we return the last array element
						return \TwistTPL\SmartTwist::arr_last($val); // mixed
					} //end if
					//--
				} //end if
				//-- no key / no value
				if(((string)\trim((string)$nextPartName) == '') || (!\array_key_exists((string)$nextPartName, $val))) {
					return null;
				} //end if
				$val = $val[(string)$nextPartName];
				//--
				continue;
				//--
			} //end if
			//--
		} //end while
		//--
		if(!\TwistTPL\SmartTwist::is_array_or_nscalar($val)) {
			return null; // SAFETY CHECK !
		} //end if
		//--
		return $val; // mixed
		//--
	} //END FUNCTION


} //END CLASS

// #end
