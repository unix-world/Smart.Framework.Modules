
// Js Kanban CSS
// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

var JS_Kanban = new function() { // START CLASS

	var kData = [];

	this.initBoard = function() {

		jQuery('.jskanban_sorter').sortable({
			connectWith: '.jskanban_connect_sorter',
			remove: function(event, ui) {
				var id = ui.item.attr('id') || '';
				var status = jQuery(this).attr('data-kanban-status');
				//console.log('[' + id + '] was removed from: [' + status + ']');
				jQuery('.jskanban_holder').removeClass('jskanban_holder_highlight');
				jQuery(this).parent().addClass('jskanban_holder_highlight');
			},
			receive: function(event, ui) {
				var id = ui.item.attr('id') || '';
				var status = jQuery(this).attr('data-kanban-status');
				//console.log('[' + id + '] was added to: [' + status + ']');
				var updated = updateObjKStatus(id, status);
				if(!updated) {
					alert('Failed ...');
					return;
				}
				jQuery(this).parent().addClass('jskanban_holder_highlight');
			},
		}).disableSelection();

	} //END FUNCTION

	this.addTask = function(area, obj) {

		if(!obj) {
			return;
		}

		var id = String(obj.id || '');
		if(!id) {
			id = SmartJS_CryptoHash.sha1('KanBan UUID #' + randNum + ' :: ' + seconds + '.' + milliseconds);
			var date = new Date();
			var seconds = date.getTime();
			var milliseconds = date.getMilliseconds();
			var randNum = Math.random().toString(36);
			obj.id = id;
		}

		var status = String(obj.kanbanStatus || '');
		status = getValidStatus(status);
		obj.kanbanStatus = status;

		var txtNewItem = String(obj.title || 'Untitled');
		obj.title = txtNewItem;

		kData.push(obj);
		jQuery(area).find('div.jskanban_holder').find('ul#kanban-' + String(status)).append('<li id="' + SmartJS_CoreUtils.escape_html(id) + '" class="jskanban_box">' + SmartJS_CoreUtils.escape_html(txtNewItem) + '</li>');

	} //END FUNCTION

	this.saveBoard = function(area, evcode) {

		if((typeof evcode === 'function') && (evcode != null)) {
			evcode(kData);
		} //end if

		// TODO: on export parse as below and restore the order as in Kanban Board ... or not !?

		/*
		var theJson = [];
		jQuery(area).find('div.jskanban_holder').find('ul').find('li').each(function(index, element) {
			var li = jQuery(element);
			var id = li.attr('id') ? li.attr('id') : '';
			var title = li.text() ? li.text() : 'N/A';
			var objData = li.attr('data-kanban') || '';
			//console.log(objData);
			if(!objData) {
				objData = '';
			}
			try {
				objData = JSON.parse(objData);
				//console.log(objData);
			} catch(err){}
			if(!objData) {
				objData = {};
			}
			var pid = li.parent().attr('id') ? li.parent().attr('id') : '';
			if(pid != '') {
				if(!objData.id) {
					objData.id = String(id)
				}
				if(!objData.title) {
					objData.title = String(title)
				}
				objData.kanbanStatus = String(pid);
				theJson.push(objData);
				//console.log('Element [' + pid + ']: `' + title + '`' + ' @ ID: (' + id + ')');
			} //end if
		});
		if((typeof evcode === 'function') && (evcode != null)) {
			evcode(theJson);
		} //end if
		*/

	} //END FUNCTION


	var getValidStatus = function(status) {
		switch(String(status)) {
			case 'inprogress':
			case 'check':
			case 'done':
			case 'todo':
				break;
			default:
				status = 'todo';
		}
		return String(status);
	} //END FUNCTION


	var updateObjKStatus = function(id, status) {
		if(!id) {
			return false;
		}
		var obj;
		for(var i=0; i<kData.length; i++) {
			obj = kData[i];
			if(obj && obj.id) {
				if(String(id) === String(obj.id)) {
					obj.kanbanStatus = getValidStatus(status);
				}
			}
		}
		return true;
	} //END FUNCTION


} //END CLASS

// END
