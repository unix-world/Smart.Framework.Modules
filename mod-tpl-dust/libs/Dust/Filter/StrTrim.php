<?php
// created by unixman
namespace Dust\Filter;

class StrTrim implements Filter {
	public function apply($item) {
		return (string) \trim((string)$item);
	}

}

// #end of php code
