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
    	if(isset($_GET['project'])){	
       
            $project = $_GET['project'];
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
		
		if($project!='all'){
			$project_name = ucfirst(getEmployeeName($user_id));
		}else{
			$project_name = 'All_Projects';
		}
		
		$filename = $project_name."_report_from_".$startDate."_to_".$endDate;
		
		// Monthly/Yearly Report of Signle Project
    	if(isset($startDate) && isset($endDate) && isset($project) && $project != 'all' && $days_between > 1){
        	
           	$sqlQuery = "SELECT project_name,
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3) AS Meetings, 
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN site_visit as c ON c.enquiry_id = a.enquiry_id AND c.site_visit_id = a.site_visit_id AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 6) AS site_visit,
           	
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' WHERE a.lead_closure_date > '$startDate' AND a.lead_closure_date < '$endDate') AS closer,
           	
           	ROUND(((SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN site_visit as c ON c.enquiry_id = a.enquiry_id AND c.site_visit_id = a.site_visit_id AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 6)/(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3)*100),2) AS site_visit_percentage,
           	
           	
           	ROUND(((SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' WHERE a.lead_closure_date > '$startDate' AND a.lead_closure_date < '$endDate')/(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3)*100),2) AS closer_percentage
           	
           	FROM lead_enquiry_projects as a 
           	
           	WHERE project_name = '$project' GROUP BY project_name ORDER BY project_name ASC";
           	
			$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'Project NAME';
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
    	
    	// Monthly/Yearly report of all Project
    	elseif(isset($startDate) && isset($endDate) && isset($project) && $project == 'all' && $days_between > 1){
        	
           	$sqlQuery = "SELECT p.project_name,
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3 AND b.project_name = p.project_name) AS Meetings, 
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN site_visit as c ON c.enquiry_id = a.enquiry_id AND c.site_visit_id = a.site_visit_id AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 6 AND b.project_name = p.project_name) AS site_visit,
           	
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id WHERE a.lead_closure_date > '$startDate' AND a.lead_closure_date < '$endDate') AS closer,
           	
           	ROUND(((SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN site_visit as c ON c.enquiry_id = a.enquiry_id AND c.site_visit_id = a.site_visit_id AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 6 AND b.project_name = p.project_name)/(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3 AND b.project_name = p.project_name)*100),2) AS site_visit_percentage,
           	
           	
           	ROUND(((SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id WHERE a.lead_closure_date > '$startDate' AND a.lead_closure_date < '$endDate')/(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3 AND b.project_name = p.project_name)*100),2) AS closer_percentage
           	
           	FROM lead_enquiry_projects as p 
           	
           	GROUP BY p.project_name ORDER BY p.project_name ASC";
           	
			$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'Project NAME';
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
		
		// Daily Report of All Project
		elseif(isset($startDate) && isset($endDate) && isset($project) && $project == 'all' && $days_between == 1){
        	
           	$sqlQuery = "SELECT p.project_name,
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3 AND b.project_name = p.project_name) AS Meetings, 
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id JOIN site_visit as c ON c.enquiry_id = a.enquiry_id AND c.site_visit_id = a.site_visit_id AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 6 AND b.project_name = p.project_name) AS site_visit,
           	
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id WHERE a.lead_closure_date > '$startDate' AND a.lead_closure_date < '$endDate' AND b.project_name = p.project_name) AS closer
           	
           	FROM lead_enquiry_projects as p 
           	
           	GROUP BY p.project_name ORDER BY p.project_name ASC";
           	
//           	die($sqlQuery);           	
			$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
					
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'Project NAME';
			    $header[] = 'Meetings';
			    $header[] = 'Site Visit';
			    $header[] = 'Closer';
			    
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
		
    	// Daily Report of sinle project
    	elseif(isset($startDate) && isset($endDate) && isset($project) && $project != 'all' && $days_between == 1){
    		
			$sqlQuery = "SELECT project_name,
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN lead_meeting as c ON c.enquiry_id = a.enquiry_id AND c.meetingId = a.meeting_id AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 3) AS Meetings, 
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' JOIN site_visit as c ON c.enquiry_id = a.enquiry_id AND c.site_visit_id = a.site_visit_id AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') > '$startDate' AND FROM_UNIXTIME(c.site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s') < '$endDate' WHERE a.disposition_status_id = 6) AS site_visit,
           	
           	
           	(SELECT count(a.enquiry_id) FROM lead as a JOIN lead_enquiry_projects as b ON b.enquiry_id = a.enquiry_id AND b.project_name = '$project' WHERE a.lead_closure_date > '$startDate' AND a.lead_closure_date < '$endDate') AS closer
           	
           	FROM lead_enquiry_projects as a 
           	
           	WHERE project_name = '$project' GROUP BY project_name ORDER BY project_name ASC";
           	
			$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					array_push($leads, $row);
				}
//				print_r($leads); die;
				$header = [];
			    $header[] = 'Project NAME';
			    $header[] = 'Meetings';
			    $header[] = 'Site Visit';
			    $header[] = 'Closer';
			    
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