<?php

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

class OldSmartUtils {

	// ::
	// v.20191203


	//================================================================
	/**
	 * GET/SET a cache file on the file system ; Cached File will be saved in 'tmp/cache/{prefix}/.../'
	 * When SET will return the content back
	 *
	 * @param STRING 	$y_cache_file_extension		:: File Extension (example: '.ext')
	 * @param STRING 	$y_cache_prefix				:: prefix dir (at least 3 chars ; max 64 chars) ended by slash (Example: 'prefix')
	 * @param STRING 	$y_cache_unique_id			:: The Cache ID Unique Key (Ex: some.unique.key)
	 * @param STRING	$y_content					:: default is FALSE to GET the cached content if exists ; if this is a non-empty string will SET the content into the cache and will return it back
	 * @param INT 		$y_cache_expire				:: 0=never ; (>0)=seconds
	 * @param ENUM 		$y_encrypted				:: yes/no to encrypt the file content
	 * @return STRING								:: cached contents or empty string
	 */
	public static function load_cached_file_content($y_cache_file_extension, $y_cache_prefix, $y_cache_unique_id, $y_set_content=false, $y_cache_expire=0, $y_encrypted='no') {

		//--
		$y_cache_unique_id = (string) $y_cache_unique_id;
		//--
		if((string)$y_cache_unique_id == '') {
			Smart::log_warning('Utils // Load From Cache ... Empty URL ...');
			return '';
		} //end if
		//--
		$y_cache_file_extension = Smart::safe_validname($y_cache_file_extension);
		//--
		$y_cache_expire = Smart::format_number_int($y_cache_expire, '+');
		//--
		$y_cache_prefix = (string) Smart::safe_varname($y_cache_prefix);
		//--
		if((strlen($y_cache_prefix) >= 3) AND (strlen($y_cache_prefix) <= 64)) {
			//--
			$y_cache_prefix = SmartFileSysUtils::add_dir_last_slash($y_cache_prefix); // fix trailing slash
			//--
		} else {
			//--
			Smart::log_warning('Utils // Load From Cache ... Invalid Cache Prefix ...');
			$y_cache_prefix = 'default/';
			//--
		} //end if
		//--

		//--
		$unique_id = (string) SmartHashCrypto::sha1('@@::Smart.Framework::Content::Cache@@'.$y_cache_unique_id);
		//--
		$dir = 'tmp/cache/'.$y_cache_prefix.SmartFileSysUtils::prefixed_sha1_path($unique_id);
		SmartFileSysUtils::raise_error_if_unsafe_path($dir);
		//--
		$file = (string) $dir.$unique_id.$y_cache_file_extension;
		SmartFileSysUtils::raise_error_if_unsafe_path($file);
		//--
		if(!SmartFileSystem::is_type_dir($dir)) {
			SmartFileSystem::dir_create($dir, true); // recursive create
			if(!SmartFileSystem::is_type_dir($dir)) {
				Smart::log_warning('Utils // Load From Cache ... Cannot Create Directory Structure: '.$dir);
				return '';
			} //end if
		} // end if
		//--
		/* avoid this to avoid overload Inodes !! ('tmp/cache/' is already protected)
		$protect_file = $dir.'index.html';
		if(!SmartFileSystem::is_type_file($protect_file)) {
			SmartFileSystem::write($protect_file, '');
		} //end if
		*/
		//--

		//-- will go through this only if cache expired or no cache
		if((!SmartFileSystem::is_type_file($file)) OR ((SmartFileSystem::is_type_file($file)) AND ($y_cache_expire > 0) AND ((SmartFileSystem::get_file_mtime($file) + $y_cache_expire) < time()))) {
			//--
			//Smart::log_notice('MUST Resave to cache ... '.$y_cache_unique_id);
			//-- write to cache if not empty (set)
			if((string)$y_set_content != '') { // If Content Have been Set
				//--
				//Smart::log_notice('Resave to cache ... '.$y_cache_unique_id);
				//-- if required, apply encryption
				if((string)$y_encrypted == 'yes') {
					$y_set_content = (string) self::crypto_blowfish_encrypt($y_set_content);
				} //end if
				//--
				SmartFileSystem::write($file, $y_set_content); // save file to cache (safe write is controlled via locks)
				//--
			} else {
				//--
				$y_set_content = ''; // expired content
				//--
				// do not delete file in multi concurrency, simply rewrite ...
				//--
			} //end if
			//--
			$out = (string) $y_set_content;
			$y_set_content = ''; // free mem
			//--
		} else {
			//--
			$out = (string) SmartFileSystem::read($file); // ccahe valid, read from cache
			//Smart::log_notice(__METHOD__.'() read FS-Cached Content from: '.$file.' @ '.$out);
			//--
		} //end if
		//--
		if((string)$y_encrypted == 'yes') {
			if((string)$out != '') {
				$out = (string) self::crypto_blowfish_decrypt($out);
			} //end if
		} //end if
		//--

		//--
		return (string) $out;
		//--

	} //END FUNCTION
	//================================================================


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


