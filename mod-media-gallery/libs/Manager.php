<?php
// Class: \SmartModExtLib\MediaGallery\Manager
// Media Gallery Manager :: Smart.Framework Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\MediaGallery;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Media Gallery - Process Images and Videos (Movies)
// DEPENDS:
//	* Smart::
//	* SmartUtils::
//	* SmartFileSystem::
//	* SmartFileSysUtils::
// REQUIRED CSS:
//	* mediagallery.css
//======================================================


//==================================================================================================
//================================================================================================== START CLASS
//==================================================================================================


/**
 * Class: SmartMediaGalleryManager - provides the Media Gallery manager.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	extensions: PHP GD Extension (w. TrueColor support) ; optional executables: imageMagick Utility (can replace PHP GD), FFMpeg (for movies), Pdf2HtmlEx (for PDF) ; classes: Smart, SmartUtils, SmartFileSystem
 * @version 	v.20221220
 * @package 	modules:ViewComponents
 *
 */
final class Manager { // [OK]

	// ->

//=====================================================================
//-- secure links (this is to make gallery possible from secure (protected access) folders)
public $use_secure_links;			// yes/no :: if yes will use secure (download bind to unique browser session that will expire after browser is closed and will not be accessible to other browsers ...)
public $secure_download_link;		// if secure links is turned on, then this parameter is mandatory to be set to an url that will handle the downloads like: script.php?page=controller.action-download
public $secure_download_ctrl_key;	// the page controller key (a secret non-public key that have to bind to a specific unique combination of controller context)
//-- styles
public $use_styles;					// yes/no :: use styles
//-- preview
public $preview_formvar;			// '' | the name of html form variable for checkbox
public $preview_formpict;			// '' | the html image to attach near checkbox
public $preview_description;		// if = 'no' will hide the description under previews
public $preview_watermark;			// '' | (relative path) 'path/to/watermark.gif|jpg|png' :: watermark for image previews
public $preview_place_watermark;	// '' (default is 'center') | 'northeast' | 'southwest' | 'southeast' | 'northwest'
//--
public $preview_width;				// width in pixels for creating previews
public $preview_height;				// height in pixels for creating previews (required for ffmpeg as WxH)
public $preview_quality;			// default preview quality
public $force_preview_w;			// force preview width to display in pixels
public $force_preview_h;			// force preview height to display in pixels
//-- images
public $img_width;					// '800' :: the image width (height will keep proportions to avoid distortion)
public $img_quality;				// '90' :: the preview quality
public $img_watermark;				// '' | (relative path) 'path/to/watermark.gif|jpg|png' :: watermark for images
public $img_place_watermark;		// '' (default is 'center') | 'northeast' | 'southwest' | 'southeast' | 'northwest'
//-- movies
public $mov_pw_watermark;			// '' | (relative path) 'path/to/watermark.gif|jpg|png' :: watermark for movie previews
public $mov_pw_blank;				// (relative path) 'path/to/moviepreview.gif|jpg|png' :: if FFMpeg is not available, this preview will be used
public $url_player_mov;				// movie player 'script.php?action&player_type={{{MOVIE-TYPE}}}&player_movie={{{MOVIE-FILE}}}&player_title={{{MOVIE-TITLE}}}' :: the URL to movie player
//-- extra
public $pict_reloading;				// path to reloading animated icon gif
public $pict_delete;				// path to delete icon
//-- counter for items [private]
public $gallery_show_counter;		// can be: full/yes/no
public $gallery_items;				// register the number of gallery items
//-- gallery viewer
public $gallery_img_link_attr = 'data-slimbox="slimbox" rel="nofollow"'; // can also be: 'class="swipebox"', ...
public $gallery_mov_link_attr = 'data-smart="open.modal 780 475 1" rel="nofollow"';
//--
//=====================================================================


//===================================================================== [OK]
public function __construct($y_url_player_mov='') {

	//--
	$this->use_secure_links = 'no';
	$this->secure_download_link = '';
	$this->secure_download_ctrl_key = '';
	//--
	$this->use_styles = 'yes';
	//--
	$this->pict_reloading = 'lib/framework/img/loading-spokes.svg';
	$this->pict_delete = 'modules/mod-media-gallery/views/img/delete.png';
	//--
	$this->preview_formvar = '';
	$this->preview_formpict = '<img src="'.$this->pict_delete.'" alt="[x]" title="[x]">';
	$this->preview_description = 'yes';
	//--
	$this->preview_width = '160';
	$this->preview_height = '120';
	$this->preview_quality = '85';
	$this->force_preview_w = '';
	$this->force_preview_h = '';
	//--
	$this->img_width = '800';
	$this->img_quality = '89';
	$this->preview_watermark = '';
	$this->img_watermark = '';
	//--
	$this->mov_pw_watermark = 'modules/mod-media-gallery/views/img/play.png';
	$this->mov_pw_blank = 'modules/mod-media-gallery/views/img/video.jpg';
	$this->url_player_mov = (string) $y_url_player_mov;
	//--
	$this->gallery_show_counter = 'yes';
	//--
	$this->gallery_items = 0; // init (do not change !)
	//--

} //END FUNCTION
//=====================================================================


//===================================================================== [OK]
/**
 * [PUBLIC] Draw an Image Gallery
 *
 * @param STRING $y_title									:: a title for the gallery
 * @param STRING $y_dir										:: path to scan
 * @param *OPTIONAL[yes/no] $y_process_previews_and_images	:: If = 'yes', will create previews for images and videos (movies) and will create conformed images
 * @param *OPTIONAL[yes/no] $y_remove_originals				:: If = 'yes', will remove original (images) after creating previews [$y_process_previews_and_images must be yes]
 * @param *OPTIONAL[>=0]	$y_display_limit				:: Items limit to display
 */
public function draw($y_title, $y_dir, $y_process_previews_and_images='no', $y_remove_originals='no', $y_display_limit='0') {

	//--
	$y_title = (string) $y_title;
	//--
	$y_dir = (string) $y_dir;
	//--
	$y_process_previews_and_images = (string) $y_process_previews_and_images;
	if((string)$y_process_previews_and_images != 'yes') {
		$y_process_previews_and_images = 'no';
	} //end if
	//--
	$y_display_limit = (int) \Smart::format_number_int($y_display_limit,'+');
	//--

	//--
	if((string)$this->use_secure_links == 'yes') {
		if(((string)$this->secure_download_link == '') OR ((string)$this->secure_download_ctrl_key == '')) {
			return '<h1>WARNING: Media Gallery / Secure Links Mode is turned ON but at least one of the: download link or the controller was NOT provided ...</h1>';
		} //end if
	} //end if
	//--

	//--
	if(!\SmartFileSysUtils::checkIfSafePath((string)$y_dir)) {
		return '<h1>ERROR: Invalid Folder for Media Gallery ...</h1>';
	} //end if
	//--
	$y_dir = \SmartFileSysUtils::addPathTrailingSlash((string)$y_dir);
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$y_dir);
	//--
	if(!is_dir($y_dir)) {
		return '<h1>WARNING: The Folder for Media Gallery does not exists ...</h1>';
	} //end if
	//--

