<?php

require_once '../../function.php';

$user_id = '';
$date_filter_1 = $date_filter_2 = '';

if(isset($_GET['user_id'])){
    $user_id = $_GET['user_id'];
}

$where_date = '';

if(isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != ''){
    $date_filter_1  = $_GET['filter_date_from'];    
    $where_date     = ' AND DATE(date) = "'.$date_filter_1.'"';
}

if(isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != ''){
    $date_filter_2  = $_GET['filter_date_to'];
}

if($date_filter_1 != '' && $date_filter_2 != ''){
    $where_date = ' AND DATE(date) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
}

$data = 0;

$query = 'SELECT DISTINCT (
t1.`enquiry_id`
), t1.`to_user_id` , t1.`disposition_status_id` , t2.leadStatus, t2.is_lead_accepted, t2.lead_closure_date, t2.reassign_user_id, t3.id as rejected_enquiry
FROM  `lead_re_assign` AS t1
LEFT JOIN lead AS t2 ON ( t1.enquiry_id = t2.enquiry_id ) 
LEFT JOIN lead_assignment_sales as t3 ON (t1.enquiry_id = t3.enquiry_id)
WHERE  `to_user_id` = '.$user_id.' '. $where_date;


$result = mysql_query($query);

$total_records = mysql_num_rows($result);

$total_accepted = 0;

if($result && $total_records > 0){
    while($row = mysql_fetch_assoc($result)){
        if(is_null($row['reassign_user_id']) && $row['is_lead_accepted'] == 1) {
            $total_accepted++;
        }
        else{
            if($row['rejected_enquiry'] != ''){
                $total_accepted++;
            }
        }        
    }
}

echo json_encode(array('total_accepted_count' => (int)$total_accepted),true);