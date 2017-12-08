<?php
session_start();
require 'function.php';

if(!isset($_SESSION['currentUser'])){
    echo json_encode(array(),true); exit;
}

$agent_desingation = mysql_query('SELECT id FROM designationmaster WHERE designation_slug = "agent" LIMIT 1');

$agents = array();

if($agent_desingation && mysql_num_rows($agent_desingation) > 0){

    $designation_id  = mysql_fetch_assoc($agent_desingation)['id'];

    $agent_users = mysql_query('SELECT id, CONCAT(firstname," ",lastname) as agent_name FROM employees WHERE designation = '.$designation_id.' AND isDelete=0 AND activeStatus=1 ORDER BY firstname ASC');

    if($agent_users && mysql_num_rows($agent_users) > 0){

        while($row = mysql_fetch_assoc($agent_users)){
            
            array_push($agents, $row);
        }
    }
}

echo json_encode(array('agents' => $agents),true); exit;
