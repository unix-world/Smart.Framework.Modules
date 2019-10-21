[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Mod.TPL-Dust: START :: r.181221

(function() {

	QUnit.module('Templating.Modules', function(){

		QUnit.test('Dust TPL Demo Test: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=tpl-dust.test',
				500,
				assert,
				'Dust TPL w. Sub-Template Render (Table with #3 columns):'
			);
		});

	});

})();

//===== QUnit Tests.TPL-Dust: #END
