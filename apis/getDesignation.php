<?php

require_once 'db_connection.php';

$select_designation = 'SELECT * FROM `designationmaster` WHERE markAsDelete = "0" AND disable = "0"';

$result = mysql_query($select_designation);

$rows = mysql_num_rows($result);

$designations = array();

if($rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        array_push($designations,$row);
    }
    
    echo json_encode(array(
        'success' => 1,
        'designations' => $designations
    ),true); exit;
    
}else{
    echo json_encode(array(
        'success' => 0, 
        'message' => 'No designation found',
        'designations' => array()
    ),true); exit;
}