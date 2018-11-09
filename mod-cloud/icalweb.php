<?php
// Controller: Cloud/iCalendarWeb
// Route: admin.php?/page/cloud.icalweb
// Author: unix-world.org
// v.180206

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
			$this->PageViewSetErrorStatus(403, 'ERROR: WebCalendar Invalid Auth ...');
			return;
		} //end if
		//--
		$safe_user_dir = (string) Smart::safe_username(SmartAuth::get_login_id());
		if(((string)$safe_user_dir == '') OR (SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$safe_user_dir) != '1')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebCalendar Unsafe User Dir ...');
			return;
		} //end if
		//--
		$safe_user_path = (string) 'wpub/dav/'.$safe_user_dir.'/caldav';
		if(SmartFileSysUtils::check_if_safe_path((string)$safe_user_path) != '1') {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebCalendar Unsafe User Path ...');
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$safe_user_path) !== true) {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebCalendar User Path does not exists ...');
			return;
		} //end if
		//--

		//--
		$ical_action = $this->RequestVarGet('action', '', 'string');
		$ical_calendar = $this->RequestVarGet('calendar', '', 'string');
		//--
		switch((string)$ical_action) {
			case 'ics':
				//--
				$out = $this->getCalendarIcsAsFile((string)$safe_user_path, (string)$safe_user_dir, (string)$ical_calendar); // mixed
				if($out === false) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid Calendar Name for: '.$safe_user_dir);
					return;
				} //end if
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				$arr_mime = SmartFileSysUtils::mime_eval('calendar-'.$safe_user_dir.'-'.$ical_calendar.'-'.time().'.ics', 'inline');
				//--
				$this->PageViewSetCfg('rawmime', (string)$arr_mime[0]);
				$this->PageViewSetCfg('rawdisp', (string)$arr_mime[1]);
				$this->PageViewSetRawHeaders([
					'Z-iCalendar-Mode' => 'Web-Calendar.ics',
					'Z-iCalendar-Name' => (string) $safe_user_dir
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
				$ics_events = (array) (new \SmartModExtLib\Cloud\IcalParser(
					(string) $this->getCalendarIcsAsFile((string)$safe_user_path, (string)$safe_user_dir, (string)$ical_calendar)
				))->getSortedEvents(); //->getSortedEvents();
				//--
				$events = [];
				for($i=0; $i<Smart::array_size($ics_events); $i++) {
					//--
					if(Smart::array_size($ics_events[$i]) > 0) {
						$events[] = [
							'id' 			=> (string) $ics_events[$i]['UID'],
							'name' 			=> (string) $ics_events[$i]['SUMMARY'],
							'startdate' 	=> (string) ($ics_events[$i]['DTSTART'] ? $ics_events[$i]['DTSTART']->format('Y-m-d') : ''), // ->format('Y-m-d H:i:s')
							'starttime' 	=> (string) ($ics_events[$i]['DTSTART'] ? $ics_events[$i]['DTSTART']->format('H:i') : ''),
							'enddate' 		=> (string) ($ics_events[$i]['DTEND'] ? $ics_events[$i]['DTEND']->format('Y-m-d') : ''), // ->format('Y-m-d H:i:s')
							'endtime' 		=> (string) ($ics_events[$i]['DTEND'] ? $ics_events[$i]['DTEND']->format('H:i') : ''),
							'color' 		=> (string) '#778899',
							'url' 			=> ''
						];
					} //end if
					//--
				} //end for
				//--
				$ics_events = null; // free mem
				//--
				$this->PageViewSetVars([
					'title' => 'WebCalendar / Calendar View',
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'icalweb-webcal.mtpl.inc.htm',
						[
							'USER-ACC' 		=> (string) $safe_user_dir,
							'USER-CAL' 		=> (string) $ical_calendar,
							'COUNT-EVENTS' 	=> (string) Smart::array_size($events),
							'JSON-EVTS' 	=> (string) Smart::json_encode($events)
						]
					)
				]);
				//--
				break;
			default:
				//--
				$ical_dir = (string) \SmartFileSysUtils::add_dir_last_slash((string)$safe_user_path).'calendars/'.$safe_user_dir;
				if(SmartFileSysUtils::check_if_safe_path((string)$ical_dir) != '1') {
					$this->PageViewSetErrorStatus(500, 'ERROR: Invalid Calendars Path Access for: '.$safe_user_dir);
					return;
				} //end if
				//--
				$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)$ical_dir, false, false, '.ics'); // non-recuring
				$files_n_dirs = (array) $files_n_dirs['list-dirs'];
				//--
				$base_link = (string) $this->ControllerGetParam('url-script').'?/page/'.Smart::escape_url($this->ControllerGetParam('controller'));
				//--
				$this->PageViewSetVars([
					'title' => 'WebCalendar',
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'icalweb-default.mtpl.inc.htm',
						[
							'USER-ACC' 		=> (string) $safe_user_dir,
							'LINK-ICS' 		=> (string) $base_link.'/action/ics/calendar/',
							'LINK-WEB' 		=> (string) $base_link.'/action/web/calendar/',
							'CALENDARS'		=> (array)  $files_n_dirs
						]
					)
				]);
				//--
		} //end switch
		//--

	} //END FUNCTION


	private function getCalendarIcsAsFile($safe_user_path, $safe_user_dir, $ical_calendar) {
		//--
		$ics_out = '';
		//--
		$ical_calendar = (string) trim((string)$ical_calendar);
		if((string)$ical_calendar == '') {
			return false;
		} //end if
		//--
		if((string)$ical_calendar != '') {
			$ical_calendar = (string) Smart::safe_filename((string)$ical_calendar);
		} //end if
		//--
		if((string)$ical_calendar == '') {
			return false;
		} //end if
		//--
		$ical_dir = (string) \SmartFileSysUtils::add_dir_last_slash((string)$safe_user_path).'calendars/'.$safe_user_dir.'/'.$ical_calendar;
		//--
		if(SmartFileSysUtils::check_if_safe_path((string)$ical_dir) != '1') {
			return false;
		} //end if
		//--
		if(!SmartFileSystem::is_type_dir((string)$ical_dir)) {
			return false;
		} //end if
		//--
		$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)$ical_dir, false, false, '.ics'); // non-recuring
		$files_n_dirs = (array) $files_n_dirs['list-files'];
		if(Smart::array_size($files_n_dirs) > 0) {
			for($i=0; $i<Smart::array_size($files_n_dirs); $i++) {
				if(((string)trim((string)$files_n_dirs[$i]) != '') AND ((string)substr((string)$files_n_dirs[$i], -4, 4) == '.ics')) {
					$ical_cfile = (string) \SmartFileSysUtils::add_dir_last_slash($ical_dir).$files_n_dirs[$i];
					if(SmartFileSysUtils::check_if_safe_path((string)$ical_cfile) == '1') {
						if(SmartFileSystem::is_type_file((string)$ical_cfile)) {
							$ical_cfdata = (string) SmartFileSystem::read((string)$ical_cfile);
							$ical_cfdata = (string) trim((string)$ical_cfdata);
							if((string)$ical_cfdata != '') {
								$ics_out .= (string) $ical_cfdata."\n";
							} //end if
							$ical_cfdata = '';
						} //end if
					} //end if
					$ical_cfile = '';
				} //end if
			} //end for
		} //end if
		//--
		//Smart::log_notice('iCal Method ICS / Calendar: '.$ical_dir.print_r($files_n_dirs,1));
		//--
		return (string) $ics_out;
		//--
	} //END FUNCTION


} //END CLASS

//end of php code
?>