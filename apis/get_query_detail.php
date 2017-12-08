<?php
require_once 'db_connection.php';


if(isset($_REQUEST['PhoneNumber']) && $_REQUEST['PhoneNumber']!=''){

	if(is_numeric( $_REQUEST['PhoneNumber'] ))
	{
		
		$phonenumber 			=	$_REQUEST['PhoneNumber'];
		
		/*
			if(preg_match('/^[0-9]{10}+$/', $_REQUEST['PhoneNumber']))
			{
						$phonenumber 			=	$_REQUEST['PhoneNumber'];
					
			}else{
				echo json_encode(array('action'=>'error','message'=>'Please enter 10 digit phone number.'));
						die();
			}
		*/	
		
	 }else{
			
			echo json_encode(array('action'=>'error','message'=>'Please enter valid phone number.'));
			die();
	}
	
	}else{
		echo json_encode(array('action'=>'error','message'=>'Please phone number is blank.'));
		die();
	
	}
	
	$selectEnquiry 		=	"select name, phone, email ,tell_us_are_you_interested,want_to_schedule_a_free_site_visit,preferred_day_for_site_visit,city,gender,country,enquiry,enquiry_from,tell_us_are_you_interested,want_to_schedule_a_free_site_visit,preferred_day_for_site_visit,city,gender,country,enquiry,project_name,project_url from crm_enquiry_capture where phone='".$phonenumber."'";
	
//	echo $selectEnquiry; exit;
	$result = mysql_query($selectEnquiry);
	
	if($result){
		
		if(mysql_num_rows($result)>0){
			
			$lead_data = mysql_fetch_assoc($result);
			
			echo json_encode(array('success'=>1,'data'=>$lead_data), true); exit;
			
		}else{
			echo json_encode(array('success'=>0,'data'=>array()), true); exit;
		}
		
	}else{
		echo json_encode(array('success'=>0,'data'=>array()), true); exit;
	}
