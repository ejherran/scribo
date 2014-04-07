<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Uno;

class UnoController extends Controller
{
    private $tipos = array("CC"=>"C.C.","CE"=>"C.E.","NT"=>"NIT","RC"=>"R. Civil","RM"=>"R. Mercantil");
    
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            return $this->render('ScriboBundle:Uno:index.html.twig');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function visorAction($id)
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            return $this->render('ScriboBundle:Uno:visor.html.twig', array('URL' => $this->generateUrl('scribo_uno_docu', array('id' => $id))));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function fcliAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Uno();
                $res = $obj->findCli($this);
                
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
    
    public function fmateAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Uno();
                $res = $obj->findMate($this);
                
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
    
    public function ftintaAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Uno();
                $res = $obj->findTinta($this);
                
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
    
    public function facabadoAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Uno();
                $res = $obj->findAcabado($this);
                
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
    
    public function saveAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Uno();
                $res = $obj->save($this);
                
                
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
    
    
    
    public function docuAction($id)
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $obj = new Uno();
            $ord = $obj->getOrder($this, $id);
            $firma = null;
            
            $cfg = Gestion::getConfiguracion($this);
            $logo = Gestion::creaImg($cfg['logo']);
            
            $pdf = new \Tcpdf_Tcpdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
            $pdf->emp_name = $cfg['name'];
            $pdf->emp_reg = $cfg['type'].': '.$cfg['document'];
            $pdf->emp_ubi = $cfg['address'].' - '.$cfg['phone'];
            $pdf->emp_web = $cfg['web'].' - '.$cfg['mail'];
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT Sribo');
            $pdf->SetTitle('Comprobante De Ordenes Tipo I');
            $pdf->SetSubject('Comprobante');
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
            $pdf->setMemoTitle("COMPROBANTE DE ORDENES TIPO I");

            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            
            if($ord != null)
            {
                $firma = Gestion::creaImg($ord['signature']);
                
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
                
                $itm = $obj->getItems($this, $id);
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
                        $iac = $obj->getAcabados($this, $it['idx']);
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
                
                $pdf->Ln(5);
                $html = '<table style="text-align: left;"><tr><th><b>OBSERVACIONES:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td>'.$ord['data'].'</td></tr></table><br /><br /><br />';
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true);
                
                $html = '<table style="text-align: left;"><tr><th><b>FIRMA DE ACEPTACIÓN:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td style="border: 3px solid #000000;"><img src="/tmp/'.$firma.'" height="55.03mm" width="73.38mm"/></td></tr></table>';
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), $html, 0, 1, 0, true, 'C', true, 90);
            }
            else
                $pdf->autoCell(0, 0, 20, $pdf->GetY(), '<b>NO EXISTEN DATOS ASOCIADOS!.</b>', 0, 1, 0, true, 'C', true);
            
            
            unlink("/tmp/".$logo);
            if($firma != null)
                unlink("/tmp/".$firma);
            
            $pdf->Output('comprobante.pdf', 'I');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
}

