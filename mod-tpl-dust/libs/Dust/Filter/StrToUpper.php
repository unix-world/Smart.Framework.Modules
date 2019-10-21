<?php
// created by unixman
namespace Dust\Filter;

class StrToUpper implements Filter {
	public function apply($item) {
		return (string) \SmartUnicode::str_toupper((string)$item);
	}

}

// #end of php code
