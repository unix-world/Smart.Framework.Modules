<?php
// Module Lib: \SmartModExtLib\Cloud\IcalParser
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\Cloud;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

//namespace om;

/**
 * Project URL: https://github.com/OzzyCzech/icalparser
 *
 * Copyright (c) 2004-2015 Roman Ožana (http://www.omdesign.cz)
 * @author Roman Ožana <ozana@omdesign.cz>
 *
 * Modified and adapted by unixman (c) 2018-2023
 * @author unix-world.org
 *
 */

class IcalParser {

	// r.20230123

	/** @var \DateTimeZone */
	public $timezone;

	/** @var array */
	public $data;

	/** @var array */
	public $windows_timezones = [
		'Dateline Standard Time' => 'Etc/GMT+12',
		'UTC-11' => 'Etc/GMT+11',
		'Hawaiian Standard Time' => 'Pacific/Honolulu',
		'Alaskan Standard Time' => 'America/Anchorage',
		'Pacific Standard Time (Mexico)' => 'America/Santa_Isabel',
		'Pacific Standard Time' => 'America/Los_Angeles',
		'US Mountain Standard Time' => 'America/Phoenix',
		'Mountain Standard Time (Mexico)' => 'America/Chihuahua',
		'Mountain Standard Time' => 'America/Denver',
		'Central America Standard Time' => 'America/Guatemala',
		'Central Standard Time' => 'America/Chicago',
		'Central Standard Time (Mexico)' => 'America/Mexico_City',
		'Canada Central Standard Time' => 'America/Regina',
		'SA Pacific Standard Time' => 'America/Bogota',
		'Eastern Standard Time' => 'America/New_York',
		'US Eastern Standard Time' => 'America/Indianapolis',
		'Venezuela Standard Time' => 'America/Caracas',
		'Paraguay Standard Time' => 'America/Asuncion',
		'Atlantic Standard Time' => 'America/Halifax',
		'Central Brazilian Standard Time' => 'America/Cuiaba',
		'SA Western Standard Time' => 'America/La_Paz',
		'Pacific SA Standard Time' => 'America/Santiago',
		'Newfoundland Standard Time' => 'America/St_Johns',
		'E. South America Standard Time' => 'America/Sao_Paulo',
		'Argentina Standard Time' => 'America/Buenos_Aires',
		'SA Eastern Standard Time' => 'America/Cayenne',
		'Greenland Standard Time' => 'America/Godthab',
		'Montevideo Standard Time' => 'America/Montevideo',
		'Bahia Standard Time' => 'America/Bahia',
		'UTC-02' => 'Etc/GMT+2',
		'Azores Standard Time' => 'Atlantic/Azores',
		'Cape Verde Standard Time' => 'Atlantic/Cape_Verde',
		'Morocco Standard Time' => 'Africa/Casablanca',
		'UTC' => 'Etc/GMT',
		'GMT Standard Time' => 'Europe/London',
		'Greenwich Standard Time' => 'Atlantic/Reykjavik',
		'W. Europe Standard Time' => 'Europe/Berlin',
		'Central Europe Standard Time' => 'Europe/Budapest',
		'Romance Standard Time' => 'Europe/Paris',
		'Central European Standard Time' => 'Europe/Warsaw',
		'W. Central Africa Standard Time' => 'Africa/Lagos',
		'Namibia Standard Time' => 'Africa/Windhoek',
		'GTB Standard Time' => 'Europe/Bucharest',
		'Middle East Standard Time' => 'Asia/Beirut',
		'Egypt Standard Time' => 'Africa/Cairo',
		'Syria Standard Time' => 'Asia/Damascus',
		'South Africa Standard Time' => 'Africa/Johannesburg',
		'FLE Standard Time' => 'Europe/Kiev',
		'Turkey Standard Time' => 'Europe/Istanbul',
		'Israel Standard Time' => 'Asia/Jerusalem',
		'Libya Standard Time' => 'Africa/Tripoli',
		'Jordan Standard Time' => 'Asia/Amman',
		'Arabic Standard Time' => 'Asia/Baghdad',
		'Kaliningrad Standard Time' => 'Europe/Kaliningrad',
		'Arab Standard Time' => 'Asia/Riyadh',
		'E. Africa Standard Time' => 'Africa/Nairobi',
		'Iran Standard Time' => 'Asia/Tehran',
		'Arabian Standard Time' => 'Asia/Dubai',
		'Azerbaijan Standard Time' => 'Asia/Baku',
		'Russian Standard Time' => 'Europe/Moscow',
		'Mauritius Standard Time' => 'Indian/Mauritius',
		'Georgian Standard Time' => 'Asia/Tbilisi',
		'Caucasus Standard Time' => 'Asia/Yerevan',
		'Afghanistan Standard Time' => 'Asia/Kabul',
		'West Asia Standard Time' => 'Asia/Tashkent',
		'Pakistan Standard Time' => 'Asia/Karachi',
		'India Standard Time' => 'Asia/Calcutta',
		'Sri Lanka Standard Time' => 'Asia/Colombo',
		'Nepal Standard Time' => 'Asia/Katmandu',
		'Central Asia Standard Time' => 'Asia/Almaty',
		'Bangladesh Standard Time' => 'Asia/Dhaka',
		'Ekaterinburg Standard Time' => 'Asia/Yekaterinburg',
		'Myanmar Standard Time' => 'Asia/Rangoon',
		'SE Asia Standard Time' => 'Asia/Bangkok',
		'N. Central Asia Standard Time' => 'Asia/Novosibirsk',
		'China Standard Time' => 'Asia/Shanghai',
		'North Asia Standard Time' => 'Asia/Krasnoyarsk',
		'Singapore Standard Time' => 'Asia/Singapore',
		'W. Australia Standard Time' => 'Australia/Perth',
		'Taipei Standard Time' => 'Asia/Taipei',
		'Ulaanbaatar Standard Time' => 'Asia/Ulaanbaatar',
		'North Asia East Standard Time' => 'Asia/Irkutsk',
		'Tokyo Standard Time' => 'Asia/Tokyo',
		'Korea Standard Time' => 'Asia/Seoul',
		'Cen. Australia Standard Time' => 'Australia/Adelaide',
		'AUS Central Standard Time' => 'Australia/Darwin',
		'E. Australia Standard Time' => 'Australia/Brisbane',
		'AUS Eastern Standard Time' => 'Australia/Sydney',
		'West Pacific Standard Time' => 'Pacific/Port_Moresby',
		'Tasmania Standard Time' => 'Australia/Hobart',
		'Yakutsk Standard Time' => 'Asia/Yakutsk',
		'Central Pacific Standard Time' => 'Pacific/Guadalcanal',
		'Vladivostok Standard Time' => 'Asia/Vladivostok',
		'New Zealand Standard Time' => 'Pacific/Auckland',
		'UTC+12' => 'Etc/GMT-12',
		'Fiji Standard Time' => 'Pacific/Fiji',
		'Magadan Standard Time' => 'Asia/Magadan',
		'Tonga Standard Time' => 'Pacific/Tongatapu',
		'Samoa Standard Time' => 'Pacific/Apia',
	];

