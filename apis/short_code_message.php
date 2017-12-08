<?php
	error_reporting(E_ALL);
	require_once 'db_connection.php';
	
	if(isset($_REQUEST['PhoneNumber']) && $_REQUEST['PhoneNumber']!=''){
		
		if(is_numeric( $_REQUEST['PhoneNumber'] ))
		{
			
			/*
			if(preg_match('/^[0-9]{10}+$/', $_REQUEST['PhoneNumber']))
			{
				$phonenumber 			=	$_REQUEST['PhoneNumber'];
			
			}else{
				
				echo json_encode(array('action'=>'error','message'=>'Please enter 10 digit phone number.'));
				die();
			} 
		   */	
			
			$phonenumber 			=	$_REQUEST['PhoneNumber'];
		
		}else{
			
			echo json_encode(array('action'=>'error','message'=>'Please enter valid phone number.'));
			die();
		}
	
	 }else{
		
		echo json_encode(array('action'=>'error','message'=>'Please phone number is blank.'));
		die();
	}
	
	if(isset($_REQUEST['MESSAGE']) && $_REQUEST['MESSAGE']!=''){
		
		$message 			=   $_REQUEST['MESSAGE'];	
	
	}else{
		
		echo json_encode(array('action'=>'error','message'=>'Please m is blank.'));
		die();
	}
	
	$sql_selectQuery	=	 "select *from crm_enquiry_capture where phone='".$phonenumber."'";	
	$sqlQuery 			=	 mysql_query($sql_selectQuery);
	$numrow				=	 mysql_num_rows($sqlQuery);

	/*
	if($numrow>0)
	{
		echo json_encode(array('action'=>'error','message'=>'phone number already exist.'));
		die();
	}
	*/
	
	$json_encode 		= json_encode($_REQUEST);
	
	$jsonPost			=	json_encode(array('phone_number'=>$phonenumber,'enquiry'=>$message));
	
	$numberdigit 		= 	'ACL'.str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
	
	//$insertLead 		=	"insert into crm_enquiry_capture set query_request_id='".$numberdigit."', enquiry_from='ACL' , leadvalujson='".$jsonPost."', phone='".$phonenumber."', enquiry='".$message."' , created_time='".time()."'"
	
	
	$insertLead 		=	"insert into crm_enquiry_capture set query_request_id='".$numberdigit."', enquiry_from='ACL' , leadvalujson='".$jsonPost."', phone='".$phonenumber."', enquiry='".$message."' ,created_on='".date('Y-m-d H:i:s')."' , created_time='".time()."', acl_response='".$json_encode."'";
	
	
	if(mysql_query($insertLead)){
		
		echo json_encode(array('action'=>'success','message'=>'Record has been saved succesfully.'));
		exit;
	
	}else{
		
		echo json_encode(array('action'=>'error','message'=>'database error.'));
		exit;
	}
	
?>