<?php

/**
 * @API: addTeamMember
 * @author: Abhishek Agrawal
 * @created on: 21/06/2017
 */

 require '../function.php';

 $post_data = json_decode(file_get_contents('php://input'),true); 

 $errors = array();

 if($post_data['emp_id'] == ''){
    
    $errors['emp_id'] = 'Employee is not selected';
 }

 if($post_data['team_id'] == ''){
    
    $errors['team_id'] = 'Team is not selected';
 }

 // if some error 
 if(!empty($errors)){

    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => 0,
        'is_error' => 1,
        'errors' => $errors,
        'message' => 'Please correct following errors'
    ),true);
    exit;
 }
 
 // check employee exists or not 

 $get_employee = 'SELECT * FROM employees WHERE id = '.$post_data['emp_id'].' LIMIT 1';

 $employee = mysql_query($get_employee);

 if($employee && mysql_num_rows($employee) > 0 ){
     
     $query = 'UPDATE employees SET crm_team = '.$post_data['team_id'].' WHERE id = '.$post_data['emp_id'].' LIMIT 1';
     
     $insert = mysql_query($query);

    if($insert){
    
        // JSON resonse 
        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => 1,
            'message' => 'New team member has been added succesfully'
        ),true); exit;
    }
 }else{

    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => 0,
        'is_error' => 1,
        'messsage' => 'Employee not exist' 
    ),true);
    exit;
 }
?>