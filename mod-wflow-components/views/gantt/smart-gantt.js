
// Gantt Manager
// (c) 2019 unix-world.org
// License: GPLv3
// v.20190207

var SmartGanttManager = new function() { // START CLASS :: v.20190129

	// :: static


	this.drawInstance = function(theData, isEditable, showGrid, gantID, theScale, theReferenceDate, theNowDate) {

		if((typeof theData != 'object') || (!theData)) {
			theData = newGanttDataStructure();
		} else if(!theData.hasOwnProperty('data')) {
			theData = newGanttDataStructure();
		} else if(!theData.data.hasOwnProperty('todos')) {
			theData = newGanttDataStructure();
		} else if(!theData.data.todos.hasOwnProperty('data')) {
			theData = newGanttDataStructure();
		} //end if
		if(!theData.data.todos.hasOwnProperty('links')) {
			theData.data.todos.links = [];
		} //end if
		if(!theData.data.hasOwnProperty('view')) {
			theData.data.view = 'day';
		} //end if
		if(!theData.data.hasOwnProperty('date')) {
			theData.data.date = SmartJS_DateUtils.getIsoDate(new Date());
		} //end if
		if(!theData.data.hasOwnProperty('now')) {
			theData.data.now = true;
		} //end if

		// fixes: get this from doc
		if(theScale === null) {
			theScale = theData.data.view;
		} //end if
		if(theReferenceDate === null) {
			theReferenceDate = theData.data.date;
		} //end if
		if(theNowDate === null) {
			theNowDate = theData.data.now;
		} //end if

		var gantInstance = new SmartGanttInstance(); // supports multiple instances

		var objScales = getGanttSafeScales(theScale);
		var theDateScale = objScales.dateScale;
		theScale = objScales.scale;

		var objDate = getGanttDatesByScale(theScale, theReferenceDate, theNowDate);
		var gantDateRef = objDate.refDate;
		var gantDateNow = objDate.nowDate;
		var gantDateStart = objDate.startDate;
		var gantDateEnd = objDate.endDate;
		//console.log(gantDateRef, gantDateNow, gantDateStart, gantDateEnd);

		isEditable = !!isEditable; // force boolean
		showGrid = !!showGrid; // force boolean

		jQuery('#' + gantID).dhx_gantt(gantInstance, {
			data: theData.data.todos,
			reference_date: String(gantDateRef), // reference date
			marker_date: gantDateNow, // YYYY-MM-DD or set to true to use TODAY or FALSE to hide
			start_date: new Date(String(gantDateStart)),
			end_date: new Date(String(gantDateEnd)), // IMPORTANT: when no end date, flex tasks will fill all available space !!!
			scale_unit: theScale, // day, week, month
			duration_step: 1,
			date_scale: theDateScale,
			readonly: !isEditable,
			show_grid : showGrid, // grid_width: 0, // or use this with zero to hide right grid
			//show_task_cells : false,
			//sort: true,
			//step:1,
		}); // .load("data.json")

		if(isEditable) {
			gantInstance.config.timeout_to_hide = 50;
		}

		setTimeout(function(){ gantInstance.showDate(new Date(String(gantDateRef))) }, 50);

		return gantInstance;

	} //END FUNCTION


	this.getDate = function(gantInstance) {
		//--
		if(!gantInstance) {
			return '';
		} //end if
		//--
		return gantInstance.config.reference_date || SmartJS_DateUtils.getIsoDate(new Date());
		//--
	} //END FUNCTION


	this.changeDate = function(gantInstance, theReferenceDate, theNowDate) {
		//--
		if(!gantInstance) {
			return false;
		} //end if
		//--
		if(typeof theReferenceDate == 'undefined') {
			theReferenceDate = SmartJS_DateUtils.getIsoDate(new Date());
		} else if(!theReferenceDate) {
			theReferenceDate = gantInstance.config.reference_date;
		} else {
			theReferenceDate = SmartJS_DateUtils.getIsoDate(new Date(String(theReferenceDate)));
		} //end if else
		//--
		if(typeof theNowDate == 'undefined') {
			theNowDate = true;
		} else if(!theNowDate) {
			theNowDate = gantInstance.config.marker_date;
		} //end if
		//--
		theScale = gantInstance.config.scale_unit;
		//--
		var objDate = getGanttDatesByScale(theScale, theReferenceDate, theNowDate);
		var gantDateRef = objDate.refDate;
		var gantDateNow = objDate.nowDate;
		var gantDateStart = objDate.startDate;
		var gantDateEnd = objDate.endDate;
		//console.log(theScale, gantDateRef, gantDateNow, gantDateStart, gantDateEnd);
		//--
		gantInstance.config.reference_date = String(theReferenceDate);
		gantInstance.config.start_date = new Date(String(gantDateStart));
		gantInstance.config.end_date = new Date(String(gantDateEnd));
		gantInstance.render();
		setTimeout(function(){ gantInstance.showDate(new Date(String(gantDateRef))) }, 50);
		//--
		return true;
		//--
	} //END FUNCTION


	this.getScale = function(gantInstance) {
		//--
		if(!gantInstance) {
			return '';
		} //end if
		//--
		return gantInstance.config.scale_unit;
		//--
	} //END FUNCTION


	this.changeScale = function(gantInstance, theScale, theReferenceDate, theNowDate) {
		//--
		if(!gantInstance) {
			return false;
		} //end if
		//--
		if(typeof theReferenceDate == 'undefined') {
			theReferenceDate = SmartJS_DateUtils.getIsoDate(new Date());
		} else if(!theReferenceDate) {
			theReferenceDate = gantInstance.config.reference_date;
		} //end if else
		//--
		if(typeof theNowDate == 'undefined') {
			theNowDate = true;
		} else if(!theNowDate) {
			theNowDate = gantInstance.config.marker_date;
		} //end if
		//--
		var objScales = getGanttSafeScales(theScale);
		var theDateScale = objScales.dateScale;
		theScale = objScales.scale;
		//--
		var objDate = getGanttDatesByScale(theScale, theReferenceDate, theNowDate);
		var gantDateRef = objDate.refDate;
		var gantDateNow = objDate.nowDate;
		var gantDateStart = objDate.startDate;
		var gantDateEnd = objDate.endDate;
		//console.log(gantDateRef, gantDateNow, gantDateStart, gantDateEnd);
		//--
		gantInstance.config.scale_unit = theScale;
		gantInstance.config.date_scale = theDateScale;
		gantInstance.config.start_date = new Date(String(gantDateStart));
		gantInstance.config.end_date = new Date(String(gantDateEnd));
		gantInstance.render();
		setTimeout(function(){ gantInstance.showDate(new Date(String(gantDateRef))) }, 50);
		//--
		return true;
		//--
	} //END FUNCTION


	this.getExportData = function(gantInstance) {
		//--
		if(!gantInstance) {
			return;
		} //end if
		//--
		var expDoc = newGanttDataStructure();
		expDoc.data.view = String(gantInstance.config.scale_unit);
		expDoc.data.date = String(gantInstance.config.reference_date);
		expDoc.data.now = gantInstance.config.marker_date ? (gantInstance.config.marker_date === true ? true : String(gantInstance.config.marker_date)) : false;
		expDoc.data.todos = gantInstance.serialize() || { data:[], links:[] };
		return expDoc;
		//--
	} //END FUNCTION


	//#####


	var newGanttDataStructure = function() {
		//--
		var dateobj = new Date();
		var expDoc = {
			docTitle: '', // to be updated later
			docDate: String(dateobj.toISOString()),
			docType: 'smartWorkFlow.TodoList',
			docVersion: '1.0',
			dataFormat: 'data/structure',
			data: {
				view: 'day',
				date: SmartJS_DateUtils.getIsoDate(new Date()),
				now: true,
				todos: { data:[], links:[] }
			}
		};
		return expDoc;
		//--
	} //END FUNCTION


	var getGanttDatesByScale = function(theScale, theReferenceDate, theNowDate) {
		//--
		if(typeof theReferenceDate == 'undefined' || (!theReferenceDate)) {
			theReferenceDate = new Date();
			theReferenceDate = SmartJS_DateUtils.getIsoDate(theReferenceDate);
		} //end if
		//--
		var dt = new Date(String(theReferenceDate));
		var ds  = SmartJS_DateUtils.standardizeDate(dt);
		//--
		var gantDateNow = true;
		if(typeof theNowDate != 'undefined') {
			if((theNowDate === true) || (theNowDate === 'true')) {
				gantDateNow = true; // today
			} else if((theNowDate === false) || (theNowDate === 'false')) {
				gantDateNow = false; // hide
			} else if((theNowDate != '') && (theNowDate != 'undefined') && (theNowDate != null)) { // a date YYYY-MM-DD
				gantDateNow = new Date(String(theNowDate));
				gantDateNow = SmartJS_DateUtils.getIsoDate(gantDateNow);
			} //end if else
		} //end if
		//--
		var gantDateStart = SmartJS_DateUtils.getIsoDate(ds);
		var gantDateEnd = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, 1));
		if(theScale == 'day') {
			dt = new Date(String(theReferenceDate));
			ds  = SmartJS_DateUtils.standardizeDate(dt);
			gantDateStart = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, -1)); // past 1 day
			gantDateEnd = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, 90)); // next 75 days
		} else if(theScale == 'week') {
			dt = new Date(String(theReferenceDate));
			ds  = SmartJS_DateUtils.standardizeDate(dt);
			gantDateStart = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, -7)); // past 1 week
			gantDateEnd = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, 210)); // +30 weeks
		} else if(theScale == 'year') {
			dt = new Date(String(theReferenceDate));
			ds  = SmartJS_DateUtils.standardizeDate(dt);
			gantDateStart = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, -31)); // past 1 month
			gantDateEnd = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, 1097)); // +3 years
		} else { // month, quarter
			dt = new Date(String(theReferenceDate));
			ds  = SmartJS_DateUtils.standardizeDate(dt);
			gantDateStart = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, -31)); // past 1 month
			gantDateEnd = SmartJS_DateUtils.getIsoDate(SmartJS_DateUtils.addDays(ds, 367)); // +1 year
		}
		//--
		return { // theNowDate
			refDate: 	String(SmartJS_DateUtils.getIsoDate(new Date(theReferenceDate))),
			nowDate: 	(gantDateNow === true || gantDateNow === false) ? !!gantDateNow : String(gantDateNow),
			startDate: 	String(gantDateStart),
			endDate: 	String(gantDateEnd)
		};
		//--
	} //END FUNCTION


	var getGanttSafeScales = function(theScale) {
		//--
		var theDateScale = '%d %M`%y';
		//--
		switch(theScale) {
			case 'year':
				theDateScale = '%Y';
				break;
			case 'quarter': // this is too slow on edit ; use just on visualise
			case 'month':
				theDateScale = '%M %Y';
				break;
			case 'week':
			//	theDateScale = '`%y-%m-%d';
				break;
			case 'day':
			default:
				theScale = 'day';
		} //end switch
		//--
		return {
			scale: theScale,
			dateScale: theDateScale
		};
		//--
	} //END FUNCTION


} //END CLASS


// #END

