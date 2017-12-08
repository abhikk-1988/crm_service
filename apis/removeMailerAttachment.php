<?php
session_start();
require 'function.php';

$data = json_decode(file_get_contents('php://input'),true);

if(isset($data)){

    $path = '';

    if(isset($data['name'])){

        $path = '../upload/mailer_attachments/'.$data['name'];

        $mailer_id = '';

        // unlink($path)
        if(unlink($path)){
            // if mailer id is provided then we need to update DB table to remove attachment

            if(isset($data['mailer_id'])){

                $mailer_id = $data['mailer_id'];

                // remove from db itself 
            	// Get all attachments of this mailer 

				$get_mailer_attachment = mysql_query('SELECT attachments FROM project_mailer WHERE id = '.$mailer_id.' LIMIT 1');

				if($get_mailer_attachment){

					$attachments = mysql_fetch_object($get_mailer_attachment);


                    // echo $data['name'];
                    // echo '<br/>';

					$a = array();
					$b = array();

					if($attachments->attachments != 'null'){

						$a = json_decode($attachments->attachments,true);
						
                        foreach($a as $key => $val){
                            if($val['save_name'] != $data['name']){
                                array_push($b, $val);
                            }
                        }
					}

                    if(empty($b)){
                       mysql_query('UPDATE project_mailer SET `attachments` = NULL WHERE id = '.$mailer_id.' LIMIT 1');
                    }else{
                        mysql_query('UPDATE project_mailer SET `attachments` = "'.mysql_real_escape_string(json_encode($b,true)).'" WHERE id = '.$mailer_id.' LIMIT 1');
                    }

                    
                }
            }

            echo (int)1;
        }else{
            echo (int)0;
        }
    }

}