	private $arrayKeyMappings = [
		'ATTACH' => 'ATTACHMENTS',
		'EXDATE' => 'EXDATES',
		'RDATE' => 'RDATES',
	];


	/**
	 * @param string $string
	 * @param boolean $add if true the parsed string is added to existing data
	 * @return array|null
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function __construct($string, $add=false) {

		if($add !== true) {
			$this->data = []; // cleanup data
		} //end if

		$string = (string) $string;

		if(!\preg_match('/BEGIN:VCALENDAR/', $string)) {
		//	throw new \InvalidArgumentException('Invalid ICAL data format');
			return false;
		} //end if

		$counters = [];
		$section = 'VCALENDAR';

		// Replace \r\n with \n
		$string = \str_replace("\r\n", "\n", $string);

		// Unfold multi-line strings
		$string = \str_replace("\n ", '', $string);

		$arr_lines = (array) \explode("\n", (string)$string);

		foreach($arr_lines as $kk => $row) {

			switch ($row) {
				case 'BEGIN:DAYLIGHT':
				case 'BEGIN:VALARM':
				case 'BEGIN:VTIMEZONE':
				case 'BEGIN:VFREEBUSY':
				case 'BEGIN:VJOURNAL':
				case 'BEGIN:STANDARD':
				case 'BEGIN:VTODO':
				case 'BEGIN:VEVENT':
					$section = \substr($row, 6);
					$counters[$section] = isset($counters[$section]) ? $counters[$section] + 1 : 0;
					continue 2; // while
					break;
				case 'END:VEVENT':
					$section = \substr($row, 4);
					$currCounter = $counters[$section];
					$event = $this->data[$section][$currCounter];
					if (!empty($event['RRULE']) || !empty($event['RDATE'])) {
						$recurrences = $this->parseRecurrences($event);
						if (!empty($recurrences)) {
							$this->data[$section][$currCounter]['RECURRENCES'] = $recurrences;
						}
					}
					continue 2; // while
					break;
				case 'END:DAYLIGHT':
				case 'END:VALARM':
				case 'END:VTIMEZONE':
				case 'END:VFREEBUSY':
				case 'END:VJOURNAL':
				case 'END:STANDARD':
				case 'END:VTODO':
				case 'END:VCALENDAR':
					continue 2; // while
					break;
			}

		//	list($key, $middle, $value) = $this->parseRow($row);
			list($key, $middle, $value, $extra) = $this->parseRow($row); // fix by unixman

			if((string)$section === 'VCALENDAR') {
				$this->data[$key] = $value;
			} else {
				if (isset($this->arrayKeyMappings[$key])) {
					// use an array since there can be multiple entries for this key.  This does not
					// break the current implementation--it leaves the original key alone and adds
					// a new one specifically for the array of values.
					$arrayKey = $this->arrayKeyMappings[$key];
					$this->data[$section][$counters[$section]][$arrayKey][] = $value;
				}
				$this->data[$section][$counters[$section]][$key] = $value;
				//-- fix by unixman
				if($extra) {
					$this->data[$section][$counters[$section]]['@EXTRA'][(string)$extra] = true;
				}
				//-- # fix
			}

		}

		return true;

	} //END FUNCTION


	/**
	 * @param $row
	 * @return array
	 */
	private function parseRow($row) {

		\preg_match('#^([\w-]+);?(.*?):(.*)$#i', (string)$row, $matches);

		$key = false;
		$middle = null;
		$value = null;
		$extra = null; // fix by unixman

		if($matches) {

			$key = $matches[1];
			$middle = $matches[2];
			$value = $matches[3];
			$timezone = null;

			if((string)$key === 'X-WR-TIMEZONE' || (string)$key === 'TZID') {
				if(\preg_match('#(\w+/\w+)$#i', $value, $matches)) {
					$value = $matches[1];
				}
				if (isset($this->windows_timezones[$value])) {
					$value = $this->windows_timezones[$value];
				}
				$this->timezone = new \DateTimeZone($value);
			}

			// have some middle part ?
			if ($middle && \preg_match_all('#(?<key>[^=;]+)=(?<value>[^;]+)#', $middle, $matches, \PREG_SET_ORDER)) {
				$middle = [];
				foreach($matches as $kk => $match) {
					if ($match['key'] === 'TZID') {
						if (isset($this->windows_timezones[$match['value']])) {
							$match['value'] = $this->windows_timezones[$match['value']];
						}
						try {
							$middle[$match['key']] = $timezone = new \DateTimeZone($match['value']);
						} catch (\Exception $e) {
							$middle[$match['key']] = $match['value'];
						}
					} else if ($match['key'] === 'ENCODING') {
						if ($match['value'] === 'QUOTED-PRINTABLE') {
							$value = \quoted_printable_decode($value);
						}
					}
				}
			}
		}

		// process simple dates with timezone
		if (\in_array($key, ['DTSTAMP', 'LAST-MODIFIED', 'CREATED', 'DTSTART', 'DTEND'], true)) {
			try {
				$value = new \DateTime($value, ($timezone ?: $this->timezone));
			} catch (\Exception $e) {
				$value = null;
			}
			//-- fix: (unixman) for all-day events (if end at 00:00:00 then substract 1 second to fix this !!!
			if((string)$key == 'DTEND') {
				if($value) {
					if((string)$value->format('H:i:s') == '00:00:00') {
						$value = $value->modify('-1 second');
						$extra = 'all-day';
					}
				}
			} //end if
			//--# fix
		} else if (\in_array($key, ['EXDATE', 'RDATE'])) {
			$values = [];
			foreach(\explode(',', $value) as $kk => $singleValue) {
				try {
					$values[] = new \DateTime($singleValue, ($timezone ?: $this->timezone));
				} catch (\Exception $e) {
					// pass
				}
			}
			if (\count($values) === 1) {
				$value = $values[0];
			} else {
				$value = $values;
			}
		}

		if ($key === 'RRULE' && \preg_match_all('#(?<key>[^=;]+)=(?<value>[^;]+)#', $value, $matches, \PREG_SET_ORDER)) {
			$middle = null;
			$value = [];
			foreach($matches as $kk => $match) {
				if (\in_array($match['key'], ['UNTIL'])) {
					try {
						$value[$match['key']] = new \DateTime($match['value'], ($timezone ?: $this->timezone));
					} catch (\Exception $e) {
						$value[$match['key']] = $match['value'];
					}
				} else {
					$value[$match['key']] = $match['value'];
				}
			}
		}

		//split by comma, escape \,
		if ($key === 'CATEGORIES') {
			$value = \preg_split('/(?<![^\\\\]\\\\),/', $value);
		}

		//implement 4.3.11 Text ESCAPED-CHAR
		$text_properties = [
			'CALSCALE', 'METHOD', 'PRODID', 'VERSION', 'CATEGORIES', 'CLASS', 'COMMENT', 'DESCRIPTION'
			, 'LOCATION', 'RESOURCES', 'STATUS', 'SUMMARY', 'TRANSP', 'TZID', 'TZNAME', 'CONTACT', 'RELATED-TO', 'UID'
			, 'ACTION', 'REQUEST-STATUS'
		];
		if(\in_array($key, $text_properties) || \strpos($key, 'X-') === 0) {
			if(\is_array($value)) {
				foreach($value as $kk => $var) {
					$value[$kk] = \strtr($var, ['\\\\' => '\\', '\\N' => "\n", '\\n' => "\n", '\\;' => ';', '\\,' => ',']);
				}
			} else {
				$value = \strtr($value, ['\\\\' => '\\', '\\N' => "\n", '\\n' => "\n", '\\;' => ';', '\\,' => ',']);
			}
		}

	//	return [$key, $middle, $value];
		return [$key, $middle, $value, $extra]; // fix by unixman

	} //END FUNCTION


