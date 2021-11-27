<?php
namespace Dust\Evaluate;

use Dust\Ast;
class Bodies implements \ArrayAccess {

	//-- TODO (PHP 8.1 deprecations, need to be fixed in PHP 8.2+)
	// when the Smart.Framework min supported version will be 8.0, change the below method definitions, marked with #[\ReturnTypeWillChange] as above it
	//-- # unixman

	private $section;

	public $block;

	public function __construct(Ast\Section $section) {
		$this->section = $section;
		$this->block = $section->body;
	}

//	public function offsetExists(mixed $offset): bool { // PHP 8.0+
	#[\ReturnTypeWillChange]
	public function offsetExists($offset) {
		return $this[$offset] != null;
	}

//	public function offsetGet(mixed $offset): mixed { // PHP 8.0+
	#[\ReturnTypeWillChange]
	public function offsetGet($offset) {
		for ($i = 0; $i < count($this->section->bodies); $i++) {
			if ($this->section->bodies[$i]->key == $offset) {
				return $this->section->bodies[$i]->body;
			}
		}
		return null;
	}

//	public function offsetSet(mixed $offset, mixed $value): void { // PHP 8.0+
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value) {
		throw new EvaluateException($this->section, 'Unsupported set on bodies');
	}

//	public function offsetUnset(mixed $offset): void { // PHP 8.0+
	#[\ReturnTypeWillChange]
	public function offsetUnset($offset) {
		throw new EvaluateException($this->section, 'Unsupported unset on bodies');
	}

}

// #end of php code
