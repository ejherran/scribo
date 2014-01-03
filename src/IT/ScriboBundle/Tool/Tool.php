<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Config\Config;

class Tool
{
    public static function getDbCon()
    {
        if(Config::DbDriver == 'mysql')
        {
            $con =  @mysql_connect(Config::DbHost.':'.Config::DbPort, Config::DbUser, Config::DbPass);
            
            if (!$con) 
                return null;
            else
            {
                if(mysql_select_db(Config::DbName, $con))
                    return $con;
                else
                    return null;
            }
        }
        else
            return null;
    }
    
    public static function newDbCon($hash)
    {
        $xcn = explode('|:|', base64_decode($hash));
        if($xcn[0] == 'mysql')
        {
            $con =  @mysql_connect($xcn[1].':'.$xcn[2], $xcn[4], $xcn[5]);
            if (!$con) 
                return null;
            else
            {
                if(mysql_select_db($xcn[3], $con))
                    return $con;
                else
                    return null;
            }
        }
        else
            return null;
    }
    
    public static function closeDbCon($con, $driver='*')
    {
        $driver = $driver == '*' ? Config::DbDriver : $driver;
        
        if($driver == 'mysql')
            mysql_close($con);
    }
}
