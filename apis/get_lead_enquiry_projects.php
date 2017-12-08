<?php
session_start();
require_once 'function.php';


if(!function_exists('get_project_city')){

	function get_project_city($project_id = null){
  
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'http://52.77.73.171/apimain/api/get_project_city.php',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => array('project_id' => $project_id)
		));
	
		$resp = curl_exec($curl);
		curl_close($curl);
		if(!$resp){
			return '';
		}else{
			
			$response_obj = json_decode($resp,true);
			return $response_obj['city_name']; 
		}
	}
}


// lead number or enquiry number 

$enquiry_number = '';
$lead_number    = '';
$status_id      = '';
$status_slug    = '';
$projects       = array();

if(isset($_GET['enquiry_number']) ){   
	$enquiry_number = $_GET['enquiry_number'];
}

if(isset($_GET['lead_number'])){
	$lead_number = $_GET['lead_number'];
}

// Current Status
if( isset($_GET['status']) ){
    $status_id = $_GET['status'];
}else{
    
    // get enquiry status of enquiry 
    $enquiry_status = mysql_query('SELECT disposition_status_id , disposition_sub_status_id FROM lead WHERE enquiry_id = '.$enquiry_number.' LIMIT 1');

    if($enquiry_status && mysql_num_rows($enquiry_status) > 0){
        
        $enquiry_status_data = mysql_fetch_object($enquiry_status);
        $status_id = $enquiry_status_data -> disposition_status_id;   
    }   
}

$condition= 'WHERE enquiry_id = '.$enquiry_number.' '; 

if($lead_number != null && $lead_number != 'null'){
   
	$condition .= 'OR lead_number = "'.$lead_number.'" ';
}
 
    $select_enquiry_project = 'SELECT project_id, project_name, project_url , project_city  FROM `lead_enquiry_projects` '.trim($condition).'';

    $result = mysql_query($select_enquiry_project);

    if($result){

        if(mysql_num_rows($result) > 0){

            while($row = mysql_fetch_assoc($result)){

                $row['project_city'] = get_project_city($row['project_id']);
                array_push($projects, $row);
            }
        }
    }
//}

echo json_encode(array('success' => 1, 'data' => $projects));