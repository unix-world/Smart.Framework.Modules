
// # JS Package: syntax-ms-ext.pak.js :: #START# :: @ generated from mod-highlight-syntax/views/js/highlightjs-extra/syntax/ms/*.js
// Included Files: ms/*.js #

// ### DO NOT EDIT THIS FILE AS IT WILL BE OVERWRITTEN EACH TIME THE INCLUDED SCRIPTS WILL CHANGE !!! ###

// === ms/dos.js

/*
Language: DOS .bat
Author: Alexander Makarov <sam@rmcreative.ru>
Contributors: Anton Kochkov <anton.kochkov@gmail.com>
*/

// syntax/ms/dos.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('dos',
function(hljs) {
	var COMMENT = hljs.COMMENT(
		/^\s*@?rem\b/, /$/,
		{
			relevance: 10
		}
	);
	var LABEL = {
		className: 'symbol',
		begin: '^\\s*[A-Za-z._?][A-Za-z0-9_$#@~.?]*(:|\\s+label)',
		relevance: 0
	};
	return {
		aliases: ['bat', 'cmd'],
		case_insensitive: true,
		illegal: /\/\*/,
		keywords: {
			keyword:
				'if else goto for in do call exit not exist errorlevel defined ' +
				'equ neq lss leq gtr geq',
			built_in:
				'prn nul lpt3 lpt2 lpt1 con com4 com3 com2 com1 aux ' +
				'shift cd dir echo setlocal endlocal set pause copy ' +
				'append assoc at attrib break cacls cd chcp chdir chkdsk chkntfs cls cmd color ' +
				'comp compact convert date dir diskcomp diskcopy doskey erase fs ' +
				'find findstr format ftype graftabl help keyb label md mkdir mode more move path ' +
				'pause print popd pushd promt rd recover rem rename replace restore rmdir shift' +
				'sort start subst time title tree type ver verify vol ' +
				// winutils
				'ping net ipconfig taskkill xcopy ren del'
		},
		contains: [
			{
				className: 'variable', begin: /%%[^ ]|%[^ ]+?%|![^ ]+?!/
			},
			{
				className: 'function',
				begin: LABEL.begin, end: 'goto:eof',
				contains: [
					hljs.inherit(hljs.TITLE_MODE, {begin: '([_a-zA-Z]\\w*\\.)*([_a-zA-Z]\\w*:)?[_a-zA-Z]\\w*'}),
					COMMENT
				]
			},
			{
				className: 'number', begin: '\\b\\d+',
				relevance: 0
			},
			COMMENT
		]
	};
}
);

// #END

// === ms/powershell.js

/*
Language: PowerShell
Author: David Mohundro <david@mohundro.com>
Contributors: Nicholas Blumhardt <nblumhardt@nblumhardt.com>, Victor Zhou <OiCMudkips@users.noreply.github.com>, Nicolas Le Gall <contact@nlegall.fr>
*/

