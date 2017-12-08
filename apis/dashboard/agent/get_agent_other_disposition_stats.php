<?php

require '../../function.php';

$get_data = $_GET;

if(isset($get_data['agent_id'])){
    $agent_id = $get_data['agent_id'];
}

if(isset($get_data['filter_date_from'])){
    $date_from = $get_data['filter_date_from'];
}
else{
    $date_from = date('Y-m-d');
}

if(isset($get_data['filter_date_to'])){
    $date_to = $get_data['filter_date_to'];
}else{
    $date_to = '';
}


if($date_to == ''){

    $query = 'SELECT COUNT(a.disposition_status_id) as total_status , b.status_title
    FROM `lead` as a
    LEFT JOIN disposition_status_substatus_master as b ON (a.disposition_status_id = b.id)
    WHERE a.disposition_status_id IN (1,5,38) AND (a.lead_added_by_user = '.$agent_id.' OR reassign_user_id = '.$agent_id.') AND (Date(a.leadAddDate) = "'.$date_from.'" OR Date(leadUpdateDate) = "'.$date_from.'")
    GROUP BY a.disposition_status_id';
}else{
    $query = 'SELECT COUNT(a.id) as total_status , b.status_title
FROM `lead` as a
LEFT JOIN disposition_status_substatus_master as b ON (a.disposition_status_id = b.id)
WHERE a.disposition_status_id IN (1,5,38) 
AND (a.lead_added_by_user = '.$agent_id.' OR reassign_user_id = '.$agent_id.')
AND  ( Date(a.leadAddDate) BETWEEN "'.$date_from.'" AND "'.$date_to.'" OR DATE(a.leadUpdateDate) BETWEEN "'.$date_from.'" AND "'.$date_to.'") 
GROUP BY a.disposition_status_id';
}

$result = mysql_query($query);

$data = array();

$data['total_count'] = $total_count = 0; 
    
if($result && mysql_num_rows($result) > 0){
    
    $data['status'] = array();
    
    while ($row = mysql_fetch_assoc($result)){
        
        $total_count = $total_count + $row['total_status'];
        
        array_push($data['status'], array(
            'status' => $row['status_title'], 'count' => $row['total_status']
        ));
        
    }
    
    $data['total_count'] = $total_count;
}

echo json_encode($data,true);