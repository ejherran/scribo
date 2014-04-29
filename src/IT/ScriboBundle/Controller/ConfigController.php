<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Config;

class ConfigController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            exec("rm -fr /tmp/sql_".Gestion::getSQLName($this)."*");
            $cnf = new Config();
            $act = $cnf->get($this, '1');
            return $this->render('ScriboBundle:Config:index.html.twig', array('act' => $act));
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
                $cnf = new Config();
                $res = $cnf->save($this);
                
                if($res == -1)
                    return new Response("Imposible guardar los cambios!...");
                else if($res == 0)
                    return new Response("Cambios guardados con éxito!...<br />Los cambios se aplicaran tras el próximo inicio de sesión!.");
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
    
    public function dSqlAction()
    {
        if(Gestion::isGrant($this, 'R'))
        {
            $file = Gestion::getSQLCopy($this);
            $response = new BinaryFileResponse($file);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, explode('/',$file)[2]);
            return $response;
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'Intento de acceso no autorizado!...');
            return $this->redirect($this->generateUrl('scribo'));
        }
    }
}

