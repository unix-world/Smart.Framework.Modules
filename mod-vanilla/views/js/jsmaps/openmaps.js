
// [@[#[!JS-Compress!]#]@]
// OpenMaps // JS Lib # v.2017.04.21
// (c) 2012-2017 unix-world.org
// License: aGPLv3

// DEPENDS: OpenLayers

//==
function Smart_OpenMaps(y_proxycachebuf, y_proxycacheurl, y_proxymaptype) { // OBJECT-CLASS

// -> dynamic (new)

var _this = this; // self referencing

var version_openmaps = '20151120';
var author = '(c) 2012-2015 unix-world.org - all rights reserved';
var typelicense = 'aGPLv3';
var license = 'SmartMaps // Version: ' + version_openmaps + ' / Author: ' + author + ' / License (' + typelicense + '): Opensource. This software can be used for free by respecting the license terms.';
var copyright = '<span style="padding: 2px; font-size:12px; color: #1D0092; background-color: #FFFFFF; cursor: help;" title="' + license + '">Map data CC-By-SA: OpenStreetMap // API by: &copy; unix-world.org</span>';

//--
var rendermode = '[?]';
var localproxyurl = ''; // the local proxycache url (open maps support local caching)
var buflevel = 0; // when loading extra surrounding tiles can be buffered
if((typeof y_proxycachebuf != 'undefined') && (y_proxycachebuf !== undefined) && (y_proxycachebuf !== null) && (y_proxycachebuf != '')) {
	buflevel = parseInt(y_proxycachebuf);
	if(isNaN(buflevel)) {
		buflevel = 0;
	} //end if
} //end if
var localproxyurl = ''; // the local proxycache url (open maps support local caching)
if((typeof y_proxycacheurl != 'undefined') && (y_proxycacheurl !== undefined) && (y_proxycacheurl !== null)) {
	localproxyurl = '' + y_proxycacheurl;
} //end if
var localproxytype = ''; // the local proxy type )only support one type ... disk space can be a problem with more than one !!)
if((typeof y_proxymaptype != 'undefined') && (typeof y_proxymaptype != 'undefined') && (y_proxymaptype !== undefined) && (y_proxymaptype !== null)) {
	localproxytype = '' + y_proxymaptype;
} //end if
//--

var divmap = ''; // the div to bind to

var map; // map object
var pmap; // map projection
var markers; // markers layer

var crrlat, crrlon;
var crrzoom;
var crrMousePos = { x: 0, y: 0 };

var use_icon_offset = true; // by default marker centers but we want have it above/centered

var icon_marker;
var icon_w_marker;
var icon_h_marker;
var icon_newmarker;
var icon_w_newmarker;
var icon_h_newmarker;

//-- some internals
var debug = 0; // debugging
var uniquemarkerids = 1000; // this is a unique ID, so will never reset, except ClearMarkers
var proj4326 = new OpenLayers.Projection("EPSG:4326"); // The standard latitude/longitude projection string is EPSG:4326
var export_arr_markers = new Array(); // export markers array
//--

//-- by default the edit is restricted
var use_formdata = 0;
var opmode = '';
var allow_edit_num_markers = 0; // if this var is > 0 will allow placing as many markers as this number
//--

//--
var BingApiKey = ''; // this is required to use with BingMaps which are commercial not open
//--

//--
// Open Maps that can be cached have a reference at: http://wiki.openstreetmap.org/wiki/Slippy_map_tilenames
//--
OpenLayers.Layer.OSM.MapnikLocalProxy = OpenLayers.Class(OpenLayers.Layer.OSM, {
	initialize: function(name, options) {
		var url;
		var mode;
		if((localproxytype == 'mapnik') && (localproxyurl !== '')) { // local proxy / cache
			mode = '[P]';
			url = [localproxyurl + "&z=${z}&x=${x}&y=${y}&r=mapnik"];
		} else { // direct
			mode = '[D]';
			url = [ // here apparently we can serve both http:// and https://
				"//a.tile.openstreetmap.org/${z}/${x}/${y}.png",
				"//b.tile.openstreetmap.org/${z}/${x}/${y}.png",
				"//c.tile.openstreetmap.org/${z}/${x}/${y}.png"
			];
		} //end if else
		options = OpenLayers.Util.extend({
			singleTile: false,
			numZoomLevels: 19,
			buffer: buflevel, // when loading extra surrounding tiles can be buffered
			attribution: copyright
		}, options);
		var newArguments = [name, url, options];
		OpenLayers.Layer.OSM.prototype.initialize.apply(this, newArguments);
	},
	CLASS_NAME: "OpenLayers.Layer.OSM.MapnikLocalProxy"
});
//--
OpenLayers.Layer.OSM.CycleMapLocalProxy = OpenLayers.Class(OpenLayers.Layer.OSM, {
	initialize: function(name, options) {
		var url;
		var mode;
		if((localproxytype == 'opencyclemap') && (localproxyurl !== '')) { // local proxy / cache
			mode = '[P]';
			url = [localproxyurl + "&z=${z}&x=${x}&y=${y}&r=cyclemap"];
		} else { // direct
			mode = '[D]';
			url = [
				"http://a.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png",
				"http://b.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png",
				"http://c.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png"
			];
		} //end if else
		options = OpenLayers.Util.extend({
			singleTile: false,
			numZoomLevels: 19,
			buffer: buflevel, // when loading extra surrounding tiles can be buffered
			attribution: copyright
		}, options);
		var newArguments = [name, url, options];
		OpenLayers.Layer.OSM.prototype.initialize.apply(this, newArguments);
	},
	CLASS_NAME: "OpenLayers.Layer.OSM.CycleMapLocalProxy"
});
//--
OpenLayers.Layer.OSM.CycleMapTransportLocalProxy = OpenLayers.Class(OpenLayers.Layer.OSM, {
	initialize: function(name, options) {
		var url;
		var mode;
		if((localproxytype == 'opencyclemap-transport') && (localproxyurl !== '')) { // local proxy / cache
			mode = '[P]';
			url = [localproxyurl + "&z=${z}&x=${x}&y=${y}&r=cyclemap-transport"];
		} else { // direct
			mode = '[D]';
			url = [
				"http://a.tile2.opencyclemap.org/transport/${z}/${x}/${y}.png",
				"http://b.tile2.opencyclemap.org/transport/${z}/${x}/${y}.png",
				"http://c.tile2.opencyclemap.org/transport/${z}/${x}/${y}.png"
			];
		} //end if else
		options = OpenLayers.Util.extend({
			singleTile: false,
			numZoomLevels: 19,
			buffer: buflevel, // when loading extra surrounding tiles can be buffered
			attribution: copyright
		}, options);
		var newArguments = [name, url, options];
		OpenLayers.Layer.OSM.prototype.initialize.apply(this, newArguments);
	},
	CLASS_NAME: "OpenLayers.Layer.OSM.CycleMapTransportLocalProxy"
});
//-- Bing Maps ApiKey
this.BingSetApiKey = function(y_apikey) {
	BingApiKey = '' + y_apikey;
} //END FUNCTION
//--

//--
var hitMap = function(y_init, y_markers, y_lat, y_lon, y_zoom) { // y_init: init / update
	//--
	var redrawhandler = '[???]';
	//--
	redrawhandler = _this.Smart_Handler_OpenMaps_LoadData(y_init, y_markers, y_lat, y_lon, y_zoom);
	//--
	if(debug) {
		setDivContent(divmap + "_crrmap", '<b>CURRENT MAP DEBUG ' + redrawhandler + ' (' + y_init + '):</b> ' + 'Lat=' + y_lat + ' ; Lon=' + y_lon + ' ; Zoom=' + y_zoom);
	} //end if
	//--
} //END FUNCTION
//--
var getDivContent = function(y_the_div) {
	//--
	var content;
	//--
	content = OpenLayers.Util.getElement('' + y_the_div).innerHTML;
	//--
	return content;
	//--
} //END FUNCTION
//--
var setDivContent = function(y_the_div, y_content) {
	//--
	OpenLayers.Util.getElement('' + y_the_div).innerHTML = '' + y_content;
	//--
} //END FUNCTION
//--

//--
var getFormData = function() {
	//--
	return getDivContent(divmap + '_data');
	//--
} //END FUNCTION
//--
var setFormData = function() {
	//--
	out = '';
	//--
	if(use_formdata == 1) {
		//--
		//alert('Using Set Form Data ...');
		//--
		for(var i=0; i<export_arr_markers.length; i++) {
			out += '<input type="hidden" name="' + divmap + '_data[' + getMarkerMetaInfo(export_arr_markers[i], 'id') + '][lat]" value="' + getMarkerMetaInfo(export_arr_markers[i], 'lat') +'">' + "\n";
			out += '<input type="hidden" name="' + divmap + '_data[' + getMarkerMetaInfo(export_arr_markers[i], 'id') + '][lon]" value="' + getMarkerMetaInfo(export_arr_markers[i], 'lon') +'">' + "\n";
			out += '<input type="hidden" name="' + divmap + '_data[' + getMarkerMetaInfo(export_arr_markers[i], 'id') + '][ttl]" value="' + getMarkerMetaInfo(export_arr_markers[i], 'ttl') +'">' + "\n";
			out += '<input type="hidden" name="' + divmap + '_data[' + getMarkerMetaInfo(export_arr_markers[i], 'id') + '][lnk]" value="' + getMarkerMetaInfo(export_arr_markers[i], 'lnk') +'">' + "\n";
		} //end for
		//--
		setDivContent(divmap + '_data', out);
		//--
	} //end if
	//--
} //END FUNCTION
//--
var getMarkerMetaInfo = function(y_new_marker, y_field) { // sync with registerMarker() for export_arr_markers[i] fields
	//--
	var the_metainfo = '';
	//--
	if(y_new_marker instanceof Array) {
		//--
		switch(y_field) {
			case 'id':
				the_metainfo = y_new_marker[0]; // ID
				break;
			case 'lat':
				the_metainfo = y_new_marker[1]; // Latitude
				break;
			case 'lon':
				the_metainfo = y_new_marker[2]; // Longitude
				break;
			case 'ttl':
				the_metainfo = y_new_marker[3]; // Title
				break;
			case 'lnk':
				the_metainfo = y_new_marker[4]; // LinkURL
				break;
			case 'icf':
				the_metainfo = y_new_marker[5]; // Icon File
				break;
			case 'icw':
				the_metainfo = y_new_marker[6]; // Icon Width
				break;
			case 'ich':
				the_metainfo = y_new_marker[7]; // Icon Height
				break;
			default:
				alert('ERROR: Undefined Field for getMarkerMetaInfo() !');
		} //end switch
		//--
	} else {
		//--
		alert('ERROR: Undefined Marker type for getMarkerMetaInfo() !');
		//--
	} //end if else
	//--
	return the_metainfo;
	//--
} //END FUNCTION
var registerMarker = function(y_id, y_lat, y_lon, y_ttl, y_lnk, y_ficon, y_wicon, y_hicon) { // sync with getMarkerMetaInfo() for export_arr_markers[i] fields
	//--
	var tmp_new_marker = new Array();
	//--
	tmp_new_marker[0] = '' + y_id; 	 // Marker ID
	//--
	tmp_new_marker[1] = y_lat;  // Latitude
	tmp_new_marker[2] = y_lon;  // Longitude
	tmp_new_marker[3] = '' + y_ttl;  // Title
	tmp_new_marker[4] = '' + y_lnk;  // LinkURL
	tmp_new_marker[5] = '' + y_ficon; // Icon File
	tmp_new_marker[6] = y_wicon; // Icon Width
	tmp_new_marker[7] = y_hicon; // Icon Height
	//--
	export_arr_markers.push(tmp_new_marker);
	//--
	setFormData();
	//--
} //END FUNCTION
//--
var registerOutMarker = function(y_marker) { // unregister the marker
	//--
	var id = '' + y_marker.id;
	//--
	if((id === undefined) || (id === null) || (id.substring(0, 16) !== 'SmartOpenMapsID_')) {
		alert('ERROR: Failed to unregister this Marker (1)');
		return;
	} //end if
	//--
	var tmp_new_arr = new Array();
	//--
	var ii=0;
	var found=0;
	for(var i=0; i<export_arr_markers.length; i++) {
		//--
		var tmp_mkid = getMarkerMetaInfo(export_arr_markers[i], 'id'); // the marker ID - see it in registerMarker()
		//alert('#' + tmp_mkid);
		//--
		if(id === tmp_mkid) { // this is the selected marker that we do not register
			found++;
		} else {
			tmp_new_arr[ii] = export_arr_markers[i]; // add as array
			ii++;
		} //end id
		//--
	} //end for
	//--
	if(found !== 1) {
		alert('ERROR: Failed to unregister this Marker (2): ID=' + id + ' / Found=' + found);
		return;
	} //end if
	//--
	export_arr_markers = new Array();
	export_arr_markers = tmp_new_arr;
	//--
	setFormData();
	//--
} //END FUNCTION
//--
var ClearMarkers = function() {
	//--
	markers.clearMarkers();
	//--
	export_arr_markers = new Array();
	//--
	setFormData();
	//--
} //END FUNCTION
//--
this.clearUpMarkers = function() {
	//--
	ClearMarkers();
	//--
} //END FUNCTION
//--
var DrawMarkers = function(y_markers) {
	//--
	if((y_markers !== undefined) && (y_markers !== null) && (y_markers instanceof Array)) {
		//--
		var size = new OpenLayers.Size(parseInt(icon_w_marker), parseInt(icon_h_marker));
		var offset = null;
		if(use_icon_offset) {
			offset = new OpenLayers.Pixel(parseInt(Math.floor(size.w / -2)), parseInt(Math.floor(size.h / -1)));
		} //end if else
		var icon = new OpenLayers.Icon('' + icon_marker, size, offset);
		var clonedicon = false;
		//--
		for(var i=0; i<y_markers.length; i++) {
			//--
			var tmp_the_marker = y_markers[i];
			//--
			var tmp_id = '';
			if((tmp_the_marker[0] == null) || (tmp_the_marker[0] == '')) {
				tmp_id = '_' + uniquemarkerids; // markers with internal id will have an underscore as prefix
				uniquemarkerids += 1;
			} else {
				tmp_id = '' + tmp_the_marker[0]; // existing markers will be kept as they are
			} //end if
			if(tmp_id.substring(0, 16) !== 'SmartOpenMapsID_') {
				tmp_id = 'SmartOpenMapsID_' + tmp_id; // only add prefix if does not have one
			} //end if
			//--
			var tmp_the_lat = tmp_the_marker[1]; // lat
			var tmp_the_lon = tmp_the_marker[2]; // lon
			//--
			var tmp_the_ttl = '';
			if((typeof tmp_the_marker[3] != 'undefined') && (tmp_the_marker[3] != '')) {
				tmp_the_ttl = tmp_the_marker[3]; // ttl
			} //end if
			//--
			var tmp_the_lnk = '';
			if((typeof tmp_the_marker[4] != 'undefined') && (tmp_the_marker[4] != '')) {
				tmp_the_lnk = tmp_the_marker[4]; // lnk
			} //end if
			//--
			var extrealicon = '' + icon_marker;
			var exticon = null;
			var extsize = null;
			var extoffset = null;
			var ext_w_size = icon_w_marker;
			var ext_h_size = icon_h_marker;
			if((typeof tmp_the_marker[5] != 'undefined') && (tmp_the_marker[5] != '')) {
				//--
				if((typeof tmp_the_marker[6] != 'undefined') && (tmp_the_marker[6] != '') && (typeof tmp_the_marker[7] != 'undefined') && (tmp_the_marker[7] != '')) {
					ext_w_size = parseInt(tmp_the_marker[6]);
					ext_h_size = parseInt(tmp_the_marker[7]);
					if(ext_w_size < 1) {
						ext_w_size = 1;
					} //end if
					if(ext_h_size < 1) {
						ext_h_size = 1;
					} //end if
				} //end if
				//--
				extsize = new OpenLayers.Size(parseInt(ext_w_size), parseInt(ext_h_size));
				if(use_icon_offset) {
					extoffset = new OpenLayers.Pixel(parseInt(Math.floor(extsize.w / -2)), parseInt(Math.floor(extsize.h / -1)));
				} //end if
				exticon = new OpenLayers.Icon('' + tmp_the_marker[5], extsize, extoffset);
				extrealicon = '' + tmp_the_marker[5];
				//--
			} //end if
			//--
			//alert(pmap);
			var tmp_marker_coords = new OpenLayers.LonLat(tmp_the_lon, tmp_the_lat).transform(
				proj4326, // transform from WGS 1984
				pmap // to Spherical Mercator Projection (EPSG:900913)
			);
			//--
			if(exticon != null) {
				//alert('extra icon');
				var tmp_marker_draw = new OpenLayers.Marker(tmp_marker_coords, exticon);
			} else {
				if(clonedicon) {
					//alert('clone icon ...');
					var tmp_marker_draw = new OpenLayers.Marker(tmp_marker_coords, icon.clone());
				} else {
					//alert('original icon');
					clonedicon = true;
					var tmp_marker_draw = new OpenLayers.Marker(tmp_marker_coords, icon);
				} //end if else
			} //end if else
			//--
			tmp_marker_draw.id = tmp_id;
			markers.addMarker(tmp_marker_draw);
			registerMarker(tmp_id, tmp_the_lat, tmp_the_lon, tmp_the_ttl, tmp_the_lnk, extrealicon, ext_w_size, ext_h_size);
			markerDefaultActions(tmp_marker_draw, tmp_the_lat, tmp_the_lon, tmp_the_ttl, tmp_the_lnk);
			//--
		} //end for
		//--
	} //end if
	//--
} //END FUNCTION
//--
this.DrawUpMarkers = function(y_markers) {
	//--
	DrawMarkers(y_markers);
	//--
} //END FUNCTION
//--

//--
var markerClickAction = function(y_marker, y_lat, y_lon, y_title, y_link) {
	//--
	switch(opmode) {
		case 'delete-markers': // delete markers
			//--
			var the_delmsg = 'Press [OK] to DELETE this Marker !';
			if(y_title != '') {
				the_delmsg = 'Press [OK] to DELETE the Marker: ' + y_title + ' !';
			} //end if else
			//--
			var deletion = confirm(the_delmsg);
			//--
			if(deletion) {
				//--
				markers.removeMarker(y_marker);
				registerOutMarker(y_marker);
				//--
			} //end if
			//--
			break;
			//--
		case 'add-markers': // add markers
		case '': // just display
		default:
			//--
			_this.Smart_Handler_OpenMaps_Click(map, y_marker, y_lat, y_lon, y_title, y_link)
			//--
	} //end switch
	//--
} //END FUNCTION
//--
var markerDefaultActions = function(y_marker, y_lat, y_lon, y_title, y_link) {
	//-- 'click' / 'touchend' to handle both mouse and touch devices
	y_marker.events.register('click', y_marker, function(evt) { markerClickAction(y_marker, y_lat, y_lon, y_title, y_link); });
	y_marker.events.register('touchend', y_marker, function(evt) { markerClickAction(y_marker, y_lat, y_lon, y_title, y_link); });
	//--
	var the_infolog = '<b>Marker:</b>&nbsp;' + 'Lat=' + y_lat + '&nbsp;;&nbsp;Lon=' + y_lon + ' # ' + y_title + ' # ' + y_link;
	y_marker.events.register('mouseover', y_marker, function(evt) { setDivContent(divmap + "_markerinfo", '' + the_infolog); _this.Smart_Handler_OpenMaps_RollOver(map, crrMousePos, y_lat, y_lon, y_title, y_link); });
	y_marker.events.register('mouseout', y_marker, function(evt) { setDivContent(divmap + "_markerinfo", '&nbsp;'); _this.Smart_Handler_OpenMaps_RollOut(map, crrMousePos, y_lat, y_lon, y_title, y_link); });
	//--
} //END FUNCTION
//--

//--
var getCrrZoom = function() {
	return parseInt(crrzoom);
} //END FUNCTION
//--
this.getCurrentZoom = function() {
	return getCrrZoom();
} //END FUNCTION
//--

//--
var getCrrLat = function() {
	return crrlat;
} //END FUNCTION
//--
this.getCurrentLat = function() {
	return getCrrLat();
} //END FUNCTION
//--

//--
var getCrrLon = function() {
	return crrlon;
} //END FUNCTION
//--
this.getCurrentLon = function() {
	return getCrrLon();
} //END FUNCTION
//--

//--
this.SetMapDivID = function(y_div) {
	//--
	divmap = '' + y_div;
	//--
} //END FUNCTION
//--

//--
var draw_map = function(y_lat, y_lon, y_zoom, y_markers, y_mode) {

	//--
	y_zoom = parseInt(y_zoom);
	//--

	//--
	if(divmap == '') {
		alert('ERROR: Map Div is NOT SET !');
		return;
	} //end if
	//--

	//--
	map = new OpenLayers.Map({
		'div': divmap,
		projection: 'EPSG:900913', // 'EPSG:900913' is default for openmaps
		units: 'm'
	}); // display with default options
	//--

	//--
	if((y_zoom < 1) || (y_zoom > 18)) {
		y_zoom = 18;
	} //end if
	//--
	var offszoom = 0;
	//--
	switch(y_mode) {
		case 'test': // Test Direct Render (Mapnik)
			map.addLayer(new OpenLayers.Layer.OSM());
			break;
		case 'mapnik': // OpenStreetMaps (Mapnik) Proxy Caching Render
		case 'openstreetmap':
			map.addLayer(new OpenLayers.Layer.OSM.MapnikLocalProxy("OpenStreetMap"));
			break;
		case 'cyclemap': // OpenCycleMaps Proxy Caching Render
			map.addLayer(new OpenLayers.Layer.OSM.CycleMapLocalProxy("OpenCycleMap"));
			break;
		case 'cyclemap-transport': // OpenCycleMaps Transport Proxy Caching Render
			map.addLayer(new OpenLayers.Layer.OSM.CycleMapTransportLocalProxy("OpenCycleMap Transport"));
			break;
		case 'bing':
		case 'bing-hybrid':
		case 'bing-aerial':
			//-- commercial, requires an BingApiKey
			if((BingApiKey === undefined) || (BingApiKey === null) || (BingApiKey == '')) {
				alert('NOTICE: Bing Maps require an API Key ! You must set this before using Bing Maps ...');
				return;
			} //end if
			//--
			if(y_mode == 'bing-hybrid') {
				var BingHybridMap = new OpenLayers.Layer.Bing({
					name: "Bing Hybrid",
					key: BingApiKey,
					type: "AerialWithLabels"
				});
				map.addLayers([BingHybridMap]);
			} else if(y_mode == 'bing-aerial') {
				var BingAerialMap = new OpenLayers.Layer.Bing({
					name: "Bing Aerial",
					key: BingApiKey,
					type: "Aerial"
				});
				map.addLayers([BingAerialMap]);
			} else {
				var BingRoadMap = new OpenLayers.Layer.Bing({
					name: "Bing Roads",
					key: BingApiKey,
					type: "Road"
				});
				map.addLayers([BingRoadMap]);
			} //end if else
			//--
			offszoom = 1; // offset zoom is needed here ; bing zooms returned by map.getZoom() differ with this offset
			//--
			break;
		case 'google':
		case 'google-physical':
		case 'google-hybrid':
		case 'google-aerial':
			//-- commercial, for high loads requires a contract # because of a recent bug in GoogleMaps v3, multiple layers at once fail with OpenStreetMaps !!
			if(y_mode == 'google-physical') {
				if(y_zoom > 15) {
					y_zoom = 15;
				} //end if
				var GooglePhy = new OpenLayers.Layer.Google(
					"Google Physical",
					{type: google.maps.MapTypeId.TERRAIN}
				);
				map.addLayers([GooglePhy]);
			} else if(y_mode == 'google-hybrid') {
				var GoogleHyb = new OpenLayers.Layer.Google(
					"Google Hybrid",
					{type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}
				);
				map.addLayers([GoogleHyb]);
			} else if(y_mode == 'google-aerial') {
				var GoogleSat = new OpenLayers.Layer.Google(
					"Google Satellite",
					{type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}
				);
				map.addLayers([GoogleSat]);
			} else {
				var GoogleMap = new OpenLayers.Layer.Google(
					"Google Streets", // the default
					{numZoomLevels: 20}
				);
				map.addLayers([GoogleMap]);
			} //end if else
			//--
			break;
		default:
			alert('ERROR: OpenMaps // Invalid Draw Mode !');
			return;
	} //end switch
	//--
	pmap = map.getProjectionObject(); // it should be EPSG:900913
	//alert(pmap);
	//--
	var lonLat = new OpenLayers.LonLat(y_lon, y_lat).transform(
		proj4326, // transform from WGS 1984
		pmap // to Spherical Mercator Projection (EPSG:900913)
	);
	//--
	var zoom = parseInt(y_zoom);
	//--
	map.setCenter(lonLat, zoom);
	if(!map.getCenter()) {
		map.zoomToMaxExtent();
	} //end if
	//--
	crrzoom = zoom;
	crrlat = '' + y_lat;
	crrlon = '' + y_lon;
	//--
	markers = new OpenLayers.Layer.Markers("Markers");
	map.addLayer(markers);
	//--
	hitMap('init', y_markers, crrlat, crrlon, crrzoom); // init loader
	//--

	//--
	var AddNewMarker = function(e) {
		//--
		if(opmode === 'add-markers') {
			//--
			if(allow_edit_num_markers === 1) { // this is a special case, when only one marker is allowed we want to automatically delete the old one when placing the new marker for easy usage
				ClearMarkers();
			} //end if
			//--
			if(export_arr_markers.length < allow_edit_num_markers) {
				//--
				var prompt_title = prompt('Enter the title for the New Marker', '');
				var prompt_link = '';
				//--
				if(prompt_title !== null) {
					//--
					if((prompt_title == '') || (typeof prompt_title == 'undefined')) {
						prompt_title = 'New Marker (No Title)';
					} //end if
					//--
					prompt_link = prompt('Enter the link for the New Marker', '');
					if((prompt_link == '') || (typeof prompt_link == 'undefined')) {
						prompt_link = '';
					} //end if
					//--
					var newsize = new OpenLayers.Size(parseInt(icon_w_newmarker), parseInt(icon_h_newmarker));
					var newoffset = null;
					if(use_icon_offset) {
						newoffset = new OpenLayers.Pixel(parseInt(Math.floor(newsize.w / -2)), parseInt(Math.floor(newsize.h / -1)));
					} //end if
					var newicon = new OpenLayers.Icon('' + icon_newmarker, newsize, newoffset);
					//-- mouse events (basic)
					//this.events.clearMouseCache();
					//var crrpos = this.events.getMousePosition(e);
					//var lonlat = map.getLonLatFromPixel(crrpos);
					//-- touchpad events (advanced)
					var lonlat = map.getLonLatFromViewPortPx(e.xy);
					//--
					var lonlatTransf = lonlat.transform(pmap, proj4326);
					//--
					var tmp_the_lat = lonlatTransf.lat;
					var tmp_the_lon = lonlatTransf.lon;
					var tmp_marker_coords = lonlatTransf.transform(proj4326, pmap);
					//--
					var tmp_id = 'SmartOpenMapsID_' + uniquemarkerids;
					uniquemarkerids += 1;
					//--
					var tmp_marker_draw = new OpenLayers.Marker(tmp_marker_coords, newicon);
					tmp_marker_draw.id = tmp_id;
					markers.addMarker(tmp_marker_draw);
					registerMarker(tmp_id, tmp_the_lat, tmp_the_lon, prompt_title, prompt_link, icon_newmarker, icon_w_newmarker, icon_h_newmarker);
					markerDefaultActions(tmp_marker_draw, tmp_the_lat, tmp_the_lon, prompt_title, prompt_link);
					//--
				} //end if
				//--
			} else {
				//--
				alert('NOTICE: Current settings allow a maximum of [' + allow_edit_num_markers + '] markers !');
				//--
			} //end if else
			//--
		} //end if
		//--
	} //END FUNCTION
	//--

	//-- this is required only for basic mode (mouse)
	//map.events.register("click", map, function(e) {
	//	AddNewMarker(e);
	//});
	//--

	//-- disable the doubleclick
	var Navigation = new OpenLayers.Control.Navigation({
		'defaultDblClick': function(event) { return; }
	});
	map.addControl(Navigation);
	//--

	//-- mobile support
	// NEW: will use: var lonlat = map.getLonLatFromViewPortPx(e.xy);
	var TouchNavigation = new window.OpenLayers.Control.TouchNavigation({
		'defaultClickOptions': {
			pixelTolerance: 10
		},
		'defaultClick' : function(e) {
			AddNewMarker(e);
		}
	});
	map.addControl(TouchNavigation);
	TouchNavigation.activate();
	//--

	//-- handle map events: moveend, zoomend (zoomend - appears not to be required as it is handled by moveend)
	map.events.register("moveend", map, function(e) {
		//--
		var zoom = parseInt(map.getZoom() + offszoom);
		var lonlat =  map.getCenter();
		var lonlatTransf = lonlat.transform(pmap, proj4326);
		//--
		crrzoom = parseInt(zoom);
		crrlat = '' + lonlatTransf.lat;
		crrlon = '' + lonlatTransf.lon;
		//--
		hitMap('update', y_markers, crrlat, crrlon, crrzoom); // update loader
		//--
	});
	//--

	//--
	map.addControl(new OpenLayers.Control.LayerSwitcher());
	//--

	//--
	map.events.register("mousemove", map, function(e) {
		//--
		this.events.clearMouseCache();
		var position = this.events.getMousePosition(e);
		crrMousePos = position;
		//--
		var lonlat = map.getLonLatFromPixel(position);
		var lonlatTransf = lonlat.transform(pmap, proj4326);
		//--
		setDivContent(divmap + "_lonlat", '<b>Coordinates:</b>&nbsp;' + 'Lat=' + lonlatTransf.lat + '&nbsp;;&nbsp;Lon=' + lonlatTransf.lon + '&nbsp;#&nbsp;[&nbsp;<b><i>Markers:</i></b>&nbsp;' + export_arr_markers.length + '&nbsp;]');
		//--
		if(debug) {
			setDivContent(divmap + "_coords", '<b>MOUSE POSITION:</b> ' + 'position.x=' + parseInt(crrMousePos.x) + ' ; position.y=' + parseInt(crrMousePos.y));
			setDivContent(divmap + "_tglonlat", '<b>LON-LAT OBJECT:</b> ' + lonlat);
		} //end if
		//--
	});
	//--

	//--
	map.addControl(new OpenLayers.Control.ScaleLine());
	//--

} //END FUNCTION
//--
this.DrawMap = function(y_lat, y_lon, y_zoom, y_markers, y_mode) {
	//--
	draw_map(y_lat, y_lon, y_zoom, y_markers, y_mode);
	//--
} //END FUNCTION
//--

//--
this.setMarkerIcon = function(y_icon_file, y_icon_w, y_icon_h) {
	//-- set default marker icon
	icon_marker = '' + y_icon_file;
	icon_w_marker = parseInt(y_icon_w);
	if(icon_w_marker < 1) {
		icon_w_marker = 1;
	} //end if
	icon_h_marker = parseInt(y_icon_h);
	if(icon_h_marker < 1) {
		icon_h_marker = 1;
	} //end if
	//-- set also new marker icon, just in case is missed
	this.setNewMarkerIcon(y_icon_file, y_icon_w, y_icon_h);
	//--
} //END FUNCTION
//--

//--
this.setNewMarkerIcon = function(y_icon_file, y_icon_w, y_icon_h) {
	//-- set new marker icon
	icon_newmarker = '' + y_icon_file;
	icon_w_newmarker = parseInt(y_icon_w);
	if(icon_w_newmarker < 1) {
		icon_w_newmarker = 1;
	} //end if
	icon_h_newmarker = parseInt(y_icon_h);
	if(icon_h_newmarker < 1) {
		icon_h_newmarker = 1;
	} //end if
	//--
} //END FUNCTION
//--

//--
this.setIconOffsetMode = function(y_use_offset) {
	if(y_use_offset === false) {
		use_icon_offset = false;
	} else {
		use_icon_offset = true;
	} //end if else
} //END FUNCTION
//--

//--
this.OperationModeGet = function() {
	return opmode;
} //END FUNCTION
//--
this.OperationModeSwitch = function(y_mode) {
	//--
	if(allow_edit_num_markers > 0) {
		//--
		switch(parseInt(y_mode)) {
			case 1:
				opmode = 'add-markers';
				use_formdata = 1;
				break;
			case 2:
				opmode = 'delete-markers';
				use_formdata = 1;
				break;
			case 0:
			default:
				opmode = '';
				use_formdata = 0;
		} //end switch
		//--
	} else {
		//--
		opmode = ''; // in this case we do not allow edit modes (place / delete markers)
		use_formdata = 0;
		//--
	} //end if else
	//--
	setFormData();
	//--
} //END FUNCTION
//--

//--
this.allowOperationEdit = function(y_mode) {
	//--
	y_mode = parseInt(y_mode);
	//--
	if(y_mode > 0) {
		allow_edit_num_markers = y_mode;
	} else {
		allow_edit_num_markers = 0;
	} //end switch
	//--
} //END FUNCTION
//--

//--
this.setDebug = function(y_debug) {
	//--
	y_debug = parseInt(y_debug);
	//--
	if(y_debug === 1) {
		debug = 1;
	} else {
		debug = 0;
	} //end if else
	//--
} //END FUNCTION
//--

//--
this.getMarkers = function(y_mode) {
	//--
	if(y_mode === '#') {
		//--
		var out = '';
		out = getFormData();
		alert('RESULT: ' + '\n' + out);
		//--
	} //end if
	//--
	return export_arr_markers;
	//--
} //END FUNCTION
//--

//--
this.RestrictExtent = function(lon1, lat1, lon2, lat2) {
	//--
	var extent = new OpenLayers.Bounds(lon1, lat1, lon2, lat2).transform(proj4326, pmap);
	//--
	map.setOptions({ restrictedExtent: extent });
	//--
} //END FUNCTION
//--

//== ALL BELOW ARE EXTERNAL HANDLERS THAT CAN BE REDEFINED PER INSTANCE

this.Smart_Handler_OpenMaps_LoadData = function(y_init, y_markers, y_lat, y_lon, y_zoom) {
	//--
	var redrawhandler;
	//--
	if(y_init == 'init') { // avoid draw markers on map move (that can be done by extending with above function)
		//alert('CLR');
		redrawhandler = '[DRAW]';
		ClearMarkers();
		DrawMarkers(y_markers);
	} else {
		redrawhandler = '[KEEP]';
	} //end if
	//--
	return redrawhandler;
	//--
} //END FUNCTION

this.Smart_Handler_OpenMaps_Click = function(map, y_marker, y_lat, y_lon, y_title, y_link) {
	//--
	//alert('Internal ... Click');
	//--
	map.addPopup(
		new OpenLayers.Popup.FramedCloud(
		divmap + '__smart_popup',
		y_marker.lonlat,
		new OpenLayers.Size(100, 100),
		'<small>' + 'Latitude: ' + y_lat + ' / Longitude: ' + y_lon + '<br>' + '<b>' + y_title + '</b>' + '<br>' + '<a href="' + y_link + '" target="_blank">' + y_link + '<a>' + '</small>',
		null, // anchor
		true // show close
		)
	);
	//--
} //END FUNCTION

this.Smart_Handler_OpenMaps_RollOver = function(map, crrMousePos, y_lat, y_lon, y_title, y_link) {
	//alert('Roll Over ...');
} //END FUNCTION

this.Smart_Handler_OpenMaps_RollOut = function(map, crrMousePos, y_lat, y_lon, y_title, y_link) {
	//alert('Roll Out ...');
} //END FUNCTION

//==

} //END OBJECT-CLASS
//==

// #END
