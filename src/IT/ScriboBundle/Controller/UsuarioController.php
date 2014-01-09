<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Usuario;

class UsuarioController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            return $this->render('ScriboBundle:Usuario:index.html.twig');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
    
    public function saveAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $per = new Usuario();
                $res = $per->save($this);
                
                if($res == -1)
                    return new Response("Imposible guardar los datos de Usuario!...");
                else if($res == 0)
                    return new Response("Nuevo registro de Usuario guardado con éxito!...");
                else
                    return new Response("Registro de Usuario actualizado con éxito!...");
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
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $per = new Usuario();
                $res = $per->enum($this);
                
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
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $per = new Usuario();
                $res = $per->get($this);
                
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
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $per = new Usuario();
                $res = $per->del($this);
                
                if($res > 0)
                    return new Response("Registro de Usuario ($res) eliminado con éxito");
                else
                    return new Response("Imposible eliminar el registro de Usuario. Integridad relacional!...");
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

