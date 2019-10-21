<?php
// created by unixman
namespace Dust\Filter;

class ForceNumeric implements Filter {
	public function apply($item) {
		return (string) (float) $item;
	}

}

// #end of php code
