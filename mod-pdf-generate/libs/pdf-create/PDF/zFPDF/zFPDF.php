<?php

namespace PDF\zFPDF;

//-- class:
// zPdf (based on tFPDF 1.33)
// Version: 1.33.uxm
// Date:     2026-01-20
// Authors:  Radu Ovidiu I. <iradu@unix-world.org>
// Copyright (c) unix-world.org, 2026-present
// License:  GPLv3
//-- based on class:
// tFPDF (PHP), based on FPDF 1.85
// Version:  1.33
// Date:     2022-12-20
// Authors:  Ian Back <ianb@bpm1.com> ; Tycho Veltmeijer <tfpdf@tychoveltmeijer.nl> (versions 1.30+)
// Copyright (c) Ian Back, 2010
// License:  LGPL
//-- #


interface zInterfaceFPDF {

	public function Header() : void;
	public function Footer() : void;

} //END INTERFACE


class zFPDF
	implements zInterfaceFPDF {

	// PHP 8.1 or later
	// depends on PHP Extensions: MBString, Zlib, GD
	// depends on classes: PDF\zFPDF\zTTFontFile, Smart, SmartFileSysUtils

	public const VERSION = '1.33.uxm.20260120.2358';

	private const PDFVersion = '1.4'; // {{{SYNC-PDF-MIN-VERSION}}}

	//-- Page sizes
	private const PAGE_SIZES = [
		'a3'		=>	[ 841.89, 1190.55 ],
		'a4'		=>	[ 595.28,  841.89 ],
		'a5'		=>	[ 420.94,  595.28 ],
		'letter'	=>	[ 612,     792    ],
		'legal'		=>	[ 612,    1008    ],
	];

	private $uxmFontsPath; 		// Fonts Path (unixman)
	private $uxmUseFontCache; 	// if TRUE will use caching for Font Metrics, otherwise will not

	private $unifontSubset;
	private $page; 				// current page number
	private $n; 				// current object number
	private $offsets; 			// array of object offsets
	private $buffer; 			// buffer holding in-memory PDF
	private $pages; 			// array containing pages
	private $state; 			// current document state
	private $compress; 			// compression flag
	private $k; 				// scale factor (number of points in user unit)
	private $DefOrientation; 	// default orientation
	private $CurOrientation; 	// current orientation
	private $DefPageSize; 		// default page size
	private $CurPageSize; 		// current page size
	private $CurRotation; 		// current page rotation
	private $PageInfo; 			// page-related data
	private $wPt, $hPt; 		// dimensions of current page in points
	private $w, $h; 			// dimensions of current page in user unit
	private $lMargin; 			// left margin
	private $tMargin; 			// top margin
	private $rMargin; 			// right margin
	private $bMargin; 			// page break margin
	private $cMargin; 			// cell margin
	private $x, $y; 			// current position in user unit
	private $lasth; 			// height of last printed cell
	private $LineWidth; 		// line width in user unit
	private $fontpath; 			// path containing fonts
	private $CoreFonts; 		// array of core font names
	private $fonts; 			// array of used fonts
	private $FontFiles; 		// array of font files
	private $encodings; 		// array of encodings
	private $cmaps; 			// array of ToUnicode CMaps
	private $FontFamily; 		// current font family
	private $FontStyle; 		// current font style
	private $underline; 		// underlining flag
	private $CurrentFont; 		// current font info
	private $FontSizePt; 		// current font size in points
	private $FontSize; 			// current font size in user unit
	private $DrawColor; 		// commands for drawing color
	private $FillColor; 		// commands for filling color
	private $TextColor; 		// commands for text color
	private $ColorFlag; 		// indicates whether fill and text colors are different
	private $WithAlpha; 		// indicates whether alpha channel is used
	private $ws; 				// word spacing
	private $images; 			// array of used images
	private $PageLinks; 		// array of links in pages
	private $links; 			// array of internal links
	private $AutoPageBreak; 	// automatic page breaking
	private $PageBreakTrigger; 	// threshold used to trigger page breaks
	private $InHeader; 			// flag set when processing header
	private $InFooter; 			// flag set when processing footer
	private $AliasNbPages; 		// alias for total number of pages
	private $ZoomMode; 			// zoom display mode
	private $LayoutMode; 		// layout display mode
	private $metadata; 			// document properties
	private $CreationDate; 		// document creation date


	//-------- [PUBLIC METHODS]


	final public function __construct(bool $useFontCaching=false, string $orientation='P', string $unit='mm', string $size='A4', string $uxmFontsPath='modules/mod-pdf-generate/libs/pdf-create/PDF/zFPDF/') {

		//--
		if(!\class_exists('\\PDF\\zFPDF\\zTTFontFile')) {
			$this->Error('Init: zTTFontFile class is missing');
			return;
		} //end if
		//--

		//--
		$this->uxmUseFontCache = (bool) $useFontCaching;
		//--
		$uxmFontsPath = (string) \trim((string)$uxmFontsPath);
		//--
		if((string)$uxmFontsPath == '') {
			$this->Error('Init: Fonts Path is Empty');
			return;
		} //end if
		if(!!\str_starts_with((string)$uxmFontsPath, '.')) {
			$this->Error('Init: Fonts Path Must Not Start with a Dot `.`');
			return;
		} //end if
		if(!!\str_starts_with((string)$uxmFontsPath, '/')) {
			$this->Error('Init: Fonts Path Must Not Start with a Slash `/`');
			return;
		} //end if
		if(!\str_ends_with((string)$uxmFontsPath, '/')) {
			$this->Error('Init: Fonts Path Must End with a Slash `/`');
			return;
		} //end if
		//--

		//-- Initialization of properties
		$this->state = 0;
		$this->page = 0;
		$this->n = 2;
		$this->buffer = '';
		$this->pages = [];
		$this->PageInfo = [];
		$this->fonts = [];
		$this->FontFiles = [];
		$this->encodings = [];
		$this->cmaps = [];
		$this->images = [];
		$this->links = [];
		$this->lasth = 0;
		$this->FontFamily = '';
		$this->FontStyle = '';
		$this->FontSizePt = 12;
		$this->underline = false;
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->WithAlpha = false;
		$this->ws = 0;
		//--
		$this->InHeader = false;
		$this->InFooter = false;
		//--

		//-- Font path
		$this->fontpath = (string) $uxmFontsPath;
		//--

		//-- Core fonts
		$this->CoreFonts = [ 'courier', 'helvetica', 'times', 'symbol', 'zapfdingbats' ];
		//--

		//-- Scale factor
		if((string)$unit == 'pt') {
			$this->k = 1;
		} elseif((string)$unit == 'mm') {
			$this->k = 72/25.4;
		} elseif((string)$unit == 'cm') {
			$this->k = 72/2.54;
		} elseif((string)$unit == 'in') {
			$this->k = 72;
		} else {
			$this->Error('Incorrect unit: '.$unit);
			return;
		} //end if else
		//--

		//--
		$size = $this->_getpagesize((string)$size);
		$this->DefPageSize = $size;
		$this->CurPageSize = $size;
		//--

		//-- Page orientation
		$orientation = (string) \strtolower((string)$orientation);
		if(($orientation === 'p') || ($orientation === 'portrait')) {
			$this->DefOrientation = 'P';
			$this->w = $size[0];
			$this->h = $size[1];
		} elseif(($orientation === 'l') || ($orientation === 'landscape')) {
			$this->DefOrientation = 'L';
			$this->w = $size[1];
			$this->h = $size[0];
		} else {
			$this->Error('Incorrect orientation: '.$orientation);
			return;
		} //end if else
		//--
		$this->CurOrientation = $this->DefOrientation;
		$this->wPt = $this->w*$this->k;
		$this->hPt = $this->h*$this->k;
		//--

		//-- Page rotation
		$this->CurRotation = 0;
		//-- Page margins (1 cm)
		$margin = 28.35/$this->k;
		$this->SetMargins($margin,$margin);
		//-- Interior cell margin (1 mm)
		$this->cMargin = $margin/10;
		//-- Line width (0.2 mm)
		$this->LineWidth = .567/$this->k;
		//-- Automatic page break
		$this->SetAutoPageBreak(true,2*$margin);
		//-- Default display mode
		$this->SetDisplayMode('default');
		//-- Enable compression
		$this->SetCompression(true);
		//-- Metadata
		$this->metadata = [ 'Producer' => 'zFPDF '.self::VERSION ];
		//--

	} //END FUNCTION


	public function Header() : void {
		//--
		// To be implemented in the extended class ...
		//--
	} //END FUNCTION


	public function Footer() : void {
		//--
		// To be implemented in the extended class ...
		//--
	} //END FUNCTION


	final public function SetMargins(float $left, float $top, ?float $right=null) : void {
		//--
		// Set left, top and right margins
		//--
		if($right === null) {
			$right = (float) $left;
		} //end if
		//--
		$this->SetLeftMargin((float)$left);
		$this->SetTopMargin((float)$top);
		$this->SetRightMargin((float)$right);
		//--
	} //END FUNCTION


	final public function SetLeftMargin(float $margin) : void {
		//--
		// Set left margin
		//--
		if($margin < 0) {
			return;
		} //end if
		//--
		$this->lMargin = $margin;
		//--
		if(($this->page > 0) && ($this->x < $margin)) {
			$this->x = $margin;
		} //end if
		//--
	}

	final public function SetTopMargin(float $margin) : void {
		//--
		// Set top margin
		//--
		if($margin < 0) {
			return;
		} //end if
		//--
		$this->tMargin = $margin;
		//--
	} //END FUNCTION


	final public function SetRightMargin(float $margin) : void {
		//--
		// Set right margin
		//--
		if($margin < 0) {
			return;
		} //end if
		//--
		$this->rMargin = $margin;
		//--
	} //END FUNCTION


	final public function SetAutoPageBreak(bool $auto, float $margin=0) : void {
		//--
		// Set auto page break mode and triggering margin
		//--
		$this->AutoPageBreak 	= (bool) $auto;
		$this->bMargin 			= (float) $margin;
		$this->PageBreakTrigger = (float) ($this->h - $margin);
		//--
	} //END FUNCTION


	final public function SetDisplayMode(string|int $zoom, string $layout='default') : void {
		//--
		// Set display mode in viewer
		//--
		if(($zoom === 'fullpage') || ($zoom === 'fullwidth') || ($zoom === 'real') || ($zoom === 'default') || (\is_int($zoom) && ($zoom > 0))) {
			$this->ZoomMode = $zoom;
		} else {
			$this->Error('Incorrect zoom display mode: '.$zoom);
			return;
		} //end if else
		//--
		if(($layout === 'single') || ($layout === 'continuous') || ($layout === 'two') || ($layout === 'default')) {
			$this->LayoutMode = $layout;
		} else {
			$this->Error('Incorrect layout display mode: '.$layout);
			return;
		} //end if else
		//--
	} //END FUNCTION


	final public function SetCompression(bool $compress) : void {
		//--
		// Set page compression
		//--
		$this->compress = (bool) $compress;
		//--
	} //END FUNCTION


	final public function SetTitle(string $title) : void {
		//--
		// Title of document
		//--
		$title = (string) \trim((string)$title);
		if((string)$title == '') {
			return;
		} //end if
		//--
		$isUTF8 = (bool) $this->_isascii((string)$title);
		//--
		$this->metadata['Title'] = (string) ($isUTF8 ? $title : $this->_UTF8encode((string)$title));
		//--
	} //END FUNCTION


	final public function SetAuthor(string $author) : void {
		//--
		// Author of document
		//--
		$author = (string) \trim((string)$author);
		if((string)$author == '') {
			return;
		} //end if
		//--
		$isUTF8 = (bool) $this->_isascii((string)$author);
		//--
		$this->metadata['Author'] = (string) ($isUTF8 ? $author : $this->_UTF8encode((string)$author));
		//--
	} //END FUNCTION


	final public function SetSubject(string $subject) : void {
		//--
		// Subject of document
		//--
		$subject = (string) \trim((string)$subject);
		if((string)$subject == '') {
			return;
		} //end if
		//--
		$isUTF8 = (bool) $this->_isascii((string)$subject);
		//--
		$this->metadata['Subject'] = (string) ($isUTF8 ? $subject : $this->_UTF8encode((string)$subject));
		//--
	} //END FUNCTION


	final public function SetKeywords(string $keywords) : void {
		//--
		// Keywords of document
		//--
		$keywords = (string) \trim((string)$keywords);
		if((string)$keywords == '') {
			return;
		} //end if
		//--
		$isUTF8 = (bool) $this->_isascii((string)$keywords);
		//--
		$this->metadata['Keywords'] = (string) ($isUTF8 ? $keywords : $this->_UTF8encode((string)$keywords));
		//--
	} //END FUNCTION


	final public function SetCreator(string $creator) : void {
		//--
		// Creator of document
		//--
		$creator = (string) \trim((string)$creator);
		if((string)$creator == '') {
			return;
		} //end if
		//--
		$isUTF8 = (bool) $this->_isascii((string)$creator);
		//--
		$this->metadata['Creator'] = (string) ($isUTF8 ? $creator : $this->_UTF8encode((string)$creator));
		//--
	} //END FUNCTION


	final public function AliasNbPages(string $alias='{nb}') : void {
		//--
		// Define an alias for total number of pages
		//--
		$alias = (string) \trim((string)$alias);
		if((string)$alias == '') {
			return;
		} //end if
		//--
		$this->AliasNbPages = (string) $alias;
		//--
	} //END FUNCTION


	final public function Close() : void {
		//--
		// Terminate document
		//--
		if($this->state == 3) {
			return;
		} //end if
		//--
		if($this->page == 0) {
			$this->AddPage();
		} //end if
		//-- Page footer
		$this->InFooter = true;
		$this->Footer();
		$this->InFooter = false;
		//-- Close page
		$this->_endpage();
		//-- Close document
		$this->_enddoc();
		//--
	} //END FUNCTION


	final public function AddPage(string $orientation='', array|string $size='', int $rotation=0) : void {
		//--
		// Start a new page
		//--
		if($this->state == 3) {
			$this->Error('The document is closed');
			return;
		} //end if
		//--
		$family = $this->FontFamily;
		$style = $this->FontStyle.($this->underline ? 'U' : '');
		$fontsize = $this->FontSizePt;
		$lw = $this->LineWidth;
		$dc = $this->DrawColor;
		$fc = $this->FillColor;
		$tc = $this->TextColor;
		$cf = $this->ColorFlag;
		//--
		if($this->page > 0) {
			//-- Page footer
			$this->InFooter = true;
			$this->Footer();
			$this->InFooter = false;
			//-- Close page
			$this->_endpage();
			//--
		} //end if
		//-- Start new page
		$this->_beginpage($orientation, $size, $rotation);
		//-- Set line cap style to square
		$this->_out('2 J');
		//-- Set line width
		$this->LineWidth = $lw;
		$this->_out((string)\sprintf('%.2F w', $lw * $this->k));
		//-- Set font
		if($family) {
			$this->SetFont($family, $style, $fontsize);
		} //end if
		//-- Set colors
		$this->DrawColor = $dc;
		if($dc != '0 G') {
			$this->_out($dc);
		} //end if
		//--
		$this->FillColor = $fc;
		if($fc != '0 g') {
			$this->_out($fc);
		} //end if
		//--
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		//-- Page header
		$this->InHeader = true;
		$this->Header();
		$this->InHeader = false;
		//-- Restore line width
		if($this->LineWidth!=$lw) {
			$this->LineWidth = $lw;
			$this->_out((string)\sprintf('%.2F w', $lw * $this->k));
		} //end if
		//-- Restore font
		if($family) {
			$this->SetFont($family,$style,$fontsize);
		} //end if
		//-- Restore colors
		if($this->DrawColor != $dc) {
			$this->DrawColor = $dc;
			$this->_out($dc);
		} //end if
		if($this->FillColor!=$fc) {
			$this->FillColor = $fc;
			$this->_out($fc);
		} //end if
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		//--
	} //END FUNCTION


	final public function PageNo() : int {
		//--
		// Get current page number
		//--
		$page = (int) \intval($this->page);
		if((int)$page < 0) {
			$page = 0;
		} //end if
		//--
		return (int) $page;
		//--
	} //END FUNCTION


	final public function SetDrawColor(int $r, int $g, int $b) : void {
		//--
		// Set color for all stroking operations
		//--
		if($r <= 0) {
			$r = 0;
		} elseif($r >= 255) {
			$r = 255;
		} //end if
		//--
		if($g <= 0) {
			$g = 0;
		} elseif($g >= 255) {
			$g = 255;
		} //end if
		//--
		if($b <= 0) {
			$b = 0;
		} elseif($b >= 255) {
			$b = 255;
		} //end if
		//--
		if(($r == 0) && ($g == 0) && ($b == 0)) {
			$this->DrawColor = (string) \sprintf('%.3F G', 0/255);
		} else {
			$this->DrawColor = (string) \sprintf('%.3F %.3F %.3F RG', $r/255, $g/255, $b/255);
		} //end if
		//--
		if($this->page > 0) {
			$this->_out((string)$this->DrawColor);
		} //end if
		//--
	}

	final public function SetFillColor(int $r, int $g, int $b) : void {
		//--
		// Set color for all filling operations
		//--
		if($r <= 0) {
			$r = 0;
		} elseif($r >= 255) {
			$r = 255;
		} //end if
		//--
		if($g <= 0) {
			$g = 0;
		} elseif($g >= 255) {
			$g = 255;
		} //end if
		//--
		if($b <= 0) {
			$b = 0;
		} elseif($b >= 255) {
			$b = 255;
		} //end if
		//--
		if(($r == 0) && ($g == 0) && ($b == 0)) {
			$this->FillColor = (string) \sprintf('%.3F g', 0/255);
		} else {
			$this->FillColor = (string) \sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
		} //end if else
		//--
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		//--
		if($this->page>0) {
			$this->_out((string)$this->FillColor);
		} //end if
		//--
	} //END FUNCTION


	final public function SetTextColor(int $r, int $g=0, int $b=0) : void {
		//--
		// Set color for text
		//--
		if($r <= 0) {
			$r = 0;
		} elseif($r >= 255) {
			$r = 255;
		} //end if
		//--
		if($g <= 0) {
			$g = 0;
		} elseif($g >= 255) {
			$g = 255;
		} //end if
		//--
		if($b <= 0) {
			$b = 0;
		} elseif($b >= 255) {
			$b = 255;
		} //end if
		//--
		if(($r == 0) && ($g == 0) && ($b == 0)) {
			$this->TextColor = (string) \sprintf('%.3F g', 0/255);
		} else {
			$this->TextColor = (string) \sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
		} //end if else
		//--
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		//--
	} //END FUNCTION


	final public function GetStringWidth(string $s) : float {
		//--
		// Get width of a string in the current font
		//--
		$cw = $this->CurrentFont['cw'];
		//--
		$w = 0;
		//--
		if($this->unifontSubset) {
			//--
			$unicode = $this->UTF8StringToArray((string)$s);
			//--
			foreach($unicode as $char) {
				if(isset($cw[2*$char])) {
					$w += (\ord($cw[2*$char])<<8) + \ord($cw[2*$char+1]);
				} elseif(($char > 0) && ($char < 128) && isset($cw[\chr($char)])) {
					$w += $cw[\chr($char)];
				} elseif(isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif(isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				} //end if else
			} //end foreach
			//--
		} else {
			//--
			$l = (int) \strlen((string)$s);
			//--
			for($i=0; $i<$l; $i++) {
				$w += $cw[$s[$i]];
			} //end for
			//--
		} //end if else
		//--
		return (float) ($w * $this->FontSize / 1000);
		//--
	} //END FUNCTION


	final public function SetLineWidth(float $width) : void {
		//--
		// Set line width
		//--
		$this->LineWidth = (float) $width;
		//--
		if($this->page > 0) {
			$this->_out((string)\sprintf('%.2F w', $width * $this->k));
		} //end if
		//--
	} //END FUNCTION


	final public function Line(float $x1, float $y1, float $x2, float $y2) : void {
		//--
		// Draw a line
		//--
		$this->_out((string)\sprintf('%.2F %.2F m %.2F %.2F l S', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k));
		//--
	} //END FUNCTION


	final public function Rect(float $x, float $y, float $w, float $h, string $style='') : void {
		//--
		// Draw a rectangle
		//--
		if((string)$style == 'F') {
			$op = 'f';
		} elseif(((string)$style == 'FD') || ((string)$style == 'DF')) {
			$op = 'B';
		} else {
			$op = 'S';
		} //end if else
		//--
		$this->_out((string)\sprintf('%.2F %.2F %.2F %.2F re %s', $x * $this->k, ($this->h - $y) * $this->k, $w * $this->k, -1 * ($h * $this->k), $op));
		//--
	} //END FUNCTION


	final public function AddFont(string $family, string $style='', string $file='') : void {
		//--
		// Add a TrueType font
		//--
		$family = (string) \strtolower((string)\trim((string)$family));
		$style  = (string) \strtoupper((string)\trim((string)$style));
		$file   = (string) \trim((string)$file);
		//--
		if((string)$family == '') {
			$this->Error('AddFont: Font Family is Empty');
			return;
		} //end if
		//--
		// $style can be empty !
		//--
		if((string)$file == '') {
			$this->Error('AddFont: Font File is Empty');
			return;
		} //end if
		//--
		if((string)$style == 'IB') {
			$style = 'BI';
		} //end if
		//--
		$fontkey = (string) $family.$style;
		if(isset($this->fonts[$fontkey])) {
			return;
		} //end if
		//--
		if(\str_ends_with((string)$file, '.ttf') !== true) {
			$this->Error('Failed: Font File Name have no TTF extension ...');
			return;
		} //end if
		//--
		$uxmPosDot = \strpos((string)$file, '.');
		if($uxmPosDot === false) {
			$this->Error('Failed: Font File Name have no Dot extension ...');
			return;
		} //end if
		//--
		$ttffilename = (string) $this->fontpath.'unifont/'.$file;
		$unifilename = (string) $this->fontpath.'unifont/'.\strtolower((string)\substr((string)$file, 0, ((int)$uxmPosDot)));
		//--
		$ttfstat = \stat((string)$ttffilename);
		if(!\is_array($ttfstat)) {
			$this->Error('Failed to Stat Font: '.$ttffilename);
			return;
		} //end if
		//--
		$name = '';
		$originalsize = (int) \intval($ttfstat['size'] ?? null);
		$ttffile = (string) $ttffilename;
		$type = 'TTF';
		//-- unixman
		$uxmCacheFName = (string) 'tmp/pdfFontCache-'.\SmartHashCrypto::sha224((string)$ttffile.'#'.$originalsize.'@'.self::VERSION).'.json';
		if(($this->uxmUseFontCache === true) AND is_file((string)$uxmCacheFName)) {
			//--
			$uxmJsonCache = (string) \SmartFileSysUtils::readStaticFile((string)$uxmCacheFName);
			$uxmJsonCache = \Smart::json_decode((string)$uxmJsonCache);
			if(!\is_array($uxmJsonCache)) {
				$uxmJsonCache = [];
			} //end if
			//--
			$cw 	= $uxmJsonCache['cw'] ?? null;
			$name 	= $uxmJsonCache['name'] ?? null;
			$desc 	= (array) \Smart::array_init_keys(($uxmJsonCache['desc'] ?? null), [ // {{{SYNC-PDF-DESC-ARR-KEYS}}}
				'Ascent',
				'Descent',
				'CapHeight',
				'Flags',
				'FontBBox',
				'ItalicAngle',
				'StemV',
				'MissingWidth',
			]);
			$up 	= $uxmJsonCache['up'] ?? null;
			$ut 	= $uxmJsonCache['ut'] ?? null;
			$cw 	= (string) \Smart::b64_dec((string)($uxmJsonCache['cw'] ?? null), true);
			//--
		} else {
			//--
			$ttf = new \PDF\zFPDF\zTTFontFile();
			try {
				$ttf->getMetrics((string)$ttffile);
			} catch(\Exception $e) {
				$this->Error('Get Font Metrics Failed: '.$e->getMessage());
				return;
			} //end try catch
			//--
			$name = (string) \preg_replace('/[ ()]/', '', (string)$ttf->fullName);
			$desc = [ // {{{SYNC-PDF-DESC-ARR-KEYS}}}
				'Ascent'		=> \round($ttf->ascent),
				'Descent'		=> \round($ttf->descent),
				'CapHeight'		=> \round($ttf->capHeight),
				'Flags'			=> $ttf->flags,
				'FontBBox'		=> '['.\round($ttf->bbox[0]).' '.\round($ttf->bbox[1]).' '.\round($ttf->bbox[2]).' '.\round($ttf->bbox[3]).']',
				'ItalicAngle'	=> $ttf->italicAngle,
				'StemV'			=> \round($ttf->stemV),
				'MissingWidth'	=> \round($ttf->defaultWidth),
			];
			$up = \round($ttf->underlinePosition);
			$ut = \round($ttf->underlineThickness);
			$cw = $ttf->charWidths;
			//--
			if($this->uxmUseFontCache === true) {
				$uxmJsonCache  = (string) \Smart::json_encode(
					[
						'cache-fname' 	=> $uxmCacheFName,
						'ttffile' 		=> $ttffile,
						'originalsize' 	=> $originalsize,
						'name' 			=> $name,
						'type' 			=> $type,
						'desc' 			=> $desc,
						'up' 			=> $up,
						'ut' 			=> $ut,
						'fontkey' 		=> $fontkey,
						'cw' 			=> \Smart::b64_enc((string)$cw),
					],
					true, // pretty
					false, // escape also unicode
					false // no html safe
				);
				\SmartFileSysUtils::writeStaticFile((string)$uxmCacheFName, (string)$uxmJsonCache);
			} //end if
			//--
		} //end if else
		//--
		$i = (int) \count($this->fonts) + 1;
		if(!empty($this->AliasNbPages)) {
			$sbarr = (array) \range(0, 57);
		} else {
			$sbarr = (array) \range(0, 32);
		} //end if else
		//--
		$this->fonts[$fontkey] = [
			'i'				=> $i,
			'type'			=> $type,
			'name'			=> $name,
			'desc'			=> $desc,
			'up'			=> $up,
			'ut'			=> $ut,
			'cw'			=> $cw,
			'ttffile'		=> $ttffile,
			'fontkey'		=> $fontkey,
			'subset'		=> $sbarr,
			'unifilename'	=> $unifilename,
		];
		//--
		$cw = null; // free mem
		//--
		$this->FontFiles[$fontkey] 	= [
			'length1' 	=> $originalsize,
			'type' 		=> $type,
			'ttffile' 	=> $ttffile
		];
		//--
		$this->FontFiles[$file]	= [ 'type' => $type ];
		//--
	} //END FUNCTION


	final public function SetFontStyle(string $style, float $size=0) : void {
		//--
		$this->SetFont('', (string)$style, (float)$size);
		//--
	} //END FUNCTION


	final public function SetFont(string $family, string $style='', float $size=0) : void {
		//--
		// Select a font; size given in points
		//--
		if($family == '') {
			$family = (string) $this->FontFamily;
		} else {
			$family = (string) \strtolower((string)$family);
		} //end if
		//--
		$style = (string) \strtoupper((string)$style);
		//--
		if(\strpos((string)$style, 'U') !== false) {
			$this->underline = true;
			$style = (string) \str_replace('U', '', (string)$style);
		} else {
			$this->underline = false;
		} //end if else
		//--
		if((string)$style == 'IB') {
			$style = 'BI';
		} //end if
		//--
		if($size <= 0) {
			$size = $this->FontSizePt;
		} //end if
		//-- test if font is already selected
		if(($this->FontFamily == $family) && ($this->FontStyle == $style) && ($this->FontSizePt == $size)) {
			return;
		} //end if
		//-- test if font is already loaded
		$fontkey = (string) $family.$style;
		if(!isset($this->fonts[$fontkey])) { // test if one of the core fonts
			if(\in_array((string)$family, (array)$this->CoreFonts)) {
				if(((string)$family == 'symbol') || ((string)$family == 'zapfdingbats')) {
					$style = '';
				} //end if
				$fontkey = (string) $family.$style;
				if(!isset($this->fonts[$fontkey])) {
					$this->AddFont($family,$style);
				} //end if
			} else {
				$this->Error('Undefined font: '.$family.' '.$style);
				return;
			} //end if else
		} //end if
		//-- select the font
		$this->FontFamily = $family;
		$this->FontStyle = $style;
		$this->FontSizePt = $size;
		$this->FontSize = $size/$this->k;
		$this->CurrentFont = &$this->fonts[$fontkey];
		//--
		if((string)$this->fonts[$fontkey]['type'] == 'TTF') {
			$this->unifontSubset = true; // unixman: TODO: embedd entire font not a subset
		} else {
			$this->unifontSubset = false;
			$this->Error('Unsupported font (Non-TTF / Non Unicode): '.$family);
			return;
		} //end if else
		//--
		if($this->page > 0) {
			$this->_out((string)\sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
		} //end if
		//--
	} //END FUNCTION


	final public function SetFontSize(float $size) : void {
		//--
		// Set font size in points
		//--
		if($this->FontSizePt == $size) {
			return;
		} //end if
		//--
		$this->FontSizePt = $size;
		$this->FontSize = $size / $this->k;
		//--
		if($this->page > 0) {
			$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
		} //end if
		//--
	} //END FUNCTION


	final public function AddLink() : int {
		//--
		// Create a new internal link
		//--
		$n = (int) \count($this->links) + 1;
		$this->links[$n] = [ 0, 0 ];
		//--
		return (int) $n;
		//--
	} //END FUNCTION


	final public function SetLink(int $link, float $y=0, int $page=-1) : void {
		//--
		// Set destination of internal link
		//--
		if($y == -1) {
			$y = $this->y;
		} //end if
		//--
		if($page == -1) {
			$page = $this->page;
		} //end if
		//--
		$this->links[$link] = [ $page, $y ];
		//--
	} //END FUNCTION


	final public function Link(float $x, float $y, float $w, float $h, int|string $link) : void {
		//--
		// Put a link on the page
		//--
		$this->PageLinks[$this->page][] = [ $x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link ];
		//--
	} //END FUNCTION


	final public function Text(float $x, float $y, string $txt) : void {
		//--
		// Output a string
		//--
		if(!isset($this->CurrentFont)) {
			$this->Error('No font has been set');
			return;
		} //end if
		//--
		if($this->unifontSubset) {
			$txt2 = '('.$this->_escape((string)$this->UTF8ToUTF16BE((string)$txt, false)).')';
			foreach($this->UTF8StringToArray((string)$txt) as $uni) {
				$this->CurrentFont['subset'][$uni] = $uni;
			} //end foreach
		} else {
			$txt2 = '('.$this->_escape((string)$txt).')';
		} //end if else
		//--
		$s = (string) \sprintf('BT %.2F %.2F Td %s Tj ET', $x * $this->k, ($this->h - $y) * $this->k, (string)$txt2);
		if($this->underline && ((string)$txt != '')) {
			$s .= ' '.$this->_dounderline($x, $y, (string)$txt);
		} //end if
		//--
		if($this->ColorFlag) {
			$s = 'q '.$this->TextColor.' '.$s.' Q';
		} //end if
		//--
		$this->_out((string)$s);
		//--
	} //END FUNCTION


	final public function AcceptPageBreak() : bool {
		//--
		// Accept automatic page break or not
		//--
		return (bool) $this->AutoPageBreak;
		//--
	} //END FUNCTION


	final public function Cell(float $w, float $h=0, string $txt='', $border=0, int $ln=0, string $align='', bool $fill=false, int|string $link='') : void {
		//--
		// Output a cell
		//--
		$k = $this->k;
		//--
		if((($this->y + $h) > $this->PageBreakTrigger) && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) { // Automatic page break
			//--
			$x = $this->x;
			$ws = $this->ws;
			//--
			if($ws > 0) {
				$this->ws = 0;
				$this->_out('0 Tw');
			} //end if
			//--
			$this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
			//--
			$this->x = $x;
			if($ws > 0) {
				$this->ws = $ws;
				$this->_out((string)\sprintf('%.3F Tw', $ws * $k));
			} //end if
			//--
		} //end if
		//--
		if($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		} //end if
		//--
		$s = '';
		if($fill || ($border === 1)) {
			if($fill) {
				$op = ($border === 1) ? 'B' : 'f';
			} else {
				$op = 'S';
			} //end if else
			$s = (string) \sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -1 * ($h * $k), $op);
		} //end if
		//--
		if(\is_string($border)) {
			//--
			$x = $this->x;
			$y = $this->y;
			//--
			if(\strpos((string)$border, 'L') !== false) {
				$s .= (string) \sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
			} //end if
			if(\strpos((string)$border, 'T') !== false) {
				$s .= (string) \sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h-$y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
			} //end if
			if(\strpos((string)$border, 'R') !== false) {
				$s .= (string) \sprintf('%.2F %.2F m %.2F %.2F l S ',($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
			} //end if
			if(\strpos((string)$border, 'B') !== false) {
				$s .= (string) \sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
			} //end if
			//--
		} //end if
		//--
		if((string)$txt != '') {
			if(!isset($this->CurrentFont)) {
				$this->Error('No font has been set');
				return;
			} //end if
			if((string)$align == 'R') {
				$dx = $w - $this->cMargin - $this->GetStringWidth($txt);
			} elseif((string)$align=='C') {
				$dx = ($w - $this->GetStringWidth($txt)) / 2;
			} else {
				$dx = $this->cMargin;
			} //end if else
			//--
			if($this->ColorFlag) {
				$s .= 'q '.$this->TextColor.' ';
			} //end if
			//-- If multibyte, Tw has no effect - do word spacing using an adjustment before each space
			if($this->ws && $this->unifontSubset) {
				//--
				foreach($this->UTF8StringToArray($txt) as $uni) {
					$this->CurrentFont['subset'][$uni] = $uni;
				} //end foreach
				//--
				$space = (string) $this->_escape((string)$this->UTF8ToUTF16BE(' ', false));
				$s .= (string) \sprintf('BT 0 Tw %.2F %.2F Td [', ($this->x + $dx) * $k, ($this->h - ($this->y + 0.5 * $h + 0.3 * $this->FontSize)) * $k);
				$t = (array) \explode(' ', (string)$txt);
				$numt = (int) \count($t);
				for($i=0; $i<$numt; $i++) {
					$tx = $t[$i];
					$tx = '('.$this->_escape((string)$this->UTF8ToUTF16BE((string)$tx, false)).')';
					$s .= (string) \sprintf('%s ', $tx);
					if(($i+1) < $numt) {
						$adj = -1 * (($this->ws * $this->k) * 1000 / $this->FontSizePt);
						$s .= (string) \sprintf('%d(%s) ', $adj, $space);
					} //end if
				} //end for
				//--
				$s .= '] TJ';
				$s .= ' ET';
				//--
			} else {
				//--
				if($this->unifontSubset) {
					$txt2 = '('.$this->_escape((string)$this->UTF8ToUTF16BE((string)$txt, false)).')';
					foreach($this->UTF8StringToArray($txt) as $uni) {
						$this->CurrentFont['subset'][$uni] = $uni;
					} //end foreach
				} else {
					$txt2 = '('.$this->_escape($txt).')';
				} //end if else
				//--
				$s .= (string) \sprintf('BT %.2F %.2F Td %s Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + 0.5 * $h + 0.3 * $this->FontSize)) * $k, $txt2);
				//--
			} //end if else
			//--
			if($this->underline) {
				$s .= ' '.$this->_dounderline($this->x + $dx, $this->y + 0.5 * $h + 0.3 * $this->FontSize, $txt);
			} //end if
			//--
			if($this->ColorFlag) {
				$s .= ' Q';
			} //end if
			//--
			if($link) {
				$this->Link($this->x + $dx, $this->y + 0.5 * $h - 0.5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize,$link);
			} //end if
			//--
		} //end if
		//--
		if($s) {
			$this->_out((string)$s);
		} //end if
		//--
		$this->lasth = $h;
		//--
		if($ln > 0) { // Go to next line
			$this->y += $h;
			if($ln == 1) {
				$this->x = $this->lMargin;
			} //end if
		} else {
			$this->x += $w;
		} //end if
		//--
	} //END FUNCTION


	final public function MultiCell(float $w, float $h, string $txt, int|string $border=0, string $align='J', bool $fill=false) : void {
		//--
		// Output text with automatic or explicit line breaks
		//--
		if(!isset($this->CurrentFont)) {
			$this->Error('No font has been set');
			return;
		} //end if
		//--
		$cw = $this->CurrentFont['cw'];
		//--
		if($w == 0) {
			$w = $this->w-$this->rMargin-$this->x;
		} //end if
		//--
		$wmax = ($w-2*$this->cMargin);
		//$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
		//--
		$s = (string) \str_replace("\r", '', (string)$txt);
		if($this->unifontSubset) {
			//--
			$nb = (int) \mb_strlen((string)$s, 'UTF-8');
			//--
			while(($nb > 0) && ((string)\mb_substr((string)$s, $nb-1, 1, 'UTF-8') == "\n")) {
				$nb--;
			} //end while
			//--
		} else {
			//--
			$nb = (int) \strlen((string)$s);
			if(($nb > 0) && ((string)$s[$nb-1] == "\n")) {
				$nb--;
			} //end if
			//--
		} //end if else
		//--
		$b = 0;
		if($border) {
			if((\is_int($border)) && ($border === 1)) {
				//--
				$border = 'LTRB';
				$b = 'LRT';
				$b2 = 'LR';
				//--
			} elseif(\is_string($border)) {
				//--
				$b2 = '';
				//--
				if(\strpos((string)$border, 'L') !== false) {
					$b2 .= 'L';
				} //end if
				//--
				if(\strpos((string)$border, 'R') !== false) {
					$b2 .= 'R';
				} //end if
				//--
				$b = (string) ((\strpos((string)$border, 'T') !== false) ? $b2.'T' : $b2);
				//--
			} //end if else
		} //end if
		//--
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$ns = 0;
		$nl = 1;
		//--
		while($i < $nb) { // Get next character
			//--
			if($this->unifontSubset) {
				$c = (string) \mb_substr((string)$s, $i, 1, 'UTF-8');
			} else {
				$c = (string) $s[$i];
			} //end if
			//--
			if((string)$c == "\n") { // Explicit line break
				//--
				if($this->ws > 0) {
					$this->ws = 0;
					$this->_out('0 Tw');
				} //end if
				//--
				if($this->unifontSubset) {
					$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $i-$j, 'UTF-8'), $b, 2, $align, $fill);
				} else {
					$this->Cell($w, $h, (string)\substr((string)$s, $j, $i-$j),             $b, 2, $align, $fill);
				} //end if else
				//--
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				$ns = 0;
				$nl++;
				//--
				if($border && ($nl == 2)) {
					$b = $b2;
				} //end if
				//--
				continue;
				//--
			} //end if
			//--
			if((string)$c == ' ') {
				$sep = $i;
				$ls = $l;
				$ns++;
			} //end if
			//--
			if($this->unifontSubset) {
				$l += $this->GetStringWidth($c);
			} else {
				$l += $cw[$c] * $this->FontSize / 1000;
			} //end if else
			//--
			if($l > $wmax) { // Automatic line break
				if($sep == -1) {
					//--
					if($i == $j) {
						$i++;
					} //end if
					//--
					if($this->ws > 0) {
						$this->ws = 0;
						$this->_out('0 Tw');
					} //end if
					//--
					if($this->unifontSubset) {
						$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $i-$j, 'UTF-8'), $b, 2, $align, $fill);
					} else {
						$this->Cell($w, $h, (string)\substr((string)$s, $j, $i-$j),             $b, 2, $align, $fill);
					} //end if else
					//--
				} else {
					//--
					if((string)$align == 'J') {
						$this->ws = ($ns>1) ? ($wmax-$ls) / ($ns-1) : 0;
						$this->_out((string)\sprintf('%.3F Tw', $this->ws * $this->k));
					} //end if
					//--
					if($this->unifontSubset) {
						$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $sep-$j, 'UTF-8'), $b, 2, $align, $fill);
					} else {
						$this->Cell($w, $h, (string)\substr((string)$s, $j, $sep-$j), $b, 2, $align, $fill);
					} //end if else
					//--
					$i = $sep + 1;
					//--
				} //end if else
				//--
				$sep = -1;
				$j = $i;
				$l = 0;
				$ns = 0;
				$nl++;
				//--
				if($border && ($nl == 2)) {
					$b = $b2;
				} //end if
				//--
			} else {
				//--
				$i++;
				//--
			} //end if else
			//--
		} //end while
		//--
		if($this->ws > 0) { // Last chunk
			$this->ws = 0;
			$this->_out('0 Tw');
		} //end if
		//--
		if($border && \is_string($border) && (\strpos((string)$border,'B') !== false)) {
			$b .= 'B';
		} //end if
		//--
		if($this->unifontSubset) {
			$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $i-$j, 'UTF-8'), $b, 2, $align, $fill);
		} else {
			$this->Cell($w, $h, (string)\substr((string)$s, $j, $i-$j),             $b, 2, $align, $fill);
		} //end if else
		//--
		$this->x = $this->lMargin;
		//--
	} // END FUNCTION


	final public function Write(float $h, string $txt, int|string $link='') {
		//--
		// Output text in flowing mode
		//--
		if(!isset($this->CurrentFont)) {
			$this->Error('No font has been set');
			return;
		} //end if
		//--
		$cw = $this->CurrentFont['cw'];
		$w = $this->w-$this->rMargin-$this->x;
		$wmax = ($w-2*$this->cMargin);
		$s = (string) \str_replace("\r", '', (string)$txt);
		//--
		if($this->unifontSubset) {
			$nb = (int) \mb_strlen((string)$s, 'UTF-8');
			if(($nb == 1) && ($s == ' ')) {
				$this->x += $this->GetStringWidth($s);
				return;
			} //end if
		} else {
			$nb = (int) \strlen((string)$s);
		} //end if else
		//--
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;
		//--
		while($i < $nb) { // Get next character
			//--
			if($this->unifontSubset) {
				$c = (string) \mb_substr($s, $i, 1, 'UTF-8');
			} else {
				$c = (string) $s[$i];
			} //end if else
			//--
			if((string)$c == "\n") { // Explicit line break
				//--
				if($this->unifontSubset) {
					$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $i-$j, 'UTF-8'), 0, 2, '', false, $link);
				} else {
					$this->Cell($w, $h, (string)\substr((string)$s, $j, $i-$j),             0, 2, '', false, $link);
				} //end if else
				//--
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				//--
				if($nl==1) {
					$this->x = $this->lMargin;
					$w = $this->w-$this->rMargin-$this->x;
					$wmax = ($w-2*$this->cMargin);
				} //end if
				//--
				$nl++;
				//--
				continue;
				//--
			} //end if
			//--
			if((string)$c == ' ') {
				$sep = $i;
			} //end if
			//--
			if($this->unifontSubset) {
				$l += $this->GetStringWidth($c);
			} else {
				$l += $cw[$c] * $this->FontSize / 1000;
			} //end if else
			//--
			if($l > $wmax) { // Automatic line break
				//--
				if($sep == -1) {
					//--
					if($this->x > $this->lMargin) { // Move to next line
						//--
						$this->x = $this->lMargin;
						$this->y += $h;
						$w = $this->w-$this->rMargin-$this->x;
						$wmax = ($w-2*$this->cMargin);
						$i++;
						$nl++;
						//--
						continue;
						//--
					} //end if
					//--
					if($i == $j) {
						$i++;
					} //end if
					//--
					if($this->unifontSubset) {
						$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $i-$j, 'UTF-8'), 0, 2, '', false, $link);
					} else {
						$this->Cell($w, $h, (string)\substr((string)$s, $j, $i-$j),             0, 2, '', false, $link);
					} //end if else
					//--
				} else {
					//--
					if($this->unifontSubset) {
						$this->Cell($w, $h, (string)\mb_substr((string)$s, $j, $sep-$j, 'UTF-8'), 0, 2, '', false, $link);
					} else {
						$this->Cell($w, $h, (string)\substr((string)$s, $j, $sep-$j),             0, 2, '', false, $link);
					} //end if else
					//--
					$i = $sep + 1;
					//--
				} //end if else
				//--
				$sep = -1;
				$j = $i;
				$l = 0;
				//--
				if($nl == 1) {
					$this->x = $this->lMargin;
					$w = $this->w-$this->rMargin-$this->x;
					$wmax = ($w-2*$this->cMargin);
				} //end if
				//--
				$nl++;
				//--
			} else {
				//--
				$i++;
				//--
			} //end if else
			//--
		} //end while
		//--
		if($i != $j) { // Last chunk
			if($this->unifontSubset) {
				$this->Cell($l, $h, (string)\mb_substr((string)$s, $j, $i-$j, 'UTF-8'), 0, 0, '', false, $link);
			} else {
				$this->Cell($l, $h, (string)\substr((string)$s, $j),                    0, 0, '', false, $link);
			} //end if
		} //end if
		//--
	} //END FUNCTION


	final public function Ln(?float $h=null) : void {
		//--
		// Line feed ; default value is the last cell height
		//--
		$this->x = $this->lMargin;
		if($h === null) {
			$this->y += $this->lasth;
		} else {
			$this->y += $h;
		} //end if else
		//--
	} //END FUNCTION


	final public function Image(string $file, ?float $x=null, ?float $y=null, float $w=0, float $h=0, string $type='', int|string $link='') : void {
		//--
		// Insert an image on the page
		//--
		if((string)$file == '') {
			$this->Error('Image file name is empty');
		} //end if
		//--
		if(!isset($this->images[$file])) {
			//-- First use of this image, get info
			if((string)$type == '') {
				$pos = \strrpos((string)$file, '.'); // do not cast
				if(!$pos) {
					$this->Error('Image file has no extension and no type was specified: '.$file);
				} //end if
				$type = \substr((string)$file, $pos+1);
			} //end if
			//--
			$info = [];
			$type = (string) \strtolower((string)$type);
			if((string)$type == 'jpeg') {
				$type = 'jpg';
			} //end if
			switch((string)$type) {
				case 'png':
					$info = $this->_parsepng($file);
					break;
				case 'jpg':
					$info = $this->_parsejpg($file);
					break;
				case 'gif':
					$info = $this->_parsegif($file);
					break;
				default:
					$this->Error('Unsupported image type: '.$type.' for: '.$file);
					return;
			} //end switch
			if(!\is_array($info) OR \count($info) <= 0) {
				$this->Error('FAILED: Image type: '.$type.' for: '.$file);
				return;
			} //end if
			//--
			$info['i'] = (int) \count($this->images) + 1;
			$this->images[$file] = $info;
			//--
		} else {
			$info = $this->images[$file];
		} //end if else
		//-- Automatic width and height calculation if needed
		if(($w == 0) && ($h == 0)) { // Put image at 96 dpi
			$w = -96;
			$h = -96;
		} //end if
		if($w < 0) {
			$w = -1 * ($info['w'] * 72 / $w / $this->k);
		} //end if
		if($h < 0) {
			$h = -1 * ($info['h'] * 72 / $h / $this->k);
		} //end if
		if($w == 0) {
			$w = $h * $info['w'] / $info['h'];
		} //end if
		if($h == 0) {
			$h = $w * $info['h'] / $info['w'];
		} //end if
		//--
		if($y === null) { // Flowing mode
			if((($this->y + $h) > $this->PageBreakTrigger) && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) { // Automatic page break
				$x2 = $this->x;
				$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
				$this->x = $x2;
			} //end if
			$y = $this->y;
			$this->y += $h;
		} //end if
		//--
		if($x === null) {
			$x = $this->x;
		} //end if
		//--
		$this->_out((string)\sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
		//--
		if($link) {
			$this->Link($x, $y, $w, $h, $link);
		} //end if
		//--
	} //END FUNCTION


	final public function GetPageWidth() : float {
		//--
		// Get current page width
		//--
		return (float) $this->w;
		//--
	} //END FUNCTION


	final public function GetPageHeight() : float {
		//--
		// Get current page height
		//--
		return (float) $this->h;
		//--
	} //END FUNCTION


	final public function GetX() : float {
		//--
		// Get x position
		//--
		return (float) $this->x;
		//--
	} //END FUNCTION

	final public function SetX(float $x) : void {
		//--
		// Set x position
		//--
		if($x >= 0) {
			$this->x = $x;
		} else {
			$this->x = $this->w + $x;
		} //end if else
		//--
	} //END FUNCTION


	final public function GetY() : float {
		//--
		// Get y position
		//--
		return (float) $this->y;
		//--
	} //END FUNCTION


	final public function SetY(float $y, bool $resetX=true) : void {
		//--
		// Set y position and optionally reset x
		//--
		if($y >= 0) {
			$this->y = $y;
		} else {
			$this->y = $this->h+$y;
		} //end if
		//--
		if($resetX) {
			$this->x = $this->lMargin;
		} //end if
		//--
	} //END FUNCTION


	final public function SetXY(float $x, float $y) : void {
		//--
		// Set x and y positions
		//--
		$this->SetX($x);
		$this->SetY($y, false);
		//--
	} //END FUNCTION


	final public function Output() : string {
		//--
		// Output PDF to some destination
		//--
		$this->Close();
		//--
		return (string) $this->buffer;
		//--
	} //END FUNCTION


	//-------- [PRIVATE METHODS]


	private function Error(?string $msg) : void {
		//--
		// Fatal error
		//--
		$msg = (string) \trim((string)$msg);
		if((string)$msg == '') {
			$msg = 'Unknown Error';
		} //end if
		//--
		throw new \Exception('zFPDF error: '.$msg);
		//--
	} //END FUNCTION


	private function _getpagesize(string|array $size) : array {
		//--
		if(\is_array($size)) {
			//--
			if($size[0] > $size[1]) {
				return [ $size[1], $size[0] ];
			} //end if
			//--
			return (array) $size;
			//--
		} //end if
		//-- string
		$size = (string) \strtolower((string)$size);
		if(!isset(self::PAGE_SIZES[(string)$size])) {
			$this->Error('Unknown page size: '.$size);
			return [];
		} //end if
		//--
		$a = (array) self::PAGE_SIZES[(string)$size];
		//--
		return [ $a[0] / $this->k, $a[1] / $this->k ];
		//--
	} //END FUNCTION


	private function _beginpage(string $orientation, string|array $size, int $rotation) : void {
		//--
		$this->page++;
		$this->pages[$this->page] = '';
		$this->PageLinks[$this->page] = [];
		$this->state = 2;
		$this->x = $this->lMargin;
		$this->y = $this->tMargin;
		$this->FontFamily = '';
		//-- page orientation
		if((string)$orientation == '') {
			$orientation = (string) $this->DefOrientation;
		} else {
			$orientation = (string) \strtoupper((string)$orientation[0]);
		} //end if else
		//-- page size
		if(empty($size)) {
			$size = (array) $this->DefPageSize;
		} else {
			$size = (array)  $this->_getpagesize($size);
		} //end if
		//--
		if(($orientation != $this->CurOrientation) || ($size[0] != $this->CurPageSize[0]) || ($size[1] != $this->CurPageSize[1])) { // New size or orientation
			//--
			if((string)$orientation == 'P') {
				$this->w = $size[0];
				$this->h = $size[1];
			} else {
				$this->w = $size[1];
				$this->h = $size[0];
			} //end if else
			//--
			$this->wPt = $this->w*$this->k;
			$this->hPt = $this->h*$this->k;
			$this->PageBreakTrigger = $this->h-$this->bMargin;
			$this->CurOrientation = $orientation;
			$this->CurPageSize = $size;
			//--
		} //end if
		//--
		if(($orientation != $this->DefOrientation) || ($size[0] != $this->DefPageSize[0]) || ($size[1] != $this->DefPageSize[1])) {
			$this->PageInfo[$this->page]['size'] = [ $this->wPt, $this->hPt ];
		} //end if
		//--
		if($rotation != 0) {
			if(($rotation % 90) != 0) {
				$this->Error('Incorrect rotation value: '.$rotation);
			} //end if
			$this->PageInfo[$this->page]['rotation'] = $rotation;
		} //end if
		//--
		$this->CurRotation = $rotation;
		//--
	} //END FUNCTION


	private function _endpage() : void {
		//--
		$this->state = 1;
		//--
	} //END FUNCTION


	private function _isascii(?string $s) : bool {
		//--
		// Test if string is ASCII
		//--
		$nb = (int) \strlen((string)$s);
		//--
		for($i=0; $i<$nb; $i++) {
			if(\ord($s[$i])>127) {
				return false;
			} //end if
		} //end for
		//--
		return true;
		//--
	} //END FUNCTION


	private function _UTF8encode(?string $s) : string {
		//--
		return (string) \mb_convert_encoding((string)$s, 'UTF-8', 'ISO-8859-1'); // Convert ISO-8859-1 to UTF-8
		//--
	} //END FUNCTION


	private function _UTF8toUTF16(?string $s) : string {
		//--
		return (string) "\xFE\xFF".mb_convert_encoding((string)$s, 'UTF-16BE', 'UTF-8'); // Convert UTF-8 to UTF-16BE with BOM
		//--
	} //END FUNCTION


	private function _escape(?string $s) : string {
		//--
		// Escape special characters
		//--
		if(
			(\strpos((string)$s, '(') !== false)
			||
			(\strpos((string)$s, ')') !== false)
			||
			(\strpos((string)$s, '\\') !== false)
			||
			(\strpos((string)$s, "\r") !== false)
		) {
			return (string) \str_replace(
				[ '\\',   '(',     ')',   "\r"  ],
				[ '\\\\', '\\(',   '\\)', '\\r' ],
				(string) $s
			);
		}
		//--
		return (string) $s;
		//--
	} //END FUNCTION


	private function _textstring(?string $s) : string {
		//--
		// Format a text string
		//--
		if(!$this->_isascii((string)$s)) {
			$s = (string) $this->_UTF8toUTF16((string)$s);
		} //end if
		//--
		return (string) '('.$this->_escape((string)$s).')';
		//--
	}

	private function _dounderline(float $x, float $y, ?string $txt) : string {
		//--
		// Underline text
		//--
		$up = $this->CurrentFont['up'];
		$ut = $this->CurrentFont['ut'];
		$w = $this->GetStringWidth((string)$txt) + $this->ws * (int)\substr_count((string)$txt, ' ');
		//--
		return (string) \sprintf('%.2F %.2F %.2F %.2F re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -1 * ($ut / 1000 * $this->FontSizePt));
		//--
	} //END FUNCTION


	private function _parsejpg(string $file) : array {
		//--
		// Extract info from a JPEG file
		//--
		if(\SmartFileSysUtils::checkIfSafePath((string)$file) != 1) {
			$this->Error('Unsafe JPG image file path: '.$file);
			return [];
		} //end if
		//--
		$a = \getimagesize((string)$file);
		if(!$a) {
			$this->Error('Missing or incorrect image file: '.$file);
			return [];
		} //end if
		//--
		if($a[2] !== 2) {
			$this->Error('Not a JPEG file: '.$file);
			return [];
		} //end if
		//--
		if(!isset($a['channels']) || ($a['channels'] == 3)) {
			$colspace = 'DeviceRGB';
		} elseif($a['channels'] == 4) {
			$colspace = 'DeviceCMYK';
		} else {
			$colspace = 'DeviceGray';
		} //end if else
		//--
		$bpc = isset($a['bits']) ? $a['bits'] : 8;
		//--
		$data = (string) \file_get_contents((string)$file);
		//--
		return [ 'w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data ];
		//--
	} //END FUNCTION


	private function _parsegif(string $file) : array {
		//--
		// Extract info from a GIF file (via PNG conversion)
		//--
		if(\SmartFileSysUtils::checkIfSafePath((string)$file) != 1) {
			$this->Error('Unsafe GIF image file path: '.$file);
			return [];
		} //end if
		//--
		$im = \imagecreatefromgif((string)$file);
		if(!$im) {
			$this->Error('Missing or incorrect image file: '.$file);
			return [];
		} //end if
		//--
		\imageinterlace($im, 0);
		//--
		\ob_start();
		//--
		\imagepng($im);
		//--
		$data = \ob_get_clean();
		//--
		\imagedestroy($im);
		//--
		$f = \fopen('php://temp', 'rb+');
		if(!$f) {
			$this->Error('Unable to create memory stream');
			return [];
		} //end if
		\fwrite($f, $data);
		\rewind($f);
		//--
		$info = $this->_parsepngstream($f, (string)$file);
		//--
		\fclose($f);
		//--
		return (array) $info;
		//--
	} //END FUNCTION


	private function _parsepng(string $file) : array {
		//--
		// Extract info from a PNG file
		//--
		if(\SmartFileSysUtils::checkIfSafePath((string)$file) != 1) {
			$this->Error('Unsafe PNG image file path: '.$file);
			return [];
		} //end if
		//--
		$f = \fopen((string)$file, 'rb');
		if(!$f) {
			$this->Error('Can\'t open image file: '.$file);
			return [];
		} //end if
		//--
		$info = (array) $this->_parsepngstream($f, (string)$file);
		//--
		\fclose($f);
		//--
		return (array) $info;
		//--
	} //END FUNCTION


	private function _parsepngstream($f, string $file) : array {
		//-- verify resource
		if(!\is_resource($f) || !$f) {
			$this->Error('_parsepngstream expects a resource');
			return [];
		} //end if
		if(!$f) {
			$this->Error('_parsepngstream resource is unavailable');
			return [];
		} //end if
		//-- Check signature
		if($this->_readstream($f, 8) != \chr(137).'PNG'.\chr(13).\chr(10).\chr(26).\chr(10)) {
			$this->Error('Not a PNG file: '.$file);
			return [];
		} //end if
		//-- Read header chunk
		$this->_readstream($f, 4);
		if($this->_readstream($f, 4) != 'IHDR') {
			$this->Error('Incorrect PNG file: '.$file);
			return [];
		} //end if
		//--
		$w = $this->_readint($f);
		$h = $this->_readint($f);
		$bpc = \ord($this->_readstream($f, 1));
		if($bpc > 8) {
			$this->Error('16-bit depth not supported: '.$file);
			return [];
		} //end if
		//--
		$ct = \ord($this->_readstream($f, 1));
		if(($ct == 0) || ($ct == 4)) {
			$colspace = 'DeviceGray';
		} elseif(($ct == 2) || ($ct == 6)) {
			$colspace = 'DeviceRGB';
		} elseif($ct == 3) {
			$colspace = 'Indexed';
		} else {
			$this->Error('Unknown color type: '.$file);
			return [];
		} //end if
		//--
		if(\ord($this->_readstream($f, 1)) != 0) {
			$this->Error('Unknown compression method: '.$file);
			return [];
		} //end if
		//--
		if(\ord($this->_readstream($f, 1)) != 0) {
			$this->Error('Unknown filter method: '.$file);
			return [];
		} //end if
		//--
		if(\ord($this->_readstream($f, 1)) != 0) {
			$this->Error('Interlacing not supported: '.$file);
			return [];
		} //end if
		//--
		$this->_readstream($f,4);
		$dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;
		//-- Scan chunks looking for palette, transparency and image data
		$pal = '';
		$trns = '';
		$data = '';
		do {
			$n = $this->_readint($f);
			$type = $this->_readstream($f,4);
			if((string)$type == 'PLTE') { // Read palette
				$pal = $this->_readstream($f,$n);
				$this->_readstream($f,4);
			} elseif((string)$type == 'tRNS') { // Read transparency info
				$t = $this->_readstream($f,$n);
				if($ct == 0) {
					$trns = [ \ord(\substr($t, 1, 1)) ]; // array
				} elseif($ct == 2) {
					$trns = [ \ord(\substr($t, 1, 1)), \ord(\substr($t, 3, 1)), \ord(\substr($t, 5, 1)) ]; // array
				} else {
					$pos = \strpos($t, \chr(0));
					if($pos !== false) {
						$trns = [ $pos ]; // array
					} //end if
				} //end if else
				$this->_readstream($f, 4);
			} elseif((string)$type == 'IDAT') { // Read image data block
				$data .= $this->_readstream($f, $n);
				$this->_readstream($f, 4);
			} elseif((string)$type == 'IEND') {
				break;
			} else {
				$this->_readstream($f,$n+4);
			} //end if else
		} while($n);
		//--
		if(((string)$colspace == 'Indexed') && empty($pal)) {
			$this->Error('Missing palette in '.$file);
			return [];
		} //end if
		//--
		$info = [ 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns ];
		if($ct >= 4) { // Extract alpha channel
			//--
			$data = (string) \gzuncompress((string)$data);
			//--
			$color = '';
			$alpha = '';
			//--
			if($ct == 4) { // Gray image
				$len = 2 * $w;
				for($i=0; $i<$h; $i++) {
					$pos = (1 + $len) * $i;
					$color .= $data[$pos];
					$alpha .= $data[$pos];
					$line = (string) \substr((string)$data, $pos+1, $len);
					$color .= (string) \preg_replace('/(.)./s', '$1', (string)$line);
					$alpha .= (string) \preg_replace('/.(.)/s', '$1', (string)$line);
				} //end for
			} else { // RGB image
				$len = 4 * $w;
				for($i=0; $i<$h; $i++) {
					$pos = (1 + $len) * $i;
					$color .= $data[$pos];
					$alpha .= $data[$pos];
					$line = (string) \substr((string)$data, $pos+1, $len);
					$color .= (string) \preg_replace('/(.{3})./s', '$1', (string)$line);
					$alpha .= (string) \preg_replace('/.{3}(.)/s', '$1', (string)$line);
				} //end for
			} //end if else
			//--
			unset($data);
			$data = (string) \gzcompress((string)$color);
			$info['smask'] = \gzcompress((string)$alpha);
			$this->WithAlpha = true;
			//--
			// requires PDFVersion 1.4 or higher (not supported in PDFVersion 1.3) ; {{{SYNC-PDF-MIN-VERSION}}}
			//--
		} //end if
		//--
		$info['data'] = $data;
		//--
		return (array) $info;
		//--
	} //END FUNCTION


	private function _readstream($f, $n) : string {
		//-- verify resource
		if(!\is_resource($f) || !$f) {
			$this->Error('_parsepngstream expects a resource');
			return '';
		} //end if
		if(!$f) {
			$this->Error('_parsepngstream resource is unavailable');
			return '';
		} //end if
		//-- Read n bytes from stream
		$res = '';
		while($n > 0 && !\feof($f)) {
			$s = \fread($f,$n);
			if($s === false) {
				$this->Error('Error while reading stream');
			} //end if
			$n -= (int) \strlen((string)$s);
			$res .= $s;
		} //end while
		//--
		if($n > 0) {
			$this->Error('Unexpected end of stream');
			return '';
		} //end if
		//--
		return (string) $res;
		//--
	} //END FUNCTION


	private function _readint($f) : int {
		//--
		$a = \unpack('Ni', $this->_readstream($f, 4)); // Read a 4-byte integer from stream ; DO NOT CAST, may return array or false
		if(!\is_array($a)) {
			$this->Error('_readint: unpack failed');
			return 0;
		} //end if
		//--
		return (int) \intval($a['i']);
		//--
	} //END FUNCTION


	private function _out($s) : void {
		//--
		// Add a line to the document
		//--
		if($this->state <= 0) {
			$this->Error('No page has been added yet');
			return;
		} elseif($this->state == 2) {
			$this->pages[$this->page] .= $s."\n";
		} elseif($this->state == 1) {
			$this->_put($s);
		} elseif($this->state == 3) {
			$this->Error('The document is closed');
		} //end if else
		//--
	} //END FUNCTION


	private function _put(?string $s) : void {
		//--
		$this->buffer .= (string) $s."\n";
		//--
	} //END FUNCTION


	private function _getoffset() : int {
		//--
		return (int) \strlen((string)$this->buffer);
		//--
	} //END FUNCTION


	private function _newobj(?int $n=null) : void {
		//--
		// Begin a new object
		//--
		if($n === null) {
			$n = ++$this->n;
		} //end if
		//--
		$this->offsets[$n] = $this->_getoffset();
		$this->_put($n.' 0 obj');
		//--
	} //END FUNCTION


	private function _putstream(?string $data) : void {
		//--
		$this->_put('stream');
		$this->_put((string)$data);
		$this->_put('endstream');
		//--
	} //END FUNCTION


	private function _putstreamobject(?string $data) : void {
		//--
		if($this->compress) {
			$entries = '/Filter /FlateDecode ';
			$data = (string) \gzcompress((string)$data);
		} else {
			$entries = '';
		} //end if
		//--
		$entries .= '/Length '.(int)\strlen((string)$data);
		//--
		$this->_newobj();
		$this->_put('<<'.$entries.'>>');
		$this->_putstream((string)$data);
		$this->_put('endobj');
		//--
	} //END FUNCTION


	private function _putlinks(int $n) : void {
		//--
		foreach($this->PageLinks[$n] as $pl) {
			$this->_newobj();
			$rect = (string) \sprintf('%.2F %.2F %.2F %.2F', $pl[0], $pl[1], $pl[0] + $pl[2], $pl[1] - $pl[3]);
			$s = '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
			if(\is_string($pl[4])) {
				$s .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
			} else {
				$l = $this->links[$pl[4]];
				if(isset($this->PageInfo[$l[0]]['size'])) {
					$h = $this->PageInfo[$l[0]]['size'][1];
				} else {
					$h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
				} //end if else
				$s .= (string) \sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>', $this->PageInfo[$l[0]]['n'], $h - $l[1] * $this->k);
			} //end if else
			$this->_put($s);
			$this->_put('endobj');
		} //end foreach
		//--
	} //END FUNCTION


	private function _putpage(int $n) : void {
		//--
		$this->_newobj();
		$this->_put('<</Type /Page');
		$this->_put('/Parent 1 0 R');
		//--
		if(isset($this->PageInfo[$n]['size'])) {
			$this->_put((string)\sprintf('/MediaBox [0 0 %.2F %.2F]', $this->PageInfo[$n]['size'][0], $this->PageInfo[$n]['size'][1]));
		} //end if
		//--
		if(isset($this->PageInfo[$n]['rotation'])) {
			$this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
		} //end if
		//--
		$this->_put('/Resources 2 0 R');
		//--
		if(!empty($this->PageLinks[$n])) {
			$s = '/Annots [';
			foreach($this->PageLinks[$n] as $pl) {
				$s .= $pl[5].' 0 R ';
			} //end foreach
			$s .= ']';
			$this->_put($s);
		} //end if
		//--
		if($this->WithAlpha) {
			$this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
		} //end if
		//--
		$this->_put('/Contents '.($this->n+1).' 0 R>>');
		$this->_put('endobj');
		//-- Page content
		if(!empty($this->AliasNbPages)) {
			$alias = $this->UTF8ToUTF16BE($this->AliasNbPages, false);
			$r = $this->UTF8ToUTF16BE($this->page, false);
			$this->pages[$n] = \str_replace($alias, $r, $this->pages[$n]);
			$this->pages[$n] = \str_replace($this->AliasNbPages,$this->page,$this->pages[$n]); // repeat for no pages in non-subset fonts
		} //end if
		//--
		$this->_putstreamobject($this->pages[$n]);
		//-- Link annotations
		$this->_putlinks($n);
		//--
	} //END FUNCTION


	private function _putpages() : void {
		//--
		$nb = $this->page;
		$n = $this->n;
		//--
		for($i=1; $i<=$nb; $i++) {
			$this->PageInfo[$i]['n'] = ++$n;
			$n++;
			foreach($this->PageLinks[$i] as &$pl) {
				$pl[5] = ++$n;
			} //end foreach
			unset($pl);
		} //end for
		//--
		for($i=1; $i<=$nb; $i++) {
			$this->_putpage($i);
		} //end for
		//-- Pages root
		$this->_newobj(1);
		$this->_put('<</Type /Pages');
		$kids = '/Kids [';
		//--
		for($i=1; $i<=$nb; $i++) {
			$kids .= $this->PageInfo[$i]['n'].' 0 R ';
		} //end for
		//--
		$kids .= ']';
		$this->_put($kids);
		$this->_put('/Count '.$nb);
		//--
		if($this->DefOrientation=='P') {
			$w = $this->DefPageSize[0];
			$h = $this->DefPageSize[1];
		} else {
			$w = $this->DefPageSize[1];
			$h = $this->DefPageSize[0];
		} //end if else
		//--
		$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]', $w * $this->k, $h * $this->k));
		$this->_put('>>');
		$this->_put('endobj');
		//--
	} //END FUNCTION


	private function _putfonts() : void {
		//--
		foreach($this->fonts as $k => $font) {
			//-- Encoding
			if(isset($font['diff'])) {
				if(!isset($this->encodings[$font['enc']])) {
					$this->_newobj();
					$this->_put('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$font['diff'].']>>');
					$this->_put('endobj');
					$this->encodings[$font['enc']] = $this->n;
				} //end if
			} //end if
			//-- ToUnicode CMap
			if(isset($font['uv'])) {
				if(isset($font['enc'])) {
					$cmapkey = $font['enc'];
				} else {
					$cmapkey = $font['name'];
				} //end if else
				if(!isset($this->cmaps[$cmapkey])) {
					$cmap = $this->_tounicodecmap($font['uv']);
					$this->_putstreamobject($cmap);
					$this->cmaps[$cmapkey] = $this->n;
				} //end if
			} //end if
			//-- Font object
			$type = $font['type'];
			$name = $font['name'];
			//--
			if($type === 'TTF') { // TrueType embedded SUBSETS or FULL
				//--
				$this->fonts[$k]['n']= $this->n + 1;
				//--
				$ttf = new \PDF\zFPDF\zTTFontFile();
				$fontname = 'MPDFAA'.'+'.$font['name'];
				$subset = (array) $font['subset'];
				unset($subset[0]);
				$ttfontstream = '';
				try {
					$ttfontstream = $ttf->makeSubset((string)$font['ttffile'], $subset);
				} catch(\Exception $e) {
					$this->Error('Make TTF Font Subset Failed: '.$e->getMessage());
					return;
				} //end try catch
				$ttfontsize = (int) \strlen((string)$ttfontstream);
				$fontstream = (string) \gzcompress((string)$ttfontstream);
				$codeToGlyph = $ttf->codeToGlyph;
				unset($codeToGlyph[0]);
				//--
				// Type0 Font
				// A composite font - a font composed of other fonts, organized hierarchically
				//--
				$this->_newobj();
				$this->_put('<</Type /Font');
				$this->_put('/Subtype /Type0');
				$this->_put('/BaseFont /'.$fontname.'');
				$this->_put('/Encoding /Identity-H');
				$this->_put('/DescendantFonts ['.($this->n + 1).' 0 R]');
				$this->_put('/ToUnicode '.($this->n + 2).' 0 R');
				$this->_put('>>');
				$this->_put('endobj');
				//--
				// CIDFontType2
				// A CIDFont whose glyph descriptions are based on TrueType font technology
				$this->_newobj();
				$this->_put('<</Type /Font');
				$this->_put('/Subtype /CIDFontType2');
				$this->_put('/BaseFont /'.$fontname.'');
				$this->_put('/CIDSystemInfo '.($this->n + 2).' 0 R');
				$this->_put('/FontDescriptor '.($this->n + 3).' 0 R');
				if(isset($font['desc']['MissingWidth'])){
					$this->_out('/DW '.$font['desc']['MissingWidth'].'');
				} //end if
				//--
				$this->_putTTfontwidths($font, $ttf->maxUni);
				//--
				$this->_put('/CIDToGIDMap '.($this->n + 4).' 0 R');
				$this->_put('>>');
				$this->_put('endobj');
				//-- ToUnicode
				$this->_newobj();
				$toUni = "/CIDInit /ProcSet findresource begin\n";
				$toUni .= "12 dict begin\n";
				$toUni .= "begincmap\n";
				$toUni .= "/CIDSystemInfo\n";
				$toUni .= "<</Registry (Adobe)\n";
				$toUni .= "/Ordering (UCS)\n";
				$toUni .= "/Supplement 0\n";
				$toUni .= ">> def\n";
				$toUni .= "/CMapName /Adobe-Identity-UCS def\n";
				$toUni .= "/CMapType 2 def\n";
				$toUni .= "1 begincodespacerange\n";
				$toUni .= "<0000> <FFFF>\n";
				$toUni .= "endcodespacerange\n";
				$toUni .= "1 beginbfrange\n";
				$toUni .= "<0000> <FFFF> <0000>\n";
				$toUni .= "endbfrange\n";
				$toUni .= "endcmap\n";
				$toUni .= "CMapName currentdict /CMap defineresource pop\n";
				$toUni .= "end\n";
				$toUni .= "end";
				$this->_put('<</Length '.(int)\strlen((string)$toUni).'>>');
				$this->_putstream($toUni);
				$this->_put('endobj');
				//-- CIDSystemInfo dictionary
				$this->_newobj();
				$this->_put('<</Registry (Adobe)');
				$this->_put('/Ordering (UCS)');
				$this->_put('/Supplement 0');
				$this->_put('>>');
				$this->_put('endobj');
				//-- Font descriptor
				$this->_newobj();
				$this->_put('<</Type /FontDescriptor');
				$this->_put('/FontName /'.$fontname);
				foreach($font['desc'] as $kd => $v) {
					if($kd == 'Flags') { // SYMBOLIC font flag
						$v = $v | 4; $v = $v & ~32;
					} //end if
					$this->_out(' /'.$kd.' '.$v);
				} //end foreach
				$this->_put('/FontFile2 '.($this->n + 2).' 0 R');
				$this->_put('>>');
				$this->_put('endobj');
				//-- Embed CIDToGIDMap, a specification of the mapping from CIDs to glyph indices
				$cidtogidmap = '';
				$cidtogidmap = \str_pad('', 256*256*2, "\x00");
				foreach($codeToGlyph as $cc => $glyph) {
					$cidtogidmap[$cc*2] = \chr($glyph >> 8);
					$cidtogidmap[$cc*2 + 1] = \chr($glyph & 0xFF);
				} //end foreach
				$cidtogidmap = (string) \gzcompress((string)$cidtogidmap);
				$this->_newobj();
				$this->_put('<</Length '.(int)\strlen((string)$cidtogidmap));
				$this->_put('/Filter /FlateDecode');
				$this->_put('>>');
				$this->_putstream($cidtogidmap);
				$this->_put('endobj');
				//-- Font file
				$this->_newobj();
				$this->_put('<</Length '.(int)\strlen((string)$fontstream));
				$this->_put('/Filter /FlateDecode');
				$this->_put('/Length1 '.$ttfontsize);
				$this->_put('>>');
				$this->_putstream($fontstream);
				$this->_put('endobj');
				unset($ttf);
				//--
			} elseif($type === 'Core') {
				//--
				$this->Error('_putfonts: Core Fonts are DISABLED, they are not Unicode, use TTF instead');
				return;
				//--
			} else {
				//-- Allow for additional types
				$this->Error('Unsupported Font Type: '.$type);
				return;
			} //end if else
			//--
		} //end foreach
		//--
	} //END FUNCTION


	private function _putTTfontwidths(array $font, int $maxUni) : void {
		//--
		$rangeid = 0;
		$range = [];
		$prevcid = -2;
		$prevwidth = -1;
		$interval = false;
		$startcid = 1;
		//--
		$cwlen = $maxUni + 1;
		//-- for each character
		for($cid=$startcid; $cid<$cwlen; $cid++) {
			//--
			if((!isset($font['cw'][$cid*2]) || !isset($font['cw'][$cid*2+1])) || ($font['cw'][$cid*2] == "\00" && $font['cw'][$cid*2+1] == "\00")) {
				continue;
			} //end if
			//--
			$width = (\ord($font['cw'][$cid*2]) << 8) + \ord($font['cw'][$cid*2+1]);
			if($width == 65535) {
				$width = 0;
			} //end if
			//--
			if($cid > 255 && (!isset($font['subset'][$cid]) || !$font['subset'][$cid])) {
				continue;
			} //end if
			//--
			if(!isset($font['dw']) || (isset($font['dw']) && $width != $font['dw'])) {
				if($cid == ($prevcid + 1)) {
					if($width == $prevwidth) {
						if($width == $range[$rangeid][0]) {
							$range[$rangeid][] = $width;
						} else {
							\array_pop($range[$rangeid]);
							$rangeid = $prevcid; // new range
							$range[$rangeid] = [];
							$range[$rangeid][] = $prevwidth;
							$range[$rangeid][] = $width;
						} //end if else
						$interval = true;
						$range[$rangeid]['interval'] = true;
					} else {
						if($interval) {
							$rangeid = $cid; // new range
							$range[$rangeid] = [];
							$range[$rangeid][] = $width;
						} else {
							$range[$rangeid][] = $width;
						} //end if else
						$interval = false;
					} //end if else
				} else {
					$rangeid = $cid;
					$range[$rangeid] = [];
					$range[$rangeid][] = $width;
					$interval = false;
				} //end if else
				$prevcid = $cid;
				$prevwidth = $width;
			} //end if
			//--
		} //end for
		//--
		$prevk = -1;
		$nextk = -1;
		$prevint = false;
		//--
		foreach($range as $k => $ws) {
			$cws = \count($ws);
			if(($k == $nextk) AND (!$prevint) AND ((!isset($ws['interval'])) OR ($cws < 4))) {
				if(isset($range[$k]['interval'])) {
					unset($range[$k]['interval']);
				} //end if
				$range[$prevk] = \array_merge($range[$prevk], $range[$k]);
				unset($range[$k]);
			} else {
				$prevk = $k;
			} //end if else
			$nextk = $k + $cws;
			if(isset($ws['interval'])) {
				if($cws > 3) {
					$prevint = true;
				} else {
					$prevint = false;
				} //end if else
				unset($range[$k]['interval']);
				--$nextk;
			} else {
				$prevint = false;
			} //end if else
		} //end foreach
		//--
		$w = '';
		foreach($range as $k => $ws) {
			if(\count(\array_count_values($ws)) == 1) {
				$w .= ' '.$k.' '.($k + \count($ws) - 1).' '.$ws[0];
			} else {
				$w .= ' '.$k.' [ '.\implode(' ', $ws).' ]' . "\n";
			} //end if else
		} //end foreach
		//--
		$this->_out('/W ['.$w.' ]');
		//--
	} //END FUNCTION


	private function _tounicodecmap(array $uv) : string {
		//--
		$ranges = '';
		$nbr = 0;
		$chars = '';
		$nbc = 0;
		//--
		foreach($uv as $c => $v) {
			if(\is_array($v)) {
				$ranges .= (string) \sprintf('<%02X> <%02X> <%04X>'."\n", $c, $c + $v[1] - 1, $v[0]);
				$nbr++;
			} else {
				$chars  .= (string) \sprintf('<%02X> <%04X>'."\n", $c, $v);
				$nbc++;
			} //end if else
		} //end foreach
		//--
		$s = "/CIDInit /ProcSet findresource begin\n";
		$s .= "12 dict begin\n";
		$s .= "begincmap\n";
		$s .= "/CIDSystemInfo\n";
		$s .= "<</Registry (Adobe)\n";
		$s .= "/Ordering (UCS)\n";
		$s .= "/Supplement 0\n";
		$s .= ">> def\n";
		$s .= "/CMapName /Adobe-Identity-UCS def\n";
		$s .= "/CMapType 2 def\n";
		$s .= "1 begincodespacerange\n";
		$s .= "<00> <FF>\n";
		$s .= "endcodespacerange\n";
		//--
		if($nbr > 0) {
			$s .= (string) $nbr." beginbfrange\n";
			$s .= (string) $ranges;
			$s .= "endbfrange\n";
		} //end if
		//--
		if($nbc > 0) {
			$s .= (string) $nbc." beginbfchar\n";
			$s .= (string) $chars;
			$s .= "endbfchar\n";
		} //end if
		//--
		$s .= "endcmap\n";
		$s .= "CMapName currentdict /CMap defineresource pop\n";
		$s .= "end\n";
		$s .= "end";
		//--
		return (string) $s;
		//--
	} //END FUNCTION


	private function _putimages() : void {
		//--
		foreach(array_keys($this->images) as $file) {
			//--
			$this->_putimage($this->images[$file]);
			//--
			unset($this->images[$file]['data']);
			unset($this->images[$file]['smask']);
			//--
		} //end foreach
		//--
	} //END FUNCTION


	private function _putimage(array &$info) : void {
		//--
		$this->_newobj();
		//--
		$info['n'] = $this->n;
		//--
		$this->_put('<</Type /XObject');
		$this->_put('/Subtype /Image');
		$this->_put('/Width '.$info['w']);
		$this->_put('/Height '.$info['h']);
		//--
		if($info['cs'] === 'Indexed') {
			$this->_put('/ColorSpace [/Indexed /DeviceRGB '.((int)\strlen((string)$info['pal']) / 3 - 1).' '.($this->n + 1).' 0 R]');
		} else {
			$this->_put('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK') {
				$this->_put('/Decode [1 0 1 0 1 0 1 0]');
			} //end if
		} //end if else
		//--
		$this->_put('/BitsPerComponent '.$info['bpc']);
		//--
		if(isset($info['f'])) {
			$this->_put('/Filter /'.$info['f']);
		} //end if
		//--
		if(isset($info['dp'])) {
			$this->_put('/DecodeParms <<'.$info['dp'].'>>');
		} //end if
		//--
		if(isset($info['trns']) && \is_array($info['trns'])) {
			$trns = '';
			for($i=0; $i<\count($info['trns']); $i++) {
				$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
			} //end for
			$this->_put('/Mask ['.$trns.']');
		} //end if
		//--
		if(isset($info['smask'])) {
			$this->_put('/SMask '.($this->n+1).' 0 R');
		} //end if
		//--
		$this->_put('/Length '.(int)\strlen((string)$info['data']).'>>');
		$this->_putstream($info['data']);
		$this->_put('endobj');
		//-- Soft mask
		if(isset($info['smask'])) {
			$dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w'];
			$smask = [ 'w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>$info['f'], 'dp'=>$dp, 'data'=>$info['smask'] ];
			$this->_putimage($smask);
		} //end if
		//-- Palette
		if($info['cs']=='Indexed') {
			$this->_putstreamobject($info['pal']);
		} //end if
		//--
	} //END FUNCTION


	private function _putxobjectdict() : void {
		//--
		foreach($this->images as $image) {
			$this->_put('/I'.$image['i'].' '.$image['n'].' 0 R');
		} //end foreach
		//--
	} //END FUNCTION


	private function _putresourcedict() : void {
		//--
		$this->_put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
		$this->_put('/Font <<');
		//--
		foreach($this->fonts as $font) {
			$this->_put('/F'.$font['i'].' '.$font['n'].' 0 R');
		} //end foreach
		//--
		$this->_put('>>');
		$this->_put('/XObject <<');
		$this->_putxobjectdict();
		$this->_put('>>');
		//--
	} //END FUNCTION


	private function _putresources() : void {
		//--
		$this->_putfonts();
		$this->_putimages();
		//-- Resource dictionary
		$this->_newobj(2);
		$this->_put('<<');
		$this->_putresourcedict();
		$this->_put('>>');
		$this->_put('endobj');
		//--
	} //END FUNCTION


	private function _putinfo() : void {
		//--
		$date = (string) \date('YmdHisO', $this->CreationDate);
		//--
		$this->metadata['CreationDate'] = 'D:'.\substr($date, 0, -2)."'".\substr($date, -2)."'";
		foreach($this->metadata as $key => $value) {
			$this->_put('/'.$key.' '.$this->_textstring($value));
		} //end foreach
		//--
	} //END FUNCTION


	private function _putcatalog() : void {
		//--
		$n = $this->PageInfo[1]['n'];
		//--
		$this->_put('/Type /Catalog');
		$this->_put('/Pages 1 0 R');
		//--
		if($this->ZoomMode=='fullpage') {
			$this->_put('/OpenAction ['.$n.' 0 R /Fit]');
		} elseif($this->ZoomMode=='fullwidth') {
			$this->_put('/OpenAction ['.$n.' 0 R /FitH null]');
		} elseif($this->ZoomMode=='real') {
			$this->_put('/OpenAction ['.$n.' 0 R /XYZ null null 1]');
		} elseif(!is_string($this->ZoomMode)) {
			$this->_put('/OpenAction ['.$n.' 0 R /XYZ null null '.\sprintf('%.2F', $this->ZoomMode / 100).']');
		} //end if else
		//--
		if($this->LayoutMode=='single') {
			$this->_put('/PageLayout /SinglePage');
		} elseif($this->LayoutMode=='continuous') {
			$this->_put('/PageLayout /OneColumn');
		} elseif($this->LayoutMode=='two') {
			$this->_put('/PageLayout /TwoColumnLeft');
		} //end if else
		//--
	} //END FUNCTION


	private function _putheader() : void {
		//--
		$this->_put('%PDF-'.self::PDFVersion);
		//--
	} //END FUNCTION


	private function _puttrailer() : void {
		//--
		$this->_put('/Size '.($this->n+1));
		$this->_put('/Root '.$this->n.' 0 R');
		$this->_put('/Info '.($this->n-1).' 0 R');
		//--
	} //END FUNCTION


	private function _enddoc() : void {
		//-- Date
		$this->CreationDate = time();
		$this->_putheader();
		$this->_putpages();
		$this->_putresources();
		//-- Info
		$this->_newobj();
		$this->_put('<<');
		$this->_putinfo();
		$this->_put('>>');
		$this->_put('endobj');
		//-- Catalog
		$this->_newobj();
		$this->_put('<<');
		$this->_putcatalog();
		$this->_put('>>');
		$this->_put('endobj');
		//-- Cross-ref
		$offset = $this->_getoffset();
		$this->_put('xref');
		$this->_put('0 '.($this->n+1));
		$this->_put('0000000000 65535 f ');
		for($i=1;$i<=$this->n;$i++) {
			$this->_put((string)sprintf('%010d 00000 n ', $this->offsets[$i]));
		} //end for
		//-- Trailer
		$this->_put('trailer');
		$this->_put('<<');
		$this->_puttrailer();
		$this->_put('>>');
		$this->_put('startxref');
		$this->_put($offset);
		$this->_put('%%EOF');
		$this->state = 3;
		//--
	} //END FUNCTION


	// ********* NEW FUNCTIONS *********


	private function UTF8ToUTF16BE(?string $str, bool $setbom=true) : string {
		//--
		// Converts UTF-8 strings to UTF16-BE
		//--
		$outstr = '';
		//--
		if($setbom) {
			$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
		} //end if
		$outstr .= (string) \mb_convert_encoding((string)$str, 'UTF-16BE', 'UTF-8');
		//--
		return (string) $outstr;
		//--
	} //END FUNCTION


	private function UTF8StringToArray(?string $str) : array {
		//--
		// Converts UTF-8 strings to codepoints array
		//--
		$out = [];
		//--
		$len = (int) \strlen((string)$str);
		for($i=0; $i<$len; $i++) {
			$uni = -1;
			$h = \ord($str[$i]);
			if($h <= 0x7F) {
				$uni = $h;
			} elseif($h >= 0xC2) {
				if(($h <= 0xDF) && ($i < $len -1)) {
					$uni = ($h & 0x1F) << 6 | (\ord($str[++$i]) & 0x3F);
				} elseif(($h <= 0xEF) && ($i < $len -2)) {
					$uni = ($h & 0x0F) << 12 | (\ord($str[++$i]) & 0x3F) << 6 | (\ord($str[++$i]) & 0x3F);
				} elseif(($h <= 0xF4) && ($i < $len -3)) {
					$uni = ($h & 0x0F) << 18 | (\ord($str[++$i]) & 0x3F) << 12 | (\ord($str[++$i]) & 0x3F) << 6 | (\ord($str[++$i]) & 0x3F);
				} //end if else
			} //end if else
			if($uni >= 0) {
				$out[] = $uni;
			} //end if
		} //end for
		//--
		return (array) $out;
		//--
	} //END FUNCTION


	//-------- [#]


} //END CLASS


// #end
