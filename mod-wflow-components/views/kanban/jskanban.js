
// Js Kanban CSS
// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190227

var JS_Kanban = new function() { // START CLASS

	var kData = [];
	var projects = {};
	var enableSortingInTheSameContainer = true;

	this.initBoard = function(isReadOnly, sortInSameContainer) {
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
					}
					ui.item.attr('data-kanban-item-status', status);
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

	this.addTask = function(area, obj) {
		//--
		if(!obj) {
			return;
		} //end if
		//--
		var id = String(obj.id || '');
		if(!id) {
			id = SmartJS_CryptoHash.sha1('KanBan UUID #' + randNum + ' :: ' + seconds + '.' + milliseconds);
			var date = new Date();
			var seconds = date.getTime();
			var milliseconds = date.getMilliseconds();
			var randNum = Math.random().toString(36);
			obj.id = id;
			var d = new Date();
			var dz  = SmartJS_DateUtils.standardizeDate(d);
			var iso = SmartJS_DateUtils.getIsoDate(dz);
			obj.start = String(iso);
			obj.type = 'flextask';
		} //end if
		//--
		var status = String(obj.kanbanStatus || '');
		if(status === '') {
			if(obj.progress) {
				if(obj.progress < 1) {
					status = 'inprogress';
				} else if(obj.progress >= 1) {
					status = 'check';
				} //end if else
			} //end if
		} //end if
		status = getValidStatus(status);
		obj.kanbanStatus = status;
		//--
		var ttl = String(obj.title || 'Untitled');
		obj.title = ttl;
		//--
		var extraClass = '';
		var display = 2;
		var t_start = String(obj.start || '');
		var t_end = String(obj.end || '');
		switch(String(obj.type)) {
			case 'project':
				display = 0;
				t_end = '-';
				if(id) {
					projects['proj__id__' + String(id)] = String(ttl);
				} //end if
				break;
			case 'milestone':
				display = 1;
				t_end = '@';
				break;
			case 'flextask':
				display = 3;
				extraClass = ' taskTypeFlex';
				t_end = '*';
				break;
			default:
				// nothing
		} //end switch
		//--
		var timings = String(t_start.split(' ')[0]);
		if(t_end) {
			timings += ' - ' + String(t_end.split(' ')[0]);
		}
		//--
		var parentTtl = '';
		if(obj.parent) {
			parentTtl = projects['proj__id__' + String(obj.parent)];
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
			jQuery(area).find('div.jskanban_holder').find('ul#kanban-' + String(status)).append('<li title="Task ID: ' + SmartJS_CoreUtils.escape_html(id) + (parentTtl ? '\n' + 'Project: ' + SmartJS_CoreUtils.escape_html(parentTtl) : '') + '" id="kanban-task--' + SmartJS_CoreUtils.escape_html(id) + '" data-kanban-item-status="" data-id="' + SmartJS_CoreUtils.escape_html(id) + '" class="jskanban_box' + SmartJS_CoreUtils.escape_html(extraClass) + '"><div class="task-text"><div class="task-row">' + SmartJS_CoreUtils.escape_html(ttl) + '</div><div class="task-subrow">' + SmartJS_CoreUtils.escape_html(timings) + '</div></div><div title="Progress: ' + SmartJS_CoreUtils.escape_html(p_progress) + '%" class="sparkline" data-chart="' + SmartJS_CoreUtils.escape_html(t_progress + ',' + (1-t_progress)) + '"></div></li>');
		} else if(display === 0) {
			jQuery('body').find('div.jskanban_passive_elements').append('<div class="type-project" title="Project">' + SmartJS_CoreUtils.escape_html(ttl) + '</div>');
		} else if(display === 1) {
			jQuery('body').find('div.jskanban_passive_elements').append('<div class="type-milestone" title="Milestone">' + SmartJS_CoreUtils.escape_html(ttl) + '</div>');
		} //end if else
		//--
		if(jQuery.fn.sparkline) {
			jQuery('#kanban-task--' + SmartJS_CoreUtils.escape_html(id) + ' > div.sparkline').sparkline('html', {
				type: 'pie',
				enableTagOptions: true,
				tagOptionsPrefix: '',
				tagValuesAttribute: 'data-chart',
				disableTooltips: true,
				disableInteraction: true,
				sliceColors: ['#758C33', '#DDDDDD']
			});
		} //end if
		//--
	} //END FUNCTION


	this.saveBoard = function(area, evcode) {
		//-- IMPORTANT: Do Not Change Order on save as it would not correctly display parents (projects, ...)
		if((typeof evcode === 'function') && (evcode != null)) {
			evcode(kData);
		} //end if
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
					obj.kanbanStatus = getValidStatus(status);
				} //end if
			} //end if
		} //end for
		//--
		return true;
		//--
	} //END FUNCTION


} //END CLASS

// END
