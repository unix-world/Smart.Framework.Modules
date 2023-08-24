<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Test/iCal
// Route: admin.php/page/cloud.test-ical
// (c) 2006-2021 unix-world.org - all rights reserved

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

		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if

		$this->PageViewSetCfg('rawpage', true);

		//die(date('Y-m-d', 9223372036854775807));
		$y = '2023';
		$m = '05';
		$d = '22';
		$sql = calendarEvents::build_sql_query(
			'read',
			(string) $y,
			(string) $m,
			(string) $d,
			'', // extra condition
			0, // limit
			0 // offset
		);

		$this->PageViewSetVar(
			'main',
			'<pre style="background:#778899; color:#FFFFFF; padding:10px;">'.Smart::escape_html((string)$sql).'</pre>'
		);

	} //END FUNCTION

} //END CLASS


//=======


final class calendarEvents {


	private const DB_TABLE_NAME = 'netofx_icalendar';


//==================================================================
private static function build_sql_occurences(string $y_type, string $y_date, string $y_overlaps_date) {

	//--
	switch($y_type) {
		case 'm': // occurence by months
			$sql_diff_calc = 'smart_date_period_diff(("date_st_y" || \'-\' || "date_st_m" || \'-\' || "date_st_d")::date, \''.$y_date.'\')';
			$sql_diff_type = 'months';
			$sql_where_rec = ' OR (("recc_mode" = \'m\') AND ((diff_m_date_mod % "recc_interval") = 0) AND ((recalc_date_m_st, recalc_date_m_end) OVERLAPS ('.$y_overlaps_date.')))';
		break;
		case 'd': // occurence by days
			$sql_diff_calc = '(FLOOR(smart_date_diff(("date_st_y" || \'-\' || "date_st_m" || \'-\' || "date_st_d")::date, \''.$y_date.'\') / "recc_interval"::bigint)::bigint * "recc_interval"::bigint)';
			$sql_diff_type = 'days';
			$sql_where_rec = ' OR (("recc_mode" = \'d\') AND ((diff_d_date_mod % "recc_interval") = 0) AND ((recalc_date_d_st, recalc_date_d_end) OVERLAPS ('.$y_overlaps_date.')))';
		break;
		default:
			die('ERROR: NetOffice iCalendar - Invalid Query Mode');
	} //END SWITCH
	//--
	$sql_recalc_st = '(("date_st_y" || \'-\' || "date_st_m" || \'-\' || "date_st_d")::date + ('.$sql_diff_calc.' || \' '.$sql_diff_type.'\')::interval)::timestamp';
	$sql_recalc_end = '(('.$sql_recalc_st.' + age(("date_end_y" || \'-\' || "date_end_m" || \'-\' || "date_end_d")::date, ("date_st_y" || \'-\' || "date_st_m" || \'-\' || "date_st_d")::date)::interval)::date || \' 23:59:59\')::timestamp';
	//--
	return array('diff' => $sql_diff_calc, 'start' => $sql_recalc_st, 'end' => $sql_recalc_end, 'where_rec' => $sql_where_rec);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function build_sql_query(string $y_mode, string $wy, string $wm, string $wd, string $extra_sql_condition='', int $limit=0, int $offset=0) : string {

//-- sample
//$wy = '2011';
//$wm = '06';
//$wd = '03';
//--

//--
$query_date = date('Y-m-d', @strtotime($wy.'-'.$wm.'-'.$wd));
$daysinmonth = date('t', @strtotime($query_date));
//--

//-- Month by WeekDay
$wx_1st_ww 	= date('d', strtotime('first '.date('l', @strtotime($wy.'-'.$wm.'-'.$wd)),  @strtotime($wy.'-'.$wm.'-'.'01')));
$wx_2nd_ww 	= ''.sprintf("%02s", ($wx_1st_ww + 7));
$wx_3rd_ww 	= ''.sprintf("%02s", ($wx_1st_ww + 14));
$wx_4th_ww 	= ''.sprintf("%02s", ($wx_1st_ww + 21));
//--
$wx_last_ww = ''.sprintf("%02s", ($wx_1st_ww + 28));
if($wx_last_ww > $daysinmonth) {
	$wx_last_ww = $wx_4th_ww;
} //end if

//--
$sql_overlaps_date = '\''.$query_date.' 00:00:00\'::timestamp, \''.$query_date.' 00:00:01\'::timestamp';
//--
$arr_sql_m = self::build_sql_occurences('m', $query_date, $sql_overlaps_date);
$sql_recalc_m_diff = $arr_sql_m['diff'];
$sql_recalc_m_st = $arr_sql_m['start'];
$sql_recalc_m_end = $arr_sql_m['end'];
$sql_where_m_rec = $arr_sql_m['where_rec'];
//--
$arr_sql_d = self::build_sql_occurences('d', $query_date, $sql_overlaps_date);
$sql_recalc_d_diff = $arr_sql_d['diff'];
$sql_recalc_d_st = $arr_sql_d['start'];
$sql_recalc_d_end = $arr_sql_d['end'];
$sql_where_d_rec = $arr_sql_d['where_rec'];
//--

//--
$where_condition = '(false)';
//--

//-- no rec
$where_condition .= ' OR (("recc_mode" = \'x\') AND ("recc_interval" >= 0) AND (((set_date_st || \' 00:00:00\')::timestamp, (set_date_end || \' 23:59:59\')::timestamp) OVERLAPS ('.$sql_overlaps_date.')))';
//-- rec by month
$where_condition .= ''.$sql_where_m_rec;
//-- rec by day
$where_condition .= ''.$sql_where_d_rec;
//--

//--
$limit = (int) $limit;
$offset = (int) $offset;
//--
if($limit < 0) {
	$limit = 0;
} //end if
if($offset < 0) {
	$offset = 0;
} //end if
//--
if($limit > 0) {
	$sql_limit = "LIMIT {$limit} OFFSET {$offset}";
} else {
	$sql_limit = '-- ALL (NO LIMIT/OFFSET)';
} //end if else
//--

//--
if((string)$y_mode == 'read') {
	//--
	$sql_fields = '*';
	$sql_orderby = 'ORDER BY set_date_st ASC, "time_st" ASC';
	//--
} elseif((string)$y_mode == 'count') {
	//--
	$sql_fields = 'COUNT(1)';
	$sql_orderby = '';
	//-- in count mode no need for limit
	$sql_limit = '-- COUNT';
	//--
} else {
	//--
	die('ERROR: NetOffice iCalendar - Invalid Get Events Mode');
	//--
} //end if else
//--


//--
$table = (string) self::DB_TABLE_NAME;
//--
// SAFETY: we need a unique table name to avoid colissions
// if the same session is used (for example with PgPool before PgSQL)
// new versions of PgPool know how to handle this, but is much safe to use like this
$tmp_tbl_unique_name = 'netofx__ical__tmp__'.sha1('iCalendarTable_'.date('ymd', @strtotime($query_date))).'_'.rand(100000,999999);
//--
$sql_occurences = <<<SQL
-- NetOfx iCalendar SQL: START [ {$tmp_tbl_unique_name} ]
SELECT {$sql_fields} FROM (
	SELECT
	"id",
	("date_st_y" || '-' || "date_st_m" || '-' || "date_st_d") AS set_date_st,
	("date_end_y" || '-' || "date_end_m" || '-' || "date_end_d") AS set_date_end,
	smart_date_diff(("date_st_y" || '-' || "date_st_m" || '-' || "date_st_d")::date, ("date_end_y" || '-' || "date_end_m" || '-' || "date_end_d")::date) AS set_duration,
	"recc_mode",
	"recc_interval",
	{$sql_recalc_m_st} AS recalc_date_m_st,
	{$sql_recalc_m_end} AS recalc_date_m_end,
	{$sql_recalc_m_diff} AS diff_m_date_mod,
	{$sql_recalc_d_st} AS recalc_date_d_st,
	{$sql_recalc_d_end} AS recalc_date_d_end,
	{$sql_recalc_d_diff} AS diff_d_date_mod,
	"time_st",
	"time_end",
	"subject",
	"location",
	"calendar",
	"resource",
	"category",
	"status",
	"recc_end",
	"rel_id"
	FROM "{$table}"
) AS {$tmp_tbl_unique_name}
	WHERE (
		((set_date_st <= '{$query_date}') AND (("recc_end" = '') OR ("recc_end" >= '{$query_date}')))
		AND
		(
			({$where_condition})
			-- EXTRA SQL CONDITION:
			{$extra_sql_condition}
		)
	)
	{$sql_orderby}
	{$sql_limit}
-- NetOfx iCalendar SQL: END
SQL;
//--

//--
return (string) $sql_occurences;
//--

} //END FUNCTION
//==================================================================


} //END CLASS


//=======


/* SQL


--
-- PostgreSQL database dump
--

-- Dumped from database version 13.10
-- Dumped by pg_dump version 13.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;


BEGIN;


CREATE TABLE public.netofx_icalendar (
    id character varying(15) NOT NULL,
    rel_id character varying(15) DEFAULT ''::character varying NOT NULL,
    type character varying(3) NOT NULL,
    date_st_y character varying(12) NOT NULL,
    date_st_m character varying(2) NOT NULL,
    date_st_d character varying(2) NOT NULL,
    date_st_w character varying(2) NOT NULL,
    time_st character varying(5) NOT NULL,
    date_end_y character varying(12) NOT NULL,
    date_end_m character varying(2) NOT NULL,
    date_end_d character varying(2) NOT NULL,
    date_end_w character varying(2) NOT NULL,
    time_end character varying(5) NOT NULL,
    tz_offset smallint NOT NULL,
    recc_mode character varying(2) NOT NULL,
    recc_interval smallint DEFAULT 1 NOT NULL,
    recc_end character varying(18) DEFAULT ''::character varying NOT NULL,
    busy_compl smallint DEFAULT 0 NOT NULL,
    priority smallint DEFAULT 3 NOT NULL,
    status smallint DEFAULT 1 NOT NULL,
    calendar character varying(96) DEFAULT ''::character varying NOT NULL,
    resource character varying(96) DEFAULT ''::character varying NOT NULL,
    location character varying(96) DEFAULT ''::character varying NOT NULL,
    category character varying(96) DEFAULT ''::character varying NOT NULL,
    subject character varying(128) NOT NULL,
    url_link character varying(255) DEFAULT ''::character varying NOT NULL,
    description text DEFAULT ''::text NOT NULL,
    log text DEFAULT ''::text,
    published bigint DEFAULT 0 NOT NULL,
    admin character varying(96) DEFAULT ''::character varying NOT NULL,
    modified character varying(28) DEFAULT ''::character varying NOT NULL,
    CONSTRAINT check__netofx_icalendar_busy_compl CHECK (((busy_compl = 0) OR (busy_compl = 1))),
    CONSTRAINT check__netofx_icalendar_date_end_d CHECK ((char_length((date_end_d)::text) = 2)),
    CONSTRAINT check__netofx_icalendar_date_end_m CHECK ((char_length((date_end_m)::text) = 2)),
    CONSTRAINT check__netofx_icalendar_date_end_w CHECK ((char_length((date_end_w)::text) = 2)),
    CONSTRAINT check__netofx_icalendar_date_end_y CHECK ((char_length((date_end_y)::text) >= 4)),
    CONSTRAINT check__netofx_icalendar_date_st_d CHECK ((char_length((date_st_d)::text) = 2)),
    CONSTRAINT check__netofx_icalendar_date_st_m CHECK ((char_length((date_st_m)::text) = 2)),
    CONSTRAINT check__netofx_icalendar_date_st_w CHECK ((char_length((date_st_w)::text) = 2)),
    CONSTRAINT check__netofx_icalendar_date_st_y CHECK ((char_length((date_st_y)::text) >= 4)),
    CONSTRAINT check__netofx_icalendar_id CHECK (((char_length((id)::text) = 10) OR (char_length((id)::text) = 12) OR (char_length((id)::text) = 13) OR (char_length((id)::text) = 15))),
    CONSTRAINT check__netofx_icalendar_priority CHECK (((priority = 1) OR (priority = 3) OR (priority = 5))),
    CONSTRAINT check__netofx_icalendar_published CHECK ((published >= 0)),
    CONSTRAINT check__netofx_icalendar_recc_end CHECK (((char_length((recc_end)::text) = 0) OR (char_length((recc_end)::text) >= 10))),
    CONSTRAINT check__netofx_icalendar_recc_interval CHECK ((recc_interval >= 1)),
    CONSTRAINT check__netofx_icalendar_recc_mode CHECK ((((recc_mode)::text = 'x'::text) OR ((recc_mode)::text = 'd'::text) OR ((recc_mode)::text = 'm'::text) OR ((recc_mode)::text = 'w1'::text) OR ((recc_mode)::text = 'w2'::text) OR ((recc_mode)::text = 'w3'::text) OR ((recc_mode)::text = 'w4'::text) OR ((recc_mode)::text = 'we'::text))),
    CONSTRAINT check__netofx_icalendar_rel_id CHECK (((char_length((rel_id)::text) = 0) OR (char_length((rel_id)::text) = 10) OR (char_length((rel_id)::text) = 12) OR (char_length((rel_id)::text) = 13) OR (char_length((rel_id)::text) = 15))),
    CONSTRAINT check__netofx_icalendar_status CHECK (((status = 1) OR (status = 0))),
    CONSTRAINT check__netofx_icalendar_time_end CHECK (((char_length((time_end)::text) = 0) OR (char_length((time_end)::text) = 5))),
    CONSTRAINT check__netofx_icalendar_time_st CHECK (((char_length((time_st)::text) = 0) OR (char_length((time_st)::text) = 5))),
    CONSTRAINT check__netofx_icalendar_type CHECK ((((type)::text = 'evt'::text) OR ((type)::text = 'tsk'::text) OR ((type)::text = 'nte'::text))),
    CONSTRAINT check__netofx_icalendar_tz_offset CHECK (((tz_offset <= 23) AND (tz_offset >= '-23'::integer)))
);

COMMENT ON TABLE public.netofx_icalendar IS 'NetOfx iCalendar v.2023.05.23';
COMMENT ON COLUMN public.netofx_icalendar.id IS 'ID as UUID: 10 ; 12 ; 13 ; 15';
COMMENT ON COLUMN public.netofx_icalendar.rel_id IS 'Related ID';
COMMENT ON COLUMN public.netofx_icalendar.type IS 'evt / tsk / nte';
COMMENT ON COLUMN public.netofx_icalendar.date_st_y IS 'Start Date: YYYY';
COMMENT ON COLUMN public.netofx_icalendar.date_st_m IS 'Start Month: MM';
COMMENT ON COLUMN public.netofx_icalendar.date_st_d IS 'Start Day: DD';
COMMENT ON COLUMN public.netofx_icalendar.date_st_w IS 'Start WeekDay: mo / tu / we / th / fr / sa / su';
COMMENT ON COLUMN public.netofx_icalendar.time_st IS 'Start Hours and Minutes: HH:ii  [UTC]';
COMMENT ON COLUMN public.netofx_icalendar.date_end_y IS 'End Year: YYYY';
COMMENT ON COLUMN public.netofx_icalendar.date_end_m IS 'End Month: MM';
COMMENT ON COLUMN public.netofx_icalendar.date_end_d IS 'End Day: DD';
COMMENT ON COLUMN public.netofx_icalendar.date_end_w IS 'End WeekDay: mo / tu / we / th / fr / sa / su';
COMMENT ON COLUMN public.netofx_icalendar.time_end IS 'End Hours and Minutes: HH:ii  [UTC]';
COMMENT ON COLUMN public.netofx_icalendar.tz_offset IS 'Timezone Offset -23 .. +23 vs.  [UTC]';
COMMENT ON COLUMN public.netofx_icalendar.recc_mode IS 'x / d / m / w1 / w2 / w3 / w4 / we';
COMMENT ON COLUMN public.netofx_icalendar.recc_interval IS 'Reccuring Interval: 1 .. 99999';
COMMENT ON COLUMN public.netofx_icalendar.recc_end IS 'Reccuring End Date: YYYY-MM-DD';
COMMENT ON COLUMN public.netofx_icalendar.busy_compl IS 'Show Busy or Completed: 0 / 1';
COMMENT ON COLUMN public.netofx_icalendar.priority IS 'Priority: 1 / 3 / 5';
COMMENT ON COLUMN public.netofx_icalendar.status IS 'Active Status: 0 / 1';
COMMENT ON COLUMN public.netofx_icalendar.calendar IS 'Calendar';
COMMENT ON COLUMN public.netofx_icalendar.resource IS 'Resource';
COMMENT ON COLUMN public.netofx_icalendar.location IS 'Location';
COMMENT ON COLUMN public.netofx_icalendar.category IS 'Category';
COMMENT ON COLUMN public.netofx_icalendar.subject IS 'Subject';
COMMENT ON COLUMN public.netofx_icalendar.url_link IS 'URL Link (if any)';
COMMENT ON COLUMN public.netofx_icalendar.description IS 'Description';
COMMENT ON COLUMN public.netofx_icalendar.log IS 'Log for Changes';
COMMENT ON COLUMN public.netofx_icalendar.published IS 'Published Time';
COMMENT ON COLUMN public.netofx_icalendar.admin IS 'Owner ID';
COMMENT ON COLUMN public.netofx_icalendar.modified IS 'Last Modified';

ALTER TABLE ONLY public.netofx_icalendar ADD CONSTRAINT calendar__id PRIMARY KEY (id);

CREATE INDEX netofx_icalendar__busy_compl ON public.netofx_icalendar USING btree (busy_compl);
CREATE INDEX netofx_icalendar__calendar ON public.netofx_icalendar USING btree (calendar);
CREATE INDEX netofx_icalendar__category ON public.netofx_icalendar USING btree (category);
CREATE INDEX netofx_icalendar__date_end_d ON public.netofx_icalendar USING btree (date_end_d);
CREATE INDEX netofx_icalendar__date_end_m ON public.netofx_icalendar USING btree (date_end_m);
CREATE INDEX netofx_icalendar__date_end_w ON public.netofx_icalendar USING btree (date_end_w);
CREATE INDEX netofx_icalendar__date_end_y ON public.netofx_icalendar USING btree (date_end_y);
CREATE INDEX netofx_icalendar__date_st_d ON public.netofx_icalendar USING btree (date_st_d);
CREATE INDEX netofx_icalendar__date_st_m ON public.netofx_icalendar USING btree (date_st_m);
CREATE INDEX netofx_icalendar__date_st_w ON public.netofx_icalendar USING btree (date_st_w);
CREATE INDEX netofx_icalendar__date_st_y ON public.netofx_icalendar USING btree (date_st_y);
CREATE INDEX netofx_icalendar__location ON public.netofx_icalendar USING btree (location);
CREATE INDEX netofx_icalendar__priority ON public.netofx_icalendar USING btree (priority);
CREATE INDEX netofx_icalendar__recc_end ON public.netofx_icalendar USING btree (recc_end);
CREATE INDEX netofx_icalendar__recc_interval ON public.netofx_icalendar USING btree (recc_interval);
CREATE INDEX netofx_icalendar__recc_mode ON public.netofx_icalendar USING btree (recc_mode);
CREATE INDEX netofx_icalendar__rel_id ON public.netofx_icalendar USING btree (rel_id);
CREATE INDEX netofx_icalendar__resource ON public.netofx_icalendar USING btree (resource);
CREATE INDEX netofx_icalendar__status ON public.netofx_icalendar USING btree (status);
CREATE INDEX netofx_icalendar__subject ON public.netofx_icalendar USING btree (subject);
CREATE INDEX netofx_icalendar__time_end ON public.netofx_icalendar USING btree (time_end);
CREATE INDEX netofx_icalendar__time_st ON public.netofx_icalendar USING btree (time_st);
CREATE INDEX netofx_icalendar__type ON public.netofx_icalendar USING btree (type);


COMMIT;


--
-- Data for Name: netofx_icalendar; Type: TABLE DATA; Schema: public; Owner: pgsql
--

BEGIN;

INSERT INTO public.netofx_icalendar VALUES ('0A3Y13I5K7', '', 'evt', '2011', '02', '03', 'mo', '08:00', '2011', '02', '06', 'th', '12:30', 0, 'm', 2, '', 1, 3, 1, '', 'admin', '', '', 'An event from 3rd to 6th February, repeating each 2 months', '', '', '', 0, 'admin', '');
INSERT INTO public.netofx_icalendar VALUES ('0A3Y13NEH912', '', 'evt', '2011', '04', '04', 'mo', '08:00', '2011', '05', '02', 'th', '12:30', 0, 'm', 2, '', 1, 3, 1, '', 'admin', '', '', 'An event from 4th April to 1st May, repeating each 2 months', '', '', '', 0, 'admin', '');
INSERT INTO public.netofx_icalendar VALUES ('0A7C17NUB6z13', '', 'evt', '2011', '06', '18', 'sa', '12:30', '2011', '06', '30', 'tu', '14:40', 0, 'x', 1, '', 0, 3, 1, '', 'admin', '', '', 'aaa', '', '', '', 0, '', '');
INSERT INTO public.netofx_icalendar VALUES ('0A7C1B4NCO', '', 'evt', '2009', '06', '02', 'fr', '09:00', '2009', '06', '23', 'fr', '08:00', 0, 'm', 24, '', 0, 3, 1, '', 'admin', '', '', 'A year repetitive event (each 2 years as each 24 months)', '', 'a description ...', '', 0, '', '');
INSERT INTO public.netofx_icalendar VALUES ('0A3Y14L7CW', '', 'evt', '2011', '05', '01', 'we', '08:00', '2011', '05', '08', 'su', '08:00', 0, 'd', 14, '', 0, 3, 1, '', 'admin', '', '', 'A repeting event by Day, each 14th day', '', '', '', 0, '', '');
INSERT INTO public.netofx_icalendar VALUES ('0A7C17O7DI', '', 'evt', '2012', '01', '06', 'fr', '01:00', '2012', '01', '06', 'fr', '02:44', 0, 'x', 1, '', 0, 3, 1, '', 'admin', '', '', 'bbb', '', '', '', 0, '', '');

INSERT INTO public.netofx_icalendar VALUES ('0A7C17O7DIzTx15', '', 'evt', '1998', '05', '22', 'fr', '00:00', '1998', '05', '22', 'fr', '23:59', 0, 'm', 12, '', 0, 3, 1, '', 'admin', '', '', 'every year = every 12 months', '', 'uxm ;-)', '', 0, '', '');

COMMIT;

--
-- PostgreSQL database dump complete
--

*/

//=======

// #end
