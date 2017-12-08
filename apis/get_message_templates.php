<?php
session_start();

require 'function.php';

$template_id = '';

$message_templates = array();

$where_template_id = '';

if(isset($_GET['template_id']) && $_GET['template_id'] != ''){
	
	$template_id = $_GET['template_id']; // Get template id for single template select
	$where_template_id .= ' WHERE template_id = '.$template_id;
}

if($where_template_id != ''){
		$select_email_template  = 'SELECT * FROM `message_templates` '. $where_template_id;
}else{
		$select_email_template  = 'SELECT * FROM `message_templates`';
}

$result = mysql_query($select_email_template);

if($result && mysql_num_rows($result) > 0){
	
	while($row = mysql_fetch_assoc($result)){
		
		$numbers = explode(',',$row['default_numbers']);
		unset($row['default_numbers']);
		$row['default_numbers'] = $numbers;
		
		array_push($message_templates, $row);
	}
}

echo json_encode($message_templates, true);