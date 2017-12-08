<?php

session_start();

require_once 'function.php';

$user = $_SESSION['currentUser'];

$user_id = $user['id'];

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


$data = json_decode(file_get_contents('php://input'),true);

if(!empty($data) && isset($data['enquiry_id'])){
	
	$enquiry_id = $data['enquiry_id'];
	
	$lead_number = 'NULL';
	
	if(isset($data['lead_number'])){
		$lead_number = $data['lead_number'];
	}
    
	foreach ($data['projects'] as $key => $val) {

		$project_id		= $val['project_id'];
		$project_name	= $val['project_name'];
		$project_url	= $val['project_url'];
        $project_city   = get_project_city($project_id);
					
		$save_enquiry_projects = 'INSERT INTO `lead_enquiry_projects`'
				. '  (enquiry_id,lead_number,project_id,project_name,project_city,project_url) '
				. ' VALUES (' . $enquiry_id .',"'.$lead_number.'", '. $project_id . ', "' . $project_name . ' ","' . $project_city . '" ,"'.$project_url.'")';

		if (mysql_query($save_enquiry_projects)) {
			$flag_of_save_enquiry_projects = true;
            
            
            // History of new projects added against a enquiry number 
            
            $new_projects = array(
                
                'enquiry_id' => $enquiry_id,
                'lead_number' => $lead_number,
                'details' => 'New project\'s has been added on '. date('d-M-Y H:i:s'),
                'type' => 'edit',
                'employee_id' => $user_id
            );
            
            createLog($new_projects);
		}
	}

	// update category of lead/enquiry
	mysql_query('UPDATE lead SET lead_category = "SPL" WHERE enquiry_id= '.$enquiry_id.'');
	
	echo json_encode(array('success' => 1),TRUE); exit;
}
else{
	echo json_encode(array('success' => 0),TRUE); exit;
}