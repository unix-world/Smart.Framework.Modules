
// wwwsqldesigner v.1.7: io.js
// (c) 2005-2018, Ondrej Zara
// License: BSD

// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

SQL.IO = function(owner) {
	this.owner = owner;
	this._name = ""; /* last used name with server load/save */
	this.lastUsedName = ""; /* last used name with external load/save */
	this.dom = {
		container:OZ.$("io")
	};

	try { // unixman
		OZ.$('the-db-type').innerText = 'DB-Type: ' + this.owner.getOption('db');
	} catch(err){}

	if(!this.dom.container) {
		return; // unixman fix for readonly
	}

	var ids = [ "saveload", "clientsql",
				"clientsave", "clientload",
			//	"serversave", "serverload" // "serverlist", "serverimport"
	];

	for (var i=0;i<ids.length;i++) {
		var id = ids[i];
		var elm = OZ.$(id);
		this.dom[id] = elm;
		try {
			elm.value = dbModelerLocalText(id);
		} catch(err){}
	}

	var ids = ["client","server","output"];
	for (var i=0;i<ids.length;i++) {
		var id = ids[i];
		var elm = OZ.$(id);
		try {
			elm.innerHTML = dbModelerLocalText(id);
		} catch(err){}
	}

	this.dom.ta = OZ.$("textarea");
	this.dom.backend = OZ.$("backend");

	this.dom.container.parentNode.removeChild(this.dom.container);
	this.dom.container.style.visibility = "";

//	this.saveresponse = this.saveresponse.bind(this);
//	this.loadresponse = this.loadresponse.bind(this);
//	this.listresponse = this.listresponse.bind(this);
//	this.importresponse = this.importresponse.bind(this);

	OZ.Event.add(this.dom.saveload, "click", this.click.bind(this));
	OZ.Event.add(this.dom.clientsql, "click", this.clientsql.bind(this));
	try {
		OZ.Event.add(this.dom.clientsave, "click", this.clientsave.bind(this));
	} catch(err) {}
	try {
		OZ.Event.add(this.dom.clientload, "click", this.clientload.bind(this));
	} catch(err) {}
	try {
		OZ.Event.add(this.dom.serversave, "click", this.serversave.bind(this));
	} catch(err) {}
	try {
		OZ.Event.add(this.dom.serverload, "click", this.serverload.bind(this));
	} catch(err) {}
//		OZ.Event.add(this.dom.serverlist, "click", this.serverlist.bind(this));
//		OZ.Event.add(this.dom.serverimport, "click", this.serverimport.bind(this));
	OZ.Event.add(document, "keydown", this.press.bind(this));
	this.build();
}

SQL.IO.prototype.build = function() {
	OZ.DOM.clear(this.dom.backend);

	var bs = CONFIG.AVAILABLE_BACKENDS;
	var be = CONFIG.DEFAULT_BACKEND;
	var r = window.location.search.substring(1).match(/backend=([^&]*)/);
	if (r) {
		req = r[1];
		if (bs.indexOf(req) != -1) {
		  be = req;
		}
	}
	for (var i=0;i<bs.length;i++) {
		var o = OZ.DOM.elm("option");
		o.value = bs[i];
		o.innerHTML = bs[i];
		this.dom.backend.appendChild(o);
		if (bs[i] == be) { this.dom.backend.selectedIndex = i; }
	}
}

SQL.IO.prototype.click = function() { /* open io dialog */
	this.build();
	this.dom.ta.value = "";
	this.dom.clientsql.value = dbModelerLocalText("clientsql") + " (" + window.DATATYPES.getAttribute("db") + ")";
	this.owner.window.open(dbModelerLocalText("saveload"),this.dom.container);
}

SQL.IO.prototype.fromXMLText = function(xml) {
	try {
		if (window.DOMParser) {
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(xml, "text/xml");
		} else if (window.ActiveXObject || "ActiveXObject" in window) {
			var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
			xmlDoc.loadXML(xml);
		} else {
			throw new Error("No XML parser available.");
		}
	} catch(e) {
		alert(dbModelerLocalText("xmlerror")+': '+e.message);
		return;
	}
	this.fromXML(xmlDoc);
}

SQL.IO.prototype.fromXML = function(xmlDoc) {
	if (!xmlDoc || !xmlDoc.documentElement) {
		alert(dbModelerLocalText("xmlerror")+': Null document');
		return false;
	}
	this.owner.fromXML(xmlDoc.documentElement);
	this.owner.window.close();
	return true;
}

SQL.IO.prototype.clientsave = function() {
	var xml = this.owner.toXML();
	this.dom.ta.value = xml;
}

SQL.IO.prototype.clientload = function() {
	var result = confirm(dbModelerLocalText("unsavedlost"));
	if(!result) {
		return;
	} //end if
	var xml = this.dom.ta.value;
	if(!xml) {
		alert(dbModelerLocalText("empty"));
		return;
	}
	this.fromXMLText(xml);
}

/*
SQL.IO.prototype.promptName = function(title, suffix) {
	var lastUsedName = this.owner.getOption("lastUsedName") || this.lastUsedName;
	var name = prompt(dbModelerLocalText(title), lastUsedName);
	if (!name) { return null; }
	if (suffix && name.endsWith(suffix)) {
		// remove suffix from name
		name = name.substr(0, name.length-4);
	}
	this.owner.setOption("lastUsedName", name);
	this.lastUsedName = name;	// save this also in variable in case cookies are disabled
	return name;
}
*/

