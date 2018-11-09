<?php
// Module Lib: \SmartModExtLib\Cloud\IcalParseRecurrence # Private class for IcalParser

namespace SmartModExtLib\Cloud;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//namespace om;

/**
 * Project URL: https://github.com/OzzyCzech/icalparser
 * Class taken from https://github.com/coopTilleuls/intouch-iCalendar.git (Recurrence.php)
 *
 * A wrapper for recurrence rules in iCalendar.  Parses the given line and puts the
 * recurrence rules in the correct field of this object.
 *
 * See http://tools.ietf.org/html/rfc2445 for more information.  Page 39 and onward contains more
 * information on the recurrence rules themselves.  Page 116 and onward contains
 * some great examples which were often used for testing.
 *
 * @author Steven Oxley
 * @author Michael Kahn (C) 2013
 * @license http://creativecommons.org/licenses/by-sa/2.5/dk/deed.en_GB CC-BY-SA-DK
 */
final class IcalParseRecurrence {

	// r.180207

	public $rrule;
	private $freq;
	private $until;
	private $count;
	private $interval;
	private $bysecond;
	private $byminute;
	private $byhour;
	private $byday;
	private $bymonthday;
	private $byyearday;
	private $byweekno;
	private $bymonth;
	private $bysetpos;
	private $wkst;

	/**
	 * A list of the properties that can have comma-separated lists for values.
	 *
	 * @var array
	 */
	private $listProperties = [
		'bysecond', 'byminute', 'byhour', 'byday', 'bymonthday',
		'byyearday', 'byweekno', 'bymonth', 'bysetpos'
	];


	/**
	 * Creates an recurrence object with a passed in line.  Parses the line.
	 *
	 * @param array $rrule an om\icalparser row array which will be parsed to get the
	 * desired information.
	 */
	public function __construct($rrule) {
		$this->parseRrule($rrule);
	} //END FUNCTION


	/**
	 * Parses an 'RRULE' array and sets the member variables of this object.
	 * Expects a string that looks like this:  'FREQ=WEEKLY;INTERVAL=2;BYDAY=SU,TU,WE'
	 *
	 * @param $rrule
	 */
	private function parseRrule($rrule) {
		$this->rrule = $rrule;
		//loop through the properties in the line and set their associated
		//member variables
		foreach ($this->rrule as $propertyName => $propertyValue) {
			//need the lower-case name for setting the member variable
			$propertyName = strtolower($propertyName);
			//split up the list of values into an array (if it's a list)
			if (in_array($propertyName, $this->listProperties, true)) {
				$propertyValue = explode(',', $propertyValue);
			}
			$this->$propertyName = $propertyValue;
		}
	} //END FUNCTION


	/**
	 * Set the $until member
	 *
	 * @param mixed timestamp (int) / Valid DateTime format (string)
	 */
	public function setUntil($ts) {
		if ($ts instanceof \DateTime) {
			$dt = $ts;
		} else if (is_int($ts)) {
			$dt = new \DateTime('@' . $ts);
		} else {
			$dt = new \DateTime($ts);
		}
		$this->until = $dt->format('Ymd\THisO');
		$this->rrule['until'] = $this->until;
	} //END FUNCTION


	/**
	 * Retrieves the desired member variable and returns it (if it's set)
	 *
	 * @param  string $member name of the member variable
	 * @return mixed  the variable value (if set), false otherwise
	 */
	private function getMember($member) {
		if (isset($this->$member)) {
			return $this->$member;
		}
		return false;
	} //END FUNCTION


	/**
	 * Returns the frequency - corresponds to FREQ in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getFreq() {
		return $this->getMember('freq');
	} //END FUNCTION


	/**
	 * Returns when the event will go until - corresponds to UNTIL in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getUntil() {
		return $this->getMember('until');
	} //END FUNCTION


	/**
	 * Returns the count of the times the event will occur (should only appear if 'until'
	 * does not appear) - corresponds to COUNT in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getCount() {
		return $this->getMember('count');
	} //END FUNCTION


	/**
	 * Returns the interval - corresponds to INTERVAL in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getInterval() {
		return $this->getMember('interval');
	} //END FUNCTION


	/**
	 * Returns the bysecond part of the event - corresponds to BYSECOND in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getBySecond() {
		return $this->getMember('bysecond');
	} //END FUNCTION


	/**
	 * Returns the byminute information for the event - corresponds to BYMINUTE in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByMinute() {
		return $this->getMember('byminute');
	} //END FUNCTION


	/**
	 * Corresponds to BYHOUR in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByHour() {
		return $this->getMember('byhour');
	} //END FUNCTION


	/**
	 *Corresponds to BYDAY in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByDay() {
		return $this->getMember('byday');
	} //END FUNCTION


	/**
	 * Corresponds to BYMONTHDAY in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByMonthDay() {
		return $this->getMember('bymonthday');
	} //END FUNCTION


	/**
	 * Corresponds to BYYEARDAY in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByYearDay() {
		return $this->getMember('byyearday');
	} //END FUNCTION


	/**
	 * Corresponds to BYWEEKNO in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByWeekNo() {
		return $this->getMember('byweekno');
	} //END FUNCTION


	/**
	 * Corresponds to BYMONTH in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getByMonth() {
		return $this->getMember('bymonth');
	} //END FUNCTION


	/**
	 * Corresponds to BYSETPOS in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getBySetPos() {
		return $this->getMember('bysetpos');
	} //END FUNCTION


	/**
	 * Corresponds to WKST in RFC 2445.
	 *
	 * @return mixed string if the member has been set, false otherwise
	 */
	public function getWkst() {
		return $this->getMember('wkst');
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>