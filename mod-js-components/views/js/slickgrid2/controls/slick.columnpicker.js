
// (c) 2017-2020 unix-world.org
// SlickGrid v2.3.uxm20200502
// Fixes by unixman:
// 	- jQuery 3.5.0 ready (fixed XHTML Tags)

(function ($) {
	function SlickColumnPicker(columns, grid, options) {
		var $menu;
		var columnCheckboxes;

		var defaults = {
			fadeSpeed:250
		};

		function init() {
			grid.onHeaderContextMenu.subscribe(handleHeaderContextMenu);
			grid.onColumnsReordered.subscribe(updateColumnOrder);
			options = $.extend({}, defaults, options);

			$menu = $("<span class='slick-columnpicker' style='display:none;position:absolute;z-index:20;overflow-y:scroll;'></span>").appendTo(document.body);

			$menu.on("mouseleave", function (e) {
				$(this).fadeOut(options.fadeSpeed)
			});
			$menu.on("click", updateColumn);

		}

		function destroy() {
			grid.onHeaderContextMenu.unsubscribe(handleHeaderContextMenu);
			grid.onColumnsReordered.unsubscribe(updateColumnOrder);
			$menu.remove();
		}

		function handleHeaderContextMenu(e, args) {
			e.preventDefault();
			$menu.empty();
			updateColumnOrder();
			columnCheckboxes = [];

			var $li, $input;
			for (var i = 0; i < columns.length; i++) {
				$li = $("<li></li>").appendTo($menu);
				$input = $("<input type='checkbox'>").data("column-id", columns[i].id);
				columnCheckboxes.push($input);

				if (grid.getColumnIndex(columns[i].id) != null) {
					$input.prop("checked", true);
				}

				$("<label></label>")
						.html(columns[i].name)
						.prepend($input)
						.appendTo($li);
			}

			$("<hr>").appendTo($menu);
			$li = $("<li></li>").appendTo($menu);
			$input = $("<input type='checkbox'>").data("option", "autoresize");
			$("<label></label>")
					.text("Force fit columns")
					.prepend($input)
					.appendTo($li);
			if (grid.getOptions().forceFitColumns) {
				$input.prop("checked", true);
			}

			$li = $("<li></li>").appendTo($menu);
			$input = $("<input type='checkbox'>").data("option", "syncresize");
			$("<label></label>")
					.text("Synchronous resize")
					.prepend($input)
					.appendTo($li);
			if (grid.getOptions().syncColumnCellResize) {
				$input.prop("checked", true);
			}

			$menu
					.css("top", e.pageY - 10)
					.css("left", e.pageX - 10)
					.css("max-height", $(window).height() - e.pageY -10)
					.fadeIn(options.fadeSpeed);
		}

		function updateColumnOrder() {
			// Because columns can be reordered, we have to update the `columns`
			// to reflect the new order, however we can't just take `grid.getColumns()`,
			// as it does not include columns currently hidden by the picker.
			// We create a new `columns` structure by leaving currently-hidden
			// columns in their original ordinal position and interleaving the results
			// of the current column sort.
			var current = grid.getColumns().slice(0);
			var ordered = new Array(columns.length);
			for (var i = 0; i < ordered.length; i++) {
				if ( grid.getColumnIndex(columns[i].id) === undefined ) {
					// If the column doesn't return a value from getColumnIndex,
					// it is hidden. Leave it in this position.
					ordered[i] = columns[i];
				} else {
					// Otherwise, grab the next visible column.
					ordered[i] = current.shift();
				}
			}
			columns = ordered;
		}

		function updateColumn(e) {
			if ($(e.target).data("option") == "autoresize") {
				if (e.target.checked) {
					grid.setOptions({forceFitColumns:true});
					grid.autosizeColumns();
				} else {
					grid.setOptions({forceFitColumns:false});
				}
				return;
			}

			if ($(e.target).data("option") == "syncresize") {
				if (e.target.checked) {
					grid.setOptions({syncColumnCellResize:true});
				} else {
					grid.setOptions({syncColumnCellResize:false});
				}
				return;
			}

			if ($(e.target).is(":checkbox")) {
				var visibleColumns = [];
				$.each(columnCheckboxes, function (i, e) {
					if ($(this).is(":checked")) {
						visibleColumns.push(columns[i]);
					}
				});

				if (!visibleColumns.length) {
					$(e.target).prop("checked", true);
					return;
				}

				grid.setColumns(visibleColumns);
			}
		}

		function getAllColumns() {
			return columns;
		}

		init();

		return {
			"getAllColumns": getAllColumns,
			"destroy": destroy
		};
	}

	// Slick.Controls.ColumnPicker
	$.extend(true, window, { Slick:{ Controls:{ ColumnPicker:SlickColumnPicker }}});
})(jQuery);

// #END
