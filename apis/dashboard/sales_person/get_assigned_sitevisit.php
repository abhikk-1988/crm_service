<?php

require_once '../../function.php';

$user_id        = '';
$date_filter_1  = $date_filter_2 = '';

if(isset($_GET['user_id'])){
    $user_id = $_GET['user_id'];
}

$where_date = '';
$where_sitevisit_date = '';

if(isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != ''){
    $date_filter_1 = $_GET['filter_date_from'];
    $where_date = ' AND DATE(date) = "'.$date_filter_1.'" ';
    $where_sitevisit_date = ' AND DATE(leadUpdateDate) = "'.$date_filter_1.'" ';
}

if(isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != ''){
    $date_filter_2 = $_GET['filter_date_to'];
}

if($date_filter_1 != '' && $date_filter_2 != ''){
    $where_date = ' AND DATE(date) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
    $where_sitevisit_date = ' AND DATE(leadUpdateDate) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
}

$data   = 0;

$query = 'SELECT DISTINCT (
t1.`enquiry_id`
), t1.`to_user_id` , t1.`disposition_status_id` , t2.leadStatus, t2.is_lead_accepted, t2.lead_closure_date, t2.reassign_user_id, t3.id as rejected_enquiry
FROM  `lead_re_assign` AS t1
LEFT JOIN lead AS t2 ON ( t1.enquiry_id = t2.enquiry_id ) 
LEFT JOIN lead_assignment_sales as t3 ON (t1.enquiry_id = t3.enquiry_id)
WHERE  `to_user_id` = '.$user_id.' '. $where_date;

$result = mysql_query($query);

$total_records = mysql_num_rows($result);
    
$total_accepted_sitevisit = 0;
$total_accepted_sitevisit_enquiry_numbers = array();

$status_wise_result         = array();
$status_wise_result['hot']  = 0;
$status_wise_result['warm'] = 0;
$status_wise_result['cold'] = 0;
    
$enquiry = array();

if($result && $total_records > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        // count only accepted leads of sales person which is not reassigned to some other user
        if(is_null($row['reassign_user_id']) && $row['is_lead_accepted'] == 1 && $row['disposition_status_id'] == 6){
            array_push($total_accepted_sitevisit_enquiry_numbers, $row['enquiry_id']);
            $total_accepted_sitevisit++;
        }else{
            
            if($row['rejected_enquiry'] != ''){
                
                // Get first status of this enquiry 
                
                $get_first_status = mysql_query('SELECT id, disposition_status_id, hot_warm_cold_status 
                    FROM  `lead_status` 
                    WHERE enquiry_id = '.$row['enquiry_id'].'
                    ORDER BY id ASC 
                    LIMIT 1 ');
                
                $first_status_object = mysql_fetch_object($get_first_status);
                
                if(mysql_num_rows($get_first_status) > 0){
                    
                    if($first_status_object -> disposition_status_id == 6){
                     
                        $total_accepted_sitevisit++;
                    }
                }   
            }
        }
    } 
}



// get actual sitevisit leads 

$fetch_meetings = 'SELECT enquiry_id, count(enquiry_id) as total , leadStatus FROM lead WHERE (lead_assigned_to_sp = '.$user_id.' OR reassign_user_id = '.$user_id.') AND is_lead_accepted = 1 
 AND disposition_status_id  = 6 AND disposition_sub_status_id IN (14,23) AND lead_closure_date IS NULL '.$where_sitevisit_date.'
 group by leadStatus;';

$fetch_mneeting_result = mysql_query($fetch_meetings);

if(mysql_num_rows($fetch_mneeting_result) > 0){

    while($row = mysql_fetch_assoc($fetch_mneeting_result)){
        $status_wise_result[$row['leadStatus']] = $row['total'];            
    }  
}



echo json_encode(array(
    'total_sitevisit_count' => (int)$total_accepted_sitevisit, 
    'status_wise_count' => $status_wise_result
),true);