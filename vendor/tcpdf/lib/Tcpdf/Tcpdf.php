<?php
# vendor/tcpdf/lib/Tcpdf/Tcpdf.php

require_once __DIR__.'/src/tcpdf.php';

class Tcpdf_Tcpdf extends TCPDF
{
    /* --- */

    private $isWLogo = true;
    private $isTabl = false;
    private $isFoot = true;
    private $grPaginate = false;
    private $isMkLateral = false;
    private $entity = null;
    private $memoTitle = "None";
    private $image_file = "None";
    public $emp_name = "_NONE_";
    public $emp_reg = "_NONE_";
    public $emp_ubi = "_NONE_";
    public $emp_web = "_NONE_";

    public function getDir()
    {
        return __DIR__.'/src';
    }

    public function Header()
    {

        if($this->isWLogo)
        {
            if($this->image_file == "None")
                $this->image_file = K_PATH_IMAGES.'scribo_logo.jpg';
            
            $this->Image($this->image_file, 20, 15, '', 23, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);
            $this->SetFont('helvetica', '', 20);
        }

        if($this->isTabl)
        {
            $hoy = new \DateTime();

            $html = '<table style="border-bottom: 3px solid #063166;">';
            $html .= '<tr><td>&nbsp;</td></tr>';
            $html .= '<tr><td><b>'.$this->memoTitle.'</b></td></tr>';
            $html .= '<tr><td><b>'.$this->emp_name.'</b></td></tr>';
            $html .= '<tr><td><b>'.$this->emp_reg.'</b></td></tr>';
            $html .= '<tr><td><b>SCRIBO 1.0</b></td></tr>';
            $html .= '</table>';

            $this->SetFont('helvetica','',10);
            $this->writeHTMLCell(0, 0, 20, 17, $html, 0, 1, 0, true, 'R', true);
        }
    }
    
    public function Footer()
    {
        if($this->isFoot)
        {
            $this->SetY(-1*$this->footer_margin);
            $this->SetFont('helvetica', 'B', 8);
            $html = '<table style="border-top: 3px solid #063166;">';
            $html .= '<tr><td colspan="2"><b>'.$this->emp_ubi.'</b></td></tr>';
            $html .= '<tr><td colspan="2">'.$this->emp_web.'</td></tr>';
            
            if($this->grPaginate)
                $html .= '<tr><td align="left" style="width: 58.5%;">Powered By IT Coporation · www.itclatam.com · sales@itclatam.com</td><td align="right">Pag '.$this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias().'</td></tr>';
            else
                $html .= '<tr><td align="left" style="width: 58.5%;">Powered By IT Coporation · www.itclatam.com · sales@itclatam.com</td><td align="right">Pag '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td></tr>';
            
            $html .= '</table>';
            $this->writeHTMLCell(0, 0, 20, $this->GetY(), $html, 0, 1, 0, true, 'C', true);

            if($this->isMkLateral)
                $this->mkLateral();
        }
        else
        {
            $this->SetY((-1*$this->footer_margin)-5);
            $this->SetFont('helvetica', 'I', 8);

            if($this->grPaginate)
                $this->Cell(0, 10, $this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
            else
                $this->Cell(0, 10, $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        }
    }
    
    public function setImgLogo($img)
    {
        $this->image_file = $img;
    }
    
    private function mkLateral()
    {
        $fac = 0.75;
        if($this->CurOrientation == 'L')
            $fac = 0.85;

        $this->SetTextColor(6, 49, 102);
        $this->StartTransform();
        $this->Rotate(90, 10, $this->getPageHeight()*$fac);
        $this->Text(10, ($this->getPageHeight()*$fac)-4, 'R. M. de Barcelona · Tomo 14.157 · Folio 67 · Hoja/Dup. 241948 · Inscripción 1ª · N.I.F. B-62809306 ');
        $this->StopTransform();
    }

    public function setWLogo($val)
    {
        $this->isWLogo = $val;
    }

    public function setMemoTitle($val)
    {
        $this->memoTitle = $val;
    }

    public function setTabl($val)
    {
        $this->isTabl = $val;
    }

    public function setFoot($val)
    {
        $this->isFoot = $val;
    }

    public function setGrPaginate($val)
    {
        $this->grPaginate = $val;
    }

    public function setMkLateral($val)
    {
        $this->isMkLateral = $val;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function autoCell($w, $h, $x, $y, $html = '', $border = 0 , $ln = 0, $fill = false, $reseth = true, $align = '', $autopadding = true, $PCMAX = 40)
    {
        $toc = false;

        if(($this->y + $PCMAX) >= $this->h)
        {
            $this->AddPage();
            $y = $this->GetY();
            $toc = true;
        }

        $this->writeHTMLCell($w, $h, $x, $y, $html, $border, $ln, $fill, $reseth, $align, $autopadding);
    }
}

