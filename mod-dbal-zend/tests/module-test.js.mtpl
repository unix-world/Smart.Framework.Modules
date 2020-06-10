[%%%COMMENT%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%/COMMENT%%%]
//===== QUnit Tests.Mod.Zend-DBAL: START :: r.20200121

(function() {

	var localTest = [###TEST-MOD-DB|bool###];

	if(!localTest) {

		// skip Zend-DBAL Test
		smartQUnitDisabledTests++;

	} else {

		localTest = false; // reset

		var localTests = {};
		localTests.SQLite 		= [###TEST-DB-SQLITE|bool###];
		localTests.PostgreSQL 	= [###TEST-DB-PGSQL|bool###];
		localTests.MySQL 		= [###TEST-DB-MYSQL|bool###];
		for(var val in localTests) {
			if(localTests[val] === true) {
				localTest = true;
				break;
			} //end if
		} //end for

		if(!localTest) {

			// skip Zend-DBAL Test
			smartQUnitDisabledTests++;

		} else {

			QUnit.module('DataBase.Modules', function(){

				QUnit.test('Zend DBAL Test: PHP :: iFrame', function(assert) {
					if(localTests.SQLite) {
						SmartQUnit.runiFrameTest(
							baseScript + '?page=dbal-zend.test&driver=sqlite',
							500,
							assert,
							'Test OK: Zend-DBAL/PDO-SQLITE.'
						);
					}
					if(localTests.PostgreSQL) {
						SmartQUnit.runiFrameTest(
							baseScript + '?page=dbal-zend.test&driver=pgsql',
							500,
							assert,
							'Test OK: Zend-DBAL/PDO-PGSQL.'
						);
					}
					if(localTests.MySQL) {
						SmartQUnit.runiFrameTest(
							baseScript + '?page=dbal-zend.test&driver=mysql',
							500,
							assert,
							'Test OK: Zend-DBAL/PDO-MYSQL.'
						);
					}
				});

			});

		} //end if else

	} //end if else

})();

//===== QUnit Tests.Mod.Zend-DBAL: #END
