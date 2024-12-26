
// Facebook JS API: Login Handler
// (c) 2012-present unix-world.org
// v.20241218

// Depends on: jQuery, smartJ$Utils

const FacebookLoginHandler = new class{constructor(){ // STATIC CLASS
	'use strict';
	const _N$ = 'FacebookLoginHandler';

	// :: static
	const _C$ = this; // self referencing

	const _p$ = console;

	let SECURED = false;
	_C$.secureClass = () => { // implements class security
		if(SECURED === true) {
			_p$.warn(_N$, 'Class is already SECURED');
		} else {
			SECURED = true;
			Object.freeze(_C$);
		} //end if
	}; //END

	//========

	const _Utils$ = smartJ$Utils;

	const FbSettings = {
		appId: 		'',
		lang: 		'en_US', // en_US ; ro_RO ; ...
		domain: 	null,
		status: 	false,
		cookie: 	false,
		oauth: 		false,
		xfbml: 		false,
		perms: 		'email',
		fields: 	'id,email,first_name,last_name', // id is always includded
		version: 	'v16.0', // v16 works well with firefox ESR
	};

	let FbLoginData = null;

	const init = (settings, fxSubscribe, fxResponseOk, fxResponseNotOk, fxResponseUnauth) => {
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
		if(settings.domain) {
			FbSettings.domain = String(settings.domain);
		} //end if
		//-- async init
		window.fbAsyncInit = () => {
			FB.init({
				appId: 		FbSettings.appId,
				status: 	FbSettings.status,
				cookie: 	FbSettings.cookie,
				xfbml: 		FbSettings.xfbml,
				oauth: 		FbSettings.oauth,
				version: 	FbSettings.version,
			});
			FB.Event.subscribe('auth.login', (response) => {
				if(typeof fxSubscribe === 'function') {
					fxSubscribe(response); // Ex: self.location = self.location;
				} //end if
			});
			FB.getLoginStatus((response) => {
				//console.log('FB:Logging....');
				if(response && response.status === 'connected' && response.authResponse && response.authResponse.accessToken) {
					// the user is logged in and has authenticated your app, and response.authResponse supplies the user's ID,
					// a valid access token, a signed request, and the time the access token  and signed request each expire
					FbGetLoginData(response.authResponse, fxResponseOk, fxResponseNotOk, fxResponseUnauth);
				} else if(response && response.status === 'not_authorized') {
					// the user is logged in to Facebook, but has not authorized the app
					if(typeof fxResponseUnauth === 'function') {
						fxResponseUnauth(response); // Ex: console.log('WARNING: You must accept the app via Facebook !');
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
		((d, s, id) => {
			let js, fjs = d.getElementsByTagName(s)[0];
			if(d.getElementById(id)) {
				return;
			} //end if
			js = d.createElement(s); js.id = id;
			js.src = '//connect.facebook.net/' + FbSettings.lang + '/sdk.js';
			fjs.parentNode.insertBefore(js, fjs);
		})(document, 'script', 'facebook-jssdk');
		//--
	} //END FUNCTION
	_C$.init = init; // export

	const login = (fxResponseOk, fxResponseNotOk, fxResponseUnauth) => {
		//--
		FB.getLoginStatus(
			(response) => {
				FB.login(
					(response) => {
						if(response && response.authResponse) { //user authorized the app
							if(response.authResponse && response.authResponse.accessToken) {
								FbGetLoginData(response.authResponse, fxResponseOk, fxResponseNotOk, fxResponseUnauth);
							} //end if
						} //end if
					}, // end fx
					{
						scope: String(FbSettings.perms),
						return_scopes: true,
					}
				);
			} //end fx
		);
		//--
	} //END FUNCTION
	_C$.login = login; // export

	const logout = (fxResponseLogout) => {
		//--
		FB.getLoginStatus(
			(response) => {
				if((response && response.status === 'connected') || (response && response.status === 'not_authorized')) {
					FB.logout(
						(response) => {
							FbLoginData = null;
							fxResponseLogout(response);
						}
					);
				} //end if
			} //end fx
		);
		//--
	} //END FUNCTION
	_C$.logout = logout; // export

	//======== PRIVATES: FB

	const FbGetLoginData = (authResponse, fxResponseOk, fxResponseNotOk, fxResponseUnauth) => {
		//-- {{{SYNC-FACEBOOK-GET-ME}}}
		if(!authResponse || !authResponse.accessToken) {
			if(typeof fxResponseUnauth === 'function') {
				fxResponseUnauth(authResponse);
			} //end if
			return;
		} //end if
		//--
		FB.api('/me?fields=' + _Utils$.escape_url(FbSettings.fields), (response) => {
			//console.log('FB.API Response', response);
			if(response && response.id && response.email) {
				//--
				FbLoginData = { // {{{SYNC-FB-DATA}}}
					//--
					isLoggedIn: true,
					//--
					id: _Utils$.stringPureVal(response.id, true),
					email: _Utils$.stringPureVal(response.email, true),
					name_f: _Utils$.stringPureVal(response.first_name, true), // given name
					name_l: _Utils$.stringPureVal(response.last_name, true), // family name
					//--
					accessToken: _Utils$.stringPureVal(authResponse.accessToken, true),
					validateUrl: null, // the validateUrl can be used server-side to validate the response fields
					//--
				};
				//--
				FbLoginData.validateUrl = 'https://graph.facebook.com/' + _Utils$.escape_url(FbLoginData.id) + '/' + '?fields=' + _Utils$.escape_url(FbSettings.fields) + '&access_token=' + _Utils$.escape_url(FbLoginData.accessToken);
				//--
				if(typeof fxResponseOk === 'function') {
					fxResponseOk(authResponse, response, FbLoginData);
				} //end if
				//--
			} else {
				//--
				if(typeof fxResponseNotOk === 'function') {
					fxResponseNotOk(authResponse, response);
				} //end if
				//--
			} //end if else
			//console.log(FbLoginData);
		});
		//--
	} //END FUNCTION

}}; //END CLASS

FacebookLoginHandler.secureClass(); // implements class security

if(typeof(window) != 'undefined') {
	window.FacebookLoginHandler = FacebookLoginHandler; // global export
} //end if

// #END
