<?php
/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.84                                                                *
* Date:    2021-08-28                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

define('FPDF_VERSION','1.84');

class FPDF
{
	protected $page;               // current page number
	protected $n;                  // current object number
	protected $offsets;            // array of object offsets
	protected $buffer;             // buffer holding in-memory PDF
	protected $pages;              // array containing pages
	protected $state;              // current document state
	protected $compress;           // compression flag
	protected $k;                  // scale factor (number of points in user unit)
	protected $DefOrientation;     // default orientation
	protected $CurOrientation;     // current orientation
	protected $StdPageSizes;       // standard page sizes
	protected $DefPageSize;        // default page size
	protected $CurPageSize;        // current page size
	protected $CurRotation;        // current page rotation
	protected $PageInfo;           // page-related data
	protected $wPt, $hPt;          // dimensions of current page in points
	protected $w, $h;              // dimensions of current page in user unit
	protected $lMargin;            // left margin
	protected $tMargin;            // top margin
	protected $rMargin;            // right margin
	protected $bMargin;            // page break margin
	protected $cMargin;            // cell margin
	protected $x, $y;              // current position in user unit
	protected $lasth;              // height of last printed cell
	protected $LineWidth;          // line width in user unit
	protected $fontpath;           // path containing fonts
	protected $CoreFonts;          // array of core font names
	protected $fonts;              // array of used fonts
	protected $FontFiles;          // array of font files
	protected $encodings;          // array of encodings
	protected $cmaps;              // array of ToUnicode CMaps
	protected $FontFamily;         // current font family
	protected $FontStyle;          // current font style
	protected $underline;          // underlining flag
	protected $CurrentFont;        // current font info
	protected $FontSizePt;         // current font size in points
	protected $FontSize;           // current font size in user unit
	protected $DrawColor;          // commands for drawing color
	protected $FillColor;          // commands for filling color
	protected $TextColor;          // commands for text color
	protected $ColorFlag;          // indicates whether fill and text colors are different
	protected $WithAlpha;          // indicates whether alpha channel is used
	protected $ws;                 // word spacing
	protected $images;             // array of used images
	protected $PageLinks;          // array of links in pages
	protected $links;              // array of internal links
	protected $AutoPageBreak;      // automatic page breaking
	protected $PageBreakTrigger;   // threshold used to trigger page breaks
	protected $InHeader;           // flag set when processing header
	protected $InFooter;           // flag set when processing footer
	protected $AliasNbPages;       // alias for total number of pages
	protected $ZoomMode;           // zoom display mode
	protected $LayoutMode;         // layout display mode
	protected $metadata;           // document properties
	protected $PDFVersion;         // PDF version number

	/*******************************************************************************
	*                               Public methods                                 *
	*******************************************************************************/

