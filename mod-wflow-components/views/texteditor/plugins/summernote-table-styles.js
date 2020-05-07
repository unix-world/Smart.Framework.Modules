
// (c) 2019-2020 unix-world.org
// License: GPLv3
// v.20200507
// contains fixes by unixman

// License: MIT
// https://github.com/tylerecouture/summernote-lists

(function(factory) {
	/* global define */
	if (typeof define === "function" && define.amd) {
		// AMD. Register as an anonymous module.
		define(["jquery"], factory);
	} else if (typeof module === "object" && module.exports) {
		// Node/CommonJS
		module.exports = factory(require("jquery"));
	} else {
		// Browser globals
		factory(window.jQuery);
	}
})(function($) {
	$.extend(true, $.summernote.lang, {
		"en-US": {
			tableStyles: {
				tooltip: 'Table style',
				stylesExclusive: ['Default', 'Bordered', 'Striped'],
				stylesInclusive: []
			}
		}
	});
	$.extend($.summernote.options, {
		tableStyles: { // Must keep the same order as in lang.tableStyles.styles*
			stylesExclusive: ['table-default', 'table-bordered', 'table-striped'],
			stylesInclusive: [], // inclusive styles can be combined with exclusive styles
		//	css: ''
		}
	});

	// Extends plugins for emoji plugin.
	$.extend($.summernote.plugins, {
		tableStyles: function(context) {
			var self = this,
				ui = $.summernote.ui,
				options = context.options,
				lang = options.langInfo;
			($editor = context.layoutInfo.editor),
				($editable = context.layoutInfo.editable);
			editable = $editable[0];

			context.memo("button.tableStyles", function() {
				var button = ui.buttonGroup([
					ui.button({
						className: "dropdown-toggle",
						contents: ui.dropdownButtonContents(
							ui.icon(options.icons.magic),
							options
						),
						tooltip: lang.tableStyles.tooltip,
						container: options.container, // tooltip fix by unixman
						data: {
							toggle: "dropdown"
						},
						callback: function($dropdownBtn) {
							$dropdownBtn.click(function() {
								self.updateTableMenuState($dropdownBtn);
							});
						}
					}),
					ui.dropdownCheck({
						className: "dropdown-table-style",
						checkClassName: options.icons.menuCheck,
						items: self.generateListItems(
							options.tableStyles.stylesExclusive,
							lang.tableStyles.stylesExclusive,
							options.tableStyles.stylesInclusive,
							lang.tableStyles.stylesInclusive
						),
						callback: function($dropdown) {
							$dropdown.find("a").each(function() {
								$(this).click(function() {
									self.updateTableStyles(this);
								});
							});
						}
					})
				]);
				return button.render();
			});

			self.updateTableStyles = function(chosenItem) {
				var rng = context.invoke("createRange", $editable);
				var dom = $.summernote.dom;
				if (rng.isCollapsed() && rng.isOnCell()) {
					context.invoke("beforeCommand");
					var table = dom.ancestor(rng.commonAncestor(), dom.isTable);
					self.updateStyles(
						$(table),
						chosenItem,
						options.tableStyles.stylesExclusive
					);
				}
			};

			/* Makes sure the check marks are on the currently applied styles */
			self.updateTableMenuState = function($dropdownButton) {
				var rng = context.invoke("createRange", $editable);
				var dom = $.summernote.dom;
				if (rng.isCollapsed() && rng.isOnCell()) {
					var $table = $(dom.ancestor(rng.commonAncestor(), dom.isTable));
					var $listItems = $dropdownButton.next().find("a");
					self.updateMenuState(
						$table,
						$listItems,
						options.tableStyles.stylesExclusive
					);
				}
			};

			/* The following functions might be turnkey in other menu lists
						with exclusive and inclusive items that toggle CSS classes. */

			self.updateMenuState = function($node, $listItems, exclusiveStyles) {
				var hasAnExclusiveStyle = false;
				$listItems.each(function() {
					var cssClass = $(this).data("value");
					if ($node.hasClass(cssClass)) {
						$(this).addClass("checked");
						if ($.inArray(cssClass, exclusiveStyles) != -1) {
							hasAnExclusiveStyle = true;
						}
					} else {
						$(this).removeClass("checked");
					}
				});

				// if none of the exclusive styles are checked, then check a blank
				if (!hasAnExclusiveStyle) {
					$listItems.filter('[data-value=""]').addClass("checked");
				}
			};

			self.updateStyles = function($node, chosenItem, exclusiveStyles) {
				var cssClass = $(chosenItem).data("value");
				context.invoke("beforeCommand");
				// Exclusive class: only one can be applied at a time
				if ($.inArray(cssClass, exclusiveStyles) != -1) {
					$node.removeClass(exclusiveStyles.join(" "));
					$node.addClass(cssClass);
				} else {
					// Inclusive classes: multiple are ok
					$node.toggleClass(cssClass);
				}
				context.invoke("afterCommand");
			};

			self.generateListItems = function(
				exclusiveStyles,
				exclusiveLabels,
				inclusiveStyles,
				inclusiveLabels
			) {
				var index = 0;
				var list = "";
				//-- unixman fix for es5
				/*
				for(var style of exclusiveStyles) {
					list += self.getListItem(style, exclusiveLabels[index], true);
					index++;
				}
				*/
				exclusiveStyles.forEach(function(style) {
					list += self.getListItem(style, exclusiveLabels[index], true);
					index++;
				});
				// #end fix
				if(inclusiveStyles.length) {
					list += '<hr style="margin: 5px 0px; background-color: #CCCCCC; height: 1px; border: 0;">';
					index = 0;
					//-- unixman fix for es5
					/*
					for(var style of inclusiveStyles) {
						list += self.getListItem(style, inclusiveLabels[index], false);
						index++;
					}
					*/
					inclusiveStyles.forEach(function(style) {
						list += self.getListItem(style, inclusiveLabels[index], false);
						index++;
					});
					// #end fix
				}
				return list;
			};

			self.getListItem = function(value, label, isExclusive) {
				var item = '<li style="list-style-type:none;"><a href="#" class="note-dropdown-item ' + (isExclusive ? "exclusive-item" : "inclusive-item") + '" style="display: block;" data-value="' + value + '">' +
					'<i class="sfi sfi-checkmark" ' + (!isExclusive ? 'style="color:#777777;" ' : 'style="color:#888888;" ') + '></i>' + ' ' + label + '</a></li>';
				return item;
			};
		}
	});
});

// #END
