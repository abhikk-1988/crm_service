<?php

/**
 * PHP pusher client
 */

require 'Pusher.php';

// Pusher server side conf

 $options = array(
    'encrypted' => false
  );

// New Pusher Instance 
$pusher = new Pusher('63248131fd9867c8e685','11e9c4f6a6dfe6eb0f4f','321647',$options);

$channel    = '';

// Domain 
$domain = $_SERVER['HTTP_HOST'];

// Current Date
$current_date = date('Y-m-d H:i:s');

// POST DATA 
if(isset($_POST)){

    $post = filter_input_array(INPUT_POST);

    require_once '../db_connection.php';

    // Channel name
    $channel = $domain.'@'.$post['user_id'];
    
    // Insert query to push notification message to DB
    $insert_notification = 'INSERT INTO notifications_center ';
    $insert_notification .= ' (user_id,event,title,message,link,is_read,notification_bg_color, domain, channel,notification_generation_date)';
    $insert_notification .= ' VALUES ('.$post['user_id'].' , "'.$post['event'].'","'.$post['title'].'","'.htmlspecialchars($post['message']).'","'.$post['link'].'","0","'.$post['bg_color'].'","'.$domain.'","'.$channel.'","'.$current_date.'")';

    if(!mysql_query($insert_notification)){
        echo mysql_error(); 
    }
    
    // Trigger event on channel
    $pusher -> trigger($channel,$post['event'],array('message' => htmlspecialchars($post['message']),'title'=> $post['title'],'notification_type' => $post['notification_type'], 'mode' => 'push_notifcation'));

    echo 'Notification sent successfully<br/>';
}


