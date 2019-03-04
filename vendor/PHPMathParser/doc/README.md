
A Shunting-Yard Based Math Engine For PHP
https://github.com/ircmaxell/php-math-parser

@commit 5698ff2 / 07 Sep 2016

Sample:

```php

$math = new \PHPMathParser\Math();
$expr = '(((((1 + 2 * ((3 + 4) * 5 + 6)) - 2) / 9) ^ 2) / 3 / 3) / 2 + 0.5';
$answer = $math->evaluate((string)$expr); // int(5)
var_dump($answer);

```