	//--
	$this->gallery_items = 0;
	//--

	//-- constraint of params
	if((string)$y_process_previews_and_images != 'yes') {
		$y_remove_originals = 'no';
	} //end if
	//--
	if(strlen($this->preview_formvar) > 0) { // avoid processing if it is displayed in a form
		$y_process_previews_and_images = 'no';
		$y_remove_originals = 'no';
	} //end if
	//--

	//-- some inits ...
	$out = '';
	$arr_files = array();
	$processed = 0;
	//--

	//--
	$arr_storage = (array) (new \SmartGetFileSystem(true))->get_storage($y_dir, false, false);
	$all_mg_files = (array) $arr_storage['list-files'];
	//--

	//--
	for($i=0; $i<\Smart::array_size($all_mg_files); $i++) {
		//--
		$file = (string) $all_mg_files[$i];
		$ext = strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$file));
		//--
		if((substr($file, 0, 1) != '.') AND (strpos($file, '.#') === false) AND (strpos($file, '#.') === false)) {
			//--
			if((is_file($y_dir.$file)) AND (((string)$ext == 'jpeg') OR ((string)$ext == 'jpg') OR ((string)$ext == 'gif') OR ((string)$ext == 'png'))) {
				//--
				if(\SmartFileSysUtils::fnameVersionCheck((string)$file, 'mg-preview')) {
					//-- it is an image preview file
					if(!is_file($y_dir.\SmartFileSysUtils::fnameVersionAdd((string)$file, 'mg-image'))) {
						\SmartFileSystem::delete($y_dir.$file); // remove preview if orphan
					} //end if
					//--
				} elseif(\SmartFileSysUtils::fnameVersionCheck((string)$file, 'mg-image')) {
					//-- it is an image file
					if((string)$y_process_previews_and_images == 'yes') {
						//--
						$tmp_file = $y_dir.\SmartFileSysUtils::fnameVersionAdd((string)$file, 'mg-preview');
						//--
						if(!is_file($tmp_file)) {
							//--
							$out .= $this->img_preview_create($y_dir.$file, $tmp_file).'<br>';
							$processed += 1;
							//--
						} //end if
						//--
					} //end if
					//--
					$arr_files[] = $file;
					$this->gallery_items += 1;
					//--
				} elseif(\SmartFileSysUtils::fnameVersionCheck((string)$file, 'mg-vpreview')) {
					//-- it is a movie preview file
					if(stripos($file, '.#tmp-preview#.jpg') === false) {
						//--
						$tmp_linkback_file = \SmartFileSysUtils::extractPathFileNoExtName((string)\SmartFileSysUtils::fnameVersionClear((string)$file));
						//--
						if(!is_file($y_dir.$tmp_linkback_file)) {
							\SmartFileSystem::delete($y_dir.$file); // remove if orphan
						} //end if
						//--
					} //end if
					//--
				} else { // unprocessed image
					//--
					if((string)$y_process_previews_and_images == 'yes') {
						//--
						$tmp_file = (string) $y_dir.\SmartFileSysUtils::fnameVersionAdd((string)$file, 'mg-image');
						//--
						if(!is_file($tmp_file)) {
							//--
							if((string)$y_dir.$file != (string)$y_dir.strtolower($file)) {
								\SmartFileSystem::rename((string)$y_dir.$file, (string)$y_dir.strtolower($file)); // make sure is lowercase, to be ok for back-check since versioned is lowercase
							} //end if
							//--
							$out .= $this->img_conform_create($y_dir.$file, $tmp_file).'<br>';
							$processed += 1;
							//--
						} else {
							//--
							if((string)$y_remove_originals == 'yes') {
								//--
								\SmartFileSystem::delete((string)$y_dir.$file);
								$out .= '<table width="550" bgcolor="#FF3300"><tr><td>removing original image: \''.\Smart::escape_html($file).'\'</td></tr></table><br>';
								$processed += 1;
								//--
							} //end if
							//--
						} //end if else
						//--
					} //end if
					//--
				} //end if else
				//--
			} elseif((defined('SMART_FRAMEWORK_MEDIAGALLERY_MOV_THUMBNAILER')) AND ((string)SMART_FRAMEWORK_MEDIAGALLERY_MOV_THUMBNAILER != '') AND (is_file($y_dir.$file)) AND (((string)$ext == 'webm') OR ((string)$ext == 'ogv') OR ((string)$ext == 'mp4') OR ((string)$ext == 'mov') OR ((string)$ext == 'flv'))) { // WEBM, OGV, MP4, MOV, FLV
				//-- process preview FLV / MOV ...
				if((string)$y_process_previews_and_images == 'yes') {
					//--
					$tmp_file = (string) $y_dir.\SmartFileSysUtils::fnameVersionAdd((string)$file, 'mg-vpreview').'.jpg';
					//--
					if(!is_file($tmp_file)) {
						//--
						if((string)$y_dir.$file != (string)$y_dir.strtolower($file)) {
							\SmartFileSystem::rename((string)$y_dir.$file, (string)$y_dir.strtolower($file)); // make sure is lowercase, to be ok for back-check since versioned is lowercase
						} //end if
						//--
						$out .= $this->mov_preview_create($y_dir.strtolower($file), $tmp_file).'<br>';
						$processed += 1;
						//--
					} //end if
					//--
				} //end if
				//--
				$arr_files[] = $file;
				$this->gallery_items += 1;
				//--
			} //end if else
			//--
		} //end if
		//--
	} //end for
	//--

	//--
	$out .= '<!-- START MEDIA GALLERY -->'."\n";
	//--
	if((string)$this->use_styles == 'yes') {
		$out .= '<div id="mediagallery_box">'."\n";
	} //end if
	//--

	//--
	$out_arr = array();
	//--
	if($processed <= 0) {
		//--
		$arr_files = \Smart::array_sort($arr_files, 'natsort');
		//--
		$max_loops = \Smart::array_size($arr_files);
		if($y_display_limit > 0) {
			if($y_display_limit < $max_loops) {
				$max_loops = $y_display_limit;
			} //end if
		} //end if
		//--
		for($i=0; $i<$max_loops; $i++) {
			//--
			$tmp_the_ext = strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$arr_files[$i])); // [OK]
			//--
			if(((string)$tmp_the_ext == 'webm') OR ((string)$tmp_the_ext == 'ogv') OR ((string)$tmp_the_ext == 'mp4') OR ((string)$tmp_the_ext == 'mov') OR ((string)$tmp_the_ext == 'flv')) {
				$out_arr[] = $this->mov_draw_box($y_dir, $arr_files[$i], $tmp_the_ext);
			} else {
				$out_arr[] = $this->img_draw_box($y_dir, $arr_files[$i]);
			} //end if
			//--
		} //end for
		//--
		$out .= '<div title="'.\Smart::escape_html($this->gallery_show_counter).'">'."\n";
		//--
		if((string)$y_title != '') {
			$out .= '<div id="mediagallery_title">'.\Smart::escape_html($y_title).'</div><br>';
		} //end if
		$out .= '<div id="mediagallery_row">';
		for($i=0; $i<\Smart::array_size($out_arr); $i++) {
			$out .= '<div id="mediagallery_cell">';
			$out .= $out_arr[$i];
			$out .= '</div>'."\n";
		} //end for
		$out .= '</div>';
		//--
		$out .= '</div>'."\n";
		//--
	} //end if
	//--
	$out_arr = array();
	//--

	//--
	if((string)$this->use_styles == 'yes') {
		$out .= '</div>'."\n";
	} //end if
	//--
	$out .= '<!-- END MEDIA GALLERY -->'."\n";
	//--

	//--
	if(!\SmartEnvironment::ifDebug()) {
		if($processed > 0) {
			$out = '<img src="'.$this->pict_reloading.'" alt="[Reloading Page ...]" title="[Reloading Page ...]"><script type="text/javascript">setTimeout(function(){ self.location = self.location; }, 2000);</script>'.'<br><hr><br>'.$out;
			if(!defined('SMART_FRAMEWORK__MEDIA_GALLERY_IS_PROCESSING')) {
				define('SMART_FRAMEWORK__MEDIA_GALLERY_IS_PROCESSING', $processed); // notice that the media galery is processing
			} //end if
		} //end if
	} //end if
	//--

	//--
	return (string) $out;
	//--

} //END FUNCTION
//=====================================================================


