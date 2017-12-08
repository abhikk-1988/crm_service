<?php
/**
 * API to get employee's list for email users 
 */

require_once 'function.php';


$users = array();


// we exclude admin from this list 

$select_users = 'SELECT id, email, firstname, lastname FROM employees WHERE role != 1';

$result = mysql_query($select_users);

if($result){
	
	while($user = mysql_fetch_assoc($result)){
		
		$name = $user['firstname'].' '.$user['lastname'];
		
		$user['user_fullname'] = $name;
		
		array_push($users, $user);
		
	}
	
}

echo json_encode($users, true); exit;


