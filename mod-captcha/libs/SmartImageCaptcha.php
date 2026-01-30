<?php
// Class: \SmartModExtLib\Captcha\SmartImageCaptcha
// [Smart.Framework.Modules - Captcha / Image Captcha]
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Captcha;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartImageCaptcha - A Flat Image Plugin for SmartCaptcha
 * Create a Form Captcha Validation Image (PNG / GIF / JPEG)
 * If SVG mode is used it will embed the Image into a SVG container to make harder the job of captcha solvers
 *
 * <code>
 * //-- set the captcha options
 * $captcha = new SmartImageCaptcha();
 * $captcha->format='png';
 * $captcha->width = 100;
 * $captcha->height = 50;
 * $captcha->noise = 300;
 * $captcha->chars = 5;
 * $captcha->charfont = 'modules/mod-captcha/fonts/barrio.ttf'; // or a .gdf font if GD have no TTF support
 * $captcha->charttfsize = 24;
 * $captcha->charspace = 18;
 * $captcha->charxvar = 11;
 * $captcha->charyvar = 22;
 * //-- output captcha image
 * header('Content-Type: image/png');
 * echo $captcha->draw_image(); // raw output the captcha image
 * //-- use captcha generated code to store somewhere
 * echo $captcha->get_code();
 * //--
 * </code>
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP GD Extension w. *optional TTF support ; classes: Smart, SmartFileSysUtils
 * @version 	v.20260130
 * @package 	modules:development:Captcha
 */
final class SmartImageCaptcha {

	// ->


	//================================================================

	/**
	 * Captcha Format
	 * possible values: svg | png | gif | jpg
	 * @var ENUM
	 * @default 'svg'
	 */
	public $format = 'svg';

	/**
	 * Captcha Mode (hashed is 10x slower than dotted)
	 * possible values: dotted | hashed
	 * @var ENUM
	 * @default 'dotted'
	 */
	public $mode = 'dotted';

	/**
	 * Captcha Noise Level
	 * possible values: 10..1000
	 * @var INTEGER+
	 * @default 100
	 */
	public $noise = 100;

	/**
	 * Captcha Image Width
	 * possible values: 80..320
	 * @var INTEGER+
	 * @default 160
	 */
	public $width = 160;

	/**
	 * Captcha Image Height
	 * possible values: 40..160
	 * @var INTEGER+
	 * @default 40
	 */
	public $height = 40;

	/**
	 * Captcha Image Quality
	 * Applies only for jpeg format
	 * possible values: 20..100
	 * @var INTEGER+
	 * @default 90
	 */
	public $quality = 90;

	/**
	 * Captcha Characters Pool (the list of characters from where the Captcha will pick random characters)
	 * possible values (from this list) '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
	 * @var STRING
	 * @default '01234567890'
	 */
	public $pool = '01234567890';

	/**
	 * Captcha Characters Number (how many characters from the possible characters list to pickup randomly and display)
	 * possible values: 3..7
	 * @var INTEGER+
	 * @default 5
	 */
	public $chars = 5;

	/**
	 * Captcha Display Tunnings - Characters Space
	 * @var INTEGER+
	 * @default 8
	 */
	public $charspace = 8;

	/**
	 * Captcha Display Tunnings - Characters X space start (5..50)
	 * @var INTEGER+
	 * @default 7
	 */
	public $charxvar = 7;

	/**
	 * Captcha Display Tunnings - Characters Y space start (5..25)
	 * @var INTEGER+
	 * @default 8
	 */
	public $charyvar = 8;

	/**
	 * Captcha Display Tunnings - Characters Color Palette
	 * @var INTEGER+
	 * @default [0x111111, 0x333333, 0x778899, 0x666699, 0x003366, 0x669966, 0x006600, 0xFF3300]
	 */
	public $colors_chars = [0x111111, 0x333333, 0x778899, 0x666699, 0x003366, 0x669966, 0x006600, 0xFF3300];

	/**
	 * Captcha Display Tunnings - Noise Color Palette
	 * @var INTEGER+
	 * @default [0x888888, 0x999999, 0xAAAAAA, 0xBBBBBB, 0xCCCCCC, 0xDDDDDD, 0xEEEEEE, 0x8080C0]
	 */
	public $colors_noise = [0x888888, 0x999999, 0xAAAAAA, 0xBBBBBB, 0xCCCCCC, 0xDDDDDD, 0xEEEEEE, 0x8080C0];

