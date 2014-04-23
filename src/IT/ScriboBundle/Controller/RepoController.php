<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Repo;

class RepoController extends Controller
{
    private $NM = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            return $this->render('ScriboBundle:Repo:index.html.twig');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function enDayAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getEnForDay($this, $D);
            $tam = count($data)/2;
            $vals = array_slice($data, 0, $tam);
            $cans = array_slice($data, $tam, $tam);
            $data = null;
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte De Entregas Por Día');
            $pdf->SetSubject('Reporte');
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', '');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(20, 40, 20);
            $pdf->SetHeaderMargin(2);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(TRUE, 21);
            $pdf->setImgLogo("/tmp/".$logo);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setTabl(true);
            $pdf->setMemoTitle("REPORTE DE ENTREGAS POR DÍA");

            /*######### CANTIDADES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Fecha: $D</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $X = 40;
            $Y = $pdf->GetY()+5;
            $W = 150;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6*($i+1);
                $m = $i < 10 ? '0'.$i : ''.$i;
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $m);
            }
            
            $mx = floatval(max($cans));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $pr*$i, 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6*($i+1);
                $yy = ((floatval($cans[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $m = $i < 10 ? '0'.$i : $i;
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$m.'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$cans[$i].'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<b>CANTIDAD DE ENTREGAS POR HORA</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### VALORES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Fecha: $D</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6*($i+1);
                $m = $i < 10 ? '0'.$i : ''.$i;
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $m);
            }
            
            $mx = floatval(max($vals));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6*($i+1);
                $yy = ((intval($vals[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $m = $i < 10 ? '0'.$i : $i;
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$m.'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($vals[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<b>VALOR ($) ACUMULADO DE INGRESOS POR HORA</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### PROMEDIO ################*/
            
