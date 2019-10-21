<?php
// created by unixman
namespace Dust\Filter;

class StrUcWords implements Filter {
	public function apply($item) {
		return (string) \SmartUnicode::uc_words((string)$item);
	}

}

// #end of php code
