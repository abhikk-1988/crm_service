<?php
session_start();
require 'function.php';

if(!isset($_SESSION['currentUser'])){
    // unauthorized access 
    // user session is not there
    echo json_encode(array(
        'success' => (int) 1 ,
        'message' => 'Unauthorized access. You are not allowed to do this action',
        'http_status_code' => (int) 401
    ),true); exit;
}

// session user
$user = $_SESSION['currentUser'];

$post_data = json_decode(file_get_contents('php://input'),true);

if(isset($post_data['enquiry_id']) && isset($post_data['project'])){

    $project = $post_data['project'];
    $enquiry_id = $post_data['enquiry_id'];

    if($project['project_id'] != '' && $enquiry_id!= ''){

        $delete_project = mysql_query('DELETE FROM `lead_enquiry_projects` WHERE enquiry_id = '.$enquiry_id.' AND project_id = '.$project['project_id'].' LIMIT 1');
        
        if($delete_project){

            // enter log of removing project
            $log_text = 'Enquiry Project ('.$project['project_name'].') has been removed by '. $user['firstname'] . ' '. $user['lastname'] . ' on '. date('d/m/Y H:i A');

            $log = array(
                'details' => $log_text,
                'type' => 'edit',
                'employee_id' => $user['id'],
                'enquiry_id' => $enquiry_id,
                'lead_number' => getLeadNumber($enquiry_id)
            );

            createLog($log);

            echo json_encode(array(
                'success' => (int) 1 ,
                'message' => 'Project removed successfully',
                'http_status_code' => (int) 200
            ),true); exit;
        }else{
            echo json_encode(array(
                'success' => (int) 0 ,
                'message' => 'Unable to delete project at this time',
                'http_status_code' => (int) 200
            ),true); exit;
        }
    }    
}
else{
    echo json_encode(array(
        'success' => (int) 0 ,
        'message' => 'This action cannot be done at this time.',
        'http_status_code' => (int) 200
    ),true); exit;
}