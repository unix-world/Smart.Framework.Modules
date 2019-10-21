<?php
// created by unixman
namespace Dust\Filter;

class EscapeCss implements Filter {
	public function apply($item) {
		return (string) \Smart::escape_css((string)$item);
	}

}

// #end of php code
