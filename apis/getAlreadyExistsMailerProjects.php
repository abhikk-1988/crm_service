<?php

session_start();
require 'function.php';

$get_mailer_projects_name = 'SELECT project_id, project_name FROM project_mailer';

$result = '';
$projects = array();

if($result = mysql_query($get_mailer_projects_name)){

    while($record = mysql_fetch_assoc($result)){
        array_push($projects,$record);
    }
}

echo json_encode($projects); exit;