[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Mod.TPL-Nette-Latte: START :: r.20191021

(function() {

	QUnit.module('Templating.Modules', function(){

		QUnit.test('Nette-Latte TPL Demo Test: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=tpl-nette-latte.test',
				500,
				assert,
				'nette/Latte TPL w. Sub-Template Render (Table with #3 columns):'
			);
		});

	});

})();

//===== QUnit Tests.TPL-Nette-Latte: #END
