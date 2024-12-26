<?php
// [LIB - Smart.Framework.Modules / ExtraLibs / GD Charts]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// PHP Charts - Output to PNG or GIF images
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
// DEPENDS-EXT: PHP GD Extension
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartImgBizCharts - Generates IMG Biz Charts
 *
 * <code>
 *
 * // Sample Usage (in a controller):
 * //--
 * $chart = new SmartImgBizCharts(
 * 		'matrix', // currently, only matrix type is stable and implemented
 * 		'Chart Title',
 * 		array(
 * 			'Chart 1' => array(
 * 				'red' => array('x'=>Smart::random_number(5,7), 'y'=>Smart::random_number(100,120), 'z'=>Smart::random_number(45,75), 'color'=>'#FF3300', 'labelcolor'=>'#003399'),
 *				'blue' => array('x'=>Smart::random_number(100,115), 'y'=>Smart::random_number(200,210), 'z'=>Smart::random_number(20,50), 'color'=>'#003399'),
 *				'green' => array('x'=>Smart::random_number(150,175), 'y'=>Smart::random_number(250,270), 'z'=>Smart::random_number(2,8), 'color'=>'#33CC33'),
 *				'yellow' => array('x'=>Smart::random_number(400,420), 'y'=>Smart::random_number(70,90), 'z'=>Smart::random_number(50,90), 'color'=>'#FFCC00'),
 *				'default' => array('x'=>Smart::random_number(300,325), 'y'=>Smart::random_number(300,320))
 *			)
 *		),
 *		'png'
 * );
 * $chart->width = 500;
 * $chart->height = 500;
 * $chart->axis_x_label = 'Relative Market Share';
 * $chart->axis_y_label = 'Market Growth Rate';
 * //--
 * $this->PageViewSetCfg('rawpage', true);
 * $this->PageViewSetCfg('rawmime', $chart->mime_header());
 * $this->PageViewSetCfg('rawdisp', $chart->disposition_header());
 * $this->PageViewSetVar('main', $chart->generate());
 * //--
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20210307
 * @package 	extralibs:ViewComponents
 *
 */
final class SmartImgBizCharts {

