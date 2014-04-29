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
        
        $oldpass = Gestion::sqlKill($oldpass);
        $newpass = Gestion::sqlKill($newpass);
        
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
                    $cfg = mysql_fetch_assoc($cfg);
                    
                Tool::closeDbCon($con);
            }
        }
            
        
        return Gestion::utf8Fix($cfg);
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
                    $sto = mysql_fetch_assoc($sto);
                    $sto = $sto['storage'];
                }
                   
                Tool::closeDbCon($con);
            }
        }
            
        
        return Gestion::utf8Fix($sto);
    }
    
    public static function sqlKill($str)
    {
        $str = str_ireplace("\\", "\\\\", $str);
        $str = str_ireplace("'", "\\'", $str);
        
        return utf8_decode($str);
    }
    
    public static function utf8Fix($src)
    {
        if(is_array($src))
        {
            $res = array();
            foreach($src as $k => $v)
                $res[$k] = utf8_encode($v);
            
            return $res;
        }
        else if(is_string($src))
            return utf8_encode($src);
        else
            return null;
    }
    
    public static function creaImg($data)
    {
        $fname = '';
        
        $img = explode(',',$data);
        if(count($img) > 1)
        {
            $fname = explode('/', $img[0])[1];
            $fname = explode(';', $fname)[0];
            $fname = sha1(date('Y-m-d_H:i:s').'_'.rand(0, 1000)).'.'.$fname;
            file_put_contents('/tmp/'.$fname, base64_decode($img[1]));
        }
        
        return $fname;
    }
    
    public static function perRole($controller, $action)
    {
        $permit = array();
        $permit["A"] = array("F" => "usuario.role='P'", "R" => "usuario.id=0", "B" => "usuario.role='N'", "T" => "usuario.role='A'");
        $permit["P"] = array("F" => "usuario.role='I'", "R" => "usuario.id=proceso.emite_id", "B" => "usuario.role='A'", "T" => "usuario.role='P'");
        $permit["I"] = array("F" => "usuario.role='T'", "R" => "usuario.id=proceso.emite_id", "B" => "usuario.role='N'", "T" => "usuario.role='N'");
        $permit["T"] = array("F" => "usuario.role='D'", "R" => "usuario.id=proceso.emite_id", "B" => "usuario.role='I'", "T" => "usuario.role='T'");
        $permit["D"] = array("F" => "usuario.role='C'", "R" => "usuario.id=proceso.emite_id", "B" => "usuario.role='N'", "T" => "usuario.role='N'");
        $permit["C"] = array("F" => "usuario.role='N'", "R" => "usuario.id=proceso.emite_id", "B" => "((usuario.role='A' or usuario.role='P' or usuario.role='T') and usuario.role<>'R' and usuario.role<>'C')", "T" => "usuario.role='C'");
        
        return $permit[Gestion::getRole($controller)][$action];
    }
    
    public static function getSQLName($controller)
    {
        $xcn = explode('|:|', base64_decode(Gestion::getLicencia(Gestion::getDomain($controller))));
        return $xcn[3];
    }
    
    public static function getSQLCopy($controller)
    {
        $xcn = explode('|:|', base64_decode(Gestion::getLicencia(Gestion::getDomain($controller))));
        $file = "/tmp/sql_".$xcn[3]."_".date('Ymd')."_".date('His');
        exec("mysqldump -h ".$xcn[1]." -P ".$xcn[2]." -u ".$xcn[4]." -p".$xcn[5]." ".$xcn[3]." > ".$file.".sql");
        exec("zip -9 -j ".$file.".zip ".$file.".sql");
        unlink($file.".sql");
        return  $file.".zip";
    }
}
 
