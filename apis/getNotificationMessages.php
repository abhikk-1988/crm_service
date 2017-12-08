<?php

/**
 *  API to get notifications
 */

    session_start();

    $user = $_SESSION['currentUser'];

    require 'db_connection.php';

    $notifications      = array();

    if(isset($_GET)){

        // Select messages

        $is_read = 0;
        if($_GET['mode'] === 'unread'){
            $is_read = 0;
        }
        else{
            $is_read = 1;
        }

        
        $messages           = 'SELECT * FROM notifications_center 
        WHERE is_read = "'.$is_read.'" AND domain = "'.$_GET['domain'].'" AND DATE(notification_generation_date) = "'.$_GET['date'].'" AND user_id = '.$user['id'].' 
        ORDER BY notification_generation_date DESC';
           
        $messages_result    = mysql_query($messages);

        if($messages_result && mysql_num_rows($messages_result) > 0){

            while($row = mysql_fetch_assoc($messages_result)){

                if($is_read == 0){
                    
                    mysql_query('UPDATE notifications_center SET is_read = "1" WHERE id = '.$row['id'].'');
                }

                // $notification_timestamp = strtotime($row['notification_generation_date']);
                
                $row['time'] = date('d/m/y H:i A', strtotime($row['notification_generation_date']));
                array_push($notifications, $row);
            }
        }
    }


    echo json_encode($notifications,true);