<?php 
header('Content-Type: application/json');
require_once 'db_connection.php';

$data = file_get_contents('php://input');

if($data == ''){
    
    $response =  array('success' => 0, 'message' => 'No input recieved');
    echo json_encode($response,true); exit;
}

// Decode JSON input into array

$input_array = json_decode($data,true);

$designation	= $input_array['designation'];
$action			= $input_array['action'];
$id				= $input_array['id'];
$disable		= $input_array['disable'];


if($id == null || $id == ''){
    
    // Add new designation 
    $save_designation = 'INSERT INTO `designationmaster` '
			. ' SET designation = "'.$designation.'", '
			. ' designation_slug = "'.str_replace(' ','_',strtolower($designation)).'" ,'
			. ' `insertDate` = "'.date('Y-m-d H:i:s').'"';
    
    if(mysql_query($save_designation)){
        
        echo json_encode(array('success' => 1, 'message' => 'Designation saved successfully'),true); exit;
    }
}else{
    // update designation  
    
    if($disable == null || $disable == ''){
        $is_disable = 0;
    }else{
        $is_disable = 1;
    }
    
    $update_designation = 'UPDATE `designationmaster` SET `designation` = "'.$designation.'", `disable` = "'.$is_disable.'" WHERE id = '.$id.' LIMIT 1';
    
    mysql_query($update_designation);
    
    echo json_encode(array('success' => 1, 'message' => 'Designation update successfully'),true); exit;
}