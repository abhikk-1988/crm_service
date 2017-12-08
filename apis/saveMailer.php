<?php
session_start();

require 'function.php';

$data = json_decode(file_get_contents('php://input'),true);

$date = date('Y-m-d H:i:s');

if(isset($data)){
    $save_mailer_query = 'INSERT INTO project_mailer (project_id, project_name, content,attachments,creation_date)';
   
    $content = mysql_real_escape_string($data['mailer_data']['content']);

    $attachments = mysql_real_escape_string(json_encode($data['mailer_data']['attachments']));

    if($attachments == 'null' || $attachments == ''){
        
        $save_mailer_query .= ' VALUES ('.$data['mailer_data']['project']['project_id'].',"'.$data['mailer_data']['project']['project_name'].'","'.$content.'",NULL,"'.$date.'")';
    }else{
        $save_mailer_query .= ' VALUES ('.$data['mailer_data']['project']['project_id'].',"'.$data['mailer_data']['project']['project_name'].'","'.$content.'","'.$attachments.'","'.$date.'")';
    }

    
    if(mysql_query($save_mailer_query)){
        echo json_encode(
            array(
                'success' => (int)1,
                'message' => 'Mailer saved successfully'  
             ),true
        ); exit;
    }
    else{
        echo json_encode(array(
             'success' => (int)0,
             'message' => 'Could not save mailer.'
        ),true); exit;
    }
}






