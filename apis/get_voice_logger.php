<?php
session_start();
require 'db_connection.php';
$user = $_SESSION['currentUser'];

$data = file_get_contents('php://input');
$jsonDecode = json_decode($data, true);
if(!empty($_REQUEST)){
$mob = $_REQUEST['mobno'];
}else{
    $mob = $jsonDecode['mobno'];
}
$logger = array();
$where = " AND agent_id = '".$user['crm_id']."'";
foreach($user['modules']['modules']['Lead']['submenu'] as $modules){
    foreach($modules as $module => $data){
        if($module == 'All Lead' AND $data['permission'] == 7){
            $where = "";
        }
        if($module == 'Re-Assign Lead' AND $data['permission'] == 7){
            $where = "";
        }
    }
}
$query = "SELECT voice_logger.*,emp.firstname,emp.lastname FROM voice_logger LEFT JOIN employees as emp ON emp.crm_id = voice_logger.agent_id WHERE cust_mobile_no = '".$mob."' AND voice_logger.agent_id != 0 ".$where;

$entries = mysql_query($query);
while($row = mysql_fetch_assoc($entries)){
	
$logger[] = array(
    'audio' => 'https://admin.c-zentrixcloud.com/logger/'.$row['voice_url'],
    'insert_date_time' => date('d F Y g:i a',strtotime($row['insert_date_time'])),
    'call_type' => $row['call_type'],
    'agent' => $row['firstname'].$row['lastname'],
    'agent_id' => $row['agent_id']
    );	

}

header('Content-Type: application/json');
echo json_encode($logger);	

	