	/**
	 * @param $event
	 * @return array
	 * @throws \Exception
	 */
	public function parseRecurrences($event) {

		$recurring = new \SmartModExtLib\Cloud\IcalParseRecurrence($event['RRULE']);
		$exclusions = [];
		$additions = [];

		if (!empty($event['EXDATES'])) {
			foreach($event['EXDATES'] as $kk => $exDate) {
				if (\is_array($exDate)) {
					foreach($exDate as $kxk => $singleExDate) {
						$exclusions[] = $singleExDate->getTimestamp();
					}
				} else {
					$exclusions[] = $exDate->getTimestamp();
				}
			}
		}

		if (!empty($event['RDATES'])) {
			foreach ($event['RDATES'] as $kk => $rDate) {
				if (\is_array($rDate)) {
					foreach ($rDate as $kxk => $singleRDate) {
						$additions[] = $singleRDate->getTimestamp();
					}
				} else {
					$additions[] = $rDate->getTimestamp();
				}
			}
		}

		$until = $recurring->getUntil();
		if ($until === false) {
			//forever... limit to 3 years
			$end = clone($event['DTSTART']);
			$end->add(new \DateInterval('P3Y')); // + 3 years
			$recurring->setUntil($end);
			$until = $recurring->getUntil();
		}

		$original_tz = \date_default_timezone_get(); // mixed
		\date_default_timezone_set($event['DTSTART']->getTimezone()->getName());
		$frequency = new \SmartModExtLib\Cloud\IcalParseFreq($recurring->rrule, $event['DTSTART']->getTimestamp(), $exclusions, $additions);
		$recurrenceTimestamps = $frequency->getAllOccurrences();
		$recurrences = [];
		foreach($recurrenceTimestamps as $kk => $recurrenceTimestamp) {
			$tmp = new \DateTime('now', $event['DTSTART']->getTimezone());
			$tmp->setTimestamp($recurrenceTimestamp);
			$recurrences[] = $tmp;
		}
		if($original_tz) {
			\date_default_timezone_set($original_tz); // fix by unixman: restore original TZ
		} //end if

		return $recurrences;

	} //END FUNCTION


