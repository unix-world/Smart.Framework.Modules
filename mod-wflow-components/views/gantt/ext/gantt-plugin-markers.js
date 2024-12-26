
// dhtmlxGantt - Marker Extension v.3.2.1
// (c) 2015 Dinamenta, UAB.
// License: GPLv2

// (c) 2017-2021 unix-world.org
// License: GPLv3
// v.20210411 (stable)
/*
modified by unixman:
	- isolate in a function
	- fix HTML escapings
	- changed marker.title as marker.name (as the below change w. title is mapped directly from task ...)
	- changed task structure [ start = start_date ; end = end_date ; title = text ]
*/

function SmartGanttPluginMarkers(gantt) {

	if(!gantt._markers) {
		gantt._markers = {};
	}

	gantt.config.show_markers = true;

	gantt.attachEvent("onClear", function(){
		gantt._markers = {};
	});

	gantt.attachEvent("onGanttReady", function(){
		var markerArea = document.createElement("div");
		markerArea.className = "gantt_marker_area";
		gantt.$task_data.appendChild(markerArea);
		gantt.$marker_area = markerArea;

		gantt._markerRenderer = gantt._task_renderer("markers", render_marker, gantt.$marker_area, null);

		function render_marker(marker){
			if(!gantt.config.show_markers) {
				return false;
			}
			if(!marker.start) {
				return false;
			}
			var state = gantt.getState();
			if(+marker.start > +state.max_date) {
				return;
			}
			if(+marker.end && +marker.end < +state.min_date || +marker.start < +state.min_date) {
				return;
			}
			var div = document.createElement("div");
			div.setAttribute("marker_id", marker.id);
			var css = "gantt_marker";
			if(gantt.templates.marker_class) {
				css += " " + gantt.templates.marker_class(marker);
			}
			if(marker.css) {
				css += " " + marker.css;
			}
			if(marker.name) {
				div.title = smartJ$Utils.escape_html(marker.name);
			}
			div.className = css;
			var start = gantt.posFromDate(marker.start);
			div.style.left = start + "px";
			div.style.height = Math.max(gantt._y_from_ind(gantt._order.length), 0) + "px";
			if(marker.end) {
				var end = gantt.posFromDate(marker.end);
				div.style.width = Math.max((end - start), 0) + "px";
			}
			if(marker.title){
				div.innerHTML = '<div class="gantt_marker_content">' + smartJ$Utils.escape_html(marker.title) + '</div>';
			}
			return div;
		}
	});


	gantt.attachEvent("onDataRender", function(){
		gantt.renderMarkers();
	});

	gantt.getMarker = function(id){
		if(!this._markers) {
			return null;
		}
		return this._markers[id];
	};

	gantt.addMarker = function(marker){
		marker.id = marker.id || dhtmlx.uid(); // unixman: ok numeric UUID
		this._markers[marker.id] = marker;
		return marker.id;
	};

	gantt.deleteMarker = function(id){
		if(!this._markers || !this._markers[id]) {
			return false;
		}
		delete this._markers[id];
		return true;
	};

	gantt.updateMarker = function(id){
		if(this._markerRenderer) {
			this._markerRenderer.render_item(id);
		}
	};

	gantt.renderMarkers = function(){
		if(!this._markers) {
			return false;
		}
		if(!this._markerRenderer) {
			return false;
		}
		var to_render = [];
		for(var id in this._markers) {
			to_render.push(this._markers[id]);
		}
		this._markerRenderer.render_items(to_render);
		return true;
	};

	return gantt;

} //END FUNCTION

// #END