SQL.IO.prototype.clientsql = function() {
	var bp = this.owner.getOption("staticpath");
	var path = bp + "db/"+window.DATATYPES.getAttribute("db")+"/output.xsl";
	this.owner.window.showThrobber();
	OZ.Request(path, this.finish.bind(this), {xml:true});
}

SQL.IO.prototype.finish = function(xslDoc) {
	this.owner.window.hideThrobber();
	var xml = this.owner.toXML();
	var sql = "";
	try {
		if (window.XSLTProcessor && window.DOMParser) {
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(xml, "text/xml");
			var xsl = new XSLTProcessor();
			xsl.importStylesheet(xslDoc);
			var result = xsl.transformToDocument(xmlDoc);
			sql = result.documentElement.textContent;
		} else if (window.ActiveXObject || "ActiveXObject" in window) {
			var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
			xmlDoc.loadXML(xml);
			sql = xmlDoc.transformNode(xslDoc);
		} else {
			throw new Error("No XSLT processor available");
		}
	} catch(e) {
		alert(dbModelerLocalText("xmlerror")+': '+e.message);
		return;
	}
	this.dom.ta.value = sql.trim();
}

SQL.IO.prototype.serversave = function(e) {
	var xml = this.owner.toXML();
	var fx = this.owner.getOption('xhrsavefunction');
	if(typeof fx === 'function') {
		fx(xml, e);
	} //end if
}

SQL.IO.prototype.serverload = function(e) {
	var fx = this.owner.getOption('xhrloadfunction');
	if(typeof fx === 'function') {
		fx(this, e);
	} //end if
}

SQL.IO.prototype.exportdata = function(e) {
	var xml = this.owner.toXML();
	var dateobj = new Date();
	var obj = {
		docTitle: '', // to be updated later
		docDate: String(dateobj.toISOString()),
		docType: 'smartWorkFlow.DbModel',
		docVersion: '1.0',
		dataFormat: 'text/xml',
		data: {
			type: String(window.DATATYPES.getAttribute('db') || 'unknown'),
			xml: String(xml)
		}
	};
	return obj;
}

/*
SQL.IO.prototype.serversave = function(e, keyword) {
	var name = keyword || prompt(dbModelerLocalText("serversaveprompt"), this._name);
	if (!name) { return; }
	this._name = name;
	var xml = this.owner.toXML();
	var bp = this.owner.getOption("xhrpath");
	var url = bp + "backend/"+this.dom.backend.value+"/?action=save&keyword="+encodeURIComponent(name);
	var h = {"Content-type":"application/xml"};
	this.owner.window.showThrobber();
	this.owner.setTitle(name);
	OZ.Request(url, this.saveresponse, {xml:true, method:"post", data:xml, headers:h});
}
SQL.IO.prototype.serverload = function(e, keyword) {
	var name = keyword || prompt(dbModelerLocalText("serverloadprompt"), this._name);
	if (!name) { return; }
	this._name = name;
	var bp = this.owner.getOption("xhrpath");
	var url = bp + "backend/"+this.dom.backend.value+"/?action=load&keyword="+encodeURIComponent(name);
	this.owner.window.showThrobber();
	this.name = name;
	OZ.Request(url, this.loadresponse, {xml:true});
}
SQL.IO.prototype.serverlist = function(e) {
	var bp = this.owner.getOption("xhrpath");
	var url = bp + "backend/"+this.dom.backend.value+"/?action=list";
	this.owner.window.showThrobber();
	OZ.Request(url, this.listresponse);
}
SQL.IO.prototype.serverimport = function(e) {
	var name = prompt(dbModelerLocalText("serverimportprompt"), "");
	if (!name) { return; }
	var bp = this.owner.getOption("xhrpath");
	var url = bp + "backend/"+this.dom.backend.value+"/?action=import&database="+name;
	this.owner.window.showThrobber();
	OZ.Request(url, this.importresponse, {xml:true});
}

SQL.IO.prototype.check = function(code) {
	switch (code) {
		case 201:
		case 404:
		case 500:
		case 501:
		case 503:
			var lang = "http"+code;
			this.dom.ta.value = dbModelerLocalText("httpresponse")+": "+dbModelerLocalText(lang);
			return false;
		break;
		default: return true;
	}
}

SQL.IO.prototype.saveresponse = function(data, code) {
	this.owner.window.hideThrobber();
	this.check(code);
}

SQL.IO.prototype.loadresponse = function(data, code) {
	this.owner.window.hideThrobber();
	if (!this.check(code)) { return; }
	this.fromXML(data);
	this.owner.setTitle(this.name);
}

SQL.IO.prototype.listresponse = function(data, code) {
	this.owner.window.hideThrobber();
	if (!this.check(code)) { return; }
	this.dom.ta.value = data;
}

SQL.IO.prototype.importresponse = function(data, code) {
	this.owner.window.hideThrobber();
	if (!this.check(code)) { return; }
	if (this.fromXML(data)) {
		this.owner.alignTables();
	}
}
*/

SQL.IO.prototype.press = function(e) {
/*
	switch (e.keyCode) {
		case 113:
			if(OZ.opera) {
				e.preventDefault();
			}
			this.someFunction(e);
		break;
	}
*/
}

// #END
