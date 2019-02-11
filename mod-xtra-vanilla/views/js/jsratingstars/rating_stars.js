
// NetVision JS - Voting Stars
// (c) 2006-2015 unix-world.org
// v.2015.02.15

// DEPENDS: CrossBrowser, jQuery, Growl

//=====================================

var JS_RatingStars_Path = ''; // EMPTY | must end with a slash /

var JS_RatingStars_Class = new function() { // START CLASS

// :: static

//== [PUBLIC]
this.draw = function(el_id, url) {
	//--
	var tmp_stars = ''; // init
	var curent = '';
	//--
	var tmp_obj = $('#' + el_id);
	var tmp_title = '' + tmp_obj.attr('title');
	//--
	for(var i=1; i<=10; i++) {
		//--
		curent = 'l';
		if(i % 2 == 0) {
			curent = 'r';
		} //end if
		//--
		tmp_stars += '<img src="' + JS_RatingStars_Path + 'img/gstar_' + curent + '.gif" alt="' + i + '" title="' + i + '" border="0" onMouseOver="JS_RatingStars_Class.display_stars(' + i + ', 10, \'' + el_id + '\', \'rstar\', \'gstar\')" onClick="JS_RatingStars_Class.cast_vote(' + i + ', \'' + el_id + '\', \'' + url + '\')" id="' + el_id + '_' + i + '">';
		//--
	} //end for
	//--
	tmp_obj.mouseout(function() {
		hide_vote_stars(10, parseInt($(this).attr('title')), el_id, 'gstar', 'rstar');
	}); //end
	//--
	tmp_obj.css({
		'width': '100px',
		'padding-top': '3px',
		'text-align': 'center',
		'background-color': '#F5F5F5',
		'border': '1px solid #ECECEC',
		'cursor': 'pointer'
	});
	//--
	tmp_obj.html(tmp_stars);
	tmp_stars = ''; // clean
	//--
	hide_vote_stars(10, parseInt(tmp_title), el_id, 'gstar', 'rstar');
	//--
} //END FUNCTION

//== [PUBLIC but Private use]
this.display_stars = function(i, total, object_type, img1_name, img2_name) {
	//--
	var curent = '';
	var tmp_star = '';
	//--
	var j;
	//--
	for(j=1; j<=i; j++) {
		//--
		curent = 'l';
		if(j % 2 == 0) {
			curent = 'r';
		} //end if
		//--
		tmp_star = '' + JS_RatingStars_Path + 'img/' + img1_name + '_' + curent + '.gif';
		$('#' + object_type + '_' + j).attr('src', tmp_star);
		//--
	} //end for
	//--
	for(j=i+1; j<=total; j++) {
		//--
		curent = 'l';
		if(j % 2 == 0) {
			curent = 'r';
		} //end if
		//--
		tmp_star = '' + JS_RatingStars_Path + 'img/' + img2_name + '_' + curent + '.gif';
		$('#' + object_type + '_' + j).attr('src', tmp_star);
		//--
	} //end for
} //END FUNCTION

//== [PUBLIC but Private use]
this.cast_vote = function(score, el_id, url) {
	//-- url sample: test.php?id= [must end with =]
	if((typeof url == 'undefined') || (url == 'undefined')) {
		//--
		alert('ERROR: URL is NOT Defined for Rating Stars Element ID: ' + el_id);
		//--
	} else {
		//--
		var tmp_obj = $('#' + el_id);
		var tmp_old_score = parseInt(tmp_obj.attr('title'));
		var tmp_url = '' + url + encodeURIComponent(el_id) + '&stars=' + encodeURIComponent(score);
		//--
		if(isNaN(tmp_old_score)) {
			tmp_old_score = 0;
		} //end if
		if(tmp_old_score < 0) {
			tmp_old_score = 0;
		} //end if
		if(tmp_old_score > 10) {
			tmp_old_score = 10;
		} //end if
		//--
		$.ajax({
			async: true,
			cache: false,
			timeout: 0,
			type: 'GET',
			url: tmp_url,
			data: '',
			dataType: 'text',
			success: function(answer) {
				//--
				var str = SmartJS_CoreUtils.stringTrim(answer).split("\n");
				//-- errcode [\n] score [\n] info [\n] #end
				var msg = SmartJS_CoreUtils.stringTrim(str[0]);
				var res = SmartJS_CoreUtils.stringTrim(str[1]);
				var inf = SmartJS_CoreUtils.stringTrim(str[2]);
				var end = SmartJS_CoreUtils.stringTrim(str[3]);
				//--
				var tmp_score = tmp_old_score;
				//--
				if(end == 'RATINGSTARS: END') {
					switch(msg) {
						case 'RATINGSTARS: +200 OK':
							//--
							tmp_score = parseInt(res);
							//--
							//alert(inf);
							$.gritter.add({
								class_name: 'gritter-green',
								title: 'Rating Stars',
								text: '' + inf,
								sticky: false,
								time: 3500
							});
							//--
							break;
						case 'RATINGSTARS: +400 ERROR':
							//--
							//alert(inf);
							$.gritter.add({
								class_name: 'gritter-red',
								title: 'Rating Stars',
								text: '' + inf,
								sticky: false,
								time: 3500
							});
							//--
							break;
						default:
							//--
							alert('RATINGSTARS: Unknown Status: ' + msg);
							//--
					} //end switch
					//--
				} else {
					//--
					alert('RATINGSTARS: INVALID OR INCOMPLETE RESPONSE !' + "\n\n" + msg + "\n" + res + "\n" + inf + "\n" + end);
					//--
				} //end if else
				//--
				if(tmp_score < 0) {
					tmp_score = 0;
				} //end if
				if(tmp_score > 10) {
					tmp_score = 10;
				} //end if
				//--
				tmp_obj.attr('title', tmp_score.toString());
				//--
				hide_vote_stars(10, tmp_score, el_id, 'gstar', 'rstar');
				//--
			}, //END FUNCTION
			error: function(answer) {
				//--
				alert('ERROR (RatingStars): Invalid Server Response !', '' + answer.responseText);
				//--
			} //END FUNCTION
		});
		//--
	} //end if else
	//--
} //END FUNCTION

//== [PRIVATE]
var hide_vote_stars = function(total, score, object_type, img1_name, img2_name) {
	//--
	var curent = '';
	var tmp_star = '';
	//--
	for(var j=1; j<=total; j++) {
		//--
		curent = 'l';
		if(j % 2 == 0) {
			curent = 'r';
		} //end if
		//--
		if(j <= score) {
			tmp_star = '' + JS_RatingStars_Path + 'img/' + img2_name + '_' + curent + '.gif';
		} else {
			tmp_star = '' + JS_RatingStars_Path + 'img/' + img1_name + '_' + curent + '.gif';
		} //end if else
		//--
		$('#' + object_type + '_' + j).attr('src', tmp_star);
		//--
	} //end for
	//--
} //END FUNCTION

} //END CLASS

//=====================================

// #END
