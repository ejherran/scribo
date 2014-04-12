<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Home;

class HomeController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            $obj = new Home();
            $lis = $obj->getList($this);
            return $this->render('ScriboBundle:Home:index.html.twig', array('lis' => $lis));
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
}
