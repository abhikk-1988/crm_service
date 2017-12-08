<?php
require_once 'db_connection.php';

$country_code = 91;

$state_master = 'SELECT * FROM `statemaster` WHERE `status` = 1 ORDER BY statemaster.state_name ';
$result = mysql_query($state_master);
$rows   = mysql_num_rows($result);
$states = array();
if($rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        array_push($states,$row);
    }
}

echo json_encode($states,true);