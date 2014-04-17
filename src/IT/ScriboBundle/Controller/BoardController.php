<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Board;

class BoardController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, '*'))
        {
            return $this->render('ScriboBundle:Board:index.html.twig');
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
                $obj = new Board();
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
}
