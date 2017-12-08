<?php

require_once '../../function.php';

$get_data = $_GET;

if(isset($get_data['agent_id'])){
    $agent_id = $get_data['agent_id'];
}

if(isset($get_data['filter_date_from'])){
    $from_date = $get_data['filter_date_from'];
}
else{
    $from_date = date('Y-m-d');
}


if(isset($get_data['filter_date_to'])){
    $to_date = $get_data['filter_date_to'];
}
else{
    $to_date = '';
}

if($to_date == ''){
 
    $query = 'SELECT COUNT( * ) 
    FROM  `lead` 
    WHERE lead_added_by_user = '.$agent_id.'
    AND DATE( leadAddDate ) =  "'. $from_date .'"';

}
else
{
    $query = 'SELECT COUNT( * ) 
    FROM  `lead` 
    WHERE lead_added_by_user = '.$agent_id.'
    AND DATE( leadAddDate ) BETWEEN  "'.$from_date.'" AND "'.$to_date.'"';

}

$result = mysql_query($query);

if($result && mysql_num_rows($result) > 0){

    // Send count of total calls
    
    $data = mysql_fetch_row($result);

    echo $data[0];
    exit;
}
else{
    echo 0; exit;
}
