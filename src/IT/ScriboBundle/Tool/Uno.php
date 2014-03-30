<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Uno
{
    public function findCli($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select id, document, name from cliente where name like '%$param%' or document like '%$param%' order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['name'].'=>'.$row['document'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function findMate($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select id, name, value from material where name like '%$param%' and type = 'P' order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['name'].'=>$ '.$row['value'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function findTinta($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select id, name, value from tinta where name like '%$param%' and type = 'P' order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['name'].'=>$ '.$row['value'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function findAcabado($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select id, name, value from acabado where name like '%$param%' and type = 'P' order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['name'].'=>$ '.$row['value'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function save($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        $ifo = explode('|-*-*-|', $param);
        
        $user = Gestion::getUserId($controller);
        $pars = explode('|=-=|', $ifo[0]);
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $obs = $pars[6] != '@@@' ? $pars[6] : '';
                $fech = date('Y-m-d');
                $init = date('H:i:s');
                $sql = "insert into orden values ('0', '".$pars[0]."', '".$user."', '".$fech."', '".$init."', '0', '".$pars[1]."', '".$pars[2]."', '".$pars[3]."', '".$pars[4]."', '".$pars[5]."', '".$obs."');";
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $oid = mysql_insert_id();
                    
                    if(intval($oid) > 0)
                    {
                        $items = explode('|:-:|', $ifo[1]);
                        
                        foreach($items as $it)
                        {
                            
                            $dit = explode('|=-=|', $it);
                            $diobs = $dit[8] != '@@@' ? $dit[8] : '';
                            $sql = "insert into papel values('0', '".$oid."', '".$dit[0]."', '".$dit[1]."', '".$dit[2]."', '".$dit[3]."', '".$dit[4]."', '".$dit[5]."', '".$dit[6]."', '".$dit[7]."', '".$diobs."');";
                            $r = mysql_query($sql, $con);
                            
                            $iid = mysql_insert_id();
                            
                            if(intval($iid) > 0 && $dit[9] != '@@@')
                            {
                                $daca = explode('|-|', $dit[9]);
                                
                                foreach($daca as $dc)
                                {
                                    $sql = "insert into papelAcabado values('0', '".$iid."', '".$dc."');";
                                    $r = mysql_query($sql, $con);
                                }
                            }
                        }
                        
                        $sql = "insert into proceso values('0', '".$oid."', '".$user."', '".$user."', 'C', 'Nueva orden de tipo 1');";
                        $r = mysql_query($sql, $con);
                        
                        $data = 'OK!';
                    }
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
}
 
