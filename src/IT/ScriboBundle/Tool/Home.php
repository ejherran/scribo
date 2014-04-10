<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Home
{
    public function getList($controller)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $user = Gestion::getUserId($controller);
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {    
                $r = mysql_query("select proceso.id as pid, orden.id as orden, orden.type as tipo, orden.date as inicio, orden.time * 3600 as tiempo, TIME_TO_SEC(TIMEDIFF(now(), orden.date)) as lapso from proceso, orden, usuario where proceso.orden_id=orden.id and proceso.recibe_id=usuario.id and proceso.status='O' and orden.status=usuario.role and usuario.id='$user' order by inicio asc;", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row);
                }
                else
                    Tool::getDbError($con);
            }
        }
        
        return $data;
    }
}
 
