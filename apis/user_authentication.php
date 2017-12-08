<?php
// Authenticate user login from session data 
    $is_authenticate = FALSE;
    $user_id = '';
    $email = '';
    
    if(isset($_SESSION) && isset($_SESSION['currentUser'])){
        
            if(!empty($_SESSION['currentUser']['id'])){
                
                    $user_id		= $_SESSION['currentUser']['id'];
                    $email			= $_SESSION['currentUser']['email'];
                    
                    $authenticate_user = authenticateUser($user_id,$email);
                    
                        if($authenticate_user == 0){
                            
                                $is_authenticate = FALSE;
                        }else{
                                $is_authenticate = TRUE;
                        }
                    
            }else{
                
            }
    }else{
        $is_authenticate = FALSE;
    }
    
if(!$is_authenticate){
   
	$failure_authentication_response = array();
	
	$failure_authentication_response['success'] = '0';
	
	$failure_authentication_response['http_status_code'] = 401;
	
	echo json_encode($failure_authentication_response, true); exit;
}