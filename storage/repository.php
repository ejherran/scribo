<?php
    
    init();
    
    function init()
    {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            if(isset($_POST['action']))
            {
                if($_POST['action'] == 'test')
                    crossMsg('Ok!');
                else if($_POST['action'] == 'save')
                    save();
                else if($_POST['action'] == 'hash')
                    getHash();
                else if($_POST['action'] == 'delete')
					delete();
                else
                    crossMsg('No Action!...');
            }    
            else
                crossMsg("No Action!");
        }
        else
        {
            crossMsg("<b>Access denied, only requests scribo platform allowed!.</b>");
        }
    }

    function save()
    {
        $dir = './files/';
        $name = $_POST['upName'];
        $data = $_POST['upData'];
        $out = $_POST['upOut'];
        
        if($out == '@')
            $name = fakeName($dir, $name);
        else
            $name = $out;
            
        $data = explode(',', $data);
        $data = count($data) > 1 ? $data[1] : $data[0];
        
        $fp = fopen($dir.$name.'.b64',"ab");
        fwrite($fp, $data);
        fclose($fp);
        
        crossMsg($name);
    }
    
    function getHash()
    {
        $dir = './files/';
        $name = $_POST['upName'];
        $res = '-1';
        
        if(file_exists($dir.$name.'.b64'))
        {
            $chunkSize = 1048576;
			$src = fopen($dir.$name.'.b64', 'rb');
			$dst = fopen($dir.$name, 'wb');
			while (!feof($src)) 
			{
				fwrite($dst, base64_decode(fread($src, $chunkSize)));
			}
			fclose($dst);
			fclose($src);
            unlink($dir.$name.'.b64');
            
            $res = sha1_file($dir.$name);
        }
        
        crossMsg($res);
    }
    
    function delete()
	{
		$dir = './files/';
        $target = $_POST['target'];
        $target = explode(',', $target);
        
        foreach($target as $t)
			unlink($dir.$t);
			
		crossMsg('0');
	}
	
    function crossMsg($msg)
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) 
        {  
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");  
            header('Access-Control-Allow-Credentials: true');  
            header('Access-Control-Max-Age: 1');   
        }  
      
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') 
        {  
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))  
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
      
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))  
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        
        echo $msg;
    }
    
    function fakeName($dir, $name)
    {
        $time = explode(' ', microtime());
        $orig = $name;
        $r1 = mt_rand(0, 10000);
        $r2 = mt_rand(0, 10000);
        $r3 = mt_rand(0, 10000);
        $r4 = mt_rand(0, 10000);
        
        $name = explode('.', $name);
        $name = count($name) > 1 ? '.'.$name[count($name)-1] : '.scr';
        
        $tmp = '';
            
        while(true)
        {
            $tmp = strtoupper( sha1( $r1.$orig.$r2.$time[0].$r3.$time[1].$r4 ) ).''.$name;
            if(!file_exists($dir.$tmp))
                break;
        }
            
        $name = $tmp;
        
        return $name;
    }
?>
