[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Mod.Zend-DBAL: START :: r.181221

(function() {

	var localTest = [####TEST-MOD-DB|bool####];

	if(!localTest) {

		// skip Zend-DBAL Test
		testingQUnitDisabledTests++;

	} else {

		QUnit.module('DataBase.Modules', function(){

			QUnit.test('Zend DBAL Test: PHP :: iFrame', function(assert) {
				SmartQUnit.runiFrameTest(
					baseScript + '?page=dbal-zend.test&driver=sqlite',
					500,
					assert,
					'Test OK: Zend-DBAL/PDO-SQLITE.'
				);
				SmartQUnit.runiFrameTest(
					baseScript + '?page=dbal-zend.test&driver=pgsql',
					500,
					assert,
					'Test OK: Zend-DBAL/PDO-PGSQL.'
				);
				SmartQUnit.runiFrameTest(
					baseScript + '?page=dbal-zend.test&driver=mysql',
					500,
					assert,
					'Test OK: Zend-DBAL/PDO-MYSQL.'
				);
			});

		});

	} //end if else

})();

//===== QUnit Tests.Mod.Zend-DBAL: #END
