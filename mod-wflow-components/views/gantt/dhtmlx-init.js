
// dhtmlx Objects v.3.2.1
// License: GPLv2
// (c) 2015 Dinamenta, UAB.

// (c) 2017-2020 unix-world.org
// License: GPLv3
// v.20200701 (stable)
/*
modified by unixman:
	- separed the dhtmlx object and functions from gantt (init) object
	- contains some functions ported from gantt that need to be re-exported
*/

if(typeof(window.dhtmlx) == "undefined") {

	window.dhtmlx = {
		extend: function(a, b){
			for(var key in b)
				if (!a[key])
					a[key]=b[key];
			return a;
		},
		extend_api: function(name,map,ext){
			var t = window[name];
			if (!t) return; //component not defined
			window[name] = function(obj) {
				if(obj && typeof obj == "object" && !obj.tagName) {
					var that = t.apply(this,(map._init?map._init(obj):arguments));
					//global settings
					for (var a in dhtmlx)
						if (map[a]) this[map[a]](dhtmlx[a]);
					//local settings
					for(var a in obj){
						if(map[a]) {
							this[map[a]](obj[a]);
						} else if(a.indexOf("on")===0) {
							this.attachEvent(a,obj[a]);
						} //end if else
					} //end for
				} else {
					var that = t.apply(this,arguments);
				} //end if else
				if(map._patch) {
					map._patch(this);
				} //end if
				return that||this;
			};
			window[name].prototype=t.prototype;
			if(ext) {
				dhtmlx.extend(window[name].prototype, ext);
			} //end if
		},
		url: function(str){
			if(str.indexOf("?") != -1) {
				return "&";
			} else {
				return "?";
			} //end if else
		}
	};
};

if(typeof(window.dhtmlxEvent) == "undefined") {
	function dhtmlxEvent(el, event, handler){
		if(el.addEventListener) {
			el.addEventListener(event, handler, false);
		} else if(el.attachEvent) {
			el.attachEvent("on"+event, handler);
		} //end if else
	} //end function
};

if(dhtmlxEvent.touchDelay == null) {
	dhtmlxEvent.touchDelay = 2000;
};

if(typeof(dhtmlxEvent.initTouch) == "undefined") {

	dhtmlxEvent.initTouch = function() {

		var longtouch;
		var target;
		var tx, ty;

		dhtmlxEvent(document.body, "touchstart", function(ev){
			target = ev.touches[0].target;
			tx = ev.touches[0].clientX;
			ty = ev.touches[0].clientY;
			longtouch = window.setTimeout(touch_event, dhtmlxEvent.touchDelay);
		});
		function touch_event(){
			if(target){
				var ev = document.createEvent("HTMLEvents"); // for chrome and firefox
				ev.initEvent("dblclick", true, true);
				target.dispatchEvent(ev);
				longtouch = target = null;
			}
		};
		dhtmlxEvent(document.body, "touchmove", function(ev){
			if(longtouch){
				if (Math.abs(ev.touches[0].clientX - tx) > 50 || Math.abs(ev.touches[0].clientY - ty) > 50 ){
					window.clearTimeout(longtouch);
					longtouch = target = false;
				}
			}
		});
		dhtmlxEvent(document.body, "touchend", function(ev){
			if(longtouch){
				window.clearTimeout(longtouch);
				longtouch = target = false;
			}
		});

		dhtmlxEvent.initTouch = function(){};

	};

};

if(!window.dhtmlx) {
	window.dhtmlx = {};
} //end if

