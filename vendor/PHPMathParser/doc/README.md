
A Shunting-Yard Based Math Engine For PHP
https://github.com/ircmaxell/php-math-parser

Sample:

```php

$math = new \PHPMathParser\Math();
$expr = '(((((1 + 2 * ((3 + 4) * 5 + 6)) - 2) / 9) ^ 2) / 3 / 3) / 2 + 0.5';
$answer = $math->evaluate((string)$expr); // int(5)
var_dump($answer);

```
