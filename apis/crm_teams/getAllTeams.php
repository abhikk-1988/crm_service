<?php

// API overview: Get All CRM TEAMS
// HTTP METHOD : Get

require '../function.php';

$crm_teams = array();

$query = 'SELECT * FROM crm_teams';

$result = mysql_query($query);

if($result && mysql_num_rows($result) > 0){

    while($row = mysql_fetch_assoc($result)){

        // Get team members 
        $members_query = 'SELECT id, CONCAT(firstname," ",lastname) AS member_name FROM employees WHERE crm_team = '.$row['id'].' ';

        $members_query_result = mysql_query($members_query);
        
        $row['members'] = array();

        while($inner_row = mysql_fetch_assoc($members_query_result)){
            array_push($row['members'], $inner_row);
        }   
        
        array_push($crm_teams, $row);
    }
}

echo json_encode($crm_teams,true); exit;