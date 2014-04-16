<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Filer;

class FilerController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            return $this->render('ScriboBundle:Filer:index.html.twig');
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
                $obj = new Filer();
                $obj->checkExpiry($this);
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
    
    public function purgeAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Filer();
                $res = $obj->purge($this);
                
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
    
    public function updateAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Filer();
                $res = $obj->update($this);
                
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
    
    public function deleteAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $request = $this->getRequest();
			if($request->isXmlHttpRequest())
			{
                $obj = new Filer();
                $res = $obj->delete($this);
                
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
}

