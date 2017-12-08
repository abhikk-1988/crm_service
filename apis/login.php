<?php
session_start();

/**
 * Login API for CRM 
 * 
 * @version 1.0
 * @param type $name Description
 */

    // READ JSON STREAM 
    $data = file_get_contents('php://input');
  
    if($data == ''){
        $response = array('success' => -1,'message' => 'No input recieved');
        response($response); exit;
    }
    
    $data_array = json_decode($data,true);
 
	// user login ip address
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
    /**
     *  INPUT VALIDATION  
     */
    
    $username	= $data_array['username'];
    $password	= $data_array['password'];
    $login_type = $data_array['loginType'];
	
    $validation_errors = array();
    
    if( filter_var($username,FILTER_VALIDATE_EMAIL) == FALSE && $data_array['loginType'] == 'email'){
        $validation_errors['errors'][] = array('field' => 'username','error_msg' => 'Username is not a valid email id');
    }
    
    if($username == '' && $password == ''){
         $validation_errors['errors'][] = array('field' => '_empty','error_msg' => 'Please provide valid login details');
    }
    
    if($username == ''){
         $validation_errors['errors'][] = array('field' => 'username','error_msg' => 'Please provide a valid username');
    }
    
    if($password == ''){
         $validation_errors['errors'][] = array('field' => 'password','error_msg' => 'Please provide your password');
    }
    
    if(!empty($validation_errors)){
        $response = array('success' => 0,'errors' => $validation_errors);
        response($response); exit;
    }
    /*******************************VALIDATION END***************************************/
    
    /***************************************************************************/
    
	// Autheticate user 

    require_once 'function.php';
	
	if($login_type === 'email'){
		
		$select_user = 'SELECT emp.*, designation.designation as designation_title,designation.designation_slug '
			. ' FROM employees as emp '
			. ' LEFT JOIN `designationmaster` as designation ON (emp.designation = designation.id)'
            . ' WHERE emp.email = "'. mysql_escape_string($username).'"  AND emp.password = "'.hash('sha1',  mysql_escape_string($password)).'" ';
    
	}
	
	else {
		$select_user = 'SELECT emp.*, designation.designation as designation_title,designation.designation_slug '
			. ' FROM employees as emp '
			. ' LEFT JOIN `designationmaster` as designation ON (emp.designation = designation.id)'
            . ' WHERE emp.username = "'.  mysql_real_escape_string($username).'" AND emp.password = "'.hash('sha1',  mysql_escape_string($password)).'" ';
    
	}

    $result = mysql_query($select_user);
    
    $rows	= mysql_num_rows($result);
    
    if($rows > 0){
		
        // Prepare a object of user's system configuration
        // Call an external API to get this configuration object from server
        
        $employee = mysql_fetch_assoc($result);
		
        
        // Message for account suspension
        if($employee['isDelete'] == 1){
            
             $response = array('success' => 0,'errors' => 'Account has been suspended');
             response($response); exit;
            
        }
        
		// Save user IP address
		mysql_query('UPDATE `employees` SET login_ip_address = "'.$ip_address.'" WHERE id = '.$employee['id'].' LIMIT 1');
	
        // Converting user profile from binary to base64 
        $profile_image = blobToImage($employee['profile_image']);
		
        // unsetting blob type profile image from employee array 
        unset($employee['profile_image']); 
        
        // set base64 image into employee array
        $employee['profile_image']	= $profile_image;
        $designation_id		= $employee['designation'];
        
        // extracting assigned employee modules
        $modules		= json_decode(file_get_contents(BASE_URL.'apis/getEmpModules_beta.php?designation_id='.$designation_id),true);
        $employee['modules']	= $modules;
        
        // Put current month capacities of area sales manager and sales person 
        if($employee['designation_slug'] == 'area_sales_manager'){
            $employee['current_month_capacity'] = getUserCurrentMonthCapacities($employee['id']);
        }
        
        // Creating a new user session on server 
        $_SESSION['currentUser'] = $employee;	
        
        // API response 
        $employee_response = array('success' => 1,'employee' => $employee);
       
        response($employee_response);
        exit;
        
    }else{
        // Generating employee data response 
        $response = array('success' => 0,'errors' => 'Authenticaton Failure!! Please check your login credentials');
        response($response); exit;
    }
    
    
    /**
     * Function to send API response
     * @param type $data <should be in array format>
     * @param type $format <json,array,plain text>
     */
    function response($data = '', $format='json'){
        
        switch($format){
            
            case 'json':
                echo json_encode($data,true);
            break;
            
        }
    }
    
    /**
     * Function to convert blob data into image
     * @param type $binary
     * @return type
     */
    function blobToImage($binary = ''){
        return 'data:image/jpeg;base64,'.base64_encode( $binary ).'';
    }