	//->

// TODO: Order The Array DESCENDING by Sizes because the small ones may appear in the back of big ones ...

//======================================================================
//-- public required
public $width = 700;
public $height = 500;
public $axis_x_label = 'X-Axis';
public $axis_y_label = 'Y-Axis';
public $labelcolor = '#000000';
public $defaultcolor = '#666699';
//-- public settings
public $left = 50;
public $top = 50;
public $right = 50;
public $bottom = 50;
public $x_mark = 50;
public $y_mark = 50;
public $bgcolor = '#F9F9F9';
public $framecolor = '#CCCCCC';
public $axiscolor = '#778899';
public $markcolor = '#ECECEC';
//-- debugging
public $debug = 0;
//--
//======================================================================
//-- privates
private $title; // chart title
private $data; // chart data
private $format; // gif | png
private $type; // chart type
private $img; // image object
private $area_width;
private $area_height;
//--
//======================================================================


//====================================================================== INIT
public function __construct($y_type, $y_title, $y_arr_data, $y_format='png') {
	//--
	if(!function_exists('imagecreatetruecolor')) {
		Smart::log_warning('"[ERROR] :: SmartImgBizCharts :: PHP-GD TrueColor extension is missing ...');
		return;
	} //end if
	//--
	switch((string)$y_type) {
		case 'matrix':
			$this->type = 'matrix';
			break;
		default:
			Smart::log_warning('"[ERROR] :: SmartImgBizCharts :: Invalid Chart Type: '.$y_type.' ...');
			return;
	} //end if
	//--
	$this->title = (string) SmartUnicode::deaccent_str((string)$y_title);
	//--
	if(is_array($y_arr_data)) {
		$this->data = (array) $y_arr_data;
	} else {
		$this->data = array();
	} //end if else
	//--
	if((string)$y_format == 'gif') {
		$this->format = 'gif';
	} else {
		$this->format = 'png';
	} //end if else
	//--
} //END FUNCTION
//======================================================================


//=====================================================================
public function mime_header() {
	//--
	return (string) 'image/'.$this->format;
	//--
} //END FUNCTION
//=====================================================================


//=====================================================================
public function disposition_header($y_filename='biz-chart') {
	//--
	return (string) 'inline; filename="'.Smart::safe_validname($y_filename.'-'.time().'.'.$this->format).'"';
	//--
} //END FUNCTION
//=====================================================================


//======================================================================
public function generate($y_mode='raw') {

	//--
	$this->axis_x_label = (string) SmartUnicode::deaccent_str((string)$this->axis_x_label);
	$this->axis_y_label = (string) SmartUnicode::deaccent_str((string)$this->axis_y_label);
	//--

	//--
	$this->area_width = $this->width - $this->left - $this->right;
	$this->area_height = $this->height - $this->top - $this->bottom;
	//--

	//--
	$this->img = @imagecreatetruecolor($this->width, $this->height);
	//--

	//--
	$bgcolor = $this->color_alocate($this->bgcolor);
	@imagefill($this->img, 0, 0, $bgcolor);
	//--

	//--
	$framecolor = $this->color_alocate($this->framecolor);
	$axiscolor = $this->color_alocate($this->axiscolor);
	$markcolor = $this->color_alocate($this->markcolor);
	//--

	//-- draw frame
	@imagerectangle($this->img, $this->left, $this->top, $this->width - $this->right, $this->height - $this->bottom, $framecolor);
	//--

	//-- draw markers on x axis
	for($i=($this->left+$this->x_mark); $i<($this->area_width + $this->left); $i++) {
		@imageline($this->img, $i, $this->top + 1, $i, $this->height - $this->bottom, $markcolor);
		$i += $this->x_mark - 1;
	} //end for
	//-- draw markers on y axis
	for($i=($this->top+$this->y_mark); $i<($this->area_height + $this->top); $i++) {
		@imageline($this->img, $this->left, $i, $this->width - $this->right, $i, $markcolor);
		$i += $this->y_mark - 1;
	} //end for
	//--

	//-- x axis
	$this->draw_arrow($this->left - 10, $this->height - $this->bottom, $this->width - $this->right + 20, $this->height - $this->bottom, 10, 5, $axiscolor);
	@imageline($this->img, $this->left, $this->height - $this->bottom - round($this->area_height / 2), $this->width - $this->right, $this->height - $this->bottom - round($this->area_height / 2), $axiscolor);
	//-- y axis
	$this->draw_arrow($this->left, $this->height - $this->bottom + 10, $this->left, $this->top - 20, 10, 5, $axiscolor);
	@imageline($this->img, $this->left + round($this->area_width / 2), $this->height - $this->bottom, $this->left + round($this->area_width / 2), $this->top, $axiscolor);
	//--

	//-- set min and max
	$min_x = 0;
	$max_x = 0;
	$min_y = 0;
	$max_y = 0;
	$arr_x = array();
	$arr_y = array();
	//--
	if(is_array($this->data)) {
		foreach($this->data as $key => $chart) {
			if(is_array($chart)) {
				foreach($chart as $label => $dat) {
					$arr_x[] = $dat['x'];
					$arr_y[] = $dat['y'];
				} //end foreach
			} //end if
		} //end foreach
	} //end if
	//--
	$min_x = min($arr_x);
	$max_x = max($arr_x);
	$min_y = min($arr_y);
	$max_y = max($arr_y);
	//-- debug
	if($this->debug) {
		@imagestring($this->img, 3, $this->left + 15, $this->top - 25, 'Min and Max: [x] '.$min_x.'::'.$max_x.' of '.$this->area_width.' / [y] '.$min_y.'::'.$max_y.' of '.$this->area_height, $axiscolor);
	} elseif((string)$this->title != '') {
		@imagestring($this->img, 3, $this->left + 15, $this->top - 25, $this->title, $axiscolor);
	} //end if
	//-- end debug
	$delta_x = $max_x - $min_x;
	if($delta_x <= 0) {
		$delta_x = 0.01; // avoid division by zero or negative
	} //end if
	$delta_y = $max_y - $min_y;
	if($delta_y <= 0) {
		$delta_y = 0.01; // avoid division by zero or negative
	} //end if
	//--
	$ratio_x = $this->area_width / $delta_x;
	if($ratio_x > 1) {
		$ratio_x = 1;
	} //end if
	$ratio_y = $this->area_height / $delta_y;
	if($ratio_y > 1) {
		$ratio_y = 1;
	} //end if
	//--
	if($this->debug) {
		@imagestring($this->img, 3, $this->left + 15, $this->top - 15, 'Ratios: [x] '.$ratio_x.' / [y] '.$ratio_y, $axiscolor);
	} //end if
	//--

	//-- draw chart by type
	switch((string)$this->type) {
		case 'matrix':
			$this->draw_chart_matrix($this->data, $ratio_x, $ratio_y);
			break;
		default: // invalid type !
			@imagestring($this->img, 5, $this->left + 15, $this->top + 15, 'INVALID Chart Type: '.SmartUnicode::deaccent_str((string)$this->type), $this->color_alocate($this->defaultcolor));
	} //end if
	//--

	//-- X axis label
	$len_x_text = @imagefontwidth(4) * strlen($this->axis_x_label);
	@imagestring($this->img, 4, $this->width - $this->right - $len_x_text - 10, $this->height - $this->bottom + 15, $this->axis_x_label, $axiscolor);
	//-- Y axis label
	$len_y_text = @imagefontwidth(4) * strlen($this->axis_y_label);
	@imagestringup($this->img, 4, $this->left - 35, $this->top + $len_y_text + 10, $this->axis_y_label, $axiscolor);
	//--

	//-- (c)
	$this->draw_credits();
	//--

	//--
	ob_start();
	//--
	if((string)$this->format == 'gif') { // gif
		//--
		@imagegif($this->img);
		//--
	} else { // png (default)
		//--
		@imagepng($this->img); // charts are speed oriented ! default compression of zlib is 6 ; if there is a need for more optimized images as captcha have to use gif !
		//--
	} //end if else
	//--
	$out = ob_get_contents();
	//--
	ob_end_clean();
	//--
	@imagedestroy($this->img);
	$this->img = null;
	//--

	//--
	if((string)$y_mode == 'html') {
		return (string) '<img src="data:image/'.$this->format.';base64,'.Smart::escape_html(base64_encode((string)$out)).'">';
	} else {
		return (string) $out;
	} //end if else
	//--

} //END FUNCTION
//======================================================================


// ===== PRIVATES


//======================================================================
private function draw_credits() {
	//--
	if($this->debug) {
		$copyrightext = 'DEBUG ON :: SmartFramework PHP BizCharts';
		$copyrightcolor = @imagecolorallocate($this->img, 0, 0, 0); // #000000
	} else {
		$copyrightext = '(c) SmartFramework PHP BizCharts';
		$copyrightcolor = @imagecolorallocate($this->img, 221, 221, 221); // #DDDDDD
	} //end if else
	//--
	$x = (($this->width / 2) - ((@imagefontwidth(1) * strlen($copyrightext)) / 2));
	$y = ($this->height - 10);
	//--
	@imagestring($this->img, 1, $x, $y, $copyrightext, $copyrightcolor);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function draw_chart_matrix($arr, $ratio_x=1, $ratio_y=1) {
	//-- draw data
	if(is_array($arr)) {
		//--
		foreach($arr as $key => $chart) {
			//--
			if(is_array($chart)) {
				//--
				foreach($chart as $label => $dat) {
					//--
					if((string)$dat['color'] != '') {
						$tmp_color = $this->color_alocate((string)$dat['color']);
					} else {
						$tmp_color = $this->color_alocate($this->defaultcolor);
					} //end if else
					//--
					if((string)$dat['labelcolor'] != '') {
						$labelcolor = $this->color_alocate((string)$dat['labelcolor']);
					} else {
						$labelcolor = $this->color_alocate($this->labelcolor);
					} //end if else
					//--
					if($dat['z'] < 5) {
						$dat['z'] = 5; // default and min z size
					} //end if
					if($dat['z'] > 105) {
						$dat['z'] = 105; // max z size
					} //end if
					//--
					$tmp_x = $this->left + round($dat['x'] * $ratio_x);
					$tmp_y = $this->top + round($dat['y'] * $ratio_y);
					//--
					@imagefilledellipse($this->img, $tmp_x, $tmp_y, $dat['z'], $dat['z'], $tmp_color);
					//--
					if($this->debug) {
						@imagestring($this->img, 1, $tmp_x - round($dat['z']/4) - 25, $tmp_y + round($dat['z']/4) - 3, $tmp_x.'('.$dat['x'].')'.' / '.$tmp_y.'('.$dat['y'].')', $labelcolor);
					} // end if
					@imagestring($this->img, 2, $tmp_x - round($dat['z']/4) - 3, $tmp_y + round($dat['z']/4) + 3, (string)SmartUnicode::deaccent_str((string)$label), $labelcolor);
					//--
				} //end foreach
				//--
			} //end if
			//--
		} //end foreach
		//--
	} //end if
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function draw_arrow($x1, $y1, $x2, $y2, $alength, $awidth, $color) {
	//--
	$distance = sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));
	$dx = $x2 + ($x1 - $x2) * $alength / $distance;
	$dy = $y2 + ($y1 - $y2) * $alength / $distance;
	$k = $awidth / $alength;
	$x2o = $x2 - $dx;
	$y2o = $dy - $y2;
	$x3 = $y2o * $k + $dx;
	$y3 = $x2o * $k + $dy;
	$x4 = $dx - $y2o * $k;
	$y4 = $dy - $x2o * $k;
	//--
	@imageline($this->img, $x1, $y1, $dx, $dy, $color);
	//--
	@imagefilledpolygon($this->img, array($x2, $y2, $x3, $y3, $x4, $y4), 3, $color);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// converts a #CCCCCC color to GD colors v.160107
private function color_alocate($y_color) {
	//-- init
	$r = 0;
	$g = 0;
	$b = 0;
	//--
	$y_color = trim((string)$y_color);
	//--
	if(preg_match('/#[0-9a-fA-F]{6}/', (string)$y_color)) {
		//--
		$r = hexdec(substr($y_color, 1, 2));
		$g = hexdec(substr($y_color, 3, 2));
		$b = hexdec(substr($y_color, 5, 2));
	} //end if
	//--
	return @imagecolorallocate($this->img, $r, $g, $b);
	//--
} //END FUNCTION
//======================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartImgGfxCharts - Generates IMG Gfx Charts.
 *
 * <code>
 *
 * // Sample Usage:
 * //--
 *	$chart = new SmartImgGfxCharts(
 *		'vbars', // vbars, hbars, dots, lines, pie, donut
 *		"Title goes here",
 *		array(
 *			array(
 *				'x' => "white",
 *				'y' => Smart::random_number(10,90),
 *				'z' => Smart::random_number(10,90),
 *				'w' => 10,
 *				'v' => '#ECECEC'
 *			),
 *			array(
 *				'x' => "red",
 *				'y' => 22.45,
 *				'z' => Smart::random_number(10,90),
 *				'w' => 25,
 *				'v' => '#FF3300'
 *			),
 *			array(
 *				'x' => "blue",
 *				'y' => Smart::random_number(10,90),
 *				'z' => Smart::random_number(10,90),
 *				'w' => 7,
 *				'v' => '#003399'
 *			),
 *			array(
 *				'x' => "yellow",
 *				'y' => Smart::random_number(10,90),
 *				'z' => Smart::random_number(10,90),
 *				'w' => 17,
 *				'v' => '#FFCC00'
 *			),
 *			array(
 *				'x' => "green",
 *				'y' => Smart::random_number(10,90),
 *				'z' => Smart::random_number(10,90),
 *				'w' => 31,
 *				'v' => '#33CC33'
 *			),
 *			array(
 *				'x' => "black",
 *				'y' => Smart::random_number(10,90),
 *				'z' => Smart::random_number(10,90),
 *				'w' => 17,
 *				'v' => '#333333'
 *			)
 *		),
 *		'png'
 *	);
 *	$chart->axis_x = 'X-Axis';
 *	$chart->axis_y = 'Y-Axis';
 *	//--
 *	header('Content-Type: '.$chart->mime_header());
 *	header('Content-Disposition: '.$chart->disposition_header());
 *	echo $chart->generate();
 * //--
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20210307
 * @package 	extralibs:ViewComponents
 *
 */
final class SmartImgGfxCharts {

	// ->


//======================================================================
//--
public $axis_x = ''; // X axis label
public $axis_y = ''; // Y axis label
public $axis_dec = 0; // number of decimals for axis
//--
public $tsize = 5; // title text size
public $size = 2; // labels text size
//--
public $h3d = 15; // 3D height
//--
//======================================================================
//--
private $title; // chart title
private $height_title; // auto calculated height for title
private $type; // 1..6 (vbars, hbars, dots, lines, pie, donut)
private $format; // gif | png
private $skin;
private $color;
//--
private $width; // width is auto-calculated
private $height; // height is auto-calculated
//--
private $x; // label
private $y; // 1st series
private $z; // 2nd series (not for pie or donut)
private $w; // buble size
private $v; // custom color
//--
private $total_parameters;
private $sum_total;
private $biggest_x;
private $biggest_y;
private $graphic_1;
private $graphic_2;
private $exists_graph2;
private $graphic_area_x1;
private $graphic_area_y1;
private $graphic_area_x2;
private $graphic_area_y2;
private $legend_exists;
//--
//======================================================================


//======================================================================
public function __construct($y_type, $y_title, $y_arr_data, $y_format='png', $y_display_graph2=true, $y_display_graph_depths=true) {
	//--
	if(!function_exists('imagecreatetruecolor')) {
		Smart::log_warning('"[ERROR] :: SmartImgGfxCharts :: PHP-GD TrueColor extension is missing ...');
		return;
	} //end if
	//--
	switch((string)$y_type) {
		case 'vbars':
			$this->type = 1;
			break;
		case 'hbars':
			$this->type = 2;
			break;
		case 'dots':
			$this->type = 3;
			break;
		case 'lines':
			$this->type = 4;
			break;
		case 'pie':
			$this->type = 5;
			break;
		case 'donut':
			$this->type = 6;
			break;
		default:
			Smart::log_warning('"[ERROR] :: SmartImgBizCharts :: Invalid Chart Type: '.$y_type.' ...');
			return;
	} //end if
	//--
	$this->title = (string) SmartUnicode::deaccent_str((string)$y_title);
	//--
	if((string)$y_format == 'gif') {
		$this->format = 'gif';
	} else {
		$this->format = 'png';
	} //end if else
	//--
	$this->skin = 1; // by now only this color schema !
	//--
	if(!is_array($y_arr_data)) {
		$y_arr_data = array();
	} //end if else
	//--
	$y_display_graph2 = (bool) $y_display_graph2;
	$y_display_graph_depths = (bool) $y_display_graph_depths;
	$this->x = $this->y = $this->z = $this->w = $this->v = array();
	//--
	for($i=0; $i<count($y_arr_data); $i++) {
		//--
		$tmp_arr = (array) $y_arr_data[$i];
		//--
		$this->x[$i] = (string) $tmp_arr['x']; // label
		$this->y[$i] = (float) $tmp_arr['y']; // 1st series
		if($y_display_graph2 !== false) {
			$this->z[$i] = (float) $tmp_arr['z']; // 2nd series
		} //end if
		if($y_display_graph_depths !== false) {
			$this->w[$i] = (int) $tmp_arr['w']; // buble size
		} //end if
		$this->v[$i] = (string) $tmp_arr['v']; // custom color
		//--
	} //end for
	//--
} //END FUNCTION
//======================================================================


//=====================================================================
public function mime_header() {
	//--
	return (string) 'image/'.$this->format;
	//--
} //END FUNCTION
//=====================================================================


//=====================================================================
public function disposition_header($y_filename='gfx-chart') {
	//--
	return (string) 'inline; filename="'.Smart::safe_validname($y_filename.'-'.$this->type.'-'.time().'.'.$this->format).'"';
	//--
} //END FUNCTION
//=====================================================================


//======================================================================
public function generate($y_mode='raw') {

	//--
	$this->color 			= array();
	$this->biggest_x 		= NULL;
	$this->biggest_y 		= NULL;
	$this->exists_graph2 = false;
	$this->total_parameters = 0;
	$this->sum_total 		= 1;
	//--

	//--
	$this->legend_exists        = (bool) (preg_match('/(5|6)/', (string)$this->type)) ? true : false;
	$this->biggest_graphic_name = (string) (strlen($this->graphic_1) > strlen($this->graphic_2)) ? $this->graphic_1 : $this->graphic_2;
	$this->height_title         = (int) (!empty($this->title)) ? ($this->string_height($this->tsize) + 15) : 0;
	$this->space_between_bars   = (int) ($this->type == 1) ? 40 : 30;
	$this->space_between_dots   = 40;
	$this->higher_value         = 0;
	$this->higher_strvalue      = 0;
	//--

	//--
	$this->width               = 0;
	$this->height              = 0;
	$this->graphic_area_width  = 0;
	$this->graphic_area_height = 0;
	$this->graphic_area_x1     = 30;
	$this->graphic_area_y1     = 20 + $this->height_title;
	$this->graphic_area_x2     = $this->graphic_area_x1 + $this->graphic_area_width;
	$this->graphic_area_y2     = $this->graphic_area_y1 + $this->graphic_area_height;
	//--

	//--
	if(count($this->z) && (preg_match('/(1|2|3|4)/', (string)$this->type))) {
		$this->exists_graph2 = true;
	} //end if
	//--
	$this->total_parameters = count($this->x);
	//--
	for($i=0; $i<$this->total_parameters; $i++) {
		//--
		if(strlen($this->x[$i]) > strlen($this->biggest_x)) {
			$this->biggest_x = $this->x[$i];
		} //end if
		//--
		if($this->y[$i] > $this->biggest_y) {
			$this->biggest_y = Smart::format_number_dec(round($this->y[$i], 1), 1, ".", "");
		} //end if
		//--
		if($this->exists_graph2) {
			if(isset($this->z[$i]) && $this->z[$i] > $this->biggest_y) {
				$this->biggest_y = Smart::format_number_dec(round($this->z[$i], 1), 1, ".", "");
			} //end if
		} //end if
		//--
	} // end for
	//--

	//--
	if(($this->exists_graph2 == true) && ((!empty($this->graphic_1)) || (!empty($this->graphic_2)))) {
		$this->legend_exists = true;
	} //end if
	//--

	//--
	$this->sum_total = array_sum($this->y);
	$this->space_between_bars += ($this->exists_graph2 == true) ? 10 : 0;
	//--

	//--
	$this->calculate_higher_value();
	$this->calculate_width();
	$this->calculate_height();
	//--

	//--
	if((string)$y_mode == 'html') {
		return (string) '<img src="data:image/'.$this->format.';base64,'.Smart::escape_html(base64_encode((string)$this->draw_chart())).'">';
	} else {
		return (string) $this->draw_chart();
	} //end if else
	//--

} //END FUNCTION
//======================================================================


// ===== PRIVATES


//======================================================================
private function draw_chart() {

	//--
	$size = 3;
	//--
	$this->img = @imagecreatetruecolor($this->width, $this->height);
	//--
	$this->load_color_palette();
	//--

	//-- Fill background
	@imagefill($this->img, 0, 0, $this->color['background']);
	//@imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $this->color['background']);
	//--

	//-- Draw title
	if(!empty($this->title)) {
		$center = ($this->width / 2) - ($this->string_width($this->title, $this->tsize) / 2);
		$this->draw_img_string($this->tsize, $center, 10, $this->title, $this->color['title']);
	} //end if
	//--

	//--
	if(preg_match("/^(1|3|4)$/", (string)$this->type)) { // Draw axis and background lines for "vertical bars", "dots" and "lines"
		//--
		if($this->legend_exists == true) {
			$this->draw_legend();
		} //end if
		//--
		$higher_value_y    = $this->graphic_area_y1 + (0.1 * $this->graphic_area_height);
		$higher_value_size = 0.9 * $this->graphic_area_height;
		//--
		$less = 7 * strlen($this->higher_strvalue);
		//--
		@imageline($this->img, $this->graphic_area_x1, $higher_value_y, $this->graphic_area_x2, $higher_value_y, $this->color['bg_lines']);
		$this->draw_img_string($this->size, ($this->graphic_area_x1-$less-7), ($higher_value_y-7), $this->higher_strvalue, $this->color['axis_values']);
		//--
		for($i=1; $i<10; $i++) {
			//--
			$dec_y = $i * ($higher_value_size / 10);
			$x1 = $this->graphic_area_x1;
			$y1 = $this->graphic_area_y2 - $dec_y;
			$x2 = $this->graphic_area_x2;
			$y2 = $this->graphic_area_y2 - $dec_y;
			//--
			@imageline($this->img, $x1, $y1, $x2, $y2, $this->color['bg_lines']);
			//--
			if($i % 2 == 0) {
				$value = $this->number_preformated($this->higher_value * $i / 10, $this->axis_dec);
				$less = 7 * strlen($value);
				$this->draw_img_string($this->size, ($x1-$less-7), ($y2-7), $value, $this->color['axis_values']);
			} //end if
			//--
		} //end for
		//-- Axis X
		$this->draw_img_string($this->size, $this->graphic_area_x2+10, $this->graphic_area_y2+3, $this->axis_x, $this->color['title']);
		@imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y2, $this->graphic_area_x2, $this->graphic_area_y2, $this->color['axis_line']);
		//-- Axis Y
		$this->draw_img_string($this->size, 20, $this->graphic_area_y1-20, $this->axis_y, $this->color['title']);
		@imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y1, $this->graphic_area_x1, $this->graphic_area_y2, $this->color['axis_line']);
		//--
	} elseif($this->type == 2) { // Draw axis and background lines for "horizontal bars"
		//--
		if($this->legend_exists == true) {
			$this->draw_legend();
		} //end if
		//--
		$higher_value_x    = $this->graphic_area_x2 - (0.2 * $this->graphic_area_width);
		$higher_value_size = 0.8 * $this->graphic_area_width;
		//--
		@imageline($this->img, ($this->graphic_area_x1+$higher_value_size), $this->graphic_area_y1, ($this->graphic_area_x1+$higher_value_size), $this->graphic_area_y2, $this->color['bg_lines']);
		$this->draw_img_string($this->size, (($this->graphic_area_x1+$higher_value_size) - ($this->string_width($this->higher_value, $this->size)/2)), ($this->graphic_area_y2+2), $this->higher_strvalue, $this->color['axis_values']);
		//--
		for($i=1, $alt=15; $i<10; $i++) {
			//--
			$dec_x = Smart::format_number_dec(round($i * ($higher_value_size  / 10), 1), 1, ".", "");
			//--
			@imageline($this->img, ($this->graphic_area_x1+$dec_x), $this->graphic_area_y1, ($this->graphic_area_x1+$dec_x), $this->graphic_area_y2, $this->color['bg_lines']);
			//--
			if($i % 2 == 0) {
				$alt   = (strlen($this->biggest_y) > 4 && $alt != 15) ? 15 : 2;
				$value = $this->number_preformated($this->higher_value * $i / 10, $this->axis_dec);
				$this->draw_img_string($this->size, (($this->graphic_area_x1+$dec_x) - ($this->string_width($this->higher_value, $this->size)/2)), ($this->graphic_area_y2), $value, $this->color['axis_values'], $alt);
			} //end if
			//--
		} //end for
		//-- Axis X
		$this->draw_img_string($this->size, ($this->graphic_area_x2+10), ($this->graphic_area_y2+3), $this->axis_y, $this->color['title']);
		@imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y2, $this->graphic_area_x2, $this->graphic_area_y2, $this->color['axis_line']);
		//-- Axis Y
		$this->draw_img_string($this->size, 20, ($this->graphic_area_y1-20), $this->axis_x, $this->color['title']);
		@imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y1, $this->graphic_area_x1, $this->graphic_area_y2, $this->color['axis_line']);
		//--
	} elseif(preg_match("/^(5|6)$/", (string)$this->type)) { // Draw legend box for "pie" or "donut"
		//--
		$this->draw_legend();
		//--
	} //end if else
	//--

	//--
	if($this->type == 1) { // Draw graphic: VERTICAL BARS
		//--
		$num = 1;
		$x = $this->graphic_area_x1 + 20;
		//--
		foreach($this->x as $i => $parameter) {
			//--
			if(isset($this->z[$i])) {
				//--
				$size = round($this->z[$i] * $higher_value_size / $this->higher_value);
				$x1   = $x + 10;
				$y1   = ($this->graphic_area_y2 - $size) + 1;
				$x2   = $x1 + 20;
				$y2   = $this->graphic_area_y2 - 1;
				//--
				@imageline($this->img, ($x1+1), ($y1-1), $x2, ($y1-1), $this->color['bars_2_shadow']);
				@imageline($this->img, ($x2+1), ($y1-1), ($x2+1), $y2, $this->color['bars_2_shadow']);
				@imageline($this->img, ($x2+2), ($y1-1), ($x2+2), $y2, $this->color['bars_2_shadow']);
				@imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_2']);
				//--
			} //end if
			//--
			$size = round($this->y[$i] * $higher_value_size / $this->higher_value);
			$alt  = (($num % 2 == 0) && (strlen($this->biggest_x) > 5)) ? 15 : 2;
			$x1   = $x;
			$y1   = ($this->graphic_area_y2 - $size) + 1;
			$x2   = $x1 + 20;
			$y2   = $this->graphic_area_y2 - 1;
			$x   += $this->space_between_bars;
			//--
			$num++;
			//--
			@imageline($this->img, ($x1+1), ($y1-1), $x2, ($y1-1), $this->color['bars_shadow']);
			@imageline($this->img, ($x2+1), ($y1-1), ($x2+1), $y2, $this->color['bars_shadow']);
			@imageline($this->img, ($x2+2), ($y1-1), ($x2+2), $y2, $this->color['bars_shadow']);
			@imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars']);
			//--
			$this->draw_img_string($this->size, ((($x1+$x2)/2) - (strlen($parameter)*7/2)), ($y2+2), $parameter, $this->color['axis_values'], $alt);
			//--
		} //end foreach
		//--
	} elseif($this->type == 2) { // Draw graphic: HORIZONTAL BARS
		//--
		$y = 10;
		//--
		foreach($this->x as $i => $parameter) {
			//--
			if(isset($this->z[$i])) {
				//--
				$size = round($this->z[$i] * $higher_value_size / $this->higher_value);
				//--
				$x1   = $this->graphic_area_x1 + 1;
				$y1   = $this->graphic_area_y1 + $y + 10;
				$x2   = $x1 + $size;
				$y2   = $y1 + 15;
				//--
				@imageline($this->img, ($x1), ($y2+1), $x2, ($y2+1), $this->color['bars_2_shadow']);
				@imageline($this->img, ($x1), ($y2+2), $x2, ($y2+2), $this->color['bars_2_shadow']);
				@imageline($this->img, ($x2+1), ($y1+1), ($x2+1), ($y2+2), $this->color['bars_2_shadow']);
				@imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_2']);
				//--
				$this->draw_img_string($this->size, ($x2+7), ($y1+7), $this->z[$i], $this->color['bars_2_shadow']);
				//--
			} //end if
			//--
			$size = round(($this->y[$i] / $this->higher_value) * $higher_value_size);
			$x1   = $this->graphic_area_x1 + 1;
			$y1   = $this->graphic_area_y1 + $y;
			$x2   = $x1 + $size;
			$y2   = $y1 + 15;
			$y   += $this->space_between_bars;
			//--
			@imageline($this->img, ($x1), ($y2+1), $x2, ($y2+1), $this->color['bars_shadow']);
			@imageline($this->img, ($x1), ($y2+2), $x2, ($y2+2), $this->color['bars_shadow']);
			@imageline($this->img, ($x2+1), ($y1+1), ($x2+1), ($y2+2), $this->color['bars_shadow']);
			@imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars']);
			//--
			$this->draw_img_string($this->size, ($x2+7), ($y1+2), $this->y[$i], $this->color['bars_shadow']);
			//--
			$this->draw_img_string($this->size, ($x1 - ((strlen($parameter)*7)+7)), ($y1+2), $parameter, $this->color['axis_values']);
			//--
		} //end foreach
		//--
	} elseif(preg_match("/^(3|4)$/", (string)$this->type)) { // Draw graphic: DOTS or LINE
		//--
		$x[0] = $this->graphic_area_x1+1;
		//--
		foreach($this->x as $i => $parameter) {
			//--
			if($this->exists_graph2 == true) {
				//--
				$size  = round($this->z[$i] * $higher_value_size / $this->higher_value);
				$z[$i] = $this->graphic_area_y2 - $size;
				//--
			} //end if
			//--
			$alt   = (($i % 2 == 0) && (strlen($this->biggest_x) > 5)) ? 15 : 2;
			$size  = round($this->y[$i] * $higher_value_size / $this->higher_value);
			$y[$i] = $this->graphic_area_y2 - $size;
			//--
			if($i != 0) {
				@imageline($this->img, $x[$i], ($this->graphic_area_y1+10), $x[$i], ($this->graphic_area_y2-1), $this->color['bg_lines']);
			} //end if
			//--
			$this->draw_img_string($this->size, ($x[$i] - (strlen($parameter)*7/2 )), ($this->graphic_area_y2+2), $parameter, $this->color['axis_values'], $alt);
			//--
			$x[$i+1] = $x[$i] + 40;
			//--
		} //end foreach
		//--
		//foreach($x as $i => $value_x) {
		for($i=0; $i<(count($x)-1); $i++) {
			//--
			if(count($y) > 1) {
				//--
				if(strlen($this->v[$i]) > 0) {
					$tmp_color = $this->color_alocate($this->v[$i]);
				} else {
					$tmp_color = $this->color['line'];
				} //end if else
				//--
				if(isset($y[$i+1])) {
					//--
					if($this->type == 4) { // Draw lines
						//--
						if(isset($z[$i+1])) {
							//--
							if($this->exists_graph2 == true) {
								@imageline($this->img, $x[$i], $z[$i], $x[$i+1], $z[$i+1], $this->color['line_2']);
								@imageline($this->img, $x[$i], ($z[$i]+1), $x[$i+1], ($z[$i+1]+1), $this->color['line_2']);
							} //end if
							//--
						} //end if
						//--
						@imageline($this->img, $x[$i], $y[$i], $x[$i+1], $y[$i+1], $this->color['line']);
						@imageline($this->img, $x[$i], ($y[$i]+1), $x[$i+1], ($y[$i+1]+1), $this->color['line']);
						//--
					} //end if
					//--
				} //end if else
				//--
				if($this->exists_graph2 == true) {
					@imagefilledrectangle($this->img, $x[$i]-3, $z[$i]-3, $x[$i]+4, $z[$i]+4, $this->color['line_2']);
					if($this->type != 4) {
						if($this->w[$i] > 0) {
							@imagefilledellipse($this->img, $x[$i]-1, $z[$i]-1, ($this->w[$i] + 7), ($this->w[$i] + 7), $this->color['line_2']);
						} //end if
					} //end if
				} //end if
				//--
				imagefilledrectangle($this->img, $x[$i]-3, $y[$i]-3, $x[$i]+4, $y[$i]+4, $tmp_color);
				if($this->type != 4) {
					if($this->w[$i] > 0) {
						@imagefilledellipse($this->img, $x[$i]-1, $y[$i]-1, ($this->w[$i] + 7), ($this->w[$i] + 7), $tmp_color);
					} //end if
				} //end if
				//--
			} //end if
			//--
		} //end for
		//--
	} elseif(preg_match("/^(5|6)$/", (string)$this->type)) { // Draw graphic: PIE or DONUT
		//--
		$center_x = ($this->graphic_area_x1 + $this->graphic_area_x2) / 2;
		$center_y = ($this->graphic_area_y1 + $this->graphic_area_y2) / 2;
		$width    = $this->graphic_area_width;
		$height   = $this->graphic_area_height;
		$start    = 0;
		$sizes    = array();
		//--
		foreach($this->x as $i => $parameter) {
			//--
			$size    = $this->y[$i] * 360 / $this->sum_total;
			$sizes[] = $size;
			$start  += $size;
			//--
		} //end foreach
		//--
		$start = 270;
		//--
		if($this->type == 5) { // Draw PIE
			//-- Draw shadow
			foreach($sizes as $i => $size) {
				//--
				$num_color = $i + 1;
				//--
				while($num_color > 7) {
					//--
					$num_color -= 5;
				} //end while
				//--
				$color = 'arc_' . $num_color . '_shadow';
				//--
				for($i = $this->h3d; $i >= 0; $i--) {
					//--
					//@imagearc($this->img, $center_x, ($center_y+$i), $width, $height, $start, ($start+$size), $this->color[$color]);
					@imagefilledarc($this->img, $center_x, ($center_y+$i), $width, $height, $start, ($start+$size), $this->color[$color], IMG_ARC_NOFILL);
				} //end for
				//--
				$start += $size;
				//--
			} //end foreach
			//--
			$start = 270;
			//--
			foreach($sizes as $i => $size) { // Draw pieces
				//--
				$num_color = $i + 1;
				//--
				while($num_color > 7) {
					$num_color -= 5;
				} //end while
				//--
				$color = 'arc_' . $num_color;
				//--
				@imagefilledarc($this->img, $center_x, $center_y, ($width+2), ($height+2), $start, ($start+$size), $this->color[$color], IMG_ARC_EDGED);
				//--
				$start += $size;
				//--
			} //end foreach
			//--
		} elseif($this->type == 6) { // Draw DONUT
			//--
			foreach($sizes as $i => $size) {
				//--
				$num_color = $i + 1;
				//--
				while($num_color > 7) {
					$num_color -= 5;
				} //end while
				//--
				$color        = 'arc_' . $num_color;
				$color_shadow = 'arc_' . $num_color . '_shadow';
				//--
				@imagefilledarc($this->img, $center_x, $center_y, $width, $height, $start, ($start+$size), $this->color[$color], IMG_ARC_PIE);
				//--
				$start += $size;
				//--
			} //end foreach
			//--
			@imagefilledarc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['background'], IMG_ARC_PIE);
			@imagearc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['bg_legend']);
			@imagearc($this->img, $center_x, $center_y, ($width+1), ($height+1), 0, 360, $this->color['bg_legend']);
			//--
		} //end if else
		//--
	} //end if else
	//--

	//--
	$this->draw_credits();
	//--

	//--
	ob_start();
	//--
	if((string)$this->format == 'gif') { // gif
		@imagegif($this->img);
	} else { // png
		@imagepng($this->img); // charts are speed oriented ! default compression of zlib is 6 ; if there is a need for more optimized images as captcha have to use gif !
	} //end if else
	//--
	$out = ob_get_contents();
	//--
	ob_end_clean();
	//--
	@imagedestroy($this->img);
	$this->img = null;
	//--

	//--
	return (string) $out;
	//--

} //END FUNCTION
//======================================================================


