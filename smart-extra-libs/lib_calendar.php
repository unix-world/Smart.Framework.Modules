<?php
// [LIB - Smart.Framework / Plugins / Calendar]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Calendar (HTML Component)
// DEPENDS:
//	* Smart::
// REQUIRED CSS:
//	* calendar.css
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartCalendarComponent - Easy to use HTML Calendar Component (with / without Events).
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20221220
 * @package 	extralibs:ViewComponents
 *
 */
final class SmartCalendarComponent {

	// ::


	//================================================================
	public static function display_html_minicalendar($y_sel_date='', $y_width='150', $y_highlight_selected=true, $y_events_arr=array()) {
		//--
		return (string) self::display_calendar('small', (string)$y_sel_date, (string)$y_width, (bool)$y_highlight_selected, (array)$y_events_arr);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	public static function display_html_calendar($y_sel_date='', $y_width='100%', $y_highlight_selected=true, $y_events_arr=array()) {
		//--
		return (string) self::display_calendar('', (string)$y_sel_date, (string)$y_width, (bool)$y_highlight_selected, (array)$y_events_arr);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function display_calendar($y_mode, $y_sel_date, $y_width, $y_highlight_selected, $y_events_arr) {
		//--
		global $configs;
		//--
		if($configs['regional']['calendar-week-start'] == 1) {
			$the_first_day = 1; // Calendar Start on Monday
		} else {
			$the_first_day = 0; // Calendar Start on Sunday
		} //end if else
		//--
		if((string)$y_sel_date == '') {
			$y_sel_date = date('Y-m-d');
		} //end if
		//--
		$translator_core_calendar = SmartTextTranslations::getTranslator('@core', 'calendar');
		//--
		$calendar = new SmartHTMLCalendar(date('Y-m-d', @strtotime($y_sel_date)), $y_highlight_selected, $y_width, (string)$y_mode);
		//-- set months
		$calendar->setMonthNames(array(
			'01' => $translator_core_calendar->text('m_01'),
			'02' => $translator_core_calendar->text('m_02'),
			'03' => $translator_core_calendar->text('m_03'),
			'04' => $translator_core_calendar->text('m_04'),
			'05' => $translator_core_calendar->text('m_05'),
			'06' => $translator_core_calendar->text('m_06'),
			'07' => $translator_core_calendar->text('m_07'),
			'08' => $translator_core_calendar->text('m_08'),
			'09' => $translator_core_calendar->text('m_09'),
			'10' => $translator_core_calendar->text('m_10'),
			'11' => $translator_core_calendar->text('m_11'),
			'12' => $translator_core_calendar->text('m_12')
		));
		//-- set days
		if((string)$y_mode == 'small') { // short day names
			$calendar->setDayNames(array(
				0 => SmartUnicode::sub_str($translator_core_calendar->text('w_1'), 0, 2),
				1 => SmartUnicode::sub_str($translator_core_calendar->text('w_2'), 0, 2),
				2 => SmartUnicode::sub_str($translator_core_calendar->text('w_3'), 0, 2),
				3 => SmartUnicode::sub_str($translator_core_calendar->text('w_4'), 0, 2),
				4 => SmartUnicode::sub_str($translator_core_calendar->text('w_5'), 0, 2),
				5 => SmartUnicode::sub_str($translator_core_calendar->text('w_6'), 0, 2),
				6 => SmartUnicode::sub_str($translator_core_calendar->text('w_7'), 0, 2)
			));
		} else { // full day names
			$calendar->setDayNames(array(
				0 => $translator_core_calendar->text('w_1'),
				1 => $translator_core_calendar->text('w_2'),
				2 => $translator_core_calendar->text('w_3'),
				3 => $translator_core_calendar->text('w_4'),
				4 => $translator_core_calendar->text('w_5'),
				5 => $translator_core_calendar->text('w_6'),
				6 => $translator_core_calendar->text('w_7'),
			));
		} //end if else
		//-- set start on
		$calendar->setStartOfWeek($the_first_day);
		//--
		if(Smart::array_size($y_events_arr) > 0) {
			for($i=0; $i<count($y_events_arr); $i++) {
				if(($y_events_arr[$i]['date-end'] ?? null) === false) {
					$calendar->addDayEvent((string)$y_events_arr[$i]['event-html'], date('Y-m-d', @strtotime((string)$y_events_arr[$i]['date-start'])), false);
				} else {
					$calendar->addDayEvent((string)$y_events_arr[$i]['event-html'], date('Y-m-d', @strtotime((string)$y_events_arr[$i]['date-start'])), date('Y-m-d', @strtotime((string)($y_events_arr[$i]['date-end'] ?? null))));
				} //end if else
			} //end for
		} //end if
		//-- draw
		return '<div title="'.Smart::escape_html(date('Y-m', @strtotime((string)$y_sel_date))).'">'.$calendar->draw().'</div>';
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


// based on: SimpleCalendar by Jesse G. Donat under MIT License
// developed and extended by unix-world.org under BSD License

/**
 * Class: SmartHTMLCalendar - Generates a complex HTML Calendar that can handle events.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20221220
 * @package 	Plugins:ViewComponents
 *
 */
final class SmartHTMLCalendar {

	// ->


	//--
	private $extra_day_txt_arr = array(); // the day header by 1..28/29/30/31
	private $extra_info_txt_arr = array(); // the tooltip for events 0..n
	//--
	private $width = '100%';
	private $is_mobile = false;
	private $month_names = array(
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December'
	);
	private $wday_names = array(
		0 => 'Sunday',
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday'
	);
	private $highlight_selected = false;
	private $now = false;
	private $day_events_arr = array();
	private $offset = 0;
	private $css_class = 'SmartSmallCalendar';
	private $htmlCount = 0;
	//--


	/**
	 * Constructor - Calls the setDate function
	 */
	public function __construct($date_string, $highlight_selected=false, $width='', $css_class='') {
		//--
		$this->setDate($date_string);
		//--
		if($highlight_selected === true) {
			$this->highlight_selected = true;
		} else {
			$this->highlight_selected = false;
		} //end if else
		//--
		$this->width = (string) $width;
		//--
		switch(strtolower($css_class)) {
			case 'small':
				$this->css_class = 'SmartSmallCalendar';
				break;
			case '':
			default:
				$this->css_class = 'SmartCalendar';
		} //end switch
		//--
	} // END FUNCTION


	public function isMobile($is_mobile) {
		//--
		if($is_mobile === true) {
			$this->is_mobile = true;
		} else {
			$this->is_mobile = false;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Sets the date for the calendar
	 *
	 * @param null|string $date_string Date string parsed by strtotime for the calendar date. If null set to current timestamp.
	 */
	private function setDate($date_string) {
		//--
		if(strlen($date_string) > 0) {
			//--
			if(strlen($date_string) < 10) {
				Smart::log_warning(__CLASS__.'::'.__FUNCTION__.'() expect a date string as parameter !');
				return;
			} else {
				$this->now = getdate(strtotime($date_string));
			} //end if else
			//--
		} else {
			//--
			$this->now = getdate();
			//--
		} //end if else
		//--
	} // END FUNCTION


	public function setMonthNames($y_month_names_arr) {
		//--
		if(!is_array($y_month_names_arr)) {
			Smart::log_warning(__CLASS__.'::'.__FUNCTION__.'() expect an array as parameter !');
			return;
		} //end if
		//--
		if(Smart::array_size($y_month_names_arr) != 12) {
			Smart::log_warning(__CLASS__.'::'.__FUNCTION__.'() expect the size of array to be 12 !');
			return;
		} //end if
		//--
		$this->month_names = (array) $y_month_names_arr;
		//--
	} //END FUNCTION


	public function setDayNames($y_day_names_arr) {
		//--
		if(!is_array($y_day_names_arr)) {
			Smart::log_warning(__CLASS__.'::'.__FUNCTION__.'() expect an array as parameter !');
			return;
		} //end if
		//--
		if(Smart::array_size($y_day_names_arr) != 7) {
			Smart::log_warning(__CLASS__.'::'.__FUNCTION__.'() expect the size of array to be 7 !');
			return;
		} //end if
		//--
		$this->wday_names = (array) $y_day_names_arr;
		//--
	} //END FUNCTION


	/**
	 * Add a daily event to the calendar
	 *
	 * @param string      $html The raw HTML to place on the calendar for this event
	 * @param string      $start_date_string Date string for when the event starts
	 * @param bool|string $end_date_string Date string for when the event ends. Defaults to start date
	 * @return void
	 */
	public function addDayEvent($html, $start_date_string, $end_date_string=false) {
		//--
		$start_date = strtotime($start_date_string);
		//--
		if($end_date_string) {
			$end_date = strtotime($end_date_string);
		} else {
			$end_date = $start_date;
		} //end if else
		//--
		$working_date = $start_date;
		//--
		do {
			$tDate = getdate($working_date);
			$working_date += 86400;
			$this->day_events_arr[$tDate['year']][$tDate['mon']][$tDate['mday']][$this->htmlCount] = $html;
		} while( $working_date < $end_date + 1 );
		//--
		$this->htmlCount++;
		//--
	} // END FUNCTION


	/**
	 * Clear all daily events for the calendar
	 *
	 * @return void
	 */
	public function clearDayEvents() {
		//--
		$this->day_events_arr = array();
		//--
	} //END FUNCTION


	/**
	 * Sets the first day of Week
	 *
	 * @param int|string $offset Day to start on, ex: "Monday" or 0-6 where 0 is Sunday
	 */
	public function setStartOfWeek($offset) {
		//--
		if(is_int($offset)) {
			$this->offset = $offset % 7;
		} else {
			$this->offset = date('N', strtotime($offset)) % 7;
		} //end if else
		//--
	} // END FUNCTION


	/**
	 * Show the Calendars current date
	 *
	 * @param bool $echo Whether to echo resulting calendar
	 * @return string
	 */
	public function draw() {
		//--
		if($this->is_mobile) {
			$my_var_dayrows = 1;
		} else {
			$my_var_dayrows = 7;
		} //end if else
		//--
		$the_mktime = mktime(0, 0, 1, $this->now['mon'], 1, $this->now['year']);
		$wday = date('N', $the_mktime) - $this->offset;
		$no_days = date('t', $the_mktime);
		//--
		$out = '<table cellpadding="0" cellspacing="0" class="'.$this->css_class.'" width="'.$this->width.'"><thead>';
		//--
		$out .= '<tr><td colspan="'.$my_var_dayrows.'"><div class="SCalendarHeader">'.$this->drawMonthTitle().'</div></td></tr>'."\n";
		//--
		$out .= '<tr>';
		//--
		$wdays = (array) $this->getDayNames();
		reset($wdays);
		$this->arr_rotate($wdays, $this->offset);
		//--
		if(!$this->is_mobile) {
			//--
			for($i=0; $i<7; $i++) {
				$out .= '<th>'.Smart::escape_html($wdays[$i]).'</th>';
			} //end for
			//--
		} //end if
		//--
		$out .= '</tr>';
		//--
		$out .= '</thead>'."\n".'<tbody>'."\n";
		//--
		$out .= '<tr>';
		//--
		if($wday == $my_var_dayrows) {
			$wday = 0;
		} else {
			if(!$this->is_mobile) {
				$out .= str_repeat('<td class="SCalendarPrefix">&nbsp;</td>', $wday);
			} //end if
		} //end if else
		//--
		$count = $wday + 1;
		//--
		for($i=1; $i<=$no_days; $i++) {
			//--
			$datetime = mktime(0, 0, 1, $this->now['mon'], $i, $this->now['year']);
			$index_wday = date('w', $datetime);
			if($this->offset > 0) {
				$index_wday = (int) $index_wday - $this->offset;
				if($index_wday < 0) {
					$index_wday = 7 + $index_wday;
				} //end if
			} //end if
			if(!$this->is_mobile) {
				$dayname = '';
			} else {
				$dayname = (string) $wdays[$index_wday]; // display day name on mobile
			} //end if else
			//--
			if(($i == date('j')) && ($this->now['mon'] == date('n')) && ($this->now['year'] == date('Y'))) {
				$tmp_today = ' class="SCalendarToday" title="Today"'; // today
			} else {
				if(($this->highlight_selected === true) && ($i == $this->now['mday'])) {
					$tmp_today = ' class="SCalendarSelected" title="Selected Day"'; // selected day
				} else {
					$tmp_today = '';
				} //end if else
			} //end if else
			//--
			$out .= '<td'.$tmp_today.'><div class="SCalendarContent">';
			$out .= '<div id="'.'the_dayHeader_'.date('Y_m_d', $datetime).'" class="SCalendarDayHead">';
			$out .= '&nbsp;'.$i;
			if((string)$dayname != '') {
				$out .= '&nbsp;'.Smart::escape_html($dayname);
			} //end if
			if((string)($this->extra_day_txt_arr[$i] ?? null) != '') {
				$out .= '&nbsp;'.$this->extra_day_txt_arr[$i];
			} //end if
			$out .= '</div>';
			//--
			$dHtml_arr = false;
			if(isset( $this->day_events_arr[$this->now['year']][$this->now['mon']][$i] )) {
				$dHtml_arr = $this->day_events_arr[$this->now['year']][$this->now['mon']][$i];
			} //end if
			//--
			if(is_array($dHtml_arr)) {
				foreach($dHtml_arr as $eid => $dHtml) {
					$out .= '<div class="SCalendarEvent" id="SCalendarEvent" title="'.($this->extra_info_txt_arr[$eid] ?? null).'" >'.$dHtml.'</div>';
				} //end foreach
			} //end if
			//--
			$out .= '</td></td>';
			if($count > ($my_var_dayrows - 1)) {
				$out .= '</tr>'."\n".($i != $count ? '<tr>' : '');
				$count = 0;
			} //end if
			//--
			$count++;
			//--
		} //end for
		//--
		if(!$this->is_mobile) {
			//--
			$out .= ($count != 1 ? str_repeat('<td class="SCalendarSuffix">&nbsp;</td>', ($my_var_dayrows + 1) - $count) : '');
			//--
			$out .= '</tr>'."\n";
			//--
		} //end if
		//--
		$out .= '</tbody></table>'."\n";
		//--
		return $out;
		//--
	} // END FUNCTION


	private function drawMonthTitle() {
		//--
		$datetime = mktime(0, 0, 1, $this->now['mon'], 1, $this->now['year']);
		$month = date('m', $datetime);
		$year = date('Y', $datetime);
		//--
		return (string) Smart::escape_html($this->month_names[$month]).'&nbsp;&nbsp;'.Smart::escape_html($year);
		//--
	} //END FUNCTION


	private function getDayNames() {
		//--
		return (array) $this->wday_names;
		//--
	} //END FUNCTION


	private function arr_rotate(&$data, $steps) {
		//--
		$count = Smart::array_size($data);
		//--
		if($steps < 0) {
			$steps = $count + $steps;
		} //end if
		$steps = $steps % $count;
		//--
		for($i=0; $i<$steps; $i++) {
			array_push($data, array_shift($data));
		} //end for
		//--
	} // END FUNCTION


} // END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
