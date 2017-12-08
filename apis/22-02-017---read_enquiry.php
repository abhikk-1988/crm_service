<?php

	require_once 'db_connection.php';
	
	$data 				=	file_get_contents('php://input');
	$josnDecode 		=	json_decode($data,true); 
	
	$todayDate 		=	date('Y-m-d');
	$dateValue 		=	'';
	$where 			=	" and created_on = '".$todayDate."'";
	$dateValue 		=	"Today";
	
	if(isset($josnDecode['date_range']) && $josnDecode['date_range']!=''){
		
		$var  			=	$josnDecode['date_range'];
		$varArr 		=	explode("-",$var);
		
		$dateStr1		=	explode("/",$varArr[0]);
		$sd				=	 trim($dateStr1[0]);
		$sm				=	 trim($dateStr1[1]);
		$sy				=	 trim($dateStr1[2]);


		$dateEnd		=	explode("/",$varArr[1]);
		$ed				=	 trim($dateEnd[0]);
		$em				=	 trim($dateEnd[1]);
		$ey				=	 trim($dateEnd[2]);
	
	
	
		$startDate		=	$sy."-".$sm."-".$sd;
		$endDate		=	$ey."-".$em."-".$ed;
		
		
		if($startDate!=$endDate){
		
			$where 			=	" and created_on BETWEEN '".$startDate."' AND '".$endDate."'";
			$dateValue 		=	$varArr[0]."-".$varArr[1];
		
		}else{
			
			
			$where 			=	" and created_on = '".$startDate."'";
			
				
			
			if($todayDate==$startDate){
				
				$dateValue 		=	"Today";
			
			}else{
				
				$dateValue 		=	$startDate;
			
			}
			
		
		}
		
		$dateValue			=	$dateValue;
	}
	
	//$selectEnquiry 		=	"select *from crm_enquiry_capture where 1=1 ". $where." order by created_time desc";
	//$dateValue			=	$dateValue;//$selectEnquiry;
	
	$selectEnquiry 			=	"select ENQ.*,EMP.firstname as agent_firstname,EMP.lastname as agent_lastname,EMP.email as agent_email,EMP.contactNumber as agent_contact_number , EMP.id as agent_id from crm_enquiry_capture ENQ  left join employees EMP on (EMP.id=ENQ.enquiry_assign_to_agent_id)  where 1=1 ". $where." order by ENQ.created_time desc";
	
	$result 			= mysql_query($selectEnquiry);
	$rows 				= mysql_num_rows($result);
	$enquery 			= array();
	
	if($rows > 0){
		
		while($row = mysql_fetch_assoc($result)){
		
			$row['leadJson'] 	=		json_decode($row['leadvalujson']);
			unset($row['leadvalujson']);
			
			$date = new DateTime();
			$date->setTimestamp($row['created_time']);
			$row['created_time']	=	$date->format('d/m/Y H:i:s');
			$date->setTimestamp($row['ivr_push_date']);
			$row['ivr_push_date']	=	$date->format('d/m/Y H:i:s');
			array_push($enquery,$row);
			//$selectupdate 		=	"update crm_enquiry_capture set syn_in_crm=1,syn_marker_new=1";
			$selectupdate 		=	"update crm_enquiry_capture set syn_in_crm=1";
			mysql_query($selectupdate);
		
		}
		
	}

	echo json_encode(array("action"=>'success',"dateRange"=>$dateValue,'enData'=>$enquery,'count'=>count($enquery)),true);

?>

