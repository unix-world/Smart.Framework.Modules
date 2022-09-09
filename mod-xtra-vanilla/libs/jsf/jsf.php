<?php

// Smart Jsf Obfuscator (PHP) @head.20191219
// (c) 2019 unix-world.org
// (c) 2019 Kamil Monicz, github.com/Zaczero

/*
$code = (new SmartJsfObfuscate())->encode('ABCDE');
$hcode = htmlspecialchars($code);
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>
<div id="code">{$hcode}</div>
<script>
var obfs = '{$code}';
alert(eval(obfs));
</script>
</body>
</html>
HTML;
echo $html;
*/

final class SmartJsfObfuscate {

	const MIN = 32;
	const MAX = 126;
	const USE_CHAR_CODE = 'USE_CHAR_CODE';

	const SIMPLE = [
		'false' 	=> '![]',
		'true' 		=> '!![]',
		'undefined' => '[][[]]',
		'NaN' 		=> '+[![]]',
		'Infinity' 	=> '+(+!+[]+(!+[]+[])[!+[]+!+[]+!+[]]+[+!+[]]+[+[]]+[+[]]+[+[]])',
	];

	const CONSTRUCTORS = [
		'Array' 	=> '[]',
		'Number' 	=> '(+[])',
		'String' 	=> '([]+[])',
		'Boolean' 	=> '(![])',
		'Function' 	=> '[]["fill"]',
		'RegExp' 	=> 'Function("return/"+false+"/")()',
		'Object'	=> '[]["entries"]()'
	];

	const GLOBALS = 'Function("return this")()';

	private $MAPPING = [
		'a' => '(false+"")[1]',
		'b' => '([]["entries"]()+"")[2]',
		'c' => '([]["fill"]+"")[3]',
		'd' => '(undefined+"")[2]',
		'e' => '(true+"")[3]',
		'f' => '(false+"")[0]',
		'g' => '(false+[0]+String)[20]',
		'h' => '(+(101))["to"+String["name"]](21)[1]',
		'i' => '([false]+undefined)[10]',
		'j' => '([]["entries"]()+"")[3]',
		'k' => '(+(20))["to"+String["name"]](21)',
		'l' => '(false+"")[2]',
		'm' => '(Number+"")[11]',
		'n' => '(undefined+"")[1]',
		'o' => '(true+[]["fill"])[10]',
		'p' => '(+(211))["to"+String["name"]](31)[1]',
		'q' => '("")["fontcolor"]([0]+false+")[20]',
		'r' => '(true+"")[1]',
		's' => '(false+"")[3]',
		't' => '(true+"")[0]',
		'u' => '(undefined+"")[0]',
		'v' => '(+(31))["to"+String["name"]](32)',
		'w' => '(+(32))["to"+String["name"]](33)',
		'x' => '(+(101))["to"+String["name"]](34)[1]',
		'y' => '(NaN+[Infinity])[10]',
		'z' => '(+(35))["to"+String["name"]](36)',
		'A' => '(+[]+Array)[10]',
		'B' => '(+[]+Boolean)[10]',
		'C' => 'Function("return escape")()(("")["italics"]())[2]',
		'D' => 'Function("return escape")()([]["fill"])["slice"]("-1")',
		'E' => '(RegExp+"")[12]',
		'F' => '(+[]+Function)[10]',
		'G' => '(false+Function("return Date")()())[30]',
		'H' => self::USE_CHAR_CODE,
		'I' => '(Infinity+"")[0]',
		'J' => self::USE_CHAR_CODE,
		'K' => self::USE_CHAR_CODE,
		'L' => self::USE_CHAR_CODE,
		'M' => '(true+Function("return Date")()())[30]',
		'N' => '(NaN+"")[0]',
		'O' => '(+[]+Object)[10]',
		'P' => self::USE_CHAR_CODE,
		'Q' => self::USE_CHAR_CODE,
		'R' => '(+[]+RegExp)[10]',
		'S' => '(+[]+String)[10]',
		'T' => '(NaN+Function("return Date")()())[30]',
		'U' => '(NaN+Object()["to"+String["name"]]["call"]())[11]',
		'V' => self::USE_CHAR_CODE,
		'W' => self::USE_CHAR_CODE,
		'X' => self::USE_CHAR_CODE,
		'Y' => self::USE_CHAR_CODE,
		'Z' => self::USE_CHAR_CODE,
		' ' => '(NaN+[]["fill"])[11]',
		'!' => self::USE_CHAR_CODE,
		'"' => '("")["fontcolor"]()[12]',
		'#' => self::USE_CHAR_CODE,
		'$' => self::USE_CHAR_CODE,
		'%' => 'Function("return escape")()([]["fill"])[21]',
		'&' => '("")["fontcolor"](")[13]',
		'\'' => self::USE_CHAR_CODE,
		'(' => '([]["fill"]+"")[13]',
		')' => '([0]+false+[]["fill"])[20]',
		'*' => self::USE_CHAR_CODE,
		'+' => '(+(+!+[]+(!+[]+[])[!+[]+!+[]+!+[]]+[+!+[]]+[+[]]+[+[]])+[])[2]',
		',' => '([]["slice"]["call"](false+"")+"")[1]',
		'-' => '(+(.+[0000001])+"")[2]',
		'.' => '(+(+!+[]+[+!+[]]+(!![]+[])[!+[]+!+[]+!+[]]+[!+[]+!+[]]+[+[]])+[])[+!+[]]',
		'/' => '(false+[0])["italics"]()[10]',
		':' => '(RegExp()+"")[3]',
		';' => '("")["fontcolor"](NaN+")[21]',
		'<' => '("")["italics"]()[0]',
		'=' => '("")["fontcolor"]()[11]',
		'>' => '("")["italics"]()[2]',
		'?' => '(RegExp()+"")[2]',
		'@' => self::USE_CHAR_CODE,
		'[' => '([]["entries"]()+"")[0]',
		'\\' => '(RegExp("/")+"")[1]',
		']' => '([]["entries"]()+"")[22]',
		'^' => self::USE_CHAR_CODE,
		'_' => self::USE_CHAR_CODE,
		'`' => self::USE_CHAR_CODE,
		'{' => '(true+[]["fill"])[20]',
		'|' => self::USE_CHAR_CODE,
		'}' => '([]["fill"]+"")["slice"]("-1")',
		'~' => self::USE_CHAR_CODE,
	];


