[%%%COMMENT%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%/COMMENT%%%]
//===== QUnit Tests.Mod.Highlight-Syntax: START :: r.20200121

(function() {

	QUnit.module('Utils.Modules', function(){

		QUnit.test('Highlight Syntax (Server-Side) Tests: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=highlight-syntax.test',
				500,
				assert,
				'Syntax Highlight (Server-Side): HTML.'
			);
			SmartQUnit.runiFrameTest(
				baseScript + '?page=highlight-syntax.test&mode=js',
				500,
				assert,
				'Syntax Highlight (Server-Side): JS.'
			);
		});

	});

})();

//===== QUnit Tests.Mod.Highlight-Syntax: #END