	/**
	 * @return array
	 */
	public function getEvents() {

		$events = [];

		if (isset($this->data['VEVENT'])) {
			foreach($this->data['VEVENT'] as $kk => $event) {
				if (empty($event['RECURRENCES'])) {
					$events[] = $event;
				} else {
					$recurrences = $event['RECURRENCES'];
					$event['RECURRING'] = true;
					$event['DTEND'] = !empty($event['DTEND']) ? $event['DTEND'] : $event['DTSTART'];
					$eventInterval = $event['DTSTART']->diff($event['DTEND']);

					$firstEvent = true;
					foreach($recurrences as $kxk => $recurDate) {
						$newEvent = $event;
						if (!$firstEvent) {
							unset($event['RECURRENCES']);
							$newEvent['DTSTART'] = $recurDate;
							$newEvent['DTEND'] = clone($recurDate);
							$newEvent['DTEND']->add($eventInterval);
						}

						$events[] = $newEvent;
						$firstEvent = false;
					}
				}
			}
		}

		return $events;

	} //END FUNCTION


	/**
	 * @return array
	 */
	public function getAlarms() {
		return isset($this->data['VALARM']) ? $this->data['VALARM'] : [];
	} //END FUNCTION


	/**
	 * @return array
	 */
	public function getTimezones() {
		return isset($this->data['VTIMEZONE']) ? $this->data['VTIMEZONE'] : [];
	} //END FUNCTION


	/**
	 * Return sorted event list as array
	 *
	 * @return array
	 */
	public function getSortedEvents() {
		if($events = $this->getEvents()) {
			\usort(
				$events, function ($a, $b) {
				return (int) ($a['DTSTART'] > $b['DTSTART']);
			}
			);
			return $events;
		}
		return [];
	} //END FUNCTION


	/**
	 * @return array
	 */
	public function getReverseSortedEvents() {
		if($events = $this->getEvents()) {
			\usort(
				$events, function ($a, $b) {
				return (int) ($a['DTSTART'] < $b['DTSTART']);
			}
			);
			return $events;
		}
		return [];
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
