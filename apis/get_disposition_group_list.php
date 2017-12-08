<?php
ini_set('max_execution_time', 120);
require_once 'db_connection.php';

$select_group = 'SELECT master_tbl.*, GROUP_CONCAT(junction_tbl.disposition_status_id) as group_status_ids '
		. ' FROM `disposition_group` as master_tbl '
		. ' LEFT JOIN disposition_group_status_master as junction_tbl ON (master_tbl.id = junction_tbl.disposition_group_id) '
		. ' GROUP by master_tbl.group_title '
		. ' ORDER BY master_tbl.`group_title` ';

$result = mysql_query($select_group);

$groups = array();

if($result){
    
    if(mysql_num_rows($result) > 0){
        
        while($row = mysql_fetch_assoc($result)){
            
		   $row['childs'] = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getDispositionStatusHeirarchy&params=ids:'.$row['group_status_ids']),true);
            
		   array_push($groups,$row);
        }
    }
}

echo json_encode(array('success' => 1, 'data' => $groups),true);