	/**
	 * Captcha Display Tunnings - Font
	 * possible values: 1..5 as INTEGER will use the GD built-in font ; as STRING can be a relative path to a GDF font as 'path/to/font.gdf' or to a TTF font as 'path/to/font.ttf'
	 * @var MIXED
	 * @default 5
	 */
	public $charfont = 5;

	/**
	 * Captcha Display Tunnings - TTF Font Size (apply just for TTF fonts ; the GDF fonts are not scalable)
	 * possible values: 10..70
	 * @var INTEGER+
	 * @default 20
	 */
	public $charttfsize = 20;

	/**
	 * Captcha Display Tunnings - Apply random lines over the image ; If > 0 will apply a number of lines over the image
	 * possible values: 0..9
	 * @var INTEGER+
	 * @default 0
	 */
	public $overlines = 0;

	/**
	 * Captcha Display Tunnings - Apply distort for the image ; If TRUE will distort the image with a random value
	 * @var BOOLEAN
	 * @default false
	 */
	public $distort = false;

	/**
	 * Captcha Display Tunnings - Apply sketchy filter to the image ; If TRUE will apply a sketchy filter to the image
	 * @var BOOLEAN
	 * @default false
	 */
	public $sketchy = false;

	/**
	 * Captcha Display Tunnings - Apply emboss filter to the image ; If TRUE will apply a emboss filter to the image
	 * @var BOOLEAN
	 * @default false
	 */
	public $emboss = false;

	/**
	 * Captcha Display Tunnings - Apply scatter filter to the image ; If TRUE will apply a scatter filter to the image
	 * @var BOOLEAN
	 * @default false
	 */
	public $scatter = false;

	/**
	 * Captcha Display Tunnings - Apply negate filter to the image ; If TRUE will negate the image colors
	 * @var BOOLEAN
	 * @default false
	 */
	public $negate = false;

	/**
	 * Captcha Display Tunnings - Apply contrast to the image ; If TRUE will apply a random contrast to the image
	 * @var BOOLEAN
	 * @default false
	 */
	public $contrast = false;

	/**
	 * Captcha Display Tunnings - Apply colorize effect to the image ; If TRUE will apply a random colorize effect to the image
	 * @var BOOLEAN
	 * @default false
	 */
	public $colorize = false;

	/**
	 * Captcha Display Tunnings - Apply grayscale effect to the image ; If TRUE will apply a grayscale effect to the image
	 * @var BOOLEAN
	 * @default false
	 */
	public $grayscale = false;

	//--
	private $gdmode = null; 		// GD Mode
	private $code = '';				// The Captcha code
	private $time = 0;				// Captcha benchmark
	//--

	//================================================================


