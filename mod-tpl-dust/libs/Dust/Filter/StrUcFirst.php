<?php
// created by unixman
namespace Dust\Filter;

class StrUcFirst implements Filter {
	public function apply($item) {
		return (string) \SmartUnicode::uc_first((string)$item);
	}

}

// #end of php code
