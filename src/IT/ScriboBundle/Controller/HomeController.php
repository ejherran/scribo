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
}
