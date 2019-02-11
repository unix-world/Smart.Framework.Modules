
// NetVision JS - Float Div
// (c) 2006-2015 unix-world.org
// v.2015.02.15

// DEPENDS: jQuery, Cross-Browser

//============================

var FloatDiv_Class = new function() { // START CLASS

// :: static

//--
var FloatMoveActive;
var FloatMouseX, FloatMouseY;
var FloatOffsetMouseX, FloatOffsetMouseY;
var FloatDIVObj, FloatAreaID, FloatIfrmObj;
//--

//--
var FloatXLayer_InitMove = function(e) {
	//--
	var isIE = document.all;
	//--
	topOne = isIE ? 'BODY' : 'HTML';
	ActiveOne = isIE ? event.srcElement : e.target;
	//--
	while ((ActiveOne.id != FloatAreaID) && (ActiveOne.tagName != topOne)) {
		ActiveOne = isIE ? ActiveOne.parentElement : ActiveOne.parentNode;
	} //end while
	//--
	if (ActiveOne.id == FloatAreaID) {
		FloatOffsetMouseX = isIE ? event.clientX : e.clientX;
		FloatOffsetMouseY = isIE ? event.clientY : e.clientY;
		FloatMouseX = parseInt(FloatDIVObj.style.left);
		FloatMouseY = parseInt(FloatDIVObj.style.top);
		FloatMoveActive = true;
		document.onmousemove = FloatXLayer_Move;
	} //end if
	//--
} //END FUNCTION
//--
var FloatXLayer_EndMove = function(e) {
	//--
	FloatMoveActive = false;
	document.onmousemove = function() {};
	FloatIfrmObj.style.visibility = 'visible';
	//--
} //END FUNCTION
//--

//--
var FloatXLayer_Move = function(e) {
	//--
	var isIE=document.all;
	//--
	if (!FloatMoveActive) {
		return;
	} //end if
	//--
	FloatIfrmObj.style.visibility = 'hidden';
	FloatDIVObj.style.left = isIE ? (FloatMouseX + event.clientX - FloatOffsetMouseX) + 'px' : (FloatMouseX + e.clientX - FloatOffsetMouseX) + 'px';
	FloatDIVObj.style.top = isIE ? (FloatMouseY + event.clientY - FloatOffsetMouseY) + 'px' : (FloatMouseY + e.clientY - FloatOffsetMouseY) + 'px';
	//-- vars
	var min_w = (parseInt($(window).scrollLeft()) + 10);
	var max_w = (parseInt($(window).scrollLeft()) + parseInt($(window).width()) - parseInt(FloatDIVObj.style.width) - 30);
	var min_h = (parseInt($(window).scrollTop()) + 10);
	var max_h = (parseInt($(window).scrollTop()) + parseInt($(window).height()) - parseInt(FloatDIVObj.style.height) - 15);
	//-- constrain horiz
	if(parseInt(FloatDIVObj.style.left) < parseInt(min_w)) {
		FloatDIVObj.style.left = parseInt(min_w) + 'px';
	} //end if
	if(parseInt(FloatDIVObj.style.left) >= parseInt(max_w)) {
		FloatDIVObj.style.left = parseInt(max_w) + 'px';
	} //end if
	//-- constrain vert
	if(parseInt(FloatDIVObj.style.top) < parseInt(min_h)) {
		FloatDIVObj.style.top = parseInt(min_h) + 'px';
	} //end if
	if(parseInt(FloatDIVObj.style.top) >= parseInt(max_h)) {
		FloatDIVObj.style.top = parseInt(max_h) + 'px';
	} //end if
	//--
	return false;
	//--
} //END FUNCTION
//--

//-- [PUBLIC]
this.FloatXLayer_Toggle = function(DivID, TitleID, iFrmID, iState, fURL, fWidth, fHeight) { // 1 visible, 0 hidden
	//--
	fWidth = parseInt(fWidth);
	if((fWidth < 10) || isNaN(fWidth)) {
		fWidth = 10;
	} //end if
	fHeight = parseInt(fHeight);
	if((fHeight < 10) || isNaN(fHeight)) {
		fHeight = 10;
	} //end if
	//--
	if(iState) { // this is the case when mixing http:// with https://, iFrame does not work, content is blocked
		//--
		var crr_protocol = '' + document.location.protocol;
		var crr_arr_url = fURL.split(':');
		var crr_url = crr_arr_url[0] + ':';
		//--
		if(((crr_protocol === 'http:') || (crr_protocol === 'https:')) && ((crr_url === 'http:') || (crr_url === 'https:')) && (crr_url !== crr_protocol)) {
			var wnd = window.open(fURL, 'FloatXLayer_PopUp', "top=20,left=20,width=" + fWidth + ",height=" + fHeight + ",toolbar=0,scrollbars=1,resizable=1");
			try {
				wnd.focus(); //focus the window
			} catch(err) {
				// older browsers have some bugs, ex: IE8 on IETester
			} //end try
			return;
		} //end if
		//--
	} //end if
	//--
	var obj = document.getElementById(DivID);
	var ttl = document.getElementById(TitleID);
	var ifrm = document.getElementById(iFrmID);
	//--
	FloatDIVObj = obj;
	FloatIfrmObj = ifrm;
	FloatAreaID = TitleID;
	//-- depth
	obj.style.position = 'fixed';
	//-- we need to set style to properly detect width n height
	obj.style.width = fWidth + 'px';
	obj.style.height = fHeight + 'px';
	//--
	if(iState) {
		//--
		if(obj.style.visibility != 'visible') { // avoid reload if already visible (because at init is not defined)
			//--
			ifrm.src = '' + fURL;
			ifrm.width = '' + fWidth;
			ifrm.height = '' + fHeight;
			//--
			obj.style.visibility = 'visible';
			obj.style.zIndex = SmartJS_BrowserUtils.getHighestZIndex();
			obj.style.left = (parseInt($(window).width()) / 2) - (fWidth / 2) + 'px';
			obj.style.top  = '25px';
			//--
		} //end if
		//--
		obj.onmousedown = FloatXLayer_InitMove;
		obj.onmouseup = FloatXLayer_EndMove;
		obj.onDblclick = FloatXLayer_EndMove;
		//--
		try {
			SmartJS_BrowserUtils_PageAway = false;
		} catch(err){}
		//--
	} else {
		//--
		ifrm.src = '';
		ifrm.width = '1';
		ifrm.height = '1';
		//--
		obj.style.visibility = 'hidden';
		obj.style.zIndex = 1;
		obj.style.left = '1px';
		obj.style.top = '1px';
		//--
		obj.onmousedown = function() {};
		obj.onmouseup = function() {};
		obj.onDblclick = function() {};
		//--
		try {
			SmartJS_BrowserUtils_PageAway = true;
		} catch(err){}
		//--
	} //end if
	//--
} //END FUNCTION
//--

} //END CLASS

//============================

// #END
