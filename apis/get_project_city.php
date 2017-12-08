<?php
require_once 'db.php';

// Data will be available in POST HTTP request method 

$project_id = '';

if(isset($_POST['project_id'])){
   
	if(empty($_POST['project_id'])){
		
		return array();
	}
	
	$project_id = $_POST['project_id'];

	$select_project = 'SELECT project.project_id, project.city_id, city.city_name FROM `bmh_project` as project '
		  . ' LEFT JOIN bmh_city_master as city ON (project.city_id = city.city_id) WHERE project.project_id = '.$project_id.' LIMIT 1';
	
	$result = mysql_query($select_project);
	
	if($result){
		
		if(mysql_num_rows($result) > 0){
			
			$data = mysql_fetch_object($result);
			print_r ( json_encode($data,true));
		}
	}
}