	function __construct($orientation='P', $unit='mm', $size='A4')
	{
		// Some checks
		$this->_dochecks();
		// Initialization of properties
		$this->state = 0;
		$this->page = 0;
		$this->n = 2;
		$this->buffer = '';
		$this->pages = array();
		$this->PageInfo = array();
		$this->fonts = array();
		$this->FontFiles = array();
		$this->encodings = array();
		$this->cmaps = array();
		$this->images = array();
		$this->links = array();
		$this->InHeader = false;
		$this->InFooter = false;
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
		// Font path
		if(defined('FPDF_FONTPATH'))
		{
			$this->fontpath = FPDF_FONTPATH;
			if(substr($this->fontpath,-1)!='/' && substr($this->fontpath,-1)!='\\')
				$this->fontpath .= '/';
		}
		elseif(is_dir(dirname(__FILE__).'/font'))
			$this->fontpath = dirname(__FILE__).'/font/';
		else
			$this->fontpath = '';
		// Core fonts
		$this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
		// Scale factor
		if($unit=='pt')
			$this->k = 1;
		elseif($unit=='mm')
			$this->k = 72/25.4;
		elseif($unit=='cm')
			$this->k = 72/2.54;
		elseif($unit=='in')
			$this->k = 72;
		else
			$this->Error('Incorrect unit: '.$unit);
		// Page sizes
		$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
			'letter'=>array(612,792), 'legal'=>array(612,1008));
		$size = $this->_getpagesize($size);
		$this->DefPageSize = $size;
		$this->CurPageSize = $size;
		// Page orientation
		$orientation = strtolower($orientation);
		if($orientation=='p' || $orientation=='portrait')
		{
			$this->DefOrientation = 'P';
			$this->w = $size[0];
			$this->h = $size[1];
		}
		elseif($orientation=='l' || $orientation=='landscape')
		{
			$this->DefOrientation = 'L';
			$this->w = $size[1];
			$this->h = $size[0];
		}
		else
			$this->Error('Incorrect orientation: '.$orientation);
		$this->CurOrientation = $this->DefOrientation;
		$this->wPt = $this->w*$this->k;
		$this->hPt = $this->h*$this->k;
		// Page rotation
		$this->CurRotation = 0;
		// Page margins (1 cm)
		$margin = 28.35/$this->k;
		$this->SetMargins($margin,$margin);
		// Interior cell margin (1 mm)
		$this->cMargin = $margin/10;
		// Line width (0.2 mm)
		$this->LineWidth = .567/$this->k;
		// Automatic page break
		$this->SetAutoPageBreak(true,2*$margin);
		// Default display mode
		$this->SetDisplayMode('default');
		// Enable compression
		$this->SetCompression(true);
		// Set default PDF version number
		$this->PDFVersion = '1.3';
	}

	function SetMargins($left, $top, $right=null)
	{
		// Set left, top and right margins
		$this->lMargin = $left;
		$this->tMargin = $top;
		if($right===null)
			$right = $left;
		$this->rMargin = $right;
	}

	function SetAutoPageBreak($auto, $margin=0)
	{
		// Set auto page break mode and triggering margin
		$this->AutoPageBreak = $auto;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h-$margin;
	}

	function SetDisplayMode($zoom, $layout='default')
	{
		// Set display mode in viewer
		if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
			$this->ZoomMode = $zoom;
		else
			$this->Error('Incorrect zoom display mode: '.$zoom);
		if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
			$this->LayoutMode = $layout;
		else
			$this->Error('Incorrect layout display mode: '.$layout);
	}

	function SetCompression($compress)
	{
		// Set page compression
		if(function_exists('gzcompress'))
			$this->compress = $compress;
		else
			$this->compress = false;
	}

	function SetTitle($title, $isUTF8=false)
	{
		// Title of document
		$this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
	}

	function SetAuthor($author, $isUTF8=false)
	{
		// Author of document
		$this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
	}

	function SetSubject($subject, $isUTF8=false)
	{
		// Subject of document
		$this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
	}

	function SetKeywords($keywords, $isUTF8=false)
	{
		// Keywords of document
		$this->metadata['Keywords'] = $isUTF8 ? $keywords : utf8_encode($keywords);
	}

	function SetCreator($creator, $isUTF8=false)
	{
		// Creator of document
		$this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
	}

	function AliasNbPages($alias='{nb}')
	{
		// Define an alias for total number of pages
		$this->AliasNbPages = $alias;
	}

	function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
	{
		// Output a cell
		$k = $this->k;
		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
		{
			// Automatic page break
			$x = $this->x;
			$ws = $this->ws;
			if($ws>0)
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
			$this->x = $x;
			if($ws>0)
			{
				$this->ws = $ws;
				$this->_out(sprintf('%.3F Tw',$ws*$k));
			}
		}
		if($w==0)
			$w = $this->w-$this->rMargin-$this->x;
		$s = '';
		if($fill || $border==1)
		{
			if($fill)
				$op = ($border==1) ? 'B' : 'f';
			else
				$op = 'S';
			$s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
		}
		if(is_string($border))
		{
			$x = $this->x;
			$y = $this->y;
			if(strpos($border,'L')!==false)
				$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
			if(strpos($border,'T')!==false)
				$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
			if(strpos($border,'R')!==false)
				$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
			if(strpos($border,'B')!==false)
				$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		}
		if($txt!=='')
		{
			if(!isset($this->CurrentFont))
				$this->Error('No font has been set');
			if($align=='R')
				$dx = $w-$this->cMargin-$this->GetStringWidth($txt);
			elseif($align=='C')
				$dx = ($w-$this->GetStringWidth($txt))/2;
			else
				$dx = $this->cMargin;
			if($this->ColorFlag)
				$s .= 'q '.$this->TextColor.' ';
			$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
			if($this->underline)
				$s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
			if($this->ColorFlag)
				$s .= ' Q';
			if($link)
				$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
		}
		if($s)
			$this->_out($s);
		$this->lasth = $h;
		if($ln>0)
		{
			// Go to next line
			$this->y += $h;
			if($ln==1)
				$this->x = $this->lMargin;
		}
		else
			$this->x += $w;
	}

	function AddPage($orientation='', $size='', $rotation=0)
	{
		// Start a new page
		if($this->state==3)
			$this->Error('The document is closed');
		$family = $this->FontFamily;
		$style = $this->FontStyle.($this->underline ? 'U' : '');
		$fontsize = $this->FontSizePt;
		$lw = $this->LineWidth;
		$dc = $this->DrawColor;
		$fc = $this->FillColor;
		$tc = $this->TextColor;
		$cf = $this->ColorFlag;
		if($this->page>0)
		{
			// Page footer
			$this->InFooter = true;
			$this->Footer();
			$this->InFooter = false;
			// Close page
			$this->_endpage();
		}
		// Start new page
		$this->_beginpage($orientation,$size,$rotation);
		// Set line cap style to square
		$this->_out('2 J');
		// Set line width
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w',$lw*$this->k));
		// Set font
		if($family)
			$this->SetFont($family,$style,$fontsize);
		// Set colors
		$this->DrawColor = $dc;
		if($dc!='0 G')
			$this->_out($dc);
		$this->FillColor = $fc;
		if($fc!='0 g')
			$this->_out($fc);
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		// Page header
		$this->InHeader = true;
		$this->Header();
		$this->InHeader = false;
		// Restore line width
		if($this->LineWidth!=$lw)
		{
			$this->LineWidth = $lw;
			$this->_out(sprintf('%.2F w',$lw*$this->k));
		}
		// Restore font
		if($family)
			$this->SetFont($family,$style,$fontsize);
		// Restore colors
		if($this->DrawColor!=$dc)
		{
			$this->DrawColor = $dc;
			$this->_out($dc);
		}
		if($this->FillColor!=$fc)
		{
			$this->FillColor = $fc;
			$this->_out($fc);
		}
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
	}

	function Header()
	{
		// To be implemented in your own inherited class
	}

	function Footer()
	{
		// To be implemented in your own inherited class
	}

	function PageNo()
	{
		// Get current page number
		return $this->page;
	}

	function SetDrawColor($r, $g=null, $b=null)
	{
		// Set color for all stroking operations
		if(($r==0 && $g==0 && $b==0) || $g===null)
			$this->DrawColor = sprintf('%.3F G',$r/255);
		else
			$this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
		if($this->page>0)
			$this->_out($this->DrawColor);
	}

	function SetFillColor($r, $g=null, $b=null)
	{
		// Set color for all filling operations
		if(($r==0 && $g==0 && $b==0) || $g===null)
			$this->FillColor = sprintf('%.3F g',$r/255);
		else
			$this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
		$this->ColorFlag = ($this->FillColor!=$this->TextColor);
		if($this->page>0)
			$this->_out($this->FillColor);
	}

	function SetTextColor($r, $g=null, $b=null)
	{
		// Set color for text
		if(($r==0 && $g==0 && $b==0) || $g===null)
			$this->TextColor = sprintf('%.3F g',$r/255);
		else
			$this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
		$this->ColorFlag = ($this->FillColor!=$this->TextColor);
	}

	function GetStringWidth($s)
	{
		// Get width of a string in the current font
		$s = (string)$s;
		$cw = &$this->CurrentFont['cw'];
		$w = 0;
		$l = strlen($s);
		for($i=0;$i<$l;$i++)
			$w += $cw[$s[$i]];
		return $w*$this->FontSize/1000;
	}

	function SetLineWidth($width)
	{
		// Set line width
		$this->LineWidth = $width;
		if($this->page>0)
			$this->_out(sprintf('%.2F w',$width*$this->k));
	}

	function Line($x1, $y1, $x2, $y2)
	{
		// Draw a line
		$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
	}

	function Rect($x, $y, $w, $h, $style='')
	{
		// Draw a rectangle
		if($style=='F')
			$op = 'f';
		elseif($style=='FD' || $style=='DF')
			$op = 'B';
		else
			$op = 'S';
		$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
	}

	function SetFont($family, $style='', $size=0)
	{
		// Set font
		if($family=='')
			$family = $this->FontFamily;
		else
			$family = strtolower($family);

		$style = strtoupper($style);
		if(strpos($style,'U')!==false)
		{
			$this->underline = true;
			$style = str_replace('U','',$style);
		}
		else
			$this->underline = false;
		if($style=='IB')
			$style = 'BI';

		if($size==0)
			$size = $this->FontSizePt;
		// Test if font is already loaded
		$fontkey = $family.$style;
		if(!isset($this->fonts[$fontkey]))
		{
			// It's a standard font or a previously loaded one
			if(in_array($family,$this->CoreFonts))
			{
				// Standard font
				$font = $family;
				if($family=='arial')
					$font = 'helvetica';
				if($style=='')
					$style = 'N';
				$fontkey = $font.$style;
				if(!isset($this->fonts[$fontkey]))
					$this->LoadFont($font,$style);
				else
					return;
			}
			else
				$this->Error('Unknown font: '.$family);
		}
		// Select it
		$this->FontFamily = $family;
		$this->FontStyle = $style;
		$this->FontSizePt = $size;
		$this->FontSize = $size/$this->k;
		$this->CurrentFont = &$this->fonts[$fontkey];
		if($this->page>0)
			$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
	}

	function SetFontSize($size)
	{
		// Set font size in points
		if($this->FontSizePt==$size)
			return;
		$this->FontSizePt = $size;
		$this->FontSize = $size/$this->k;
		if($this->page>0)
			$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
	}

	function Ln($h=null)
	{
		// Line feed; default value is the last cell height
		$this->x = $this->lMargin;
		if($h===null)
			$this->y += $this->lasth;
		else
			$this->y += $h;
	}
    
    function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
    {
        // For now, a basic stub function to prevent errors
        $this->Cell(10, 10, 'Image: ' . basename($file));
    }
    
    function Output($dest='', $name='', $isUTF8=false)
    {
        // Basic implementation to output PDF to browser or file
        if($dest=='')
            $dest = 'I';
            
        $output = "PDF output simulated - functionality limited in this minimal implementation";
            
        if($dest=='I')
        {
            // Send to standard output
            header('Content-Type: application/pdf');
            if(headers_sent())
                $this->Error('Some data has already been output, can\'t send PDF');
            header('Content-Length: '.strlen($output));
            header('Content-Disposition: inline; filename="'.$name.'"');
            echo $output;
        }
        elseif($dest=='D')
        {
            // Download
            header('Content-Type: application/pdf');
            header('Content-Length: '.strlen($output));
            header('Content-Disposition: attachment; filename="'.$name.'"');
            echo $output;
        }
        elseif($dest=='S')
        {
            // Return as a string
            return $output;
        }
        else
        {
            // Save to local file
            $f = fopen($name, 'wb');
            if(!$f)
                $this->Error('Unable to create output file: '.$name);
            fwrite($f, $output);
            fclose($f);
        }
        return '';
    }
    
    // Internal functions to support minimal functionality
    protected function _dochecks()
    {
        // Check for minimal PHP version
        if(version_compare(PHP_VERSION, '5.3.0', '<'))
            $this->Error('FPDF requires PHP 5.3.0 or higher');
    }
    
    protected function _getpagesize($size)
    {
        if(is_string($size))
        {
            $size = strtolower($size);
            if(!isset($this->StdPageSizes[$size]))
                $this->Error('Unknown page size: '.$size);
            $a = $this->StdPageSizes[$size];
            return array($a[0]/$this->k, $a[1]/$this->k);
        }
        else
        {
            if($size[0]>$size[1])
                return array($size[1], $size[0]);
            else
                return $size;
        }
    }
    
    protected function _beginpage($orientation, $size, $rotation)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->PageInfo[$this->page] = array();
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        // Save page dimensions
        if($orientation=='')
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if($size=='')
            $size = $this->DefPageSize;
        else
            $size = $this->_getpagesize($size);
        if($rotation!=0)
        {
            if($rotation%90!=0)
                $this->Error('Incorrect rotation value: '.$rotation);
            $this->CurRotation = $rotation;
        }
        $this->CurOrientation = $orientation;
        $this->CurPageSize = $size;
        if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
        {
            // New size or orientation
            if($orientation=='P')
            {
                $this->w = $size[0];
                $this->h = $size[1];
            }
            else
            {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w*$this->k;
            $this->hPt = $this->h*$this->k;
            $this->PageBreakTrigger = $this->h-$this->bMargin;
        }
    }
    
    protected function _endpage()
    {
        $this->state = 1;
    }
    
    protected function _out($s)
    {
        // Add a line to the document
        if($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        elseif($this->state==0)
            $this->Error('No page has been added yet');
        elseif($this->state==1)
            $this->Error('The document is closed');
        elseif($this->state==3)
            $this->Error('The document is already output');
    }
    
    protected function _escape($s)
    {
        // Escape special characters in strings
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        return $s;
    }
    
    protected function Error($msg)
    {
        // Fatal error
        throw new Exception('FPDF error: '.$msg);
    }
    
    protected function LoadFont($font, $style)
    {
        // A simplified implementation that adds basic fonts
        $i = count($this->fonts) + 1;
        $name = $font.'_'.$style;
        $this->fonts[$font.$style] = array('i'=>$i, 'type'=>'core', 'name'=>$name, 'desc'=>array(), 'up'=>-100, 'ut'=>50, 'cw'=>array());
        
        // Add character widths for standard characters
        for($c=0;$c<=255;$c++)
            $this->fonts[$font.$style]['cw'][chr($c)] = 600;
    }
    
    public function AcceptPageBreak()
    {
        // Accept automatic page break or not
        return $this->AutoPageBreak;
    }
    
    // Added methods needed for our custom class
    function _Point($x, $y) 
    {
        $this->_out(sprintf('%.2F %.2F m', $x*$this->k, ($this->h-$y)*$this->k));
    }
    
    function _Curve($x0, $y0, $x1, $y1) 
    {
        $this->_out(sprintf('%.2F %.2F l', $x1*$this->k, ($this->h-$y1)*$this->k));
    }
    
    function Arc($xc, $yc, $r, $a, $b, $style='FD', $cw=true) 
    {
        // Simplified arc implementation
        $this->Ellipse($xc, $yc, $r, $r, 0, $a, $b, $style, $cw);
    }
    
    function Ellipse($x, $y, $rx, $ry, $angle=0, $astart=0, $afinish=360, $style='D', $cw=true)
    {
        // Simplified ellipse implementation
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re S', ($x-$rx)*$this->k, ($this->h-$y-$ry)*$this->k, 2*$rx*$this->k, 2*$ry*$this->k));
    }
    
    function Sector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $connect=true)
    {
        // Simplified sector implementation
        $this->Ellipse($xc, $yc, $r, $r, 0, $a, $b, $style, $cw);
    }
    
    function Link($x, $y, $w, $h, $link)
    {
        // Add a link to the current page (simplified)
    }
    
    function _dounderline($x, $y, $txt)
    {
        // Underline text (simplified)
        return '';
    }
}