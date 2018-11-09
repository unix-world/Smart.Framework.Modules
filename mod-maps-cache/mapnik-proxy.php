<?php
// Mapnik Cache Proxy: (index|admin).php?page=maps-cache.mapnik-proxy{&x=&y=&z=&r=}
// Author: unix-world.org
// r.181109

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

//=======================================================
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//--
		$this->PageViewSetCfg('rawpage', 'yes');
		//$this->PageViewSetCfg('expires', 120); // cache expire ; or may use heads
		//--

		//--
		$page = (string) $this->RequestVarGet('page', '', 'string');
		//--
		$x = (string) $this->RequestVarGet('x', 0, 'integer+');
		$y = (string) $this->RequestVarGet('y', 0, 'integer+');
		$z = (string) $this->RequestVarGet('z', 0, 'integer+');
		$r = (string) $this->RequestVarGet('r', '', 'string');
		//--

		//==
		$cfg_cache_get_timeout = (int) $this->ConfigParamGet('maps.proxy-cache.timeout');
		if(($cfg_cache_get_timeout <= 0) OR ($cfg_cache_get_timeout > 120)) {
			$cfg_cache_get_timeout = 15;
		} //end if
		$cfg_max_cache_zoom = (int) $this->ConfigParamGet('maps.proxy-cache.max-cache-zoom');
		//==
		$max_cache_zoom = 18; // generally, avoid cache over level 15 ... it can be a big space saving
		if(($cfg_max_cache_zoom > 0) AND ($cfg_max_cache_zoom <= 18)) {
			$max_cache_zoom = $cfg_max_cache_zoom;
		} //end if
		//==
		$cfg_custom_mapnik = (string) $this->ConfigParamGet('maps.proxy-cache.custom-mapnik');
		if((string)$cfg_custom_mapnik != '') {
			$r = (string) $cfg_custom_mapnik; // 'custom-mapnik' :: force mapnik (caching cost a lot of space, so we just do this trick ...)
		} //end if
		//==
		$cfg_custom_iprange_start = (string) $this->ConfigParamGet('maps.proxy-cache.ip-range-start');
		$cfg_custom_iprange_end = (string) $this->ConfigParamGet('maps.proxy-cache.ip-range-end');
		//==

		//-- check IP (referer must come from javascript and must be the same as domain)
		if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
			if((string)$_SERVER['HTTP_REFERER'] == '') {
				Smart::log_warning('Empty Referer in Mapnik Proxy (101)');
				$this->PageViewSetVar('main', SmartComponents::http_message_403_forbidden('Invalid Proxy Referer (101)'));
				return;
			} //end if
			//--
			$parsed_referer = (array) Smart::url_parse($_SERVER['HTTP_REFERER']);
			if((string)$parsed_referer['host'] == '') {
				Smart::log_warning('Empty Referer in Mapnik Proxy / Parsed Host (102)');
				$this->PageViewSetVar('main', SmartComponents::http_message_403_forbidden('Invalid Proxy Referer (102)'));
				return;
			} //end if
			//--
			$parsed_domain = (string) gethostbyname($parsed_referer['host']);
			if((string)$parsed_domain == '') {
				Smart::log_warning('Empty Referer in Mapnik Proxy / Parsed HostByName (103)');
				$this->PageViewSetVar('main', SmartComponents::http_message_403_forbidden('Invalid Proxy Referer (103)'));
				return;
			} //end if
			if(\SmartUtils::check_ip_in_range('127.0.0.1', '127.0.0.255', (string)$parsed_domain) != 1) {
				Smart::log_warning('Invalid Proxy Access // Client IP Not Allowed (104): '.$parsed_domain);
				$this->PageViewSetVar('main', SmartComponents::http_message_403_forbidden('Invalid Proxy Referer (104)'));
				return;
			} //end if
			if(((string)$cfg_custom_iprange_start != '') AND ((string)$cfg_custom_iprange_end != '')) {
				if(\SmartUtils::check_ip_in_range((string)$cfg_custom_iprange_start, (string)$cfg_custom_iprange_end, (string)$parsed_domain) != 1) {
					Smart::log_warning('Invalid Proxy Access // Client IP Not Allowed (105): '.$parsed_domain);
					$this->PageViewSetVar('main', SmartComponents::http_message_403_forbidden('Invalid Proxy Referer (105)'));
					return;
				} //end if
			} //end if
		} //end if
		//--

		//--
		if((string)$page != '') {
			$this->PageViewSetVar('main', \SmartModExtLib\MapsCache\OpenMapsProxyCache::getTilesFromCache($max_cache_zoom, $r, $x, $y, $z, 'tmp/cache-maps/', $cfg_cache_get_timeout, $cfg_custom_mapnik));
		} else {
			trigger_error('Invalid Proxy Access Mode - Mapnik (105)'."\n", E_USER_NOTICE);
			$this->PageViewSetVar('main', 'Invalid Proxy Access Mode - Mapnik (105)');
		} //end if else
		//--

	} //END FUNCTION

} //END CLASS

class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the IndexAppModule to run exactly the same action in admin.php

} //END CLASS

//end of php code
?>