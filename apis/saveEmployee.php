<?php 
session_start();

require_once 'db_connection.php';

$input_post_data = json_decode(file_get_contents('php://input'),true);

/* Validation on form fields */

$form_errors =  array();

if(!isset($input_post_data['firstname']) || $input_post_data['firstname'] == ''){
    
    $form_errors['firstname'] = 'First name field is required';
}

if(!isset($input_post_data['email']) || $input_post_data['email'] == ''){
    $form_errors['email'] = 'Email field is required';
}else if(!filter_var($input_post_data['email'],FILTER_VALIDATE_EMAIL) === TRUE){
    $form_errors['email'] = 'Email id is not valid';
}

if(!isset($input_post_data['phone']) || $input_post_data['phone'] == ''){
    $form_errors['phone'] = 'Phone field is required';
}

if(!isset($input_post_data['joining_date']) || $input_post_data['joining_date'] == ''){
    $form_errors['joining_date'] = 'Join Date feild is required';
}

if(!isset($input_post_data['address1']) || $input_post_data['address1'] == ''){
    $form_errors['address'] = 'Address field is required';
}

if(empty($input_post_data['state']) || $input_post_data['state']['id'] == ''){
    $form_errors['state'] = 'State field is required';
}

if(empty($input_post_data['city']) || $input_post_data['city']['id'] == ''){
    $form_errors['city'] = 'City field is required';
}

if(empty($input_post_data['designation']) || $input_post_data['designation']['id'] == ''){
    $form_errors['designation'] = 'Designation field is required';
}

if(empty($input_post_data['disposition_group'])){
	$form_errors['disposition_group'] = 'Disposition group is not selected';
}

// Reporting 
$reporting = "NULL";

if(!empty($input_post_data['reporting_to'])){
	$reporting = $input_post_data['reporting_to'];
}

// Email Block
if($input_post_data['isCreateLogin'] == 1){
    
    if(!isset($input_post_data['username']) || $input_post_data['username'] == ''){
        $form_errors['username'] = 'Username is required';
    }else{
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => BASE_URL . 'apis/check_username_availibility.php',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode(array('username' => $input_post_data['username']),true)
        ));
        
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        
        // DECODE JSON response 
        $decoded_json_resp = json_decode($resp,true);
        
        if($decoded_json_resp['is_available'] == 0){
            $form_errors['username'] = 'Username is not available';
        }
    }
    
}

