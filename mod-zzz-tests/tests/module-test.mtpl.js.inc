[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Mod.ZZZ-Tests: START :: r.20190412

(function() {

	QUnit.module('Utils.Modules', function(){

		QUnit.test('ZZZ Tests: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=zzz-tests.test-math-parser',
				500,
				assert,
				'Math Expression Parser Test: OK'
			);
			SmartQUnit.runiFrameTest(
				baseScript + '?page=zzz-tests.test-html2markdown',
				500,
				assert,
				'HTML2Markdown Test: OK'
			);
			SmartQUnit.runiFrameTest(
				baseScript + '?page=zzz-tests.test-svg-draw',
				500,
				assert,
				'SVG Test Result: OK'
			);
		});

	});

})();

//===== QUnit Tests.Mod.ZZZ-Tests: #END
