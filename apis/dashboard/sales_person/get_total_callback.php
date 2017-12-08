<?php

require_once '../../function.php';

$user_id    =  '';
$date1      =   '';
$date2      =   '';

if(isset($_GET['user_id'])){
    $user_id = $_GET['user_id'];
}

$where_date = '';

if(isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != ''){
    $date1  = $_GET['filter_date_from'];    
    $where_date     = ' AND DATE(future_followup_date) = "'.$date1.'"';
}

if(isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != ''){
    $date2  = $_GET['filter_date_to'];
}

if($date1 != '' && $date2 != ''){
    
    // Subtract 1 dat from from date 
    $where_date = ' AND DATE(future_followup_date) BETWEEN "'.$date1.'" AND "'.$date2.'"';
}

$query = 'SELECT GROUP_CONCAT(enquiry_id) as enquiry_ids,COUNT(enquiry_id) as status_wise_count , leadStatus as hot_warm_cold_status'
        . ' FROM lead '
        . ' WHERE lead_assigned_to_sp = '. $user_id . ' AND is_lead_accepted = 1 AND disposition_status_id = 47 AND lead_closure_date IS NULL '. $where_date
        . ' GROUP BY leadStatus';


$result         = mysql_query($query);
$response_array = array();
$response_array['status']  = array();
$response_array['inquiry'] = array();

$total_rows = mysql_num_rows($result);

if($total_rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        switch(strtolower($row['hot_warm_cold_status'])){
            
            case 'hot':
                $response_array['status']['hot'] = array('count' => $row['status_wise_count'],'enquiry' => explode(',',$row['enquiry_ids']));
                $response_array['total_callback'] += $row['status_wise_count'];
                break;
            case 'warm':
                $response_array['status']['warm'] = array('count' => $row['status_wise_count'],'enquiry' => explode(',',$row['enquiry_ids']));            
                 $response_array['total_callback'] += $row['status_wise_count'];
                break;
            case 'cold':
                $response_array['status']['cold'] = array('count' => $row['status_wise_count'],'enquiry' => explode(',',$row['enquiry_ids']));
                 $response_array['total_callback'] += $row['status_wise_count'];
                break;
        }
        
        array_push($response_array['inquiry'],$row['enquiry_ids']);
    }
}
else{
    $response_array['total_callback'] = 0;
}

// Response output
echo json_encode($response_array,true);
