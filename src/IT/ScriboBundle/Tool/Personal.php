<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Personal
{
    public function save($controller)
    {
        $id = Gestion::sqlKill($controller->getRequest()->request->get('id'));
        $document = Gestion::sqlKill($controller->getRequest()->request->get('document'));
        $surname = Gestion::sqlKill($controller->getRequest()->request->get('surname'));
        $name = Gestion::sqlKill($controller->getRequest()->request->get('name'));
        $address = Gestion::sqlKill($controller->getRequest()->request->get('address'));
        $phone = Gestion::sqlKill($controller->getRequest()->request->get('phone'));
        $mail = Gestion::sqlKill($controller->getRequest()->request->get('mail'));
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
                    $r = mysql_query("insert into personal values ('$id', '$document', '$surname', '$name', '$address', '$phone', '$mail', '$data');", $con);
                    if($r)
                        $flag = 0;
                    else
                        Tool::getDbError($con);
                }
                else
                {
                    $r = mysql_query("update personal set document='$document', surname='$surname', name='$name', address='$address', phone='$phone', mail='$mail', data='$data' where id='$id';", $con);
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
                $r = mysql_query("select id, document, surname, name from personal order by surname;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['document'].'=>'.$row['surname'].'=>'.$row['name'];
                    
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
                $r = mysql_query("select * from personal where id='$id' limit 1;", $con);
                if($r)
                {
                    $data = array();
                    
                    $r = mysql_fetch_assoc($r);
                    
                    foreach($r as $k => $v)
                        $data[] = $k.'=>'.$v;
                    
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
                $r = mysql_query("delete from personal where id='$id';", $con);
                if($r)
                    $data = $id;
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function find($controller)
    {
        $par = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        
        $data = '_NONE_';
        
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {                    
                $r = mysql_query("select id, document, surname, name from personal where document like '%$par%' or surname like '%$par%' or name like '%$par%' order by surname;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['document'].'=>'.$row['surname'].'=>'.$row['name'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($data);
    }
}
 
