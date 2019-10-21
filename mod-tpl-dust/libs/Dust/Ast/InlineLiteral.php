<?php
namespace Dust\Ast;

class InlineLiteral extends InlinePart {
	public $value;

	public function __toString() {
		return $this->value;
	}

}

// #end of php code
