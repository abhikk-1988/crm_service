<?php
//error_reporting(E_ALL);
require_once 'db_connection.php';
if($_FILES[0]['name']){

	
	$foldername			=	"../queryupload/".time()."_".$_FILES[0]['name'];
	
	if(move_uploaded_file($_FILES[0]['tmp_name'],$foldername)){
	
	

	$arrayFromCSV =  array_map('str_getcsv', file($foldername));
	$headArr			=	$arrayFromCSV[0];
	
	$Mytemp				=	array();
	$count 				=	1;
	foreach($arrayFromCSV as $key=>$val){
	
		$TempArr				=	array();
		$TempArr2				=	array();
		$validated 				=	array();
		$unvalidated 			=	array();
		
		if($count>1){
			
			$i		=	0;
			
			$flag 	= true;	
			foreach($val as $k=>$v){
				$index				=	strtolower(trim($headArr[$i]));	
				$TempArr[]			=	$v;	
				$TempArr2[$index]	=	$v;	
				$i++;
			}
			
			
			//echo "<pre>";
			//print_r($TempArr2);
			//exit;
			
			save_enquery($TempArr2 , $enquiry_from='CSV');
			$Mytemp[]		=	$TempArr;
		
		}
		
		$count++;
	}
	
	//echo  json_encode(array('action'=>'success', 'message'=>'file has been uploaded successfully.','csvHead'=>$headArr,'csvData'=>$Mytemp));
	echo  json_encode(array('action'=>'success', 'message'=>'file has been uploaded successfully.'));

	}else{
		
		echo  json_encode(array('action'=>'error', 'message'=>'file is not uploaded.'));
	}
	
}




	//Save enquery.
	function  save_enquery($post , $enquiry_from='BMH'){
	
	
	
		$numberdigit 		= 	$enquiry_from.str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
		
		
		//echo "<pre>";
		//print_r($post);
		
		$jsonPost			=	json_encode($post);
		$Mydata 			=   mapFiledKey($post);
	
		if(count($Mydata)>0){
		
				$addingQuery 			=	'';
				
				
				foreach($Mydata as $key=>$val){
				
					$addingQuery		.=	", ".$key."='".$val."'";
					
					if($key=='phone'){
					
						$phone		=  $val;
					}
				}
			
				$sql_selectQuery	=	 "select *from crm_enquiry_capture where phone='".$phone."'";	
				$sqlQuery 			=	mysql_query($sql_selectQuery);
				$numrow				=	mysql_num_rows($sqlQuery);
			
				if($numrow>0){
					
					//echo json_encode(array('action'=>'error','message'=>'phone number already exist.'));
					//die();
					
				}else{
				
				$insertLead 		=	"insert into crm_enquiry_capture set query_request_id='".$numberdigit."' ,enquiry_from='".$enquiry_from."', leadvalujson='".$jsonPost."',created_time='".time()."' ".$addingQuery;

					if(mysql_query($insertLead)){
						return true;
					}
				}	
			
			}else{
				
				$insertLead 		=	"insert into crm_enquiry_capture set query_request_id='".$numberdigit."' ,enquiry_from='".$enquiry_from."', leadvalujson='".$jsonPost."',created_time='".time()."'";
			
			if(mysql_query($insertLead)){
				return true;
			}
			
		}
		
	
		
	}
	
	function mapFiledKey($jsonPost){
		
		try {
			
			$NameArr	=  array(
								'full_name'=>'name', 
								'name'=>'name', 
								'user_name'=>'name',
								'phone_number'=>'phone',
								'email'=>'email',
								'user_contact'=>'phone',
								'phone'=>'phone',
								'tell_us_are_you_interested_'=>'tell_us_are_you_interested',
								'want_to_schedule_a_free_site_visit_'=>"want_to_schedule_a_free_site_visit",
								'preferred_day_for_site_visit_'=>'preferred_day_for_site_visit',
								'city'=>'city',
								'gender'=>'gender',
								'country'=>'country',
								'enquiry'=>'enquiry' 
		);	
		
		$jsonPost2		=	array();
		
		foreach($NameArr as $key=>$val){
			
			if(@array_key_exists($key , $jsonPost))
			{
				@$jsonPost2[$val] = @$jsonPost[$key];
			}
		}
		
		return $jsonPost2;
 
  }catch (Exception $e) {
		return false;
   }
   
}

?>