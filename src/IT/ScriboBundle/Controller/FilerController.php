<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Config;

class FilerController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            $cnf = new Config();
            $act = $cnf->get($this, '1');
            return $this->render('ScriboBundle:Filer:index.html.twig', array('act' => $act));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
}

