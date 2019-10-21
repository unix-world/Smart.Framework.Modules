<?php
// created by unixman
namespace Dust\Filter;

class EscapeHtml implements Filter {
	public function apply($item) {
		return (string) \Smart::escape_html((string)$item);
	}

}

// #end of php code
