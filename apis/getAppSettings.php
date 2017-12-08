<?php
session_start();

require 'function.php';

$data = json_decode(file_get_contents('php://input'),true);

if(isset($data['setting'])){
	
	$setting = $data['setting'];
	
	$get_setting = 'SELECT '.$setting.' FROM `crm_settings` LIMIT 1';
	
	$result = mysql_query($get_setting);
	
	if($result && mysql_num_rows($result) > 0){
		
		$setting_data = mysql_fetch_object($result);
		
		echo $setting_data -> $setting;
		
	}else{
		echo 0; exit;
	}
}else{
	echo 0; exit;
}
