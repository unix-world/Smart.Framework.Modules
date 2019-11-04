
// Twitter JS API Handler
// (c) 2012 - 2017 Radu I.
// v.20191104

// Depends on: codebird.js, SmartJS_BrowserUtils

var TwitterApiHandler = new function() { // START CLASS

	// :: static

	var _class = this; // self referencing

	var cb = null;

	var storageBaseDomain = null;

	this.init = function(kKey, kSecret, proxyUrl, theBaseDomain) { // always !!

		if(!kKey) {
			alert('Twitter Api: Empty CB Key !');
			return false;
		} //end if
		if(!kSecret) {
			alert('Twitter Api: Empty CB Secret !');
			return false;
		} //end if
		if(!proxyUrl) {
			alert('Twitter Api: Empty CB Proxy !');
			return false;
		} //end if

		storageBaseDomain = theBaseDomain ? String(theBaseDomain) : null;

		cb = new Codebird;
		if(proxyUrl) {
			cb.setUseProxy(true);
			cb.setProxy(String(proxyUrl));
		} //end if
		cb.setConsumerKey(String(kKey), String(kSecret));

		return true;

	} //END FUNCTION


	this.unauthorize = function(fxLogout) {

		storageSetItem('smarttwittjsapi_data', '', true);
		storageSetItem('smarttwittjsapi_auth', '');
		storageSetItem('smarttwittjsapi_vf', '');
		storageSetItem('smarttwittjsapi_sk', '');
		storageSetItem('smarttwittjsapi_token', '');

		if(typeof fxLogout === 'function') {
			fxLogout();
		} //end if

	} //END FUNCTION


	this.authorize = function(callbackUrl, fxResponseOk, fxResponseNotOk) { // for main window

		var oauth_data = _class.getLoginData();

		if(oauth_data && oauth_data.token && oauth_data.secret && oauth_data.uid) {
			cb.setToken(String(oauth_data.token), String(oauth_data.secret));
			if(typeof fxResponseOk === 'function') {
				fxResponseOk();
			} else {
				console.log('Twitter already authorized ...');
			} //end if else
		} else {
			requestPermissions(String(callbackUrl), fxResponseOk, fxResponseNotOk);
		} //end if else

	} //END FUNCTION


	this.finalizeauth = function(fxFinalizeOk, fxFinalizeNotOk) { // for redirection popup

		var urlParams = parseUrlParams();
		var oauth_secret = String(storageGetItem('smarttwittjsapi_sk'));

		// get new token from URL
		var oauth_token = '';
		if(urlParams.oauth_token && oauth_secret) {
			oauth_token = String(urlParams.oauth_token);
			cb.setToken(oauth_token, oauth_secret); // oauth_token_secret
		} else {
			console.error('Twitter Js Api FinalizeAuth: No oAuth Token in URL');
			return;
		} //end if

		// get oauth_verifier from URL
		var oauth_verifier = '';
		if(urlParams.oauth_verifier) {
			oauth_verifier = String(urlParams.oauth_verifier);
			storageSetItem('smarttwittjsapi_vf', String(oauth_verifier));
		} else {
			console.error('Twitter Js Api FinalizeAuth: No oAuth Verifier in URL');
			return;
		} //end if

		cb.__call(
			'oauth_accessToken',
			{
				oauth_verifier: String(oauth_verifier)
			},
			function(reply) {
				//-- unset these, no more need
				storageSetItem('smarttwittjsapi_token', '');
				storageSetItem('smarttwittjsapi_sk', '');
				storageSetItem('smarttwittjsapi_vf', '');
				//--
				if(!reply || !reply.oauth_token || !reply.oauth_token_secret) {
					console.error('Twitter Js/Api: Failed to get New Twitter Token/Secret ...');
					return;
				} //end if
				cb.setToken(reply.oauth_token, reply.oauth_token_secret);
				var twData = {};
				try {
					for(var k in reply) {
						k = String(k);
						if(k.substring(0,1) != '_') {
							var o = reply[k];
							twData[k] = o;
						} //end if
					} //end for
				} catch(err){}
				//console.log(twData);
				if(twData.httpstatus === 200 && twData.user_id && twData.oauth_token && twData.oauth_token_secret) {
					cb.__call(
						'account_verifyCredentials',
						{
							include_entities: 'false',
							skip_status: 'false',
							include_email: 'true'
						},
						function(reply) {
							//console.log(reply);
							if(reply && reply.httpstatus === 200 && reply.id && reply.id == twData.user_id && reply.id_str && reply.id_str == twData.user_id) {
								//--
								loginData = {}; // {{{SYNC-TWITT-DATA}}}
								//--
								loginData.httpstatus = String(twData.httpstatus) + '/' + String(reply.httpstatus);
								//--
								loginData.token = String(twData.oauth_token) || '';
								loginData.secret = String(twData.oauth_token_secret) || '';
								//--
								loginData.uid = String(reply.id_str) || ''; // When consuming the API using JSON, it is important to always use the field id_str instead of id. This is due to the way Javascript and other languages that consume JSON evaluate large integers (https://dev.twitter.com/overview/api/twitter-ids-json-and-snowflake)
								loginData.email = reply.email || '';
								loginData.name = reply.name || '';
								loginData.timezone = Math.round(reply.utc_offset / 60) || 0;
								loginData.location = reply.location || '';
								//--
								loginData.locale = reply.lang || '';
								loginData.username = reply.screen_name || '';
								//--
								loginData.verified = reply.verified ? 1 : 0;
								loginData.permissions = [];
								//--
								// #imageurl# : https://twitter.com/{username}/profile_image?size=mini|normal|original
								//--
								storageSetItem('smarttwittjsapi_data', String(JSON.stringify(loginData)), true);
								storageSetItem('smarttwittjsapi_auth', 'Y');
								//--
								if(typeof fxFinalizeOk === 'function') {
									fxFinalizeOk(reply); // be sure to call popup close in fxFinalizeOk()
								} else {
									console.log('TwitterApiHandler: OK-reply =');
									console.log(reply);
									_class.closepopup();
								} //end if else
								//--
							} else {
								//--
								storageSetItem('smarttwittjsapi_data', '', true);
								//--
								if(typeof fxFinalizeNotOk === 'function') {
									fxFinalizeNotOk(reply); // be sure to call popup close in fxFinalizeOk()
								} else {
									console.log('TwitterApiHandler: NOTOK-#2-reply =');
									console.log(reply);
									_class.closepopup();
								} //end if else
								//--
							} //end if else
						} // end function
					);
				} else {
					//--
					storageSetItem('smarttwittjsapi_data', '', true);
					//--
					if(typeof fxFinalizeNotOk === 'function') {
						fxFinalizeNotOk(reply); // be sure to call popup close in fxFinalizeOk()
					} else {
						console.log('TwitterApiHandler: NOTOK-#1-reply =');
						console.log(reply);
						_class.closepopup();
					} //end if else
					//--
				} //end if else
			} //end function
		);

	} //END FUNCTION


	this.closepopup = function() {

		//if(window.opener) {
		if(SmartJS_BrowserUtils.WindowIsPopup()) {
			try {
				self.close();
			} catch(err){}
		} //end if

	} //END FUNCTION


	this.getLoginData = function() {

		var oauth_data = String(storageGetItem('smarttwittjsapi_data', true));
		//console.log(oauth_data);
		if(!oauth_data) {
			return false;
		} //end if

		if(oauth_data) {
			try {
				oauth_data = JSON.parse(oauth_data);
				if(!oauth_data) {
					return false;
				} //end if
			} catch(err){}
		} //end if

		if(!oauth_data) {
			return false;
		} //end if

		if((!oauth_data.hasOwnProperty('token')) || (!oauth_data.hasOwnProperty('secret')) || (!oauth_data.hasOwnProperty('uid')) || (!oauth_data.hasOwnProperty('httpstatus')) || (oauth_data.httpstatus !== '200/200')) {
			return false;
		} //end if

		return oauth_data;

	} //END FUNCTION


	this.postMedia = function(mB64Data, postMessage, fxDone, fxFail) {

		var oauth_data = _class.getLoginData();

		if(!oauth_data || !oauth_data.token || !oauth_data.secret || !oauth_data.uid || !mB64Data) {
			if(typeof fxFail === 'function') {
				fxFail(null, null, 'ERROR: Media / Invalid Twitter Data');
			} else {
				console.error('ERROR: Media / Invalid Twitter Data');
			} //end if else
			return;
		} //end if

		var oauth_token = String(oauth_data.token);
		var oauth_token_secret = String(oauth_data.secret);
		//console.log(oauth_token, oauth_token_secret);
		cb.setToken(oauth_token, oauth_token_secret);

		cb.__call(
			'media_upload',
			{
				'media': String(mB64Data.split(',')[1])
			},
			function(reply, rate, err) {
				//console.log(reply, rate, err);
				if(reply && !err) {
					cb.__call(
						'statuses_update',
						{
							'media_ids': String(reply.media_id_string),
							'status': String(postMessage)
						},
						function(reply) {
							if(!reply.errors) {
								if(typeof fxDone === 'function') {
									fxDone(reply, rate, err);
								} else {
									console.log('OK, media posted on Twitter ...')
									console.log(reply, rate, err);
								} //end if else
							} else {
								if(typeof fxFail === 'function') {
									fxFail(reply, rate, err);
								} else {
									console.error('NOTOK, media was NOT posted on Twitter ...')
									console.log(reply, rate, err);
								} //end if else
							} //end if else
						} //end function
					);
				} else {
					if(typeof fxFail === 'function') {
						fxFail(reply, rate, err);
					} else {
						console.error('NOTOK, media was NOT posted on Twitter ...')
						console.log(reply, rate, err);
					} //end if else
				} //end if else
			} //end function
		);

	} //END FUNCTION


	//#####


	var requestPermissions = function(callbackUrl, fxResponseOk, fxResponseNotOk) {

		var wndPopUp = window.open('', '_blank', 'location=no,width=600,height=400,top=10,left=10');

		cb.__call(
			'oauth_requestToken',
			{
				oauth_callback: String(callbackUrl)
			},
			function(reply, rate, err) {
				if(err) {
					if(wndPopUp) {
						try {
							wndPopUp.close();
						} catch(err){}
					} //end if
					console.error('TwitterApiHandler: Error response or timeout exceeded: ' + err.error);
					return;
				} //end if
				if(reply && reply.errors && reply.errors['415']) {
					console.log('TwitterApiHandler: Error: ' + reply.errors['415']);
				} else if(reply && reply.oauth_token && reply.oauth_token_secret) {
					//console.log('reply', reply)
					// stores it
					cb.setToken(String(reply.oauth_token), String(reply.oauth_token_secret));
					// save the token for the redirect (after user authorizes) ; we'll want to compare these values
					storageSetItem('smarttwittjsapi_token', String(reply.oauth_token));
					storageSetItem('smarttwittjsapi_sk', String(reply.oauth_token_secret));
					// gets the authorize screen URL
					// window.open(auth_url);
					// $('#authorize').attr('href', auth_url);
					// after user authorizes, user will be redirected to
					// http://127.0.0.1:49479/?oauth_token=[some_token]&oauth_verifier=[some_verifier]
					// then follow this section for coding that page:
					// https://github.com/jublonet/codebird-js#authenticating-using-a-callback-url-without-pin
					cb.__call(
						'oauth_authorize',
						{},
						function(auth_url) {
							try {
								//var wndPopUp = window.open(String(auth_url), '_blank', 'location=no,width=600,height=400,top=10,left=10');
								wndPopUp.location = String(auth_url);
							} catch(err){
								console.error('ERROR when trying to raise and redirect a new PopUp Window for Twitter CallBack: ' + err);
							} //end try catch
							if(!wndPopUp) { // popup may be blocked
								alert('Twitter Js Api requires a PopUp to be opened. If you blocked PopUps you may allow this one in order to continue ...');
								return;
							} //end if
							var pollTimer = setInterval(function() {
								if(wndPopUp && wndPopUp.closed) {
									clearInterval(pollTimer);
									var auth_ok = storageGetItem('smarttwittjsapi_auth');
									if(auth_ok) {
										auth_ok = String(auth_ok);
									} else {
										auth_ok = null;
									} //end if else
									storageSetItem('smarttwittjsapi_auth', '');
									if(auth_ok === 'Y') {
										if(typeof fxResponseOk === 'function') {
											fxResponseOk();
										} else {
											console.log('TwitterApiHandler: auth_url = ' + String(auth_url));
										} //end if else
									} else {
										var msg = 'Twitter Api Authorization Failed. User did not accepted the permissions ...';
										if(typeof fxResponseNotOk === 'function') {
											fxResponseNotOk(msg);
										} else {
											console.error(msg);
										} //end if
									} //end if else
									return false;
								}
							}, 500);
						} //end function
					);
				} else {
					if(wndPopUp) {
						try {
							wndPopUp.close();
						} catch(err){}
					} //end if
					if(typeof fxResponseNotOk === 'function') {
						fxResponseNotOk(reply);
					} else {
						console.error('Twitter Request: Invalid / Incomplete Reply ...');
						console.error(reply);
					} //end if
				} //end if
			} //end function
		);

	} //END FUNCTION


	//##### Data Model


	var storageGetItem = function(key, archive) {
		//--
		var value = getCookie(key);
		//--
		if(archive) {
			if(value) {
				value = String(SmartJS_Archiver_LZS.decompressFromBase64(String(value)));
			} //end if
		} //end if
		//--
		return value;
		//--
	} //END FUNCTION


	var storageSetItem = function(key, value, archive) {
		//--
		if(archive) {
			if(value) {
				value = String(SmartJS_Archiver_LZS.compressToBase64(String(value)));
			} //end if
		} //end if
		//--
		if(value) {
			setCookie(key, value, null, '/', storageBaseDomain);
		} else {
			deleteCookie(key, '/', storageBaseDomain);
		} //end if else
		//--
	} //END FUNCTION


	//##### Below functions can be supplied by Smart.Framework/Js.Api/SmartJS_BrowserUtils


	var parseUrlParams = function() {
		//--
		return SmartJS_BrowserUtils.parseCurrentUrlGetParams();
		//--
		/*
		var result = {};
		//--
		if(!location.search) {
			return result; // Object
		} //end if
		var query = String(location.search.substr(1)); // get: 'param1=value1&param2=value%202' from '?param1=value1&param2=value%202'
		if(!query) {
			return result; // Object
		} //end if
		//--
		query.split('&').forEach(function(part) {
			var item = '';
			part = String(part);
			if(part) {
				item = part.split('=');
				result[String(item[0])] = String(decodeURIComponent(String(item[1])));
			} //end if
		});
		//--
		return result; // Object
		*/
		//--
	} //END FUNCTION


	var getCookie = function(name) {
		//--
		return SmartJS_BrowserUtils.getCookie(name);
		//--
		/*
		var c;
		try {
			c = document.cookie.match(new RegExp('(^|;)\\s*' + String(name) + '=([^;\\s]*)'));
		} catch(err){
			console.error('NOTICE: BrowserUtils Failed to getCookie: ' + err);
		} //end try catch
		//--
		if(c && c.length >= 3) {
			var d = decodeURIComponent(c[2]) || ''; // fix to avoid working with null !!
			return String(d);
		} else {
			return ''; // fix to avoid working with null !!
		} //end if
		*/
		//--
	} //END FUNCTION


	var setCookie = function(name, value, days, path, domain, secure) {
		//--
		SmartJS_BrowserUtils.setCookie(name, value, days, path, domain, secure);
		//--
		/*
		if((typeof value == 'undefined') || (value == undefined) || (value == null)) {
			return; // bug fix (avoid to set null cookie)
		} //end if
		//--
		var d = new Date();
		//--
		if(days) {
			d.setTime(d.getTime() + (days * 8.64e7)); // now + days in milliseconds
		} //end if
		//--
		try {
			document.cookie = String(name) + '=' + encodeURIComponent(value) + (days ? ('; expires=' + d.toGMTString()) : '') + '; path=' + (path || '/') + (domain ? ('; domain=' + domain) : '') + (secure ? '; secure' : '');
		} catch(err){
			console.error('NOTICE: Failed to setCookie: ' + err);
		} //end try catch
		*/
		//--
	} //END FUNCTION


	var deleteCookie = function(name, path, domain, secure) {
		//--
		SmartJS_BrowserUtils.deleteCookie(name, path, domain, secure);
		//--
		/*
		setCookie(name, '', -1, path, domain, secure); // sets expiry to now - 1 day
		*/
		//--
	} //END FUNCTION



} //END CLASS


// END
