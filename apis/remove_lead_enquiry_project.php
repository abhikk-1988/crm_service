<?php
session_start();

require 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if(empty($data)){
   
	echo json_encode(array('success' => 0), true); exit;
}

$enquiry_number = $data['enquiry_number'];

$project_id     = $data['project_id'];

$query          = 'DELETE FROM lead_enquiry_projects WHERE enquiry_id = '.$enquiry_number.' AND project_id = '.$project_id.' LIMIT 1';

$deleted_rows   = mysql_query($query);


// UPDATE SITE VISIT OR MEETING TABLE AS WELL TO REMOVE PROJECT FROM THERE ALSO

    //removeProjectFromSiteVisitAndMeeting($enquiry_number,$project_id);

if($deleted_rows){
   
	echo json_encode(array('success' => 1), true); exit;
}else{
	echo json_encode(array('success' => 0), true); exit;
}