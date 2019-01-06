[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Mod.TPL-Nette-Latte: START :: r.181221

(function() {

	QUnit.module('Templating.Modules', function(){

		QUnit.test('Twig TPL Demo Test: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=tpl-twig.test',
				500,
				assert,
				'Twig TPL w. Sub-Template Render (Table with #3 columns):'
			);
		});

	});

})();

//===== QUnit Tests.TPL-Nette-Latte: #END
