<?php
session_start();
require_once 'function.php';

$notes		= array();
$enquiry_id	= '';

$post = json_decode(file_get_contents('php://input'),true);

$page				= 1;
$offset				= 0;
$notes_per_page		= NOTES_PER_PAGE;
$limit = '';

if(!empty($post)){

	
	if(isset($post['page'])){
		
		$page = $post['page'];
	}
	
	$offset = ($page - 1) * $notes_per_page; 
	
	$limit = 'LIMIT '. $offset . ', '. $notes_per_page; 
	
	$select_notes = 'SELECT note.*, CONCAT(emp.firstname," ", emp.lastname) as note_added_by_employee'
			. ' FROM notes as note'
			. ' LEFT JOIN employees as emp ON (note.note_added_by = emp.id)'
			. ' WHERE note.enquiry_id = '.$post['enquiry_id'].' ORDER BY note.note_add_date DESC '. $limit;
	
	$result = mysql_query($select_notes);
	
	if($result && mysql_num_rows($result) > 0){
	
		while($row = mysql_fetch_assoc($result)){
			array_push($notes, $row);
		}
	}
}

echo json_encode($notes,true); exit;




