<?php
session_start();
require 'function.php';

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['asm_id']) && $data['asm_id'] != ''){
	
	$current_year	= date('Y');
	$current_month	= (int) date('m') - 1;
	
	$condition = ' capacity_month = '.$current_month.' AND capacity_year = "'.$current_year.'"';
	
	$sql = 'SELECT DISTINCT(pId) FROM capacity_master WHERE userId != '.$data['asm_id'].' AND '. $condition;
	
	$result = mysql_query($sql);
	
	$projects = array();
	
	if($result && mysql_num_rows($result) > 0){
		while($row = mysql_fetch_assoc($result)){
			array_push($projects, $row['pId']);
		}
	}

	echo json_encode($projects, true); exit;
}else{
	echo json_encode(array(),true); exit;
}


