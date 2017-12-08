<?php

/**
 * @description: API for getting total leads assign to sales person
 * @author Abhishek Agrawal <abhishek.agrawal@bookmyhouse.com>
 */

require_once '../../function.php';

$user_id = '';
$date_filter_1 = $date_filter_2 = '';


if(isset($_GET['user_id'])){
    $user_id = $_GET['user_id'];
}

$where_date = '';

if(isset($_GET['filter_date_from']) && ($_GET['filter_date_from'])){
    
    $date_filter_1 = $_GET['filter_date_from'];
    $where_date = ' AND DATE(date) = "'.$date_filter_1.'"';
}

if(isset($_GET['filter_date_to']) && ($_GET['filter_date_to'])){
    $date_filter_2 = $_GET['filter_date_to'];
}

if($date_filter_1 != '' && $date_filter_2 != ''){
    $where_date = ' AND DATE(date) BETWEEN "'.$date_filter_1.'" AND "'.$date_filter_2.'"';
}

$data = 0;

$query = 'SELECT COUNT(DISTINCT(enquiry_id)) as total_assigned, GROUP_CONCAT(DISTINCT(enquiry_id)) as enquiry '
        . ' FROM  `lead_re_assign` '
        . ' WHERE  `to_user_id` = '.$user_id . ' '. $where_date;

$result = mysql_query($query);

if($result && mysql_num_rows($result) > 0){
    
    $row = mysql_fetch_row($result);
    
    $data = $row[0];
    
    $enquiry = explode(',', $row[1]);
}

echo json_encode(array('total_assigned' => (int)$data, 'enquiry' => $enquiry,'query' => $query ),true);