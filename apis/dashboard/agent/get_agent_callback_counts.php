<?php

require '../../function.php';

$get_data = $_GET;

if(isset($get_data['agent_id'])){
    $agent_id = $get_data['agent_id'];
}

if(isset($get_data['filter_date_from'])){
    $date_from = $get_data['filter_date_from'];
}else{
    $date_from = date('Y-m-d');
}

if(isset($get_data['filter_date_to'])){
    $date_to = $get_data['filter_date_to'];
}else{
    $date_to = date('Y-m-d');
}

$callback_status = mysql_query('SELECT id 
FROM disposition_status_substatus_master 
WHERE status_slug = "callback" LIMIT 1');

$callback_status_id = '';

if($callback_status){
    
    $callback_status_result = mysql_fetch_assoc($callback_status);
    
    $callback_status_id = $callback_status_result['id'];
}

$callbacks_with_status = array();
$callbacks_with_status['labels']        = array();
$callbacks_with_status['data']          = array();
$callbacks_with_status['bg_colors']     = array();
$callbacks_with_status['total']         = 0;

if($callback_status_id != ''){

        if($date_to == ''){
            
            $query = 'SELECT `hot_warm_cold_status`, GROUP_CONCAT(DISTINCT enquiry_id) as callback_enquiries,         COUNT(DISTINCT(enquiry_id)) as status_wise_count
            FROM  `lead_status` 
            WHERE  `disposition_status_id` = '.$callback_status_id.'
            AND (Date(`date`) = "'.$date_from.'" OR DATE(updated_on) = "'.$date_from.'") 
            AND user_id = '.$agent_id.'
            AND `hot_warm_cold_status` !=  ""
            GROUP BY `hot_warm_cold_status` 
            ORDER BY hot_warm_cold_status';
        }
        else{
            
            $query = 'SELECT `hot_warm_cold_status`, GROUP_CONCAT(DISTINCT enquiry_id) as callback_enquiries,         COUNT(DISTINCT(enquiry_id)) as status_wise_count
            FROM  `lead_status` 
            WHERE  
            (Date(`date`) BETWEEN "'.$date_from.'" AND "'.$date_to.'" OR  DATE(updated_on) BETWEEN "'.$date_from.'" AND "'.$date_to.'") 
			AND `disposition_status_id` = '.$callback_status_id.'
			AND user_id = '.$agent_id.'
            AND `hot_warm_cold_status` !=  ""
            GROUP BY `hot_warm_cold_status` 
            ORDER BY hot_warm_cold_status';    
        }
        
        $result = mysql_query($query);
    
        if($result && mysql_num_rows($result) > 0){

            $total = 0;
            
            while($row = mysql_fetch_assoc($result)){
                
                if($row['hot_warm_cold_status'] != ''){
                    
                    $total += $row['status_wise_count'];
                    
                    if($row['hot_warm_cold_status'] == 'hot'){
                        array_push($callbacks_with_status['data'],(int)$row['status_wise_count']);
                        array_push($callbacks_with_status['bg_colors'],'#00ff00');    
                        array_push($callbacks_with_status['labels'], 'Hot');
                    }
                    
                    if($row['hot_warm_cold_status'] == 'warm'){
                        array_push($callbacks_with_status['data'],(int)$row['status_wise_count']); 
                        array_push($callbacks_with_status['bg_colors'],'#e5ff00');
                        array_push($callbacks_with_status['labels'], 'Warm');
                    }

                    if($row['hot_warm_cold_status'] == 'cold'){
                        array_push($callbacks_with_status['data'],(int)$row['status_wise_count']);
                        array_push($callbacks_with_status['bg_colors'],'#ff0000');
                        array_push($callbacks_with_status['labels'], 'Cold');
                    }
                }
            }
            
            // Set Total
            $callbacks_with_status['total'] = $total;
            
        }else{
            $callbacks_with_status['data']= [0,0,0];
        }
}else{
    $callbacks_with_status['data']= [0,0,0];
}

// Response
echo json_encode($callbacks_with_status,true); exit;

?>