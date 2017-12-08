<?php

session_start();

require_once 'function.php';

$post_data = file_get_contents('php://input');

if($post_data == ''){
    
    echo json_encode(array('success' => 0,'message' => ''), TRUE); exit;
};

$data = json_decode($post_data, true);

/* Validations on employee data */

$form_errors =  array();

if(!isset($data['firstname']) || $data['firstname'] == ''){
    
    $form_errors['firstname'] = 'First name field is required';
}

if(!isset($data['email']) || $data['email'] == ''){
    $form_errors['email'] = 'Email field is required';
}else if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL) === TRUE){
    $form_errors['email'] = 'Email id is not valid';
}

if(!isset($data['contactNumber']) || $data['contactNumber'] == ''){
    $form_errors['phone'] = 'Phone field is required';
}else{
    // check existence of number only for active employees (skip this check for deleted employees)

    $number_exists = mysql_query('SELECT id FROM employees WHERE contactNumber = '.$data['contactNumber'].' AND isDelete = 0 AND id != '.$data['id'].' LIMIT 1');

    if(mysql_num_rows($number_exists) > 0){
        $form_errors['phone'] = 'Contact Number already exists with an active employee';
    }
}

if(!isset($data['doj']) || $data['doj'] == ''){
    $form_errors['joining_date'] = 'Join Date feild is required';
}

if(!isset($data['address']) || $data['address'] == ''){
    $form_errors['address'] = 'Address field is required';
}

if(empty($data['state']) || $data['state'] == ''){
    $form_errors['state'] = 'State field is required';
}

if(empty($data['city']) || $data['city'] == ''){
    $form_errors['city'] = 'City field is required';
}

if(empty($data['designation']) || $data['designation'] == ''){
    $form_errors['designation'] = 'Designation field is required';
}

if(!empty($form_errors)){
    echo json_encode(array('success' => -1, 'errors' => $form_errors),true); exit;
}else{
 
    // Update employee
    $reporting_to = '0';

    if(isset($data['reportingTo']) && $data['reportingTo'] != ''){
        $reporting_to = $data['reportingTo'];
    }
    
    $update_emp = 'UPDATE  '.strtolower('employees').' '
            . ' SET  firstname = "'.$data['firstname'].'" ,'
            . ' lastname = "'.$data['lastname'].'" , '
            . ' email = "'.$data['email'].'" ,'
            . ' contactNumber = "'.$data['contactNumber'].'" ,'
            . ' doj = "'.date('Y-m-d', strtotime($data['doj'])).'" ,' 
            . ' state = '.$data['state'].' ,'
            . ' city = '.$data['city'].' ,'
            . ' designation = '.$data['designation'].' ,'
            . ' reportingTo = '.$reporting_to.' ,'
            . ' address = "'.$data['address'].'" ,'
            . ' addressLine2 = "'.$data['addressLine2'].'",'
            . ' employee_code = "'.$data['employee_code'].'", '
            . ' employee_ctc = "'.$data['employee_ctc'].'" , '
            . ' crm_id = "'.$data['crm_id'].'"'
            . ' WHERE id = '.$data['id'].'';
    
    $query_flag = 0;        

    if(mysql_query($update_emp)){
        
        $query_flag = 1;
		
            // Now we have to put employee(agent) in a team of area sales manager
 
            if($reporting_to != '0'){
                
                $designation_slug_query = mysql_query('SELECT designation_slug FROM designationmaster WHERE id = '.$data['designation'].' LIMIT 1');
                
                if($designation_slug_query){

                    $designation_data = mysql_fetch_object($designation_slug_query);
                    
                    if($designation_data -> designation_slug == 'agent'){

                            // If the "reporting to person" is an area sales manager then we have to get his team and enroll agent to their team

                            $reporting_to_designation = getEmployeeDesignation($reporting_to);

                            if($reporting_to_designation[1] == 'area_sales_manager'){

                                // get team of area sales manager if exists

                                $query_asm_team = mysql_query('SELECT id FROM crm_teams WHERE asm_id = '.$reporting_to.' LIMIT 1');

                                if($query_asm_team && mysql_num_rows($query_asm_team) > 0){
                                    
                                    // put agent in that team 
                                    $team = mysql_fetch_object($query_asm_team);
                                    
                                    mysql_query('UPDATE employees SET crm_team = '.$team -> id.' WHERE email = "'.$data['email'].'" AND id = '.$data['id'].' LIMIT 1');   
                                }
                            }else{
                                mysql_query('UPDATE employees SET crm_team = 0 WHERE email = "'.$data['email'].'" AND id='.$data['id'].' LIMIT 1');
                            }
                    }
                }
            }
            
            /**********************End of code block*******************************/

            echo json_encode(array('success' => 1),true); exit;
		
    }else{
		
		/*$mysql_error = mysql_error();
		
		if( stripos($mysql_error, "duplicate", 0) >= 0){
			
			$error_text = 'Contact number already exists';
		}else{
			
			$error_text = 'Employee details could not be updated. Try again later';
		}
        */
		
        // $query_flag = 0;
		
        echo json_encode(array('success' => 0,'error' => 'There are some error in editing employee'),true); exit;
    }
}