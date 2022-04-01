[%%%COMMENT%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
// task-all-docs-js r.20210612
[%%%/COMMENT%%%]

//===== QUnit: START

QUnit.config.testTimeout = [###TIMEOUT-TEST|int###] * 1000;
QUnit.config.uxmAllowRerun = false;
QUnit.config.uxmHideFilterSearchModules = true;
smartQUnitStartDelay = 500;

(() => {

	// @Done handler
	QUnit.done((details) => {});

	// @TestDone handler
	QUnit.testDone((details) => {
		if(details.failed > 0) {
			QUnit.config.queue.length = 0; // stop after 1st failure
		} //end if
	});

	// @Settings
	const charSet 				= '[###CHARSET|js###]';
	const appRealm 				= '[###APP-REALM|js###]';
	const debugMode 			= '[###DEBUG-MODE|js###]';
	const currentLanguage 		= '[###LANG|js###]';
	const modulePath 			= '[###MODULE-PATH|js###]';
	const baseScript  			= '[###SRV-SCRIPT|js###]';

	// @Self Tests
	QUnit.module('@App-Environment', () => {
		QUnit.test('Server Side Tests: Framework Settings', (assert) => {
			let expected;
			expected = 'charSet: UTF-8';
			assert.equal('charSet: ' + charSet, expected, expected);
			expected = 'appRealm: [###APP-REALM|js###] / [###SRV-SCRIPT|js###]';
			assert.equal('appRealm: ' + appRealm + ' / ' + baseScript, expected, expected);
			expected = 'debugMode: no';
			assert.equal('debugMode: ' + debugMode, expected, expected);
			expected = 'currentLanguage: en';
			assert.equal('currentLanguage: ' + currentLanguage, expected, expected);
			expected = 'modulePath: [###MODULE-PATH|js###]';
			assert.equal('modulePath: ' + modulePath, expected, expected);
		});
	});

	// Local Tasks

	[%%%IF:TASKS:@>0;%%%]
	const msgOkTask = 'OK: Completed ... DONE ...';
	QUnit.module('Docs Optimize', () => {
		[%%%LOOP:TASKS%%%]
		QUnit.test('Task #[###TASKS.-_INDEX_-|js###]: [###TASKS._-VAL-_|js###]', (assert) => {
			SmartQUnit.runAjaxTest(
				baseScript + '?page=[###PAGE|url|js###]&realm=[###TASKS._-VAL-_|url|js###]',
				'GET',
				'html', // data type
				assert,
				msgOkTask,
				(QAsyncTestDone, testOK, msg) => {
					let value;
					if((typeof(msg) == 'string') && (msg != '')) {
						const $domNodes = $($.parseHTML(msg, null, false)); // null: use new document ; false: do not keep scripts
						value = smartJ$Utils.stringPureVal($domNodes.find('div#operation_success').text(), true); // +trim
					} else {
						value = 'INVALID TASK RESULT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
					} //end if else
					assert.equal(
						value, testOK,
						testOK
					);
					QAsyncTestDone();
				} //end
			);
		});
		[%%%/LOOP:TASKS%%%]
	});
	[%%%ELSE:TASKS%%%]
	// there are no tasks ...
	[%%%/IF:TASKS%%%]

})();

//===== QUnit: #END
