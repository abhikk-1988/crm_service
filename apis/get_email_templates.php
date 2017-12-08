<?php
session_start();

require 'function.php';

$template_id = '';

$email_templates = array();

$where_template_id = '';

if(isset($_GET['template_id']) && $_GET['template_id'] != ''){
	
	$template_id = $_GET['template_id']; // Get template id for single template select
	$where_template_id .= ' WHERE template_id = '.$template_id;
}

if($where_template_id != ''){
		$select_email_template  = 'SELECT * FROM `email_templates` '. $where_template_id;
}else{
		$select_email_template  = 'SELECT * FROM `email_templates`';
}

$result = mysql_query($select_email_template);

if($result && mysql_num_rows($result) > 0){
	
	while($row = mysql_fetch_assoc($result)){
		
		$to_user	=	json_decode($row['to_users']);
		$cc_user	=	json_decode($row['cc_users']);
		$bcc_user	=	json_decode($row['bcc_users']);
		
		unset($row['to_users']); unset($row['cc-users']); unset($row['bcc_users']);
		
		$row['to_users'] = $to_user; $row['cc_users'] = $cc_user; $row['bcc_users'] = $bcc_user;
		
		array_push($email_templates, $row);
	}
}

echo json_encode($email_templates, true);