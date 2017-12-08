<?php
ini_set('display_errors', 1);
define("HOST", "localhost"); //Define a hostname
define("USER", "root");      //Define a username
define("PASSWORD", "bmhproduction@123!");        //Define a password
define("DB", "bmh_crm");  //Define a database

$con = mysqli_connect(HOST, USER, PASSWORD, DB) OR DIE("Error in  connecting DB : " . mysqli_connect_error());  //Database connection


    
    
$query = "SELECT  a.enquiry_id,a.leadAddDate,a.leadUpdateDate, a.reassign_user_id, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(distinct p.project_name) as enquiry_projects,count(distinct p.project_name) as projectcount,p.project_name

FROM  `lead` as a


LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 

LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 

LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 

LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 

LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 

LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)

LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)

WHERE

a.enquiry_id IN (SELECT a.enquiry_id as enquiry_id

FROM  `lead_meeting` as lm

LEFT JOIN lead AS a ON (lm.enquiry_id = a.enquiry_id)

WHERE
 
from_unixtime(lm.meeting_timestamp/1000) LIKE '%2017-04-17%' AND a.disposition_status_id = 3 AND a.disposition_sub_status_id = 22

UNION ALL

SELECT a11.enquiry_id as enquiryid

FROM  `site_visit` as sv

LEFT JOIN lead AS a11 ON (sv.enquiry_id = a11.enquiry_id)

WHERE
 
from_unixtime(sv.site_visit_timestamp/1000) LIKE '%2017-04-17%' AND a11.disposition_status_id = 6 AND a11.disposition_sub_status_id = 23
)
 
GROUP BY a.enquiry_id,b.firstname,p.project_name";

$result = mysqli_query($con,$query);  // Execute the query
$num_rows = mysqli_num_rows($result);    
        
$projects = [];
//            header("Content-type: text/csv");
//            header("Content-Disposition: attachment; filename=file.csv");
//            header("Pragma: no-cache");
//            header("Expires: 0");
$crm = array();       
while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){  //Fetching the data from the database
	$projectsname = [];
	$projectsname = $row['enquiry_projects'];
	array_push($projects,$projectsname); 
	$projects = array_unique($projects, SORT_REGULAR);
	if($row['reassign_user_id']){
		$user = $row['reassign_user_id'];    
	}else{
		$user = $row['lead_added_by_agent_name'];
	}
  
	$project_name = $row['project_name'];
              
	//$data = array(
	//  'user'  =>  $user,
	//  'count'   => $row['projectcount']
	// );
	// $crm = $data;
}
echo 'username';
foreach($projects as $h){
	echo $h.',';
}
echo PHP_EOL;
$data = array();
$result1 = mysqli_query($con,$query);
while($rows = mysqli_fetch_array($result1,MYSQLI_ASSOC)){ 
	$user = $rows['lead_added_by_agent_name'];
	$project_name = $rows['project_name'];
	$data = array(
		'user'  =>  $user,
		'project_name' => $project_name,
		'count'   => $rows['projectcount']
	);
	if(in_array($projects,$data)){
		echo 'yes';
	}

                
	foreach($data as $key => $value){
		//  $value = str_replace(',','-',$value);
		echo $value.',';
	}
	echo PHP_EOL; 
}
            
            
?>