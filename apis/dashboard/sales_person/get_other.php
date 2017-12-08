<?php

require_once '../../function.php';

$user_id        = '';
$filter_date1   = '';
$filter_date2   = '';

if(isset($_GET['user_id']) && $_GET['user_id'] != ''){
    
    $user_id = $_GET['user_id'];
}

$where_date = '';

$where_removed_date = '';

if(isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != ''){
    $date_filter_1 = $_GET['filter_date_from'];
    $where_date = ' AND DATE(leadUpdateDate) = "'.$date_filter_1.'" ';
    $where_removed_date = ' AND DATE(remove_date) = "'.$date_filter_1.'" ';
}

if(isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != ''){
    $date_filter_2 = $_GET['filter_date_to'];
}

if($date_filter_1 != '' && $date_filter_2 != ''){
    $where_date = ' AND DATE(leadUpdateDate) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
    $where_removed_date = ' AND DATE(remove_date) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
}


$query = 'SELECT COUNT(ld.enquiry_id) as total, GROUP_CONCAT(ld.enquiry_id) as enquiry_ids, dsma.status_title '
        . ' FROM lead as ld'
        . ' LEFT JOIN disposition_status_substatus_master as dsma ON (ld.disposition_status_id = dsma.id)'
        . ' WHERE ld.is_lead_accepted = 1 AND (ld.lead_assigned_to_sp = '.$user_id.' OR ld.reassign_user_id = '.$user_id.') AND ld.lead_closure_date IS NULL AND ld.disposition_status_id NOT IN (3,6,47,7) '. $where_date
        . '  GROUP BY dsma.status_title';

$result = mysql_query($query);

$rows = mysql_num_rows($result);

$total_count = 0; 

$data = array();

$data['others_total'] = 0;

$data['status'] = array();

if($rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        $total_count += $row['total'];
        
        $temp = array();
                
        $data['status'][$row['status_title']] = (int)$row['total'];
    }
    
    $data['others_total'] = $total_count;
}


// Fetch Meeting Not Done Data 

$meeting_not_done = 'SELECT COUNT(ld.enquiry_id) as total, GROUP_CONCAT(ld.enquiry_id) as enquiry_ids, dsma.status_title '
        . ' FROM lead as ld'
        . ' LEFT JOIN disposition_status_substatus_master as dsma ON (ld.disposition_status_id = dsma.id)'
        . ' WHERE ld.is_lead_accepted = 1 AND (ld.lead_assigned_to_sp = '.$user_id.' OR ld.reassign_user_id = '.$user_id.') AND ld.lead_closure_date IS NULL AND ld.disposition_status_id = 3 AND ld.disposition_sub_status_id = 44 '. $where_date
        . '  GROUP BY dsma.status_title';

$meeting_not_done_result = mysql_query($meeting_not_done);

if(mysql_num_rows($meeting_not_done_result) > 0){
    
   $meeting_not_done_result_row = mysql_fetch_object($meeting_not_done_result);
   
   $data['status']['meeting_not_done'] = (int)$meeting_not_done_result_row -> total;
   
   $data['others_total'] += $meeting_not_done_result_row -> total;
   
}

// Get Removed Leads 

$removed_leads = 'SELECT COUNT(enquiry_id) as total , t2.status_title 
FROM `lead_assignment_sales` as t1
LEFT JOIN disposition_status_substatus_master as t2 ON (t1.disposition_status_id = t2.id)
WHERE sp_id = '.$user_id. ' '. $where_removed_date . '
GROUP BY disposition_status_id';

$removed_leads_result = mysql_query($removed_leads);

$removed_leads_count = mysql_num_rows($removed_leads_result);

if($removed_leads_count > 0){
    
    $removed_total = 0;
    while($row = mysql_fetch_object($removed_leads_result)){
        
        $removed_total += $row -> total;
        $data['status'][$row -> status_title] = (int)$row -> total;
    }
    
    $data['others_total'] = $data['others_total'] + $removed_total;
}

// RESPONSE
echo json_encode($data,true);