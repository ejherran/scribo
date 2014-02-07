<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Cliente;

class ClienteController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            return $this->render('ScriboBundle:Cliente:index.html.twig');
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
                $cli = new Cliente();
                $res = $cli->save($this);
                
                if($res == -1)
                    return new Response("Imposible guardar los datos del cliente!...");
                else if($res == 0)
                    return new Response("Nuevo registro de cliente guardado con éxito!...");
                else
                    return new Response("Registro de cliente actualizado con éxito!...");
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
    
    public function enumAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $cli = new Cliente();
                $res = $cli->enum($this);
                
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
    
    public function getAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $cli = new Cliente();
                $res = $cli->get($this);
                
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
    
    public function delAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $cli = new Cliente();
                $res = $cli->del($this);
                
                if($res > 0)
                    return new Response("Registro de cliente ($res) eliminado con éxito");
                else
                    return new Response("Imposible eliminar el registro de cliente. Integridad relacional!...");
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

