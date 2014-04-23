<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Repo;

class RepoController extends Controller
{
    private $NM = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
    private $tipos = array("CC"=>"C.C.","CE"=>"C.E.","NT"=>"NIT","RC"=>"R. Civil","RM"=>"R. Mercantil");
    private $acciones = array('C' => 'CREAR', 'A' => 'ANOTAR', 'F' => 'AVANZAR', 'B' => 'RETROCEDER', 'R' => 'DEVOLVER', 'T' => 'TRANSFERIR', 'O' => 'ACEPTAR', 'X' => 'ENTREGAR');
    private $roles = array('R'=>'Administrador', 'A'=>'Asesor','P'=>'Jefe de Prensa','I'=>'Operario de Prensa','T'=>'Jefe de Acabados','D'=>'Operario de Acabados','C'=>'Entregas');
    
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R,A,C'))
        {
            return $this->render('ScriboBundle:Repo:index.html.twig');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function getOrdenAction()
    {
        if(Gestion::isGrant($this, 'R,A,C'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Repo();
                $res = $obj->filterOrden($this);
                
                return new Response($res);
            }
            else
                return $this->redirect($this->generateUrl('scribo_home'));
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
    
    public function perDayAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getPerForDay($this, $D);
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
            $pdf->SetTitle('Reporte De Pérdidas Por Día');
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
            $pdf->setMemoTitle("REPORTE DE PÉRDIDAS POR DÍA");

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
            $s3 = array('width' => 0.5, 'color' => array(255, 0, 0));
            $s4 = array('width' => 0.5, 'color' => array(0, 255, 0));
            
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
            
            $html = '<b>CANTIDAD DE PÉRDIDAS POR HORA</b><br />';
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
            
            $html = '<b>VALOR ($) ACUMULADO DE PÉRDIDAS POR HORA</b><br />';
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
            
            $html = '<b>VALOR MEDIO ($) POR PÉRDIDA</b><br />';
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
            
            $html = '<b>RESUMEN DE PÉRDIDAS Y VALORES ($) POR DÍA</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/24.0), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('per_day_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function perWeekAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getPerForWeek($this, $D);
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
            $pdf->SetTitle('Reporte De Pérdidas Por Semana');
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
            $pdf->setMemoTitle("REPORTE DE PÉRDIDAS POR SEMANA");

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
            $s3 = array('width' => 0.5, 'color' => array(255, 0, 0));
            $s4 = array('width' => 0.5, 'color' => array(0, 255, 0));
            
            
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
            
            $html = '<b>CANTIDAD DE PÉRDIDAS POR DÍA</b><br />';
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
            
            $html = '<b>VALOR ($) ACUMULADO DE PÉRDIDAS POR DÍA</b><br />';
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
            
            $html = '<b>VALOR MEDIO ($) POR PÉRDIDA</b><br />';
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
            
            $html = '<b>RESUMEN DE PÉRDIDAS Y VALORES ($) POR SEMANA</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/7.0), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('per_week_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function perMonthAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $TD = explode('-', $D);
            $TD = $this->NM[intval($TD[1])-1].' DE '.$TD[0];
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getPerForMonth($this, $D);
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
            $pdf->SetTitle('Reporte De Pérdidas Por Mes');
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
            $pdf->setMemoTitle("REPORTE DE PÉRDIDAS POR MES");

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
            $s3 = array('width' => 0.5, 'color' => array(255, 0, 0));
            $s4 = array('width' => 0.5, 'color' => array(0, 255, 0));
            
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
            $html = '<b>CANTIDAD DE PÉRDIDAS POR DÍA</b><br />';
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
            $html = '<b>VALOR ($) ACUMULADO DE PÉRDIDAS POR DÍA</b><br />';
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
            $html = '<b>VALOR MEDIO ($) POR PÉRDIDA</b><br />';
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
            
            $html = '<b>RESUMEN DE PÉRDIDAS Y VALORES ($) POR MES</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/$tam), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('per_month_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function perYearAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getPerForYear($this, $D);
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
            $pdf->SetTitle('Reporte De Pérdidas Por Año');
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
            $pdf->setMemoTitle("REPORTE DE PÉRDIDAS POR AÑO");

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
            $s3 = array('width' => 0.5, 'color' => array(255, 0, 0));
            $s4 = array('width' => 0.5, 'color' => array(0, 255, 0));
            
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
            $html = '<b>CANTIDAD DE PÉRDIDAS POR MES</b><br />';
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
            $html = '<b>VALOR ($) ACUMULADO DE PÉRDIDAS POR MES</b><br />';
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
            $html = '<b>VALOR MEDIO ($) POR PÉRDIDA</b><br />';
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
            
            $html = '<b>RESUMEN DE PÉRDIDAS Y VALORES ($) POR MES</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/$tam), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('per_year_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function perDecaAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $MKY = intval($D)-9;
            $TD = ($MKY).' a '.($MKY+9);
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            $data = $obj->getPerForDeca($this, $D);
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
            $pdf->SetTitle('Reporte De Pérdidas Por Década');
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
            $pdf->setMemoTitle("REPORTE DE PÉRDIDAS POR DÉCADA");

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
            $s3 = array('width' => 0.5, 'color' => array(255, 0, 0));
            $s4 = array('width' => 0.5, 'color' => array(0, 255, 0));
            
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
            $html = '<b>CANTIDAD DE PÉRDIDAS POR AÑO</b><br />';
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
            $html = '<b>VALOR ($) ACUMULADO DE PÉRDIDAS POR AÑO</b><br />';
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
            $html = '<b>VALOR MEDIO ($) POR PÉRDIDA</b><br />';
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
            
            $html = '<b>RESUMEN DE PÉRDIDAS Y VALORES ($) POR DÉCADA</b><br>';
            $html .= '<table border="1">';
            $html .= '<tr style="background-color: #B1CDFF; font-weight: bold;"><th></th><th>MIN</th><th>MAX</th><th>AVG</th><th>TOTAL</th></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">ENTREGAS</th><td>'.$emin.'</td><td>'.$emax.'</td><td>'.round(floatval($esum/$tam), 2).'</td><td>'.$esum.'</td></tr>';
            $html .= '<tr><th style="background-color: #B1CDFF; font-weight: bold;">VALOR ($)</th><td>'.$this->vMoney(intval($vmin)).'</td><td>'.$this->vMoney(intval($vmax)).'</td><td>'.$tavg.'</td><td>'.$this->vMoney(intval($vsum)).'</td></tr>';
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('per_deca_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function conDayAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            
            $data = $obj->getEnForDay($this, $D);
            $tam = count($data)/2;
            $evs = array_slice($data, 0, $tam);
            $ecs = array_slice($data, $tam, $tam);
            $data = null;
            
            $data = $obj->getPerForDay($this, $D);
            $pvs = array_slice($data, 0, $tam);
            $pcs = array_slice($data, $tam, $tam);
            $data = null;
            
            $bal = array();
            
            $pdf = new \Tcpdf_Tcpdf('L', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte Consolidado Por Día');
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
            $pdf->setMemoTitle("REPORTE CONSOLIDADO POR DÍA: ".$D);
            
            /* ### TABLA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<table border="1" cellpadding="3">';
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold;">';
            $html .= '<th>HORA</th><th>C. ENTREGAS</th><th>C. PÉRDIDAS</th><th>DIFERENCIA</th><th>A. ENTREGAS ($)</th><th>A. PÉRDIDAS ($)</th><th>BALANCE ($)</th>';
            $html .= '</tr>';
            
            for($i = 0; $i < $tam; $i++)
            {
                $fb = $i % 2 != 0 ? ' background-color: #DCDCDC;' : '';
                $mh = $i < 9 ? '0'.$i : ''.$i;
                
                $dic = ($ecs[$i]-$pcs[$i]);
                $dic = $dic < 0 ? '<b style="color: red;">'.$dic.'</b>' : '<b style="color: blue;">'.$dic.'</b>';
                
                $div = intval($evs[$i])-intval($pvs[$i]);
                $bal[] = $div;
                $div = $div < 0 ? '<b style="color: red;">'.$this->vMoney($div).'</b>' : '<b style="color: blue;">'.$this->vMoney($div).'</b>';
                
                $html .= '<tr style="font-weight: bold; font-size: 0.8em;'.$fb.'">';
                $html .= '<td>'.$mh.'</td><td>'.$ecs[$i].'</td><td>'.$pcs[$i].'</td><td>'.$dic.'</td><td>'.$this->vMoney(intval($evs[$i])).'</td><td>'.$this->vMoney(intval($pvs[$i])).'</td><td>'.$div.'</td>';
                $html .= '</tr>';
            }
            
            $sec = array_sum($ecs);
            $spc = array_sum($pcs);
            $sdc = $sec-$spc;
            $sdc = $sdc < 0 ? '<b style="color: red;">'.$sdc.'</b>' : '<b style="color: blue;">'.$sdc.'</b>';
            
            $sev = array_sum($evs);
            $spv = array_sum($pvs);
            $sdv = intval($sev)-intval($spv);
            $sdv = $sdv < 0 ? '<b style="color: red;">'.$this->vMoney($sdv).'</b>' : '<b style="color: blue;">'.$this->vMoney($sdv).'</b>';
            
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold; font-size: 0.8em;">';
            $html .= '<th>TOTAL</th><th>'.$sec.'</th><th>'.$spc.'</th><th>'.$sdc.'</th><th>'.$this->vMoney(intval($sev)).'</th><th>'.$this->vMoney(intval($spv)).'</th><th>'.$sdv.'</th>';
            $html .= '</tr>';
            
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            /* ### BASES ### */
            
            $X = 60;
            $Y = 0;
            $W = 200;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            /* ### GRAFICA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = $i < 9 ? '0'.$i : ''.$i;
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+2, $mh);
            }
            
            $mxe = floatval(max($evs));
            $mxe = $mxe == 0 ? 1 : $mxe;
            $mxp = floatval(max($pvs));
            $mxp = $mxp == 0 ? 1 : $mxp;
            $max = max($mxe, $mxp);
            
            $mie = floatval(min($evs));
            $mip = floatval(min($pvs));
            $min = min($mie, $mip);
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($evs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($pvs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s4);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            /* ### BALANCE ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = $i < 9 ? '0'.$i : ''.$i;
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+2, $mh);
            }
            
            $max = floatval(max($bal));
            $min = floatval(min($bal));
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            if($min < 0)
            {
                $yy = ( ( ( floatval(0) - $min ) * 140 ) / $sca ) + 7;
                $pdf->Line($X+6, ($Y+$H)-$yy, $X+$W, ($Y+$H)-$yy, $s4);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($bal[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s2);
                
                $pdf->SetLineStyle($s1);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('con_day_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function conWeekAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            
            $data = $obj->getEnForWeek($this, $D);
            $tam = (count($data)-1)/2;
            $ifa = $data[0];
            $evs = array_slice($data, 1, $tam);
            $ecs = array_slice($data, $tam+1, $tam);
            $data = null;
            
            $data = $obj->getPerForWeek($this, $D);
            $pvs = array_slice($data, 1, $tam);
            $pcs = array_slice($data, $tam+1, $tam);
            $data = null;
            
            $bal = array();
            
            $pdf = new \Tcpdf_Tcpdf('L', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte Consolidado Por Semana');
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
            $pdf->setMemoTitle("REPORTE CONSOLIDADO POR SEMANA: ".$ifa);
            
            $mkd = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB'];
            
            /* ### TABLA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<table border="1" cellpadding="3">';
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold;">';
            $html .= '<th>DIA</th><th>C. ENTREGAS</th><th>C. PÉRDIDAS</th><th>DIFERENCIA</th><th>A. ENTREGAS ($)</th><th>A. PÉRDIDAS ($)</th><th>BALANCE ($)</th>';
            $html .= '</tr>';
            
            for($i = 0; $i < $tam; $i++)
            {
                $fb = $i % 2 != 0 ? ' background-color: #DCDCDC;' : '';
                
                $dic = ($ecs[$i]-$pcs[$i]);
                $dic = $dic < 0 ? '<b style="color: red;">'.$dic.'</b>' : '<b style="color: blue;">'.$dic.'</b>';
                
                $div = intval($evs[$i])-intval($pvs[$i]);
                $bal[] = $div;
                $div = $div < 0 ? '<b style="color: red;">'.$this->vMoney($div).'</b>' : '<b style="color: blue;">'.$this->vMoney($div).'</b>';
                
                $html .= '<tr style="font-weight: bold; font-size: 0.8em;'.$fb.'">';
                $html .= '<td>'.$mkd[$i].'</td><td>'.$ecs[$i].'</td><td>'.$pcs[$i].'</td><td>'.$dic.'</td><td>'.$this->vMoney(intval($evs[$i])).'</td><td>'.$this->vMoney(intval($pvs[$i])).'</td><td>'.$div.'</td>';
                $html .= '</tr>';
            }
            
            $sec = array_sum($ecs);
            $spc = array_sum($pcs);
            $sdc = $sec-$spc;
            $sdc = $sdc < 0 ? '<b style="color: red;">'.$sdc.'</b>' : '<b style="color: blue;">'.$sdc.'</b>';
            
            $sev = array_sum($evs);
            $spv = array_sum($pvs);
            $sdv = intval($sev)-intval($spv);
            $sdv = $sdv < 0 ? '<b style="color: red;">'.$this->vMoney($sdv).'</b>' : '<b style="color: blue;">'.$this->vMoney($sdv).'</b>';
            
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold; font-size: 0.8em;">';
            $html .= '<th>TOTAL</th><th>'.$sec.'</th><th>'.$spc.'</th><th>'.$sdc.'</th><th>'.$this->vMoney(intval($sev)).'</th><th>'.$this->vMoney(intval($spv)).'</th><th>'.$sdv.'</th>';
            $html .= '</tr>';
            
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            /* ### BASES ### */
            
            $X = 60;
            $Y = 0;
            $W = 200;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            /* ### GRAFICA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+2, $mkd[$i]);
            }
            
            $mxe = floatval(max($evs));
            $mxe = $mxe == 0 ? 1 : $mxe;
            $mxp = floatval(max($pvs));
            $mxp = $mxp == 0 ? 1 : $mxp;
            $max = max($mxe, $mxp);
            
            $mie = floatval(min($evs));
            $mip = floatval(min($pvs));
            $min = min($mie, $mip);
            
            $pdf->Text(200, $Y, $mie.' '.$mip.' '.$min);
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($evs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($pvs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s4);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            /* ### BALANCE ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = $i < 9 ? '0'.$i : ''.$i;
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+2, $mkd[$i]);
            }
            
            $max = floatval(max($bal));
            $min = floatval(min($bal));
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            if($min < 0)
            {
                $yy = ( ( ( floatval(0) - $min ) * 140 ) / $sca ) + 7;
                $pdf->Line($X+6, ($Y+$H)-$yy, $X+$W, ($Y+$H)-$yy, $s4);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($bal[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s2);
                
                $pdf->SetLineStyle($s1);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('con_week_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function conMonthAction($D)
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
            $evs = array_slice($data, 0, $tam);
            $ecs = array_slice($data, $tam, $tam);
            $data = null;
            
            $data = $obj->getPerForMonth($this, $D);
            $pvs = array_slice($data, 0, $tam);
            $pcs = array_slice($data, $tam, $tam);
            $data = null;
            
            $bal = array();
            
            $pdf = new \Tcpdf_Tcpdf('L', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte Consolidado Por Mes');
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
            $pdf->setMemoTitle("REPORTE CONSOLIDADO POR MES: ".$TD);
            
            /* ### TABLA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<table border="1" cellpadding="1.9">';
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold;">';
            $html .= '<th>DIA</th><th>C. ENTREGAS</th><th>C. PÉRDIDAS</th><th>DIFERENCIA</th><th>A. ENTREGAS ($)</th><th>A. PÉRDIDAS ($)</th><th>BALANCE ($)</th>';
            $html .= '</tr>';
            
            for($i = 0; $i < $tam; $i++)
            {
                $fb = $i % 2 != 0 ? ' background-color: #DCDCDC;' : '';
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                
                $dic = ($ecs[$i]-$pcs[$i]);
                $dic = $dic < 0 ? '<b style="color: red;">'.$dic.'</b>' : '<b style="color: blue;">'.$dic.'</b>';
                
                $div = intval($evs[$i])-intval($pvs[$i]);
                $bal[] = $div;
                $div = $div < 0 ? '<b style="color: red;">'.$this->vMoney($div).'</b>' : '<b style="color: blue;">'.$this->vMoney($div).'</b>';
                
                $html .= '<tr style="font-weight: bold; font-size: 0.8em;'.$fb.'">';
                $html .= '<td>'.$mh.'</td><td>'.$ecs[$i].'</td><td>'.$pcs[$i].'</td><td>'.$dic.'</td><td>'.$this->vMoney(intval($evs[$i])).'</td><td>'.$this->vMoney(intval($pvs[$i])).'</td><td>'.$div.'</td>';
                $html .= '</tr>';
            }
            
            $sec = array_sum($ecs);
            $spc = array_sum($pcs);
            $sdc = $sec-$spc;
            $sdc = $sdc < 0 ? '<b style="color: red;">'.$sdc.'</b>' : '<b style="color: blue;">'.$sdc.'</b>';
            
            $sev = array_sum($evs);
            $spv = array_sum($pvs);
            $sdv = intval($sev)-intval($spv);
            $sdv = $sdv < 0 ? '<b style="color: red;">'.$this->vMoney($sdv).'</b>' : '<b style="color: blue;">'.$this->vMoney($sdv).'</b>';
            
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold; font-size: 0.8em;">';
            $html .= '<th>TOTAL</th><th>'.$sec.'</th><th>'.$spc.'</th><th>'.$sdc.'</th><th>'.$this->vMoney(intval($sev)).'</th><th>'.$this->vMoney(intval($spv)).'</th><th>'.$sdv.'</th>';
            $html .= '</tr>';
            
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            /* ### BASES ### */
            
            $X = 60;
            $Y = 0;
            $W = 200;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            /* ### GRAFICA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+2, $mh);
            }
            
            $mxe = floatval(max($evs));
            $mxe = $mxe == 0 ? 1 : $mxe;
            $mxp = floatval(max($pvs));
            $mxp = $mxp == 0 ? 1 : $mxp;
            $max = max($mxe, $mxp);
            
            $mie = floatval(min($evs));
            $mip = floatval(min($pvs));
            $min = min($mie, $mip);
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($evs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($pvs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s4);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            /* ### BALANCE ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-2.5, $Y+$H+2, $mh);
            }
            
            $max = floatval(max($bal));
            $min = floatval(min($bal));
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            if($min < 0)
            {
                $yy = ( ( ( floatval(0) - $min ) * 140 ) / $sca ) + 7;
                $pdf->Line($X+6, ($Y+$H)-$yy, $X+$W, ($Y+$H)-$yy, $s4);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($bal[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s2);
                
                $pdf->SetLineStyle($s1);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('con_month_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function conYearAction($D)
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $obj = new Repo();
            
            $data = $obj->getEnForYear($this, $D);
            $tam = count($data)/2;
            $evs = array_slice($data, 0, $tam);
            $ecs = array_slice($data, $tam, $tam);
            $data = null;
            
            $data = $obj->getPerForYear($this, $D);
            $pvs = array_slice($data, 0, $tam);
            $pcs = array_slice($data, $tam, $tam);
            $data = null;
            
            $bal = array();
            
            $pdf = new \Tcpdf_Tcpdf('L', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte Consolidado Por Año');
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
            $pdf->setMemoTitle("REPORTE CONSOLIDADO POR AÑO: ".$D);
            
            /* ### TABLA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<table border="1" cellpadding="3">';
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold;">';
            $html .= '<th>MES</th><th>C. ENTREGAS</th><th>C. PÉRDIDAS</th><th>DIFERENCIA</th><th>A. ENTREGAS ($)</th><th>A. PÉRDIDAS ($)</th><th>BALANCE ($)</th>';
            $html .= '</tr>';
            
            for($i = 0; $i < $tam; $i++)
            {
                $fb = $i % 2 != 0 ? ' background-color: #DCDCDC;' : '';
                
                $dic = ($ecs[$i]-$pcs[$i]);
                $dic = $dic < 0 ? '<b style="color: red;">'.$dic.'</b>' : '<b style="color: blue;">'.$dic.'</b>';
                
                $div = intval($evs[$i])-intval($pvs[$i]);
                $bal[] = $div;
                $div = $div < 0 ? '<b style="color: red;">'.$this->vMoney($div).'</b>' : '<b style="color: blue;">'.$this->vMoney($div).'</b>';
                
                $html .= '<tr style="font-weight: bold; font-size: 0.8em;'.$fb.'">';
                $html .= '<td>'.$this->NM[$i].'</td><td>'.$ecs[$i].'</td><td>'.$pcs[$i].'</td><td>'.$dic.'</td><td>'.$this->vMoney(intval($evs[$i])).'</td><td>'.$this->vMoney(intval($pvs[$i])).'</td><td>'.$div.'</td>';
                $html .= '</tr>';
            }
            
            $sec = array_sum($ecs);
            $spc = array_sum($pcs);
            $sdc = $sec-$spc;
            $sdc = $sdc < 0 ? '<b style="color: red;">'.$sdc.'</b>' : '<b style="color: blue;">'.$sdc.'</b>';
            
            $sev = array_sum($evs);
            $spv = array_sum($pvs);
            $sdv = intval($sev)-intval($spv);
            $sdv = $sdv < 0 ? '<b style="color: red;">'.$this->vMoney($sdv).'</b>' : '<b style="color: blue;">'.$this->vMoney($sdv).'</b>';
            
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold; font-size: 0.8em;">';
            $html .= '<th>TOTAL</th><th>'.$sec.'</th><th>'.$spc.'</th><th>'.$sdc.'</th><th>'.$this->vMoney(intval($sev)).'</th><th>'.$this->vMoney(intval($spv)).'</th><th>'.$sdv.'</th>';
            $html .= '</tr>';
            
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            /* ### BASES ### */
            
            $X = 60;
            $Y = 0;
            $W = 200;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            /* ### GRAFICA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+2, substr($this->NM[$i], 0, 3));
            }
            
            $mxe = floatval(max($evs));
            $mxe = $mxe == 0 ? 1 : $mxe;
            $mxp = floatval(max($pvs));
            $mxp = $mxp == 0 ? 1 : $mxp;
            $max = max($mxe, $mxp);
            
            $mie = floatval(min($evs));
            $mip = floatval(min($pvs));
            $min = min($mie, $mip);
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($evs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($pvs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s4);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            /* ### BALANCE ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+2, substr($this->NM[$i], 0, 3));
            }
            
            $max = floatval(max($bal));
            $min = floatval(min($bal));
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            if($min < 0)
            {
                $yy = ( ( ( floatval(0) - $min ) * 140 ) / $sca ) + 7;
                $pdf->Line($X+6, ($Y+$H)-$yy, $X+$W, ($Y+$H)-$yy, $s4);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($bal[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s2);
                
                $pdf->SetLineStyle($s1);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('con_year_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function conDecaAction($D)
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
            $evs = array_slice($data, 0, $tam);
            $ecs = array_slice($data, $tam, $tam);
            $data = null;
            
            $data = $obj->getPerForDeca($this, $D);
            $pvs = array_slice($data, 0, $tam);
            $pcs = array_slice($data, $tam, $tam);
            $data = null;
            
            $bal = array();
            
            $pdf = new \Tcpdf_Tcpdf('L', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte Consolidado Por Década');
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
            $pdf->setMemoTitle("REPORTE CONSOLIDADO POR DÉCADA: ".$TD);
            
            /* ### TABLA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',10);
            
            $html = '<table border="1" cellpadding="3">';
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold;">';
            $html .= '<th>AÑO</th><th>C. ENTREGAS</th><th>C. PÉRDIDAS</th><th>DIFERENCIA</th><th>A. ENTREGAS ($)</th><th>A. PÉRDIDAS ($)</th><th>BALANCE ($)</th>';
            $html .= '</tr>';
            
            for($i = 0; $i < $tam; $i++)
            {
                $fb = $i % 2 != 0 ? ' background-color: #DCDCDC;' : '';
                
                $dic = ($ecs[$i]-$pcs[$i]);
                $dic = $dic < 0 ? '<b style="color: red;">'.$dic.'</b>' : '<b style="color: blue;">'.$dic.'</b>';
                
                $div = intval($evs[$i])-intval($pvs[$i]);
                $bal[] = $div;
                $div = $div < 0 ? '<b style="color: red;">'.$this->vMoney($div).'</b>' : '<b style="color: blue;">'.$this->vMoney($div).'</b>';
                
                $html .= '<tr style="font-weight: bold; font-size: 0.8em;'.$fb.'">';
                $html .= '<td>'.($MKY+$i).'</td><td>'.$ecs[$i].'</td><td>'.$pcs[$i].'</td><td>'.$dic.'</td><td>'.$this->vMoney(intval($evs[$i])).'</td><td>'.$this->vMoney(intval($pvs[$i])).'</td><td>'.$div.'</td>';
                $html .= '</tr>';
            }
            
            $sec = array_sum($ecs);
            $spc = array_sum($pcs);
            $sdc = $sec-$spc;
            $sdc = $sdc < 0 ? '<b style="color: red;">'.$sdc.'</b>' : '<b style="color: blue;">'.$sdc.'</b>';
            
            $sev = array_sum($evs);
            $spv = array_sum($pvs);
            $sdv = intval($sev)-intval($spv);
            $sdv = $sdv < 0 ? '<b style="color: red;">'.$this->vMoney($sdv).'</b>' : '<b style="color: blue;">'.$this->vMoney($sdv).'</b>';
            
            $html .= '<tr style="background-color: #ADD8E6; font-weight: bold; font-size: 0.8em;">';
            $html .= '<th>TOTAL</th><th>'.$sec.'</th><th>'.$spc.'</th><th>'.$sdc.'</th><th>'.$this->vMoney(intval($sev)).'</th><th>'.$this->vMoney(intval($spv)).'</th><th>'.$sdv.'</th>';
            $html .= '</tr>';
            
            $html .= '</table>';
            
            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
            
            /* ### BASES ### */
            
            $X = 60;
            $Y = 0;
            $W = 200;
            $H = 150;
            
            $s1 = array('width' => 1, 'color' => array(0, 0, 0));
            $s2 = array('width' => 0.5, 'color' => array(0, 0, 255));
            $s3 = array('width' => 0.5, 'color' => array(0, 255, 0));
            $s4 = array('width' => 0.5, 'color' => array(255, 0, 0));
            
            /* ### GRAFICA ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+2, ($MKY+$i));
            }
            
            $mxe = floatval(max($evs));
            $mxe = $mxe == 0 ? 1 : $mxe;
            $mxp = floatval(max($pvs));
            $mxp = $mxp == 0 ? 1 : $mxp;
            $max = max($mxe, $mxp);
            
            $mie = floatval(min($evs));
            $mip = floatval(min($pvs));
            $min = min($mie, $mip);
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($evs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s3);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($pvs[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s4);
                
                $pdf->SetLineStyle($s2);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            /* ### BALANCE ### */
            $pdf->AddPage();
            $pdf->SetFont('dejavusansmono','',6);
            
            $Y = $pdf->GetY();
            
            $pdf->Line($X, $Y+$H, $X+$W, $Y+$H, $s1);
            $pdf->Line($X, $Y+$H, $X, $Y, $s1);
            
            $mov = round($W/$tam, 1)-0.1;
            for($i = 0; $i < $tam; $i++)
            {
                $mh = ($i+1) < 9 ? '0'.($i+1) : ''.($i+1);
                $p = 6+($mov*($i));
                $pdf->Line($X+$p, $Y+$H+2, $X+$p, $Y+$H-2, $s2);
                $pdf->Text($X+$p-3, $Y+$H+2, ($MKY+$i));
            }
            
            $max = floatval(max($bal));
            $min = floatval(min($bal));
            
            $sca = $max - $min;
            $sca = $sca == 0 ? 1 : $sca;
            $pr = $sca / 20.0;
            
            for($i = 0; $i < 21; $i++)
            {
                $p = 7*($i+1);
                $pdf->Line($X-2, $Y+$H-$p, $X+2, $Y+$H-$p, $s2);
                $pdf->SetY($Y);
                $pdf->autoCell(39, 0, 20, $Y+$H-$p-2, $this->vMoney(intval($min+($pr*$i))), 0, 1, 0, true, 'R', true);
            }
            
            if($min < 0)
            {
                $yy = ( ( ( floatval(0) - $min ) * 140 ) / $sca ) + 7;
                $pdf->Line($X+6, ($Y+$H)-$yy, $X+$W, ($Y+$H)-$yy, $s4);
            }
            
            $ox = 0;
            $oy = 0;
            for($i = 0; $i < $tam; $i++)
            {
                $xx = 6+($mov*($i));
                $yy = ( ( ( floatval($bal[$i]) - $min ) * 140 ) / $sca ) + 7;
                
                if($i > 0)
                    $pdf->Line($X+$ox, ($Y+$H)-$oy, $X+$xx, ($Y+$H)-$yy, $s2);
                
                $pdf->SetLineStyle($s1);
                $pdf->Circle($X+$xx, ($Y+$H)-$yy, 0.6);
                
                $ox = $xx;
                $oy = $yy;
            }
            
            unlink("/tmp/".$logo);
            
            $pdf->Output('con_deca_report.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function logCatAction($id)
    {
        if(Gestion::isGrant($this, 'R,A,C'))
        {
            $obj = new Repo();
            $ord = $obj->getOrder($this, $id);
            $firmac = null;
            $firmae = null;
            
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Reporte Procesos Por Orden');
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
            $pdf->setMemoTitle("REPORTE DE PROCESOS POR ORDEN: ".$id);
            
            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            
            if($ord != null)
            {
                $firmac = Gestion::creaImg($ord['signature']);
                
                $per = $obj->getPersonal($this, $ord['usuario_id']);
                $cli = $obj->getCliente($this, $ord['cliente_id']);
                
                $html = '<table>';
                $html .= '<tr><td style="text-align: left;"><b>Fecha De Registro: '.$ord['date'].'</b></td><td style="text-align: right;"><b>Nº: '.$ord['id'].'</b></td></tr>';
                $html .= '<tr><td style="text-align: left;"><b>Generado Por: '.$per['surname'].' '.$per['name'].'</b></td><td style="text-align: right;"><b>Doc.: '.$per['document'].'</b></td></tr>';
                $html .= '</table><br /><br />';
                $html .= '<b>DATOS DEL CLIENTE</b><br /><br />';
                $html .= '<table border="1" style="text-align: left;">';
                $html .= '<tr><td colspan="2" style="text-align: center;"><b>'.$cli['name'].'</b></td><td colspan="2" style="text-align: center;"><b>'.$this->tipos[$cli['type']].': '.$cli['document'].'</b></td></tr>';
                $html .= '<tr><th style="width: 15%;"><b>Contacto</b></th><td style="width: 35%;">'.$cli['contact'].'</td><th style="width: 15%;"><b>Diección</b></th><td style="width: 35%;">'.$cli['address'].'</td></tr>';
                $html .= '<tr><th style="width: 15%;"><b>Telefono</b></th><td style="width: 35%;">'.$cli['phone'].'</td><th style="widh: 15%;"><b>Mail</b></th><td style="width: 35%;">'.$cli['mail'].'</td></tr>';
                $html .= '</table>';
                $html .= '<br /><br /><b>DATOS DE EJECUCIÓN</b><br /><br />';
                $html .= '<table border="1">';
                $html .= '<tr style="font-weight: bold;"><th>TIEMPO</th><th>SUBTOTAL</th><th>IVA</th><th>TOTAL</th></tr>';
                $html .= '<tr><td>'.$ord['time'].' Hr.</td><td>$ '.floatval($ord['subtotal']).'</td><td>$ '.((floatval($ord['iva'])/100)*floatval($ord['subtotal'])).'</td><td>$ '.floatval($ord['total']).'</td></tr>';
                $html .= '</table>';
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                $pdf->Ln(5);
                
                if($ord['type'] == 'A')
                {
                    $itm = $obj->getItemsA($this, $id);
                    if($itm != null && is_array($itm))
                    {
                        $html = '<b>DETALLE DE ITEMS</b><br /><br />';
                        
                        $html .= '<table border="1" style="font-weight: bold; background-color: #D6D9F4;">';
                        $html .= '<tr><td>FICHERO</td><td>EXPIRACIÓN</td><td>MATERIAL</td><td>TINTA</td></tr>';
                        $html .= '<tr><td>PAGINAS</td><td>CANTIDAD</td><td>V. UNITARIO</td><td>VALOR</td></tr>';
                        $html .= '<tr><td colspan="4">ACABADOS</td></tr>';
                        $html .= '<tr><td colspan="4">NOTAS</td></tr>';
                        $html .= '</table>';
                        $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                        $pdf->Ln(5);
                        
                        foreach($itm as $it)
                        {
                            $iac = $obj->getAcabadosA($this, $it['idx']);
                            $iac = $iac != null ? $iac : 'Sin Acabados!';
                            $ino = $it['notas'] != '' ? $it['notas'] : 'Sin Notas!';
                            $cad = $it['caduca'] != '@' ? $it['caduca'] : 'Al Entregar!';
                            
                            $html = '<table border="1">';
                            $html .= '<tr><td>'.$it['fichero'].'</td><td>'.$cad.'</td><td>'.$it['material'].'</td><td>'.$it['tinta'].'</td></tr>';
                            $html .= '<tr><td>'.$it['paginas'].'</td><td>'.$it['cantidad'].'</td><td>$ '.floatval($it['unitario']).'</td><td>$ '.floatval($it['valor']).'</td></tr>';
                            $html .= '<tr><td colspan="4">'.$iac.'</td></tr>';
                            $html .= '<tr><td colspan="4">'.$ino.'</td></tr>';
                            $html .= '</table>';
                            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                            $pdf->Ln(5);
                        }
                    }
                }
                else if($ord['type'] == 'B')
                {
                    $itm = $obj->getItemsB($this, $id);
                    if($itm != null && is_array($itm))
                    {
                        $html = '<b>DETALLE DE ITEMS</b><br /><br />';
                        
                        $html .= '<table border="1" style="font-weight: bold; background-color: #D6D9F4;">';
                        $html .= '<tr><td>FICHERO</td><td>EXPIRACIÓN</td><td>MATERIAL</td><td>TINTA</td></tr>';
                        $html .= '<tr><td>DIMENSIONES (cm)</cm></td><td>CANTIDAD</td><td>V. UNITARIO</td><td>VALOR</td></tr>';
                        $html .= '<tr><td colspan="4">ACABADOS</td></tr>';
                        $html .= '<tr><td colspan="4">NOTAS</td></tr>';
                        $html .= '</table>';
                        $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                        $pdf->Ln(5);
                        
                        foreach($itm as $it)
                        {
                            $iac = $obj->getAcabadosB($this, $it['idx']);
                            $iac = $iac != null ? $iac : 'Sin Acabados!';
                            $ino = $it['notas'] != '' ? $it['notas'] : 'Sin Notas!';
                            $cad = $it['caduca'] != '@' ? $it['caduca'] : 'Al Entregar!';
                            
                            $html = '<table border="1">';
                            $html .= '<tr><td>'.$it['fichero'].'</td><td>'.$cad.'</td><td>'.$it['material'].'</td><td>'.$it['tinta'].'</td></tr>';
                            $html .= '<tr><td>'.$it['ancho'].'x'.$it['largo'].'</td><td>'.$it['cantidad'].'</td><td>$ '.floatval($it['unitario']).'</td><td>$ '.floatval($it['valor']).'</td></tr>';
                            $html .= '<tr><td colspan="4">'.$iac.'</td></tr>';
                            $html .= '<tr><td colspan="4">'.$ino.'</td></tr>';
                            $html .= '</table>';
                            $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                            $pdf->Ln(5);
                        }
                    }
                }
                
                $pdf->Ln(5);
                $html = '<table style="text-align: left;"><tr><th><b>OBSERVACIONES:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td>'.$ord['data'].'</td></tr></table><br /><br /><br />';
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                
                $html = '<table style="text-align: left;"><tr><th><b>FIRMA DE ACEPTACIÓN:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td style="border: 3px solid #000000;" align="center;"><img src="/tmp/'.$firmac.'" height="55.03mm" width="73.38mm"/></td></tr></table>';
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true, 90);
                
                if($ord['status'] == 'X')
                {
                    $ent = $obj->getEntrega($this, $id);
                    
                    $pdf->AddPage();
                    $pdf->SetFont('dejavusansmono','',10);
                    
                    $firmae = Gestion::creaImg($ent['signature']);
                
                    $per = $obj->getPersonal($this, $ent['usuario_id']);
                    $cli = $obj->getCliente($this, $ord['cliente_id']);
                    
                    $html = '<table>';
                    $html .= '<tr><td style="text-align: left;"><b>Fecha De Entrega: '.$ent['date'].'</b></td><td style="text-align: right;"><b>Nº: '.$ord['id'].'</b></td></tr>';
                    $html .= '<tr><td style="text-align: left;"><b>Entregado Por: '.$per['surname'].' '.$per['name'].'</b></td><td style="text-align: right;"><b>Doc.: '.$per['document'].'</b></td></tr>';
                    $html .= '</table><br /><br />';
                    $html .= '<b>DATOS DEL CLIENTE</b><br /><br />';
                    $html .= '<table border="1" style="text-align: left;">';
                    $html .= '<tr><td colspan="2" style="text-align: center;"><b>'.$cli['name'].'</b></td><td colspan="2" style="text-align: center;"><b>'.$this->tipos[$cli['type']].': '.$cli['document'].'</b></td></tr>';
                    $html .= '<tr><th style="width: 15%;"><b>Contacto</b></th><td style="width: 35%;">'.$cli['contact'].'</td><th style="width: 15%;"><b>Diección</b></th><td style="width: 35%;">'.$cli['address'].'</td></tr>';
                    $html .= '<tr><th style="width: 15%;"><b>Telefono</b></th><td style="width: 35%;">'.$cli['phone'].'</td><th style="widh: 15%;"><b>Mail</b></th><td style="width: 35%;">'.$cli['mail'].'</td></tr>';
                    $html .= '</table>';
                    $html .= '<br /><br /><b>DATOS DE EJECUCIÓN</b><br /><br />';
                    $html .= '<table border="1">';
                    $html .= '<tr style="font-weight: bold;"><th>TIEMPO</th><th>SUBTOTAL</th><th>IVA</th><th>TOTAL</th></tr>';
                    $html .= '<tr><td>'.$ord['time'].' Hr.</td><td>$ '.floatval($ord['subtotal']).'</td><td>$ '.((floatval($ord['iva'])/100)*floatval($ord['subtotal'])).'</td><td>$ '.floatval($ord['total']).'</td></tr>';
                    $html .= '</table>';
                    $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                    $pdf->Ln(5);
                    
                    $html = '<table style="text-align: left;"><tr><th><b>OBSERVACIONES:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td>'.$ent['data'].'</td></tr></table><br /><br /><br />';
                    $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                    
                    $html = '<table style="text-align: left;"><tr><th><b>FIRMA DE ACEPTACIÓN:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td style="border: 3px solid #000000;" align="center;"><img src="/tmp/'.$firmae.'" height="55.03mm" width="73.38mm"/></td></tr></table>';
                    $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true, 90);
                }
                
                $log = $obj->logCat($this, $id);
                if($log != '')
                {
                    $log = explode('|:|', $log);
                    $pdf->AddPage();
                    $pdf->SetFont('dejavusansmono','',8);
                    
                    $html = '<table border="1" style="background-color: #D6D9F4; font-weight: bold;">';
                        
                    $html .= '<tr>';
                    $html .= '<td colspan="2">PID</td>';
                    $html .= '<td colspan="2">FECHA</td>';
                    $html .= '</tr>';
                    
                    $html .= '<tr>';
                    $html .= '<td>R. EMITE</td>';
                    $html .= '<td>U. Emite</td>';
                    $html .= '<td colspan="2">P. EMITE</td>';
                    $html .= '</tr>';
                    
                    $html .= '<tr>';
                    $html .= '<td>R. RECIBE</td>';
                    $html .= '<td>U. RECIBE</td>';
                    $html .= '<td colspan="2">P. RECIBE</td>';
                    $html .= '</tr>';
                    
                    $html .= '<tr>';
                    $html .= '<td colspan="2">ESTADO</td>';
                    $html .= '<td colspan="2">ACCIÓN</td>';
                    $html .= '</tr>';
                    
                    $html .= '<tr>';
                    $html .= '<td colspan="4">OBSERVACIONES</td>';
                    $html .= '</tr>';
                    
                    $html .= '</table>';
                    
                    $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                    $pdf->Ln(5);
                    
                    foreach($log as $l)
                    {
                        $pls = explode('=>', $l);
                        
                        $html = '<table border="1">';
                        
                        $html .= '<tr style="background-color: #D6D9F4; font-weight: bold;">';
                        $html .= '<td colspan="2">'.$pls[0].'</td>';
                        $html .= '<td colspan="2">'.$pls[1].'</td>';
                        $html .= '</tr>';
                        
                        $html .= '<tr>';
                        $html .= '<td>'.$this->roles[$pls[2]].'</td>';
                        $html .= '<td>'.$pls[3].'</td>';
                        $html .= '<td colspan="2">'.$pls[4].'</td>';
                        $html .= '</tr>';
                        
                        $html .= '<tr>';
                        $html .= '<td>'.$this->roles[$pls[5]].'</td>';
                        $html .= '<td>'.$pls[6].'</td>';
                        $html .= '<td colspan="2">'.$pls[7].'</td>';
                        $html .= '</tr>';
                        
                        $html .= '<tr>';
                        $html .= '<td colspan="2">'.($pls[8] == 'C' ? 'CERRADO' : 'ABIERTO').'</td>';
                        $html .= '<td colspan="2">'.$this->acciones[$pls[9]].'</td>';
                        $html .= '</tr>';
                        
                        $html .= '<tr>';
                        $html .= '<td colspan="4">'.$pls[10].'</td>';
                        $html .= '</tr>';
                        
                        $html .= '</table>';
                        
                        $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                        $pdf->Ln(5);
                    }
                }
            }
            else
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), '<b>NO EXISTEN DATOS ASOCIADOS!.</b>', 0, 1, 0, true, 'C', true);
            
            unlink("/tmp/".$logo);
            if($firmac != null)
                unlink("/tmp/".$firmac);
            if($firmae != null)
                unlink("/tmp/".$firmae);
            
            $pdf->Output('logcat.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    private function vMoney($v)
    {
        $sig = '';
        if($v < 0)
        {
            $sig = '-';
            $v = -1*$v;
        }
        
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
        
        return $sig.$r;
    }
}
