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
        $role = Gestion::getRole($controller);
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {    
                if($role != 'R')
                {
                    $r = mysql_query("select proceso.id as pid, orden.id as orden, orden.type as tipo, cliente.name as cliente, orden.date as inicio, orden.time * 3600 as tiempo, TIME_TO_SEC(TIMEDIFF(now(), orden.date)) as lapso from proceso, orden, cliente, usuario where proceso.orden_id=orden.id and proceso.recibe_id=usuario.id and proceso.status='O' and orden.status=usuario.role and orden.cliente_id=cliente.id and usuario.id='$user' order by inicio asc;", $con);
                    if($r)
                    {
                        while($row = mysql_fetch_assoc($r))
                        {
                            $data[] = join('=>', Gestion::utf8Fix($row));
                        }
                    }
                    else
                        Tool::getDbError($con);
                }
                else
                {
                    $r = mysql_query("select proceso.id as pid, orden.id as orden, orden.type as tipo, cliente.name as cliente, orden.date as inicio, orden.time * 3600 as tiempo, TIME_TO_SEC(TIMEDIFF(now(), orden.date)) as lapso from proceso, orden, cliente where proceso.orden_id=orden.id and proceso.status='O' and orden.cliente_id=cliente.id order by inicio asc;", $con);
                    if($r)
                    {
                        while($row = mysql_fetch_assoc($r))
                        {
                            $data[] = join('=>', Gestion::utf8Fix($row));
                        }
                    }
                    else
                        Tool::getDbError($con);
                }
                
                Tool::closeDbCon($con);
            }
        }
        
        return join('|:|', $data);
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
                if($rec == '+')
                {
                    $r = mysql_query("select emite_id from proceso where id='$pid' limit 1;", $con);
                    if($r)
                    {
                        $r = mysql_fetch_assoc($r);
                        $rec = $r['emite_id'];
                    }
                    else
                        Tool::getDbError($con);
                }
                
                $r = mysql_query("update proceso set status='C', date=now() where id='$pid';", $con);
                $r = mysql_query("insert into proceso values('0', now(), '$oid', '$user', '$rec', 'O', '$act', '$dat');", $con);
                $r = mysql_query("update orden, usuario set orden.status=usuario.role where orden.id='$oid' and usuario.id='$rec';", $con);
                
                Tool::closeDbCon($con);
            }
        }
        
        return "0";
    }
    
    public function cicle($controller)
    {
        $oid = Gestion::sqlKill($controller->getRequest()->request->get('oid'));
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = '';
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $data = array();
                
                $r = mysql_query("select proceso.id as pid, proceso.date as date, (select concat(usuario.role,'=>',user,'=>',personal.surname,' ',personal.name) from personal, usuario where personal.id=usuario.personal_id and usuario.id=proceso.emite_id) as emite, (select concat(usuario.role,'=>',user,'=>',personal.surname,' ',personal.name) from personal, usuario where personal.id=usuario.personal_id and usuario.id=proceso.recibe_id) as recibe, proceso.status as estado, proceso.action as accion, proceso.data as datos from proceso, orden where proceso.orden_id=orden.id and orden_id='$oid' order by proceso.id asc;", $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row['pid'].'=>'.$row['date'].'=>'.$row['emite'].'=>'.$row['recibe'].'=>'.$row['estado'].'=>'.$row['accion'].'=>'.$row['datos']);
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                
                $r = mysql_query("select orden.id as orden, orden.type as tipo, cliente.name as cliente, concat(personal.surname, ' ', personal.name) as personal, orden.date as fecha, orden.status as estado, orden.time as tiempo, orden.total as valor from orden, usuario, personal, cliente where cliente.id=orden.cliente_id and usuario.id=orden.usuario_id and personal.id=usuario.personal_id and orden.id='$oid' limit 1;", $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    $row = join('=>', $row);
                    
                    $data = $row.'|:|'.$data;
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function reciv($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        $extra = Gestion::sqlKill($controller->getRequest()->request->get('extra'));
        
        $extra = explode('|:|', $extra);
        $action = $extra[0];
        $pid = $extra[1];
        $user = Gestion::getUserId($controller);
        
        $data = '_NONE_';
        
        if($action != '')
        {
            if($action != 'A')
            {
                $lic = Gestion::getLicencia(Gestion::getDomain($controller));
                    
                if($lic)
                {
                    $con = Tool::newDbCon($lic);
                    
                    if($con)
                    {
                        $r = mysql_query("select usuario.id as uid, usuario.user as nick, usuario.role as rol, personal.surname as psur, personal.name as pnam from proceso, usuario, personal where proceso.id='$pid' and (personal.surname like '%$param%' or personal.name like '%$param%') and ".Gestion::perRole($controller, $action)." and usuario.personal_id=personal.id and usuario.id<>'$user' order by personal.surname, usuario.user asc;", $con);
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
            return $data;
    }
    
    public function veri($controller)
    {
        $oid = Gestion::sqlKill($controller->getRequest()->request->get('oid'));
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = 'N';
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select id, action, status, orden_id from proceso where orden_id='$oid' order by id desc limit 1;", $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if($row['status'] == 'O' && $row['action'] == 'O')
                        $data = 'Y';
                }
                else
                    Tool::getDbError($con);
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function entrega($controller)
    {
        $pid = Gestion::sqlKill($controller->getRequest()->request->get('pid'));
        $oid = Gestion::sqlKill($controller->getRequest()->request->get('oid'));
        $fir = Gestion::sqlKill($controller->getRequest()->request->get('firma'));
        $obs = Gestion::sqlKill($controller->getRequest()->request->get('data'));
        
        $user = Gestion::getUserId($controller);
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = 'N';
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("insert into entrega values('0', '$user', '$oid', now(), '0', '$fir', '$obs');", $con);
                $eid = mysql_insert_id();
                
                $r = mysql_query("update entrega, orden set entrega.valor=orden.total where orden.id=entrega.orden_id and entrega.id='$eid';", $con);
                $r = mysql_query("update proceso set status='C', date=now() where id='$pid';", $con);
                $r = mysql_query("insert into proceso values('0', now(), '$oid', '$user', '$user', 'C', 'X', 'Orden Entregada');", $con);
                $r = mysql_query("update orden set orden.status='X' where orden.id='$oid';", $con);
                
                $data = $eid;
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getEntrega($controller, $id)
    {
        $entr = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select * from entrega where entrega.id='$id' limit 1;";
                $r = mysql_query($sql, $con);
                if($r)
                    $entr = mysql_fetch_assoc($r);
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($entr);
    }
    
    public function getOrder($controller, $id)
    {
        $order = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select orden.* from orden, entrega where orden.id=entrega.orden_id and entrega.id='$id' limit 1;";
                $r = mysql_query($sql, $con);
                if($r)
                    $order = mysql_fetch_assoc($r);
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($order);
    }
    
    public function getPersonal($controller, $id)
    {
        $per = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select personal.name, personal.surname, personal.document from usuario, personal where personal.id = usuario.personal_id and usuario.id='$id' limit 1;";
                $r = mysql_query($sql, $con);
                if($r)
                    $per = mysql_fetch_assoc($r);
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($per);
    }
    
    public function getCliente($controller, $id)
    {
        $cli = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select * from cliente where id='$id' limit 1;";
                $r = mysql_query($sql, $con);
                if($r)
                    $cli = mysql_fetch_assoc($r);
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($cli);
    }
}
 