if(!empty($form_errors)){   
    $response = array('success' => (int) -1,'errors' => $form_errors,'message' => 'Form has some errors');
    echo json_encode($response,true,10); 
    exit;
}else{
  
    // Get form fields 
    $firstname		= $input_post_data['firstname'];
    $lastname		= (isset($input_post_data['lastname']) ? $input_post_data['lastname'] : '');
    $email			= $input_post_data['email'];
    $phone			= $input_post_data['phone'];
    $join_date		= $input_post_data['joining_date'];
	$disposition_group = $input_post_data['disposition_group'];
	
	$disposition_status_json = '';
	
	if($disposition_group == 2){
		$disposition_status_json = DEFAULT_SALES_PERSON_STATUS;
	}
	
	
    $username		= '';
    $password		= '';
    
    if(isset($input_post_data['username'])){
        $username = $input_post_data['username'];
    }
    
    if(isset($input_post_data['password'])){
        $password = hash('sha1', $input_post_data['password']); 
    }
    
    $address1 = $input_post_data['address1'];
    $address2 = '';
    
    if(isset($input_post_data['address2'])){
        $address2 = $input_post_data['address2'];
    }
    
    $city				= $input_post_data['city']['id'];
    $state				= $input_post_data['state']['id'];
    $designation		= $input_post_data['designation']['id'];
    $designation_label	= $input_post_data['designation']['text'];
    
    // IVR ID AGENT
    $ivr_id = ( isset($input_post_data['crm_id']) ? $input_post_data['crm_id'] : '');
 
	// Getting default profile image 
	
	$profile_image = mysql_real_escape_string(file_get_contents(BASE_URL .'stuffs/images/default.png'));
	
	/**
	 * Every employee created having a role of other user as of now.
	 */
    
    /* Database query to insert record */
    
    $save_employee = 'INSERT INTO `employees` '
				. ' SET firstname = "'.$firstname.'" ,'
				. ' lastname = "'.$lastname.'" ,'
				. ' email = "'.$email.'" ,'
				. ' username = "'.$username.'" ,'
				. ' password = "'.$password.'" ,'
				. ' doj = "'.date('Y-m-d',  strtotime($join_date)).'" ,'
				. ' contactNumber = "'.$phone.'" ,'
				. ' address = "'.$address1.'" ,'
				. ' addressLine2 = "'.$address2.'" ,'
				. ' designation = '.$designation.' ,'
				. ' city = '.$city.' ,'
				. ' state = '.$state.' ,'
				. ' empCreationDate = "'.date('Y-m-d').'",'
				. '	role = 2 ,'
				. ' reportingTo = '.$reporting.','
				. ' disposition_group = '.$disposition_group.','
				. ' assigned_disposition_status_json = "'.mysql_real_escape_string($disposition_status_json).'",'
				. ' profile_image = "'.$profile_image.'" ,'
                . ' crm_id = "'.$ivr_id.'"' ;
	
    if(mysql_query($save_employee)){

            /*-------------------------------------------------------/  
                       Code add on 23 june 2017 by abhishek         */
                                
            // Now we have to put employee(agent) in a team of area sales manager 
            
            if(!is_null($reporting)){
				
                $designation_slug_query = mysql_query('SELECT designation_slug FROM designationmaster WHERE id = '.$designation.' LIMIT 1');
                
                if($designation_slug_query){

                    $designation_data = mysql_fetch_object($designation_slug_query);

                    if($designation_data -> designation_slug == 'agent'){

                            // If the "reporting to person" is an area sales manager then we have to get his team and enroll agent to their team

                            $reporting_to_designation = getEmployeeDesignation($reporting);
                        
							if(!empty($reporting_to_designation)){
							
								if($reporting_to_designation[1] == 'area_sales_manager'){

									// get team of area sales manager if exists

									$query_asm_team = mysql_query('SELECT id FROM crm_teams WHERE asm_id = '.$reporting.' LIMIT 1');

									if($query_asm_team && mysql_num_rows($query_asm_team) > 0){

										// put agent in that team 
										$team = mysql_fetch_object($query_asm_team);

										mysql_query('UPDATE employees SET crm_team = '.$team -> id.' WHERE email = "'.$email.'" LIMIT 1');   
									}
								}
							}	
							
                    }
                }
            }
        
            /**********************End of code block*******************************/
		
			// now send email and sms to user 
            /* Send email to user with login credentials */
        
            if(IS_EMAIL_ON == 1){
					require_once 'email.php';
           
					$recepient_name = $firstname. ' '. $lastname;
					$recepient_address = $email;
					$mail_body = 'Dear, '. $recepient_name;
					$mail_body .= '<br/><br/>';
					$mail_body .= 'Your account has been created in CRM';
					$mail_body .= '<br/><br/>';
					$mail_body .= 'Please find below your account/ login credentials - ';
					$mail_body .= '<br/><br/>';
					$mail_body .= '<b><u>Details:</u></b>';
					$mail_body .= '<br/><br/>';
					$mail_body .= '<b><u>Email ID:</u></b> '. $email;
					$mail_body .= '<br/>';
					$mail_body .= '<b><u>Username:</u></b> '. $username;
					$mail_body .= '<br/>';
					$mail_body .= '<b><u>Password:</u></b> ' . $input_post_data['password'];
					$mail_body .= '<br/>';
					$mail_body .= '<b><u>Designation:</u></b> '. $designation_label;
					$mail_body .= '<br/>';
					$mail_body .= '<b><u>Login URL:</u></b> ' .BASE_URL;
                    
					$mail_body .= '<br/><br/>';
					$mail_body .= '<b>Note:</b> You can login from both email id and username with the same password';
					
					// Add a recepient
					$mail->addAddress($recepient_address, $recepient_name);
					
					// Set email format to HTML
					$mail->isHTML(true);                                  

					$mail->Subject = 'Login Credentials: CRM';
					$mail->Body    = $mail_body;
					$mail->AltBody = $mail_body;

			// SENDING MAIL AND SMS 		
			/*******************************************************************************/
			
				
					
					if(!$mail->send()) {                                             
						$email_sent = 0;                                             
					} else {                                                           
						$email_sent = 1 ;                                            
					} 
                    
            } // IF EMAIL SEND IS ON
			                                                                        
			/********************************************************************************/

			if(isset($input_post_data['isSendSMS'])){

				if($input_post_data['isSendSMS'] == 1){

					/* Send sms to user with login credentials */

				   $sms =  file_get_contents(BASE_URL.'apis/sendsms.php?number='.$phone.'&username='.$username.'&password='.$input_post_data['password']);
				}
			}
		
		
        $success_response = array('success' => (int) 1,'message' => 'New employee has been created successfully', 'email_sent' => $email_sent); 
        echo json_encode($success_response,true);
        exit;
    }else{
    
        
        $error_text = mysql_error();
        $error_code = mysql_errno();
    
        if($error_code === 1062){
            $column = substr($error_text, strrpos($error_text, 'key', 16) + 3);
            $error_message = _1062;
            $error_message = str_replace("%s", $column, $error_message);
        }else{
            $error_message = mysql_error();
        }
        
        $error_response = array('success' => 0, 'message' =>'Server Error: '. $error_message);
        echo json_encode($error_response,true);
        exit;
    }
}