	public function __construct() {
		$this->FillMissingChars();
		$this->FillMissingDigits();
		$this->ReplaceMap();
		$this->ReplaceStrings();
	} //END FUNCTION


	public function encode($input, $wrapWithEval=false, $runInParentScope=false) {
		$output = [];
		$r = '';
		foreach(self::SIMPLE as $i => $val) {
			$r .= $i.'|';
		} //end foreach
		$r .= '.';
		if(preg_match_all('/'.$r.'/', $input, $matches)) {
			foreach($matches[0] as $find) {
				if(key_exists($find, self::SIMPLE)) {
					$output[] = '['.self::SIMPLE[$find].']+[]';
				} elseif(key_exists($find, $this->MAPPING)) {
					$output[] = $this->MAPPING[$find];
				} else {
					$replacement = '([]+[])['.$this->Encode('constructor').']['.$this->Encode('fromCharCode').']('.$this->Encode((string)ord($find)).')';
					$output[] = $replacement;
					$this->MAPPING[$find] = $replacement;
				} //end if else
			} //end foreach
		} //end if
		$output = join('+', $output);
		if(preg_match('/^\d$/', $input)) {
			$output .= '+[]';
		} //end if
		if($wrapWithEval) {
			if($runInParentScope) {
				$output = '[]['.$this->Encode('fill').']['.$this->Encode('constructor').']('.$this->Encode('return eval').')()('.$output.')';
			} else {
				$output = '[]['.$this->Encode('fill').']['.$this->Encode('constructor').']('.$output.')()';
			} //end if else
		} //end if
		return (string) $output;
	} //END FUNCTION


	//===== PRIVATES


	private function FillMissingChars() {
		foreach($this->MAPPING as $key => $value) {
			if($value === self::USE_CHAR_CODE) {
				$charCode = ord($key);
				$charCodeHex = dechex($charCode);
				$replace = preg_replace('/(\d+)/', '+($1)+"', $charCodeHex);
				$this->MAPPING[$key] = 'Function("return unescape")()("%"'.$replace.'")';
			} //end if
		} //end foreach
	} //END FUNCTION


	private function FillMissingDigits() {
		for($number=0; $number<10; $number++) {
			$output = "+[]";
			if($number > 0) {
				$output = "+!$output";
			} //end if
			for($i=1; $i<$number; $i++) {
				$output = "+!+[]$output";
			} //end for
			if($number > 1) {
				$output = substr($output, 1);
			} //end if
			$this->MAPPING[$number] = "[$output]";
		} //end for
	} //END FUNCTION


