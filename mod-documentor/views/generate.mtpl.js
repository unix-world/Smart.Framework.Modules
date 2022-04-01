[%%%COMMENT%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
// generate-js r.20220331
[%%%/COMMENT%%%]

//===== QUnit: START

QUnit.config.uxmAllowRerun = false;
QUnit.config.uxmHideFilterSearchModules = true;
smartQUnitStartDelay = 500;

(function() {

	// @Done handler
	QUnit.done(function(details) {
		// console.log('QUnit DONE :: Total: ', details.total, ' Failed: ', details.failed, ' Passed: ', details.passed, ' Runtime: ', details.runtime);
	});

	// @TestDone handler
	QUnit.testDone(function(details) {
		if(details.failed > 0) {
			QUnit.config.queue.length = 0; // stop after 1st failure
		} //end if
	});

	// @Settings
	var charSet 				= '[###CHARSET|js###]';
	var phpVersion 				= '[###PHP-VERSION|js###]';
	var phpMinVersion 			= '[###PHP-MIN-VERSION|js###]';
	var phpCompareVer 			= '[###PHP-COMPARE-VERSIONS|js###]';
	var phpResultOkVer 			= '[###PHP-OK-VERSION|js###]';
	var smartFrameworkVersion 	= '[###SF-VERSION|js###]';
	var appRealm 				= '[###APP-REALM|js###]';
	var debugMode 				= '[###DEBUG-MODE|js###]';
	var currentLanguage 		= '[###LANG|js###]';
	var modulePath 				= '[###MODULE-PATH|js###]';
	var baseScript  			= '[###SRV-SCRIPT|js###]';

	// @Self Tests
	QUnit.module('@App-Environment', function(){
		QUnit.test('Server Side Tests: PHP / Framework', function(assert) {
			var expected;
			expected = 'charSet: UTF-8';
			assert.equal('charSet: ' + charSet, expected, expected);
			expected = 'phpVersion: [###PHP-VERSION|js###]';
			assert.equal('phpVersion: ' + phpVersion, expected, expected);
			expected = 'phpMinVersion: [###PHP-MIN-VERSION|js###]';
			assert.equal('phpMinVersion: ' + phpMinVersion, expected, expected);
			expected = 'phpCompareVer: ' + phpResultOkVer;
			assert.equal('phpCompareVer: ' + phpCompareVer, expected, expected);
			expected = 'smartFrameworkVersion: [###SF-VERSION|js###]';
			assert.equal('smartFrameworkVersion: ' + smartFrameworkVersion, expected, expected);
			expected = 'debugMode: no';
			assert.equal('debugMode: ' + debugMode, expected, expected);
			expected = 'appRealm: [###APP-REALM|js###] / [###SRV-SCRIPT|js###]';
			assert.equal('appRealm: ' + appRealm + ' / ' + baseScript, expected, expected);
			expected = 'currentLanguage: en';
			assert.equal('currentLanguage: ' + currentLanguage, expected, expected);
			expected = 'modulePath: [###MODULE-PATH|js###]';
			assert.equal('modulePath: ' + modulePath, expected, expected);
		});
	});

	// Local Tasks

[%%%IF:PAGE:!=;%%%]

	[%%%IF:PRE-TASKS:@>0;%%%]
	var msgOkPreTask = 'Pre-Task OK: Done';
	QUnit.module('Documentation Pre-Generate', function(){
		[%%%LOOP:PRE-TASKS%%%]
		QUnit.test('Task #[###PRE-TASKS.-_INDEX_-|js###]: [###PRE-TASKS._-VAL-_|upper|js###]', function(assert) {
			SmartQUnit.runAjaxTest(
				'task.php?page=[###PAGE|url|js###]&action=[###PRE-TASKS._-VAL-_|url|js###]&mode=[###MODE|url|js###]&extra=[###EXTRA|url|js###]&heading=[###HEADING|url|js###]',
				'GET',
				'json', // data type
				assert,
				msgOkPreTask,
				function(QAsyncTestDone, testOK, msg) {
					var value;
					if((typeof msg == 'object') && (msg.status) && (typeof msg.status == 'string')) {
						if(msg.status == 'OK') {
							value = String(msgOkPreTask);
						} else {
							value = String(msg.status + ': ' + msg.message);
						} //end if else
					} else {
						value = 'INVALID PRE-TASK RESULT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
					} //end if else
					assert.equal(
						value, testOK,
						testOK
					);
					QAsyncTestDone();
				} //end function
			);
		});
		[%%%/LOOP:PRE-TASKS%%%]
	});
	[%%%ELSE:PRE-TASKS%%%]
	// there are no pre-tasks ...
	[%%%/IF:PRE-TASKS%%%]

	[%%%IF:TASKS:@>0;%%%]
	var msgOkTask = 'Task OK: Doc Generated';
	QUnit.module('Documentation Generate', function(){
		[%%%LOOP:TASKS%%%]
		QUnit.test('Task #[###TASKS.-_INDEX_-|js###]: [###TASKS._-VAL-_|js###]', function(assert) {
			SmartQUnit.runAjaxTest(
				'task.php?page=[###PAGE|url|js###]&cls=[###TASKS._-VAL-_|url|js###]&file=[###TASKS._-KEY-_|url|js###]&action=save&mode=[###MODE|url|js###]&extra=[###EXTRA|url|js###]&heading=[###HEADING|url|js###]',
				'GET',
				'json', // data type
				assert,
				msgOkTask,
				function(QAsyncTestDone, testOK, msg) {
					var value;
					if((typeof msg == 'object') && (msg.status) && (typeof msg.status == 'string')) {
						if(msg.status == 'OK') {
							value = String(msgOkTask);
						} else {
							value = String(msg.status + ': ' + msg.message);
						} //end if else
					} else {
						value = 'INVALID TASK RESULT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
					} //end if else
					assert.equal(
						value, testOK,
						testOK
					);
					QAsyncTestDone();
				} //end function
			);
		});
		[%%%/LOOP:TASKS%%%]
	});
	[%%%ELSE:TASKS%%%]
	// there are no tasks ...
	[%%%/IF:TASKS%%%]

	[%%%IF:POST-TASKS:@>0;%%%]
	var msgOkPostTask = 'Post-Task OK: Done';
	QUnit.module('Documentation Post-Generate', function(){
		[%%%LOOP:POST-TASKS%%%]
		QUnit.test('Task #[###POST-TASKS.-_INDEX_-|js###]: [###POST-TASKS._-VAL-_|upper|js###]', function(assert) {
			SmartQUnit.runAjaxTest(
				'task.php?page=[###PAGE|url|js###]&action=[###POST-TASKS._-VAL-_|url|js###]&mode=[###MODE|url|js###]&extra=[###EXTRA|url|js###]&heading=[###HEADING|url|js###]',
				'GET',
				'json', // data type
				assert,
				msgOkPostTask,
				function(QAsyncTestDone, testOK, msg) {
					var value;
					if((typeof msg == 'object') && (msg.status) && (typeof msg.status == 'string')) {
						if(msg.status == 'OK') {
							value = String(msgOkPostTask);
						} else {
							value = String(msg.status + ': ' + msg.message);
						} //end if else
					} else {
						value = 'INVALID POST-TASK RESULT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
					} //end if else
					assert.equal(
						value, testOK,
						testOK
					);
					QAsyncTestDone();
				} //end function
			);
		});
		[%%%/LOOP:POST-TASKS%%%]
	});
	[%%%ELSE:POST-TASKS%%%]
	// there are no post-tasks ...
	[%%%/IF:POST-TASKS%%%]

[%%%/IF:PAGE%%%]

})();

//===== QUnit: #END
