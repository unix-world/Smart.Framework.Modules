
// dhtmlxGantt - Tooltip Extension v.3.2.1
// (c) 2015 Dinamenta, UAB.
// License: GPLv2

// (c) 2017-2021 unix-world.org
// License: GPLv3
// v.20210411 (stable)
/*
modified by unixman:
	- changed text: Title instead of Task
	- isolate in a function
	- fix HTML escapings
	- fix max length of text
	- changed task structure [ start = start_date ; end = end_date ; title = text ]
*/

function SmartGanttPluginTooltip(gantt) {

	gantt._tooltip = {};
	gantt._tooltip_class = "gantt_tooltip";
	gantt.config.tooltip_timeout = 30;
	gantt.config.tooltip_offset_y = 20;
	gantt.config.tooltip_offset_x = -25;
		// timeout_to_hide: 50,
		// delta_x: 15,
		// delta_y: -20

	gantt._create_tooltip = function(){
		if (!this._tooltip_html){
			this._tooltip_html = document.createElement('div');
			this._tooltip_html.className = gantt._tooltip_class;
		}
		return this._tooltip_html;
	};

	gantt._is_cursor_under_tooltip = function(mouse_pos, tooltip) {
		if(mouse_pos.x >= tooltip.pos.x && mouse_pos.x <= (tooltip.pos.x + tooltip.width)) {
			return true;
		}
		if(mouse_pos.y >= tooltip.pos.y && mouse_pos.y <= (tooltip.pos.y + tooltip.height)) {
			return true;
		}
		return false;
	};

	gantt._show_tooltip = function(text, pos) {

		if(gantt.config.touch && !gantt.config.touch_tooltip) {
			return;
		}

		var tip = this._create_tooltip();

		tip.innerHTML = text;
		gantt.$task_data.appendChild(tip);

		var width = tip.offsetWidth + 20;
		var height = tip.offsetHeight + 40;
		var max_height = this.$task.offsetHeight;
		var max_width = this.$task.offsetWidth;
		var scroll = this.getScrollState();

		//pos.x += scroll.x;
		pos.y += scroll.y;

		var mouse_pos = {
			x: pos.x,
			y: pos.y
		};

		pos.x += (gantt.config.tooltip_offset_x*1 || 0);
		pos.y += (gantt.config.tooltip_offset_y*1 || 0);

		pos.y = Math.min(Math.max(scroll.y, pos.y), scroll.y+max_height - height);
		pos.x = Math.min(Math.max(scroll.x, pos.x), scroll.x+max_width - width);

		if (gantt._is_cursor_under_tooltip(mouse_pos, {pos: pos, width: width, height: height})) {
			if((mouse_pos.x+width) > (max_width + scroll.x)) pos.x = mouse_pos.x - (width - 20) - (gantt.config.tooltip_offset_x*1 || 0);
			if((mouse_pos.y+height) > (max_height + scroll.y)) pos.y = mouse_pos.y - (height - 40) - (gantt.config.tooltip_offset_y*1 || 0);
		}

		tip.style.left = pos.x + "px";
		tip.style.top  = pos.y + "px";
	};

	gantt._hide_tooltip = function(){
		if(this._tooltip_html && this._tooltip_html.parentNode) {
			this._tooltip_html.parentNode.removeChild(this._tooltip_html);
		}
		this._tooltip_id = 0;
	};

	gantt._is_tooltip = function(ev) {
		var node = ev.target || ev.srcElement;
		return gantt._is_node_child(node, function(node){
			return (node.className == this._tooltip_class);
		});
	};

	gantt._is_task_line = function(ev){
		var node = ev.target || ev.srcElement;
		return gantt._is_node_child(node, function(node){
			return (node == this.$task_data);
		});
	};

	gantt._is_node_child = function(node, condition){
		var res = false;
		while (node && !res) {
			res = condition.call(gantt, node);
			node = node.parentNode;
		}
		return res;
	};

	gantt._tooltip_pos = function(ev) {
		if(ev.pageX || ev.pageY) {
			var pos = {x:ev.pageX, y:ev.pageY};
		}
		var d = gantt._browserIE ? document.documentElement : document.body;
		var pos = {
			x:ev.clientX + d.scrollLeft - d.clientLeft,
			y:ev.clientY + d.scrollTop - d.clientTop
		};
		var box = gantt._get_position(gantt.$task_data);
		pos.x = pos.x - box.x;
		pos.y = pos.y - box.y;
		return pos;
	};

	gantt.attachEvent("onMouseMove", function(event_id, ev) { // (gantt event_id, browser event)
		if(this.config.tooltip_timeout){
			//making events survive timeout in ie
			if(document.createEventObject && !document.createEvent) {
				ev = document.createEventObject(ev);
			}
			var delay = this.config.tooltip_timeout;
			if(this._tooltip_id && !event_id){
				if(!isNaN(this.config.tooltip_hide_timeout)){
					delay = this.config.tooltip_hide_timeout;
				}
			}
			clearTimeout(gantt._tooltip_ev_timer);
			gantt._tooltip_ev_timer = setTimeout(function(){
				gantt._init_tooltip(event_id, ev);
			}, delay);
		} else {
			gantt._init_tooltip(event_id, ev);
		}
	});

	gantt._init_tooltip = function(event_id, ev){
		if(this._is_tooltip(ev)) {
			return;
		}
		if(event_id == this._tooltip_id && !this._is_task_line(ev)) {
			return;
		}
		if(!event_id) {
			return this._hide_tooltip();
		}
		this._tooltip_id = event_id;
		var task = this.getTask(event_id);
		var text = this.templates.tooltip_text(task.start, task.end, task);
		if(!text){
			this._hide_tooltip();
			return;
		}
		this._show_tooltip(text, this._tooltip_pos(ev));
	};

	gantt.attachEvent("onMouseLeave", function(ev){
		if(gantt._is_tooltip(ev)) {
			return;
		}
		this._hide_tooltip();
	});

	// gantt.attachEvent("onBeforeDrag", function() {
	// 	gantt._tooltip.hide();
	// 	return true;
	// });
	// gantt.attachEvent("onEventDeleted", function() {
	// 	gantt._tooltip.hide();
	// 	return true;
	// });


	/* Could be redefined */
	gantt.templates.tooltip_date_format = gantt.date.date_to_str("%Y-%m-%d");
	gantt.templates.tooltip_text = function(start, end, event) {
		var endTxt = '';
		if(event.type == 'flextask' && event.progress < 1) {
			endTxt = '*'; // {{{SYNC-FLEXTASK-END-TXT}}}
		} else if(event.type == 'milestone') {
			endTxt = '@'; // {{{SYNC-MILESTONE-END-TXT}}}
		} else {
			endTxt = gantt.templates.tooltip_date_format(end);
		} //end if else
		var txt = String(event.title || '');
		if(txt.length > 50) {
			txt = txt.substr(0, 50) + '...';
		} //end if
		return '<b>Title:</b> ' + smartJ$Utils.escape_html(txt) + '<br><b>Start date:</b> ' + smartJ$Utils.escape_html(gantt.templates.tooltip_date_format(start)) + '<br><b>End date:</b> ' + smartJ$Utils.escape_html(endTxt);
	}; // fix by unixman

	return gantt;

} //END FUNCTION

// #END
