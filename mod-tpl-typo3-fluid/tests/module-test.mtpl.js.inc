[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Mod.TPL-Nette-Latte: START :: r.181221

(function() {

	QUnit.module('Templating.Modules', function(){

		QUnit.test('Typo3-Fluid TPL Demo Test: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=tpl-typo3-fluid.test',
				500,
				assert,
				'Typo3-Fluid TPL w. Sub-Template Render (Table with #3 columns):'
			);
		});

	});

})();

//===== QUnit Tests.TPL-Nette-Latte: #END