            $avg = array();
            for($i = 0; $i < $tam; $i++)
            {
                if(intval($cans[$i]) != 0)
                    $avg[] = intval(intval($vals[$i]) / intval($cans[$i]));
                else
                    $avg[] = 0;
            }
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Fecha: $D</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6*($i+1);
                $m = $i < 10 ? '0'.$i : ''.$i;
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $m);
            }
            
            $mx = floatval(max($avg));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6*($i+1);
                $yy = ((intval($avg[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $m = $i < 10 ? '0'.$i : $i;
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$m.'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($avg[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<b>VALOR MEDIO ($) DE INGRESOS POR ORDEN</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### RESUMEN ################*/
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Fecha: $D</b>", 0, 1, 0, true, 'L', true);
            $pdf->Ln();
            
            $emin = min($cans);
            $emax = max($cans);
            $esum = array_sum($cans);
            
            $vmin = min($vals);
            $vmax = max($vals);
            $vsum = array_sum($vals);
            
            $tavg = $this->vMoney(intval(round(floatval($vsum/24.0), 2)));
            
            $html = '<b>RESUMEN DE ENTREGAS Y VALORES ($) POR DÍA</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/24.0), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('ent_day_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function enWeekAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getEnForWeek($this, $D);
            $tam = (count($data)-1)/2;
            $ifa = $data[0];
            $vals = array_slice($data, 1, $tam);
            $cans = array_slice($data, $tam+1, $tam);
            $data = null;
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte De Entregas Por Semana');
            $pdf->SetSubject('Reporte');
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', '');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(20, 40, 20);
            $pdf->SetHeaderMargin(2);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(TRUE, 21);
            $pdf->setImgLogo("/tmp/".$logo);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setTabl(true);
            $pdf->setMemoTitle("REPORTE DE ENTREGAS POR SEMANA");

            /*######### CANTIDADES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Intervalo: $ifa</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $X = 40;
            $Y = $pdf->GetY()+5;
            $W = 150;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mkd = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB'];
            
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+(21*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $mkd[$i]);
            }
            
            $mx = floatval(max($cans));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $pr*$i, 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+(21*($i));
                $yy = ((floatval($cans[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 4 != 0 ? (intval($tam/4)+1)*4 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$mkd[$i].'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$cans[$i].'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<b>CANTIDAD DE ENTREGAS POR DÍA</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=4)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 4)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 4)).'</tr>';
            }
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### VALORES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Intervalo: $ifa</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mkd = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB'];
            
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+(21*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $mkd[$i]);
            }
            
            $mx = floatval(max($vals));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+(21*($i));
                $yy = ((floatval($vals[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<br />';
            $html .= '<table border="1">';
            $html .= '<tr>'.join('', array_slice($cbs, 0, 4)).'</tr>';
            $html .= '<tr>'.join('', array_slice($dts, 0, 4)).'</tr>';
            $html .= '<tr>'.join('', array_slice($cbs, 4, 3)).'<th style="background-color: #B1CDFF;"></th></tr>';
            $html .= '<tr>'.join('', array_slice($dts, 4, 3)).'<td></td></tr>';
            $html .= '</table>';
            
            $lta = $tam % 4 != 0 ? (intval($tam/4)+1)*4 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$mkd[$i].'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($vals[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<b>VALOR ($) ACUMULADO DE INGRESOS POR DÍA</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=4)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 4)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 4)).'</tr>';
            }
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### PROMEDIO ################*/
            
            $avg = array();
            for($i = 0; $i < $tam; $i++)
            {
                if(intval($cans[$i]) != 0)
                    $avg[] = intval(intval($vals[$i]) / intval($cans[$i]));
                else
                    $avg[] = 0;
            }
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Intervalo: $ifa</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mkd = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB'];
            
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+(21*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $mkd[$i]);
            }
            
            $mx = floatval(max($avg));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+(21*($i));
                $yy = ((floatval($avg[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 4 != 0 ? (intval($tam/4)+1)*4 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$mkd[$i].'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($avg[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<b>VALOR MEDIO ($) DE INGRESOS POR ORDEN</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=4)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 4)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 4)).'</tr>';
            }
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $Y+$H+20, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### RESUMEN ################*/
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Intervalo: $ifa</b>", 0, 1, 0, true, 'L', true);
            $pdf->Ln();
            
            $emin = min($cans);
            $emax = max($cans);
            $esum = array_sum($cans);
            
            $vmin = min($vals);
            $vmax = max($vals);
            $vsum = array_sum($vals);
            
            $tavg = $this->vMoney(intval(round(floatval($vsum/7.0), 2)));
            
            $html = '<b>RESUMEN DE ENTREGAS Y VALORES ($) POR SEMANA</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/7.0), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('ent_week_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function enMonthAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $TD = explode('-', $D);
            $TD = $this->NM[intval($TD[1])-1].' DE '.$TD[0];
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getEnForMonth($this, $D);
            $tam = count($data)/2;
            $vals = array_slice($data, 0, $tam);
            $cans = array_slice($data, $tam, $tam);
            $data = null;
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte De Entregas Por Mes');
            $pdf->SetSubject('Reporte');
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', '');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(20, 40, 20);
            $pdf->SetHeaderMargin(2);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(TRUE, 21);
            $pdf->setImgLogo("/tmp/".$logo);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setTabl(true);
            $pdf->setMemoTitle("REPORTE DE ENTREGAS POR MES");

            /*######### CANTIDADES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $X = 40;
            $Y = $pdf->GetY()+5;
            $W = 150;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $m);
            }
            
            $mx = floatval(max($cans));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $pr*$i, 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($cans[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$m.'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$cans[$i].'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>CANTIDAD DE ENTREGAS POR DÍA</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### VALORES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $m);
            }
            
            $mx = floatval(max($vals));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($vals[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$m.'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($vals[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>VALOR ($) ACUMULADO DE INGRESOS POR DÍA</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### PROMEDIOS ################*/
            
            $avg = array();
            for($i = 0; $i < $tam; $i++)
            {
                if(intval($cans[$i]) != 0)
                    $avg[] = intval(intval($vals[$i]) / intval($cans[$i]));
                else
                    $avg[] = 0;
            }
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+3, $m);
            }
            
            $mx = floatval(max($avg));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($avg[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.$m.'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($avg[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>VALOR MEDIO ($) DE INGRESOS POR ORDEN</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### RESUMEN ################*/
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            $pdf->Ln();
            
            $emin = min($cans);
            $emax = max($cans);
            $esum = array_sum($cans);
            
            $vmin = min($vals);
            $vmax = max($vals);
            $vsum = array_sum($vals);
            
            $tavg = $tam != 0 ? $this->vMoney(intval(round(floatval($vsum/$tam), 2))) : 0;
            
            $html = '<b>RESUMEN DE ENTREGAS Y VALORES ($) POR MES</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/$tam), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('ent_month_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function enYearAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getEnForYear($this, $D);
            $tam = count($data)/2;
            $vals = array_slice($data, 0, $tam);
            $cans = array_slice($data, $tam, $tam);
            $data = null;
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte De Entregas Por Año');
            $pdf->SetSubject('Reporte');
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', '');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(20, 40, 20);
            $pdf->SetHeaderMargin(2);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(TRUE, 21);
            $pdf->setImgLogo("/tmp/".$logo);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setTabl(true);
            $pdf->setMemoTitle("REPORTE DE ENTREGAS POR AÑO");

            /*######### CANTIDADES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $D</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $X = 40;
            $Y = $pdf->GetY()+5;
            $W = 150;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+3, substr($this->NM[$i], 0, 3));
            }
            
            $mx = floatval(max($cans));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $pr*$i, 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($cans[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.substr($this->NM[$i], 0, 3).'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$cans[$i].'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>CANTIDAD DE ENTREGAS POR MES</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### VALORES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $D</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+3, substr($this->NM[$i], 0, 3));
            }
            
            $mx = floatval(max($vals));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($vals[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.substr($this->NM[$i], 0, 3).'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($vals[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>VALOR ($) ACUMULADO DE INGRESOS POR MES</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### PROMEDIOS ################*/
            
            $avg = array();
            for($i = 0; $i < $tam; $i++)
            {
                if(intval($cans[$i]) != 0)
                    $avg[] = intval(intval($vals[$i]) / intval($cans[$i]));
                else
                    $avg[] = 0;
            }
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $D</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $m = ($i+1) < 10 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+3, substr($this->NM[$i], 0, 3));
            }
            
            $mx = floatval(max($avg));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($avg[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 6 != 0 ? (intval($tam/6)+1)*6 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.substr($this->NM[$i], 0, 3).'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($avg[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>VALOR MEDIO ($) DE INGRESOS POR ORDEN</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=6)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 6)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 6)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### RESUMEN ################*/
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $D</b>", 0, 1, 0, true, 'L', true);
            $pdf->Ln();
            
            $emin = min($cans);
            $emax = max($cans);
            $esum = array_sum($cans);
            
            $vmin = min($vals);
            $vmax = max($vals);
            $vsum = array_sum($vals);
            
            $tavg = $tam != 0 ? $this->vMoney(intval(round(floatval($vsum/$tam), 2))) : 0;
            
            $html = '<b>RESUMEN DE ENTREGAS Y VALORES ($) POR MES</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/$tam), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('ent_year_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function enDecaAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $MKY = intval($D)-9;
            $TD = ($MKY).' a '.($MKY+9);
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getEnForDeca($this, $D);
            $tam = count($data)/2;
            $vals = array_slice($data, 0, $tam);
            $cans = array_slice($data, $tam, $tam);
            $data = null;
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte De Entregas Por Década');
            $pdf->SetSubject('Reporte');
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', '');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(20, 40, 20);
            $pdf->SetHeaderMargin(2);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(TRUE, 21);
            $pdf->setImgLogo("/tmp/".$logo);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setTabl(true);
            $pdf->setMemoTitle("REPORTE DE ENTREGAS POR DÉCADA");

            /*######### CANTIDADES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $X = 40;
            $Y = $pdf->GetY()+5;
            $W = 150;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-4, $Y+$H+3, $MKY+$i);
            }
            
            $mx = floatval(max($cans));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $pr*$i, 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($cans[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 5 != 0 ? (intval($tam/5)+1)*5 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.($MKY+$i).'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$cans[$i].'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>CANTIDAD DE ENTREGAS POR AÑO</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=5)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 5)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 5)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### VALORES ################*/
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-4, $Y+$H+3, $MKY+$i);
            }
            
            $mx = floatval(max($vals));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($vals[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 5 != 0 ? (intval($tam/5)+1)*5 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.($MKY+$i).'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($vals[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>VALOR ($) ACUMULADO DE INGRESOS POR AÑO</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=5)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 5)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 5)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;

            /*######### PROMEDIOS ################*/
            
            $avg = array();
            for($i = 0; $i < $tam; $i++)
            {
                if(intval($cans[$i]) != 0)
                    $avg[] = intval(intval($vals[$i]) / intval($cans[$i]));
                else
                    $avg[] = 0;
            }
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY()+5;
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round(150/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-4, $Y+$H+3, $MKY+$i);
            }
            
            $mx = floatval(max($avg));
            $pr = $mx / 20.0;
            
            if($mx == 0)
                $mx = 1;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->autoCell(19, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($pr*$i)), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ((floatval($avg[$i])*140)/$mx)+7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s4);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $cbs = array();
            $dts = array();
            
            $lta = $tam % 5 != 0 ? (intval($tam/5)+1)*5 : $tam;
            
            for($i = 0; $i < $lta; $i++)
            {
                if($i < $tam)
                {
                    $cbs[] = '<th style="background-color: #B1CDFF; font-size: 0.8em;"><b>'.($MKY+$i).'</b></th>';
                    $dts[] = '<td style="font-size: 0.8em;">'.$this->vMoney(intval($avg[$i])).'</td>';
                }
                else
                {
                    $cbs[] = '<th style="background-color: #B1CDFF;"></th>';
                    $dts[] = '<td></td>';
                }
            }
            
            $pdf->SetFont('dejavusansmono','',10);
            $html = '<b>VALOR MEDIO ($) DE INGRESOS POR ORDEN</b><br />';
            $html .= '<table border="1">';
            for($i = 0; $i < $lta; $i+=5)
            {
                $html .= '<tr>'.join('', array_slice($cbs, $i, 5)).'</tr>';
                $html .= '<tr>'.join('', array_slice($dts, $i, 5)).'</tr>';
            }
            $html .= '</table>';
            $pdf->autoCell(0, 0, 20, $Y+$H+10, $html, 0, 1, 0, true, 'C', true);
            
            $cbs = null;
            $dts = null;
            
            /*######### RESUMEN ################*/
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), "<b>Periodo: $TD</b>", 0, 1, 0, true, 'L', true);
            $pdf->Ln();
            
            $emin = min($cans);
            $emax = max($cans);
            $esum = array_sum($cans);
            
            $vmin = min($vals);
            $vmax = max($vals);
            $vsum = array_sum($vals);
            
            $tavg = $tam != 0 ? $this->vMoney(intval(round(floatval($vsum/$tam), 2))) : 0;
            
            $html = '<b>RESUMEN DE ENTREGAS Y VALORES ($) POR DÉCADA</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/$tam), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('ent_deca_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    private function vMoney($v)
    {
        $s = ''.$v;
        $r = '';
        
        $l = 1;
        for($i = strlen($s)-1; $i >= 0; $i--)
        {
            if( (($l-1) % 3 == 0) && ($l-1 != 0) )
                $r = '.'.$r;
                
            $r = $s[$i].$r;
            $l+= 1;
        }
        
        return $r;
    }
}
