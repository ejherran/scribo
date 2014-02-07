<?php

namespace IT\ScriboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IT\ScriboBundle\Tool\Gestion;
use IT\ScriboBundle\Tool\Acabado;

class AcabadoController extends Controller
{
    public function indexAction()
    {
        if(Gestion::isGrant($this, 'R,A'))
        {
            return $this->render('ScriboBundle:Acabado:index.html.twig');
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
                $aca = new Acabado();
                $res = $aca->save($this);
                
                if($res == -1)
                    return new Response("Imposible guardar los datos del acabado!...");
                else if($res == 0)
                    return new Response("Nuevo registro de acabado guardado con éxito!...");
                else
                    return new Response("Registro de acabado actualizado con éxito!...");
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
                $aca = new Acabado();
                $res = $aca->enum($this);
                
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
                $aca = new Acabado();
                $res = $aca->get($this);
                
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
                $aca = new Acabado();
                $res = $aca->del($this);
                
                if($res > 0)
                    return new Response("Registro de acabado ($res) eliminado con éxito");
                else
                    return new Response("Imposible eliminar el registro de acabado. Integridad relacional!...");
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

