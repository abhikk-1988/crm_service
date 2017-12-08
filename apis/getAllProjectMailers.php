<?php
session_start();
require 'function.php';

$select_mailer = 'SELECT * FROM project_mailer';

$mailers = array();

$data = '';

if($data = mysql_query($select_mailer)){
        
    while($row = mysql_fetch_assoc($data)){

        if(!is_null($row['attachments'])){
            $attachments = json_decode($row['attachments'],true);
            $attachments_count = count($attachments);
            unset($row['attachments']);
            $row['attachments'] = $attachments;
            $row['total_attachment'] = $attachments_count;
        }
        else{
            $row['total_attachment'] = 0;
        }

        array_push(
            $mailers, $row
        );
    }
}


echo json_encode($mailers,true);

