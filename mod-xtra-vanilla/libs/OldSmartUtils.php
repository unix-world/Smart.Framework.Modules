<?php

class OldSmartUtils {

	// ::

	//================================================================
	// Used for log arrays
	public static function arr_log_last_entries($y_arr, $y_count) {
		//--
		if(!is_array($y_arr)) {
			return array(); // return an empty array
		} //end if
		//--
		$y_count = Smart::format_number_int($y_count);
		//--
		$y_arr = (array) $y_arr;
		//--
		$y_count = Smart::format_number_int($y_count);
		//--
		if($y_count < 2) {
			$y_count = 2; // do not allow values lower than 2
		} //end if
		//--
		$new_arr = array();
		//--
		$counter = 0;
		//--
		@arsort($y_arr);
		//--
		foreach($y_arr as $key => $val) {
			if($counter < $y_count) {
				$new_arr[$key] = $val;
				$counter++;
			} else {
				break;
			} //end if else
		} //end foreach
		//--
		return (array) $new_arr;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	// pre-pend a message to a log, keep max 65535 characters long
	public static function prepend_to_log($y_message, $y_log) {
		//--
		$y_message = trim(str_replace(array("\n", "\r"), array(' ', ' '), (string)$y_message));
		$y_log = trim(str_replace(array("\r\n", "\r"), array("\n", "\n"), (string)$y_log));
		//--
		if((string)$y_message != '') {
			//--
			if((string)$y_log != '') {
				$arr = (array) explode("\n", (string)$y_log);
			} else {
				$arr = array();
			} //end if else
			$y_log = ''; // reset
			$y_log .= $y_message."\n"; // prepend message
			//--
			for($i=0; $i<Smart::array_size($arr); $i++) {
				//--
				$tmp_line = trim($arr[$i]);
				if((string)$tmp_line != '') {
					$tmp_line .= "\n";
					if((strlen($y_log) + strlen($tmp_line)) <= 65535) { // size of text
						$y_log .= $tmp_line;
					} else {
						break; // log reached max length
					} //end if
				} //end if else
				//--
			} //end for
			//--
			$y_log = trim($y_log);
			//--
		} //end if
		//--
		return (string) $y_log;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================ Add leading zeros to a string
	public static function left_pad_str($y_string, $y_padnum, $y_padchar) {
		//--
		return str_pad($y_string, $y_padnum, $y_padchar, STR_PAD_LEFT);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	public static function calc_percent($number, $maxnumber) {
		//--
		$number = (float) $number;
		$maxnumber = (float) $maxnumber;
		//--
		if($maxnumber <= 0) {
			$out = 0 ;
		} else {
			$out = $number / $maxnumber * 100 ;
		} //end if else
		//--
		return Smart::format_number_dec($out, 2, '.', '') ;
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


// #end php file