//======================================================================
private function draw_credits() {
	//--
	$copyrightext = '(c) SmartFramework PHP GfxCharts';
	//--
	$this->draw_img_string(1, (($this->width / 2) - ((@imagefontwidth(1) * strlen($copyrightext)) / 2)), ($this->height - 10), $copyrightext, $this->color['(c)']);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function draw_legend() {
	//--
	$x1 = $this->legend_box_x1;
	$y1 = $this->legend_box_y1;
	$x2 = $this->legend_box_x2;
	$y2 = $this->legend_box_y2;
	//--
	@imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bg_legend']);
	//--
	$x = $x1 + 5;
	$y = $y1 + 5;
	//--
	if(preg_match("/^(1|2|3|4)$/", (string)$this->type)) { // Draw legend values for VERTICAL BARS, HORIZONTAL BARS, DOTS and LINES
		//--
		$color_1 = (preg_match("/^(1|2)$/", (string)$this->type)) ? $this->color['bars']   : $this->color['line'];
		$color_2 = (preg_match("/^(1|2)$/", (string)$this->type)) ? $this->color['bars_2'] : $this->color['line_2'];
		//--
		imagefilledrectangle($this->img, $x, $y, ($x+10), ($y+10), $color_1);
		imagerectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color['wireframe']);
		//--
		$this->draw_img_string($this->size, ($x+15), ($y-2), $this->graphic_1, $this->color['axis_values']);
		//--
		$y += 20;
		//--
		imagefilledrectangle($this->img, $x, $y, ($x+10), ($y+10), $color_2);
		imagerectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color['wireframe']);
		//--
		$this->draw_img_string($this->size, ($x+15), ($y-2), $this->graphic_2, $this->color['axis_values']);
		//--
	} elseif(preg_match("/^(5|6)$/", (string)$this->type)) { // Draw legend values for PIE or DONUT
		//--
		if(!empty($this->axis_x)) {
			$this->draw_img_string($this->size, ((($x1+$x2)/2) - (strlen($this->axis_x)*7/2)), $y, $this->axis_x, $this->color['wireframe']);
			$y += 25;
		} //end if
		//--
		$num = 1;
		//--
		foreach($this->x as $i => $parameter) {
			//--
			while($num > 7) {
				$num -= 5;
			} //end while
			//--
			$color = 'arc_' . $num;
			//--
			$percent = Smart::format_number_dec(round(($this->y[$i] * 100 / $this->sum_total), 2), 2, ".", "") . ' %';
			$less = (strlen($percent) * 7);
			//--
			if ($num != 1) {
				imageline($this->img, ($x1+15), ($y-2), ($x2-5), ($y-2), $this->color['bg_lines']);
			} //end if
			//--
			imagefilledrectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color[$color]);
			imagerectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color['wireframe']);
			//--
			$this->draw_img_string($this->size, ($x+15), ($y-2), $parameter, $this->color['axis_values']);
			$this->draw_img_string($this->size, ($x2-$less), ($y-2), $percent, $this->color['axis_values']);
			//--
			$y += 14;
			$num++;
			//--
		} //end foreach
		//--
	} //end elseif
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function draw_img_string($size, $x, $y, $string, $col, $alt=0) {
	//--
	if($alt && strlen($string) > 12) {
		$string = substr($string, 0, 12);
	} //end if
	//--
	@imagestring($this->img, $size, $x, $y + $alt, $string, $col);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function string_width($string, $size) {
	//--
	$single_width = $size + 4;
	//--
	return (int) $single_width * strlen((string)$string);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function string_height($size) {
	//--
	if($size <= 1) {
		$height = 8;
	} elseif($size <= 3) {
		$height = 12;
	} elseif($size >= 4) {
		$height = 14;
	} //end if else
	//--
	return (int) $height;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function calculate_width() {
	//--
	switch($this->type) {
		//-- Vertical bars
		case 1:
			$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, $this->tsize) + 25) : 0;
			$this->graphic_area_width = ($this->space_between_bars * $this->total_parameters) + 30;
			$this->graphic_area_x1   += $this->string_width(($this->higher_strvalue), $this->size);
			$this->width += $this->graphic_area_x1 + 20;
			$this->width += ($this->legend_exists == true) ? 50 : ((7 * strlen($this->axis_x)) + 10);
			break;
		//-- Horizontal bars
		case 2:
			$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, $this->size) + 25) : 0;
			$this->graphic_area_width = ($this->string_width($this->higher_strvalue, $this->size) > 50) ? (5 * ($this->string_width($this->higher_strvalue, $this->size)) * 0.85) : 200;
			$this->graphic_area_x1 += 7 * strlen($this->biggest_x);
			$this->width += ($this->legend_exists == true) ? 60 : ((7 * strlen($this->axis_y)) + 30);
			$this->width += $this->graphic_area_x1;
			break;
		//-- Dots
		case 3:
			$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, $this->size) + 25) : 0;
			$this->graphic_area_width = ($this->space_between_dots * $this->total_parameters) - 10;
			$this->graphic_area_x1   += $this->string_width(($this->higher_strvalue), $this->size);
			$this->width += $this->graphic_area_x1 + 20;
			$this->width += ($this->legend_exists == true) ? 40 : ((7 * strlen($this->axis_x)) + 10);
			break;
		//-- Lines
		case 4:
			$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, $this->size) + 25) : 0;
			$this->graphic_area_width = ($this->space_between_dots * $this->total_parameters) - 10;
			$this->graphic_area_x1   += $this->string_width(($this->higher_strvalue), $this->size);
			$this->width += $this->graphic_area_x1 + 20;
			$this->width += ($this->legend_exists == true) ? 40 : ((7 * strlen($this->axis_x)) + 10);
			break;
		//-- Pie
		case 5:
			$this->legend_box_width   = $this->string_width($this->biggest_x, $this->size) + 85;
			$this->graphic_area_width = 200;
			$this->width += 90;
			break;
		//-- Donut
		case 6:
			$this->legend_box_width   = $this->string_width($this->biggest_x, $this->size) + 85;
			$this->graphic_area_width = 180;
			$this->width += 90;
			break;
	} //end switch
	//--
	$this->width += $this->graphic_area_width;
	$this->width += $this->legend_box_width;
	//--
	$this->graphic_area_x2 = $this->graphic_area_x1 + $this->graphic_area_width;
	$this->legend_box_x1   = $this->graphic_area_x2 + 40;
	$this->legend_box_x2   = $this->legend_box_x1 + $this->legend_box_width;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function calculate_height() {
	//--
	switch($this->type) {
		//-- Vertical bars
		case 1:
			$this->legend_box_height   = ($this->exists_graph2 == true) ? 40 : 0;
			$this->graphic_area_height = 150;
			$this->height += 65;
			break;
		//-- Horizontal bars
		case 2:
			$this->legend_box_height   = ($this->exists_graph2 == true) ? 40 : 0;
			$this->graphic_area_height = ($this->space_between_bars * $this->total_parameters) + 10;
			$this->height += 65;
			break;
		//-- Dots
		case 3:
			$this->legend_box_height   = ($this->exists_graph2 == true) ? 40 : 0;
			$this->graphic_area_height = 150;
			$this->height += 65;
			break;
		//-- Lines
		case 4:
			$this->legend_box_height   = ($this->exists_graph2 == true) ? 40 : 0;
			$this->graphic_area_height = 150;
			$this->height += 65;
			break;
		//-- Pie
		case 5:
			$this->legend_box_height   = (!empty($this->axis_x)) ? 30 : 5;
			$this->legend_box_height  += (14 * $this->total_parameters);
			$this->graphic_area_height = 150;
			$this->height += 50;
			break;
		//-- Donut
		case 6:
			$this->legend_box_height   = (!empty($this->axis_x)) ? 30 : 5;
			$this->legend_box_height  += (14 * $this->total_parameters);
			$this->graphic_area_height = 180;
			$this->height += 50;
			break;
	} //end switch
	//--
	$this->height += $this->height_title;
	$this->height += ($this->legend_box_height > $this->graphic_area_height) ? ($this->legend_box_height - $this->graphic_area_height) : 0;
	$this->height += $this->graphic_area_height;
	//--
	$this->graphic_area_y2 = $this->graphic_area_y1 + $this->graphic_area_height;
	$this->legend_box_y1   = $this->graphic_area_y1 + 10;
	$this->legend_box_y2   = $this->legend_box_y1 + $this->legend_box_height;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function calculate_higher_value() {
	//--
	$digits = strlen(round($this->biggest_y));
	$interval = pow(10, ($digits-1));
	//--
	$this->higher_value = round(($this->biggest_y - ($this->biggest_y % $interval) + $interval), 1);
	$this->higher_strvalue = $this->number_preformated($this->higher_value, $this->axis_dec);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function load_color_palette() {
	//--
	switch($this->skin) {
		//--
		case 1: // SmartFramework
		default:
		//--
			//--
			$this->color['(c)'] 				= @imagecolorallocate($this->img, 221, 221, 221); // #DDDDDD
			$this->color['title'] 				= @imagecolorallocate($this->img,  51,  51,  51); // #333333
			$this->color['wireframe'] 			= @imagecolorallocate($this->img,  51,  51,  51); // #333333
			$this->color['background'] 			= @imagecolorallocate($this->img, 255, 255, 255); // #FFFFFF
			$this->color['axis_values'] 		= @imagecolorallocate($this->img, 119, 136, 153); // #778899
			$this->color['axis_line'] 			= @imagecolorallocate($this->img, 119, 136, 153); // #778899
			$this->color['bg_lines'] 			= @imagecolorallocate($this->img, 236, 236, 236); // #ECECEC
			$this->color['bg_legend'] 			= @imagecolorallocate($this->img, 246, 246, 246); // #F6F6F6
			//--
			if(preg_match("/^(1|2)$/", (string)$this->type)) {
				$this->color['bars'] 			= @imagecolorallocate($this->img, 102, 153,   0); // #669900
				$this->color['bars_shadow'] 	= @imagecolorallocate($this->img,  51, 102,   0); // #336600
				$this->color['bars_2'] 			= @imagecolorallocate($this->img,   0,  51, 102); // #003366
				$this->color['bars_2_shadow'] 	= @imagecolorallocate($this->img,   0,  51, 153); // #003399
			} elseif(preg_match("/^(3|4)$/", (string)$this->type)) {
				$this->color['line'] 			= @imagecolorallocate($this->img, 255, 102,   0); // #FF6600
				$this->color['line_2'] 			= @imagecolorallocate($this->img,   0,  51, 153); // #003399
			} elseif(preg_match("/^(5|6)$/", (string)$this->type)) {
				$this->color['arc_1'] 			= @imagecolorallocate($this->img, 255, 102,   0); // #FF6600
				$this->color['arc_2'] 			= @imagecolorallocate($this->img,   0,  51, 153); // #003399
				$this->color['arc_3'] 			= @imagecolorallocate($this->img, 150, 221,  76); // #96DD4C
				$this->color['arc_4'] 			= @imagecolorallocate($this->img, 255,   0,   0); // #FF0000
				$this->color['arc_5'] 			= @imagecolorallocate($this->img, 102, 153, 255); // #6699FF
				$this->color['arc_6'] 			= @imagecolorallocate($this->img, 255, 255,   0); // #FFFF00
				$this->color['arc_7'] 			= @imagecolorallocate($this->img, 153, 153, 153); // #999999
				$this->color['arc_1_shadow'] 	= @imagecolorallocate($this->img, 255, 153,   0); // #FF9900
				$this->color['arc_2_shadow'] 	= @imagecolorallocate($this->img, 102, 102, 153); // #666699
				$this->color['arc_3_shadow'] 	= @imagecolorallocate($this->img,   0, 127,   0); // #007F00
				$this->color['arc_4_shadow'] 	= @imagecolorallocate($this->img, 127,   0,   0); // #7F0000
				$this->color['arc_5_shadow'] 	= @imagecolorallocate($this->img,  85, 124, 206); // #557CCE
				$this->color['arc_6_shadow'] 	= @imagecolorallocate($this->img, 223, 223,   0); // #007F7F
				$this->color['arc_7_shadow'] 	= @imagecolorallocate($this->img, 102, 102, 102); // #666666
			} //end if else
			//--
			break;
			//--
	} //end switch
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// converts a #CCCCCC color to GD colors v.160107
private function color_alocate($y_color) {
	//-- init
	$r = 0;
	$g = 0;
	$b = 0;
	//--
	$y_color = trim((string)$y_color);
	//--
	if(preg_match('/#[0-9a-fA-F]{6}/', (string)$y_color)) {
		//--
		$r = hexdec(substr($y_color, 1, 2));
		$g = hexdec(substr($y_color, 3, 2));
		$b = hexdec(substr($y_color, 5, 2));
	} //end if
	//--
	return @imagecolorallocate($this->img, $r, $g, $b);
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function number_preformated($number, $dec_size=1) {
	//--
	return Smart::format_number_dec(round($number, $dec_size), $dec_size, '.', ',');
	//--
} //END FUNCTION
//======================================================================


//======================================================================
private function number_float($number) {
	//--
	return (float) str_replace(',', '', (string)$number);
	//--
} //END FUNCTION
//======================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
