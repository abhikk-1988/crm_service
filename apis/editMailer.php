<?php 

session_start();
require 'function.php';

$post = json_decode(file_get_contents('php://input'),true);

if(isset($post)){

    $mailer_id  = $post['data']['id'];

    $content    = $post['data']['content'];

    if(mysql_query('UPDATE project_mailer SET content = "'.mysql_real_escape_string($content).'" WHERE id = '.$mailer_id.' LIMIT 1')){
		echo 1; exit;							
	}
    else{
        echo 0; exit;
    }
}

