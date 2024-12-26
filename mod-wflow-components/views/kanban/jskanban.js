
// Js Kanban CSS
// (c) 2017-2021 unix-world.org
// License: GPLv3
// v.20210414

var JsKanban = function() { // START CLASS

	// -> OBJECT

	var _class = this; // self referencing

	var kArea = '';
	var kObj = {};
	var kData = [];
	var kTtls = {};
	var enableSortingInTheSameContainer = true;


	this.initBoard = function(jqSelector, isReadOnly, sortInSameContainer) {
		//--
		if((typeof jqSelector == 'undefined') || (jqSelector == null) || (jqSelector == '')) {
			return;
		} //end if
		kArea = String(jqSelector);
		//--
		if(isReadOnly === true) {
			return;
		} //end if
		//--
		if(sortInSameContainer === false) {
			enableSortingInTheSameContainer = false;
		} //end if
		//--
		if(jQuery.ui && jQuery.ui.sortable) {
			jQuery('.jskanban_sorter').sortable({
				connectWith: '.jskanban_connect_sorter',
				start: function(event, ui) {
					ui.item.attr('data-kanban-item-status', '');
				},
				remove: function(event, ui) {
					var id = ui.item.attr('data-id') || '';
					var status = getValidStatus(jQuery(this).attr('data-kanban-status'));
					//console.log('[' + id + '] was removed from: [' + status + ']');
					jQuery('.jskanban_holder').removeClass('jskanban_holder_highlight');
					jQuery(this).parent().addClass('jskanban_holder_highlight');
				},
				receive: function(event, ui) {
					var id = ui.item.attr('data-id') || '';
					var status = jQuery(this).attr('data-kanban-status');
					//console.log('[' + id + '] was added to: [' + status + ']');
					var updated = updateObjKStatus(id, status);
					if(!updated) {
						alert('Failed to update status for this task ...');
						return;
					} //end if
					ui.item.attr('data-kanban-item-status', status);
					setItemSparklineData(id);
					renderItemSparklineChart(id);
					jQuery(this).parent().addClass('jskanban_holder_highlight');
				},
				deactivate: function(event, ui) {
					var oldStatus = String(ui.item.attr('data-kanban-item-status') || '');
					var newStatus = String(ui.item.parent().attr('data-kanban-status') || '');
					//console.log('Old:', (oldStatus || '-'), 'New:', (newStatus || '-'));
					if(!oldStatus) {
						if(enableSortingInTheSameContainer !== true) {
							ui.sender.sortable('cancel');
						}
					} //end if
				}
			}).disableSelection();
		} else {
			alert('None of LMDD or jQueryUI.Sortable (requirements) has been loaded !');
			throw 'LMDD or jQueryUI.Sortable not detected ...';
			return;
		} //end if else
		//--
	} //END FUNCTION


	this.importData = function(dataJson) {
		//--
		if(dataJson.docType != 'smartWorkFlow.TodoList') {
			return false;
		} //end if
		if(!dataJson.data) {
			return false;
		} //end if
		if(!dataJson.data.todos) {
			return false;
		} //end if
		if(!dataJson.data.todos.data) {
			return false;
		} //end if
		//--
		var kdata = dataJson.data.todos.data;
		var obj = null;
		for(var i=0; i<kdata.length; i++) {
			obj = kdata[i] || null;
			if(obj && obj.id) {
				_class.addTask(obj);
			} //end if
		} //end for
		obj = null;
		//--
		addDoc(dataJson);
		//--
		return true;
		//--
	} //END FUNCTION


	var addDoc = function(dataJson) {
		//--
		dataJson.data.todos.data = []; // reset
		//--
		kObj = dataJson;
		//--
		return true;
		//--
	} //END FUNCTION


	this.addTask = function(obj) {
		//--
		if(!obj) {
			return;
		} //end if
		//--
		var id = String(obj.id || '');
		if(!id) {
			id = String(smartJ$Utils.uuid());
			obj.id = id;
			obj.start = String(getIsoDate());
			obj.type = 'flextask';
		} //end if
		//--
		var status = String(obj.kanbanStatus || '');
		if(status === '') {
			if(obj.progress) {
				if(obj.progress <= 0.5) {
					status = 'inprogress';
				} else if((obj.progress > 0.5) && (obj.progress < 1)) {
					status = 'check';
				} //end if else
			} //end if
		} else if(status === 'done') {
			if(obj.progress < 1) {
				status = 'check';
			} //end if
		} //end if
		status = getValidStatus(status);
		obj.kanbanStatus = status;
		//--
		var ttl = String(obj.title || 'Untitled');
		obj.title = ttl;
		//--
		var theObjProps = getItemProperties(obj);
		var extraClass 	= theObjProps.extraClass;
		var display 	= theObjProps.display;
		var t_start 	= theObjProps.t_start;
		var t_end 		= theObjProps.t_end;
		var showEnd 	= theObjProps.showEnd;
		var timings 	= theObjProps.timings;
		theObjProps 	= null;
		//--
		if(id) {
			kTtls['__id__' + String(id)] = String(ttl);
		} //end if
		var parentTtl = '';
		if(obj.parent) {
			parentTtl = kTtls['__id__' + String(obj.parent)];
		} //end if
		//--
		var t_progress = parseFloat(obj.progress);
		if(isNaN(t_progress) || !isFinite(t_progress) || (t_progress < 0)) {
			t_progress = 0;
		} else if(t_progress > 1) {
			t_progress = 1;
		} //end if else
		var p_progress = Math.floor(t_progress * 100);
		//--
		kData.push(obj);
		if(display >= 2) {
			jQuery(String(kArea)).find('div.jskanban_holder').find('ul#kanban-' + String(status)).append('<li title="Task: ' + smartJ$Utils.escape_html(id) + '" id="kanban-task--' + smartJ$Utils.escape_html(id) + '" data-kanban-item-status="" data-id="' + smartJ$Utils.escape_html(id) + '" class="jskanban_box' + smartJ$Utils.escape_html(extraClass) + '"><div class="task-text"><div class="task-row">' + smartJ$Utils.escape_html(ttl) + '</div><div class="task-subrow"><div class="task-timings">' + smartJ$Utils.escape_html(timings) + '</div>' + (parentTtl ? ' &nbsp; ' + smartJ$Utils.escape_html(parentTtl) : '') + '</div></div><div title="Progress: ' + smartJ$Utils.escape_html(p_progress) + '%" class="sparkline" data-chart="' + smartJ$Utils.escape_html(t_progress + ',' + (1-t_progress)) + '"></div></li>');
		} else if(display === 0) {
			jQuery('body').find('div.jskanban_passive_elements').append('<div class="type-project" title="Project Feature: ' + smartJ$Utils.escape_html(id) + '">' + smartJ$Utils.escape_html(ttl) + '<br><span>' + smartJ$Utils.escape_html(timings) + '&nbsp;' + smartJ$Utils.escape_html('>') + '</span></div>');
		} else if(display === 1) {
			jQuery('body').find('div.jskanban_passive_elements').append('<div class="type-milestone" title="Milestone: ' + smartJ$Utils.escape_html(id) + '">' + smartJ$Utils.escape_html(ttl) + '<br><span>' + smartJ$Utils.escape_html(timings) + '&nbsp;' + smartJ$Utils.escape_html('^') + (parentTtl ? ' &nbsp; ' + smartJ$Utils.escape_html(parentTtl) : '') + '</span></div>');
		} //end if else
		//--
		renderItemSparklineChart(id);
		//--
	} //END FUNCTION


	this.saveBoard = function(evcode) {
		//-- IMPORTANT: Do Not Change Order on save as it would not correctly display parents (projects, ...)
		if((typeof evcode === 'function') && (evcode != null)) {
			evcode(kData, kObj);
		} //end if
		//--
	} //END FUNCTION


	var getItemProperties = function(obj) {
		//--
		if(!obj) {
			return null;
		} //end if
		//--
		var display = 2;
		var t_start = String(obj.start || '');
		var t_end = String(obj.end || '');
		var showEnd = true;
		var extraClass = '';
		switch(String(obj.type)) {
			case 'project':
				display = 0;
				t_end = '-';
				showEnd = false;
				break;
			case 'milestone':
				display = 1;
				t_end = '@';
				showEnd = false;
				break;
			case 'flextask':
				display = 3;
				extraClass = ' taskTypeFlex';
				if(obj.progress < 1) {
					t_end = '*';
				} //end if
				break;
			default:
				// nothing
		} //end switch
		//--
		var timings = String(t_start.split(' ')[0]);
		if(t_end && showEnd) {
			timings += ' - ' + String(t_end.split(' ')[0]);
		} //end if
		//--
		return {
			display: display,
			t_start: t_start,
			t_end: t_end,
			showEnd: showEnd,
			timings: timings,
			extraClass: extraClass
		};
		//--
	} //END FUNCTION

	var getValidStatus = function(status) {
		//--
		switch(String(status)) {
			case 'inprogress':
			case 'check':
			case 'done':
			case 'todo':
				break;
			default:
				status = 'todo';
		} //end switch
		//--
		return String(status);
		//--
	} //END FUNCTION


	var getIsoDate = function(oldDate, daysOffset) {
		//--
		var d = new Date();
		var dz  = smartJ$Date.standardizeDate(d);
		var iso = smartJ$Date.getIsoDate(dz);
		if(oldDate && daysOffset) {
			daysOffset = Math.floor(daysOffset);
			if(daysOffset > 0) {
				d = new Date(String(oldDate));
				dz  = smartJ$Date.standardizeDate(d);
				dz = smartJ$Date.addDays(dz, daysOffset);
				iso = smartJ$Date.getIsoDate(dz);
			} //end if
		} //end if
		//--
		return String(iso);
		//--
	} //END FUNCTION


	var updateObjTimingsDisplay = function(obj) {
		//--
		var id 			= obj.id;
		var theObjProps = getItemProperties(obj);
		var extraClass 	= theObjProps.extraClass;
		var display 	= theObjProps.display;
		var t_start 	= theObjProps.t_start;
		var t_end 		= theObjProps.t_end;
		var showEnd 	= theObjProps.showEnd;
		var timings 	= theObjProps.timings;
		theObjProps 	= null;
		//--
		jQuery(String(kArea)).find('div.jskanban_holder').find('ul[id^=kanban-]').find('li#kanban-task--' + smartJ$Utils.escape_html(id) + ' > div.task-text > div.task-subrow > div.task-timings').empty().html(smartJ$Utils.escape_html(timings));
		//--
	} //END FUNCTION


	var updateObjKStatus = function(id, status) {
		//--
		if(!id) {
			return false;
		} //end if
		//--
		var obj;
		for(var i=0; i<kData.length; i++) {
			obj = kData[i];
			if(obj && obj.id) {
				if(String(id) === String(obj.id)) {
					if(obj.kanbanStatus === 'done') { // restore only if moved back from done
						if(obj.duration) {
							obj.end = String(getIsoDate(obj.start, obj.duration));
						} else {
							delete(obj.end);
						} //end if else
					} //end if
					obj.kanbanStatus = getValidStatus(status);
					switch(String(obj.kanbanStatus)) {
						case 'todo':
							obj.progress = 0;
							break;
						case 'inprogress':
							obj.progress = 0.50;
							break;
						case 'check':
							obj.progress = 0.90;
							break;
						case 'done':
							obj.progress = 1;
							obj.end = String(getIsoDate());
							break;
						default:
							// keep
					} //end switch
					updateObjTimingsDisplay(obj);
					return true;
				} //end if
			} //end if
		} //end for
		obj = null;
		//--
		return false;
		//--
	} //END FUNCTION


	var getItemSparklineDiv = function(id) {
		//--
		if(!id) {
			return null;
		} //end if
		//--
		return jQuery(String(kArea)).find('div.jskanban_holder').find('ul[id^=kanban-]').find('li#kanban-task--' + smartJ$Utils.escape_html(id) + ' > div.sparkline');
		//--
	} //END FUNCTION


	var setItemSparklineData = function(id) {
		//--
		var item = getItemSparklineDiv(id);
		if(!item) {
			return false;
		} //end if
		//--
		var obj;
		for(var i=0; i<kData.length; i++) {
			obj = kData[i];
			if(obj && obj.id) {
				if(String(id) === String(obj.id)) {
					var t_progress = Number(obj.progress);
					var p_progress = Math.floor(t_progress * 100);
					item.attr('data-chart', String(Number(t_progress) + ',' + Number(1-t_progress))).attr('title', String('Progress: ' + smartJ$Utils.escape_html(p_progress) + '%'));
					return true;
				} //end if
			} //end if
		} //end for
		obj = null;
		//--
		return false;
		//--
	} //END FUNCTION


	var renderItemSparklineChart = function(id) {
		//--
		if(!jQuery.fn.sparkline) {
			return false;
		} //end if
		//--
		var item = getItemSparklineDiv(id);
		if(!item) {
			return false;
		} //end if
		//--
		item.empty().off().sparkline('html', {
			type: 'pie',
			enableTagOptions: true,
			tagOptionsPrefix: '',
			tagValuesAttribute: 'data-chart',
			disableTooltips: true,
			disableInteraction: true,
			sliceColors: ['#758C33', '#DDDDDD'],
			width: '22px',
			height: '22px'
		});
		//--
		return true;
		//--
	} //end if


} //END CLASS

// END
