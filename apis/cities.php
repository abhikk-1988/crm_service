<?php
require_once 'db_connection.php';

if(isset($_GET['state_id'])){
    $state_id = $_GET['state_id'];
}else{
    $state_id = 1; // Default 
}

$city_master	= 'SELECT * FROM `citymaster` WHERE `status` = 1 AND `state_id` = '.$state_id.' ORDER BY citymaster.city_name ';

$result			= mysql_query($city_master);
$rows			= mysql_num_rows($result);
$cities			= array();

if($rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        array_push($cities,$row);
    }
}

echo json_encode($cities,true);