	private function ReplaceMap() {
		for($i=self::MIN; $i<=self::MAX; $i++) {
			$char = chr($i);
			$value = $this->MAPPING[$char];
			if(empty($value)) {
				continue;
			} //end if
			foreach(self::CONSTRUCTORS as $key => $val) {
				$value = preg_replace("/\b$key/", $val.'["constructor"]', $value);
			} //end foreach
			foreach(self::SIMPLE as $key => $val) {
				$value = preg_replace("/$key/", $val, $value);
			} //end foreach
			$value = $this->NumberReplacer($value, "/(\d\d+)/i");
			$value = $this->DigitReplacer($value, "/\((\d)\)/i");
			$value = $this->DigitReplacer($value, "/\[(\d)\]/i");
			$value = preg_replace("/GLOBAL/", self::GLOBALS, $value);
			$value = preg_replace("/\+\"\"/", "+[]", $value);
			$value = preg_replace("/\"\"/", "[]+[]", $value);
			$this->MAPPING[$char] = $value;
		} //end for
	} //END FUNCTION


	private function ReplaceStrings() {
		foreach($this->MAPPING as $key => $value) {
			$this->MAPPING[$key] = $this->MappingReplacer((string) $value, "/\"([^\"]+)\"/i");
		} //end foreach
		$count = self::MAX - self::MIN;
		while(true) {
			$missing = $this->FindMissing();
			if(count($missing) <= 0) {
				break;
			} //end if
			foreach($missing as $key => $value) {
				$value = $this->ValueReplacer($value, "/[^\[\]\(\)\!\+]{1}/", $missing);
				$this->MAPPING[$key] = $value;
			} //end foreach
			if($count-- <= 0) {
				//throw new Exception("Could not compile the following chars: ".json_encode($this->FindMissing()));
				break;
			} //end if
		} //end while
	} //END FUNCTION


	private function FindMissing() {
		$missing = [];
		foreach($this->MAPPING as $key => $value) {
			if(preg_match("/[^\[\]\(\)\!\+]{1}/", $value)) {
				$missing[$key] = $value;
			} //end if
		} //end foreach
		return (array) $missing;
	} //END FUNCTION


	private function NumberReplacer($value, $pattern) {
		if(preg_match_all((string)$pattern, (string)$value, $matches, PREG_OFFSET_CAPTURE)) {
			for($i=(count($matches[0])-1); $i >= 0; $i--) {
				$find = $matches[0][$i][0];
				$offs = $matches[0][$i][1];
				$begin = substr($value, 0, $offs);
				$end = substr($value, $offs + strlen($find));
				$values = [];
				for($j=0; $j<strlen($find); $j++) {
					$values[$j] = $find[$j];
				} //end for
				$head = (int) array_shift($values);
				$output = "+[]";
				if($head > 0) {
					$output = "+!$output";
				} //end if
				for($j=1; $j<$head; $j++) {
					$output = "+!+[]$output";
				} //end for
				if($head > 1) {
					$output = substr($output, 1);
				} //end if
				$merged = array_merge([$output], $values);
				$joined = join("+", $merged);
				$value = $begin.$this->DigitReplacer($joined, "/(\d)/").$end;
			} //end for
		} //end if
		return (string) $value;
	} //END FUNCTION


	private function DigitReplacer($value, $pattern) {
		if(preg_match_all((string)$pattern, (string)$value, $matches, PREG_OFFSET_CAPTURE)) {
			for($i=(count($matches[1])-1); $i>=0; $i--) {
				$find = $matches[1][$i][0];
				$offs = $matches[1][$i][1];
				$begin = substr($value, 0, $offs);
				$end = substr($value, $offs + strlen($find));
				$value = $begin.$this->MAPPING[$find].$end;
			} //end for
		} //end if
		return (string) $value;
	} //END FUNCTION


	private function MappingReplacer($value, $pattern) {
		if(preg_match_all((string)$pattern, (string)$value, $matches, PREG_OFFSET_CAPTURE)) {
			for($i=(count($matches[1])-1); $i>=0; $i--) {
				$find = $matches[1][$i][0];
				$offs = $matches[1][$i][1];
				$begin = substr($value, 0, $offs - 1);
				$end = substr($value, $offs + strlen($find) + 1);
				$values = [];
				for($j=0; $j<strlen($find); $j++) {
					$values[$j] = $find[$j];
				} //end for
				$value = $begin.join("+", $values).$end;
			} //end for
		} //end if
		return (string) $value;
	} //END FUNCTION

	private function ValueReplacer($value, $pattern, $missing) {
		if(preg_match_all((string)$pattern, (string)$value, $matches, PREG_OFFSET_CAPTURE)) {
			for($i=(count($matches[0])-1); $i>=0; $i--) {
				$find = $matches[0][$i][0];
				$offs = $matches[0][$i][1];
				$begin = substr($value, 0, $offs);
				$end = substr($value, $offs + strlen($find));
				if(!key_exists($find, $missing)) {
					$value = $begin.$this->MAPPING[$find].$end;
				} else {
					$value = $value;
				} //end if else
			} //end for
		} //end if
		return (string) $value;
	} //END FUNCTION


} //END CLASS
