<?php

require_once '../../function.php';

function getChartColor(){
    
    $colors = ['#777777','#800000','#800000'];
    
    return $colors[rand(0,2)];
}

$get = $_GET;

$agent_id = '';

$from_date = date('Y-m-d');

$to_date = '';

if( isset($get['agent_id'])){
    $agent_id = $get['agent_id'];
}

if(isset($get['filter_date_from'])){
    
    $from_date = $get['filter_date_from'];
}

if(isset($get['filter_date_to'])){   
    $to_date = $get['filter_date_to'];
}

if($to_date == ''){

    $query = 'SELECT hot_warm_cold_status, group_concat(distinct enquiry_id) as enquiry_numbers,            COUNT(distinct enquiry_id) as total_count, group_concat(distinct enquiry_id) as enquiry_numbers
        FROM `lead_status` 
        WHERE 
        ( DATE (date) = "'.$from_date.'"  ) 
        AND user_id = "'.$agent_id.'" AND user_type     = "agent" 
        AND hot_warm_cold_status != ""
        AND disposition_status_id = 34
        group by hot_warm_cold_Status
        order by hot_warm_cold_status';

}
else{

    $query = 'SELECT hot_warm_cold_status, group_concat(distinct enquiry_id) as enquiry_numbers, COUNT(distinct enquiry_id) as total_count
    FROM `lead_status` 
    WHERE 
    ( DATE (date) between "'.$from_date.'" AND "'.$to_date.'" ) AND
    user_id = "'.$agent_id.'" AND 
    user_type     = "agent" AND
    disposition_status_id = 34 AND 
    hot_warm_cold_status != ""
    group by hot_warm_cold_Status
    order by hot_warm_cold_status';

}

$resulset = mysql_query($query);

$data = [];

if($resulset && mysql_num_rows($resulset) > 0){
    
    $total = 0;
    
    while($row = mysql_fetch_assoc($resulset)){
     
        $total = $total + $row['total_count'];
        
        $data['labels'][] = $row['hot_warm_cold_status']; 
        $data['data'][] = $row['total_count'];
        $data['label_bg_colors'][] = getChartColor();
        $data['enquiries'][] = $row['enquiry_numbers'];
    }
    
    $data['total_count'] = $total;
}else{   
    $data['total_count'] = 0;
}


echo json_encode($data,true);