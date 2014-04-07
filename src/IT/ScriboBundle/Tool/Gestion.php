<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;

class Gestion
{
    public static function getLicencia($domain)
    {
        $cxn = null;
        $con = Tool::getDbCon();
        
        if($con)
        {
            $serv = mysql_query("select id from servicio where code = 'SCRB' limit 1", $con);
            
            if($serv)
            {
                $serv = mysql_fetch_assoc($serv);
                $serv = $serv['id'];
                
                $lic = mysql_query("select id from licencia where servicio_id = '$serv' and domain = '$domain' and inicio <= curdate() and fin >= curdate() limit 1", $con);
                
                if($lic)
                {
                    $lic = mysql_fetch_assoc($lic);
                    $lic = $lic['id'];
                    
                    $cxn = mysql_query("select * from conexion where licencia_id = '$lic' and name = 'DB_MASTER'", $con);
                    
                    if($cxn)
                        $cxn = mysql_fetch_assoc($cxn);
                    else
                        $cnx = null;
                }
            }
            
            Tool::closeDbCon($con);
            
            if($cxn)
                return base64_encode($cxn['engine'].'|:|'.$cxn['host'].'|:|'.$cxn['port'].'|:|'.$cxn['path'].'|:|'.base64_decode($cxn['user']).'|:|'.base64_decode($cxn['pass']));
            else
                return null;
        }
        else
            return null;
    }
    
    public static function isUser($domain, $user, $pass)
    {
        $lic = Gestion::getLicencia($domain);
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            if($con)
            {
                $user = mysql_query("select role from usuario where user = '$user' and pass = '$pass' limit 1", $con);
                if($user)
                {
                    $user = mysql_fetch_assoc($user);
                    $user = $user['role'];
                }
                else
                    $user = '';
                    
                Tool::closeDbCon($con);
                
                return $user;
            }
            else
                return '-';
        }
        else
            return '_';
    }
    
    public static function isGrant($controller, $priv)
    {
        $priv = explode(',', $priv);
        
        $flag = false;
        $session = $controller->getRequest()->getSession();
        
        $svar = base64_decode($session->get("userActive"));
        $svar = explode("|:|", $svar);
        
        if(count($svar) > 1)
        {
            if(in_array($svar[2], $priv) || $priv[0] == '*')
                $flag = true;
        }
        
        return $flag;

    }
    
    public static function getDomain($controller)
    {
        $session = $controller->getRequest()->getSession();
        
        $svar = base64_decode($session->get("userActive"));
        $svar = explode("|:|", $svar);
        
        if(count($svar) > 1)
            return $svar[0];
        else
            return null;
    }
    
    public static function getUser($controller)
    {
        $session = $controller->getRequest()->getSession();
        
        $svar = base64_decode($session->get("userActive"));
        $svar = explode("|:|", $svar);
        
        if(count($svar) > 1)
            return $svar[1];
        else
            return null;
    }
    
    public static function getRole($controller)
    {
        $session = $controller->getRequest()->getSession();
        
        $svar = base64_decode($session->get("userActive"));
        $svar = explode("|:|", $svar);
        
        if(count($svar) > 1)
            return $svar[2];
        else
            return null;
    }
    
    public static function getUserId($controller)
    {
        $id = -1;
        
        $user = Gestion::getUser($controller);
        if($user)
        {
            $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
            if($lic)
            {
                $con = Tool::newDbCon($lic);
                if($con)
                {
                    $user = mysql_query("select id from usuario where user = '$user' limit 1", $con);
                    
                    if($user)
                    {
                        $user = mysql_fetch_assoc($user);
                        $user = $user['id'];
                        
                        if($user != '' and intval($user) > 0)
                            $id = $user;
                    }
                        
                    Tool::closeDbCon($con);
                }
            }
            
        }
        
        return $id;
    }
    
    public static function chPass($controller, $oldpass, $newpass)
    {
        $flag = false;
        
        $user = Gestion::getUser($controller);
        if($user)
        {
            $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
            if($lic)
            {
                $con = Tool::newDbCon($lic);
                if($con)
                {
                    $user = mysql_query("select id from usuario where user = '$user' and pass = '$oldpass' limit 1", $con);
                    
                    if($user)
                    {
                        $user = mysql_fetch_assoc($user);
                        $user = $user['id'];
                        
                        if($user != '' and intval($user) > 0)
                        {
                            $resul = mysql_query("update usuario set pass = '$newpass' where id = '$user'", $con);
                            
                            if($resul)
                                $flag = true;
                        }
                    }
                        
                    Tool::closeDbCon($con);
                }
            }
            
        }
        
        return $flag;
    }
    
    public static function getConfiguracion($controller)
    {
        $cfg = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            if($con)
            {
                $cfg = mysql_query("select * from configuracion where id = '1' limit 1", $con);
                
                if($cfg)
                    $cfg = Gestion::utf8Fix(mysql_fetch_assoc($cfg));
                    
                Tool::closeDbCon($con);
            }
        }
            
        
        return $cfg;
    }
    
    public static function getStorage($domain)
    {
        $sto = '';
        
        $lic = Gestion::getLicencia($domain);
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            if($con)
            {
                $sto = mysql_query("select storage from configuracion where id = '1' limit 1", $con);
                
                if($sto)
                {
                    $sto = Gestion::utf8Fix(mysql_fetch_assoc($sto));
                    $sto = $sto['storage'];
                }
                   
                Tool::closeDbCon($con);
            }
        }
            
        
        return $sto;
    }
    
    public static function sqlKill($str)
    {
        $str = str_ireplace("\\", "\\\\", $str);
        $str = str_ireplace("'", "\\'", $str);
        
        return $str;
    }
    
    public static function utf8Fix($arr)
    {
        if(is_array($arr))
        {
            $res = array();
            foreach($arr as $k => $v)
                $res[$k] = utf8_encode($v);
            
            return $res;
        }
        else
            return null;
    }
    
    public static function creaImg($data)
    {
        $img = explode(',',$data);
        $fname = explode('/', $img[0])[1];
        $fname = explode(';', $fname)[0];
        $fname = sha1(date('Y-m-d_H:i:s').'_'.rand(0, 1000)).'.'.$fname;
        file_put_contents('/tmp/'.$fname, base64_decode($img[1]));
        
        return $fname;
    }
}
 
