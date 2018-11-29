
// [@[#[!JS-Compress!]#]@]
// SmartMaps // JS Lib # v.2017.04.21
// (c) 2012-2017 unix-world.org
// License: Apache 2.0 License

// DEPENDS: SmartJS_CoreUtils, OpenLayers (patched version)

//==
function Smart_Maps(y_proxycachebuf, y_proxycacheurl, y_proxymaptype) { // OBJECT-CLASS

// -> dynamic (new)

var _class = this; // self referencing

//--
var version_smartmaps = '20170421';
var reqver_openlayers = 'v.2.12.1.r.20170421.unixw';
//--
var author = '(c) 2012-2017 unix-world.org - all rights reserved';
var typelicense = 'Apache 2.0 License';
var license = 'SmartMaps v.' + version_smartmaps + ' :: ' + author + ' :: License: ' + typelicense;
var copyright = '<span style="padding: 2px; font-size:12px; color: #FFFFFF; background-color: #1D0092; cursor: help;" title="' + license + '">Map.Data CC-By-SA: OpenStreetMap // Map.API by: &copy; unix-world.org </span>';
//--
if(OpenLayers.VERSION_NUMBER !== reqver_openlayers) {
	alert('ERROR: SmartMaps require a special (patched) version of OpenLayers: (' + reqver_openlayers + ').\nThe Current Version of OpenLayers is: (' + OpenLayers.VERSION_NUMBER + ').\nUsing SmartMaps with other versions of OpenLayers can lead to unpredictable behaviour.\nYou can use it on your own risk with many errors in: coordinates, positioning, rendering.\n\n SmartMaps (version: ' + version_smartmaps + ') / ' + author);
	return; // avoid execution in this case
} //end if
//--

//--
var staticmarkers = true;
//--
var rendermode = '[?]';
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
if((typeof y_proxymaptype != 'undefined') && (y_proxymaptype !== undefined) && (y_proxymaptype !== null)) {
	localproxytype = '' + y_proxymaptype;
} //end if
//--

//--
var is_map_changed = 0;
var divmap = ''; // the div to bind to
//--
var map; // map object
var pmap; // map projection
var markers; // markers layer
//--
var markareas;
var drawAreaControl;
//--
var crrlat, crrlon;
var crrzoom;
var crrMousePos = { x: 0, y: 0 };
var areasStyles = {
	strokeColor: "#F00081",
	strokeOpacity: 0.5,
	strokeWidth: 2,
	fillColor: "#003399",
	fillOpacity: 0.45
};
//--
var use_icon_offset = true; // by default marker centers but we want have it above/centered
//--
var icon_marker = '';
var icon_w_marker = 1;
var icon_h_marker = 1;
var icon_newmarker = '';
var icon_w_newmarker = 1;
var icon_h_newmarker = 1;
//--
var icon_def_opacity = 0.7;
var icon_sel_opacity = 1;
//--
var icon_def_zindex = 100;
var icon_sel_zindex = 101;
//--

//-- some internals
var debug = 0; // debugging
var uniquemarkerids = 1000; // this is a unique ID, so will never reset, except ClearMarkers
var proj4326 = new OpenLayers.Projection("EPSG:4326"); // The standard latitude/longitude projection string is EPSG:4326 (eliptic)

//--
var export_arr_markers = new Array(); // export markers array
var export_arr_markareas = new Array(); // export markareas array
//--

//-- by default the edit is restricted
var use_formdata = 0;
var opmode = '';
var allow_edit_num_markers = 0; // if this var is > 0 will allow placing as many markers as this number
//--

//-- measure controls
var measureDistanceControl;
var measureAreaControl;
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
		rendermode = mode;
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

//--
var hitMap = function(y_init, y_markers, y_areas, y_lat, y_lon, y_zoom) { // y_init: init / update
	//--
	var redrawhandler = '[???]';
	//--
	if(staticmarkers) {
		//--
		redrawhandler = _class.Smart_Handler_SmartMaps_LoadData('static', y_markers, y_areas, y_lat, y_lon, y_zoom);
		//--
	} else {
		//--
		redrawhandler = _class.Smart_Handler_SmartMaps_LoadData(y_init, y_markers, y_areas, y_lat, y_lon, y_zoom);
		//--
	} //end if else
	//--
	if(debug) {
		setDivContent(divmap + "_crrmap", '<b>CURRENT MAP DEBUG ' + redrawhandler + ' (' + y_init + '):</b> ' + 'Lat=' + y_lat + ' ; Lon=' + y_lon + ' ; RealZoom=' + y_zoom);
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
var getMarkersFormData = function() {
	//--
	return getDivContent(divmap + '_data');
	//--
} //END FUNCTION
//--
var setMarkersFormData = function() {
	//--
	out = '';
	//--
	if(use_formdata == 1) {
		//--
		//alert('Using Set Markers Form Data ...');
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
var getMarkerByID = function (y_id) {
	//--
	var tmp_arr;
	//--
	for(var i=0; i<export_arr_markers.length; i++) {
		var tmp_id = getMarkerMetaInfo(export_arr_markers[i], 'id');
		if(tmp_id == y_id) {
			tmp_arr = export_arr_markers[i];
		} //end if
	} //end for
	//--
	return tmp_arr;
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
	setMarkersFormData();
	//--
} //END FUNCTION
//--
var registerOutMarker = function(y_marker) { // unregister the marker
	//--
	var id = '' + y_marker.id;
	//--
	if((id === undefined) || (id === null) || (id.substring(0, 12) !== 'SmartMapsID_')) {
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
	setMarkersFormData();
	//--
} //END FUNCTION
//--
var ClearMarkers = function() {
	//--
	markers.removeAllFeatures();
	//--
	export_arr_markers = new Array();
	//--
	setMarkersFormData();
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
			if(tmp_id.substring(0, 12) !== 'SmartMapsID_') {
				tmp_id = 'SmartMapsID_' + tmp_id; // only add prefix if does not have one
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
			var extsize = null;
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
			var point = new OpenLayers.Geometry.Point(tmp_marker_coords.lon, tmp_marker_coords.lat);
			//--
			if(extsize !== null) {
				//--
				var style_mark = {
					'graphicZIndex': icon_def_zindex,
					'pointRadius': 3,
					'externalGraphic': extrealicon,
					'graphicWidth': extsize.w,
					'graphicHeight': extsize.h,
					'graphicXOffset': parseInt(Math.floor(extsize.w / -2)),
					'graphicYOffset': parseInt(Math.floor(extsize.h / -1)),
					'graphicTitle': '' + tmp_the_ttl,
					'fillOpacity': icon_def_opacity,
					'strokeOpacity': 0.25,
					'cursor': 'pointer',
					'label': '',
					'fontColor': '#000000',
					'fontSize': "9px",
					'labelAlign': "lb"
				};
				//--
			} else {
				//--
				if(icon_marker == '') { // use vector circle, mainly for paths
					//--
					var style_mark = {
						'graphicZIndex': icon_def_zindex,
						'pointRadius': 5,
						'fillColor': '#FF6600',
						'strokeColor': "#FF9900",
						'graphicWidth': size.w,
						'graphicHeight': size.h,
						'graphicXOffset': parseInt(Math.floor(size.w / -2)),
						'graphicYOffset': parseInt(Math.floor(size.h / -1)),
						'graphicTitle': '' + tmp_the_ttl,
						'fillOpacity': icon_def_opacity,
						'strokeOpacity': 0.25,
						'cursor': 'crosshair', // this must be different than pointer
						// here we do not want a label
						'strokeWidth': 0.5
					};
					//--
				} else { // use icon
					//--
					var style_mark = {
						'graphicZIndex': icon_def_zindex,
						'pointRadius': 5,
						'externalGraphic': '' + icon_marker,
						'graphicWidth': size.w,
						'graphicHeight': size.h,
						'graphicXOffset': parseInt(Math.floor(size.w / -2)),
						'graphicYOffset': parseInt(Math.floor(size.h / -1)),
						'graphicTitle': '' + tmp_the_ttl,
						'fillOpacity': icon_def_opacity,
						'strokeOpacity': 0.25,
						'cursor': 'pointer',
						'label': '',
						'fontColor': '#000000',
						'fontSize': "9px",
						'labelAlign': "lb"
					};
					//--
				} //end if else
				//--
			} //end if else
			//--
			var tmp_marker_draw = new OpenLayers.Feature.Vector(point, null, style_mark);
			//--
			tmp_marker_draw.id = tmp_id;
			//--
			markers.addFeatures([tmp_marker_draw]);
			//--
			registerMarker(tmp_id, tmp_the_lat, tmp_the_lon, tmp_the_ttl, tmp_the_lnk, extrealicon, ext_w_size, ext_h_size);
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
var getMarkAreasFormData = function() {
	//--
	return getDivContent(divmap + '_adata');
	//--
} //END FUNCTION
//--
var setMarkAreasFormData = function() {
	//--
	out = '';
	//--
	if(use_formdata == 1) {
		//--
		//alert('Using Set Areas Form Data ...');
		//--
		for(var i=0; i<export_arr_markareas.length; i++) {
			out += '<input type="hidden" name="' + divmap + '_adata[' + export_arr_markareas[i][0] + '][geometry]" value="' + export_arr_markareas[i][2] +'">' + "\n";
			out += '<input type="hidden" name="' + divmap + '_adata[' + export_arr_markareas[i][0] + '][center]" value="' + export_arr_markareas[i][3] +'">' + "\n";
			out += '<input type="hidden" name="' + divmap + '_adata[' + export_arr_markareas[i][0] + '][bounds]" value="' + export_arr_markareas[i][4] +'">' + "\n";
			//break; // at this phase we allow just one area
		} //end for
		//--
		setDivContent(divmap + '_adata', out);
		//--
	} //end if
	//--
} //END FUNCTION
//--
var ClearAreas = function() {
	//--
	markareas.removeAllFeatures();
	//--
	export_arr_markareas = new Array();
	//--
	setMarkAreasFormData();
	//--
} //END FUNCTION
//--
this.clearUpAreas = function() {
	//--
	ClearAreas();
	//--
} //END FUNCTION
//--
var registerMarkArea = function(y_id, y_type, y_dataset) {
	//--
	var wkt = new OpenLayers.Format.WKT();
	var geometry_figure = wkt.read('' + y_dataset);
	//--
	var tmp_new_area = new Array();
	//-- id
	tmp_new_area[0] = '' + y_id; // Area ID
	//-- figure
	tmp_new_area[1] = y_type;  // Type: by now we have just: polygon, multipolygon, line, multiline
	tmp_new_area[2] = y_dataset;  // DataSet like x1 y1, x2 y2
	//-- centroid
	var geometry_center = geometry_figure.geometry.getCentroid(); // get center
	tmp_new_area[3] = '' + geometry_center;
	//-- bounds
	var geometry_bounds = geometry_figure.geometry.getBounds(); // get bounds
	tmp_new_area[4] = '' + geometry_bounds;
	//--
	export_arr_markareas.push(tmp_new_area);
	//--
	setMarkAreasFormData();
	//--
} //END FUNCTION
//--
var DrawMarkAreas = function(y_areas) {
	//--
	if((y_areas !== undefined) && (y_areas !== null) && (y_areas instanceof Array)) {
		//--
		for(var i=0; i<y_areas.length; i++) {
			//--
			var tmp_the_markarea_dataset = y_areas[i];
			//--
			if((tmp_the_markarea_dataset[1] === 'polygon') || (tmp_the_markarea_dataset[1] === 'multipolygon') || (tmp_the_markarea_dataset[1] === 'line') || (tmp_the_markarea_dataset[1] === 'multiline')) {
				//--
				registerMarkArea(tmp_the_markarea_dataset[0], tmp_the_markarea_dataset[1], tmp_the_markarea_dataset[2]);
				//--
				var wkt = new OpenLayers.Format.WKT();
				var geometryFigureFeature = wkt.read('' + tmp_the_markarea_dataset[2]);
				//var geometryFigureCenter = geometryFigureFeature.geometry.getCentroid(); // get center
				//alert(geometryFigureCenter);
				geometryFigureFeature.geometry.transform(proj4326, pmap);
				markareas.addFeatures([geometryFigureFeature]);
				//--
				//break; // at this moment we allow more to be draw but only one to place at once
				//--
			} //end if
			//--
		} //end for
		//--
	} //end if
	//--
} //END FUNCTION
//--
this.DrawUpAreas = function(y_areas) {
	//--
	DrawMarkAreas(y_areas);
	//--
} //END FUNCTION
//--

//--
var fixZoom = function(zoom) {
	//--
	zoom = parseInt(zoom);
	if(isNaN(zoom) || (zoom > 50)) {
		zoom = 50; // hard limit
	} else if(zoom < 1) {
		zoom = 1;
	} //end if else
	//--
	return zoom;
	//--
} //END FUNCTION
//--
var getCrrZoom = function() {
	//--
	return parseInt(crrzoom);
	//--
} //END FUNCTION
//--
this.getCurrentZoom = function() {
	//--
	return getCrrZoom();
	//--
} //END FUNCTION
//--

//--
var getCrrLat = function() {
	//--
	return crrlat;
	//--
} //END FUNCTION
//--
this.getCurrentLat = function() {
	//--
	return getCrrLat();
	//--
} //END FUNCTION
//--

//--
var getCrrLon = function() {
	//--
	return crrlon;
	//--
} //END FUNCTION
//--
this.getCurrentLon = function() {
	//--
	return getCrrLon();
	//--
} //END FUNCTION
//--

//--
this.setMapDivID = function(y_div) {
	//--
	divmap = '' + y_div;
	//--
} //END FUNCTION
//--

//--
var draw_map = function(y_lat, y_lon, y_zoom, y_markers, y_areas, y_mode) {

	//--
	if(divmap == '') {
		alert('ERROR: Map Div is NOT SET !');
		return;
	} //end if
	//--

	//--
	map = new OpenLayers.Map({
		'div': divmap,
		projection: 'EPSG:900913', // 'EPSG:900913' is default (Sperical Mercator) - geodesic must be on
		units: 'm',
		controls: [ new OpenLayers.Control.Attribution() ]
	}); // display with default options
	//--

	//--
	var offszoom = 0; // map initialize zoom offset (some maps may have a different scale of zoom levels, thus this need to adjust it)
	//--
	if(typeof y_mode == 'function') {
		try {
			offszoom = parseInt(y_mode(map));
			if(offszoom < -5 || (offszoom > 5) || isNaN(offszoom)) {
				offszoom = 0;
			} //end if
		} catch(err){ console.log(err); }
	} else {
		switch(y_mode) {
			case 'test': // Test Direct Render (Mapnik)
				map.addLayer(new OpenLayers.Layer.OSM());
				break;
			case 'mapnik': // OpenStreetMaps (Mapnik) Proxy Caching Render
			case 'openstreetmap':
				map.addLayer(new OpenLayers.Layer.OSM.MapnikLocalProxy("OpenStreetMap"));
				break;
			default:
				alert('ERROR: SmartMaps // Invalid Map Selected ... Available built-in options are: openstreetmap');
				return;
		} //end switch
		//--
	} //end if else
	//--
	pmap = map.getProjectionObject(); // it should be EPSG:900913
	//alert(pmap);
	//--
	var lonLat = new OpenLayers.LonLat(y_lon, y_lat).transform(
		proj4326, // transform from WGS 1984
		pmap // to Spherical Mercator Projection (EPSG:900913)
	);
	//--

	//--
	crrzoom = fixZoom(y_zoom) + offszoom;
	if((crrzoom < 1) || (crrzoom > fixZoom(map.getNumZoomLevels()) - 1)) {
		crrzoom = fixZoom(map.getNumZoomLevels()) - 1;
	} //end if
	//--
	crrlat = '' + y_lat;
	crrlon = '' + y_lon;
	//--

	//--
	map.setCenter(lonLat, crrzoom);
	if(!map.getCenter()) {
		map.zoomToMaxExtent();
	} //end if
	//--

	//--
	markareas = new OpenLayers.Layer.Vector('Paths and Areas', {
		'renderers': ['SVG'], // this is the safest but req. IE9 or later
		'rendererOptions': {
			'yOrdering': false,
			'zIndexing': true
		}
	});
	markareas.style = areasStyles;
	map.addLayer(markareas);
	//--

	//--
	markers = new OpenLayers.Layer.Vector('Markers', {
		'renderers': ['SVG'], // this is the safest but req. IE9 or later :: 'SVG', 'Canvas', 'VML' (the best on all modern browsers is SVG)
		'rendererOptions': {
			'yOrdering': false,
			'zIndexing': true
		}
	});
	map.addLayer(markers);
	//--

	//--
	hitMap('init', y_markers, y_areas, crrlat, crrlon, crrzoom); // init loader
	//--

	//--
	var updateMouseData = function(position) {
		//--
		crrMousePos = position;
		//--
		var lonlat = map.getLonLatFromPixel(position);
		var lonlatTransf = lonlat.transform(pmap, proj4326);
		//--
		setDivContent(divmap + "_lonlat", '<b>Coordinates:</b>&nbsp;' + 'Z(' + (crrzoom + 1) + ')&nbsp;;&nbsp;' + 'Lat=' + lonlatTransf.lat + '&nbsp;;&nbsp;Lon=' + lonlatTransf.lon + '&nbsp;#&nbsp;[&nbsp;<b><i>Markers:</i></b>&nbsp;' + export_arr_markers.length + '&nbsp;]&nbsp;#&nbsp;' + rendermode);
		//--
		if(debug) {
			setDivContent(divmap + "_tglonlat", '<b>LON-LAT OBJECT:</b> ' + lonlat);
			setDivContent(divmap + "_coords", '<b>MOUSE POSITION:</b> ' + 'position.x=' + parseInt(crrMousePos.x) + ' ; position.y=' + parseInt(crrMousePos.y));
		} //end if
		//--
	} //END FUNCTION
	//--

	//--
	var MapSmartClickHandler = function(e) {
		//--
		updateMouseData(e.xy);
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
					//--
					var lonlat = map.getLonLatFromPixel(crrMousePos);
					//--
					var lonlatTransf = lonlat.transform(pmap, proj4326);
					//--
					var tmp_the_lat = lonlatTransf.lat;
					var tmp_the_lon = lonlatTransf.lon;
					var tmp_marker_coords = lonlatTransf.transform(proj4326, pmap);
					//--
					var tmp_id = 'SmartMapsID_' + uniquemarkerids;
					uniquemarkerids += 1;
					//--
					if(icon_newmarker != '') {
						//--
						var newstyle_mark = {
							'graphicZIndex': icon_def_zindex,
							'pointRadius': 5,
							'externalGraphic': '' + icon_newmarker,
							'graphicWidth': newsize.w,
							'graphicHeight': newsize.h,
							'graphicXOffset': parseInt(Math.floor(newsize.w / -2)),
							'graphicYOffset': parseInt(Math.floor(newsize.h / -1)),
							'graphicTitle': '' + prompt_title,
							'fillOpacity': icon_def_opacity,
							'strokeOpacity': 0.25,
							'cursor': 'pointer',
							'label': '',
							'fontColor': '#000000',
							'fontSize': "9px",
							'labelAlign': "lb"
						};
						//--
					} else {
						//--
						var newstyle_mark = {
							'graphicZIndex': icon_def_zindex,
							'pointRadius': 5,
							'fillColor': '#FF0000',
							'strokeColor': '#FF0000',
							'graphicWidth': newsize.w,
							'graphicHeight': newsize.h,
							'graphicXOffset': parseInt(Math.floor(newsize.w / -2)),
							'graphicYOffset': parseInt(Math.floor(newsize.h / -1)),
							'graphicTitle': '' + prompt_title,
							'fillOpacity': icon_def_opacity,
							'strokeOpacity': 0.25,
							'cursor': 'pointer',
							'label': '',
							'fontColor': '#000000',
							'fontSize': "9px",
							'labelAlign': "lb"
						};
						//--
					} //end if else
					//--
					var newpoint = new OpenLayers.Geometry.Point(tmp_marker_coords.lon, tmp_marker_coords.lat);
					var tmp_marker_draw = new OpenLayers.Feature.Vector(newpoint, null, newstyle_mark);
					tmp_marker_draw.id = tmp_id;
					markers.addFeatures([tmp_marker_draw]);
					//--
					registerMarker(tmp_id, tmp_the_lat, tmp_the_lon, prompt_title, prompt_link, icon_newmarker, icon_w_newmarker, icon_h_newmarker);
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

	//-- areas draw control # aaa
	drawAreaControl = new OpenLayers.Control.DrawFeature(markareas,
		//OpenLayers.Handler.RegularPolygon, { handlerOptions: { sides: 8 } }
		OpenLayers.Handler.Polygon
		//OpenLayers.Handler.Path
	);
	map.addControl(drawAreaControl);
	markareas.events.on({
		'featureadded': function(el) {
			//--
			if(opmode == 'add-areas') {
				//--
				//alert(SmartJS_CoreUtils.print_Object(el.feature));
				//--
				//var area = el.feature.geometry.getArea();
				//alert(area);
				//--
				var the_num_draws = markareas.features;
				//--
				if(the_num_draws.length > 1) { // allow only one at a time
					//--
					ClearAreas();
					//--
				} else {
					//--
					//var geoJson = new OpenLayers.Format.GeoJSON();
					//arrJson = geoJson.write(el.feature);
					var figure_data = el.feature.geometry; // figure data
					//--
					var figure_lonlat = figure_data.transform(pmap, proj4326); // convert to lat-lon
					//alert('Figure Added: ' + figure_lonlat);
					//--
					//var centroid_points = figure_data.getCentroid(); // get figure center (does not need transform if already figure is transformed)
					//alert('Centroid: ' + centroid_points);
					//--
					registerMarkArea('AREA_NEW', 'polygon', figure_lonlat);
					//--
				} //end if else
				//--
			} //end if else
			//--
		}
	});
	//--

	//-- measure controls
	var doMeasures = function(event) {
		//--
		var geometry = event.geometry;
		var units = event.units;
		var order = event.order;
		var measure = event.measure;
		//--
		if(order == 1) {
			out = '<b>MEASURE Distance: </b>' + SmartJS_CoreUtils.escape_html(measure.toFixed(3) + ' ' + units);
		} else {
			out = '<b>MEASURE Area: </b>' + SmartJS_CoreUtils.escape_html(measure.toFixed(3) + ' ' + units) + '<sup>2</sup>';
		} //end if
		//--
		setDivContent(divmap + "_lonlat", '<span style="color: #000000;">' + out + '</span>');
		if(debug) {
			setDivContent(divmap + "_measure", out);
		} //end if
		//--
		_class.Smart_Handler_SmartMaps_Measure(order, measure, units);
		//--
	} //END FUNCTION
	//--
	measureDistanceControl = new OpenLayers.Control.Measure(OpenLayers.Handler.Path, { geodesic: true, persist: true, handlerOptions: { layerOptions: { renderers: ['SVG'] } } });
	map.addControl(measureDistanceControl);
	measureDistanceControl.events.on({
		'measure': doMeasures
	});
	//--
	measureAreaControl = new OpenLayers.Control.Measure(OpenLayers.Handler.Polygon, { geodesic: true, persist: true, handlerOptions: { layerOptions: { renderers: ['SVG'] } } });
	map.addControl(measureAreaControl);
	measureAreaControl.events.on({
		'measure': doMeasures
	});
	//--

	//-- disable the doubleclick
	var Navigation = new OpenLayers.Control.Navigation({
		'defaultDblClick': function(event) { return; }
	});
	map.addControl(Navigation);
	//--
	var ZoomPanel = new OpenLayers.Control.ZoomPanel();
	map.addControl(ZoomPanel);
	var PanPanel = new OpenLayers.Control.PanPanel();
	map.addControl(PanPanel);
	//--	controls: [ new OpenLayers.Control.PanPanel(), new OpenLayers.Control.ZoomPanel()]

	//--
	map.addControl(new OpenLayers.Control.LayerSwitcher());
	//--

	//-- click handler that support also mobile devices
	OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
		defaultHandlerOptions: {
			'single': true,
			'double': false,
			'pixelTolerance': 0,
			'stopSingle': false,
			'stopDouble': false
		},
		initialize: function(options) {
			this.handlerOptions = OpenLayers.Util.extend({}, this.defaultHandlerOptions);
			OpenLayers.Control.prototype.initialize.apply(this, arguments);
			this.handler = new OpenLayers.Handler.Click(this, {'click': this.trigger}, this.handlerOptions);
		}
	});
	// Create click control
	var clickControl = new OpenLayers.Control.Click( {
		trigger: function(e) {
			MapSmartClickHandler(e);
		}
	});
	map.addControl(clickControl);
	clickControl.activate();
	//--

	//-- handle map events: moveend, zoomend (zoomend - appears not to be required as it is handled by moveend)
	map.events.register("moveend", map, function(e) {
		//--
		var zoom = parseInt(map.getZoom());
		var lonlat =  map.getCenter();
		var lonlatTransf = lonlat.transform(pmap, proj4326);
		//--
		crrzoom = parseInt(zoom);
		crrlat = '' + lonlatTransf.lat;
		crrlon = '' + lonlatTransf.lon;
		//--
		hitMap('update', y_markers, y_areas, crrlat, crrlon, crrzoom); // update loader
		//--
	});
	//--

	//--
	map.events.register("mousemove", map, function(e) {
		//--
		this.events.clearMouseCache();
		var position = this.events.getMousePosition(e);
		//--
		updateMouseData(position)
		//--
	});
	//--
	map.events.register("touchmove", map, function(e) {
		//--
		this.events.clearMouseCache();
		var position = this.events.getMousePosition(e);
		//--
		updateMouseData(position)
		//--
	});
	//--

	//-- vector marker click handler
	var vector_clk = new OpenLayers.Control.SelectFeature(
	   [markers],
	   {
			'multiple': true,
			'clickout': true,
			'toggle': true,
			'hover': false
	   }
	);
	map.addControl(vector_clk);
	//--
	markers.events.on({
		'featureselected': function(e) {
			//--
			//alert(SmartJS_CoreUtils.print_Object(e));
			//--
			if(typeof e.feature == 'undefined') {
				alert('ERROR: Invalid Object Selected (1) !');
				return;
			} //end if
			//--
			switch(opmode) {
				case 'add-areas':
					//--
					// nothing
					//--
					break;
				case 'delete-markers':
					//--
					var the_delmsg = 'Press [OK] to DELETE this Marker !';
					var deletion = confirm(the_delmsg);
					//--
					if(deletion) {
						//--
						markers.removeFeatures(e.feature);
						registerOutMarker(e.feature);
						//--
					} //end if
					//--
					break;
				case 'add-markers':
					//--
					// nothing
					//--
					break;
				default:
					//--
					var click_el_data = getMarkerByID(e.feature.id);
					var click_popup_coords = new OpenLayers.LonLat(getMarkerMetaInfo(click_el_data, 'lon'), getMarkerMetaInfo(click_el_data, 'lat')).transform(
						proj4326, // transform from WGS 1984
						pmap // to Spherical Mercator Projection (EPSG:900913)
					);
					_class.Smart_Handler_SmartMaps_Click(divmap, map, markers, e, click_popup_coords, getMarkerMetaInfo(click_el_data, 'lat'), getMarkerMetaInfo(click_el_data, 'lon'), getMarkerMetaInfo(click_el_data, 'ttl'), getMarkerMetaInfo(click_el_data, 'lnk'), icon_sel_opacity, icon_sel_zindex, icon_def_opacity, icon_def_zindex);
					//--
					break;
			} //end if
			//--
		},
		'featureunselected': function(e) {
			//--
			//alert(SmartJS_CoreUtils.print_Object(e));
			//--
			if(typeof e.feature == 'undefined') {
				alert('ERROR: Invalid Object Selected (1) !');
				return;
			} //end if
			//--
			switch(opmode) {
				case 'add-areas':
					//--
					// nothing
					//--
					break;
				case 'delete-markers':
					//--
					// nothing
					//--
					break;
				case 'add-markers':
					//--
					// nothing
					//--
					break;
				default:
					//--
					_class.Smart_Handler_SmartMaps_EndClick(divmap, map, markers, e, icon_sel_opacity, icon_sel_zindex, icon_def_opacity, icon_def_zindex);
					//--
					break;
			} //end if
			//--
		}
	});
	vector_clk.activate();
	//--

	//--
	map.addControl(new OpenLayers.Control.ScaleLine( { geodesic: true } ));
	//--

	//--
	handleTheAreaDrawings();
	//--

} //END FUNCTION
//--
this.DrawMap = function(y_lat, y_lon, y_zoom, y_markers, y_areas, y_mode) {
	//--
	is_map_changed++;
	//--
	draw_map(y_lat, y_lon, y_zoom, y_markers, y_areas, y_mode);
	//--
} //END FUNCTION
//--
var handleTheAreaDrawings = function() {
	//--
	if((typeof opmode == 'undefined') || (typeof drawAreaControl == 'undefined')) {
		return;
	} //end if
	//--
	drawAreaControl.deactivate();
	switch(opmode) {
		case 'add-areas':
			drawAreaControl.activate();
			break;
		case 'delete-markers':
		case 'add-markers':
		default:
			//--
			break;
	} //end switch
	//--
} //END FUNCTION
//--
var handleTheMeasures = function(measure_mode) {
	//--
	if((typeof opmode == 'undefined') || (typeof measureDistanceControl == 'undefined') || (typeof measureAreaControl == 'undefined')) {
		return;
	} //end if
	//--
	measureDistanceControl.deactivate();
	measureAreaControl.deactivate();
	switch(opmode) {
		case 'add-areas':
		case 'delete-markers':
		case 'add-markers':
			//--
			break;
		default:
			//--
			switch(measure_mode) {
				case 'measure-distance':
					measureDistanceControl.activate();
					break;
				case 'measure-area':
					measureAreaControl.activate();
					break;
				default:
					//--
			} //end switch
			//--
			break;
	} //end switch
	//--
} //END FUNCTION
this.handleMeasures = function(measure_mode) {
	//--
	handleTheMeasures(measure_mode);
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
	opmode = '';
	use_formdata = 0;
	//--
	switch(parseInt(y_mode)) {
		case 3: // add-areas
			if(allow_edit_num_markers > 0) {
				opmode = 'add-areas';
				use_formdata = 1;
				staticmarkers = false; // detect dynamic markers
			} else {
				opmode = '';
				use_formdata = 0;
			} //end if else
			break;
		case 2:
			if(allow_edit_num_markers > 0) {
				opmode = 'delete-markers';
				use_formdata = 1;
				staticmarkers = false; // detect dynamic markers
			} else {
				opmode = '';
				use_formdata = 0;
			} //end if else
			break;
		case 1:
			if(allow_edit_num_markers > 0) {
				opmode = 'add-markers';
				use_formdata = 1;
				staticmarkers = false; // detect dynamic markers
			} else {
				opmode = '';
				use_formdata = 0;
			} //end if else
			break;
		case 0: // display
		default:
			opmode = '';
			use_formdata = 0;
	} //end switch
	//--
	setMarkersFormData();
	setMarkAreasFormData();
	//--
	handleTheMeasures('');
	handleTheAreaDrawings();
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
var getTheMarkers = function(y_mode) {
	//--
	if(y_mode === '#') {
		//--
		var out = '';
		out = getMarkersFormData();
		alert('RESULT (markers): ' + '\n' + out);
		//--
	} //end if
	//--
	return export_arr_markers;
	//--
} //END FUNCTION
this.getMarkers = function(y_mode) {
	//--
	return getTheMarkers(y_mode);
	//--
} //END FUNCTION
//--

//--
var getTheMarkAreas = function(y_mode) {
	//--
	if(y_mode === '#') {
		//--
		var out = '';
		out = getMarkAreasFormData();
		alert('RESULT (areas): ' + '\n' + out);
		//--
	} //end if
	//--
	return export_arr_markareas;
	//--
} //END FUNCTION
this.getMarkAreas = function(y_mode) {
	//--
	return getTheMarkAreas(y_mode);
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

//--
this.SetAreasStyle = function(sColor, sOpacity, sWidth, fColor, fOpacity) {
	//--
	areasStyles = {
		strokeColor: '' + sColor,
		strokeOpacity: '' + parseFloat(sOpacity).toFixed(2),
		strokeWidth: '' + parseInt(sWidth),
		fillColor: '' + fColor,
		fillOpacity: '' + parseFloat(fOpacity).toFixed(2)
	};
	//--
} //END FUNCTION
//--

//== ALL BELOW ARE EXTERNAL HANDLERS THAT CAN BE REDEFINED PER INSTANCE

//--
this.Smart_Handler_SmartMaps_LoadMode = function(y_load_mode) {
	//--
	if(y_load_mode === 'dynamic') {
		staticmarkers = false;
	} else { // static (default)
		staticmarkers = true;
	} //end if else
	//--
} //END FUNCTION
//--

//--
this.Smart_Handler_SmartMaps_LoadData = function(y_init, y_markers, y_areas, y_lat, y_lon, y_zoom) {
	//--
	var redrawhandler;
	//--
	if(y_init == 'static') {
		//--
		redrawhandler = '[STATIC]';
		//--
		ClearMarkers();
		DrawMarkers(y_markers);
		//--
		ClearAreas();
		DrawMarkAreas(y_areas);
		//--
	} else if((y_init == 'init') && (is_map_changed <= 1)) { // avoid draw markers on map move (that can be done by extending with above function)
		//--
		redrawhandler = '[DYNAMIC-DRAW]';
		//--
		ClearMarkers();
		DrawMarkers(y_markers);
		//--
		ClearAreas();
		DrawMarkAreas(y_areas);
		//--
	} else {
		//--
		redrawhandler = '[DYNAMIC-KEEP]';
		//--
		var the_markers = new Array();
		the_markers = getTheMarkers();
		ClearMarkers();
		DrawMarkers(the_markers);
		the_markers = new Array();
		//--
		var the_markareas = new Array();
		the_markareas = getTheMarkAreas();
		ClearAreas();
		DrawMarkAreas(the_markareas);
		the_markareas = new Array();
		//--
	} //end if
	//--
	return redrawhandler;
	//--
} //END FUNCTION
//--

//--
this.Smart_Handler_SmartMaps_Click = function(divmap, map, markers, element_object, popin_coords, el_lat, el_lon, el_ttl, el_lnk, icon_sel_opacity, icon_sel_zindex, icon_def_opacity, icon_def_zindex) {
	//--
	//alert(SmartJS_CoreUtils.print_Object(element_object.feature.style));
	//--
	if(!element_object) {
		return;
	} //end if
	//--
	var show_popup = true;
	if(element_object.feature.style.cursor == 'pointer') {
		if(element_object.feature.style.label === '') {
			element_object.feature.style.fillOpacity = icon_sel_opacity;
			element_object.feature.style.label = '° ' + element_object.feature.style.graphicTitle;
			element_object.feature.style.graphicZIndex = icon_sel_zindex;
		} else {
			element_object.feature.style.label = '';
			element_object.feature.style.fillOpacity = icon_def_opacity;
			element_object.feature.style.graphicZIndex = icon_def_zindex;
			//show_popup = false;
		} //end if else
		markers.redraw();
	} //end if
	//--
	var found = -1;
	var pops = map.popups;
	if(pops) {
		for(var i=0; i<pops.length; i++) {
			if(map.popups[i]['id'] === divmap + '__smart_popup__' + element_object.feature.id) {
				found = i;
			} //end if
		} //end for
	} //end if
	//--
	if(show_popup) {
		//--
		if(found >= 0) {
			map.popups[found].show();
		} else {
			map.addPopup(
				new OpenLayers.Popup.FramedCloud(
				divmap + '__smart_popup__' + element_object.feature.id,
				popin_coords,
				new OpenLayers.Size(100, 100),
				'<small>' + 'Lat: ' + el_lat + ' / Lon: ' + el_lon + '<br>' + '<b>' + el_ttl + '</b>' + '<br>' + '<a href="' + el_lnk + '" target="_blank">' + el_lnk + '<a>' + '</small>',
				null, // anchor
				true // show close
				)
			);
		} //end if
		//--
	} else {
		//--
		if(found >= 0) {
			map.removePopup(map.popups[found]);
		} //end if
		//--
	} //end if
	//--
} //END FUNCTION
//--
this.Smart_Handler_SmartMaps_EndClick = function(divmap, map, markers, element_object, icon_sel_opacity, icon_sel_zindex, icon_def_opacity, icon_def_zindex) {
	//--
	// This will run for each marker on the map when the map is clicked, the event propagates through all markers
	//--
	//alert(SmartJS_CoreUtils.print_Object(element_object.feature.style));
	//--
	if(!element_object) {
		return;
	} //end if
	//--
	/*
	if(element_object.feature.style.cursor == 'pointer') {
		element_object.feature.style.label = '';
		element_object.feature.style.fillOpacity = icon_def_opacity;
		element_object.feature.style.graphicZIndex = icon_def_zindex;
		markers.redraw();
	} //end if
	*/
	//--
	var pops = map.popups;
	if(pops) {
		for(var i=0; i<pops.length; i++) {
			map.removePopup(map.popups[i]);
		} //end for
	} //end if
	//--
} //END FUNCTION
//--

//--
this.Smart_Handler_SmartMaps_Measure = function(order, measure, units) {
	//--
	if(order == 1) {
		out = 'Distance: ' + measure.toFixed(3) + ' ' + units;
	} else {
		out = 'Area Size: ' + measure.toFixed(3) + ' ' + units + '2';
	} //end if
	//--
	alert(out);
	//--
} //END FUNCTION
//--

//==

} //END OBJECT-CLASS
//==

// #END
