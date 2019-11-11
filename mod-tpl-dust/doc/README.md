# Dust Templating PHP for Smart.Framework

PHP templating based off Dust.js by LinkedIn - This fork is maintained by unixman (Smart.Framework.Modules) based on a fork from [http://bloafer.github.com/](http://bloafer.github.com/)


## Syntax: Variable Escapes and Formatters (By default this version of Dust Templating will apply no Escape or Formatter)

### Examples:
- {var|h} will escape the variable to HTMLSpecialChars
- {var|j|h} will escape variable to JavascriptSafeChars + HTMLSpecialChars
- {var|u} will escape variable to SafeUrlEncoding
- {var|t} will apply String Trim over variable
- {var|i} will force the var to be Integer

### Index Table of All Escapes and Formatters for a variable

- '|h' Escape Html
- '|j' Escape Js
- '|c' Escape Css
- '|u' Escape Url
- '|o' Escape Json

- '|b' Force Bool
- '|i' Force Integer
- '|d' Force Decimal (default with 2 decimals)
- '|n' Force Numeric

- '|t'  String Trim
- '|ml' String ToLower
- '|mu' String ToUpper
- '|mf' String UcFirst
- '|mw' String UcWords

- '|ih' Format HtmlId
- '|vj' Format JsVar
- '|fn' Format Nl2Br


## Syntax: Special Characters to use in templates:

- {~s} = single space
- {~t} = tab: \t
- {~n} = new line: \n
- {~r} = carriage return: \r
- {~lb} = left curly brace: {
- {~rb} = right curly brace: }


## Syntax: IF / ELSE (ELSE is optional) ; Will compare 'lexpr' (Left Expression) with 'rexpr' (Right Expression) using the 'operator'

### Available IF Comparison Operators

- '==': TRUE IF @lexpr == @rexpr # string or numeric
- '!=': TRUE IF @lexpr != @rexpr # string or numeric
- '<=': TRUE IF @lexpr <= @rexpr # numeric
- '<': TRUE IF @lexpr < @rexpr # numeric
- '>=': TRUE IF @lexpr >= @rexpr # numeric
- '>': TRUE IF @lexpr > @rexpr # numeric
- '%': TRUE IF ((@lexpr % @rexpr) == 0) # numeric
- '!%': TRUE IF ((@lexpr % @rexpr) != 0) # numeric

- '@==': TRUE IF ArraySize(@lexpr) == Integer(@rexpr)
- '@!=': TRUE IF ArraySize(@lexpr) != Integer(@rexpr)
- '@<=': TRUE IF ArraySize(@lexpr) <= Integer(@rexpr)
- '@<': TRUE IF ArraySize(@lexpr) < Integer(@rexpr)
- '@>=': TRUE IF ArraySize(@lexpr) >= Integer(@rexpr)
- '@>': TRUE IF ArraySize(@lexpr) > Integer(@rexpr)

- '?': TRUE IF String(@lexpr) is in List(@rexpr):SeparedBy(|))
- '!?': TRUE IF String(@lexpr) is not in List(@rexpr):SeparedBy(|))

- '^~': TRUE IF @lexpr starts with @rexpr # case sensitive string match
- '^*': TRUE IF @lexpr starts with @rexpr # case insensitive string match
- '&~': TRUE IF @lexpr contains @rexpr # case sensitive string match
- '&*': TRUE IF @lexpr contains @rexpr # case insensitive string match
- '$~': TRUE IF @lexpr ends with @rexpr # case sensitive string match
- '$*': TRUE IF @lexpr ends with @rexpr # case insensitive string match

### IF / ELSE Sample Usage

```dust

{@if lexpr="{a}" operator="==" rexpr="B"}
	variable a IS EQUAL (==) with string 'B'
{:else}
	variable a IS NOT EQUAL (==) with string 'B'
{/if}

```

## LOOP Context of @array

```dust
{#myarr}
	//-- loop internals
	index: {$idx} // array loop current index (1..n)
	iterator: {$iter} // array iterator (0..n-1)
	maxsize: {$len} // array max size (n)
	maxcount: {$cnt} // array max count (n-1)
	//-- array values by keys
	{key1} // outputs value of array[key1]
	{key2} // outputs value of array[key2]
	...
	{keyN} // outputs value of array[keyN]
	//--
{/myarr}
```

