<?php
// created by unixman
namespace Dust\Filter;

class ForceDecimal implements Filter {
	public function apply($item) {
		return (string) \Smart::format_number_dec((string)$item, 2, '.', '');
	}

}

// #end of php code
