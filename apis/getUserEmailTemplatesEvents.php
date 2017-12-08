<?php
session_status();

require 'function.php';

//$data = json_decode(file_get_contents('php://input'));

$data = filter_input_array(INPUT_POST);

$user_id = '';

$event_templates = array();

if(!empty($data)){
	
	$user_id	= $data['user_id'];
	$sql		= 'SELECT `assigned_disposition_status_json` FROM employees WHERE id = '.$user_id.' LIMIT 1';
	$result		= mysql_query($sql);
	
	if($result && mysql_num_rows($result) > 0){
		
		$row = mysql_fetch_assoc($result);
	
		$disposition_status_array = json_decode($row['assigned_disposition_status_json'],true);
		
		
		echo '<pre>'; print_r($disposition_status_array); 
		
		if(!empty($disposition_status_array)){
			
			foreach($disposition_status_array as $status){
				
				foreach($status as $parent => $childs){
					
					
					
					
				}
			}
		}
		
	}
}