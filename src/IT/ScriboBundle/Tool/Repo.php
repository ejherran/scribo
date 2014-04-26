<?php

namespace IT\ScriboBundle\Tool;

use IT\ScriboBundle\Tool\Tool;
use IT\ScriboBundle\Tool\Gestion;

class Repo
{
    public function filterOrden($controller)
    {
        $param = Gestion::sqlKill($controller->getRequest()->request->get('param'));
        $pic = strtoupper(substr($param, 0, 2));
        $rule = '';
        
        if($pic == 'F:')
            $rule = " and orden.date like '%".substr($param, 2)."%' ";
        else
            $rule = " and cliente.name like '%".$param."%' ";
        
        $data = '_NONE_';
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
                
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $r = mysql_query("select orden.id as id, cliente.name as cliente, orden.date as fecha from orden, cliente where cliente.id=orden.cliente_id  $rule order by orden.date asc;", $con);
                if($r)
                {
                    $data = array();
                    
                    while($row = mysql_fetch_assoc($r))
                        $data[] = $row['id'].'=>'.$row['cliente'].'=>'.$row['fecha'].'=>'.$row['id'];
                    
                    $data = count($data) > 0 ? join('|:|', $data) : '_NONE_';
                }
                else
                    Tool::getDbError($con);
                    
                Tool::closeDbCon($con);
            }
        }
        
        return Gestion::utf8Fix($data);
    }
    
    public function getEnForDay($controller, $day)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from entrega where date like '$day 00%') as h00, ";   
                $sql .= "(select sum(valor) from entrega where date like '$day 01%') as h01, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 02%') as h02, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 03%') as h03, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 04%') as h04, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 05%') as h05, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 06%') as h06, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 07%') as h07, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 08%') as h08, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 09%') as h09, ";
                $sql .= "(select sum(valor) from entrega where date like '$day 10%') as h10, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 11%') as h11, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 12%') as h12, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 13%') as h13, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 14%') as h14, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 15%') as h15, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 16%') as h16, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 17%') as h17, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 18%') as h18, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 19%') as h19, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 20%') as h20, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 21%') as h21, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 22%') as h22, "; 
                $sql .= "(select sum(valor) from entrega where date like '$day 23%') as h23, ";
                $sql .= "(select count(id) from entrega where date like '$day 00%') as c00, ";   
                $sql .= "(select count(id) from entrega where date like '$day 01%') as c01, "; 
                $sql .= "(select count(id) from entrega where date like '$day 02%') as c02, "; 
                $sql .= "(select count(id) from entrega where date like '$day 03%') as c03, "; 
                $sql .= "(select count(id) from entrega where date like '$day 04%') as c04, "; 
                $sql .= "(select count(id) from entrega where date like '$day 05%') as c05, "; 
                $sql .= "(select count(id) from entrega where date like '$day 06%') as c06, "; 
                $sql .= "(select count(id) from entrega where date like '$day 07%') as c07, "; 
                $sql .= "(select count(id) from entrega where date like '$day 08%') as c08, "; 
                $sql .= "(select count(id) from entrega where date like '$day 09%') as c09, ";
                $sql .= "(select count(id) from entrega where date like '$day 10%') as c10, "; 
                $sql .= "(select count(id) from entrega where date like '$day 11%') as c11, "; 
                $sql .= "(select count(id) from entrega where date like '$day 12%') as c12, "; 
                $sql .= "(select count(id) from entrega where date like '$day 13%') as c13, "; 
                $sql .= "(select count(id) from entrega where date like '$day 14%') as c14, "; 
                $sql .= "(select count(id) from entrega where date like '$day 15%') as c15, "; 
                $sql .= "(select count(id) from entrega where date like '$day 16%') as c16, "; 
                $sql .= "(select count(id) from entrega where date like '$day 17%') as c17, "; 
                $sql .= "(select count(id) from entrega where date like '$day 18%') as c18, "; 
                $sql .= "(select count(id) from entrega where date like '$day 19%') as c19, "; 
                $sql .= "(select count(id) from entrega where date like '$day 20%') as c20, "; 
                $sql .= "(select count(id) from entrega where date like '$day 21%') as c21, "; 
                $sql .= "(select count(id) from entrega where date like '$day 22%') as c22, "; 
                $sql .= "(select count(id) from entrega where date like '$day 23%') as c23 ";
                $sql .= "from entrega limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getEnForWeek($controller, $day)
    {
        $p = new \DateTime($day);
        $d = $p->format('w');
        $p->modify('-'.($d+1).' day');
        
        $fcs = array();
        $r = '';
        for($i = 0; $i < 7; $i++)
        {
            $p->modify('+1 day');
            $fcs[] = $p->format('Y-m-d');
        }
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        $data[] = $fcs[0].' a '.$fcs[6];
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[0]." %') as v00, ";
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[1]." %') as v01, ";
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[2]." %') as v02, ";
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[3]." %') as v03, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[4]." %') as v04, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[5]." %') as v05, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$fcs[6]." %') as v06, "; 
                $sql .= "(select count(id) from entrega where date like '".$fcs[0]." %') as c00, ";
                $sql .= "(select count(id) from entrega where date like '".$fcs[1]." %') as c01, "; 
                $sql .= "(select count(id) from entrega where date like '".$fcs[2]." %') as c02, "; 
                $sql .= "(select count(id) from entrega where date like '".$fcs[3]." %') as c03, "; 
                $sql .= "(select count(id) from entrega where date like '".$fcs[4]." %') as c04, ";
                $sql .= "(select count(id) from entrega where date like '".$fcs[5]." %') as c05, "; 
                $sql .= "(select count(id) from entrega where date like '".$fcs[6]." %') as c06 ";
                $sql .= "from entrega limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [$data[0],0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getEnForMonth($controller, $month)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        $vals = $this->getArray($month);
        $cans = $vals;
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select valor, date from entrega where date like '".$month."%';";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                    {
                        $k = explode(' ', $row['date']);
                        $k = $k[0];
                        
                        $vals[$k] += floatval($row['valor']);
                        $cans[$k] += 1;
                    }
                    
                    foreach($vals as $v)
                        $data[] = $v;
                    
                    foreach($cans as $c)
                        $data[] = $c;
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getEnForYear($controller, $year)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-01%') as v01, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-02%') as v02, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-03%') as v03, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-04%') as v04, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$year."-05%') as v05, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$year."-06%') as v06, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-07%') as v07, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-08%') as v08, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-09%') as v09, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$year."-10%') as v10, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-11%') as v11, ";
                $sql .= "(select sum(valor) from entrega where date like '".$year."-12%') as v12, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-01%') as c01, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-02%') as c02, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-03%') as c03, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-04%') as c04, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-05%') as c05, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-06%') as c06, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-07%') as c07, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-08%') as c08, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-09%') as c09, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-10%') as c10, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-11%') as c11, ";
                $sql .= "(select count(id) from entrega where date like '".$year."-12%') as c12 ";
                $sql .= "from entrega limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getEnForDeca($controller, $year)
    {
        $ly = array();
        $year = intval($year)-9;
        
        for($i = 0; $i < 10; $i++)
            $ly[] = $year+$i;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[0]."%') as v01, ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[1]."%') as v02, ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[2]."%') as v03, ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[3]."%') as v04, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$ly[4]."%') as v05, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$ly[5]."%') as v06, ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[6]."%') as v07, ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[7]."%') as v08, ";
                $sql .= "(select sum(valor) from entrega where date like '".$ly[8]."%') as v09, "; 
                $sql .= "(select sum(valor) from entrega where date like '".$ly[9]."%') as v10, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[0]."%') as c01, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[1]."%') as c02, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[2]."%') as c03, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[3]."%') as c04, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[4]."%') as c05, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[5]."%') as c06, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[6]."%') as c07, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[7]."%') as c08, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[8]."%') as c09, ";
                $sql .= "(select count(id) from entrega where date like '".$ly[9]."%') as c10 ";
                $sql .= "from entrega limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getPerForDay($controller, $day)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from perdida where date like '$day 00%') as h00, ";   
                $sql .= "(select sum(valor) from perdida where date like '$day 01%') as h01, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 02%') as h02, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 03%') as h03, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 04%') as h04, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 05%') as h05, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 06%') as h06, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 07%') as h07, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 08%') as h08, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 09%') as h09, ";
                $sql .= "(select sum(valor) from perdida where date like '$day 10%') as h10, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 11%') as h11, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 12%') as h12, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 13%') as h13, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 14%') as h14, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 15%') as h15, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 16%') as h16, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 17%') as h17, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 18%') as h18, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 19%') as h19, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 20%') as h20, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 21%') as h21, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 22%') as h22, "; 
                $sql .= "(select sum(valor) from perdida where date like '$day 23%') as h23, ";
                $sql .= "(select count(id) from perdida where date like '$day 00%') as c00, ";   
                $sql .= "(select count(id) from perdida where date like '$day 01%') as c01, "; 
                $sql .= "(select count(id) from perdida where date like '$day 02%') as c02, "; 
                $sql .= "(select count(id) from perdida where date like '$day 03%') as c03, "; 
                $sql .= "(select count(id) from perdida where date like '$day 04%') as c04, "; 
                $sql .= "(select count(id) from perdida where date like '$day 05%') as c05, "; 
                $sql .= "(select count(id) from perdida where date like '$day 06%') as c06, "; 
                $sql .= "(select count(id) from perdida where date like '$day 07%') as c07, "; 
                $sql .= "(select count(id) from perdida where date like '$day 08%') as c08, "; 
                $sql .= "(select count(id) from perdida where date like '$day 09%') as c09, ";
                $sql .= "(select count(id) from perdida where date like '$day 10%') as c10, "; 
                $sql .= "(select count(id) from perdida where date like '$day 11%') as c11, "; 
                $sql .= "(select count(id) from perdida where date like '$day 12%') as c12, "; 
                $sql .= "(select count(id) from perdida where date like '$day 13%') as c13, "; 
                $sql .= "(select count(id) from perdida where date like '$day 14%') as c14, "; 
                $sql .= "(select count(id) from perdida where date like '$day 15%') as c15, "; 
                $sql .= "(select count(id) from perdida where date like '$day 16%') as c16, "; 
                $sql .= "(select count(id) from perdida where date like '$day 17%') as c17, "; 
                $sql .= "(select count(id) from perdida where date like '$day 18%') as c18, "; 
                $sql .= "(select count(id) from perdida where date like '$day 19%') as c19, "; 
                $sql .= "(select count(id) from perdida where date like '$day 20%') as c20, "; 
                $sql .= "(select count(id) from perdida where date like '$day 21%') as c21, "; 
                $sql .= "(select count(id) from perdida where date like '$day 22%') as c22, "; 
                $sql .= "(select count(id) from perdida where date like '$day 23%') as c23 ";
                $sql .= "from perdida limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getPerForWeek($controller, $day)
    {
        $p = new \DateTime($day);
        $d = $p->format('w');
        $p->modify('-'.($d+1).' day');
        
        $fcs = array();
        $r = '';
        for($i = 0; $i < 7; $i++)
        {
            $p->modify('+1 day');
            $fcs[] = $p->format('Y-m-d');
        }
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        $data[] = $fcs[0].' a '.$fcs[6];
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[0]." %') as v00, ";
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[1]." %') as v01, ";
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[2]." %') as v02, ";
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[3]." %') as v03, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[4]." %') as v04, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[5]." %') as v05, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$fcs[6]." %') as v06, "; 
                $sql .= "(select count(id) from perdida where date like '".$fcs[0]." %') as c00, ";
                $sql .= "(select count(id) from perdida where date like '".$fcs[1]." %') as c01, "; 
                $sql .= "(select count(id) from perdida where date like '".$fcs[2]." %') as c02, "; 
                $sql .= "(select count(id) from perdida where date like '".$fcs[3]." %') as c03, "; 
                $sql .= "(select count(id) from perdida where date like '".$fcs[4]." %') as c04, ";
                $sql .= "(select count(id) from perdida where date like '".$fcs[5]." %') as c05, "; 
                $sql .= "(select count(id) from perdida where date like '".$fcs[6]." %') as c06 ";
                $sql .= "from perdida limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [$data[0],0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getPerForMonth($controller, $month)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        $vals = $this->getArray($month);
        $cans = $vals;
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select valor, date from perdida where date like '".$month."%';";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    while($row = mysql_fetch_assoc($r))
                    {
                        $k = explode(' ', $row['date']);
                        $k = $k[0];
                        
                        $vals[$k] += floatval($row['valor']);
                        $cans[$k] += 1;
                    }
                    
                    foreach($vals as $v)
                        $data[] = $v;
                    
                    foreach($cans as $c)
                        $data[] = $c;
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getPerForYear($controller, $year)
    {
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-01%') as v01, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-02%') as v02, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-03%') as v03, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-04%') as v04, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$year."-05%') as v05, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$year."-06%') as v06, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-07%') as v07, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-08%') as v08, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-09%') as v09, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$year."-10%') as v10, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-11%') as v11, ";
                $sql .= "(select sum(valor) from perdida where date like '".$year."-12%') as v12, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-01%') as c01, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-02%') as c02, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-03%') as c03, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-04%') as c04, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-05%') as c05, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-06%') as c06, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-07%') as c07, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-08%') as c08, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-09%') as c09, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-10%') as c10, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-11%') as c11, ";
                $sql .= "(select count(id) from perdida where date like '".$year."-12%') as c12 ";
                $sql .= "from perdida limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [0,0];
                    }
                }
                else
                    Tool::getDbError($con);
                
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    public function getPerForDeca($controller, $year)
    {
        $ly = array();
        $year = intval($year)-9;
        
        for($i = 0; $i < 10; $i++)
            $ly[] = $year+$i;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
        
        $data = array();
        
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[0]."%') as v01, ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[1]."%') as v02, ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[2]."%') as v03, ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[3]."%') as v04, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$ly[4]."%') as v05, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$ly[5]."%') as v06, ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[6]."%') as v07, ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[7]."%') as v08, ";
                $sql .= "(select sum(valor) from perdida where date like '".$ly[8]."%') as v09, "; 
                $sql .= "(select sum(valor) from perdida where date like '".$ly[9]."%') as v10, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[0]."%') as c01, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[1]."%') as c02, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[2]."%') as c03, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[3]."%') as c04, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[4]."%') as c05, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[5]."%') as c06, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[6]."%') as c07, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[7]."%') as c08, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[8]."%') as c09, ";
                $sql .= "(select count(id) from perdida where date like '".$ly[9]."%') as c10 ";
                $sql .= "from perdida limit 1;";
                
                $r = mysql_query($sql, $con);
                if($r)
                {
                    $row = mysql_fetch_assoc($r);
                    if(is_array($row))
                    {
                        foreach($row as $w)
                        {
                            if($w != null)
                                $data[] = $w;
                            else
                                $data[] = 0;
                        }
                    }
                    else
                    {
                        $data = [0,0];
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
    
    public function getItemsA($controller, $id)
    {
        $item = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select papel.id as idx, papel.name as fichero, material.name as material, tinta.name as tinta, pages as paginas, amount as cantidad, unit as unitario, papel.value as valor, papel.data as notas, papel.expiry as caduca from papel, material, tinta where material.id = papel.material_id and tinta.id = papel.tinta_id and orden_id='$id' order by idx asc;";
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
    
    public function getAcabadosA($controller, $id)
    {
        $item = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select acabado.name as acabado from papelAcabado, acabado where acabado.id = papelAcabado.acabado_id and papelAcabado.papel_id='$id' order by acabado asc;";
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
    
    public function getItemsB($controller, $id)
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
    
    public function getAcabadosB($controller, $id)
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
    
    public function getEntrega($controller, $id)
    {
        $entr = null;
        
        $lic = Gestion::getLicencia(Gestion::getDomain($controller));
            
        if($lic)
        {
            $con = Tool::newDbCon($lic);
            
            if($con)
            {
                $sql = "select * from entrega where entrega.orden_id='$id' limit 1;";
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
    
    public function logCat($controller, $id)
    {
        $oid = Gestion::sqlKill($id);
        
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
                
                Tool::closeDbCon($con);
            }
        }
        
        return $data;
    }
    
    private function getArray($month)
    {
        $LM = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        
        $month = explode('-', $month);
        $Y = intval($month[0]);
        $M = intval($month[1]);
        
        $r = $LM[$M-1];
        if( ((!($Y % 4) && ($Y % 100)) || !($Y % 400)) && $M == 2 )
            $r += 1;
            
        $arr = array();
        
        for($i = 1; $i <= $r; $i++)
        {
            $sy = $Y < 10 ? '0'.$Y : ''.$Y;
            $sm = $M < 10 ? '0'.$M : ''.$M;
            $sd = $i < 10 ? '0'.$i : ''.$i;
            
            $arr["$sy-$sm-$sd"] = 0;
        }
        
        return $arr;
    }
}
 
