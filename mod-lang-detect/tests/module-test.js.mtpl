[%%%COMMENT%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%/COMMENT%%%]
//===== QUnit Tests.Mod.Lang-Detect: START :: r.20200121

(function() {

	QUnit.module('Utils.Modules', function(){

		QUnit.test('Language Detection (nGrams) Tests: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=lang-detect.test',
				500,
				assert,
				'Test OK: Language Detection (nGrams: default).'
			);
			SmartQUnit.runiFrameTest(
				baseScript + '?page=lang-detect.test&mode=enhanced',
				500,
				assert,
				'Test OK: Language Detection (nGrams: enhanced).'
			);
		});

	});

})();

//===== QUnit Tests.Mod.Lang-Detect: #END
