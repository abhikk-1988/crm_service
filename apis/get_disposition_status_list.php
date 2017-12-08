<?php
require_once 'db_connection.php';

$disposition_status = array();

$select_disposition = 'SELECT id, status_title, parent_status, sub_status_title '
		. ' FROM disposition_status_substatus_master';

$result = mysql_query($select_disposition);

if($result){
	
	while($row = mysql_fetch_assoc($result)){
		
		array_push($disposition_status, $row);
	}
}

echo json_encode($disposition_status, true);

