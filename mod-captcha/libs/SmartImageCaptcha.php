<?php
// Class: \SmartModExtLib\Captcha\SmartImageCaptcha
// [Smart.Framework.Modules - Captcha / Image Captcha]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

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
 * @version 	v.20191217
 * @package 	modules:development:Captcha
 */
final class SmartImageCaptcha {

	// ->


	//================================================================

	/**
	 * Captcha Format
	 * @var ENUM ; possible values: svg | png | gif | jpg
	 * @default 'svg'
	 */
	public $format = 'svg';

	/**
	 * Captcha Mode (hashed is 10x slower than dotted)
	 * @var ENUM ; possible values: dotted | hashed
	 * @default 'dotted'
	 */
	public $mode = 'dotted';

	/**
	 * Captcha Noise Level
	 * @var INTEGER+ ; possible values: 10..1000
	 * @default 100
	 */
	public $noise = 100;

	/**
	 * Captcha Image Width
	 * @var INTEGER+ ; possible values: 80..320
	 * @default 160
	 */
	public $width = 160;

	/**
	 * Captcha Image Height
	 * @var INTEGER+ ; possible values: 40..160
	 * @default 40
	 */
	public $height = 40;

	/**
	 * Captcha Image Quality ; Applies only for jpeg format
	 * @var INTEGER+ ; possible values: 20..100
	 * @default 90
	 */
	public $quality = 90;

	/**
	 * Captcha Characters Pool (the list of characters from where the Captcha will pick random characters)
	 * @var STRING ; possible values (from this list) '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
	 * @default '01234567890'
	 */
	public $pool = '01234567890';

	/**
	 * Captcha Characters Number (how many characters from the possible characters list to pickup randomly and display)
	 * @var INTEGER+ ; possible values: 3..7
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
	 * Possible values: 1..5 as INTEGER will use the GD built-in font ; as STRING can be a relative path to a GDF font as 'path/to/font.gdf' or to a TTF font as 'path/to/font.ttf'
	 * @var MIXED
	 * @default 5
	 */
	public $charfont = 5;

	/**
	 * Captcha Display Tunnings - TTF Font Size (apply just for TTF fonts ; the GDF fonts are not scalable)
	 * Possible values: 10..70
	 * @var INTEGER+
	 * @default 20
	 */
	public $charttfsize = 20;

