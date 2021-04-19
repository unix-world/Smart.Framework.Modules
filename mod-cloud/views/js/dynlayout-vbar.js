
// Smart WebMail - Vertical Dynamic Bar
// (c) 2006-2021 unix-world.org

// v.20210411

//==================================================================
//==================================================================

var SmartCloud_DynLayout_Allow_DinamicBar = 300;

var SmartCloud_DynLayout_Vertical_DynamicBar = new function() { // START CLASS

	var min_left = 1;
	var max_left = smartJ$Utils.format_number_int(SmartCloud_DynLayout_Allow_DinamicBar);

	this.getBarWidth = function() {
		//--
		return getTheBarWidth(); // integer
		//--
	} //END FUNCTION

	// read previous stored value (from cookie) and set the width of div
	this.restoreWidth = function() {
		//--
		jQuery('#cloud_dynlayout_left_div').css({
			'width': getTheBarWidth()
		});
		//--
	} //END FUNCTION

	// handle bar drag
	this.handleDrag = function() {
		//--
		if(SmartCloud_DynLayout_Allow_DinamicBar > 0) {
			//--
			var area = jQuery('#cloud_dynlayout_left_div');
			var pwidth = smartJ$Utils.format_number_int(parseInt(area.width()));
			//--
			jQuery('#cloud_dynlayout_resizer_div').bind('dragstart', function(event) {
				//console.log('drag start');
			}).bind('drag', function(event) {
				//console.log('drag: ' + event.pageX);
				pwidth = smartJ$Utils.format_number_int(Math.round(event.pageX));
				if(pwidth < min_left) {
					pwidth = min_left;
				} else if(pwidth > max_left) {
					pwidth = max_left;
				} //end if
				area.css({
					width: pwidth + 'px'
				});
			}).bind('dragend', function(event) {
				//console.log('drag end');
				smartJ$Browser.setCookie('SmartCloud_DynLayout_LeftArea_Size', pwidth);
				jQuery(this).trigger('resize')
			});
			//--
		} //end if
		//--
	} //END FUNCTION

	// get bar width
	var getTheBarWidth = function() {
		//--
		var xwidth = 175; // this is by default
		//--
		var the_cookie = smartJ$Utils.format_number_int(smartJ$Browser.getCookie('SmartCloud_DynLayout_LeftArea_Size'), false);
		if((the_cookie > 0) && (the_cookie >= min_left) && (the_cookie <= max_left)) {
			xwidth = the_cookie;
		} //end if
		//--
		return xwidth; // integer
		//--
	} //END FUNCTION

} //END CLASS

//==================================================================
//==================================================================

// #END
