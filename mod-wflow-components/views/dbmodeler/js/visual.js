
// wwwsqldesigner v.1.7: visual.js (base visual elements)
// (c) 2005-2018, Ondrej Zara
// License: BSD

// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

SQL.Visual = function() {
	this._init();
	this._build();
}

SQL.Visual.prototype._init = function() {
	this.dom = {
		container: null,
		title: null
	};
	this.data = {
		title: ''
	}
}

SQL.Visual.prototype._build = function() {}

SQL.Visual.prototype.toXML = function() {}

SQL.Visual.prototype.fromXML = function(node) {}

SQL.Visual.prototype.destroy = function() { /* "destructor" */
	var p = this.dom.container.parentNode;
	if (p && p.nodeType == 1) {
		p.removeChild(this.dom.container);
	}
}

SQL.Visual.prototype.setTitle = function(text) {
	if(!text) {
		return;
	} //end if
	this.data.title = text;
	this.dom.title.innerHTML = SQL.escape(text); // fix by unixman, add escape
}

SQL.Visual.prototype.getTitle = function() {
	return this.data.title || '';
}

SQL.Visual.prototype.redraw = function() {}

// #END
