<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Material
{
    private $tipos = array("P"=>"PAPEL","S"=>"SUSTRATO");
    
    public function save($controller)
    {
        $id = Gestion::sqlKill($controller->getRequest()->request->get('id'));
        $name = Gestion::sqlKill($controller->getRequest()->request->get('name'));
        $cost = Gestion::sqlKill($controller->getRequest()->request->get('cost'));
        $value = Gestion::sqlKill($controller->getRequest()->request->get('value'));
        $type = Gestion::sqlKill($controller->getRequest()->request->get('type'));
        $width = Gestion::sqlKill($controller->getRequest()->request->get('width'));
        $height = Gestion::sqlKill($controller->getRequest()->request->get('height'));
        $weigth = Gestion::sqlKill($controller->getRequest()->request->get('weigth'));
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
                    $r = mysql_query("insert into material values ('$id', '$name', '$cost', '$value', '$type', '$width', '$height', '$weigth', '$data');", $con);
                    if($r)
                        $flag = 0;
                    else
                        Tool::getDbError($con);
                }
                else
                {
                    $r = mysql_query("update material set name='$name', cost='$cost', value='$value', type='$type', width='$width', height='$height', weigth='$weigth', data='$data' where id='$id';", $con);
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
                $r = mysql_query("select id, name, type, cost from material order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$this->tipos[$row['type']].'=>'.$row['name'].'=> $ '.$row['cost'];
                    
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
                $r = mysql_query("select * from material where id='$id' limit 1;", $con);
                if($r)
                {
                    $data = array();
                    
                    $r = mysql_fetch_assoc($r);
                    
                    foreach($r as $k => $v)
                        $data[] = $k.'=>'.$v;
                    
                    $data[] = 'xtype=>'.$this->tipos[$r['type']];
                    
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
                $r = mysql_query("delete from material where id='$id';", $con);
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
 
