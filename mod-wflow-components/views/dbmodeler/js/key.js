
// wwwsqldesigner v.1.7: key.js (db index)
// (c) 2005-2018, Ondrej Zara
// License: BSD

// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

SQL.Key = function(owner, type, name) {
	this.owner = owner;
	this.rows = [];
	this.type = type || "INDEX";
	this.name = name || "";
	SQL.Visual.apply(this);
}
SQL.Key.prototype = Object.create(SQL.Visual.prototype);

SQL.Key.prototype.setName = function(n) {
	this.name = n;
}

SQL.Key.prototype.getName = function() {
	return this.name;
}

SQL.Key.prototype.setType = function(t) {
	if (!t) { return; }
	this.type = t;
	for (var i=0;i<this.rows.length;i++) { this.rows[i].redraw(); }
}

SQL.Key.prototype.getType = function() {
	return this.type;
}

SQL.Key.prototype.addRow = function(r) {
	if (r.owner != this.owner) { return; }
	this.rows.push(r);
	r.addKey(this);
}

SQL.Key.prototype.removeRow = function(r) {
	var idx = this.rows.indexOf(r);
	if (idx == -1) { return; }
	r.removeKey(this);
	this.rows.splice(idx,1);
}

SQL.Key.prototype.destroy = function() {
	for (var i=0;i<this.rows.length;i++) {
		this.rows[i].removeKey(this);
	}
}

SQL.Key.prototype.getLabel = function() {
	return this.name || this.type;
}

SQL.Key.prototype.toXML = function() {
	var xml = "";
	xml += '<key type="'+this.getType()+'" name="'+SQL.escape(this.getName())+'">\n'; // unixman fixed with SQL.escape()
	for (var i=0;i<this.rows.length;i++) {
		var r = this.rows[i];
		xml += '<part>'+SQL.escape(r.getTitle())+'</part>\n'; // unixman fixed with SQL.escape()
	}
	xml += '</key>\n';
	return xml;
}

SQL.Key.prototype.fromXML = function(node) {
	this.setType(node.getAttribute("type"));
	this.setName(node.getAttribute("name"));
	var parts = node.getElementsByTagName("part");
	for (var i=0;i<parts.length;i++) {
		if(parts[i] && parts[i].firstChild) { // fix by unixman
			var name = parts[i].firstChild.nodeValue;
			var row = this.owner.findNamedRow(name);
			this.addRow(row);
		} //end if
	}
}

// #END
