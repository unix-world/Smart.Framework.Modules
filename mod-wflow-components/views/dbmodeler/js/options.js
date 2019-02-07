
// wwwsqldesigner v.1.7: options.js
// (c) 2005-2018, Ondrej Zara
// License: BSD

// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

SQL.Options = function(owner) {
	this.owner = owner;
	this.dom = {
		container:OZ.$("opts"),
		btn:OZ.$("options")
	}
	try {
		this.dom.btn.value = dbModelerLocalText("options");
	} catch(err){}
	this.save = this.save.bind(this);
	this.build();
}

SQL.Options.prototype.build = function() {
	this.dom.optionlocale = OZ.$("optionlocale");
	this.dom.optiondb = OZ.$("optiondb");
	this.dom.optionsnap = OZ.$("optionsnap");
	this.dom.optionpattern = OZ.$("optionpattern");
	this.dom.optionhide = OZ.$("optionhide");
	this.dom.optionvector = OZ.$("optionvector");
	this.dom.optionshowsize = OZ.$("optionshowsize");
	this.dom.optionshowtype = OZ.$("optionshowtype");

	var ids = ["language","db","snap","pattern","hide","vector","showsize","showtype","optionsnapnotice","optionpatternnotice","optionsnotice"];
	for (var i=0;i<ids.length;i++) {
		var id = ids[i];
		var elm = OZ.$(id);
		try {
			elm.innerHTML = dbModelerLocalText(id);
		} catch(err){}
	}

/* unixman: don't use locales or available DBs as options ...
	var ls = CONFIG.AVAILABLE_LOCALES;
	OZ.DOM.clear(this.dom.optionlocale);
	for (var i=0;i<ls.length;i++) {
		var o = OZ.DOM.elm("option");
		o.value = ls[i];
		o.innerHTML = ls[i];
		this.dom.optionlocale.appendChild(o);
		if (this.owner.getOption("locale") == ls[i]) { this.dom.optionlocale.selectedIndex = i; }
	}

	var dbs = CONFIG.AVAILABLE_DBS;
	OZ.DOM.clear(this.dom.optiondb);
	for (var i=0;i<dbs.length;i++) {
		var o = OZ.DOM.elm("option");
		o.value = dbs[i];
		o.innerHTML = dbs[i];
		this.dom.optiondb.appendChild(o);
		if (this.owner.getOption("db") == dbs[i]) { this.dom.optiondb.selectedIndex = i; }
	}
*/

	OZ.Event.add(this.dom.btn, "click", this.click.bind(this));

	try {
		this.dom.container.parentNode.removeChild(this.dom.container);
	} catch(err){}

}

SQL.Options.prototype.save = function() {
//	this.owner.setOption("locale",this.dom.optionlocale.value);
//	this.owner.setOption("db",this.dom.optiondb.value);
	this.owner.setOption("snap",this.dom.optionsnap.value);
//	this.owner.setOption("pattern",this.dom.optionpattern.value);
	this.owner.setOption("hide",this.dom.optionhide.checked ? "1" : "");
	this.owner.setOption("vector",this.dom.optionvector.checked ? "1" : "");
	this.owner.setOption("showsize",this.dom.optionshowsize.checked ? "1" : "");
	this.owner.setOption("showtype",this.dom.optionshowtype.checked ? "1" : "");
}

SQL.Options.prototype.click = function() {
	this.owner.window.open(dbModelerLocalText("options"),this.dom.container,this.save);
	this.dom.optionsnap.value = this.owner.getOption("snap");
//	this.dom.optionpattern.value = this.owner.getOption("pattern");
	this.dom.optionhide.checked = this.owner.getOption("hide");
	this.dom.optionvector.checked = this.owner.getOption("vector");
	this.dom.optionshowsize.checked = this.owner.getOption("showsize");
	this.dom.optionshowtype.checked = this.owner.getOption("showtype");
}

// #END