	//================================================================
	/**
	 * Parse Simple Notes :: '-----< yyyy-mm-dd hh:ii:ss >----- some note\nsome other line'
	 *
	 * @param STRING $ynotes			:: The Text or HTML to be processed
	 * @param YES/NO $y_hide_times 		:: Show / Hide the time stamps
	 * @param #SIZE $y_tblsize			:: HTML Table Size
	 * @param #COLOR $ytxtcolor			:: HTML Table Color for Text
	 * @param #COLOR $ycolor			:: HTML Table Row Color
	 * @param #COLOR $ycolor_alt		:: HTML Table Row Alternate Color
	 * @param #COLOR $ybrdcolor			:: HTML Table Border Color
	 * @param #STYLE $y_style			:: HTML Extra Style
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return 	STRING					:: The HTML processed code
	 */
	public static function simple_notes($ynotes, $y_hide_times, $y_tblsize='100%', $ytxtcolor='#000000', $ycolor='#FFFFFF', $ycolor_alt='#FFFFFF', $ybrdcolor='#CCCCCC', $y_style=' style="overflow: auto; height:200px;"') {
		//--
		if(strpos((string)$ynotes, '-----<') === false) {
			return $tbl_start.'<tr><td bgcolor="'.$ycolor.'" valign="top"><font size="1">'.Smart::nl_2_br(Smart::escape_html($ynotes)).'</font></td></tr>'.$tbl_end ; // not compatible notes, so we not parse them
		} //end if
		//--
		$out = '';
		//--
		$tbl_start = '<table width="'.$y_tblsize.'" cellspacing="0" cellpadding="2" border="1" bordercolor="'.$ybrdcolor.'" style="border-style: solid; border-collapse: collapse;">'."\n";
		$tbl_end = '</table>';
		//--
		$tmp_shnotes_arr = (array) explode('-----<', (string)$ynotes);
		//--
		$i_alt=0;
		//--
		if(Smart::array_size($tmp_shnotes_arr) > 0) {
			//--
			$out .= '<!-- OVERFLOW START (S.NOTES) -->'.'<div title="#S.NOTES#"'.$y_style.'>'."\n";
			$out .= $tbl_start;
			//--
			for($i=0; $i<Smart::array_size($tmp_shnotes_arr); $i++) {
				//--
				$tmp_shnotes_arr[$i] = (string) trim((string)$tmp_shnotes_arr[$i]);
				//--
				if(Smart::striptags(str_replace('-----<', '', (string)$tmp_shnotes_arr[$i])) != '') {
					//--
					$tmp_expld = (array) explode('>-----', (string)$tmp_shnotes_arr[$i]);
					//--
					$tmp_meta_expl = (array) explode('|', (string)$tmp_expld[0]);
					$tmp_meta_date = trim((string)$tmp_meta_expl[0]);
					if(strlen(trim((string)$tmp_meta_expl[1])) > 0) {
						$tmp_metainfo = ' :: '.trim($tmp_meta_expl[1]);
					} else {
						$tmp_metainfo = '';
					} //end if else
					//--
					if(strlen(trim((string)$tmp_expld[1])) > 0) {
						//--
						$i_alt += 1;
						//-- alternate
						if($i_alt % 2) {
							$alt_color = $ycolor;
						} else {
							$alt_color = $ycolor_alt;
						} //end if else
						//--
						$out .= '<tr>'."\n";
						$out .= '<td bgcolor="'.$alt_color.'" valign="top">'."\n";
						//--
						if((string)$y_hide_times != 'yes') {
							$out .= '<div align="right" title="'.Smart::escape_html('#'.$i_alt.'.'.$tmp_metainfo).'"><font size="1" color="'.$ytxtcolor.'"><b>'.Smart::escape_html($tmp_meta_date).'</b></font></div><font size="1" color="'.$ytxtcolor.'">'.Smart::nl_2_br(Smart::escape_html(trim($tmp_expld[1]))).'</font>';
						} else {
							$out .= '<div title="'.Smart::escape_html('#'.$i_alt.'. '.$tmp_meta_date.$tmp_metainfo).'"><font size="1" color="'.$ytxtcolor.'">'.Smart::nl_2_br(Smart::escape_html(trim($tmp_expld[1]))).'</font></div>';
						} //end if else
						//--
						$out .= '</td>'."\n";
						$out .= '</tr>'."\n";
						//--
					} //end if
					//--
				} //end if
				//--
			} //end for
			//--
			$out .= $tbl_end;
			$out .= '</div>'.'<!-- OVERFLOW END (S.NOTES) -->'."\n";
			//--
		} //end if
		//--
		if($i_alt <= 0) {
			$out = '';
		} //end if
		//--
		return $out ;
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


// #end php file
