<?php

session_start();

require 'function.php';

$_post = filter_input_array(INPUT_POST);

$payment_collection_data = array();

if(!empty($_post) && isset($_post['enquiry_id'])){
    
    $enquiry_id     = $_post['enquiry_id'];
    $lead_number    = '';
    
    $lead_number_condition = '';
    
    if(isset($_post['lead_number']) && $_post['lead_number'] != ''){
        $lead_number = $_post['lead_number'];
        $lead_number_condition = ' AND lead_number = "'.$lead_number.'" ';
    }
    
    
    // Get Payment Collection data query
    
    $get_collection = 'SELECT * FROM payment_collection WHERE enquiry_id = '.$enquiry_id.' '. $lead_number_condition . '';
   
    $result = mysql_query($get_collection);
    
    if($result && mysql_num_rows($result) > 0){
        
        while($row = mysql_fetch_assoc($result)){
            
            $row['file'] = '';
            if($row['payment_type'] == 'cheque'){
                $row['file'] = $row['cheque_scan_filepath'];
            }
            else if($row['payment_type'] == 'ot'){
                $row['file'] = $row['transaction_receipt_filepath'];
            }
            
            $row['employee_name'] = getEmployeeName($row['employee_id']);
            array_push($payment_collection_data, $row);
        }
    }
    
    echo json_encode(array('success' => 1, 'data' => $payment_collection_data),true); exit;
}else{
    echo json_encode(array('success' => 0 ,'data' => $payment_collection_data),true); exit;
}
