
// CountDown
// (c) 2006-2015 unix-world.org
// v.2015.02.15

/**** Example Usage
<script type="text/javascript" src="countdown.js"></script>
<div id="js__countdown_div" style="background-color:#FFFFFF; color: #FF3300">Un Test</div>
<script type="text/javascript">
var cnt_end = CountDown.Run("01/26/2020 07:50:25 PM", "%%M%% Minutes, %%S%% Seconds", "Time End !", "index2.php?aa=%20ab&ac=xx&");
</script>
****/

var CountDown = new function() {

var theDIV = 'js__countdown__div';

this.Run = function(TargetDIV, TargetDate, DisplayFormat, FinishMessage, redirectURL) {
	//-- Examples
	// TargetDate = "12/31/2020 5:00 AM";
	// DisplayFormat = "%%D%% Days, %%H%% Hours, %%M%% Minutes, %%S%% Seconds.";
	// FinishMessage = "Time is Up!";
	// redirectURL = "index2.html"; // *** Optional ***
	//-- Code
	theDIV = '' + TargetDIV;
	//--
	dthen = new Date(TargetDate);
	dnow = new Date();
	ddiff = new Date(dthen - dnow);
	//-- Run
	CountDown.CountBack(Math.floor(ddiff.valueOf() / 1000), 1, DisplayFormat, FinishMessage, redirectURL);
	//--
} //END FUNCTION

this.CountBack = function(secs, CountActive, DisplayFormat, FinishMessage, redirectURL) {
	if(secs < 0) {
		document.getElementById(theDIV).innerHTML = FinishMessage;
		if((redirectURL != undefined) && (redirectURL != "undefined")) {
			setTimeout("self.location='" + redirectURL + "'", 1000);
		} //end if
		return;
	} //end if
	DisplayStr = DisplayFormat.replace(/%%D%%/g, Cnt_SmartTimer_CalcAge(secs, 86400, 100000));
	DisplayStr = DisplayStr.replace(/%%H%%/g, Cnt_SmartTimer_CalcAge(secs, 3600, 24));
	DisplayStr = DisplayStr.replace(/%%M%%/g, Cnt_SmartTimer_CalcAge(secs, 60, 60));
	DisplayStr = DisplayStr.replace(/%%S%%/g, Cnt_SmartTimer_CalcAge(secs, 1, 60));
	document.getElementById(theDIV).innerHTML = DisplayStr;
	if(CountActive) {
		setTimeout("CountDown.CountBack(" + (secs - 1) + ", " + CountActive + ", \"" + DisplayFormat + "\", \"" + FinishMessage + "\", \"" + redirectURL + "\")", 1000);
	} //end if
} //END FUNCTION

var Cnt_SmartTimer_CalcAge = function(secs, num1, num2) {
	s = ((Math.floor(secs/num1))%num2).toString();
	if (s.length < 2) {
		s = "0" + s; // add leading zero
	} //end if
	return "<b>" + s + "</b>";
} //END FUNCTION

} //END CLASS

// END