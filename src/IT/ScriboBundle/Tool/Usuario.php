<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Usuario
{
    private $roles = array('R'=>'Administrador', 'A'=>'Asesor','P'=>'Jefe de Prensa','I'=>'Operario de Prensa','T'=>'Jefe de Acabados','D'=>'Operario de Acabados','C'=>'Entregas');
    
    public function save($controller)
    {
        $id = Gestion::sqlKill($controller->getRequest()->request->get('id'));
        $personalId = Gestion::sqlKill($controller->getRequest()->request->get('personal_id'));
        $role = Gestion::sqlKill($controller->getRequest()->request->get('role'));
        $user = Gestion::sqlKill($controller->getRequest()->request->get('user'));
        $pass = hash("sha512", base64_encode(Gestion::sqlKill($controller->getRequest()->request->get('pass'))));
        $data = Gestion::sqlKill($controller->getRequest()->request->get('data'));
        
        $flag = -1;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                if($id == '')
                {
                    $id = '0';
                    $r = mysql_query("insert into usuario values ('$id', '$personalId', '$role', '$user', '$pass', '$data');", $con);
                    if($r)
                        $flag = 0;
                    else
                        Tool::getDbError($con);
                }
                else
                {
                    $r = mysql_query("update usuario set personal_id='$personalId', role='$role', user='$user', pass='$pass', data='$data' where id='$id';", $con);
                    if($r)
                        $flag = 1;
                    else
                        Tool::getDbError($con);
                }
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $flag;
    }
    
    public function enum($controller)
    {
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {                    
                $r = mysql_query("select usuario.id, usuario.personal_id, usuario.user, personal.id as pid, personal.surname, personal.name from usuario, personal where usuario.personal_id=personal.id order by surname, usuario.user;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['user'].'=>'.$row['surname'].'=>'.$row['name'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($data);
    }
    
    public function get($controller)
    {
        $id = Gestion::sqlKill($controller->getRequest()->request->get('id'));
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {                    
                $r = mysql_query("select usuario.id, usuario.personal_id, usuario.role, usuario.user, usuario.data, personal.id as pid, personal.document, personal.surname, personal.name from usuario, personal where usuario.personal_id=personal.id and usuario.id='$id' limit 1;", $con);
                if($r)
                {
                    $data = array();
                    
                    $r = mysql_fetch_assoc($r);
                    
                    $data[] = 'id=>'.$r['id'];
                    $data[] = 'personal_id=>'.$r['personal_id'];
                    $data[] = 'xpersonal_id=>'.$r['document'].' - '.$r['surname'].' '.$r['name'];
                    $data[] = 'user=>'.$r['user'];
                    $data[] = 'role=>'.$r['role'];
                    $data[] = 'xrole=>'.$this->roles[$r['role']];
                    $data[] = 'data=>'.$r['data'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
            
        return Gestion::utf8Fix($data);
    }
    
    public function del($controller)
    {
        $id = Gestion::sqlKill($controller->getRequest()->request->get('id'));
        
        $data = -1;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {                    
                $r = mysql_query("delete from usuario where id='$id';", $con);
                if($r)
                    $data = $id;
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
}
 
