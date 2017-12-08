<?php
	
	require_once 'db_connection.php';
	
	$data 				=	file_get_contents('php://input');
	$josnDecode 		=	json_decode($data,true); 

	$todayDate 		=	date('Y-m-d');
	$today 			= 	strtotime($todayDate);
	
	$dateValue 		=	$todayDate;
	$where 			=	" and created_time = ".$today;
	
	
	if(isset($josnDecode['date_range']) && $josnDecode['date_range']!=''){
		
		$var  			=	$josnDecode['date_range'];
		$varArr 		=	explode("-",$var);
		
		$startDate 		=	str_replace("/","-",$varArr[0]);
		$EndDate 		=	$varArr[1];
		$endDate 		=	str_replace("/","-",$varArr[1]);
		
		
		
		$startDate 	= strtotime($startDate);
		$endDate 	= strtotime($endDate);
		
		if($startDate!=$endDate){
			
			$dateValue 		=	$varArr[0]."-".$varArr[1];
		
		}else{
			
			$dateValue 		=	$varArr[0];
		}
		
		$where 			=	" and created_time BETWEEN ".$startDate." AND ".$endDate;
	}
	
	$selectEnquiry 		=	"select *from crm_enquiry_capture where 1=1 ". $where." order by created_time desc";
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

