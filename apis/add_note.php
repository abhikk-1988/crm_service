<?php
session_start();
require_once 'function.php';

$post = json_decode(file_get_contents('php://input'),true);

if($post !=''){
	
	$note = array();
	
	$note['enquiry_id']			= $post['enquiry_id'];
	$note['note_text']			= mysql_real_escape_string($post['note_text']);
	$note['note_add_date']		= date('Y-m-d H:i:s');
	$note['note_added_by']		= $post['note_owner'];
	$note['note_updated_by']	= $post['note_owner'];
    
    $note_added_by_user = getEmployeeName($note['note_added_by']);

	// Insert note 
	
	$sql = 'INSERT INTO `notes` '
			. ' SET ';
	
	foreach($note as $col => $val){
		
		$sql .= $col . ' = "' . $val.'",';
	}
	
	$insert_note_sql = rtrim($sql, ',');
	
	$last_note_id = mysql_insert_id();
	
	if(mysql_query($insert_note_sql)){
	
		// Create a log of new note in history table 
		
		// lead_number 
		// enquiry_id 
		// created_at 
		// details 
		// employee id
		// type - edit/ new 
		
		$lead_number	= getLeadNumber ($note['enquiry_id']);
		$note_details	= 'A new note has been added.';
		$meta_data		= json_encode(
            array(
            'display_type' => 'popup', 
            'data' => mysql_real_escape_string($post['note_text']),
            'added_by' => $note_added_by_user
        ), true);
		
		$log_note = 'INSERT INTO `lead_history`'
				. ' SET '
				. ' lead_number = "'.$lead_number.'",'
				. ' enquiry_id = '.$note['enquiry_id'].','
				. ' details = "'.$note_details.'",'
				. ' employee_id = '.$note['note_added_by'].','
				. ' type = "new",'
				. ' meta_data = "'.  mysql_real_escape_string($meta_data).'"';
		
		mysql_query($log_note);
		
		echo json_encode(array(
			'success' => 1,
			'message' => 'Note Added successfully'
		),true); exit; 
	}else{
		
		echo json_encode(array(
			'success' => 0,
			'message' => 'Note has not been added'
		),true); exit;
	}	
	
}else{
	
	echo json_encode(array(
			'success' => 0,
			'message' => 'Note has not been added'
		),true); exit;
}