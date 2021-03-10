
// Facebook JS API Handler
// (c) 2012-2020 unix-world.org
// v.20201204

// Depends on: jQuery
// Depends on: SmartJS_Base64, SmartJS_BrowserUtils

var FacebookApiHandler = new function() { // START CLASS

	// :: static

	var _class = this; // self referencing


	var FbSettings = {
		appId: 		'',
		lang: 		'en_US', // en_US ; ro_RO ; ...
		status: 	true,
		cookie: 	true,
		oauth: 		true,
		xfbml: 		true,
		perms: 		'public_profile,email', // Extended permissions (req. FB approval): 'manage_pages,publish_pages,user_managed_groups' ; 'publish_pages,manage_pages' only for Pages and Groups
		version: 	'v5.0',
		domain: 	null
	};


	var FbAccessToken = null;
	var FbLoginData = null;


	var FbGetLoginData = function(fxResponseOk) {
		//-- {{{SYNC-FACEBOOK-GET-ME}}}
		FB.api('/me?fields=id,name,email,gender,birthday,location,timezone,locale,verified,permissions', function(response) {
			//console.log(response);
			if(response && response.id) {
				//--
				var perms = [];
				if(response.permissions && response.permissions.data) {
					for(var i=0; i<response.permissions.data.length; i++) {
						if(response.permissions.data[i].status === 'granted') {
							var p = response.permissions.data[i].permission;
							perms.push(String(p));
						} //end if
					} //end for
				} //end if
				//--
				FbLoginData = { // {{{SYNC-FB-DATA}}}
					//--
					httpstatus: '200/200',
					//--
					token: String(FbAccessToken) || '',
					//--
					uid: response.id || '',
					email: response.email || '',
					name: response.name || '',
					timezone: Math.round(response.timezone) * 60 || 0, // timezone in minutes
					location: response.location || '',
					//--
					locale: response.locale || '',
					gender: response.gender || '',
					birthday: response.birthday || '',
					//--
					verified: response.verified ? 1 : 0,
					permissions: perms
					//--
					// #imageurl# : http://graph.facebook.com/{uid}/picture?width=64|1280&height=64|1280
					//--
				};
				if(typeof fxResponseOk === 'function') {
					fxResponseOk(response, FbLoginData);
				} //end if
				storageSetItem('smartfbookjsapi_data', String(JSON.stringify(FbLoginData)), true);
			} //end if
			//console.log(FbLoginData);
		});
		//--
	} //END FUNCTION


	this.init = function(settings, fxSubscribe, fxResponseOk, fxResponseNotOk, fxResponseUnauth) {
		//--
		if(!settings.appId) {
			console.error('ERR: Facebook Js Api: The AppId was not set or is empty ...');
		} //end if
		//-- mandatory settings
		FbSettings.appId = String(settings.appId);
		//-- optional settings
		if(settings.lang) {
			FbSettings.lang = String(settings.lang);
		} //end if
		if(settings.status === false) {
			FbSettings.status = false;
		} //end if else
		if(settings.cookie === false) {
			FbSettings.cookie = false;
		} //end if else
		if(settings.oauth === false) {
			FbSettings.oauth = false;
		} //end if else
		if(settings.xfbml === false) {
			FbSettings.xfbml = false;
		} //end if else
		if(settings.perms) {
			FbSettings.perms = settings.perms;
		} //end if
		//-- async init
		window.fbAsyncInit = function() {
			FB.init({
				appId: 		FbSettings.appId,
				status: 	FbSettings.status,
				cookie: 	FbSettings.cookie,
				xfbml: 		FbSettings.xfbml,
				oauth: 		FbSettings.oauth,
				version: 	FbSettings.version
			});
			FB.Event.subscribe('auth.login', function(response) {
				if(typeof fxSubscribe === 'function') {
					fxSubscribe(response); // Ex: self.location = self.location;
				} //end if
			});
			FB.getLoginStatus(function(response) {
				//console.log('FB:Logging....');
				if(response && response.status === 'connected' && response.authResponse && response.authResponse.accessToken) {
					// the user is logged in and has authenticated your app, and response.authResponse supplies the user's ID,
					// a valid access token, a signed request, and the time the access token  and signed request each expire
					FbAccessToken = response.authResponse.accessToken;
					FbGetLoginData(fxResponseOk);
				} else if(response && response.status === 'not_authorized') {
					// the user is logged in to Facebook, but has not authorized the app
					if(typeof fxResponseUnauth === 'function') {
						fxResponseUnauth(response); // Ex: console.log('WARNING: You must accept this app via Facebook !');
					} //end if
				} else {
					// the user isn't logged in to Facebook
					if(typeof fxResponseNotOk === 'function') {
						fxResponseNotOk(response); // Ex: console.log('WARNING: Facebook Login Failed !');
					} //end if
				}
			});
		};
		//-- load FB api
		(function(d, s, id){
			var js, fjs = d.getElementsByTagName(s)[0];
			if(d.getElementById(id)) {
				return;
			} //end if
			js = d.createElement(s); js.id = id;
			js.src = '//connect.facebook.net/' + FbSettings.lang + '/sdk.js';
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
		//--
	} //END FUNCTION


	this.login = function(fxResponseOk) {
		//--
		FB.getLoginStatus(
			function(response){
				FB.login(
					function(response) {
						if(response && response.authResponse) { //user authorized the app
							if(response.authResponse && response.authResponse.accessToken) {
								FbAccessToken = response.authResponse.accessToken;
								FbGetLoginData(fxResponseOk);
							} //end if
						} //end if
					},
					{
						scope: String(FbSettings.perms),
						return_scopes: true
					}
				);
			} //end function
		);
		//--
	} //END FUNCTION


	this.logout = function(fxResponseLogout) {
		//--
		FB.getLoginStatus(
			function(response){
				if((response && response.status === 'connected') || (response && response.status === 'not_authorized')) {
					FB.logout(function(response) {
						FbLoginData = null;
						FbAccessToken = null;
						storageSetItem('smartfbookjsapi_data', '', true);
						fxResponseLogout(response);
					});
				} //end if
			} //end function
		);
		//--
	} //END FUNCTION


	this.postFeed = function(data, fxDone, fxFail) {
		FB.ui(
			{ // data definition
				method: 		'feed',
				link: 			'' + data.link,
				source: 		'' + data.source, 		// NEW: this replaces 'picture'
				redirect_uri: 	'' + data.redirect_uri, // NEW
				app_id: 		'' + FbSettings.appId, 	// NEW
			//	picture: 		'' + data.picture, 		// deprecated
			//	name: 			'' + data.name, 		// deprecated
			//	caption: 		'' + data.caption, 		// deprecated
			//	description: 	'' + data.description, 	// deprecated
			},
			function(response) {
				if(response && response.post_id) {
					if(typeof fxDone === 'function') {
						fxDone(response); //Ex: console.log('Post was published.');
					} //end if
				} else {
					if(typeof fxFail === 'function') {
						fxFail(response); //Ex: console.log('Post was not published.');
					} //end if
				} //end if else
			}
		);
	} //END FUNCTION


	this.postMedia = function(mB64Data, postMessage, fxDone, fxFail) {
		//--
		if(!mB64Data) {
			if(typeof fxFail === 'function') {
				fxFail(null, null, 'Empty media sent to Facebook');
			} else {
				console.error('ERR: Media / Empty Media');
			} //end if
			return;
		} //end if
		//--
		mB64Data = String(mB64Data);
		//--
		var mimeType = mB64Data.split(',')[0];
		mimeType = mimeType.split(';')[0];
		mimeType = mimeType.split(':')[1];
		//console.log(mimeType);
		var imB64 = mB64Data.split(',')[1];
		mB64Data = null; // free mem
		//console.log(imB64);
		//--
		var blob;
		try {
			if(typeof SmartJS_Base64 != 'undefined') {
				var byteString = SmartJS_Base64.decode(imB64, true); // works in all browsers
			} else {
			//	var byteString = atob(imB64); // IE 10+ ; FFox 3+ ; Webkit 3+ ; Safari 3+ ; Opera 7+
				console.error('ERR: Missing: SmartJS_Base64');
				return;
			} //end if else
			var ab = new ArrayBuffer(byteString.length);
			var ia = new Uint8Array(ab);
			for(var i = 0; i < byteString.length; i++) {
				ia[i] = byteString.charCodeAt(i);
			} //end for
			blob = new Blob([ab], { type: mimeType });
		} catch(e) {
			alert('Failed to convert the B64Image to Blob. See console for more details ...');
			console.error(e);
			return;
		} //end try catch
		//--
		var fd = new FormData();
		fd.append('source', blob);
		fd.append('message', '' + postMessage);
		//--
		FB.login(
			function(response){
				if(response.authResponse) {
					var auth = response.authResponse;
					jQuery.ajax({
						url: 'https://graph.facebook.com/' + auth.userID + '/photos?access_token=' + auth.accessToken,
						type: 'POST',
						data: fd,
						processData: false,
						contentType: false,
						cache: false
					}).done(function(data){
						if(typeof fxDone === 'function') {
							fxDone(data);
						} else {
							console.log('OK: Media uploaded on Facebook');
						} //end if
					}).fail(function(xhr, status, data){
						if(typeof fxFail === 'function') {
							fxFail(xhr, status, data);
						} else {
							console.error('ERROR: Media / Invalid Data');
							console.log(status);
							console.log(data);
						} //end if
					});
				} //end if
			},
			{
				scope: String(FbSettings.perms),
				return_scopes: true,
				auth_type: 'rerequest'
			}
		);
		//--
	} //END FUNCTION


	//===== Data Model


	var storageGetItem = function(key, archive) {
		//--
		var value = getCookie(key);
		//--
		if(archive) {
			if(value) {
				value = String(SmartJS_Base64.decode(String(value)));
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
				value = String(SmartJS_Base64.encode(String(value)));
			} //end if
		} //end if
		//--
		if(value) {
			setCookie(key, value, null, '/', FbSettings.domain);
		} else {
			deleteCookie(key, '/', FbSettings.domain);
		} //end if else
		//--
	} //END FUNCTION


	var getCookie = function(name) {
		//--
		if(typeof SmartJS_BrowserUtils == 'undefined') {
			console.error('ERR: Missing: SmartJS_BrowserUtils');
			return null;
		} //end if
		//--
		return SmartJS_BrowserUtils.getCookie(name);
		//--
	} //END FUNCTION


	var setCookie = function(name, value, days, path, domain, secure, samesite) {
		//--
		if(typeof SmartJS_BrowserUtils == 'undefined') {
			console.error('ERR: Missing: SmartJS_BrowserUtils');
			return false;
		} //end if
		//--
		SmartJS_BrowserUtils.setCookie(name, value, days, path, domain, secure, samesite);
		//--
		return true;
		//--
	} //END FUNCTION


	var deleteCookie = function(name, path, domain, secure, samesite) {
		//--
		if(typeof SmartJS_BrowserUtils == 'undefined') {
			console.error('ERR: Missing: SmartJS_BrowserUtils');
			return false;
		} //end if
		//--
		SmartJS_BrowserUtils.deleteCookie(name, path, domain, secure, samesite);
		//--
		return true;
		//--
	} //END FUNCTION


} //END CLASS


// #END
