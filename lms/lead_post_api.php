<?php
    $ch = curl_init();                    // Initiate cURL
    $url = "http://52.77.73.171/CRM/apis/save_enquiry.php"; // Where you want to post data
	$veryString		= "bmh_enquery";
	$postDataval		=	array('name'=>'Moahammad Asuf','email'=>'asif@bookmyhouse.com','phone'=>'9873878430');
	$postData   	= json_encode(array('key'=>$veryString,'postEnqueryData'=>$postDataval));
	
	curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, true);  // Tell cURL you want to post something
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'enquery='.$postData); // Define what you want to post
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the output in string format
    $output = curl_exec ($ch); // Execute
	curl_close ($ch); // Close cURL handle
	
	echo "<pre>";
	print_r($output);
	//var_dump($output); // Show output
?>