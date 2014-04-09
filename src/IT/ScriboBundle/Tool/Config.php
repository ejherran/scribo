<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Config
{
    private $tipos = array("CC"=>"Cédula de Ciudadanía","CE"=>"Cédula de Extranjería","NT"=>"NIT","RC"=>"Registro Civil","RM"=>"Registro Mercantil");
    
    public function save($controller)
    {
        $type = Gestion::sqlKill($controller->getRequest()->request->get('type'));
        $document = Gestion::sqlKill($controller->getRequest()->request->get('document'));
        $name = Gestion::sqlKill($controller->getRequest()->request->get('name'));
        $contact = Gestion::sqlKill($controller->getRequest()->request->get('contact'));
        $address = Gestion::sqlKill($controller->getRequest()->request->get('address'));
        $phone = Gestion::sqlKill($controller->getRequest()->request->get('phone'));
        $web = Gestion::sqlKill($controller->getRequest()->request->get('web'));
        $mail = Gestion::sqlKill($controller->getRequest()->request->get('mail'));
        $storage = Gestion::sqlKill($controller->getRequest()->request->get('storage'));
        $report = Gestion::sqlKill($controller->getRequest()->request->get('report'));
        $logo = Gestion::sqlKill($controller->getRequest()->request->get('logo'));
        
        
        $flag = -1;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
            
                $r = mysql_query("update configuracion set type='$type', document='$document', name='$name', contact='$contact', address='$address', phone='$phone', web='$web', mail='$mail', storage='$storage', report='$report', logo='$logo' where id='1';", $con);
                if($r)
                    $flag = 0;
                else
                    Tool::getDbError($con);
                
                Tool::closeDbCon($con);
            }
        }
            
        return $flag;
    }
        
    public function get($controller, $qId = '')
    {
        if($qId == '')
            $id = Gestion::sqlKill($controller->getRequest()->request->get('id'));
        else
            $id = $qId;
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {                    
                $r = mysql_query("select * from configuracion where id='$id' limit 1;", $con);
                if($r)
                {
                    $data = mysql_fetch_assoc($r);
                    $data['xtype'] = utf8_decode($this->tipos[$data['type']]);
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($data);
    }
}
 
