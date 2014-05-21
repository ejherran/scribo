<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;

class DefaultController extends Controller
{
    private $roles = array('R'=>'Administrador', 'F'=>'Facturación', 'A'=>'Asesor','P'=>'Jefe de Prensa','I'=>'Operario de Prensa','T'=>'Jefe de Acabados','D'=>'Operario de Acabados','C'=>'Entregas');
    
    public function indexAction()
    {
        $session = $this->getRequest()->getSession();
        $session->clear();
        return $this->render('ScriboBundle:Default:index.html.twig');
    }
    
    public function loginAction()
    {
        $request = $this->getRequest();
        $domain = $request->request->get('domain');
        $user = $request->request->get('user');
        $pass = hash("sha512", base64_encode($request->request->get('pass')));
        $session = $request->getSession();
        
        $role = Gestion::isUser($domain, $user, $pass);
        
        if($role == '_')
        {
            $this->get('session')->getFlashBag()->add('notice', 'El dominio indicado no presenta una "LICENCIA" válida para el servicio "SCRIBO"...');
            return $this->redirect($this->generateUrl('scribo'));
        }
        else if($role == '-')
        {
            $this->get('session')->getFlashBag()->add('notice', 'Imposible conectar con el repositorio del dominio "'.strtoupper($domain).'"...');
            return $this->redirect($this->generateUrl('scribo'));
        }
        else if($role == '')
        {
            $this->get('session')->getFlashBag()->add('notice', 'Las credenciales de acceso no son válidas!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
        else
        {
            $session->set("userActive", base64_encode($domain."|:|".$user."|:|".$role));
            $session->set("quick", $role);
            $session->set("qname", $this->roles[$role]);
            $session->set("storage", Gestion::getStorage($domain));
            return $this->redirect($this->generateUrl('scribo_home'));
        }
    }
    
    public function logoutAction()
    {
        $this->get('session')->getFlashBag()->add('notice', 'Sesión terminada con éxito!...');
        return $this->redirect($this->generateUrl('scribo'));
    }
    
    public function chpassAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $oldpass = hash("sha512", base64_encode($request->request->get('wgOldpass')));
                $newpass = hash("sha512", base64_encode($request->request->get('wgPass')));
                
                if(Gestion::chPass($this, $oldpass, $newpass))
                    return new Response("Contraseña actualizada con éxito!");
                else
                    return new Response("Error al actualizar la contraseña!");
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
}