(function(){

	var _dhx_msg_cfg = null;

	function callback(config, result) {
			var usercall = config.callback;
			modality(false);
			config.box.parentNode.removeChild(config.box);
			_dhx_msg_cfg = config.box = null;
			if(usercall) {
				usercall(result);
			}
	}

	function modal_key(e) {
		if(_dhx_msg_cfg) {
			e = e || event;
			var code = e.which || event.keyCode;
			if(dhtmlx.message.keyboard) {
				if(code == 13 || code == 32) {
					callback(_dhx_msg_cfg, true);
				}
				if(code == 27) {
					callback(_dhx_msg_cfg, false);
				}
			}
			if(e.preventDefault) {
				e.preventDefault();
			}
			return !(e.cancelBubble = true);
		}
	}

	if(document.attachEvent) {
		document.attachEvent("onkeydown", modal_key);
	} else {
		document.addEventListener("keydown", modal_key, true);
	}

	function modality(mode){
		if(!modality.cover){
			modality.cover = document.createElement("DIV");
			//necessary for IE only
			modality.cover.onkeydown = modal_key;
			modality.cover.className = "dhx_modal_cover";
			document.body.appendChild(modality.cover);
		}
		var height =  document.body.scrollHeight;
		modality.cover.style.display = mode ? "inline-block" : "none";
	}

	function button(text, result){
		var button_css = "dhtmlx_"+text.toLowerCase().replace(/ /g, "_")+"_button"; // dhtmlx_ok_button, dhtmlx_click_me_button
		return "<div class='dhtmlx_popup_button "+button_css+"' result='"+result+"' ><div>"+text+"</div></div>";
	}

	function info(text){

		if(!t.area){
			t.area = document.createElement("DIV");
			t.area.className = "dhtmlx_message_area";
			t.area.style[t.position]="5px";
			document.body.appendChild(t.area);
		}

		t.hide(text.id);
		var message = document.createElement("DIV");
		message.innerHTML = "<div>"+text.text+"</div>";
		message.className = "dhtmlx-info dhtmlx-" + text.type;
		message.onclick = function(){
			t.hide(text.id);
			text = null;
		};

		if(t.position == "bottom" && t.area.firstChild) {
			t.area.insertBefore(message,t.area.firstChild);
		} else {
			t.area.appendChild(message);
		}

		if(text.expire > 0) {
			t.timers[text.id]=window.setTimeout(function(){
				t.hide(text.id);
			}, text.expire);
		}

		t.pull[text.id] = message;
		message = null;

		return text.id;

	}

	function _boxStructure(config, ok, cancel) {

		var box = document.createElement("DIV");

		box.className = " dhtmlx_modal_box dhtmlx-"+config.type;
		box.setAttribute("dhxbox", 1);

		var inner = '';

		if(config.width) {
			box.style.width = config.width;
		}
		if(config.height) {
			box.style.height = config.height;
		}
		if(config.title) {
			inner+='<div class="dhtmlx_popup_title">'+config.title+'</div>';
		}
		inner+='<div class="dhtmlx_popup_text"><span>'+(config.content?'':config.text)+'</span></div><div  class="dhtmlx_popup_controls">';
		if(ok) {
			inner += button(config.ok || "OK", true);
		}
		if(cancel) {
			inner += button(config.cancel || "Cancel", false);
		}
		if(config.buttons) {
			for(var i=0; i<config.buttons.length; i++) {
				inner += button(config.buttons[i],i);
			}
		}
		inner += '</div>';
		box.innerHTML = inner;

		if(config.content){
			var node = config.content;
			if(typeof node == "string") {
				node = document.getElementById(node);
			}
			if(node.style.display == 'none') {
				node.style.display = "";
			}
			box.childNodes[config.title?1:0].appendChild(node);
		}

		box.onclick = function(e){
			e = e ||event;
			var source = e.target || e.srcElement;
			if(!source.className) {
				source = source.parentNode;
			}
			if(source.className.split(" ")[0] == "dhtmlx_popup_button") {
				var result = source.getAttribute("result");
				result = (result == "true") || (result == "false"?false:result);
				callback(config, result);
			}
		};

		config.box = box;

		if(ok || cancel) {
			_dhx_msg_cfg = config;
		}

		return box;

	}

	function _createBox(config, ok, cancel) {

		var box = config.tagName ? config : _boxStructure(config, ok, cancel);

		if(!config.hidden) {
			modality(true);
		}

		document.body.appendChild(box);
		var x = Math.abs(Math.floor(((window.innerWidth||document.documentElement.offsetWidth) - box.offsetWidth)/2));
		var y = Math.abs(Math.floor(((window.innerHeight||document.documentElement.offsetHeight) - box.offsetHeight)/2));

		if(config.position == "top") {
			box.style.top = "-3px";
		} else {
			box.style.top = y+'px';
		}

		box.style.left = x+'px';
		//necessary for IE only
		box.onkeydown = modal_key;

		box.focus();
		if(config.hidden) {
			dhtmlx.modalbox.hide(box);
		}

		return box;

	}

	function alertPopup(config){
		return _createBox(config, true, false);
	}

	function confirmPopup(config){
		return _createBox(config, true, true);
	}

	function boxPopup(config){
		return _createBox(config);
	}

	function box_params(text, type, callback){
		if(typeof text != "object") {
			if(typeof type == "function") {
				callback = type;
				type = "";
			}
			text = {text:text, type:type, callback:callback };
		}
		return text;
	}

	function params(text, type, expire, id){
		if(typeof text != "object") {
			text = {text:text, type:type, expire:expire, id:id};
		}
		text.id = text.id||t.uid();
		text.expire = text.expire||t.expire;
		return text;
	}

	dhtmlx.alert = function(){
		var text = box_params.apply(this, arguments);
		text.type = text.type || "confirm";
		return alertPopup(text);
	};

	dhtmlx.confirm = function(){
		var text = box_params.apply(this, arguments);
		text.type = text.type || "alert";
		return confirmPopup(text);
	};

	dhtmlx.modalbox = function(){
		var text = box_params.apply(this, arguments);
		text.type = text.type || "alert";
		return boxPopup(text);
	};

	dhtmlx.modalbox.hide = function(node){
		while (node && node.getAttribute && !node.getAttribute("dhxbox"))
			node = node.parentNode;
		if (node){
			node.parentNode.removeChild(node);
			modality(false);
		}
	};

	var t = dhtmlx.message = function(text, type, expire, id) {
		text = params.apply(this, arguments);
		text.type = text.type||"info";
		var subtype = text.type.split("-")[0];
		switch(subtype){
			case "alert":
				return alertPopup(text);
				break;
			case "confirm":
				return confirmPopup(text);
				break;
			case "modalbox":
				return boxPopup(text);
				break;
			default:
				return info(text);
		}
	};

	t.seed = (new Date()).valueOf();
	t.uid = function(){
		return t.seed++;
	};
	t.expire = 4000;
	t.keyboard = true;
	t.position = "top";
	t.pull = {};
	t.timers = {};
	t.hideAll = function() {
		for(var key in t.pull) {
			t.hide(key);
		} //end for
	};
	t.hide = function(id) {
		var obj = t.pull[id];
		if(obj && obj.parentNode) {
			window.setTimeout(function(){
				obj.parentNode.removeChild(obj);
				obj = null;
			},2000);
			obj.className += " hidden";
			if(t.timers[id]) {
				window.clearTimeout(t.timers[id]);
			}
			delete t.pull[id];
		}
	};

})();

