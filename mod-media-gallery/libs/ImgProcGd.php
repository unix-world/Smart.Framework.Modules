<?php
// Class: \SmartModExtLib\MediaGallery\ImgProcGd
// Media Gallery Process Plugin: Image GD Process :: Smart.Framework Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\MediaGallery;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//==================================================================================================
//================================================================================================== START
//==================================================================================================


/**
 * Class Smart Image Process GD
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20200121
 *
 */
final class ImgProcGd {

	// ::

	private static $extensions = array('gif', 'png', 'jpg'); // sync with SmartImageGdProcess allowed extensions


//=========================================================================== [OK]
// Create Preview Image [OK: returns 0 if OK or non-zero on errors]
public static function create_preview($path_image, $path_resize_image, $resize_width, $resize_height, $quality=100, $bg_color_rgb=[0, 0, 0]) {

	//--
	$path_image = (string) $path_image;
	$path_resize_image = (string) $path_resize_image;
	//--
	$resize_width = (int) $resize_width;
	$resize_height = (int) $resize_height;
	$quality = (int) $quality;
	$bg_color_rgb = (array) $bg_color_rgb;
	//--

	//--
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$path_image);
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$path_resize_image);
	//--

	//--
	if(!is_file($path_image)) {
		return -1; // not ok, image does not exists / not a file / invalid path provided
	} //end if
	//--

	//--
	$ext_old = (string) strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$path_image));
	if(!in_array($ext_old, self::$extensions)) {
		return -2;
	} //end if
	//--

	//--
	$ext_new = (string) strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$path_resize_image));
	if((string)$ext_new == 'jpeg') {
		$ext_new = 'jpg';
	} //end if
	if(!in_array($ext_new, self::$extensions)) {
		return -3;
	} //end if
	//--

	//--
	$imgstr = (string) \SmartFileSystem::read($path_image);
	if((string)$imgstr == '') {
		return -4; // img is not readable
	} //end if
	//--

	//--
	$imgd = new \SmartImageGdProcess($imgstr, $path_image);
	//--
	$resize = $imgd->resizeImage(
		$resize_width,
		$resize_height,
		true, // crop
		0, // mode
		$bg_color_rgb
	);
	//--
	if(!$resize) {
		return -5; // $imgd->getLastMessage()
	} //end if
	//--
	if($imgd->getStatusOk() !== true) {
		return -6;
	} //end if
	//--

	//--
	$new_img = (string) $imgd->getImageData($ext_new, $quality, 9);
	if((string)$new_img == '') {
		return -7;
	} //end if
	//--

	//--
	$wr = \SmartFileSystem::write($path_resize_image, $new_img);
	if($wr !== 1) {
		return -8;
	} //end if
	//--

	//--
	return 0;
	//--


} //END FUNCTION
//===========================================================================


