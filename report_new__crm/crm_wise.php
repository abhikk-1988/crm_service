<?php
	
	session_start();
	require_once '../apis/function.php';
	require_once '../apis/user_authentication.php';

	if(!$is_authenticate){
		echo unauthorizedResponse();
		exit;
	}
	$leads = array();
	//$_GET = array('user_id'=>38,'startDate'=>'2017-06-13', 'endDate'=>'2017-06-13');
	
    if((!empty($_GET))) {	// Check whether the date is empty	
    	if(isset($_GET['agent_id'])){	
       
            $user_id = $_GET['agent_id'];
        }
    	
        if(isset($_GET['startDate'])){	
       
            $startDate = date('Y-m-d',strtotime($_GET['startDate']));
        }
        if(isset($_GET['endDate'])){ 
        	
            $endDate = date('Y-m-d',strtotime($_GET['endDate']));    
        }
       	
       	$startDate = $startDate.' 00:00:00';
        	
        $endDate = $endDate.' 23:59:59';
       	
       	$start = strtotime($startDate);
		
		$end = strtotime($endDate);
		
		$days_between = ceil(abs($end - $start) / 86400);
		
		if($user_id!='all'){
			$user_name = ucfirst(getEmployeeName($user_id));
		}else{
			$user_name = 'All_CRM';
		}
		
		$filename = $user_name."_report_from_".$startDate."_to_".$endDate;
		
		
		// Monthly/Yearly Report of Single Agent
		if(isset($startDate) && isset($endDate) && isset($user_id) && $user_id != 'all' && $days_between > 1){
        	
           	$sqlQuery = "SELECT  CONCAT(a.firstname,' ',a.lastname) as CRM, a.doj AS DOJ,
           	
           	(CASE WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 12 THEN '> 1 Year' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 9 THEN '>9 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 6 THEN '>6 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 3 THEN '>3 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) < 3 THEN '<3 Months'  END) as Vintage, 
           	
           	(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate') as CM,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS Meetings,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS site_visit,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS closer,
           	
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS Meeting_percentage,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS site_visit_percentage,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS closer_percentage
           	
           	FROM employees as a
           	
           	WHERE a.designation = 9 AND a.id='$user_id' ORDER BY a.firstname ASC";

//			die($sqlQuery);
		  	
		  	
		  	$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'CRM NAME';
			    $header[] = 'DOJ';  
			    $header[] = 'Vintage';
			    $header[] = 'CM';
			    $header[] = 'Meetings';
			    $header[] = 'Site Visit';
			    $header[] = 'Closer';
			    $header[] = 'Meetings%';
			    $header[] = 'Site Visit%';
			    $header[] = 'Closer%';
			    
			    if(count($leads) > 0){
					$str = '<div class="media">';
			        header("Content-type: text/csv");
			        header("Content-Disposition: attachment; filename=$filename.csv");
			        header("Pragma: no-cache");
			        header("Expires: 0");
			        $data = array();
			        
			        foreach($header as $h){
			            echo $h.',';
			        }
			        echo PHP_EOL;
			        $x = 0;
			        while($x <= count($leads)) {
			        	foreach($leads[$x] as $key => $value){
							$value = str_replace(',','-',$value);
	            			echo $value.',';
	            			
				        }
				        echo PHP_EOL;
			        	
			        	$x++;
					}
				}else{
					echo "<p>No record found</p>";	
				}	
			}else{
				echo "<p>No record found!</p>";	
			}
    	}
       	// Daily Report of Single Agent
        elseif(isset($startDate) && isset($endDate) && isset($user_id) && $user_id != 'all' && $days_between==1){
        	
           	$sqlQuery = "SELECT CONCAT(a.firstname,' ',a.lastname) as CRM, a.doj AS DOJ,
           	
			(CASE WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 12 THEN '> 1 Year' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 9 THEN '>9 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 6 THEN '>6 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 3 THEN '>3 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) < 3 THEN '<3 Months' END) as Vintage, 
           	
           	(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')As CM,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS Meetings,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS site_visit,
           	
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS closer,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS Meeting_percentage,
           	
			ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS site_visit_percentage,
           	
			ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS closer_percentage
           	 
           	FROM employees as a
           	
           	WHERE a.designation = 9 AND a.id='$user_id' ORDER BY a.firstname ASC";

//			die($sqlQuery);
		  	
		  	
		  	$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'CRM NAME';
			    $header[] = 'DOJ';  
			    $header[] = 'Vintage';
			    $header[] = 'CM';
			    $header[] = 'Meetings';
			    $header[] = 'Site Visit';
			    $header[] = 'Closer';
			    $header[] = 'Meetings%';
			    $header[] = 'Site Visit%';
			    $header[] = 'Closer%';
			    
			    if(count($leads) > 0){
					$str = '<div class="media">';
			        header("Content-type: text/csv");
			        header("Content-Disposition: attachment; filename=$filename.csv");
			        header("Pragma: no-cache");
			        header("Expires: 0");
			        $data = array();
			        
			        foreach($header as $h){
			            echo $h.',';
			        }
			        echo PHP_EOL;
			        $x = 0;
			        while($x <= count($leads)) {
			        	foreach($leads[$x] as $key => $value){
							$value = str_replace(',','-',$value);
	            			echo $value.',';
	            			
				        }
				        echo PHP_EOL;
			        	
			        	$x++;
					}
				}else{
					echo "<p>No record found</p>";	
				}	
			}else{
				echo "<p>No record found!</p>";	
			}
    	}
    	
    	// Monthly/Yearly Report of All Agent
    	elseif(isset($startDate) && isset($endDate) && isset($user_id) && $user_id == 'all' && $days_between > 1){
        	
           	$sqlQuery = "SELECT  CONCAT(a.firstname,' ',a.lastname) as CRM, a.doj AS DOJ,
           	
           	(CASE WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 12 THEN '> 1 Year' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 9 THEN '>9 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 6 THEN '>6 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 3 THEN '>3 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) < 3 THEN '<3 Months'  END) as Vintage, 
           	
           	(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate') as CM,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS Meetings,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS site_visit,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS closer,
           	
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS Meeting_percentage,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS site_visit_percentage,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS closer_percentage
           	
           	FROM employees as a
           	
           	WHERE a.designation = 9 ORDER BY a.firstname ASC";

			$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'CRM NAME';
			    $header[] = 'DOJ';  
			    $header[] = 'Vintage';
			    $header[] = 'CM';
			    $header[] = 'Meetings';
			    $header[] = 'Site Visit';
			    $header[] = 'Closer';
			    $header[] = 'Meetings%';
			    $header[] = 'Site Visit%';
			    $header[] = 'Closer%';
			    
			    if(count($leads) > 0){
					$str = '<div class="media">';
			        header("Content-type: text/csv");
			        header("Content-Disposition: attachment; filename=$filename.csv");
			        header("Pragma: no-cache");
			        header("Expires: 0");
			        $data = array();
			        
			        foreach($header as $h){
			            echo $h.',';
			        }
			        echo PHP_EOL;
			        $x = 0;
			        while($x <= count($leads)) {
			        	foreach($leads[$x] as $key => $value){
							$value = str_replace(',','-',$value);
	            			echo $value.',';
	            			
				        }
				        echo PHP_EOL;
			        	
			        	$x++;
					}
				}else{
					echo "<p>No record found</p>";	
				}	
			}else{
				echo "<p>No record found!</p>";	
			}
    	}
    	
    	
    	// Daily Report of All Agent
    	elseif(isset($startDate) && isset($endDate) && isset($user_id) && $user_id == 'all' && $days_between==1){
        	
           	$sqlQuery = "SELECT CONCAT(a.firstname,' ',a.lastname) as CRM, a.doj AS DOJ,
           	
           	(CASE WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 12 THEN '> 1 Year' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 9 THEN '>9 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 6 THEN '>6 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) > 3 THEN '>3 Months' WHEN TIMESTAMPDIFF(MONTH, a.doj, CURDATE()) < 3 THEN '<3 Months' END) as Vintage, 
           	
           	(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate') as CM,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS Meetings,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS site_visit,
           	
           	(SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE user_id = a.id AND disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate') AS closer,
           	
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE disposition_status_id = 3 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS Meeting_percentage,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE disposition_status_id = 6 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS site_visit_percentage,
           	
           	ROUND(((SELECT count(DISTINCT enquiry_id) FROM lead_status WHERE disposition_status_id = 7 AND user_type='agent' AND date > '$startDate' AND date < '$endDate')/(SELECT count(enquiry_id) FROM lead_status WHERE user_id = a.id AND user_type = 'agent' AND date > '$startDate' AND date < '$endDate')*100),2) AS closer_percentage
           	
           	FROM employees as a
           	
           	WHERE a.designation = 9 ORDER BY a.firstname ASC";

//			die($sqlQuery);

			$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'CRM NAME';
			    $header[] = 'DOJ';  
			    $header[] = 'Vintage';
			    $header[] = 'CM';
			    $header[] = 'Meetings';
			    $header[] = 'Site Visit';
			    $header[] = 'Closer';
			    $header[] = 'Meetings%';
			    $header[] = 'Site Visit%';
			    $header[] = 'Closer%';
			    
			    if(count($leads) > 0){
					$str = '<div class="media">';
			        header("Content-type: text/csv");
			        header("Content-Disposition: attachment; filename=$filename.csv");
			        header("Pragma: no-cache");
			        header("Expires: 0");
			        $data = array();
			        
			        foreach($header as $h){
			            echo $h.',';
			        }
			        echo PHP_EOL;
			        $x = 0;
			        while($x <= count($leads)) {
			        	foreach($leads[$x] as $key => $value){
							$value = str_replace(',','-',$value);
	            			echo $value.',';
	            			
				        }
				        echo PHP_EOL;
			        	
			        	$x++;
					}
				}else{
					echo "<p>No record found</p>";	
				}	
			}else{
				echo "<p>No record found!</p>";	
			}
    	}
    	
    	else{
			echo "<p>Date range format not correct!</p>";
		}
    } else{
        echo "<p>You have not selected any date!</p>";
    }

?>