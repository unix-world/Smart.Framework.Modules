<?php
// created by unixman
namespace Dust\Filter;

class FormatNl2Br implements Filter {
	public function apply($item) {
		return (string) \Smart::nl_2_br((string)$item);
	}

}

// #end of php code
