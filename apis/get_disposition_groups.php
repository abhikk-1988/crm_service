<?php

require_once 'db_connection.php';


$user_disposition_groups = array();

$select_group = 'SELECT id, group_title FROM disposition_group';

$result = mysql_query($select_group);

if($result){
	
	while ($row=mysql_fetch_assoc($result)){
		
		array_push($user_disposition_groups,$row);
	}
}

echo json_encode($user_disposition_groups,true); exit; 