<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Filer
{
    public function getList($controller)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {    
                $r = mysql_query("select orden.type as tipo, papel.id as id, orden.id as orden, cliente.name as cliente, papel.name as original, papel.storage as archivo, orden.date as inicio, papel.expiry as fin, IF(STR_TO_DATE(papel.expiry, '%Y-%m-%d')<=curdate(), 'W', 'N') as status from papel, orden, cliente where papel.expiry<>'ERASED' and papel.orden_id=orden.id and cliente.id=orden.cliente_id order by papel.id asc;", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                        $data[] = join('=>', Gestion::utf8Fix($row));
                }
                else
                    Tool::getDbError($con);
                    
                $r = mysql_query("select orden.type as tipo, sustrato.id as id, orden.id as orden, cliente.name as cliente, sustrato.name as original, sustrato.storage as archivo, orden.date as inicio, sustrato.expiry as fin, IF(STR_TO_DATE(sustrato.expiry, '%Y-%m-%d')<=curdate(), 'W', 'N') as status from sustrato, orden, cliente where sustrato.expiry<>'ERASED' and sustrato.orden_id=orden.id and cliente.id=orden.cliente_id order by sustrato.id asc;", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                        $data[] = join('=>', Gestion::utf8Fix($row));
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return join('|:|', $data);
    }
    
    public function checkExpiry($controller)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {    
                $r = mysql_query("select papel.id as id from orden, papel where orden.status='X' and (STR_TO_DATE(papel.expiry, '%Y-%m-%d')<=curdate() or papel.expiry='@' or papel.expiry='EXPIRED') and orden.id=papel.orden_id;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row['id']);
                    
                    if(count($data) > 0)
                        mysql_query("update papel set expiry='EXPIRED' where id IN (".join(',', $data).");");
                    
                }
                else
                    Tool::getDbError($con);
                
                $r = mysql_query("select sustrato.id as id from orden, sustrato where orden.status='X' and (STR_TO_DATE(sustrato.expiry, '%Y-%m-%d')<=curdate() or sustrato.expiry='@' or sustrato.expiry='EXPIRED') and orden.id=sustrato.orden_id;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row['id']);
                    
                    if(count($data) > 0)
                        mysql_query("update sustrato set expiry='EXPIRED' where id IN (".join(',', $data).");");
                    
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return '0';
    }
    
    public function purge($controller)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select storage from papel where expiry='EXPIRED';", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row['storage']);
                    
                    if(count($data) > 0)
                        mysql_query("update papel set expiry='ERASED' where expiry='EXPIRED';");
                }
                else
                    Tool::getDbError($con);
                
                $r = mysql_query("select storage from sustrato where expiry='EXPIRED';", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row['storage']);
                    
                    if(count($data) > 0)
                        mysql_query("update sustrato set expiry='ERASED' where expiry='EXPIRED';");
                }
                else
                    Tool::getDbError($con);
            }
        }
        
        return join(',', $data);
    }
    
    public function update($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        $expiry = Gestion::sqlKill($controller->getRequest()->request->get('expiry'));
        
        $param = explode(',', $param);
        $papel = array();
        $sustrato = array();
        
        foreach($param as $p)
        {
            $tmp = explode('_', $p);
            if($tmp[0] == 'A')
                $papel[] = $tmp[1];
            else if($tmp[0] == 'B')
                $sustrato[] = $tmp[1];
        }
        
        $papel = join(',', $papel);
        $sustrato = join(',', $sustrato);
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                if($papel != '')
                    mysql_query("update papel set expiry='$expiry' where id IN ($papel);");
                    
                if($sustrato != '')
                    mysql_query("update sustrato set expiry='$expiry' where id IN ($sustrato);");
            }
        }
        
        return '0';
    }
    
    public function delete($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        
        $param = explode(',', $param);
        $papel = array();
        $sustrato = array();
        
        foreach($param as $p)
        {
            $tmp = explode('_', $p);
            if($tmp[0] == 'A')
                $papel[] = $tmp[1];
            else if($tmp[0] == 'B')
                $sustrato[] = $tmp[1];
        }
        
        $papel = join(',', $papel);
        $sustrato = join(',', $sustrato);
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                if($papel != '')
                {
                    $r = mysql_query("select storage from papel where id IN ($papel);", $con);
                    if($r)
                    {
                        while($row = mysql_fetch_assoc($r))
                            $data[] = Gestion::utf8Fix($row['storage']);
                        
                        if(count($data) > 0)
                            mysql_query("update papel set expiry='ERASED' where id IN ($papel);");
                    }
                    else
                        Tool::getDbError($con);
                }
                
                if($sustrato != '')
                {
                    $r = mysql_query("select storage from sustrato where id IN ($sustrato);", $con);
                    if($r)
                    {
                        while($row = mysql_fetch_assoc($r))
                            $data[] = Gestion::utf8Fix($row['storage']);
                        
                        if(count($data) > 0)
                            mysql_query("update sustrato set expiry='ERASED' where id IN ($sustrato);");
                    }
                    else
                        Tool::getDbError($con);
                }
            }
        }
        
        return join(',', $data);
    }
}
 
