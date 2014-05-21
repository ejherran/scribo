<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Dos
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
        
        return Gestion::utf8Fix($data);
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
                $r = mysql_query("select id, name, value, discount from material where name like '%$param%' and type = 'S' order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['name'].'=>$ '.$row['value'].'=>~|~'.$row['discount'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($data);
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
                $r = mysql_query("select id, name, value, discount from tinta where name like '%$param%' and type = 'S' order by name;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['name'].'=>$ '.$row['value'].'=>~|~'.$row['discount'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($data);
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
                $r = mysql_query("select id, name, value from acabado where name like '%$param%' and type = 'S' order by name;", $con);
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
        
        return Gestion::utf8Fix($data);
    }
    
    public function save($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        $param = explode('|:|', $param);
        $data = '-1';
        
        $user = Gestion::getUserId($controller);
        
        $dO = explode('|-|', $param[0]);
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $obs = $dO[6] != '@' ? $dO[6] : '';
                $sql = "insert into orden values ('0', 'U', '".$dO[0]."', 'B', '".$dO[1]."', '".$user."', now(), 'A', '".$dO[2]."', '".$dO[3]."', '".$dO[4]."', '".$dO[5]."', '".$param[count($param)-1]."', '".$obs."');";
                $r = mysql_query($sql, $con);
                if($r)
                {
                   $oid = mysql_insert_id();
                   
                    if(intval($oid) > 0)
                    {
                        $items = array_slice($param, 1, count($param)-2);
                        
                        foreach($items as $it)
                        {
                            $dit = explode('|-|', $it);
                            $diobs = $dit[11] != '@' ? $dit[11] : '';
                            
                            $sql = "insert into sustrato values('0', '".$oid."', '".$dit[0]."', '".$dit[1]."', '".$dit[2]."', '".$dit[3]."', '".$dit[4]."', '".$dit[5]."', '".$dit[6]."', '".$dit[7]."', '".$dit[8]."', '".$dit[9]."', '".$dit[10]."', '".$diobs."');";
                            $r = mysql_query($sql, $con);
                            
                            $iid = mysql_insert_id();
                            
                            if(intval($iid) > 0 && $dit[12] != '@')
                            {
                                $daca = explode(';', $dit[12]);
                                
                                foreach($daca as $dc)
                                {
                                    $sql = "insert into sustratoAcabado values('0', '".$iid."', '".$dc."');";
                                    $r = mysql_query($sql, $con);
                                }
                            }
                        }
                        
                        $sql = "insert into proceso values('0', now(), '".$oid."', '".$user."', '".$user."', 'O', 'C', 'Nueva orden de tipo 2');";
                        $r = mysql_query($sql, $con);
                        
                        $data = $oid;
                    }
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
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
                $sql = "select * from orden where id='$id' limit 1;";
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
    
    public function getItems($controller, $id)
    {
        $item = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select sustrato.id as idx, sustrato.name as fichero, material.name as material, tinta.name as tinta, sustrato.width as ancho, sustrato.height as largo, amount as cantidad, unit as unitario, sustrato.value as valor, sustrato.data as notas, sustrato.expiry as caduca from sustrato, material, tinta where material.id = sustrato.material_id and tinta.id = sustrato.tinta_id and orden_id='$id' order by idx asc;";
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row);
                    
                    $item = count($data) > 0 ? $data : null;
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $item;
    }
    
    public function getAcabados($controller, $id)
    {
        $item = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select acabado.name as acabado from sustratoAcabado, acabado where acabado.id = sustratoAcabado.acabado_id and sustratoAcabado.sustrato_id='$id' order by acabado asc;";
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = Gestion::utf8Fix($row['acabado']);
                    
                    $data = join(';', $data);
                    
                    $item = $data != '' ? $data : null;
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return $item;
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
                $sql = "select personal.name, personal.surname, personal.document from usuario, personal where personal.id = usuario.personal_id and usuario.id='$id';";
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
}
 
