
// wwwsqldesigner v.1.7: rowmanager.js
// (c) 2005-2018, Ondrej Zara
// License: BSD

// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

SQL.RowManager = function(owner) {
	this.owner = owner;
	this.dom = {};
	this.selected = null;
	this.creating = false;
	this.connecting = false;

	var ids = ["editrow","removerow","uprow","downrow","foreignconnect","foreigndisconnect","foreigncreate"];
	for (var i=0;i<ids.length;i++) {
		var id = ids[i];
		var elm = OZ.$(id);
		this.dom[id] = elm;
		try {
			elm.value = dbModelerLocalText(id);
		} catch(err){}
	}

	this.select(false);

	OZ.Event.add(this.dom.editrow, "click", this.edit.bind(this));
	OZ.Event.add(this.dom.uprow, "click", this.up.bind(this));
	OZ.Event.add(this.dom.downrow, "click", this.down.bind(this));
	OZ.Event.add(this.dom.removerow, "click", this.remove.bind(this));
	try {
		OZ.Event.add(this.dom.foreigncreate, "click", this.foreigncreate.bind(this));
	} catch(err){}
	OZ.Event.add(this.dom.foreignconnect, "click", this.foreignconnect.bind(this));
	OZ.Event.add(this.dom.foreigndisconnect, "click", this.foreigndisconnect.bind(this));
	OZ.Event.add(document, "keydown", this.press.bind(this));

	SQL.subscribe("tableclick", this.tableClick.bind(this));
	SQL.subscribe("rowclick", this.rowClick.bind(this));
}

SQL.RowManager.prototype.select = function(row) { /* activate a row */
	if (this.selected === row) { return; }
	if (this.selected) { this.selected.deselect(); }

	this.selected = row;
	if (this.selected) { this.selected.select(); }
	this.redraw();
}

SQL.RowManager.prototype.tableClick = function(e) { /* create relation after clicking target table */
	if (!this.creating) { return; }

	var r1 = this.selected;
	var t2 = e.target;

	var p = this.owner.getOption("pattern");
	p = p.replace(/%T/g,r1.owner.getTitle());
	p = p.replace(/%t/g,t2.getTitle());
	p = p.replace(/%R/g,r1.getTitle());

	var r2 = t2.addRow(p, r1.data);
	r2.update({"type":SQL.Designer.getFKTypeFor(r1.data.type)});
	r2.update({"ai":false});
	this.owner.addRelation(r1, r2);
}

SQL.RowManager.prototype.rowClick = function(e) { /* draw relation after clicking target row */
	if (!this.connecting) { return; }

	var r1 = this.selected;
	var r2 = e.target;

	if (r1 == r2) { return; }

	this.owner.addRelation(r1, r2);
}

SQL.RowManager.prototype.foreigncreate = function(e) { /* start creating fk */
	this.endConnect();
	if (this.creating) {
		this.endCreate();
	} else {
		this.creating = true;
		try {
			this.dom.foreigncreate.value = "["+dbModelerLocalText("foreignpending")+"]";
		}catch(err){}
	}
}

SQL.RowManager.prototype.foreignconnect = function(e) { /* start drawing fk */
	this.endCreate();
	if (this.connecting) {
		this.endConnect();
	} else {
		this.connecting = true;
		this.dom.foreignconnect.value = "["+dbModelerLocalText("foreignconnectpending")+"]";
	}
}

SQL.RowManager.prototype.foreigndisconnect = function(e) { /* remove connector */
	var rels = this.selected.relations;
	for (var i=rels.length-1;i>=0;i--) {
		var r = rels[i];
		if (r.row2 == this.selected) { this.owner.removeRelation(r); }
	}
	this.redraw();
}

SQL.RowManager.prototype.endCreate = function() {
	this.creating = false;
	try {
		this.dom.foreigncreate.value = dbModelerLocalText("foreigncreate");
	} catch(err){}
}

SQL.RowManager.prototype.endConnect = function() {
	this.connecting = false;
	try {
		this.dom.foreignconnect.value = dbModelerLocalText("foreignconnect");
	} catch(err){}
}

SQL.RowManager.prototype.up = function(e) {
	this.selected.up();
	this.redraw();
}

SQL.RowManager.prototype.down = function(e) {
	this.selected.down();
	this.redraw();
}

SQL.RowManager.prototype.remove = function(e) {
	var result = confirm(dbModelerLocalText("confirmrow")+" '"+this.selected.getTitle()+"' ?");
	if (!result) { return; }
	var t = this.selected.owner;
	this.selected.owner.removeRow(this.selected);

	var next = false;
	if (t.rows) { next = t.rows[t.rows.length-1]; }
	this.select(next);
}

SQL.RowManager.prototype.redraw = function() {
	this.endCreate();
	this.endConnect();
	if (this.selected) {
		var table = this.selected.owner;
		var rows = table.rows;
		try {
			this.dom.uprow.disabled = (rows[0] == this.selected);
		} catch(err){}
		try {
			this.dom.downrow.disabled = (rows[rows.length-1] == this.selected);
		} catch(err){}
		try {
			this.dom.removerow.disabled = false;
		} catch(err){}
		try {
			this.dom.editrow.disabled = false;
		} catch(err){}
		try {
			this.dom.foreigncreate.disabled = !(this.selected.isUnique());
		} catch(err){}
		try {
			this.dom.foreignconnect.disabled = !(this.selected.isUnique());
		} catch(err){}
		try {
			this.dom.foreigndisconnect.disabled = true;
		} catch(err){}
		var rels = this.selected.relations;
		for(var i=0;i<rels.length;i++) {
			var r = rels[i];
			if(r.row2 == this.selected) {
				try {
					this.dom.foreigndisconnect.disabled = false;
				} catch(err){}
			}
		}
	} else {
		try {
			this.dom.uprow.disabled = true;
		} catch(err){}
		try {
			this.dom.downrow.disabled = true;
		} catch(err){}
		try {
			this.dom.removerow.disabled = true;
		} catch(err){}
		try {
			this.dom.editrow.disabled = true;
		} catch(err){}
		try {
			this.dom.foreigncreate.disabled = true;
		} catch(err){}
		try {
			this.dom.foreignconnect.disabled = true;
		} catch(err){}
		try {
			this.dom.foreigndisconnect.disabled = true;
		} catch(err){}
	}
}

SQL.RowManager.prototype.press = function(e) {
	if (!this.selected) { return; }

	var target = OZ.Event.target(e).nodeName.toLowerCase();
	if (target == "textarea" || target == "input") { return; } /* not when in form field */

	switch (e.keyCode) {
		case 38:
			this.up();
			OZ.Event.prevent(e);
		break;
		case 40:
			this.down();
			OZ.Event.prevent(e);
		break;
		case 46:
			this.remove();
			OZ.Event.prevent(e);
		break;
		case 13:
		case 27:
			this.selected.collapse();
		break;
	}
}

SQL.RowManager.prototype.edit = function(e) {
	this.selected.expand();
}

// #END
