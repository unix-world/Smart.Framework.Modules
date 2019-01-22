/**
 *  The "codemirror" plugin. It's indented to enhance the
 *  "sourcearea" editing mode, which displays the xhtml source code with
 *  syntax highlight and line numbers.
 * Licensed under the MIT license
 * CodeMirror Plugin: http://codemirror.net/ (MIT License)
 */

// modified by unixman to remove codemirror from inclusion and load code mirror from external source !

(function() {
	CKEDITOR.plugins.add('codemirror', {
	//	icons: 'searchcode', // %REMOVE_LINE_CORE%
		lang: 'en,ro,de,fr,es,pt,it,nl,fi,da,no,sv,ja,cs,el,et,hu,pl,bg,ru', // %REMOVE_LINE_CORE%
		version: 1.13,
		init: function (editor) {
			var rootPath = this.path,
				defaultConfig = {
					autoCloseBrackets: true,
					autoCloseTags: true,
					autoFormatOnStart: false,
					autoFormatOnUncomment: true,
					continueComments: true,
					enableCodeFolding: true,
					enableCodeFormatting: true,
					enableSearchTools: true,
					highlightMatches: true,
					indentWithTabs: false,
					lineNumbers: true,
					lineWrapping: true,
					mode: 'htmlmixed',
					matchBrackets: true,
					matchTags: true,
					showFormatButton: true,
					showSearchButton: true,
					showTrailingSpace: true,
					styleActiveLine: true,
					theme: 'uxw'
				};

			CKEDITOR.document.appendStyleSheet(rootPath + "css/codemirror.css");

			// Get Config & Lang
			var config = CKEDITOR.tools.extend(defaultConfig, editor.config.codemirror || {}, true),
				lang = editor.lang.codemirror;

			// check for old config settings for legacy support
			if (editor.config.codemirror_theme) {
				config.theme = editor.config.codemirror_theme;
			}
			if (editor.config.codemirror_autoFormatOnStart) {
				config.autoFormatOnStart = editor.config.codemirror_autoFormatOnStart;
			}

			// automatically switch to bbcode mode if bbcode plugin is enabled
			if (editor.plugins.bbcode && config.mode.indexOf("bbcode") <= 0) {
				config.mode = "bbcode";
			}

			// Source mode isn't available in inline mode yet.
			if (editor.elementMode === CKEDITOR.ELEMENT_MODE_INLINE || editor.plugins.sourcedialog) {

				// Override Source Dialog
				CKEDITOR.dialog.add('sourcedialog', function (editor) {

				//	var size = CKEDITOR.document.getWindow().getViewPaneSize(),
				//		width = Math.min(size.width - 70, 800),
				//		height = size.height / 1.5,
					var width = Math.floor(parseInt($(window).width()) * 0.75), // unixman
						height = Math.floor(parseInt($(window).height()) * 0.75),
						oldData;

					function loadCodeMirrorInline(editor, textarea) {
						window["codemirror_" + editor.id] = CodeMirror.fromTextArea(textarea, {
							mode: config.mode,
							matchBrackets: config.matchBrackets,
							matchTags: config.matchTags,
							workDelay: 300,
							workTime: 35,
							readOnly: editor.readOnly,
							lineNumbers: config.lineNumbers,
							lineWrapping: config.lineWrapping,
							autoCloseTags: config.autoCloseTags,
							autoCloseBrackets: config.autoCloseBrackets,
							highlightSelectionMatches: config.highlightMatches,
							continueComments: config.continueComments,
							indentWithTabs: config.indentWithTabs,
							theme: config.theme,
							showTrailingSpace: config.showTrailingSpace,
							showCursorWhenSelecting: true,
							styleActiveLine: config.styleActiveLine,
							viewportMargin: Infinity,
							foldGutter: true,
							gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
							onKeyEvent: function (codeMirror_Editor, evt) {
								if (config.enableCodeFormatting) {
									var range = getSelectedRange();
									if (evt.type === "keydown" && evt.ctrlKey && evt.keyCode === 75 && !evt.shiftKey && !evt.altKey) {
										window["codemirror_" + editor.id].commentRange(true, range.from, range.to);
									} else if (evt.type === "keydown" && evt.ctrlKey && evt.keyCode === 75 && evt.shiftKey && !evt.altKey) {
										window["codemirror_" + editor.id].commentRange(false, range.from, range.to);
										if (config.autoFormatOnUncomment) {
											window["codemirror_" + editor.id].autoFormatRange(range.from, range.to);
										}
									} else if (evt.type === "keydown" && evt.ctrlKey && evt.keyCode === 75 && !evt.shiftKey && evt.altKey) {
										window["codemirror_" + editor.id].autoFormatRange(range.from, range.to);
									}
									/*else if (evt.type === "keydown") {
										CodeMirror.commands.newlineAndIndentContinueMarkdownList(window["codemirror_" + editor.id]);
									}*/
								}
							}
						});

						var holderHeight = height + 'px';
						var holderWidth = width + 'px';

						// Store config so we can access it within commands etc.
						window["codemirror_" + editor.id].config = config;

						if (config.autoFormatOnStart) {

								window["codemirror_" + editor.id].autoFormatAll({
									line: 0,
									ch: 0
								}, {
									line: window["codemirror_" + editor.id].lineCount(),
									ch: 0
								});

						}

						function getSelectedRange() {
							return {
								from: window["codemirror_" + editor.id].getCursor(true),
								to: window["codemirror_" + editor.id].getCursor(false)
							};
						}

						window["codemirror_" + editor.id].on("change", function () {
							window["codemirror_" + editor.id].save();
							editor.fire('change', this);
						});

						window["codemirror_" + editor.id].setSize(holderWidth, holderHeight);

						// Enable Code Folding (Requires 'lineNumbers' to be set to 'true')
						if (config.lineNumbers && config.enableCodeFolding) {
							window["codemirror_" + editor.id].on("gutterClick", window["foldFunc_" + editor.id]);
						}
						// Run config.onLoad callback, if present.
						if (typeof config.onLoad === 'function') {
							config.onLoad(window["codemirror_" + editor.id], editor);
						}

						// inherit blur event
						window["codemirror_" + editor.id].on("blur", function () {
							editor.fire('blur', this);
						});
					}

					return {
						title: editor.lang.sourcedialog.title,
						minWidth: width,
						minHeight: height,
						resizable : CKEDITOR.DIALOG_RESIZE_NONE,
						onShow: function () {
							// Set Elements
							this.getContentElement('main', 'data').focus();

							var textArea = this.getContentElement('main', 'data').getInputElement().$;

							// Load the content
							this.setValueOf('main', 'data', oldData = editor.getData());

							if (typeof (CodeMirror) == 'undefined') {
								alert('CodeMirror JS Not Loaded (1) ...'); // unixman
								throw 'codemirror.js is missing (1) ...';
							} else {
								loadCodeMirrorInline(editor, textArea);
							}
						},
						onCancel: function (event) {
							if (event.data.hide) {
								window["codemirror_" + editor.id].toTextArea();

								// Free Memory
								window["codemirror_" + editor.id] = null;
							}
						},
						onOk: (function () {

							function setData(newData) {
								var that = this;

								editor.setData(newData, function () {
									that.hide();

									// Ensure correct selection.
									var range = editor.createRange();
									range.moveToElementEditStart(editor.editable());
									range.select();
								});
							}

							return function () {
								window["codemirror_" + editor.id].toTextArea();

								// Free Memory
								window["codemirror_" + editor.id] = null;

								// Remove CR from input data for reliable comparison with editor data.
								var newData = this.getValueOf('main', 'data').replace(/\r/g, '');

								// Avoid unnecessary setData. Also preserve selection
								// when user changed his mind and goes back to wysiwyg editing.
								if (newData === oldData)
									return true;

								// Set data asynchronously to avoid errors in IE.
								CKEDITOR.env.ie ? CKEDITOR.tools.setTimeout(setData, 0, this, newData) : setData.call(this, newData);

								// Don't let the dialog close before setData is over.
								return false;
							};
						})(),

						contents: [{
							id: 'main',
							label: editor.lang.sourcedialog.title,
							elements: [
								{
									type: 'hbox',
									style: 'width:80px; margin:0;',
									widths: ['20px', '20px', '20px', '20px'],
									children: [
										{
											type: 'button',
											id: 'searchCode',
											label: '',
											title: lang.searchCode,
											'class': 'searchCodeButton',
											onClick: function() {
												CodeMirror.commands.find(window["codemirror_" + editor.id]);
											}
										},
										{
											type: 'button',
											id: 'replaceCode',
											label: '',
											title: lang.replaceCode,
											'class': 'replaceCodeButton',
											onClick: function() {
												CodeMirror.commands.replace(window["codemirror_" + editor.id]);
											}
										}
									]
								}, {
									type: 'textarea',
									id: 'data',
									dir: 'ltr',
									inputStyle: 'cursor:auto;' +
										'width:' + width + 'px;' +
										'height:' + height + 'px;' +
										'tab-size:4;' +
										'text-align:left;',
									'class': 'cke_source cke_enable_context_menu'
								}
							]
						}]
					};
				});

				// return;

			}

			var sourcearea = CKEDITOR.plugins.sourcearea;

			// check if sourcearea plugin is overrriden
			if (!sourcearea.commands.searchCode) {

				CKEDITOR.plugins.sourcearea.commands = {
					source: {
						modes: {
							wysiwyg: 1,
							source: 1
						},
						editorFocus: false,
						readOnly: 1,
						exec: function(editorInstance) {
							if (editorInstance.mode === 'wysiwyg') {
								editorInstance.fire('saveSnapshot');
							}
							editorInstance.getCommand('source').setState(CKEDITOR.TRISTATE_DISABLED);
							editorInstance.setMode(editorInstance.mode === 'source' ? 'wysiwyg' : 'source');
						},
						canUndo: false
					},
					searchCode: {
						modes: {
							wysiwyg: 0,
							source: 1
						},
						editorFocus: false,
						readOnly: 1,
						exec: function (editorInstance) {
							CodeMirror.commands.find(window["codemirror_" + editorInstance.id]);
						},
						canUndo: true
					}
				};
			}


			function getCodeMirrorScripts() {
				// disabled: unixman
				return [];
			}


			editor.addCommand('source', sourcearea.commands.source);
			if (editor.ui.addButton) {
				editor.ui.addButton('Source', {
					label: editor.lang.codemirror.toolbar,
					command: 'source',
					toolbar: 'mode,10'
				});
			}
			if (config.enableCodeFormatting) {

				editor.addCommand('searchCode', sourcearea.commands.searchCode);

				if (editor.ui.addButton) {
					if (config.showFormatButton || config.showSearchButton) {
						editor.ui.add('-', CKEDITOR.UI_SEPARATOR, { toolbar: 'mode,30' });
					}
					/*if (config.showSearchButton && config.enableSearchTools) {
						editor.ui.addButton('searchCode', {
							label: lang.searchCode,
							command: 'searchCode',
							toolbar: 'mode,40'
						});
					}*/
				}
			}

			editor.on('beforeModeUnload', function (evt) {
				if (editor.mode === 'source' && editor.plugins.textselection) {

					var range = editor.getTextSelection();

					range.startOffset = LineChannelToOffSet(window["codemirror_" + editor.id], window["codemirror_" + editor.id].getCursor(true));
					range.endOffset = LineChannelToOffSet(window["codemirror_" + editor.id], window["codemirror_" + editor.id].getCursor(false));

					// Fly the range when create bookmark.
					delete range.element;
					range.createBookmark(editor);
					sourceBookmark = true;

					evt.data = range.content;
				}
			});

			editor.on('readOnly', function () {
				if (window["editable_" + editor.id] && editor.mode === 'source') {
					window["codemirror_" + editor.id].setOption("readOnly", this.readOnly);
				}
			});

			editor.on('instanceReady', function (evt) {

				// Fix native context menu
				editor.container.getPrivate().events.contextmenu.listeners.splice(0, 1);

				var selectAllCommand = editor.commands.selectAll;

				// Replace Complete SelectAll command from the plugin, otherwise it will not work in IE10
				if (selectAllCommand != null) {
					selectAllCommand.exec = function () {
						if (editor.mode === 'source') {
							window["codemirror_" + editor.id].setSelection({
								line: 0,
								ch: 0
							}, {
								line: window["codemirror_" + editor.id].lineCount(),
								ch: 0
							});
						} else {
							var editable = editor.editable();
							if (editable.is('body'))
								editor.document.$.execCommand('SelectAll', false, null);
							else {
								var range = editor.createRange();
								range.selectNodeContents(editable);
								range.select();
							}

							// Force triggering selectionChange (#7008)
							editor.forceNextSelectionCheck();
							editor.selectionChange();
						}
					};
				}
			});

			if (typeof (jQuery) != 'undefined' && jQuery('a[data-toggle="tab"]') && window["codemirror_" + editor.id]) {
				jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function() {
					window["codemirror_" + editor.id].refresh();
				});
			}

			editor.on('setData', function (data) {

				if (window["editable_" + editor.id] && editor.mode === 'source') {
					window["codemirror_" + editor.id].setValue(data.data.dataValue);
				}
			});
		}
	});
	var sourceEditable = CKEDITOR.tools.createClass({
		base: CKEDITOR.editable,
		proto: {
			setData: function(data) {

				this.setValue(data);

				if (this.codeMirror != null) {
					this.codeMirror.setValue(data);
				}

				this.editor.fire('dataReady');
			},
			getData: function() {
				return this.getValue();
			},
			// Insertions are not supported in source editable.
			insertHtml: function() {
			},
			insertElement: function() {
			},
			insertText: function() {
			},
			// Read-only support for textarea.
			setReadOnly: function(isReadOnly) {
				this[(isReadOnly ? 'set' : 'remove') + 'Attribute']('readOnly', 'readonly');
			},
			editorID: null,
			detach: function() {
				window["codemirror_" + this.editorID].toTextArea();

				// Free Memory on destroy
				window["editable_" + this.editorID] = null;
				window["codemirror_" + this.editorID] = null;

				sourceEditable.baseProto.detach.call(this);

				this.clearCustomData();
				this.remove();
			}
		}
	});
})();
CKEDITOR.plugins.sourcearea = {
	commands: {
		source: {
			modes: {
				wysiwyg: 1,
				source: 1
			},
			editorFocus: false,
			readOnly: 1,
			exec: function(editor) {
				if (editor.mode === 'wysiwyg') {
					editor.fire('saveSnapshot');
				}

				editor.getCommand('source').setState(CKEDITOR.TRISTATE_DISABLED);
				editor.setMode(editor.mode === 'source' ? 'wysiwyg' : 'source');
			},
			canUndo: false
		},
		searchCode: {
			modes: {
				wysiwyg: 0,
				source: 1
			},
			editorFocus: false,
			readOnly: 1,
			exec: function(editor) {
				CodeMirror.commands.find(window["codemirror_" + editor.id]);
			},
			canUndo: true
		},
		commentSelectedRange: {
			modes: {
				wysiwyg: 0,
				source: 1
			},
			editorFocus: false,
			readOnly: 0,
			exec: function (editor) {
				var range = {
					from: window["codemirror_" + editor.id].getCursor(true),
					to: window["codemirror_" + editor.id].getCursor(false)
				};
				window["codemirror_" + editor.id].commentRange(true, range.from, range.to);
			},
			canUndo: true
		},
		uncommentSelectedRange: {
			modes: {
				wysiwyg: 0,
				source: 1
			},
			editorFocus: false,
			readOnly: 0,
			exec: function(editor) {
				var range = {
					from: window["codemirror_" + editor.id].getCursor(true),
					to: window["codemirror_" + editor.id].getCursor(false)
				};
				window["codemirror_" + editor.id].commentRange(false, range.from, range.to);
				if (window["codemirror_" + editor.id].config.autoFormatOnUncomment) {
					window["codemirror_" + editor.id].autoFormatRange(
						range.from,
						range.to);
				}
			},
			canUndo: true
		}
	}
};

function LineChannelToOffSet(ed, linech) {
	var line = linech.line;
	var ch = linech.ch;
	var n = (line + ch); //for the \n s & chars in the line
	for (i = 0; i < line; i++) {
		n += (ed.getLine(i)).length;//for the chars in all preceeding lines
	}
	return n;
}

function OffSetToLineChannel(ed, n) {
	var line = 0, ch = 0, index = 0;
	for (i = 0; i < ed.lineCount() ; i++) {
		len = (ed.getLine(i)).length;
		if (n < index + len) {

			line = i;
			ch = n - index;
			return { line: line, ch: ch };
		}
		len++;//for \n char
		index += len;
	}
	return { line: line, ch: ch };
}

function IsStyleSheetAlreadyLoaded(href) {
	var links = CKEDITOR.document.getHead().find('link');

	for (var i = 0; i < links.count() ; i++) {
		if (links.getItem(i).$.href === href) {
			return true;
		}
	}

	return false;
}