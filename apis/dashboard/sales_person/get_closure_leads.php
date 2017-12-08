<?php

require_once '../../function.php';

$user_id = '';
$date_filter1 = '';
$date_filter2 = '';

if(isset($_GET['user_id'])){
    $user_id = $_GET['user_id'];
}

$where_date = '';
$where_date_closure = '';

if(isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != ''){
    $date_filter_1 = $_GET['filter_date_from'];
    $where_date = ' AND DATE(entry_date) = "'.$date_filter_1.'" ';
    $where_date_closure = ' AND DATE(leadUpdateDate) = "'.$date_filter_1.'" ';
}

if(isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != ''){
    $date_filter_2 = $_GET['filter_date_to'];
}

if($date_filter_1 != '' && $date_filter_2 != ''){
    $where_date = ' AND DATE(entry_date) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
    $where_date_closure = ' AND DATE(leadUpdateDate) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
}

$query = 'SELECT DISTINCT(enquiry_id), payment_type,COUNT(DISTINCT(enquiry_id)) as total '
        . ' FROM payment_collection'
        . ' WHERE employee_id = '. $user_id .' '. $where_date
        . '  GROUP BY payment_type';

$result     = mysql_query($query);

$total_rows = mysql_num_rows($result);
$data       = array();
$data['total_closure'] = 0;
$data['status'] = array();
$data['enquiry'] = array();


if($total_rows > 0){
    while($row = mysql_fetch_assoc($result)){
        $data['total_closure'] += $row['total'];
        
        $type = '';
        if($row['payment_type'] == 'cheque'){
            $type = 'Cheque';
        }
        else if($row['payment_type'] == 'ot'){
            $type = 'Online Transaction';
        }
        
        $data['status'][$type] = $row['total'];
        
        array_push($data['enquiry'], $row['enquiry_id']);
    }
}



// Closure Leads 
$closure_leads  = 'SELECT COUNT(DISTINCT(enquiry_id)) as total_closed, enquiry_id '
        . ' FROM lead '
        . ' WHERE lead_closed_by = '.$user_id.' '. $where_date_closure;

$closure_result = mysql_query($closure_leads);

if(mysql_num_rows($closure_result) > 0){
    
    $closure_data               = mysql_fetch_row($closure_result);
        
    $data['total_closure']      +=  $closure_data[0];
    $data['status']['Close']    = (int)$closure_data[0];
    $data['close_enquiry'] = $closure_data[1];
}

echo json_encode($data,true);