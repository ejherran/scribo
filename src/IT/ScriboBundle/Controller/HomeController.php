<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Home;

class HomeController extends Controller
{
    private $tipos = array("CC"=>"C.C.","CE"=>"C.E.","NT"=>"NIT","RC"=>"R. Civil","RM"=>"R. Mercantil");
    
    public function indexAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            return $this->render('ScriboBundle:Home:index.html.twig');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function visorAction($id)
    {
        if(Gestion::isGrant($this, '*'))
        {
            return $this->render('ScriboBundle:Home:visor.html.twig', array('URL' => $this->generateUrl('scribo_home_docu', array('id' => $id))));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function listAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->getList($this);
                
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
    
    public function procAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->proc($this);
                
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
    
    public function recivAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->reciv($this);
                
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
    
    public function cicleAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->cicle($this);
                
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
    
    public function veriAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->veri($this);
                
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
    
    public function entregaAction()
    {
        if(Gestion::isGrant($this, 'C'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->entrega($this);
                
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
    
    public function ordCancelAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $obj->ordCancel($this);
                
                return new Response('O');
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
    
    public function applyperdidaAction()
    {
        if(Gestion::isGrant($this, 'I,D'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->applyPerdida($this);
                
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
    
    public function detaAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $res = $obj->deta($this);
                
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
    
    public function updateFileAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Home();
                $obj->updateFile($this);
                $res = $obj->deta($this);
                
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
        if(Gestion::isGrant($this, '*'))
        {
            $obj = new Home();
            $ent = $obj->getEntrega($this, $id);
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
            $pdf->SetTitle('Comprobante De Entregas');
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
            $pdf->setMemoTitle("COMPROBANTE DE ENTREGAS");

            $pdf->AddPage();
            
            $pdf->SetFont('dejavusansmono','',10);
            
            if($ord != null)
            {
                $firma = Gestion::creaImg($ent['signature']);
                
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
                
                $html = '<table style="text-align: left;"><tr><th><b>FIRMA DE ACEPTACIÓN:</b></th></tr><tr><th>&nbsp;</th></tr><tr><td style="border: 3px solid #000000;" align="center;"><img src="/tmp/'.$firma.'" height="55.03mm" width="73.38mm"/></td></tr></table>';
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