//=========================================================================== [OK]
// Create Resized Image [OK: returns 0 if OK or non-zero on errors]
public static function create_resized($path_image, $path_resize_image, $resize_width, $resize_height, $quality=100, $iflowerpreserve='yes', $bg_color_rgb=[0, 0, 0]) {

	//--
	$path_image = (string) $path_image;
	$path_resize_image = (string) $path_resize_image;
	//--
	$resize_width = (int) $resize_width;
	$resize_height = (int) $resize_height;
	$quality = (int) $quality;
	$iflowerpreserve = (string) $iflowerpreserve;
	$bg_color_rgb = (array) $bg_color_rgb;
	//--

	//--
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$path_image);
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$path_resize_image);
	//--

	//--
	if(!is_file($path_image)) {
		return -1; // not ok, image does not exists / not a file / invalid path provided
	} //end if
	//--

	//--
	$ext_old = (string) strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$path_image));
	if(!in_array($ext_old, self::$extensions)) {
		return -2;
	} //end if
	//--

	//--
	$ext_new = (string) strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$path_resize_image));
	if((string)$ext_new == 'jpeg') {
		$ext_new = 'jpg';
	} //end if
	if(!in_array($ext_new, self::$extensions)) {
		return -3;
	} //end if
	//--

	//--
	$imgstr = (string) \SmartFileSystem::read($path_image);
	if((string)$imgstr == '') {
		return -4; // img is not readable
	} //end if
	//--

	//--
	if((string)$iflowerpreserve == 'yes') {
		$mode = 2;
	} else {
		$mode = 3; // maybe here should be 1 to be like imagick !?
	} //end if else
	//--
	$imgd = new \SmartImageGdProcess($imgstr, $path_image);
	//--
	$resize = $imgd->resizeImage(
		$resize_width,
		$resize_height,
		false, // crop
		$mode, // mode
		$bg_color_rgb
	);
	//--
	if(!$resize) {
		return -5; // $imgd->getLastMessage()
	} //end if
	//--
	if($imgd->getStatusOk() !== true) {
		return -6;
	} //end if
	//--

	//--
	$new_img = (string) $imgd->getImageData($ext_new, $quality, 9);
	if((string)$new_img == '') {
		return -7;
	} //end if
	//--

	//--
	$wr = \SmartFileSystem::write($path_resize_image, $new_img);
	if($wr !== 1) {
		return -8;
	} //end if
	//--

	//--
	return 0;
	//--


} //END FUNCTION
//===========================================================================


//=========================================================================== [OK]
// Apply Watermark [OK: returns 0 if OK or non-zero on errors]
public static function apply_watermark($path_image, $path_wtm_image, $quality, $gravity) {

	//--
	$path_image = (string) $path_image;
	$path_wtm_image = (string) $path_wtm_image;
	//--
	$quality = (int) $quality;
	$gravity = (string) $gravity;
	//--

	//--
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$path_image);
	\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$path_wtm_image);
	//--

	//--
	if(!is_file($path_image)) {
		return -1; // not ok, image does not exists / not a file / invalid path provided
	} //end if
	//--
	if(!is_file($path_wtm_image)) {
		return -2; // not ok, watermark image does not exists / not a file / invalid path provided
	} //end if
	//--

	//--
	$ext_img = (string) strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$path_image));
	if((string)$ext_img == 'jpeg') {
		$ext_img = 'jpg';
	} //end if
	if(!in_array($ext_img, self::$extensions)) {
		return -3;
	} //end if
	//--
	$ext_wtm = (string) strtolower(\SmartFileSysUtils::extractPathFileExtension((string)$path_wtm_image));
	if(!in_array($ext_wtm, self::$extensions)) {
		return -4;
	} //end if
	//--

	//--
	$imgstr = (string) \SmartFileSystem::read($path_image);
	if((string)$imgstr == '') {
		return -5; // img is not readable
	} //end if
	//--
	$wtimgstr = (string) \SmartFileSystem::read($path_wtm_image);
	if((string)$wtimgstr == '') {
		return -6; // wtm img is not readable
	} //end if
	//--

	//--
	$imgd = new \SmartImageGdProcess($imgstr, $path_image.' #watermark# '.$path_wtm_image);
	//--
	$watermark = $imgd->applyWatermark($wtimgstr, $gravity);
	if(!$watermark) {
		return -7; // $imgd->getLastMessage()
	} //end if
	//--
	if($imgd->getStatusOk() !== true) {
		return -8;
	} //end if
	//--
	$new_img = (string) $imgd->getImageData($ext_img, $quality, 9);
	if((string)$new_img == '') {
		return -9;
	} //end if
	//--

	//--
	$wr = \SmartFileSystem::write($path_image, $new_img);
	if($wr !== 1) {
		return -10;
	} //end if
	//--

	//--
	return 0;
	//--


} //END FUNCTION
//===========================================================================


} //END CLASS


//==================================================================================================
//================================================================================================== END CLASS
//==================================================================================================


// end of php code
