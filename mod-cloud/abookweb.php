<?php
// Controller: Cloud/AddressbookWeb
// Route: admin.php?/page/cloud.abookweb
// Author: unix-world.org
// v.180220

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin only
define('SMART_APP_MODULE_AUTH', true); // requires auth always

/**
 * Admin Controller
 */
class SmartAppAdminController extends SmartAbstractAppController {


	public function Run() {

		//--
		if(SmartAuth::check_login() !== true) {
			$this->PageViewSetErrorStatus(403, 'ERROR: WebAddressbook Invalid Auth ...');
			return;
		} //end if
		//--
		$safe_user_dir = (string) Smart::safe_username(SmartAuth::get_login_id());
		if(((string)$safe_user_dir == '') OR (SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$safe_user_dir) != '1')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebAddressbook Unsafe User Dir ...');
			return;
		} //end if
		//--
		$safe_user_path = (string) 'wpub/dav/'.$safe_user_dir.'/carddav';
		if(SmartFileSysUtils::check_if_safe_path((string)$safe_user_path) != '1') {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebAddressbook Unsafe User Path ...');
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$safe_user_path) !== true) {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebAddressbook User Path does not exists ...');
			return;
		} //end if
		//--

		//--
		$abook_action = $this->RequestVarGet('action', '', 'string');
		$abook_addressbook = $this->RequestVarGet('addressbook', '', 'string');
		//--
		switch((string)$abook_action) {
			case 'vcf':
				//--
				$out = $this->getAddressbookVcfAsFile((string)$safe_user_path, (string)$safe_user_dir, (string)$abook_addressbook); // mixed
				if($out === false) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid Addressbook Name for: '.$safe_user_dir);
					return;
				} //end if
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				$arr_mime = SmartFileSysUtils::mime_eval('addressbook-'.$safe_user_dir.'-'.$abook_addressbook.'-'.time().'.vcf', 'inline');
				//--
				$this->PageViewSetCfg('rawmime', (string)$arr_mime[0]);
				$this->PageViewSetCfg('rawdisp', (string)$arr_mime[1]);
				$this->PageViewSetRawHeaders([
					'Z-Addressbook-Mode' => 'Web-Addressbook.vcf',
					'Z-Addressbook-Name' => (string) $safe_user_dir
				]);
				//--
				$this->PageViewSetVar(
					'main',
					(string) $out
				);
				//--
				return 200;
				//--
				break;
			case 'web':
				//--
				$vcf_cards = (array) (new \SmartModExtLib\Cloud\vCardParser(
					(string) $this->getAddressbookVcfAsFile((string)$safe_user_path, (string)$safe_user_dir, (string)$abook_addressbook)
				))->getParsedData();
				//--
				$cards = [];
				for($i=0; $i<Smart::array_size($vcf_cards); $i++) {
					//--
					if(Smart::array_size($vcf_cards[$i]) > 0) {
						$cards[] = [
							'id' 			=> (string) $this->getVcfFieldData($vcf_cards[$i]['uid']),
							'name' 			=> (string) $this->getVcfFieldData($vcf_cards[$i]['fn']),
							'names' 		=> (string) $this->getVcfFieldData($vcf_cards[$i]['n']),
							'organization' 	=> (string) $this->getVcfFieldData($vcf_cards[$i]['org']),
							'phone' 		=> (string) $this->getVcfFieldData($vcf_cards[$i]['tel']),
							'email' 		=> (string) $this->getVcfFieldData($vcf_cards[$i]['email']),
							'address' 		=> (string) $this->getVcfFieldData($vcf_cards[$i]['adr'])
						];
					} //end if
					//--
				} //end for
				//--
				if(Smart::array_size($cards) > 0) {
					usort($cards, function($a, $b) {
						return strcmp($a['name'], $b['name']);
						//return $a['name'] - $b['name'];
					});
				} //end if
				//--
				$vcf_cards = null; // free mem
				//--
				$tpl_one = (string) SmartFileSystem::read((string)$this->ControllerGetParam('module-view-path').'abookweb-webcards-one.mtpl.inc.htm');
				$this->PageViewSetVars([
					'title' => 'WebAddressbook / Addressbook View',
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'abookweb-webcards.mtpl.inc.htm',
						[
							'USER-ACC' 		=> (string) $safe_user_dir,
							'USER-ABK' 		=> (string) $abook_addressbook,
							'COUNT-CARDS' 	=> (string) Smart::array_size($cards),
							'JSON-CARDS' 	=> (string) Smart::json_encode($cards),
							'CARD-TPL' 		=> (string) SmartMarkersTemplating::escape_template((string)$tpl_one)
						]
					)
				]);
				//--
				break;
			default:
				//--
				$abook_dir = (string) \SmartFileSysUtils::add_dir_last_slash((string)$safe_user_path).'addressbooks/'.$safe_user_dir;
				if(SmartFileSysUtils::check_if_safe_path((string)$abook_dir) != '1') {
					$this->PageViewSetErrorStatus(500, 'ERROR: Invalid Addressbooks Path Access for: '.$safe_user_dir);
					return;
				} //end if
				//--
				$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)$abook_dir, false, false, '.vcf'); // non-recuring
				$files_n_dirs = (array) $files_n_dirs['list-dirs'];
				//--
				$base_link = (string) $this->ControllerGetParam('url-script').'?/page/'.Smart::escape_url($this->ControllerGetParam('controller'));
				//--
				$this->PageViewSetVars([
					'title' => 'WebAddressbook',
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'abookweb-default.mtpl.inc.htm',
						[
							'USER-ACC' 		=> (string) $safe_user_dir,
							'LINK-VCF' 		=> (string) $base_link.'/action/vcf/addressbook/',
							'LINK-WEB' 		=> (string) $base_link.'/action/web/addressbook/',
							'ADDRESSBOOKS'	=> (array)  $files_n_dirs
						]
					)
				]);
				//--
		} //end switch
		//--

	} //END FUNCTION


	private function getVcfFieldData($data, $impl="\n", $pfx='•') {
		//--
		$out = '';
		//--
		if($pfx) {
			$pfx .= (string) json_decode('"\\u00A0"'); // nbsp;
		} //end if
		//--
		if(is_array($data)) {
			if(Smart::array_size($data) > 0) {
				$out = [];
				foreach($data as $key => $val) {
					if(is_array($val)) {
						$val = (string) $this->getVcfFieldData($val, ' › ', '');
					} //end if
					if((string)trim((string)$val) != '') {
						if(is_int($key)) {
							$out[] = (string) $pfx.$val;
						} else {
							switch((string)strtolower((string)$key)) {
								case 'value':
								case 'type':
								case 'name':
									$out[] = (string) $val;
									break;
								default:
									$out[] = (string) $key.': '.$val;
							} //end switch
						} //end if else
					} //end if
				} //end foreach
				$out = (string) implode((string)$impl, (array)$out);
			} else {
				$out = ''; // empty array
			} //end if else
		} else {
			$out = (string) $data;
		} //end if
		//--
		return (string) trim((string)$out);
		//--
	} //END FUNCTION


	private function getAddressbookVcfAsFile($safe_user_path, $safe_user_dir, $abook_addressbook) {
		//--
		$vcf_out = '';
		//--
		$abook_addressbook = (string) trim((string)$abook_addressbook);
		if((string)$abook_addressbook == '') {
			return false;
		} //end if
		//--
		if((string)$abook_addressbook != '') {
			$abook_addressbook = (string) Smart::safe_filename((string)$abook_addressbook);
		} //end if
		//--
		if((string)$abook_addressbook == '') {
			return false;
		} //end if
		//--
		$abook_dir = (string) \SmartFileSysUtils::add_dir_last_slash((string)$safe_user_path).'addressbooks/'.$safe_user_dir.'/'.$abook_addressbook;
		//--
		if(SmartFileSysUtils::check_if_safe_path((string)$abook_dir) != '1') {
			return false;
		} //end if
		//--
		if(!SmartFileSystem::is_type_dir((string)$abook_dir)) {
			return false;
		} //end if
		//--
		$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)$abook_dir, false, false, '.vcf'); // non-recuring
		$files_n_dirs = (array) $files_n_dirs['list-files'];
		if(Smart::array_size($files_n_dirs) > 0) {
			for($i=0; $i<Smart::array_size($files_n_dirs); $i++) {
				if(((string)trim((string)$files_n_dirs[$i]) != '') AND ((string)substr((string)$files_n_dirs[$i], -4, 4) == '.vcf')) {
					$abook_cfile = (string) \SmartFileSysUtils::add_dir_last_slash($abook_dir).$files_n_dirs[$i];
					if(SmartFileSysUtils::check_if_safe_path((string)$abook_cfile) == '1') {
						if(SmartFileSystem::is_type_file((string)$abook_cfile)) {
							$abook_cfdata = (string) SmartFileSystem::read((string)$abook_cfile);
							$abook_cfdata = (string) trim((string)$abook_cfdata);
							if((string)$abook_cfdata != '') {
							//	if(strpos((string)$abook_cfdata, "\n".'UUID:') === false) {
							//		$abook_cfdata = str_replace("\n".'END:VCARD', "\n".'UUID:'.trim((string)SmartFileSysUtils::get_file_name_from_path((string)$abook_cfile))."\n".'END:VCARD';
							//	} //end if
								$vcf_out .= (string) $abook_cfdata."\n";
							} //end if
							$abook_cfdata = '';
						} //end if
					} //end if
					$abook_cfile = '';
				} //end if
			} //end for
		} //end if
		//--
		//Smart::log_notice('Abook Method VCF / Addressbook: '.$abook_dir.print_r($files_n_dirs,1));
		//--
		return (string) $vcf_out;
		//--
	} //END FUNCTION


} //END CLASS

//end of php code
?>