/*jsl:ignore*/
//import from dhtmlxcommon.js

function dhtmlxDetachEvent(el, event, handler){
	if(el.removeEventListener) {
		el.removeEventListener(event, handler, false);
	} else if(el.detachEvent) {
		el.detachEvent("on"+event, handler);
	}
} //END FUNCTION


/** Overrides event functionality.
 *  Includes all default methods from dhtmlx.common but adds _silentStart, _silendEnd
 *   @desc:
 *   @type: private
 */
dhtmlxEventable = function(obj) {
	obj._silent_mode = false;
	obj._silentStart = function() {
		this._silent_mode = true;
	};
	obj._silentEnd = function() {
		this._silent_mode = false;
	};
	obj.attachEvent = function(name, catcher, callObj){
		name='ev_'+name.toLowerCase();
		if(!this[name]) {
			this[name]=new this._eventCatcher(callObj||this);
		}
		return(name+':'+this[name].addEvent(catcher)); //return ID (event name & event ID)
	};
	obj.callEvent = function(name, arg0){
		if(this._silent_mode) {
			return true;
		}
		name='ev_'+name.toLowerCase();
		if(this[name]) {
			return this[name].apply(this, arg0);
		}
		return true;
	};
	obj.checkEvent = function(name){
		return (!!this['ev_'+name.toLowerCase()]);
	};
	obj._eventCatcher = function(obj){
		var dhx_catch = [];
		var z = function(){
			var res = true;
			for(var i=0; i<dhx_catch.length; i++){
				if(dhx_catch[i]) {
					var zr = dhx_catch[i].apply(obj, arguments);
					res = res && zr;
				}
			}
			return res;
		};
		z.addEvent = function(ev){
			if(typeof (ev) != "function") {
				ev = eval(ev);
			}
			if(ev) {
				return dhx_catch.push(ev) - 1;
			}
			return false;
		};
		z.removeEvent = function(id){
			dhx_catch[id] = null;
		};
		return z;
	};
	obj.detachEvent = function(id){
		if(id){
			var list = id.split(':'); //get EventName and ID
			this[list[0]].removeEvent(list[1]); //remove event
		}
	};
	obj.detachAllEvents = function(){
		for(var name in this){
			if(name.indexOf("ev_") === 0) {
				delete this[name];
			}
		}
	};
	obj = null;
};


