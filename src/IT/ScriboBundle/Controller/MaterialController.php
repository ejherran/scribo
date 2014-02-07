<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Material;

class MaterialController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            return $this->render('ScriboBundle:Material:index.html.twig');
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
                $mat = new Material();
                $res = $mat->save($this);
                
                if($res == -1)
                    return new Response("Imposible guardar los datos del material!...");
                else if($res == 0)
                    return new Response("Nuevo registro de material guardado con éxito!...");
                else
                    return new Response("Registro de material actualizado con éxito!...");
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
                $mat = new Material();
                $res = $mat->enum($this);
                
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
                $mat = new Material();
                $res = $mat->get($this);
                
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
                $mat= new Material();
                $res = $mat->del($this);
                
                if($res > 0)
                    return new Response("Registro de material ($res) eliminado con éxito");
                else
                    return new Response("Imposible eliminar el registro de material. Integridad relacional!...");
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

