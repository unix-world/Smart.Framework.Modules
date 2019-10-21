<?php
// created by unixman
namespace Dust\Filter;

class FormatJsVar implements Filter {
	public function apply($item) {
		return (string) \trim((string)\preg_replace('/[^a-zA-Z0-9_]/', '', (string)$item));
	}

}

// #end of php code
