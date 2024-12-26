/**
 * Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/* CkEditor Setup */

if(CKEDITOR.env.ie && CKEDITOR.env.version < 9 ) {
	CKEDITOR.tools.enableHtml5Elements(document);
} //end if

// The trick to keep the editor in the sample quite small
// unless user specified own height.
CKEDITOR.config.height = 480;
CKEDITOR.config.width = 960;

//-- unixman fixes v.160215
CKEDITOR.config.language = 'en';
CKEDITOR.config.startupOutlineBlocks = true; // enable show blocks by default
CKEDITOR.on('instanceReady', function(ev) {
	ev.editor.dataProcessor.writer.selfClosingEnd = '>'; // fix tag ends
});
CKEDITOR.on('instanceCreated', function(ev) {
		ev.editor.on('contentDom', function() {
			ev.editor.document.on('drop', function(ev) {
				ev.data.preventDefault(true); // Fix drag and drop problem
			}
		);
	});
});
//CKEDITOR.config.allowedContent = true; // to allow all tags includding <script>
CKEDITOR.config.extraAllowedContent = [ 'section[id]', 'script', 'span[*]', 'a[data-*]', 'meta[*]' ];
CKEDITOR.config.disallowedContent = [ 'table{width,height}', 'tbody[*]', 'img{width,height}' ];
//-- to catch the default (unmodified behaviour uncomment next 3 lines)
//CKEDITOR.config.protectedSource.push(/<script[\s\S]*?(<\/script>|$)/gi); 	// protect <script></script>
//CKEDITOR.config.protectedSource.push(/<noscript[\s\S]*?<\/noscript>/gi); 	// protect <noscript></noscript>
//CKEDITOR.config.protectedSource.push(/<meta[\s\S]*?\/?>/gi); 				// protect <meta>
//--
// pastefromword
//CKEDITOR.config.plugins='dialogui,dialog,basicstyles,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,dialogadvtab,div,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,format,forms,horizontalrule,htmlwriter,image,indent,indentlist,indentblock,justify,link,list,liststyle,magicline,maximize,pastetext,preview,removeformat,resize,selectall,showblocks,showborders,sourcearea,specialchar,tab,table,tabletools,toolbar,undo,wysiwygarea,video,audio,wordcount,notification';
//CKEDITOR.config.plugins='dialogui,dialog,a11yhelp,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,dialogadvtab,div,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,font,format,forms,horizontalrule,htmlwriter,iframe,image,indent,indentlist,indentblock,justify,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastefromword,pastetext,preview,print,removeformat,resize,save,selectall,showblocks,showborders,smiley,sourcearea,specialchar,stylescombo,tab,table,tableresize,tabletools,templates,toolbar,undo,wysiwygarea,audio,video,base64image,wordcount,notification';
//--
CKEDITOR.config.plugins='dialogui,dialog,basicstyles,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,dialogadvtab,elementspath,enterkey,entities,popup,filebrowser,find,link,fakeobjects,floatingspace,listblock,richcombo,format,horizontalrule,htmlwriter,image,indent,indentlist,indentblock,justify,list,liststyle,magicline,maximize,pastetext,preview,removeformat,resize,selectall,showblocks,showborders,sourcearea,tab,table,tabletools,toolbar,undo,wysiwygarea,wordcount,notification';
CKEDITOR.config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'selection', 'spellchecker', 'find', 'editing' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'about', groups: [ 'about' ] },
		{ name: 'colors', groups: [ 'colors' ] }
	];
CKEDITOR.config.removeButtons = 'SpecialChar';
//--
CKEDITOR.config.filebrowserBrowseUrl = 'sample-gallery.html';
CKEDITOR.config.image_previewText = CKEDITOR.tools.repeat(' ',1); // reset lorem ipsum text in preview
CKEDITOR.on('dialogDefinition', function(ev) {

	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;

	if(dialogName == 'image') {
		var infoTab = dialogDefinition.getContents('info');
		var advancedTab = dialogDefinition.getContents('advanced');

		var styleField = advancedTab.get('txtdlgGenStyle');
		styleField['default'] = 'max-width:100%!important;';

		dialogDefinition.removeContents( 'Link' ); // Remove Link Tab From Image Dialog popup

		infoTab.remove('basic'); // remove Alignment from Image Info tab
		advancedTab.remove('linkId'); // Remove linkid from Image Advanced tab
		advancedTab.remove('cmbLangDir'); // Remove Languade dir from Image Advanced tab
		advancedTab.remove('txtLangCode'); // Remove Language code from Image Advanced tab
		advancedTab.remove('txtGenLongDescr'); // Remove Long title description from Image Advanced tab

		var onOk = dialogDefinition.onOk;

		dialogDefinition.onOk = function( e ) {
			var input = this.getContentElement('info', 'txtAlt');
			var inputTitle = this.getContentElement('advanced', 'txtGenTitle');
			var imageSrcUrl = input.getValue();

			//! Manipulate imageSrcUrl and set it
			inputTitle.setValue( imageSrcUrl );

			onOk && onOk.apply( this, e );
		};

	}
}); // Fix image src on OK when insert Image in textarea + Removed input for width and hegight image
//--

var MyEditor;

function initSample() {
	//--
	MyEditor = CKEDITOR.replace('editor');
	//--
	MyEditor.on('change', function(evt) {
		//console.log(evt.editor.getData());
		document.getElementById('editor').value = evt.editor.getData(); // sync text area
	});
	//--
} //END FUNCTION

/*
var initSample = ( function() {
	var wysiwygareaAvailable = isWysiwygareaAvailable(),
		isBBCodeBuiltIn = !!CKEDITOR.plugins.get( 'bbcode' );

	return function() {
		var editorElement = CKEDITOR.document.getById( 'editor' );

		// :(((
		if ( isBBCodeBuiltIn ) {
			editorElement.setHtml(
				'Hello world!\n\n' +
				'I\'m an instance of [url=http://ckeditor.com]CKEditor[/url].'
			);
		}

		// Depending on the wysiwygare plugin availability initialize classic or inline editor.
		if ( wysiwygareaAvailable ) {
			CKEDITOR.replace( 'editor' );
		} else {
			editorElement.setAttribute( 'contenteditable', 'true' );
			CKEDITOR.inline( 'editor' );

			// TODO we can consider displaying some info box that
			// without wysiwygarea the classic editor may not work.
		}
	};

	function isWysiwygareaAvailable() {
		// If in development mode, then the wysiwygarea must be available.
		// Split REV into two strings so builder does not replace it :D.
		if ( CKEDITOR.revision == ( '%RE' + 'V%' ) ) {
			return true;
		}

		return !!CKEDITOR.plugins.get( 'wysiwygarea' );
	}
} )();
*/
