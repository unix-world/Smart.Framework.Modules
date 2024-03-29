
// dhtmlxGantt - QuickInfo Extension v.3.2.1
// (c) 2015 Dinamenta, UAB.
// License: GPLv2

// (c) 2017-2021 unix-world.org
// License: GPLv3
// v.20210411 (stable)
/*
modified by unixman:
	- isolate in a function
	- fix HTML escapings
	- fix max length of text
	- changed task structure [ start = start_date ; end = end_date ; title = text ]
*/

function SmartGanttPluginQuickinfo(gantt) {

	gantt.config.quickinfo_buttons = ["icon_edit", "icon_delete"];
	gantt.config.quick_info_detached = true;
	gantt.config.show_quick_info = true;

	gantt.attachEvent("onTaskClick", function(id){
		gantt.showQuickInfo(id);
		return true;
	});

	(function(){
		var events = ["onEmptyClick", "onViewChange", "onLightbox", "onBeforeTaskDelete", "onBeforeDrag"];
		var hiding_function = function(){
			gantt._hideQuickInfo();
			return true;
		};
		for (var i=0; i<events.length; i++) {
			gantt.attachEvent(events[i], hiding_function);
		}
	})();

	gantt.templates.quick_info_title = function(start, end, ev){
		var txt = String(ev.title || '');
		if(txt.length > 50) {
			txt = txt.substr(0, 37) + '...';
		} //end if
		return smartJ$Utils.escape_html(txt);
	};
	gantt.templates.quick_info_content = function(start, end, ev){
		var txt = String(ev.details || '');
		if(txt.length > 50) {
			txt = txt.substr(0, 120) + '...'; // 40 * 3 lines
		} //end if
		return smartJ$Utils.escape_html(txt);
	};
	gantt.templates.quick_info_date = function(start, end, ev){
		return gantt.templates.task_time(start, end, ev);
	};
	gantt.templates.quick_info_class = function(start, end, task){
		return '';
	};

	gantt.showQuickInfo = function(id){
		if (id == this._quick_info_box_id || !this.config.show_quick_info) return;
		this.hideQuickInfo(true);
		var pos = this._get_event_counter_part(id);
		if(pos){
			this._quick_info_box = this._init_quick_info(pos, id);
			this._quick_info_box.className = gantt._prepare_quick_info_classname(id);
			this._fill_quick_data(id);
			this._show_quick_info(pos);
		}
	};
	gantt._hideQuickInfo = function(){
		gantt.hideQuickInfo();
	};
	gantt.hideQuickInfo = function(forced){
		var qi = this._quick_info_box;
		this._quick_info_box_id = 0;
		if(qi && qi.parentNode){
			if(gantt.config.quick_info_detached) {
				return qi.parentNode.removeChild(qi);
			}
			qi.className += " gantt_qi_hidden";
			if(qi.style.right == "auto") {
				qi.style.left = "-350px";
			} else {
				qi.style.right = "-350px";
			}
			if(forced) {
				qi.parentNode.removeChild(qi);
			}
		}
	};
	dhtmlxEvent(window, "keydown", function(e){
		if (e.keyCode == 27) {
			gantt.hideQuickInfo();
		}
	});

	gantt._show_quick_info = function(pos){
		var qi = gantt._quick_info_box;

		if (gantt.config.quick_info_detached){
			if(!qi.parentNode || qi.parentNode.nodeName.toLowerCase() == "#document-fragment") { //IE8
				gantt.$task_data.appendChild(qi);
			}
			var width = qi.offsetWidth;
			var height = qi.offsetHeight;
			var scrolls = this.getScrollState();
			var screen_width = this.$task.offsetWidth + scrolls.x - width;
			//-- # fix by unixman
		//	qi.style.left = Math.min(Math.max(scrolls.x, pos.left - (pos.dx * (width - pos.width))), screen_width) + "px";
			qi.style.left = Math.min((scrolls.x + pos.left), (scrolls.x + width)) + "px";
			//-- #end fix my unixman
			qi.style.top = pos.top - (pos.dy ? height : -pos.height) - 25 + "px";
		} else {
			qi.style.top = 20 + "px";
			if (pos.dx == 1){
				qi.style.right = "auto";
				qi.style.left = "-300px";
				setTimeout(function(){
					qi.style.left = "-10px";
				},1);
			} else {
				qi.style.left = "auto";
				qi.style.right = "-300px";
				setTimeout(function(){
					qi.style.right = "-10px";
				},1);
			}
			qi.className += " gantt_qi_"+(pos.dx == 1 ? "left" : "right");
			gantt._obj.appendChild(qi);
		}
	};
	gantt._prepare_quick_info_classname = function(id){
		var task = gantt.getTask(id);
		var css = "gantt_cal_quick_info",
			template = this.templates.quick_info_class(task.start, task.end, task);
		if(template){
			css += " " + template;
		}
		return css;
	};

	gantt._init_quick_info = function(pos, id){

		var task = gantt.getTask(id);

		if(typeof this._quick_info_readonly == "boolean"){
			if(this._is_readonly(task) !== this._quick_info_readonly){
				gantt.hideQuickInfo(true);
				this._quick_info_box = null;
			}
		}

		this._quick_info_readonly = this._is_readonly(task);

		if(!this._quick_info_box){

			var qi = this._quick_info_box = document.createElement("div");

			//title
			var html = '<div class="gantt_cal_qi_title">' +
				'<div class="gantt_cal_qi_tcontent"></div><div class="gantt_cal_qi_tdate"></div>' +
				'</div>' +
				'<div class="gantt_cal_qi_content"></div>';

			//buttons
			html += '<div class="gantt_cal_qi_controls">';
			var buttons = gantt.config.quickinfo_buttons;

			var is_editor = {"icon_edit":true, "icon_delete":true};

			for(var i = 0; i < buttons.length; i++){
				if(this._quick_info_readonly && is_editor[buttons[i]]) {
					continue;
				}
				html += '<div class="gantt_qi_big_icon ' + smartJ$Utils.escape_html(buttons[i]) + '" title="' + smartJ$Utils.escape_html(gantt.locale.labels[buttons[i]]) + '"><div class="gantt_menu_icon ' + smartJ$Utils.escape_html(buttons[i]) + '"></div><div>' + smartJ$Utils.escape_html(gantt.locale.labels[buttons[i]]) + '</div></div>';
			}
			html += '</div>';

			qi.innerHTML = html;
			dhtmlxEvent(qi, "click", function(ev){
				ev = ev || event;
				gantt._qi_button_click(ev.target || ev.srcElement);
			});
			if(gantt.config.quick_info_detached) {
				dhtmlxEvent(gantt.$task_data, "scroll", function(){ gantt.hideQuickInfo(); });
			}

		}

		return this._quick_info_box;
	};

	gantt._qi_button_click = function(node){
		var box = gantt._quick_info_box;
		if (!node || node == box) return;

		var mask = node.className;
		if (mask.indexOf("_icon")!=-1){
			var id = gantt._quick_info_box_id;
			gantt.$click.buttons[mask.split(" ")[1].replace("icon_","")](id);
		} else
			gantt._qi_button_click(node.parentNode);
	};
	gantt._get_event_counter_part = function(id){
		var domEv = gantt.getTaskNode(id);
		var left = 0;
		var top = 0;

		var node = domEv;
		while (node && node.className != "gantt_task"){
			left += node.offsetLeft;
			top += node.offsetTop;
			node = node.offsetParent;
		}
		var scroll = this.getScrollState();
		if(node){
			var dx = (left + domEv.offsetWidth/2) - scroll.x > (gantt._x/2) ? 1 : 0;
			var dy = (top + domEv.offsetHeight/2) - scroll.y > (gantt._y/2) ? 1 : 0;
			return {
				left:left,
				top:top,
				dx:dx,
				dy:dy,
				width:domEv.offsetWidth,
				height:domEv.offsetHeight
			};
		}
		return 0;
	};

	gantt._fill_quick_data  = function(id){
		var ev = gantt.getTask(id);
		var qi = gantt._quick_info_box;
		gantt._quick_info_box_id = id;
		//title content
		var titleContent = qi.firstChild.firstChild;
		titleContent.innerHTML = gantt.templates.quick_info_title(ev.start, ev.end, ev);
		var titleDate = titleContent.nextSibling;
		titleDate.innerHTML = gantt.templates.quick_info_date(ev.start, ev.end, ev);
		//main content
		var main = qi.firstChild.nextSibling;
		main.innerHTML = gantt.templates.quick_info_content(ev.start, ev.end, ev);
	};

	return gantt;

} //END FUNCTION

// #END
