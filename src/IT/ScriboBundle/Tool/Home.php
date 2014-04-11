<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Home
{
    private $roles = array('R'=>'Administrador', 'A'=>'Asesor','P'=>'Jefe de Prensa','I'=>'Operario de Prensa','T'=>'Jefe de Acabados','D'=>'Operario de Acabados','C'=>'Entregas');
    
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
                $r = mysql_query("select proceso.id as pid, orden.id as orden, orden.type as tipo, cliente.name as cliente, orden.date as inicio, orden.time * 3600 as tiempo, TIME_TO_SEC(TIMEDIFF(now(), orden.date)) as lapso from proceso, orden, cliente, usuario where proceso.orden_id=orden.id and proceso.recibe_id=usuario.id and proceso.status='O' and orden.status=usuario.role and orden.cliente_id=cliente.id and usuario.id='$user' order by inicio asc;", $con);
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
    
    public function proc($controller)
    {
        $pid = Gestion::sqlKill($controller->getRequest()->request->get('pid'));
        $oid = Gestion::sqlKill($controller->getRequest()->request->get('oid'));
        $act = Gestion::sqlKill($controller->getRequest()->request->get('action'));
        $rec = Gestion::sqlKill($controller->getRequest()->request->get('reciv'));
        $dat = Gestion::sqlKill($controller->getRequest()->request->get('data'));
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $user = Gestion::getUserId($controller);
        $rec = $rec == '$' ? $user : $rec;
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {    
                $r = mysql_query("update proceso set status='C', date=now() where id='$pid';", $con);
                $r = mysql_query("insert into proceso values('0', now(), '$oid', '$user', '$rec', 'O', '$act', '$dat');", $con);
                $r = mysql_query("update orden, usuario set orden.status=usuario.role where orden.id='$oid' and usuario.id='$rec';", $con);
            }
        }
        
        return "$pid $oid $act $rec $dat";
    }
    
    public function reciv($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        $extra = Gestion::sqlKill($controller->getRequest()->request->get('extra'));
        
        $user = Gestion::getUserId($controller);
        
        $data = '_NONE_';
        
        if($extra != '')
        {
            if($extra != 'A')
            {
                $lic = Gestion::getLicencia(Gestion::getDomain($controller));
                    
                if($lic)
                {
                    $con = Tool::newDbCon($lic);
                    
                    if($con)
                    {
                        $r = mysql_query("select usuario.id as uid, usuario.user as nick, usuario.role as rol, personal.surname as psur, personal.name as pnam from usuario, personal where (personal.surname like '%$param%' or personal.name like '%$param%') and ".Gestion::perRole($controller, $extra)." and usuario.personal_id=personal.id and usuario.id<>'$user' order by personal.surname, usuario.user asc;", $con);
                        if($r)
                        {
                            $data = array();
                            
                            while($row = mysql_fetch_assoc($r))
                                $data[] = $row['uid'].'=>'.$row['psur'].' '.$row['pnam'].'=>'.$row['nick'].'=>'.$this->roles[$row['rol']];
                            
                            $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                        }
                        else
                            Tool::getDbError($con);
                            
                        Tool::closeDbCon($con);
                    }
                }
            }
            
            return Gestion::utf8Fix($data);
        }
        else
            return "@=>Debe indicar el tipo de acción!";
        
    }
}
 
