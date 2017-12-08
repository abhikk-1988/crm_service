<?php

$challenge = $_REQUEST['hub_challenge'];
$verify_token = $_REQUEST['hub_verify_token'];

if ($verify_token === 'mylead') {
  echo $challenge;
}

	$json_value		=	file_get_contents('php://input');
	$input = json_decode(file_get_contents('php://input'), true);
	error_log(print_r($input, true));

	
	$leadgen_id 		 =  $input["entry"][0]["changes"][0]["value"]["leadgen_id"];
	$form_id 			 =  $input["entry"][0]["changes"][0]["value"]["form_id"];
	$created_time		 =  $input["entry"][0]["changes"][0]["value"]["created_time"];
	$page_id 			 =  $input["entry"][0]["changes"][0]["value"]["page_id"];
	$adgroup_id 		 =  $input["entry"][0]["changes"][0]["value"]["adgroup_id"];

	
	
	// get leads data..
	function getLead($leadgen_id,$user_access_token)
	{
		
		$graph_url= 'https://graph.facebook.com/v2.7/'.$leadgen_id."?access_token=".$user_access_token;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $graph_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$output = curl_exec($ch); 
		curl_close($ch);
		//work with the lead data
		$leaddata 			= json_decode($output);
		$lead 				= [];
	  
		for($i=0;$i<count($leaddata->field_data);$i++)
		{
			
			$lead[$leaddata->field_data[$i]->name]	=	$leaddata->field_data[$i]->values[0];
		}
		
		return  array('lead'=>$lead,"respnse"=>$leaddata);
	}
	
	
	function  push_to_crm($postDataval)
	{
	
		$ch = curl_init();                   
		$url = "http://52.77.73.171/CRM/apis/save_enquiry.php"; 
		$veryString            =  "fb_enquery";
		//$postDataval        =    array();   //send a copy of enquiry data. 
		$postData           =     json_encode(array('key'=>$veryString,'postEnqueryData'=>$postDataval));
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'enquery='.$postData); // Define what you want to post
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the output in string format
		$output = curl_exec ($ch); // Execute
		curl_close ($ch); // Close cURL handle
		return true;
	}

	
	$user_access_token			=	"EAAH8GunwryEBANce6zLhHSCbsPlQ1nMGyFoBPj4G045j2cSWloW3HZAjXVydbNB1vdO9uycoZCNpWYVote63GtqqXSO0KnD7HfSAZCMT6ZB0tDyxZBRFSXnF8VBZArDkAvPNjAvwH2iZBbN0qJQ0LmsjtX1KwCKOXcZD";	
	
	
	$leadData 	= getLead($leadgen_id,$user_access_token);	
	
	if(count($leadData['lead'])>0){
		
		$leadData['lead']['leadgen_id']		= $leadgen_id;
		$leadData['lead']['form_id']		= $form_id;
		$leadData['lead']['page_id']		= $page_id;
		$leadData['lead']['created_time']	= $created_time;
		
		push_to_crm($leadData['lead']);
	}

	
	// only  for saving logs - dumy  data.
	$leadData			=		json_encode($leadData);	
	$con				=		mysql_connect('localhost','root','bmhproduction@123!');
	$selectDb			=		mysql_select_db("testmydb");
	$insert				=		"insert into fb_leads set hub_challenge ='facebooklog', inputval='".$json_value."' ,  leadvalujson='".$leadData."'";
	$executeQuery		=		mysql_query($insert);
	
?>