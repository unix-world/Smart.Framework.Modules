
// v.170507.r3

(function(iniframe, $){

	window.CustomizerForceUpdate = iniframe;

	var scriptest =  document.querySelector('script[src*="_test.js"]'),
		base      = '../'+ scriptest.attributes.src.value.replace('_test.js', '');
		//alert(base);

	var styles = [

		// uikit core
		'css/themes/{style}/uikit.css',

		// components
		'css/themes/{style}/components/accordion.css',
		'css/themes/{style}/components/autocomplete.css',
		'css/themes/{style}/components/datepicker.css',
		'css/themes/{style}/components/dotnav.css',
		'css/themes/{style}/components/form-advanced.css',
		'css/themes/{style}/components/form-file.css',
		'css/themes/{style}/components/form-password.css',
		'css/themes/{style}/components/form-select.css',
		'css/themes/{style}/components/htmleditor.css',
		'css/themes/{style}/components/nestable.css',
		'css/themes/{style}/components/notify.css',
		'css/themes/{style}/components/placeholder.css',
		'css/themes/{style}/components/progress.css',
		'css/themes/{style}/components/search.css',
		'css/themes/{style}/components/slidenav.css',
		'css/themes/{style}/components/slider.css',
		'css/themes/{style}/components/slideshow.css',
		'css/themes/{style}/components/sortable.css',
		'css/themes/{style}/components/sticky.css',
		'css/themes/{style}/components/tooltip.css',
		'css/themes/{style}/components/upload.css'
	];


	// include needed scripts
	([

		// vendor
		'../../../../lib/js/jquery/jquery.js',
		'js/extra/holder.js',

		// uikit
		'src/js/core/core.js',
		'src/js/core/touch.js',
		'src/js/core/utility.js',
		'src/js/core/smooth-scroll.js',
		'src/js/core/scrollspy.js',
		'src/js/core/toggle.js',
		'src/js/core/alert.js',
		'src/js/core/button.js',
		'src/js/core/dropdown.js',
		'src/js/core/grid.js',
		'src/js/core/modal.js',
		'src/js/core/nav.js',
		'src/js/core/offcanvas.js',
		'src/js/core/switcher.js',
		'src/js/core/tab.js',
		'src/js/core/cover.js'

	]).forEach(function(script) {
		document.writeln('<script src="'+base+script+'"></script>');
	});

	if (iniframe) {
		document.writeln('<link id="data-uikit-theme" rel="stylesheet" href="'+base+'../../../../lib/core/plugins/fonts/typo/sans/ibm-plex-sans.css">');
		document.writeln('<style type="text/css">* { font-family: \'IBM Plex Sans\',arial,sans-serif; }</style>');
		document.writeln('<link id="data-uikit-theme" rel="stylesheet" href="'+base+'../../../../lib/core/plugins/fonts/icons/fontawesome.css">');
		document.writeln('<style data-compiled-css>@import url("'+base+'css/themes/default/uikit.css"); </style>');
	}

	var tests = [

		"::Core",

			"core/alert",
			"core/animation",
			"core/article",
			"core/badge",
			"core/base",
			"core/block",
			"core/breadcrumb",
			"core/button",
			"core/close",
			"core/column",
			"core/comment",
			"core/contrast",
			"core/cover",
			"core/description-list",
			"core/dropdown",
			"core/flex",
			"core/form",
			"core/grid",
			"core/icon",
			"core/list",
			"core/modal",
			"core/nav",
			"core/navbar",
			"core/offcanvas",
			"core/overlay",
			"core/pagination",
			"core/panel",
			"core/scrollspy",
			"core/smooth-scroll",
			"core/subnav",
			"core/switcher",
			"core/tab",
			"core/table",
			"core/text",
			"core/thumbnail",
			"core/thumbnav",
			"core/toggle",
			"core/touch",
			"core/utility",

		"::Components",

			"components/accordion",
			"components/autocomplete",
			"components/datepicker",
			"components/dotnav",
			"components/form-advanced",
			"components/form-file",
			"components/form-password",
			"components/form-select",
			"components/grid-js",
			"components/grid-parallax",
			"components/htmleditor",
			"components/lightbox",
			"components/nestable",
			"components/notify",
			"components/pagination-js",
			"components/parallax",
			"components/placeholder",
			"components/progress",
			"components/search",
			"components/slidenav",
			"components/slider",
			"components/slideshow",
			"components/slideset",
			"components/sortable",
			"components/sticky",
			"components/timepicker",
			"components/tooltip",
			"components/upload"

	];


	document.addEventListener("DOMContentLoaded", function(event) {

		$ = jQuery.noConflict();

		var $body      = $("body").css("visibility", "hidden"),
			$scriptest = $(scriptest),
			controls   = $('<div class="uk-form uk-margin-top uk-margin-bottom uk-container uk-container-center"></div>');

		// test select

		var testfolder = base + 'demo/',
			testselect = $('<select><option value="">- Select Test -</option><option value="overview.html">Overview</option></select>').css("margin", "0 5px"),
			optgroup;

		$.each(tests, function(){

			var value = this, name = value.split("/").slice(-1)[0];

			name = name.charAt(0).toUpperCase() + name.slice(1);

			if (value.indexOf('::')===0) {
				optgroup = $('<optgroup label="'+value.replace('::', '')+'"></optgroup>').appendTo(testselect);
				return;
			}

			optgroup.append('<option value="'+value+'.html">'+name+'</option>');
		});

		testselect.val(testselect.find("option[value$='"+((location.href.match(/overview/) ? '':'/') + location.href.split("/").slice(-1)[0])+"']").attr("value")).on("change", function(){
			if (testselect.val()) location.href = testfolder+testselect.val();
		});

		controls.prepend(testselect);

		if (!iniframe) {

			$.get(base+"doc/customizer/themes.json", {nocache:Math.random()}).always(function(data, type){

				var theme  = localStorage["uikit.theme"] || 'default',
					themes = {
						"default"      : {"name": "Default", "url":"themes/default"},
						"almost-flat"  : {"name": "Almost Flat", "url":"themes/almost-flat"},
						"gradient"     : {"name": "Gradient", "url":"themes/gradient"}
					};

				if (type==="success") {

					themes = {};

					data.forEach(function(item){
						themes[item.id] = {"name": item.name, "url": item.url};
					});
				}

				theme = localStorage["uikit.theme"] || 'default';
				theme = themes[theme] ? theme : 'default';

				// themes
				var themeselect = $('<select><option value="">Select a theme...</option></select>');

				$.each(themes, function(key){
					themeselect.append('<option value="'+key+'">'+themes[key].name+'</option>');
				});

				themeselect.val(theme).on("change", function(){

					if (!themeselect.val()) return;

					localStorage["uikit.theme"] = themeselect.val();
					location.reload();
				});

				testselect.after(themeselect);

				var $style = $scriptest, style;
				styles.forEach(function(style) {
					var st = '<link rel="stylesheet" href="'+base+(style.replace('{style}', theme=='default' ? '':theme))+'">';
					//console.log(st);
					style = $(st);
					$style.after(style);
					$style = style;
				});

				setTimeout(function() { $body.css("visibility", ""); $(window).trigger("resize"); }, 500);
			});

		} else  {

			if(typeof window.parent.SmartFrameworkUiKitSampleDemo != 'undefined') {

				var the_styles = '';
				styles.forEach(function(style) {
					var st = '<link rel="stylesheet" href="'+base+(style.replace('{style}', 'default'))+'">';
					//console.log(st);
					the_styles += st;
				});
				$("body").prepend(the_styles);

			} //end if

			setTimeout(function() { $body.css("visibility", ""); $(window).trigger("resize"); }, 500);
		}

		$body.prepend(controls);
	});

	window.tests = tests;

})(window !== window.parent);