/*jsl:end*/


dhtmlx.copy = function(object) {
	var i, t, result; // iterator, types array, result
	if (object && typeof object == "object") {
		result = {};
		t = [Array,Date,Number,String,Boolean];
		for (i=0; i<t.length; i++) {
			if (object instanceof t[i]) {
				result = i ? new t[i](object) : new t[i](); // first one is array
			}
		}
		for (i in object) {
			if (Object.prototype.hasOwnProperty.apply(object, [i])) {
				result[i] = dhtmlx.copy(object[i]);
			}
		}
	}
	return result || object;
};

dhtmlx.mixin = function(target, source, force){
	for(var f in source) {
		if((!target[f] || force)) {
			target[f]=source[f];
		}
	}
	return target;
};


dhtmlx.defined = function(obj) {
	return typeof(obj) != "undefined";
};

dhtmlx.uid = function() {
	if(!this._seed) {
		this._seed = (new Date()).valueOf();
	}
	this._seed++;
	return this._seed;
};

//creates function with specified "this" pointer
dhtmlx.bind = function(functor, object){
	if(functor.bind) {
		return functor.bind(object);
	} else {
		return function(){ return functor.apply(object,arguments); };
	}
};

//-- fixes by unixman: re-exported functions ...

dhtmlx._browserFFox 	= (navigator.userAgent.indexOf('Firefox') >= 0);
dhtmlx._browserIE 		= (navigator.userAgent.indexOf('MSIE') >= 0 || navigator.userAgent.indexOf('Trident') >= 0);
dhtmlx._browserChrome 	= (navigator.userAgent.indexOf('Chrome') >= 0) || (navigator.userAgent.indexOf('Chromium') >= 0);
dhtmlx._isOpera 		= (navigator.userAgent.indexOf('Opera') >= 0);

dhtmlx.fix_DateNumbers = function(num) {
	if(num < 10){
		return '0' + num;
	}
	return num;
};

dhtmlx.get_ISOWeek = function(ndate) {
	if(!ndate) {
		return false;
	}
	var nday = ndate.getDay();
	if(nday === 0) {
		nday = 7;
	}
	var first_thursday = new Date(ndate.valueOf());
	first_thursday.setDate(ndate.getDate() + (4 - nday));
	var year_number = first_thursday.getFullYear(); // year of the first Thursday
	var ordinal_date = Math.round((first_thursday.getTime() - new Date(year_number, 0, 1).getTime()) / 86400000); //ordinal date of the first Thursday - 1 (so not really ordinal date)
	var week_number = 1 + Math.floor(ordinal_date / 7);
	return week_number;
};

dhtmlx.date_Locales = {
	month_full:['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	month_short:['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
	day_full:['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
	day_short:['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
};
dhtmlx.date_Locales.month_full_hash = {}; // {{{SYNC-DT-MONTH-HASHES}}}
dhtmlx.date_Locales.month_short_hash = {}; // {{{SYNC-DT-MONTH-HASHES}}}

// #END
