<?php

require_once 'db_connection.php';

$data = file_get_contents('php://input');


if($data == ''){
   echo json_encode(array('is_available' => 0)); exit;
}

$data_array = json_decode($data,true);

if(isset($data_array['username'])){
    $username = filter_var($data_array['username'], FILTER_SANITIZE_EMAIL);
}else{
    $username = '';
}


if($username == ''){
    echo json_encode(array('is_available' => 0)); exit;
}

$username = $data_array['username'];

$check_username = 'SELECT id FROM employees WHERE username = "'.$username.'"';

$result = mysql_query($check_username);

$rows = mysql_num_rows($result);

if($rows > 0){
    
    echo json_encode(array('is_available' => 0)); exit;
}else{
    echo json_encode(array('is_available' => 1)); exit;
}
