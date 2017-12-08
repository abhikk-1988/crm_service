<?php

require 'function.php';

$phone = '';

if(isset($_GET['phone'])){
    $phone = $_GET['phone'];
//    $phone = '9291929192';
}

$query = 'SELECT id, query_request_id,leadvalujson, created_time, created_on, enquiry_from, phone, email, name, address, project_name, city, ivr_push_type, ivr_push_status, ivr_push_date, ivr_pushed_by, enquiry_assign_to_agent_id, agent_assign_status, agent_assign_date,executive 
FROM crm_capture_duplicate WHERE phone = "'.$phone.'"';

$result = mysql_query($query);

$dataset = array();

if(mysql_num_rows($result) > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        array_push($dataset, $row);
    }
}


echo json_encode($dataset,true); exit;