<?php
session_start();
require 'function.php';
$data = json_decode(file_get_contents('php://input'),true);

if($data){

    if(isset($data['meta_data'])){

        // Json encode meta data 
        $meta_data = json_encode($data['meta_data']);

        unset($data['meta_data']);

        $data['meta_data'] = mysql_real_escape_string($meta_data);   
    }

    echo createLog($data);
}
