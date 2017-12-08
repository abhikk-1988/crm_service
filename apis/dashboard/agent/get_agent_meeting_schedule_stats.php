<?php

    require_once '../../function.php';

    $query_data = $_GET;

    $agent_id   = '';

    $date_from  = date('Y-m-d');
    
    $date_to    = '';

    if(isset($query_data['agent_id'])){
        $agent_id = $query_data['agent_id'];
    }else{
        echo 0; exit;
    }

    if(isset($query_data['filter_date_from'])){
        $date_from  = $query_data['filter_date_from'];
    }

    if(isset($query_data['filter_date_to'])){
        $date_to    = $query_data['filter_date_to'];
    }
    
    if($date_to == ''){
    
        $query = 'SELECT   a.`hot_warm_cold_status`, COUNT(DISTINCT(a.`enquiry_id`))  as total
                FROM lead_status AS a
                LEFT JOIN employees AS b ON ( a.user_id = b.id )  
                WHERE  `hot_warm_cold_status` !=  ""
                AND  `disposition_status_id` = 3 AND `disposition_sub_status_id` = 22 AND user_id = '.$agent_id.' 
                AND (DATE(`date`) = "'.$date_from.'" OR DATE(updated_on) = "'.$date_from.'")
                GROUP BY a.`hot_warm_cold_status`';
    }
    else{
        
        $query = 'SELECT   a.`hot_warm_cold_status`, COUNT(DISTINCT(a.`enquiry_id`))  as total
                FROM lead_status AS a
                LEFT JOIN employees AS b ON ( a.user_id = b.id )  
                WHERE  
                ( DATE(a.`date`) BETWEEN "'.$date_from.'" AND "'.$date_to.'" OR DATE(a.updated_on) BETWEEN "'.$date_from.'" AND "'.$date_to.'" )
                AND `hot_warm_cold_status` !=  ""
                AND  `disposition_status_id` = 3 
                AND `disposition_sub_status_id` = 22 AND user_id = '.$agent_id.'  
                GROUP BY a.`hot_warm_cold_status`';
    }


    $resultset = mysql_query($query);

    $dataset = array();

    if($resultset && mysql_num_rows($resultset) > 0){
  
        $dataset['status'] = array();
        
        $total_meetings = 0;
        
        while($row = mysql_fetch_assoc($resultset)){
            
            $bg_color = '';
            
            switch(strtolower($row['hot_warm_cold_status'])){
             
                case 'hot':
                    $bg_color = '#00ff00';
                    break;
                case 'warm':
                    $bg_color = '#e5ff00';
                    break;
                case 'cold':
                    $bg_color = '#ff0000';
                    break;
            }
            
            array_push($dataset['status'], array(
                'status' => $row['hot_warm_cold_status'],
                'count' => (int)$row['total'],
                'bg_color' => $bg_color
            ));
            
            $total_meetings = $total_meetings + $row['total'];
        }
    
        $dataset['total'] = $total_meetings;    
    }else{
        
        $dataset['total'] = 0;    
    }

// RESPONSE in JSON
echo json_encode($dataset,true);

?>