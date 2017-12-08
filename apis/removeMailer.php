<?php

session_start();

require 'function.php';

$post = json_decode(file_get_contents('php://input'),true);

if(isset($post)){

    $mailer_id = $post['id'];

    // remove mailer 

    if(mysql_query('DELETE FROM `project_mailer` WHERE `id` = '.$mailer_id.' LIMIT 1')){
        echo (int)1;
    }
    else{
        echo (int)0;
    }

}