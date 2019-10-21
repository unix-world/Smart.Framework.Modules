<?php
// created by unixman
namespace Dust\Filter;

class ForceInteger implements Filter {
	public function apply($item) {
		return (string) (int) $item;
	}

}

// #end of php code
