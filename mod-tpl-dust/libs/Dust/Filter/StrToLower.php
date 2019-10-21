<?php
// created by unixman
namespace Dust\Filter;

class StrToLower implements Filter {
	public function apply($item) {
		return (string) \SmartUnicode::str_tolower((string)$item);
	}

}

// #end of php code