	//================================================================
	/**
	 * Class Constructor
	 */
	public function __construct() {
		//--
		$this->time = (float) \microtime(true);
		//--
		if(\function_exists('\\imagecreatetruecolor')) {
			$this->gdmode = 'truecolor';
		} elseif(\function_exists('\\imagecreate')) {
			$this->gdmode = 'color';
		} else {
			\Smart::raise_error('[ERROR] :: '.__METHOD__.' :: PHP-GD extension is required to support ImageCreate.');
			$this->gdmode = false;
		} //end if else
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generate the code and the Image
	 * @return STRING The Captcha Image
	 */
	public function draw_image() {

		//--
		$this->noise = (int) $this->noise;
		if($this->noise < 10) {
			$this->noise = 10;
		} elseif($this->noise > 1000) {
			$this->noise = 1000;
		} //end if
		//--
		$this->width = (int) $this->width;
		if($this->width < 80) {
			$this->width = 80;
		} elseif($this->width > 320) {
			$this->width = 320;
		} //end if
		//--
		$this->height = (int) $this->height;
		if($this->height < 40) {
			$this->height = 40;
		} elseif($this->height > 160) {
			$this->height = 160;
		} //end if
		//--
		$this->quality = (int) $this->quality;
		if($this->quality < 50) {
			$this->quality = 50;
		} elseif($this->quality > 100) {
			$this->quality = 100;
		} //end if else
		//--
		$this->pool = (string) \trim((string)$this->pool);
		if((string)$this->pool == '') {
			$this->pool = '01234567890';
		} //end if
		//--
		$this->chars = (int) $this->chars;
		if($this->chars < 3) {
			$this->chars = 3;
		} elseif($this->chars > 10) {
			$this->chars = 10;
		} //end if else
		//--
		$this->charspace = (int) $this->charspace;
		if($this->charspace < 1) {
			$this->charspace = 1;
		} elseif($this->charspace > 100) {
			$this->charspace = 100;
		} //end if
		//--
		$this->charxvar = (int) $this->charxvar;
		if($this->charxvar < 0) {
			$this->charxvar = 0;
		} elseif($this->charxvar > 100) {
			$this->charxvar = 100;
		} //end if else
		//--
		$this->charyvar = (int) $this->charyvar;
		if($this->charyvar < 0) {
			$this->charyvar = 0;
		} elseif($this->charyvar > 100) {
			$this->charyvar = 100;
		} //end if else
		//--
		$this->colors_chars = (array) $this->colors_chars;
		//--
		$this->colors_noise = (array) $this->colors_noise;
		//--

		//--
		$out = '';
		//--
		\ob_start();
		//--
		if((string)$this->mode == 'hashed') {
			$captcha_arr = (array) $this->generate_captcha_hashed();
		} else { // 'dotted'
			$captcha_arr = (array) $this->generate_captcha_dotted();
		} //end if else
		$captcha_image = $captcha_arr['img-gd-resource'];
		$captcha_word = $captcha_arr['word'];
		unset($captcha_arr);
		//--
		$err = (string) \ob_get_contents();
		\ob_end_clean();
		//--
		if((string)$err != '') { // trigger errors
			\Smart::log_warning(__METHOD__.' # ['.$intext.'] Errors/Output: '.$err);
		} //end if
		//--
	//	if(!\is_resource($captcha_image)) {
		if((!\is_resource($captcha_image)) AND (!\is_a($captcha_image, '\\GdImage'))) { // fix to be PHP8 compatible # https://php.watch/versions/8.0/gdimage
			\Smart::log_warning(__METHOD__.' # Invalid Resource');
			return '';
		} //end if
		//--
		\ob_start();
		//--
		$this->overlines = (int) $this->overlines;
		if($this->overlines < 0) {
			$this->overlines = 0;
		} elseif($this->overlines > 9) {
			$this->overlines = 9;
		} //end if
		for($i=0; $i<$this->overlines; $i++) {
			$captcha_image = $this->draw_over_line($captcha_image, $this->width, $this->height);
		} //end for
		//--
		if($this->distort) {
			$captcha_image = $this->distort($captcha_image, $this->width, $this->height);
		} //end if
		//--
		if($this->sketchy) {
			\imagefilter($captcha_image, \IMG_FILTER_MEAN_REMOVAL);
		} //end if
		if($this->emboss) {
			\imagefilter($captcha_image, \IMG_FILTER_EMBOSS);
		} //end if
		if($this->scatter) {
			\imagefilter($captcha_image, \IMG_FILTER_SCATTER, \Smart::random_number(1, 3), \Smart::random_number(1, 3));
		} //end if
		if($this->negate) {
			\imagefilter($captcha_image, \IMG_FILTER_NEGATE);
		} //end if
		if($this->contrast) {
			\imagefilter($captcha_image, \IMG_FILTER_CONTRAST, \Smart::random_number(-50, 10));
		} //end if
		if($this->colorize) {
			\imagefilter($captcha_image, \IMG_FILTER_COLORIZE, \Smart::random_number(-80, 50), \Smart::random_number(-80, 50), \Smart::random_number(-80, 50));
		} //end if
		if($this->grayscale) {
			\imagefilter($captcha_image, \IMG_FILTER_GRAYSCALE);
		} //end if
		//--
		switch((string)\strtolower((string)$this->format)) {
			case 'svg':
			case 'png':
				// mime type: image/png
				\imagepng($captcha_image); // captcha is speed oriented ! default compression of zlib is 6 ; if there is a need for more optimized images as captcha have to use gif !
				break;
			case 'gif':
				// mime type: image/gif
				\imagegif($captcha_image);
				break;
			case 'jpg':
			case 'jpeg':
			default:
				// mime type: image/jpeg
				\imagejpeg($captcha_image, null, $this->quality);
		} //end switch
		//--
		$out = (string) \ob_get_contents();
		//--
		\ob_end_clean();
		//--
		$captcha_image = null; // image destroy is deprecated and has no effect since PHP 8.0
		//--
		if((string)\strtolower((string)$this->format) == 'svg') {
			// mime type: image/svg+xml (encapsulate PNG in SVG to make harder guessing it by common captcha solvers)
			$out = '<svg width="'.(int)$this->width.'" height="'.(int)$this->height.'" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><image data-time-s="'.\Smart::escape_html((string)\Smart::format_number_dec((float)(\microtime(true) - (float)$this->time), 9, '.', '')).'" href="data:image/png;base64,'.\Smart::escape_html((string)\Smart::b64_enc((string)$out)).'" height="100%" width="100%" /></svg>';
		} //end if
		//--

		//--
		return (string) $out;
		//--

	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Get the generated code
	 * Must be call only after draw_image()
	 * @return STRING The Captcha generated code
	 */
	public function get_code() {
		//--
		return (string) strtoupper((string)$this->code);
		//--
	} //END FUNCTION
	//================================================================


	//===== PRIVATES


	//================================================================
	private function draw_over_line($image, $width, $height) {
		//--
		// inspired from https://github.com/Gregwar/Captcha/blob/master/src/Gregwar/Captcha/CaptchaBuilder.php
		// (c) 2012-2017 Grégoire Passault
		// License: MIT
		//--
		$tcol = \imagecolorallocate($image, \Smart::random_number(100, 255), \Smart::random_number(100, 255), \Smart::random_number(100, 255));
		//--
		if(\Smart::random_number(0, 1)) { // Horizontal
			$Xa   = \Smart::random_number(0, $width/2);
			$Ya   = \Smart::random_number(0, $height);
			$Xb   = \Smart::random_number($width/2, $width);
			$Yb   = \Smart::random_number(0, $height);
		} else { // Vertical
			$Xa   = \Smart::random_number(0, $width);
			$Ya   = \Smart::random_number(0, $height/2);
			$Xb   = \Smart::random_number(0, $width);
			$Yb   = \Smart::random_number($height/2, $height);
		} //end if else
		//--
		\imagesetthickness($image, \Smart::random_number(1, 3));
		\imageline($image, $Xa, $Ya, $Xb, $Yb, $tcol);
		//--
		return $image;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function distort($image, $width, $height, $bg=null) {
		//--
		// inspired from https://github.com/Gregwar/Captcha/blob/master/src/Gregwar/Captcha/CaptchaBuilder.php
		// (c) 2012-2017 Grégoire Passault
		// License: MIT
		//--
		if((string)$this->gdmode == 'truecolor') {
			$contents = \imagecreatetruecolor($width, $height);
		} else {
			$contents = \imagecreate($width, $height);
		} //end if else
		//--
		if(!$bg) {
			$bg = \imagecolorallocate($contents, 255, 255, 255);
		} //end if
		//--
		$X = \Smart::random_number(0, $width);
		$Y = \Smart::random_number(0, $height);
		$phase = \Smart::random_number(0, 10);
		$scale = 1.1 + \Smart::random_number(0, 10000) / 30000;
		//--
		for($x=0; $x<$width; $x++) {
			for($y=0; $y<$height; $y++) {
				$Vx = $x - $X;
				$Vy = $y - $Y;
				$Vn = \sqrt($Vx * $Vx + $Vy * $Vy);
				if($Vn != 0) {
					$Vn2 = $Vn + 4 * \sin($Vn / 30);
					$nX  = $X + ($Vx * $Vn2 / $Vn);
					$nY  = $Y + ($Vy * $Vn2 / $Vn);
				} else {
					$nX = $X;
					$nY = $Y;
				} //end if else
				$nY = $nY + $scale * \sin($phase + $nX * 0.2);
				$p = $this->get_img_color($image, \round($nX), \round($nY), $bg);
				if($p == 0) {
					$p = $bg;
				} //end if
				\imagesetpixel($contents, $x, $y, $p);
			} //end for
		} //end for
		//--
		return $contents;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function get_img_color($image, $x, $y, $bg) {
		//--
		// inspired from https://github.com/Gregwar/Captcha/blob/master/src/Gregwar/Captcha/CaptchaBuilder.php
		// (c) 2012-2017 Grégoire Passault
		// License: MIT
		//--
		if(!$bg) {
			\Smart::raise_error('[ERROR] :: '.__METHOD__.' :: Background must be a not empty.');
			return null;
		} //end if
		//--
		$L = \imagesx($image);
		$H = \imagesy($image);
		//--
		if($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
			return $bg;
		} //end if
		//--
		return \imagecolorat($image, $x, $y);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function generate_color() {
		//-- init
		$min = 0;
		$max = 0;
		$arr = $this->colors_chars;
		//--
		$monochrome = true;
		if(\is_array($arr)) {
			$max = \count($arr) - 1;
			if($max >= 0) {
				$monochrome = false;
			} //end if
		} //end if
		//--
		if($monochrome) {
			$out = 0x999999;
		} else {
			$out = $arr[\Smart::random_number($min,$max)];
		} //end if else
		//--
		return $out;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function generate_noise_color() {
		//-- init
		$min = 0;
		$max = 0;
		$arr = $this->colors_noise;
		//--
		$monochrome = true;
		if(\is_array($arr)) {
			$max = \count($arr) - 1;
			if($max >= 0) {
				$monochrome = false;
			} //end if
		} //end if
		//--
		if($monochrome) {
			$out = 0xCCCCCC;
		} else {
			$out = $arr[\Smart::random_number($min,$max)];
		} //end if else
		//--
		return $out;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function generate_word() {
		//--
		$pool = (string) $this->pool;
		$len = (int) \strlen($pool) - 1;
		if($len <= 0) {
			$len = 1;
		} //end if
		//--
		$str = '';
		//--
		for($i=0; $i<$this->chars; $i++) {
			$str .= (string) \substr($pool, \Smart::random_number(0, (int)$len), 1);
		} //end for
		//--
		$this->code = (string) $str;
		//--
		return (string) $this->code;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function img_draw_text($im, $word) {
		//--
		$use_ttf_font = false;
		if((string)$this->charfont == '') {
			$font = 5;
		} elseif(((string)$this->charfont != '') AND (\SmartFileSysUtils::checkIfSafePath((string)$this->charfont)) AND (\SmartFileSystem::is_type_file((string)$this->charfont))) {
			if(\function_exists('\\imagettftext') AND ((string)\substr((string)$this->charfont, -4, 4) == '.ttf')) {
				$font = (string) $this->charfont;
				$use_ttf_font = true;
			} elseif((string)\substr($this->charfont, -4, 4) == '.gdf') {
				$font = \imageloadfont($this->charfont);
				if($font === false) {
					$font = 5; // on error
				} //end if
			} else { // gdf font
				$font = 5 ; // default
			} //end if else
		} else {
			$font = 5 ; // default
		} //end if else
		//--
		$first_x = (int) \Smart::random_number(\min(10, $this->charxvar), \max(10, $this->charxvar));
		//--
		for($i=0; $i<\strlen($word); $i++) {
			//--
			$w = (string) \substr((string)$word, $i, 1);
			$c = $this->generate_color();
			//--
			if(\Smart::random_number(0, 1)) {
				$sign = -1;
			} else {
				$sign = 1;
			} //end if
			//--
			if($use_ttf_font !== true) { // GDF font
				$y = \Smart::ceil_number(($this->height / 2) + ($sign * \Smart::random_number(0, $this->charyvar)));
				\imagestring($im, (int)$font, (int)$first_x, (int)$y, (string)$w, $c);
			} else { // TTF font
				$y = \Smart::ceil_number(($this->height / 2) + ($this->charttfsize / 2) + ($sign * \Smart::random_number(0, $this->charyvar)));
				if(\Smart::random_number(0, 1)) {
					$angle = \Smart::random_number(0, 30);
				} else {
					$angle = \Smart::random_number(330, 360);
				} //end if else
				\imagettftext($im, $this->charttfsize, (int)$angle, (int)$first_x, (int)$y, $c, (string)\Smart::real_path((string)$font), (string)$w); // fix: on windows, PHP 7+ GD needs real path for TTF Fonts
			} //end if else
			//--
			$first_x += (int) $this->charspace + \Smart::random_number(5, $this->charxvar);
			//--
		} //end for
		//--
	} // END FUNCTION
	//================================================================


	//================================================================
	private function generate_captcha_dotted() {
		//-- inits
		$word = (string) $this->generate_word();
		//-- create image
		if((string)$this->gdmode == 'truecolor') {
			$im = \imagecreatetruecolor($this->width, $this->height);
		} else {
			$im = \imagecreate($this->width, $this->height);
		} //end if else
		//-
		\imagefill($im, 0, 0, 0xDDDDDD);
		//-- add horiz lines
		$margin = 1;
		$first_x = $margin;
		$factor = 7;
		$max_lines = \Smart::ceil_number($this->width / $factor);
		for($i=0; $i<$max_lines; $i++) {
			if($first_x > ($this->width - $margin)) {
				break;
			} //end if
			\imageline($im, $first_x, 2, $first_x, ($this->height-2), 0xFFFFFF);
			$first_x += \Smart::ceil_number($factor);
		} //end for
		//-- add vert lines
		$margin = 1;
		$first_y = $margin;
		$factor = 7;
		$max_lines = \Smart::ceil_number($this->height / $factor);
		for($i=0; $i<$max_lines; $i++) {
			if($first_y > ($this->height - $margin)) {
				break;
			} //end if
			\imageline($im, 2, $first_y, ($this->width-2), $first_y, 0xFFFFFF);
			$first_y += \Smart::ceil_number($factor);
		} //end for
		//-- add text
		$this->img_draw_text($im, $word);
		//-- add noise
		for($i=0; $i<$this->noise; $i++){
			$noise_color = $this->generate_noise_color();
			\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
			\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
			\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
			\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
		} //end for
		//--
		return array('word' => (string)$word, 'img-gd-resource' => $im);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function generate_captcha_hashed() {
		//--
		// inspired from CodeIgniter, https://github.com/bcit-ci/CodeIgniter/blob/develop/system/helpers/captcha_helper.php
		// (c) 2014 - 2019, British Columbia Institute of Technology
		// License: MIT
		//--
		$word = (string) $this->generate_word();
		//--
		$noise = (int) \Smart::ceil_number($this->noise / 2); // sync the level of noise with the dotted one
		//--
		$length	= (int) \strlen($word);
		$angle	= ($length >= 6) ? \Smart::random_number((-1*($length-6)), ($length-6)) : 0;
		$x_axis	= \Smart::random_number(6, ((360 / $length) - 16));
		$y_axis = ($angle >= 0 ) ? \Smart::random_number($this->height, $this->width) : \Smart::random_number(6, $this->height);
		//--
		if((string)$this->gdmode == 'truecolor') {
			$im = \imagecreatetruecolor($this->width, $this->height);
		} else {
			$im = \imagecreate($this->width, $this->height);
		} //end if else
		//--
		\imagefilledrectangle($im, 0, 0, $this->width, $this->height, 0xFFFFFF);
		//--
		$theta = 1;
		$thetac = 7;
		$radius = 16;
		//--
		$circles = (int) ($noise / 1.5);
		if($circles < 1) {
			$circles = 1;
		} //end if
		//--
		$points	= (int) ($noise - $circles);
		if($points < 1) {
			$points = 1;
		} //end if
		//--
		for($i=0; $i<($circles*$points)-1; $i++) {
			//--
			$theta = $theta + $thetac;
			$rad = $radius * ($i / $points);
			$x = ($rad * \cos($theta)) + $x_axis;
			$y = ($rad * \sin($theta)) + $y_axis;
			$theta = $theta + $thetac;
			$rad1 = $radius * (($i + 1) / $points);
			$x1 = ($rad1 * \cos($theta)) + $x_axis;
			$y1 = ($rad1 * \sin($theta )) + $y_axis;
			//--
			\imageline($im, \Smart::ceil_number($x), \Smart::ceil_number($y), \Smart::ceil_number($x1), \Smart::ceil_number($y1), $this->generate_noise_color());
			//--
			$theta = $theta - $thetac;
			//--
		} //end for
		//--
		$this->img_draw_text($im, $word);
		//--
		return array('word' => (string)$word, 'img-gd-resource' => $im);
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
