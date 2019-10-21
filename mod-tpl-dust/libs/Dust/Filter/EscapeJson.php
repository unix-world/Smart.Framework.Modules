<?php
// modified and fixed by unixman
namespace Dust\Filter;

class EscapeJson implements Filter {
	public function apply($item) {
		//return json_encode($item);
		$item = (string) \Smart::json_encode(
			\Smart::json_decode($item, true),
			false,
			true,
			true
		); // it MUST be JSON with HTML-Safe Options.
		$item = (string) \trim((string)$item);
		if((string)$item == '') {
			$item = 'null'; // ensure a minimal json as empty string if no expr !
		} //end if
		return (string) $item;
	}

}

// #end of php code