//############### [PRIVATES] Movies


//===================================================================== [OK]
private function standardize_title($y_file_name) {
	//--
	$y_file_name = \SmartFileSysUtils::fnameVersionClear((string)$y_file_name);
	$y_file_name = strtolower(\SmartFileSysUtils::extractPathFileNoExtName((string)$y_file_name));
	//--
	return str_replace(array('_', '-', '  '), array(' ', ' ', ' '), (string)ucfirst((string)$y_file_name));
	//--
} //END FUNCTION
//=====================================================================


//===================================================================== [OK]
/**
 * [PRIVATE] Create a Preview to Images, and Apply a watermark to Center
 *
 * @param STRING $y_file		Path to File
 * @param STRING $y_newfile		New File Name
 * @return STRING				Message
 */
private function img_preview_create($y_file, $y_newfile) {
	//--
	return \SmartModExtLib\MediaGallery\ProcessImgAndMov::img_process('preview', 'no', $y_file, $y_newfile, $this->preview_quality, $this->preview_width, $this->preview_height, $this->preview_watermark, $this->preview_place_watermark);
	//--
} //END FUNCTION
//=====================================================================


//===================================================================== [OK]
/**
 * [PRIVATE] Create a Conformed Image, and Apply a watermark to Right-Bottom (SE) corner
 *
 * @param STRING $y_file		Path to File
 * @param STRING $y_newfile		New File Name
 * @return STRING				Message
 */
