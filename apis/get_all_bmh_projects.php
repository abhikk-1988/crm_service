<?php

	$curl = curl_init();
	curl_setopt_array($curl, array(
	   CURLOPT_RETURNTRANSFER => 1,
	   CURLOPT_URL => 'http://52.77.73.171/apimain/api/get_all_projects.php',
	));

	$resp = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);
 
	echo $resp; exit; // JSON Response
 