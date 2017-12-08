<?php

require_once 'db_connection.php';

$query = 'SELECT tbl1.`id`, tbl1.`sub_status_title`,tbl1.is_activity_status,tbl1.active_state, tbl2.id as parent_id, tbl2.status_title as parent_status_title FROM `disposition_status_substatus_master` as tbl1 
INNER JOIN disposition_status_substatus_master as tbl2 ON (tbl1.parent_status = tbl2.id) 
WHERE tbl1.`parent_status` IS NOT NULL ORDER BY tbl1.sub_status_title';

$result = mysql_query($query);

$status = array();

if($result){
    
    while($row = mysql_fetch_assoc($result)){
        $is_activity_status = (bool) $row['is_activity_status'];
        $active_state = (bool) $row['active_state'];
        
        unset($row['is_activity_status']); 
        unset($row['active_state']);
        
        $row['is_activity_status'] = $is_activity_status;
        $row['active_state'] = $active_state;
        array_push($status,$row);
    }
}
echo json_encode($status,true); exit;