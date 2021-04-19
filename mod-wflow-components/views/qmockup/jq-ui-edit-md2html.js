
// (c) 2017-2021 unix-world.org
// License: GPLv3
// v.20210411
// modified by unixman:
// 	* depends: smartJ$Utils.escape_html()

/*
 * QMockup Editor: jqueryUI plugin for editing the HTML-element’s text
 * LICENSE: MIT, (c) 2015 Jan Dittrich & Contributors
 */

// unixman
var mockEditTextVImageMkdElement = null;
$(function(){ // jpeg quality: 0.7 ; max image size: 100k
	smartJ$Browser.VirtualImageUploadHandler('img-uploader-id', 'img-uploader-preview', 0.5, 0.1, 500, 500, function(imgDataURL, w, h, isSVG, type, size){
		if(mockEditTextVImageMkdElement) {
			mockEditTextVImageMkdElement.on('blur', function(){
				mockEditTextVImageMkdElement.trigger('vimg:ok');
			});
			if(imgDataURL) {
				mockEditTextVImageMkdElement.val('![Image ' + smartJ$Utils.escape_html(type) + ' @ ' + smartJ$Utils.escape_html(size) + ' Bytes' + '](' + String(imgDataURL) + ')');
				mockEditTextVImageMkdElement.trigger('vimg:ok');
			}
		}
		mockEditTextVImageMkdElement = null;
	}, true);
});

(function($){

	$.widget('mock.editText', {

		options:{},

		widgetEventPrefix: 'inlineMDEdit',

		inputElement:null,

		_init:function(){ //init is called: 1) on creation ; 2) each time when the plugin in called without further arguments

			if(this.element.find('.editableArea').length === 0){
				return;
			} //if the element can't be edited TODO: cleaner solution?

			//if the editable element has a markdown area, render it to the element
			var editableContent = this.element.attr('data-editable-content');

		//	this.$editableElement.html(this.toHTML(editableContent)); // fix by unixman (avoid double encode)

		},

		_create:function(){//create is only fired on creation. Use it to create markup and bind events

			var idNr = this.element.attr('id').split('_')[1]; // takes the part behind the '_'

			var markdownConverter = null;

			this.$editableElement = this.element.find('#editableArea_' + idNr);

			this.editType = this.element.attr('data-editable-mode');

			if(this.editType === 'plain'){
				this.toHTML = function(string){
					return smartJ$Utils.escape_html(string); // unixman
				};
			} else if(this.editType === 'uielements'){
				this.toHTML = uiElementsConverter;
			} else {
				markdownConverter = new showdown.Converter({
					literalMidWordUnderscores:true,
					tables:true,
					extensions: ['htmlescape', 'mdui']
				});
				// mdui enables checkboxes and radio button lists in Markdown;
				this.toHTML = markdownConverter.makeHtml.bind(markdownConverter); // without the bind, jquery object is 'this', causing trouble ('Uncaught TypeError: globals.converter._dispatch is not a function')
			}

			this._on(this.element,{
				'dblclick': this._goToEditMode
			});

		},

		_destroy:function(){ //is called via an destroy event
			//remove here all additional Dom
			//and all elements which were not
			//added via this._on
		},

		_goToEditMode:function(event){

			var editableContent =  this.element.attr('data-editable-content');
			//write to edit window

			if($(event.target).closest('.mockElement')[0] !== this.element[0]){
				return;
			}

			var editablePosition = this.$editableElement.position();

			if(this.editType === 'plain'){
				this.inputElement = $('<input>', {
					type:  'text',
					class: 'plaintextinput',
					title: 'plain text entry'
				});
			} else if(this.editType === 'uielements'){
				this.inputElement = $('<input>', {
					type:  'text',
					class: 'uielementsinput',
					title: 'Example: Item; 2nd Item; * I\'m highlighted via ›*‹ at begin'
				});
			} else {
				if((this.editType === 'markdown-image') && (editableContent !== '![Image]()')) {
					return; // unixman: dissalow re-edit image
				}
				this.inputElement = $('<textarea>', {
					class: 'markdowninput',
					title: 'Markdown: **bold**, *italic* etc. + ( ) for radio, [ ] for checkboxes'
				});
				if(this.editType === 'markdown-image'){
					this.inputElement.prop('readonly',true).on('click keydown', function(){
						mockEditTextVImageMkdElement = $(this);
						jQuery('#img-uploader-id').trigger('click');
						/* this makes component unusable in webkit
						setTimeout(function(){
							mockEditTextVImageMkdElement.focus();
							mockEditTextVImageMkdElement.on('blur', function(){
								mockEditTextVImageMkdElement.trigger('vimg:ok');
							});
						}, 250);
						*/
						return false;
					});
				}
			}

			this.inputElement.css({
				position: 'absolute',
				top: parseInt(editablePosition.top)+'px',
				left: parseInt(editablePosition.left)+'px',
				width: parseInt(this.$editableElement.width())
			});

			if(this.editType === 'markdown-image'){
				this._on(this.inputElement,{
					'vimg:ok': this._leaveEditMode
				});
			} else {
				this._on(this.inputElement,{
					'blur': this._leaveEditMode
				});
			}
			this._off(this.element, 'dblclick');

			editableContent = $('<div></div>').html(editableContent).text(); // unixman: convert back to html
			//console.log(editableContent);

			this.inputElement.val(editableContent);
			this.element.append(this.inputElement);
			this.element.addClass('isEditing');
			this.inputElement.focus();
			this._trigger('isEditing');

		},

		_leaveEditMode:function(){

			var editableContent = this.inputElement.val(); //reads what the user wrote

			var html = this.toHTML(editableContent);

			// convert to html + write markdown to data attribute
			this.element.attr('data-editable-content', smartJ$Utils.escape_html(editableContent));

			// write content
			this.$editableElement.html(html);
			this.element.removeClass('isEditing');
			this.inputElement.remove();
			this._on(this.element,{'dblclick':this._goToEditMode});
			this._trigger('leaveEditing');

		}

	});

	function uiElementsConverter(string){

		var itemsArray = string.split(/;/);
		var highlightRegex = /^\s*\*/; //if this matches, the element around this text should be emphazied
		var newString = '';

		itemsArray.forEach(function(value, index, array){
			if(value === ''){
				return false;
			}
			// if the string does start with an *
			if(value.match(highlightRegex)!==null){
				// ... add the class 'highlighted'
				newString = newString+'<li class="item-highlighted">';
				// and strip the *
				value = value.replace(highlightRegex,'');
			} else {
				newString = newString+'<li>';
			}
			// anyway, close the li
			value = smartJ$Utils.escape_html(value); // unixman
			newString = newString + value + '</li>';
		});

		return '<ul>' + newString + '</ul>';

	}

})(jQuery);

// #END
