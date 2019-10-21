<?php
// created by unixman
namespace Dust\Filter;

class EscapeUrl implements Filter {
	public function apply($item) {
		return (string) \Smart::escape_url((string)$item);
	}

}

// #end of php code
