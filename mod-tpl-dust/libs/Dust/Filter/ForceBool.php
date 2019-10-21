<?php
// created by unixman
namespace Dust\Filter;

class ForceBool implements Filter {
	public function apply($item) {
		if($item) {
			$item = 'true';
		} else {
			$item = 'false';
		}
		return (string) $item;
	}

}

// #end of php code
