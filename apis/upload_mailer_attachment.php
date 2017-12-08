<?php
session_start();
require 'function.php';

if(isset($_FILES['file'])){
		
		$errors     = array();
		$file_name	=	$_FILES['file']['name'];
		$file_size	=	$_FILES['file']['size'];
		$file_tmp	=	$_FILES['file']['tmp_name'];
//		$file_type	=	$_FILES['file']['type'];
		$file_ext	=	strtolower(end(explode('.',$_FILES['file']['name'])));
		$expensions	=	array("jpeg","jpg","png");
      
		$file_name_new = 'mailer'.time().'.'. $file_ext;
		
		// system path where file will be uploaded
		$upload_path = dirname(__DIR__). '/upload/mailer_attachments/' ;
		
		if(move_uploaded_file($file_tmp, $upload_path.$file_name_new)){

            // send in resonse, name of the uploaded file and full uploaded path

			// If mailer is is there 
			if(isset($_POST['mailer_id'])){

				// Get all attachments of this mailer 
				$get_mailer_attachment = mysql_query('SELECT attachments FROM project_mailer WHERE id = '.$_POST['mailer_id'].' LIMIT 1');

				if($get_mailer_attachment){

					$attachments = mysql_fetch_object($get_mailer_attachment);

					// echo '<pre>';
					// print_r($attachments);exit;

					$a = array();
					
					if(!is_null($attachments->attachments)){
						$a = json_decode($attachments->attachments,true);
						array_push($a, array(
                			'original_name' => $file_name,
                			'save_name' => $file_name_new
            			));
					}
					else{
						array_push($a, array(
                			'original_name' => $file_name,
                			'save_name' => $file_name_new
            			));
					}

					// Update mailer 
					if(mysql_query('UPDATE project_mailer SET attachments = "'.mysql_real_escape_string(json_encode($a,true)).'" WHERE id = '.$_POST['mailer_id'].' LIMIT 1')){
						echo 1; exit;							
					}
					else{
						echo 0; exit;
					}

				}
			}		

           echo  json_encode(array(
                'is_uploaded' => (int)1,    
                'original_name' => $file_name,
                'save_name' => $file_name_new,
                'path' => BASE_URL . 'upload/mailer_attachments/'.$file_name_new
            ),true);
            exit;

		}else{
			echo  json_encode(array(
                'is_uploaded' => (int)0
            ),true);
            exit;
		}
}