	//--
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
		if(!\function_exists('gd_info')) {
			\Smart::raise_error(
				'[ERROR] :: '.__CLASS__.' :: PHP-GD extension is required.',
				'A required component is missing ... See error log for more details'
			);
			die('Missing PHP-GD Extension');
		} //end if
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
		$captcha_image = $captcha_arr['rawimage'];
		$captcha_word = $captcha_arr['word'];
		unset($captcha_arr);
		//--
		$err = (string) \ob_get_contents();
		\ob_end_clean();
		//--
		if((string)$err != '') { // trigger errors
			\Smart::log_warning('#Captcha / Draw Image ['.$intext.'] Errors/Output: '.$err);
		} //end if
		//--
		if(!\is_resource($captcha_image)) {
			\Smart::log_warning('#Captcha / Draw Image :: Invalid Resource');
			return '';
		} //end if
		//--
		\ob_start();
		//-
		switch((string)\strtolower((string)$this->format)) {
			case 'svg':
			case 'png':
				// mime type: image/png
				@\imagepng($captcha_image); // captcha is speed oriented ! default compression of zlib is 6 ; if there is a need for more optimized images as captcha have to use gif !
				break;
			case 'gif':
				// mime type: image/gif
				@\imagegif($captcha_image);
				break;
			case 'jpg':
			case 'jpeg':
			default:
				// mime type: image/jpeg
				@\imagejpeg($captcha_image, '', $this->quality);
		} //end switch
		//-
		$out = (string) \ob_get_contents();
		//-
		\ob_end_clean();
		//-
		@\imagedestroy($captcha_image); // free resource
		//--
		if((string)\strtolower((string)$this->format) == 'svg') {
			// mime type: image/svg+xml (encapsulate PNG in SVG to make harder guessing it by common captcha solvers)
			$out = '<svg width="'.(int)$this->width.'" height="'.(int)$this->height.'" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><image data-time-s="'.\Smart::escape_html(\Smart::format_number_dec((float)(\microtime(true) - (float)$this->time), 9, '.', '')).'" href="data:image/png;base64,'.\Smart::escape_html(\base64_encode((string)$out)).'" height="100%" width="100%" /></svg>';
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
		if(\is_int($this->charfont) AND ($this->charfont > 0)) {
			$font = (int) $this->charfont;
		} elseif(((string)$this->charfont != '') AND (\SmartFileSysUtils::check_if_safe_path($this->charfont)) AND (\SmartFileSystem::is_type_file($this->charfont))) {
			if(\function_exists('\\imagettftext') AND (\substr($this->charfont, -4, 4) == '.ttf')) {
				$font = (string) $this->charfont;
				$use_ttf_font = true;
			} elseif((string)\substr($this->charfont, -4, 4) == '.gdf') {
				$font = @\imageloadfont($this->charfont);
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
				$y = \ceil(($this->height / 2) + ($sign * \Smart::random_number(0, $this->charyvar)));
				@\imagestring($im, (int)$font, (int)$first_x, (int)$y, (string)$w, $c);
			} else { // TTF font
				$y = \ceil(($this->height / 2) + ($this->charttfsize / 2) + ($sign * \Smart::random_number(0, $this->charyvar)));
				if(\Smart::random_number(0, 1)) {
					$angle = \Smart::random_number(0, 30);
				} else {
					$angle = \Smart::random_number(330, 360);
				} //end if else
				@\imagettftext($im, $this->charttfsize, (int)$angle, (int)$first_x, (int)$y, $c, (string)$font, (string)$w);
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
		//--

		//-- create image
		if(\function_exists('\\imagecreatetruecolor')) {
			$im = @\imagecreatetruecolor($this->width, $this->height);
		} elseif(\function_exists('\\imagecreate')) {
			$im = @\imagecreate($this->width, $this->height);
		} else {
			\Smart::raise_error(
				'[ERROR] :: '.__METHOD__.' :: PHP-GD extension is required to support ImageCreate.',
				'A required component is missing ... See error log for more details'
			);
			die('Missing PHP-GD Extension is required to support ImageCreate');
		} //end if else
		//-
		@\imagefill($im, 0, 0, 0xDDDDDD);
		//--

		//-- add horiz lines
		$margin = 1;
		$first_x = $margin;
		$factor = 7;
		$max_lines = \ceil($this->width / $factor);
		for($i=0; $i<$max_lines; $i++) {
			if($first_x > ($this->width - $margin)) {
				break;
			} //end if
			@\imageline ($im, $first_x, 2, $first_x, ($this->height-2), 0xFFFFFF);
			$first_x += \ceil($factor);
		} //end for
		//--

		//-- add vert lines
		$margin = 1;
		$first_y = $margin;
		$factor = 7;
		$max_lines = \ceil($this->height / $factor);
		for($i=0; $i<$max_lines; $i++) {
			if($first_y > ($this->height - $margin)) {
				break;
			} //end if
			@\imageline ($im, 2, $first_y, ($this->width-2), $first_y, 0xFFFFFF);
			$first_y += \ceil($factor);
		} //end for
		//--

		//-- add text
		$this->img_draw_text($im, $word);
		//--

		//-- add noise
		for($i=0; $i<$this->noise; $i++){
			$noise_color = $this->generate_noise_color();
			@\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
			@\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
			@\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
			@\imagesetpixel($im, \Smart::random_number(2,$this->width-2), \Smart::random_number(2,$this->height-2), $noise_color);
		} //end for
		//--

		//--
		return array('word' => (string)$word, 'rawimage' => $im);
		//--

	} //END FUNCTION
	//================================================================


	//================================================================
	private function generate_captcha_hashed() {

		// v.170316
		// portions of this code is based on CodeIgniter

		//--
		$word = (string) $this->generate_word();
		//--

		//--
		$length	= (int) \strlen($word);
		$angle	= ($length >= 6) ? \Smart::random_number((-1*($length-6)), ($length-6)) : 0;
		$x_axis	= \Smart::random_number(6, ((360 / $length) - 16));
		$y_axis = ($angle >= 0 ) ? \Smart::random_number($this->height, $this->width) : \Smart::random_number(6, $this->height);
		//--

		//--
		if(\function_exists('\\imagecreatetruecolor')) {
			$im = @\imagecreatetruecolor($this->width, $this->height);
		} elseif(\function_exists('\\imagecreate')) {
			$im = @\imagecreate($this->width, $this->height);
		} else {
			\Smart::raise_error(
				'[ERROR] :: '.__METHOD__.' :: PHP-GD extension is required to support ImageCreate.',
				'A required component is missing ... See error log for more details'
			);
			die('Missing PHP-GD Extension is required to support ImageCreate');
		} //end if else
		//--

		//--
		@\imagefilledrectangle($im, 0, 0, $this->width, $this->height, 0xFFFFFF);
		//--

		//--
		$theta = 1;
		$thetac = 7;
		$radius = 16;
		//--
		$circles = (int) ($this->noise / 1.5);
		if($circles < 1) {
			$circles = 1;
		} //end if
		//--
		$points	= (int) ($this->noise - $circles);
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
			@\imageline($im, $x, $y, $x1, $y1, $this->generate_noise_color());
			//--
			$theta = $theta - $thetac;
			//--
		} //end for
		//--

		//--
		$this->img_draw_text($im, $word);
		//--

		//--
		return array('word' => (string)$word, 'rawimage' => $im);
		//--

	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>