private function img_conform_create($y_file, $y_newfile) {
	//--
	return \SmartModExtLib\MediaGallery\ProcessImgAndMov::img_process('resize', 'yes', $y_file, $y_newfile, $this->img_quality, $this->img_width, 0, $this->img_watermark, $this->img_place_watermark);
	//--
} //END FUNCTION
//=====================================================================


//===================================================================== [OK]
/**
 * [PRIVATE] Draw a box for one Image Preview with link to Image
 *
 * @param RELATIVEPATH $y_big_img_file
 * @return HTML Code
 */
private function img_draw_box($y_dir, $y_big_img_file) {

	//--
	$description = (string) \Smart::escape_html($this->standardize_title($y_big_img_file));
	//--
	$base_preview = (string) \SmartFileSysUtils::fnameVersionAdd((string)$y_big_img_file, 'mg-preview'); // req. for deletion
	//--
	$image_preview = $y_dir.$base_preview;
	$image_big = $y_dir.$y_big_img_file;
	//--

	//--
	if((string)$this->use_secure_links == 'yes') { // OK
		$the_preview = (string) $this->secure_download_link.\SmartFrameworkRuntime::Create_Download_Link($image_preview, $this->secure_download_ctrl_key);
		$the_img = (string) $this->secure_download_link.\SmartFrameworkRuntime::Create_Download_Link($image_big, $this->secure_download_ctrl_key);
	} else {
		$the_preview = (string) $image_preview;
		$the_img = (string) $image_big;
	} //end if else
	//--

	//--
	if(strlen($this->force_preview_w) > 0) {
		$forced_dim = ' width="'.$this->force_preview_w.'"';
	} elseif(strlen($this->force_preview_h) > 0) {
		$forced_dim = ' height="'.$this->force_preview_h.'"';
	} else {
		$forced_dim = '';
	} //end if else
	//--

	//--
	$out = '';
	//--
	$out .= '<div align="center" id="mediagallery_box_item">';
	//--
	if((string)$this->preview_description == 'no') {
		$description = '';
	} //end if
	//--
	$out .= '<a '.$this->gallery_img_link_attr.' href="'.\Smart::escape_html($the_img).'" target="_blank" '.'title="'.$description.'"'.'>';
	$out .= '<img src="'.\Smart::escape_html($the_preview).'" border="0" alt="'.$description.'" title="'.$description.'"'.$forced_dim.'>';
	$out .= '</a>';
	//--
	if(strlen($this->preview_formvar) > 0) {
		$out .= '<input type="checkbox" name="'.$this->preview_formvar.'[]" value="'.\Smart::escape_html($y_big_img_file.'|'.$base_preview).'" title="'.\Smart::escape_html($y_big_img_file.'|'.$base_preview).'">'.$this->preview_formpict;
	} //end if
	//--
	if((string)$this->preview_description != 'no') {
		if(strlen($description) > 0) {
			$out .= '<div id="mediagallery_label">'.$description.'</div>';
		} //end if
	} //end if
	//--
	$out .= '</div>';
	//--

	//--
	return (string) $out ;
	//--

} //END FUNCTION
//=====================================================================


