<?php

session_start();

require 'function.php';

if( !isset($_SESSION['currentUser'])){

    echo json_encode(
        array(
            'success' => (int) 0,
            'message' => 'Invalid reqest. User not authenticated',
            'title' => 'Authentication Failure',
            'http_status_code' => (int) 401
        ), true
    ); exit;
 }

 $user = $_SESSION['currentUser'];

 $post_json_string = file_get_contents('php://input');

 if(isset($post_json_string)){
    // Json Decode post data string 

    $post_data = json_decode($post_json_string, true);

    $enquiry_id = '';

    $allowed_users = array();

    if(isset($post_data['enquiry_id'])){

        $enquiry_id = $post_data['enquiry_id'];

        // Get lead crm user id and their reporting manager 
        $get_lead_crm = mysql_query('SELECT l.lead_added_by_user as crm_id,  e.reportingTo as crm_reporting_id,reassign_user_id, reassign_user_type, lead_assigned_to_asm, lead_assigned_to_sp FROM lead as l LEFT JOIN employees as e ON (l.lead_added_by_user = e.id) WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');

        if($get_lead_crm && mysql_num_rows($get_lead_crm) > 0){

            $lead_crm = mysql_fetch_assoc($get_lead_crm);
            
            array_push($allowed_users,$lead_crm['crm_reporting_id']);
            
            if($lead_crm['reassign_user_type']=='agent' && $lead_crm['reassign_user_id']){
			
				array_push($allowed_users,$lead_crm['reassign_user_id']);
			
			}else{

				array_push($allowed_users,$lead_crm['crm_id']);
			
			}

//            if($lead_crm['lead_assigned_to_asm']){
//                array_push($allowed_users,$lead_crm['lead_assigned_to_asm']);
//            }
//            if($lead_crm['lead_assigned_to_sp']){
//                array_push($allowed_users,$lead_crm['lead_assigned_to_sp']);            
//            }
           
        }

        // Get CRM TL 
        $sr_tl_designation = mysql_query('SELECT emp.id as tl_id FROM designationmaster as d LEFT JOIN employees as emp ON (d.id = emp.designation) WHERE designation_slug = "sr_team_leader" LIMIT 1');
        if($sr_tl_designation && mysql_num_rows($sr_tl_designation) > 0){

            $crm_tl = mysql_fetch_assoc($sr_tl_designation);
            array_push($allowed_users,$crm_tl['tl_id']);
        }


    }

    if(in_array($user['id'], $allowed_users)){
        
        $res = array(
            'success' => (int) 1,
            'is_editable' => (bool) true,
            'http_status_code' => (int) 200
        );

        echo json_encode($res, true); exit;
        
    }else{
        $res = array(
            'success' => (int) 1,
            'is_editable' => (bool) false,
            'http_status_code' => (int) 200
        );
        echo json_encode($res, true); exit;
    }
 }