// syntax/ms/powershell.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('powershell',
function(hljs) {
	var BACKTICK_ESCAPE = {
		begin: '`[\\s\\S]',
		relevance: 0
	};
	var VAR = {
		className: 'variable',
		variants: [
			{begin: /\$[\w\d][\w\d_:]*/}
		]
	};
	var LITERAL = {
		className: 'literal',
		begin: /\$(null|true|false)\b/
	};
	var QUOTE_STRING = {
		className: 'string',
		variants: [
			{ begin: /"/, end: /"/ },
			{ begin: /@"/, end: /^"@/ }
		],
		contains: [
			BACKTICK_ESCAPE,
			VAR,
			{
				className: 'variable',
				begin: /\$[A-z]/, end: /[^A-z]/
			}
		]
	};
	var APOS_STRING = {
		className: 'string',
		variants: [
			{ begin: /'/, end: /'/ },
			{ begin: /@'/, end: /^'@/ }
		]
	};

	var PS_HELPTAGS = {
		className: 'doctag',
		variants: [
			/* no paramater help tags */
			{ begin: /\.(synopsis|description|example|inputs|outputs|notes|link|component|role|functionality)/ },
			/* one parameter help tags */
			{ begin: /\.(parameter|forwardhelptargetname|forwardhelpcategory|remotehelprunspace|externalhelp)\s+\S+/ }
		]
	};
	var PS_COMMENT = hljs.inherit(
		hljs.COMMENT(null, null),
		{
			variants: [
				/* single-line comment */
				{ begin: /#/, end: /$/ },
				/* multi-line comment */
				{ begin: /<#/, end: /#>/ }
			],
			contains: [PS_HELPTAGS]
		}
	);

	return {
		aliases: ['ps'],
		lexemes: /-?[A-z\.\-]+/,
		case_insensitive: true,
		keywords: {
			keyword: 'if else foreach return function do while until elseif begin for trap data dynamicparam end break throw param continue finally in switch exit filter try process catch',
			built_in: 'Add-Computer Add-Content Add-History Add-JobTrigger Add-Member Add-PSSnapin Add-Type Checkpoint-Computer Clear-Content Clear-EventLog Clear-History Clear-Host Clear-Item Clear-ItemProperty Clear-Variable Compare-Object Complete-Transaction Connect-PSSession Connect-WSMan Convert-Path ConvertFrom-Csv ConvertFrom-Json ConvertFrom-SecureString ConvertFrom-StringData ConvertTo-Csv ConvertTo-Html ConvertTo-Json ConvertTo-SecureString ConvertTo-Xml Copy-Item Copy-ItemProperty Debug-Process Disable-ComputerRestore Disable-JobTrigger Disable-PSBreakpoint Disable-PSRemoting Disable-PSSessionConfiguration Disable-WSManCredSSP Disconnect-PSSession Disconnect-WSMan Disable-ScheduledJob Enable-ComputerRestore Enable-JobTrigger Enable-PSBreakpoint Enable-PSRemoting Enable-PSSessionConfiguration Enable-ScheduledJob Enable-WSManCredSSP Enter-PSSession Exit-PSSession Export-Alias Export-Clixml Export-Console Export-Counter Export-Csv Export-FormatData Export-ModuleMember Export-PSSession ForEach-Object Format-Custom Format-List Format-Table Format-Wide Get-Acl Get-Alias Get-AuthenticodeSignature Get-ChildItem Get-Command Get-ComputerRestorePoint Get-Content Get-ControlPanelItem Get-Counter Get-Credential Get-Culture Get-Date Get-Event Get-EventLog Get-EventSubscriber Get-ExecutionPolicy Get-FormatData Get-Host Get-HotFix Get-Help Get-History Get-IseSnippet Get-Item Get-ItemProperty Get-Job Get-JobTrigger Get-Location Get-Member Get-Module Get-PfxCertificate Get-Process Get-PSBreakpoint Get-PSCallStack Get-PSDrive Get-PSProvider Get-PSSession Get-PSSessionConfiguration Get-PSSnapin Get-Random Get-ScheduledJob Get-ScheduledJobOption Get-Service Get-TraceSource Get-Transaction Get-TypeData Get-UICulture Get-Unique Get-Variable Get-Verb Get-WinEvent Get-WmiObject Get-WSManCredSSP Get-WSManInstance Group-Object Import-Alias Import-Clixml Import-Counter Import-Csv Import-IseSnippet Import-LocalizedData Import-PSSession Import-Module Invoke-AsWorkflow Invoke-Command Invoke-Expression Invoke-History Invoke-Item Invoke-RestMethod Invoke-WebRequest Invoke-WmiMethod Invoke-WSManAction Join-Path Limit-EventLog Measure-Command Measure-Object Move-Item Move-ItemProperty New-Alias New-Event New-EventLog New-IseSnippet New-Item New-ItemProperty New-JobTrigger New-Object New-Module New-ModuleManifest New-PSDrive New-PSSession New-PSSessionConfigurationFile New-PSSessionOption New-PSTransportOption New-PSWorkflowExecutionOption New-PSWorkflowSession New-ScheduledJobOption New-Service New-TimeSpan New-Variable New-WebServiceProxy New-WinEvent New-WSManInstance New-WSManSessionOption Out-Default Out-File Out-GridView Out-Host Out-Null Out-Printer Out-String Pop-Location Push-Location Read-Host Receive-Job Register-EngineEvent Register-ObjectEvent Register-PSSessionConfiguration Register-ScheduledJob Register-WmiEvent Remove-Computer Remove-Event Remove-EventLog Remove-Item Remove-ItemProperty Remove-Job Remove-JobTrigger Remove-Module Remove-PSBreakpoint Remove-PSDrive Remove-PSSession Remove-PSSnapin Remove-TypeData Remove-Variable Remove-WmiObject Remove-WSManInstance Rename-Computer Rename-Item Rename-ItemProperty Reset-ComputerMachinePassword Resolve-Path Restart-Computer Restart-Service Restore-Computer Resume-Job Resume-Service Save-Help Select-Object Select-String Select-Xml Send-MailMessage Set-Acl Set-Alias Set-AuthenticodeSignature Set-Content Set-Date Set-ExecutionPolicy Set-Item Set-ItemProperty Set-JobTrigger Set-Location Set-PSBreakpoint Set-PSDebug Set-PSSessionConfiguration Set-ScheduledJob Set-ScheduledJobOption Set-Service Set-StrictMode Set-TraceSource Set-Variable Set-WmiInstance Set-WSManInstance Set-WSManQuickConfig Show-Command Show-ControlPanelItem Show-EventLog Sort-Object Split-Path Start-Job Start-Process Start-Service Start-Sleep Start-Transaction Start-Transcript Stop-Computer Stop-Job Stop-Process Stop-Service Stop-Transcript Suspend-Job Suspend-Service Tee-Object Test-ComputerSecureChannel Test-Connection Test-ModuleManifest Test-Path Test-PSSessionConfigurationFile Trace-Command Unblock-File Undo-Transaction Unregister-Event Unregister-PSSessionConfiguration Unregister-ScheduledJob Update-FormatData Update-Help Update-List Update-TypeData Use-Transaction Wait-Event Wait-Job Wait-Process Where-Object Write-Debug Write-Error Write-EventLog Write-Host Write-Output Write-Progress Write-Verbose Write-Warning Add-MDTPersistentDrive Disable-MDTMonitorService Enable-MDTMonitorService Get-MDTDeploymentShareStatistics Get-MDTMonitorData Get-MDTOperatingSystemCatalog Get-MDTPersistentDrive Import-MDTApplication Import-MDTDriver Import-MDTOperatingSystem Import-MDTPackage Import-MDTTaskSequence New-MDTDatabase Remove-MDTMonitorData Remove-MDTPersistentDrive Restore-MDTPersistentDrive Set-MDTMonitorData Test-MDTDeploymentShare Test-MDTMonitorData Update-MDTDatabaseSchema Update-MDTDeploymentShare Update-MDTLinkedDS Update-MDTMedia Update-MDTMedia Add-VamtProductKey Export-VamtData Find-VamtManagedMachine Get-VamtConfirmationId Get-VamtProduct Get-VamtProductKey Import-VamtData Initialize-VamtData Install-VamtConfirmationId Install-VamtProductActivation Install-VamtProductKey Update-VamtProduct',
			nomarkup: '-ne -eq -lt -gt -ge -le -not -like -notlike -match -notmatch -contains -notcontains -in -notin -replace'
		},
		contains: [
			BACKTICK_ESCAPE,
			hljs.NUMBER_MODE,
			QUOTE_STRING,
			APOS_STRING,
			LITERAL,
			VAR,
			PS_COMMENT
		]
	};
}
);

// #END

// === ms/typescript.js

/*
Language: TypeScript
Author: Panu Horsmalahti <panu.horsmalahti@iki.fi>
Contributors: Ike Ku <dempfi@yahoo.com>
Description: TypeScript is a strict superset of JavaScript
Category: scripting
*/

// syntax/ms/typescript.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('typescript',
function(hljs) {
	var JS_IDENT_RE = '[A-Za-z$_][0-9A-Za-z$_]*';
	var KEYWORDS = {
		keyword:
			'in if for while finally var new function do return void else break catch ' +
			'instanceof with throw case default try this switch continue typeof delete ' +
			'let yield const class public private protected get set super ' +
			'static implements enum export import declare type namespace abstract ' +
			'as from extends async await',
		literal:
			'true false null undefined NaN Infinity',
		built_in:
			'eval isFinite isNaN parseFloat parseInt decodeURI decodeURIComponent ' +
			'encodeURI encodeURIComponent escape unescape Object Function Boolean Error ' +
			'EvalError InternalError RangeError ReferenceError StopIteration SyntaxError ' +
			'TypeError URIError Number Math Date String RegExp Array Float32Array ' +
			'Float64Array Int16Array Int32Array Int8Array Uint16Array Uint32Array ' +
			'Uint8Array Uint8ClampedArray ArrayBuffer DataView JSON Intl arguments require ' +
			'module console window document any number boolean string void Promise'
	};

	var DECORATOR = {
		className: 'meta',
		begin: '@' + JS_IDENT_RE,
	};

	var ARGS =
	{
		begin: '\\(',
		end: /\)/,
		keywords: KEYWORDS,
		contains: [
			'self',
			hljs.QUOTE_STRING_MODE,
			hljs.APOS_STRING_MODE,
			hljs.NUMBER_MODE
		]
	};

	var PARAMS = {
		className: 'params',
		begin: /\(/, end: /\)/,
		excludeBegin: true,
		excludeEnd: true,
		keywords: KEYWORDS,
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			DECORATOR,
			ARGS
		]
	};

	return {
		aliases: ['ts'],
		keywords: KEYWORDS,
		contains: [
			{
				className: 'meta',
				begin: /^\s*['"]use strict['"]/
			},
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			{ // template string
				className: 'string',
				begin: '`', end: '`',
				contains: [
					hljs.BACKSLASH_ESCAPE,
					{
						className: 'subst',
						begin: '\\$\\{', end: '\\}'
					}
				]
			},
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			{
				className: 'number',
				variants: [
					{ begin: '\\b(0[bB][01]+)' },
					{ begin: '\\b(0[oO][0-7]+)' },
					{ begin: hljs.C_NUMBER_RE }
				],
				relevance: 0
			},
			{ // "value" container
				begin: '(' + hljs.RE_STARTERS_RE + '|\\b(case|return|throw)\\b)\\s*',
				keywords: 'return throw case',
				contains: [
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE,
					hljs.REGEXP_MODE,
					{
						className: 'function',
						begin: '(\\(.*?\\)|' + hljs.IDENT_RE + ')\\s*=>', returnBegin: true,
						end: '\\s*=>',
						contains: [
							{
								className: 'params',
								variants: [
									{
										begin: hljs.IDENT_RE
									},
									{
										begin: /\(\s*\)/,
									},
									{
										begin: /\(/, end: /\)/,
										excludeBegin: true, excludeEnd: true,
										keywords: KEYWORDS,
										contains: [
											'self',
											hljs.C_LINE_COMMENT_MODE,
											hljs.C_BLOCK_COMMENT_MODE
										]
									}
								]
							}
						]
					}
				],
				relevance: 0
			},
			{
				className: 'function',
				begin: 'function', end: /[\{;]/, excludeEnd: true,
				keywords: KEYWORDS,
				contains: [
					'self',
					hljs.inherit(hljs.TITLE_MODE, { begin: JS_IDENT_RE }),
					PARAMS
				],
				illegal: /%/,
				relevance: 0 // () => {} is more typical in TypeScript
			},
			{
				beginKeywords: 'constructor', end: /\{/, excludeEnd: true,
				contains: [
					'self',
					PARAMS
				]
			},
			{ // prevent references like module.id from being higlighted as module definitions
				begin: /module\./,
				keywords: { built_in: 'module' },
				relevance: 0
			},
			{
				beginKeywords: 'module', end: /\{/, excludeEnd: true
			},
			{
				beginKeywords: 'interface', end: /\{/, excludeEnd: true,
				keywords: 'interface extends'
			},
			{
				begin: /\$[(.]/ // relevance booster for a pattern common to JS libs: `$(something)` and `$.something`
			},
			{
				begin: '\\.' + hljs.IDENT_RE, relevance: 0 // hack: prevents detection of keywords after dots
			},
			DECORATOR,
			ARGS
		]
	};
}
);

// #END

// === ms/vbnet.js

/*
Language: VB.NET
Author: Poren Chiang <ren.chiang@gmail.com>
*/

// syntax/ms/vbnet.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('vbnet',
function(hljs) {
	return {
		aliases: ['vb'],
		case_insensitive: true,
		keywords: {
			keyword:
				'addhandler addressof alias and andalso aggregate ansi as assembly auto binary by byref byval ' + /* a-b */
				'call case catch class compare const continue custom declare default delegate dim distinct do ' + /* c-d */
				'each equals else elseif end enum erase error event exit explicit finally for friend from function ' + /* e-f */
				'get global goto group handles if implements imports in inherits interface into is isfalse isnot istrue ' + /* g-i */
				'join key let lib like loop me mid mod module mustinherit mustoverride mybase myclass ' + /* j-m */
				'namespace narrowing new next not notinheritable notoverridable ' + /* n */
				'of off on operator option optional or order orelse overloads overridable overrides ' + /* o */
				'paramarray partial preserve private property protected public ' + /* p */
				'raiseevent readonly redim rem removehandler resume return ' + /* r */
				'select set shadows shared skip static step stop structure strict sub synclock ' + /* s */
				'take text then throw to try unicode until using when where while widening with withevents writeonly xor', /* t-x */
			built_in:
				'boolean byte cbool cbyte cchar cdate cdec cdbl char cint clng cobj csbyte cshort csng cstr ctype ' +  /* b-c */
				'date decimal directcast double gettype getxmlnamespace iif integer long object ' + /* d-o */
				'sbyte short single string trycast typeof uinteger ulong ushort', /* s-u */
			literal:
				'true false nothing'
		},
		illegal: '//|{|}|endif|gosub|variant|wend|^\\$ ', /* reserved deprecated keywords */
		contains: [
			hljs.inherit(hljs.QUOTE_STRING_MODE, {contains: [{begin: '""'}]}),
			hljs.COMMENT(
				'\'',
				'$',
				{
					returnBegin: true,
					contains: [
						{
							className: 'doctag',
							begin: '\'\'\'|<!--|-->',
							contains: [hljs.PHRASAL_WORDS_MODE]
						},
						{
							className: 'doctag',
							begin: '</?', end: '>',
							contains: [hljs.PHRASAL_WORDS_MODE]
						}
					]
				}
			),
			hljs.C_NUMBER_MODE,
			{
				className: 'meta',
				begin: '#', end: '$',
				keywords: {'meta-keyword': 'if else elseif end region externalsource'}
			}
		]
	};
}
);

// #END

// === ms/vbscript.js

/*
Language: VBScript
Author: Nikita Ledyaev <lenikita@yandex.ru>
Contributors: Michal Gabrukiewicz <mgabru@gmail.com>
Category: scripting
*/

// syntax/ms/vbscript.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('vbscript',
function(hljs) {
	return {
		aliases: ['vbs'],
		case_insensitive: true,
		keywords: {
			keyword:
				'call class const dim do loop erase execute executeglobal exit for each next function ' +
				'if then else on error option explicit new private property let get public randomize ' +
				'redim rem select case set stop sub while wend with end to elseif is or xor and not ' +
				'class_initialize class_terminate default preserve in me byval byref step resume goto',
			built_in:
				'lcase month vartype instrrev ubound setlocale getobject rgb getref string ' +
				'weekdayname rnd dateadd monthname now day minute isarray cbool round formatcurrency ' +
				'conversions csng timevalue second year space abs clng timeserial fixs len asc ' +
				'isempty maths dateserial atn timer isobject filter weekday datevalue ccur isdate ' +
				'instr datediff formatdatetime replace isnull right sgn array snumeric log cdbl hex ' +
				'chr lbound msgbox ucase getlocale cos cdate cbyte rtrim join hour oct typename trim ' +
				'strcomp int createobject loadpicture tan formatnumber mid scriptenginebuildversion ' +
				'scriptengine split scriptengineminorversion cint sin datepart ltrim sqr ' +
				'scriptenginemajorversion time derived eval date formatpercent exp inputbox left ascw ' +
				'chrw regexp server response request cstr err',
			literal:
				'true false null nothing empty'
		},
		illegal: '//',
		contains: [
			hljs.inherit(hljs.QUOTE_STRING_MODE, {contains: [{begin: '""'}]}),
			hljs.COMMENT(
				/'/,
				/$/,
				{
					relevance: 0
				}
			),
			hljs.C_NUMBER_MODE
		]
	};
}
);

// #END

// ===== [#]

// # JS Package: syntax-ms-ext.pak.js :: #END#
