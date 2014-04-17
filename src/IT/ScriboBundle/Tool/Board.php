<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Board
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
                $r = mysql_query("select orden.id as orden, orden.status as estado, (select action from proceso where proceso.orden_id=orden.id order by proceso.id desc limit 1 ) as pro, cliente.name as cliente, orden.date as inicio, orden.time * 3600 as tiempo, TIME_TO_SEC(TIMEDIFF(now(), orden.date)) as lapso from orden, cliente where orden.status<>'X' and orden.cliente_id=cliente.id order by inicio asc;", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                    {
                        $data[] = join('=>', Gestion::utf8Fix($row));
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return join('|:|', $data);
    }
}
 