//===================================================================== [OK]
/**
 * [PRIVATE] Create a Preview to Images, and Apply a watermark to Center
 *
 * @param STRING $y_file		Path to File
 * @param STRING $y_newfile		New File Name
 * @return STRING				Message
 */
private function mov_preview_create($y_mov_file, $y_mov_img_preview) {
	//--
	return \SmartModExtLib\MediaGallery\ProcessImgAndMov::mov_pw_process($y_mov_file, $y_mov_img_preview, $this->preview_quality, $this->preview_width, $this->preview_height, $this->mov_pw_watermark, 'center', $this->mov_pw_blank);
	//--
} //END FUNCTION
//=====================================================================


//===================================================================== [OK]
private function mov_draw_box($y_dir, $y_video_file, $y_type) {

	//--
	$description = (string) \Smart::escape_html($this->standardize_title($y_video_file));
	//--

	//--
	$base_preview = (string) \SmartFileSysUtils::fnameVersionAdd((string)$y_video_file, 'mg-vpreview').'.jpg';
	$preview_file = $y_dir.$base_preview;
	$video_file = $y_dir.$y_video_file;
	//--

	//--
	if((string)$this->use_secure_links == 'yes') { // OK
		$the_preview = (string) $this->secure_download_link.\SmartFrameworkRuntime::Create_Download_Link($preview_file, $this->secure_download_ctrl_key);
		$the_video = (string) $this->secure_download_link.\SmartFrameworkRuntime::Create_Download_Link($video_file, $this->secure_download_ctrl_key);
	} else {
		$the_preview = (string) $preview_file;
		$the_video = (string) $video_file;
	} //end if else
	//--

	//--
	if(((string)$y_type == 'ogv') OR ((string)$y_type == 'webm') OR ((string)$y_type == 'mp4')) { // {{{SYNC-MOVIE-TYPE}}}
		$link = $this->url_player_mov.$the_video;
	} else { // mp4, mov, flv
		//if((string)self::get_server_current_protocol() == 'https://'){} // needs fix: the Flash player do not work with mixing http/https
		$link = $this->url_player_mov.$the_video;
	} //end if else
	//--
	$link = str_replace(array('{{{MOVIE-FILE}}}', '{{{MOVIE-TYPE}}}', '{{{MOVIE-TITLE}}}'), array(rawurlencode($the_video), rawurlencode($y_type), rawurlencode($description)), $link);
	//--

	//--
	$out = '';
	//--
	if(strlen($this->force_preview_w) > 0) {
		$forced_dim = ' width="'.$this->force_preview_w.'"';
	} elseif(strlen($this->force_preview_h) > 0) {
		$forced_dim = ' height="'.$this->force_preview_h.'"';
	} else {
		$forced_dim = '';
	} //end if else
	//--
	$out .= '<div align="center" id="mediagallery_box_item">';
	//--
	if((string)$this->preview_description == 'no') {
		$description = '';
	} //end if
	//--
	$out .= '<a '.$this->gallery_mov_link_attr.' href="'.$link.'" target="media-gallery-movie-player" '.'title="'.$description.'"'.'>';
	$out .= '<img src="'.\Smart::escape_html($the_preview).'" border="0" alt="'.$description.'" title="'.$description.'"'.$forced_dim.'>';
	$out .= '</a>';
	//--
	if(strlen($this->preview_formvar) > 0) {
		$out .= '<input type="checkbox" name="'.$this->preview_formvar.'[]" value="'.\Smart::escape_html($y_video_file.'|'.$base_preview).'" title="'.\Smart::escape_html($y_video_file.'|'.$base_preview).'">'.$this->preview_formpict;
	} //end if
	//--
	if((string)$this->preview_description != 'no') {
		if(strlen($description) > 0) {
			$out .= '<div id="mediagallery_label">'.$description.'</div>';
		} //end if
	} //end if
	//--
	$out .= '</div>';
	//--

	//--
	return (string) $out ;
	//--

} //END FUNCTION
//=====================================================================


} //END CLASS

//==================================================================================================
//================================================================================================== END CLASS
//==================================================================================================


// end of php code
