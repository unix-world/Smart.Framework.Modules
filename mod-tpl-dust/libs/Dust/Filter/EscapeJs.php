<?php
// created by unixman
namespace Dust\Filter;

class EscapeJs implements Filter {
	public function apply($item) {
		return (string) \Smart::escape_js((string)$item);
	}

}

// #end of php code
