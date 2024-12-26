<?php
// Module Lib: OpenMaps Proxy Cache
// Framework: Smart.Framework
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\MapsCache;


//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------



//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


/**
 * Class OpenMaps Proxy Cache
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20221222
 *
 */
class OpenMapsProxyCache {

	// ::


//=========================================================================
public static function getTilesFromCache($max_cache_zoom, $r, $x, $y, $z, $y_maps_cache_dir, $y_timeout=30, $y_custom_mapnik='') {

	//--
	$arr_response = [
		'status' 	=> 'ERROR', // OK
		'message' 	=> '?',
		'code' 		=> 500,
		'image' 	=> '',
		'imgtype' 	=> '',
		'imgext' 	=> '',
		'imgmodif' 	=> ''
	];
	//--

	//--
	$max_cache_zoom = (int) $max_cache_zoom;
	if($max_cache_zoom < 0) {
		$max_cache_zoom = 0;
	} //end if
	if($max_cache_zoom > 18) {
		$max_cache_zoom = 18;
	} //end if
	//--

	//--
	$r = (string) $r; // map provider
	//-- x
	$x = (int) $x;
	if($x < 0) {
		$x = 0;
	} //end if
	//-- y
	$y = (int) $y;
	if($y < 0) {
		$y = 0;
	} //end if
	//-- zoom
	$z = (int) $z;
	if($z < 0) {
		$z = 0;
	} //end if
	//--

	//--
	$y_timeout = (int) $y_timeout;
	if($y_timeout < 1) {
		$y_timeout = 1;
	} //end if
	if($y_timeout > 120) {
		$y_timeout = 120;
	} //end if
	//--

	//-- defaults
	$server = array();
	$mapdir = 'none/';
	$map_content_type = 'image/png';
	//-- control
	$valid_map_type = true;
	//--

	//--
	if($z > $max_cache_zoom) {
		$savecache = false; // avoid save in cache over this zoom level, ... huge cache can occur
	} else {
		$savecache = true; // default
	} //end if else
	//--

	//--
	switch((string)$r) {
		case 'custom-mapnik':
			//--
			$savecache = false; // disable caching for this one
			//--
			if((string)$y_custom_mapnik != '') {
				//--
				$mapdir = 'custom-mapnik/';
				//--
				$server[] = (string) $y_custom_mapnik; // domain.ext/mapnik-folder
				//--
				$url = 'http://'.$server[\array_rand($server)].'/'.$z.'/'.$x.'/'.$y.'.png';
				//--
			} else {
				//--
				$valid_map_type = false;
				//--
			} //end if else
			//--
			break;
		case 'mapnik':
			//--
			$mapdir = 'mapnik/';
			//--
			$server[] = 'a.tile.openstreetmap.org';
			$server[] = 'b.tile.openstreetmap.org';
			$server[] = 'c.tile.openstreetmap.org';
			//--
			$url = 'http://'.$server[\array_rand($server)].'/'.$z.'/'.$x.'/'.$y.'.png';
			//--
			break;
		case 'mapquest':
			//--
			$map_content_type = 'image/jpeg';
			$mapdir = 'mapquest/';
			//--
			$server[] = 'otile1.mqcdn.com';
			$server[] = 'otile2.mqcdn.com';
			$server[] = 'otile3.mqcdn.com';
			$server[] = 'otile4.mqcdn.com';
			//--
			$url = 'http://'.$server[\array_rand($server)].'/tiles/1.0.0/osm/'.$z.'/'.$x.'/'.$y.'.jpg';
			//--
			break;
		case 'mapquest-aerial':
			//--
			$map_content_type = 'image/jpeg';
			$mapdir = 'mapquest-aerial/';
			//--
			$server[] = 'oatile1.mqcdn.com';
			$server[] = 'oatile2.mqcdn.com';
			$server[] = 'oatile3.mqcdn.com';
			$server[] = 'oatile4.mqcdn.com';
			//--
			$url = 'http://'.$server[\array_rand($server)].'/tiles/1.0.0/sat/'.$z.'/'.$x.'/'.$y.'.jpg';
			//--
			break;
		case 'cyclemap':
			//--
			$mapdir = 'cyclemap/';
			//--
			$server[] = 'a.tile.opencyclemap.org';
			$server[] = 'b.tile.opencyclemap.org';
			$server[] = 'c.tile.opencyclemap.org';
			//--
			$url = 'http://'.$server[\array_rand($server)].'/cycle/'.$z.'/'.$x.'/'.$y.'.png';
			//--
			break;
		case 'cyclemap-transport':
			//--
			$mapdir = 'cyclemap-transport/';
			//--
			$server[] = 'a.tile2.opencyclemap.org';
			$server[] = 'b.tile2.opencyclemap.org';
			$server[] = 'c.tile2.opencyclemap.org';
			//--
			$url = 'http://'.$server[\array_rand($server)].'/transport/'.$z.'/'.$x.'/'.$y.'.png';
			//--
			break;
		default:
			//--
			$valid_map_type = false;
			//--
	} //end switch
	//--

	//--
	$saferand = \sha1((string)'R='.$r.'; X='.$x.'; Y='.$y.'; Z='.$z.'; M='.$max_cache_zoom.';');
	//--

	//--
	switch((string)$map_content_type) {
		case 'image/jpeg':
			$ext = '.jpg';
			break;
		case 'image/png':
		default:
			$ext = '.png';
	} //end if
	//--

	//--
	$dir = (string) $y_maps_cache_dir.$mapdir.$z.'/'.$x.'/';
	$file = (string) $dir.$y.$ext;
	if(\SmartFileSysUtils::checkIfSafePath((string)$file) !== 1) {
		$arr_response['status'] = 'ERROR';
		$arr_response['message'] = 'Invalid Map Tile: '.$y.$ext;
		$arr_response['code'] = 400;
		return (array) $arr_response;
	} //end if
	//--

	//--
	if(!$valid_map_type) {
		//--
		$arr_response['status'] = 'ERROR';
		$arr_response['message'] = 'Invalid Map Type: '.$r;
		$arr_response['code'] = 400;
		return (array) $arr_response;
		//--
	} //end if
	//--

	//--
	$cache_modified = 0;
	//--

	//--
	if($savecache) {
		\SmartFileSystem::dir_create((string)$dir, true); // recursive
	} else {
		if(!\SmartFileSystem::is_type_file((string)$y_maps_cache_dir.$mapdir.'no-cache.txt')) {
			\SmartFileSystem::dir_create((string)$y_maps_cache_dir.$mapdir, true); // recursive
			\SmartFileSystem::write((string)$y_maps_cache_dir.$mapdir.'no-cache.txt', date('Y-m-d H:i:s O'));
		} //end if
	} //end if
	//--

	//--
	$img = '';
	$msg = '';
	//--
	if(!\SmartFileSystem::is_type_file($file)) { // avoid to clear cache here, only check if file exists
		//--
		$httpclient = new \SmartHttpClient();
		$httpclient->connect_timeout = (int) $y_timeout;
		if(\SmartEnvironment::ifDebug()) {
			$httpclient->debug = 1;
		} //end if
		//--
		$bwdata = (array) $httpclient->browse_url($url, 'GET');
		//--
		$tmp_validate_by_header = \SmartDetectImages::guess_image_extension_by_url_head($bwdata['headers']);
		$tmp_validate_by_fbytes = \SmartDetectImages::guess_image_extension_by_img_content(\substr($bwdata['content'], 0, 256), false);
		//--
		$tmp_uniq_prefix = $file.'.tmp-'.$saferand;
		$tmp_uniq_file = $tmp_uniq_prefix.'.download'.$ext;
		$tmp_uniq_log = $tmp_uniq_prefix.'.debug-log.txt';
		//--
		if(\SmartEnvironment::ifDebug()) {
			\SmartFileSystem::write($tmp_uniq_log, '===== IsPngOrJpegByHeader: '.$tmp_validate_by_header."\n".'===== STATUS-CODE: '.$bwdata['code']."\n".'===== Header: '."\n".$bwdata['headers']."\n".'===== Debug-Log:'."\n".$bwdata['debuglog']."\n".'===== END #');
		} //end if
		//--
		if(((string)\trim($bwdata['code']) == '200') AND ((string)$bwdata['content'] != '')) {
			//--
			if(((string)$tmp_validate_by_header['extension'] == (string)$ext) AND ((string)$tmp_validate_by_fbytes == (string)$ext)) { // double validate: by header mime-type and also detect bytes
				//--
				if($savecache) {
					//--
					\SmartFileSystem::delete($tmp_uniq_file); // delete if a previous file exists with the same tempID
					\SmartFileSystem::write($tmp_uniq_file, $bwdata['content']);
					//--
					if(\SmartFileSystem::is_type_file($tmp_uniq_file)) { // if write successful
						//--
						\SmartFileSystem::delete($file); // delete original
						\SmartFileSystem::rename($tmp_uniq_file, $file); // replace
						\SmartFileSystem::delete($tmp_uniq_file); // cleanup if it i still there :-)
						//--
					} //end if
					//--
					if(\SmartFileSystem::is_type_file($file)) {
						$cache_modified = (int) \SmartFileSystem::get_file_mtime($file);
					} //end if
					//--
				} //end if
				//--
				$img = (string) $bwdata['content']; // serve browsed file
				$msg = 'load';
				//--
			} //end if
			//--
		} //end if
		//--
		$httpclient = null;
		//--
	} else {
		//--
		$img = (string) \SmartFileSystem::read($file); // read file from saved cache
		$msg = 'cache';
		$cache_modified = (int) \SmartFileSystem::get_file_mtime($file);
		//--
	} //end if
	//--

	//--
	if((string)$img == '') {
		//--
		$arr_response['status'] = 'ERROR';
		$arr_response['message'] = 'Map Tile Not Found: '.$y.$ext;
		$arr_response['code'] = 404;
		return (array) $arr_response;
		//--
	} //end if else
	//--

	//--
	$arr_response['status'] = 'OK';
	$arr_response['message'] = (string) $msg; // this is displayed public in headers
	$arr_response['code'] = 200;
	$arr_response['image'] = (string) $img; // image data
	$arr_response['imgtype'] = (string) $map_content_type; // image/png|jpeg
	$arr_response['imgext'] = (string) $ext;
	$arr_response['imgmodif'] = (int) $cache_modified;
	return (array) $arr_response;
	//--

} //END FUNCTION
//=========================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
