<?php
session_start();

require 'function.php';

$_post = filter_input_array(INPUT_POST);

$enquiry_id     = '';
$lead_number    = '';

if( !empty($_post) && isset($_post['enquiry_id'])){
    
    $enquiry_id = $_post['enquiry_id'];
 
    
    $lead_number_condition = '';
    
    if(isset($_post['lead_number']) && $_post['lead_number'] != ''){
        $lead_number = $_post['lead_number'];
        
        $lead_number_condition = ' AND lead_number = "'.$lead_number.'" ';
    }
    
    $query = 'SELECT * FROM payment_collection WHERE enquiry_id = '.$enquiry_id.' '. $lead_number_condition .' LIMIT 1';
    
    $payment_collection_data = array();
    
    $result = mysql_query($query);
    
    if($result && mysql_num_rows($result) > 0){
        
        while($row = mysql_fetch_assoc($result)){
            array_push($payment_collection_data, $row);           
        }
    }
    
    
    echo json_encode($payment_collection_data,true);
}
