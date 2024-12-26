
// (c) 2017-2021 unix-world.org
// License: GPLv3
// v.20210413
// modified by unixman:
// 	* save / load just canvas contents
// 	* cleanup ui garbage: droppable / draggable / resizable

/*
 * QMockup Editor: JS
 * LICENSE: MIT, (c) 2015 Jan Dittrich & Contributors
 */

var qMockupEditor = new function() { // START CLASS

	// :: static
	var _class = this; // self referencing

	var toolBarInitialized = false;
	var widgetsInitialized = false;


	this.setupElements = function() {
		//-- setup canvas
		makeDropableElement("#canvas");
		//-- setup elements already on the page
		$(".mockElement").each(function(index, element){
			$(element).find(".ui-resizable-handle").remove(); //otherwise we get them twice, they are saved with the file and created again when the element is initialized
			setupElement(element);
		});
		//-- make new sidebar elements draggable
		$(".newMockElement").draggable({
			distance: 4,
			disabled:false,
			appendTo: canvasSelector,
			helper:"clone",
			revert:"invalid",
			zIndex:999
		});
		//--
		$('#canvas').trigger('click');
		//--
	} //END FUNCTION


	this.setupEditor = function(widgetsDivID, widgetsDivMaxWidth) {
		//--
		_class.setupToolBar();
		_class.setupWidgets(widgetsDivID, widgetsDivMaxWidth);
		_class.setupCanvas();
		//--
	} //END FUNCTION


	this.setupToolBar = function() {
		//--
		if(toolBarInitialized) {
			return;
		} //end if
		//--
		var html = '<div id="toolbar" title="Tool Bar"><button class="delete-element-button" title="deletes the currently selected element (del)">✖ Delete Element</button><button class="undelete-element-button" title="undeletes most recently deleted element (Ctrl+Z)">↶ Undelete Last</button><button class="duplicate-element-button" title="duplicates currently selected element (Ctrl+D)">⚭ Duplicate Element</button><br><button class="change-canvasize-button" title="change size of the area you place elements on">⇲ Change Canvas Size</button><span><label>Width:</label><input class="change-canvasize-w" name="width" type="text">px</span> &nbsp; <span><label>Height:</label><input class="change-canvasize-h" name="height" type="text">px</span></div>';
		html += '<div id="qmockup-editor___SaveDivHelper" style="position:fixed; bottom:1px; right:1px; width:1px; height:1px; display:none;"></div>';
		//--
		$('body').append(html);
		//--
		toolBarInitialized = true;
		//--
	} //END FUNCTION


	this.setupWidgets = function(divID, maxWidth) {
		//--
		if(widgetsInitialized) {
			return;
		} //end if
		//--
		if(!divID) {
			return;
		} //end if
		//--
		if((typeof maxWidth == 'undefined') || (!maxWidth)) {
			maxWidth = 500;
		}
		maxWidth = parseInt(maxWidth);
		if(maxWidth < 300) {
			maxWidth = 300;
		}
		if(maxWidth < 1200) {
			maxWidth = 1200;
		}
		//--
		var html = '<!-- widgets --><ul id="widgetCollection" title="*** Mockup: Widgets Collection ***">';
		html += '<li title="Button"><div class="newMockElement button" data-editable-content="Button" data-editable-mode="plain"><span class="editableArea">Button</span></div></li>';
		html += '<li title="Tabs"><div class="newMockElement tabs" data-editable-content="Tab1;*Tab2; Tab3" data-editable-mode="uielements"><div class="editableArea"><ul><li>Tab1</li><li class="item-highlighted">Tab2</li><li>Tab3</li></ul></div></div></li>';
		html += '<li title="Short Text Label"><div class="newMockElement label" data-editable-content="Label" data-editable-mode="plain"><div class="editableArea">Label</div></div></li>';
		html += '<li title="A-Link"><div class="newMockElement link" data-editable-content="Link" data-editable-mode="plain"><div class="editableArea">Link</div></div></li>';
		html += '<li title="Checkbox List"><div class="newMockElement optionCheckbox" data-editable-content="option; *other option" data-editable-mode="uielements"><div class="editableArea"><ul><li>option</li><li class="item-highlighted">other option</li></ul></div></div></li>';
		html += '<li title="Radio Button List"><div class="newMockElement optionRadiobutton" data-editable-content="option; *other option" data-editable-mode="uielements"><div class="editableArea"><ul><li>option</li><li class="item-highlighted">other option</li></ul></div></div></li>';
		html += '<li title="Dropdown Element"><div class="newMockElement dropdownList" data-editable-content="DropdownTitle" data-editable-mode="plain"><span class="editableArea">Dropdown Title</span><span class="mockelement-dropdown-arrow">▾</span></div></li>';
		html += '<li title="Spinner Element"><div class="newMockElement spinner" data-editable-content="10" data-editable-mode="plain"><span class="editableArea">10</span></div></li>';
		html += '<li title="Slider Element"><div class="newMockElement slider"><span></span></div></li>';
		html += '<li title="Progress Bar Element"><div class="newMockElement loadingIndicator"><div class="bar"></div>70%</div></li>';
		html += '<li title="Headline 1"><div class="newMockElement headline" data-editable-content="Headline1" data-editable-mode="plain"><h1 class="editableArea">Headline1</h1></div></li>';
		html += '<li title="Headline 2"><div class="newMockElement headline" data-editable-content="Headline2" data-editable-mode="plain"><h2 class="editableArea">Headline2</h2></div></li>';
		html += '<li title="Headline 3"><div class="newMockElement headline" data-editable-content="Headline3" data-editable-mode="plain"><h3 class="editableArea">Headline3</h3></div></li>';
		html += '<li title="Headline 4"><div class="newMockElement headline" data-editable-content="Headline4" data-editable-mode="plain"><h4 class="editableArea">Headline4</h4></div></li>';
		html += '<li title="Headline 5"><div class="newMockElement headline" data-editable-content="Headline5" data-editable-mode="plain"><h5 class="editableArea">Headline5</h5></div></li>';
		html += '<li title="Headline 6"><div class="newMockElement headline" data-editable-content="Headline6" data-editable-mode="plain"><h6 class="editableArea">Headline6</h6></div></li>';
		html += '<li title="Paragraph (Markdown)"><div class="newMockElement paragraph" data-editable-content="' + '\nParagraph (Markdown **Supported** !)\n' + '"><div class="editableArea">Paragraph (Markdown<strong> Supported</strong>!)</div></div></li>';
		html += '<li title="Table (Markdown)"><div class="newMockElement table" data-editable-content="' + '\n| h1    | h2      |      h3 |\n|:------|:-------:|--------:|\n| lorem | ipsum   | dolor   |\n| first | second  | third   |\n' + '"><div class="editableArea"><table><thead><tr><th style="text-align:left;">h1</th><th style="text-align:center;">h2</th><th style="text-align:right;">h3</th></tr></thead><tbody><tr><td style="text-align:left;">lorem</td><td style="text-align:center;">ipsum</td><td style="text-align:right;">dolor</td></tr><tr><td style="text-align:left;">first</td><td style="text-align:center;">second</td><td style="text-align:right;">third</td></tr></tbody></table></div></div></li>';
		html += '<li title="Dialog or Alert"><div class="newMockElement dialogWindow" data-editable-content="Dialog" data-editable-mode="plain"><div class="dialogWindow-bar"><span class="editableArea">Dialog</span><span class="dialogWindow-closeButton">×</span></div></div></li>';
		html += '<li title="Rectangle"><div class="newMockElement rect"></div></li>';
		html += '<li title="Dark Rectangle"><div class="newMockElement grayrect"></div></li>';
		html += '<li title="Invisible Rectangle"><div class="newMockElement invisiblerect"><div class="desc">Invisible Rectangle<br>(for grouping)</div></div></li>';
		html += '<li title="Horizontal List"><div class="newMockElement entries-horizontal" data-editable-content="Entry1;Entry2;*Entry3" data-editable-mode="uielements"><div class="editableArea"><ul><li>Entry1</li><li>Entry2</li><li class="item-highlighted">Entry3</li></ul></div></div></li>';
		html += '<li title="Vertical List"><div class="newMockElement entries-vertical" data-editable-content="Entry1;Entry2;*Entry3" data-editable-mode="uielements"><div class="editableArea"><ul><li>Entry1</li><li>Entry2</li><li class="item-highlighted">Entry3</li></ul></div></div></li>';
		html += '<li title="Horizontal Boxes List / Horizontal ToolBar"><div class="newMockElement boxes-horizontal" data-editable-content="Entry1;Entry2;*Entry3" data-editable-mode="uielements"><div class="editableArea"><ul><li>Entry1</li><li>Entry2</li><li class="item-highlighted">Entry3</li></ul></div></div></li>';
		html += '<li title="Vertical Boxes List / Vertical ToolBar"><div class="newMockElement boxes-vertical" data-editable-content="Entry1;Entry2;*Entry3" data-editable-mode="uielements"><div class="editableArea"><ul><li>Entry1</li><li>Entry2</li><li class="item-highlighted">Entry3</li></ul></div></div></li>';
		html += '<li title="Icons Toolbar"><div class="newMockElement boxes-horizontal boxes-iconbar-horizontal" data-editable-content="☰;⌂;⚑;⚙;✂;✐;✔;✘;↶;↷;◂;▸;▴;▾;©" data-editable-mode="uielements"><div class="editableArea"><ul><li>☰</li><li>⌂</li><li>⚑</li><li>⚙</li><li>✂</li><li>✐</li><li>✔</li><li>✘</li><li>↶</li><li>↷</li><li>◂</li><li>▸</li><li>▴</li><li>▾</li><li>©</li></ul></div></div></li>';
		html += '<li title="Image Placeholder"><div class="newMockElement imageplaceholder" data-editable-content="Image Placeholder" data-editable-mode="plain"><div class="editableArea">Image Placeholder</div></div></li>';
		html += '<li title="Image Data/URL"><div class="newMockElement actualimage" data-editable-content="![Image]()" data-editable-mode="markdown-image"><div class="editableArea">Image Data/URL (double-click to open edit area then click again to add image)</div></div></li>';
		html += '<li title="Sticky Note (Markdown)"><div class="newMockElement meta-element note" data-editable-content="Sticky Note!" data-editable-mode="markdown"><div class="editableArea">Sticky Note!</div></div></li>';
		html += '<li title="Sticky Arrow-Note"><div class="newMockElement meta-element arrownote" data-editable-content="Arrow Note!" data-editable-mode="plain"><div class="editableArea">Arrow Note!</div></div></li>';
		html += '</ul><!-- #end widgets -->';
		//--
		$('#' + divID).empty().css({ 'display':'block', 'width':'300px', 'height':'100%', 'padding':'5px' }).css('background', '#FFFFFF', 'important').html(html);
		$('#' + divID).resizable({ // setup sidebar resize
			handles: 'e',
			maxWidth: maxWidth,
		});
		//--
		widgetsInitialized = true;
		//--
	} //END FUNCTION


	this.setupCanvas = function() {
		//--
		if(!Mousetrap) {
			var Mousetrap = null;
		} //end if
		//--
		var $container = $("#canvas");
		var currentWidth = $container.width();
		var currentHeight = $container.height();
		//--
		$('#toolbar .change-canvasize-w').val(currentWidth);
		$('#toolbar .change-canvasize-h').val(currentHeight);
		//-- setup toolbar
		$('#toolbar').draggable().position({
			my: 'left top',
			at: 'left+3 top+3',
			of: $container
		});
		$("#toolbar .delete-element-button").click(qMockupEditor.deleteElement); // toolbar delete button
		if(Mousetrap) {
			Mousetrap.bind(['del','backspace'],function(e){
				if(e.preventDefault) {
					e.preventDefault();
				}
				qMockupEditor.deleteElement();
			}); //keyboard shortcut
		} //end if
		$("#toolbar .undelete-element-button").click(qMockupEditor.undeleteElement);
		if(Mousetrap) {
			Mousetrap.bind(['ctrl+z','command+z'], qMockupEditor.undeleteElement);
		} //end if
		$("#toolbar .duplicate-element-button").click(qMockupEditor.duplicateElement);
		if(Mousetrap) {
			Mousetrap.bind(['ctrl+d','command+d'],function(e){
				if (e.preventDefault) {
					e.preventDefault();
				}
				qMockupEditor.duplicateElement();
			});
		} //end if
		$("#toolbar .change-canvasize-button").click(function(){
			var w = parseInt($('#toolbar .change-canvasize-w').val());
			var h = parseInt($('#toolbar .change-canvasize-h').val());
			if(w >= 300 && h >= 300) {
				$container.width(w).height(h);
				$('#widgetsArea').height(h-10);
			}
		});
		//--
	} //END FUNCTION


	this.getCanvasData = function() {
		//--
		var $container = $("#canvas");
		$container.trigger('click'); // unselect elem
		var theWidth = $container.width();
		var theHeight = $container.height();
		var htmlCode = $container.html(); // unixman (save just canvas contents ...)
		//-- fix: cleanup ui garbage: droppable / draggable / resizable
		$('#qmockup-editor___SaveDivHelper').empty().html(htmlCode);
		htmlCode = '';
		$('#qmockup-editor___SaveDivHelper .mockElement').droppable().draggable().resizable(); // need this before reset below to recreate UI objects
		$('#qmockup-editor___SaveDivHelper .mockElement').droppable('destroy').draggable('destroy').resizable('destroy'); // remove UI garbage / classes
		htmlCode = $('#qmockup-editor___SaveDivHelper').html();
		$('#qmockup-editor___SaveDivHelper').empty().html(''); // cleanup
		//--
		var dateobj = new Date();
		//--
		return {
			docTitle: '', // to be updated later
			docDate: String(dateobj.toISOString()),
			docType: 'smartWorkFlow.MockUp',
			docVersion: '1.0',
			dataFormat: 'text/html',
			data: {
				canvasWidth: parseInt(theWidth),
				canvasHeight: parseInt(theHeight),
				canvasData: String(htmlCode)
			}
		};
		//--
	} //END FUNCTION


	this.loadDocumentData = function(doc) {
		//--
		if(typeof doc == 'undefined') {
			return;
		} //end if
		//--
		if(!(doc.hasOwnProperty('data'))) {
			return;
		} //end if
		if(!(doc.data.hasOwnProperty('canvasData'))) {
			return;
		} //end if
		var w = null;
		var h = null;
		if((doc.data.hasOwnProperty('canvasWidth')) && (doc.data.hasOwnProperty('canvasHeight'))) {
			w = parseInt(doc.data.canvasWidth);
			h = parseInt(doc.data.canvasHeight);
		} //end if
		//--
		_class.loadDocumentCode(String(doc.data.canvasData), w, h);
		//--
	} //END FUNCTION


	this.loadDocumentCode = function(html, w, h) {
		if((typeof w != 'undefined') && (w != null) && (w >= 300)) {
			if((typeof h != 'undefined') && (h != null) && (h >= 300)) {
				$('#canvas').width(w).height(h);
				$('#toolbar .change-canvasize-w').val(w);
				$('#toolbar .change-canvasize-h').val(h);
				$('#widgetsArea').height(h-10);
			} //end if
		} //end if
		$('#canvas').html(html); // unixman (load canvas contents ...)
	} //END FUNCTION


	this.deleteElement = function(){
		var $canvas = $(canvasSelector);
		var $element2BDeleted = $canvas.find(element2BDeletedSelector);
		recentlyDeleted.$formerParent = $element2BDeleted.parent(); //remember parent for re-attachment on redo
		recentlyDeleted.$element = $element2BDeleted.detach(); //delete element and store it  for re-attachment on redo
	} //END FUNCTION


	this.undeleteElement = function(){
		if(!recentlyDeleted.$element || !recentlyDeleted.$formerParent ){
			return;
		} //end if
		recentlyDeleted.$formerParent.append(recentlyDeleted.$element);
	} //END FUNCTION


	this.duplicateElement = function() {
		var $canvas = $(canvasSelector);
		var $element2BDuplicated = $canvas.find(element2BDeletedSelector);
		if($element2BDuplicated.length === 0){
			return; //no element selected, duplication of selected element is futile.
		} //end if
		var clonedElement = $element2BDuplicated.clone(false); //clone all children too, don't clone events.
		//some elements have id
		var reassignID = function($element){
			var oldId = $element.attr("id")||"";
			//var oldIdNr = oldId.match(/^mockElement_(\d+)/)[1]; //[1] to get the first capture group, , the id number.
			var oldIdNr = oldId.match(/^mockElement_([0-9a-f]+)/)[1]; //[1] to get the first capture group, , the id sha1.
			//console.log(oldIdNr);
			if(oldId.length >0){ //if it actually had an Id
				//var newIdNr = parseInt(Math.random()*100000000000000);
				var newIdNr = generateElemUuid();
				$element.attr("id", "mockElement_"+newIdNr);
				$element.find("#editableArea_"+oldIdNr).attr("id", "editableArea_"+newIdNr);
			}
		};
		clonedElement.find(".mockElement").each(function(index, element){
			reassignID($(element));
		});
		reassignID($(clonedElement));
		var originalElementPos = $element2BDuplicated.position();
		clonedElement.css({
			left:(originalElementPos.left+20)+"px",
			top:(originalElementPos.top+20)+"px"
		});
		clonedElement.removeClass("custom-selected");
		clonedElement.appendTo($element2BDuplicated.parent());
		clonedElement.find(".mockElement").each(function(index, element){
			setupElement(element);
		});
		setupElement(clonedElement);
	} //END FUNCTION


	//#####


	var setupElement = function(element){
		makeMovableElement(element);
		makeDropableElement(element);
		$(element).editText();
		makeSelectableElement(element,"#canvas");
	} //END FUNCTION


	var makeMovableElement = function(element){
		//in case it already has the handle-elements (markup was duplicated or saved and now reloaded...)
		$(element).children(".ui-resizable-handle").remove(); //find handles, which are direct decendants... ; remove them (not useful when displaying)
		//now, make it draggable.
		$(element).draggable({
			distance: 4,
			disabled:false,
			revert:"invalid",
			zIndex: 999
		}).resizable({
			handles:"all"
		});
	} //END FUNCTION

	var generateElemUuid = function() {
		var date = new Date();
		var dt = date.toISOString();
		var randNum = Math.random().toString(36);
		var str = 'This is a UUID for qMockupEditor' + ' @ ' + randNum + ' :: ' + dt;
		//var str = 'This is a UUID for qMockupEditor' + ' @ ' + ' :: ' + dt;
		//console.log(str);
		var uuid = smartJ$CryptoHash.sha1(str);
		return uuid;
	} //END FUNCTION

	var makeDropableElement= function(element) {
		$(element).droppable({
			accept: ".mockElement, .newMockElement",
			tolerance: "fit",
			greedy: true, //you can only attach it to one element, otherwise every nested dropable recieves
			hoverClass: "drop-hover",
			drop: function( event, ui ) {
				//calculate offset of both
				var elementToAppend = null;
				if(ui.draggable.hasClass("newMockElement")) {//if this is a new element
					elementToAppend = ui.draggable.clone(false);
					elementToAppend.removeClass("newMockElement");
					elementToAppend.addClass("mockElement");
					elementToAppend.css("position","absolute");//always has relative otherwise = glitches
					//var idnr = parseInt(Math.random()*100000000000000); //not exactly a UUID but does the job for now.
					var idnr = generateElemUuid();
					elementToAppend.attr("id","mockElement_"+idnr);
					//TODO: assign an id "editableArea"+idnr
					elementToAppend.find(".editableArea").first().attr("id","editableArea_"+idnr);
					setupElement(elementToAppend);
				} else {
					elementToAppend = ui.draggable;
				}
				var draggableOffset = ui.helper.offset(); //was ui.draggable
				var droppableOffset = $(this).offset();
				var newLeft =  draggableOffset.left - droppableOffset.left;
				var newTop = draggableOffset.top -  droppableOffset.top;
				elementToAppend.appendTo($(this)).css({top:newTop+"px", left:newLeft+"px"});
			}
		});//droppable End
	} //END FUNCTION


	var makeSelectableElement = function(element,selectorCanvasParam){
		var $element = $(element);

		var selectorCanvas = selectorCanvasParam ? selectorCanvasParam : canvasSelector; //if selectorCanvas is defined, set it to a standard value

		var selectedClassParam = "custom-selected";
		var elementSelector = ".mockElement";

		var $canvas = $(selectorCanvas);

		//deselect if canvas is clicked
		$canvas.click(function(event){
			if(event.target=== $canvas[0]){
				$canvas.find("." + selectedClassParam).removeClass(selectedClassParam);
			}
		});

		//select the this element, deselect others. This is inefficient when you apply the function in batch, but makes much sense, when initializing single elements (dragging them on canvas) without, a new element is deselected, and needs to be clicked again. Since the performance is o.k. for now, I leave it like it is.
		$canvas.find("." + selectedClassParam).removeClass(selectedClassParam);
		$element.addClass(selectedClassParam); /*custom selected, since there is a jQuery UI selected, that might be used later*/

		$element.mousedown(function(event){
			if($(event.target).closest(elementSelector)[0] === $element[0]){ //either it is the same element that was clicked, or the element is the clicked element’s the first parent that is a mock element.
				$canvas.find("." + selectedClassParam).removeClass(selectedClassParam);
				$element.addClass(selectedClassParam); // custom selected, since there is a jQuery UI selected, that might be used later
			}
		});

	} //END FUNCTION


	var recentlyDeleted = { //hope saving these does not cause memory leaks. FUD.
		$element: null,
		$formerParent: null
	};


	var canvasSelector = "body";
	var element2BDeletedSelector = ".custom-selected";


	//loader functions
	var readStringToJquery = function(string){
		var $importedHTML =  $(string);//that feels wired. I suppose I should at least sanitize scripts.
		var $sanitizedHTML = $importedHTML.remove("script"); //test if the scripts dont execute or if this is FUD
		return $sanitizedHTML;
	};


}; //END FUNCTION

// #END
