<?php

session_start();
require 'function.php';

function sendMailData($email_data = '', $enquiry_id = '') {

    $curl_url = BASE_URL . 'apis/sendEmailReminder.php';
    $curl = curl_init($curl_url);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $email_data
    ));

    $result = curl_exec($curl);
    curl_close($curl);
}

function sendSMS($numbers = array(), $message = '') {

    if (!empty($numbers)) {

        //foreach($numbers as $number){

        $message = urlencode($message);

        $number_string = implode(',', $numbers);

        if (count($numbers) == 1) {

            $url = 'http://promotionsms.in/api/swsendSingle.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto=' . $number_string . '&message=' . $message;
        } else {

            $url = 'http://promotionsms.in/api/swsend.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto=' . $number_string . '&message=' . $message;
        }

        // Get cURL resource
        $curl = curl_init();

        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 120
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        //} // end foreach
    } // end if condition 
}

if (!function_exists('get_project_city')) {

    function get_project_city($project_id = null) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'http://localhost/apimain/api/get_project_city.php',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array('project_id' => $project_id)
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        if (!$resp) {
            return '';
        } else {

            $response_obj = json_decode($resp, true);
            return $response_obj['city_name'];
        }
    }

}

$data = file_get_contents("php://input");
$data = json_decode($data, true);
$enquiry_id = '';
$agent_id = '';
$errors = array();

// Log container for bulk upload data
$bulk_upload_log = array();

// Loop will start from here

foreach ($data['post']['csv'] as $csv_row) {


    $enquiry_id = $csv_row[0];
    $mobile_number = $csv_row[1];
    $agent_id = $csv_row[2];

    if ($enquiry_id == '' && $mobile_number == '') {

        array_push($bulk_upload_log, array(
            'enquiry_id' => '',
            'mobile_number' => '',
            'agent_id' => $agent_id,
            'remark' => 'Enquiry Id or Mobile number is missing'
        ));

        continue;
    }

    if ($enquiry_id != '') {

        if ($mobile_number != '') {

            // Check combination of both mobile number and enquiry id 

            $mobile_enquiry_combination = mysql_query('SELECT id FROM lead WHERE enquiry_id = ' . $enquiry_id . ' AND customerMobile = "' . $mobile_number . '" LIMIT 1');

            if (mysql_num_rows($mobile_enquiry_combination) <= 0) {

                // Enquiry and mobile number combination not exists

                array_push($bulk_upload_log, array(
                    'enquiry_id' => $enquiry_id,
                    'mobile_number' => $mobile_number,
                    'agent_id' => $agent_id,
                    'remark' => 'Enquiry Id and Mobile number combination not exists in system'
                ));
                continue;
            }
        } else {

            // Check enquiry id and agent is exists
            $is_enquiry_exists = mysql_query('SELECT id FROM lead WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');

            if (mysql_num_rows($is_enquiry_exists) <= 0) {

                array_push($bulk_upload_log, array(
                    'enquiry_id' => $enquiry_id,
                    'mobile_number' => $mobile_number,
                    'agent_id' => $agent_id,
                    'remark' => 'Enquiry ID not exists in system'
                ));

                continue;
            }
        }
    } else if ($mobile_number != '') {

        // Get enquiry id from mobile number

        $is_mobile_number_exists = mysql_query('SELECT enquiry_id FROM lead WHERE customerMobile = "' . $mobile_number . '" LIMIT 1');

        if (mysql_num_rows($is_mobile_number_exists) > 0) {
            $enquiry_data = mysql_fetch_assoc($is_mobile_number_exists);
            $enquiry_id = $enquiry_data['enquiry_id'];
        } else {

            // Enquiry Id is not exists against Mobile number in database

            array_push($bulk_upload_log, array(
                'enquiry_id' => '',
                'mobile_number' => $mobile_number,
                'agent_id' => $agent_id,
                'remark' => 'Enquiry Id is not exists against customer Mobile number in system'
            ));
            continue;
        }
    }

    if ($agent_id == '') {

        array_push($bulk_upload_log, array(
            'enquiry_id' => $enquiry_id,
            'mobile_number' => $mobile_number,
            'agent_id' => '',
            'remark' => 'Agent Id is missing'
        ));

        continue;
    }


    $is_agent_exists = mysql_query('SELECT id FROM employees WHERE id = ' . $agent_id . ' LIMIT 1');

    if (mysql_num_rows($is_agent_exists) <= 0) {

        array_push($bulk_upload_log, array(
            'enquiry_id' => $enquiry_id,
            'mobile_number' => $mobile_number,
            'agent_id' => $agent_id,
            'remark' => 'Agent Is does not exists in system'
        ));

        continue;
    }


    // Get category of $enquiry_id
    $current_date = date('Y-m-d H:i:s');

    $user_id = $_SESSION['currentUser']['id'];

    $designation_id = $_SESSION['currentUser']['designation'];

    $designationName = '';

    $user_name = '';

    // We query from database for user designation incase user designation cannot be found in user session data 
    // otherwise we will use user session to get designation

    if (isset($_SESSION['currentUser']['designation_title'])) {
        $designationName = $_SESSION['currentUser']['designation_title'];
    } else {
        $query = mysql_query("SELECT designation FROM designationmaster WHERE id = '$designation_id' LIMIT 1");
        $result = mysql_fetch_assoc($query);
        $designationName = $result['designation'];
    }

    $user_name = $crm_manager = $_SESSION['currentUser']['firstname'] . ' ' . $_SESSION['currentUser']['lastname'];

    // Lead Info
    $leadDetails = getLead($enquiry_id);

    $lead_added_by_id = '';
    $reassign_user_id = '';

    $lead_added_by_username = getEmployeeName($leadDetails['lead_added_by_user']);
    $lead_added_by_id = $leadDetails['lead_added_by_user'];

    if ($leadDetails['reassign_user_id'] != '') {
        $reassign_user_id = $leadDetails['reassign_user_id'];
    }


    // End Old User Details
    // Get agent details
    $get_new_agent_id = getEmployeeDetails($agent_id);

    $new_agent_email = '';
    $new_agent_name = '';
    if (!empty($get_new_agent_id)) {
        $new_agent_email = $agent_data['email'];
        $new_agent_name = getEmployeeName($agent_id);
    }

    // End Old User Details
    // Get Lead details using enquiry id
    // $leadDetails = getLead($enquiry_id);
    // Insert Data into Re-assign table
    
    if ($reassign_user_id != '') {
        
        ######### UPDATE STICKY AGENT ON REASSIGN OF LEAD ##################
        
        $query_emp = mysql_query("SELECT crm_id from employees where id = '$reassign_user_id'");
        $crm_id_data = mysql_fetch_assoc($query_emp);
        $urls = "";
        $stickyarray = array('mobile' => $leadDetails['customerMobile'], 'agent_id' => $crm_id_data, 'camp_name' => 'Bookmyhouse');
        
        foreach ($stickyarray as $keys => $values) {
            $urls .= urlencode($keys) . '=' . urlencode($values) . '&';
        }

        $hitsticky      = "https://admin.c-zentrixcloud.com/apps/addlead.php";
        $contentsticky  = file_get_contents($hitsticky . '?' . $urls);
        #########END IVR Sticky Agent Push By Sudhanshu##################
        
        mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='agent' AND enquiry_id='$enquiry_id' AND to_user_id='$reassign_user_id' AND change_status='pending' ORDER BY ID DESC LIMIT 1) s)");
    } else {

        mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id = (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='agent' AND enquiry_id='$enquiry_id' AND to_user_id='" . $leadDetails['lead_added_by_user'] . "' AND change_status='pending' ORDER BY ID DESC LIMIT 1) s)");
    }

    $from_user_id = ($leadDetails['reassign_user_id'] != '' ? $leadDetails['reassign_user_id'] : $leadDetails['lead_added_by_user']);


    $re_assign_query = "INSERT INTO lead_re_assign (enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, added_by) VALUES ('$enquiry_id', 'agent', '$from_user_id', '$agent_id', '" . $leadDetails['disposition_status_id'] . "',  '" . $leadDetails['disposition_sub_status_id'] . "', '$user_id')";

    $reAssignId = mysql_query($re_assign_query);


    if ($reAssignId) {

        // Update lead tbl

        mysql_query('UPDATE lead SET is_reassign = 1, reassign_user_id=' . $agent_id . ', reassign_user_type="agent" WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');

        $assignee_name = $user_name;

        if ($reassign_user_id) {
            $lead_added_by_username = getEmployeeName($reassign_user_id);
        }


        $history_text = "Lead/Enquiry Id: #$enquiry_id has been re-assigned by $designationName ($assignee_name) from Agent ($lead_added_by_username) to Agent ($new_agent_name) at " . date('d-m-Y H:i:s');

        $lead_number = $leadDetails['lead_id'];

        // create meta data for future reference
        $meta_data = array();
        if ($reassign_user_id) {

            $meta_data = array('from_user_id' => $reassign_user_id, 'to_user_id' => $agent_id, 'user_type' => 'agent', 'remark' => 're-assign lead', 'enquiry_id' => $enquiry_id, 'assigned_by' => $user_id, 'date' => $current_date);
        } else if ($lead_added_by_id) {

            $meta_data = array('from_user_id' => $lead_added_by_id, 'to_user_id' => $agent_id, 'user_type' => 'agent', 'remark' => 're-assign lead', 'enquiry_id' => $enquiry_id, 'assigned_by' => $user_id, 'date' => $current_date);
        }

        $assignment_history = array(
            'enquiry_id' => $enquiry_id,
            'lead_number' => $lead_number,
            'details' => $history_text,
            'employee_id' => $user_id,
            'type' => 're-assign',
            'meta_data' => mysql_real_escape_string(json_encode($meta_data))
        );

        createLog($assignment_history);

        /*         * ************************************************************* */
        // SEND MAIL TO ASM OF LEAD ASSIGNMENT
        /*         * ************************************************************* */


        $get_email_template = 'SELECT * FROM `email_templates` WHERE email_category = "internal" AND event = "re_assign_agent" LIMIT 1';

        $email_template_resource = mysql_query($get_email_template);

        if ($email_template_resource && mysql_num_rows($email_template_resource) > 0) {

            $email_template_object = mysql_fetch_object($email_template_resource);

            $address = '';

            $scheduled_datetime = '';

            $mail_keywords = array(
                '{{enquiry_no}}',
                '{{agent}}'
            );

            $keyword_replacement_values = array(
                $enquiry_id,
                $new_agent_name
            );

            // Replace value in meessage 
            $mail_body = str_replace($mail_keywords, $keyword_replacement_values, $email_template_object->message_body);

            $default_to_users = '';
            $default_cc_users = '';
            $default_bcc_users = '';

            if ($email_template_object->to_users != '') {
                $default_to_users = $email_template_object->to_users;
            }
            if ($email_template_object->cc_users != '') {
                $default_cc_users = $email_template_object->cc_users;
            }
            if ($email_template_object->bcc_users != '') {
                $default_bcc_users = $email_template_object->bcc_users;
            }

            $mail_data = array(
                MESSAGE => $mail_body,
                DEFAULT_TO_USERS => $default_to_users,
                DEFAULT_CC_USERS => $default_cc_users,
                DEFAULT_BCC_USERS => $default_bcc_users,
                TO => $new_agent_email,
                //				TO	=> "umesh@bookmyhouse.com",
                CC => '',
                BCC => '', // add if any 
                SUBJECT => $email_template_object->subject,
                TO_NAME => $new_agent_name
            );
            sendMailData($mail_data, $enquiry_id);
        }
        // End: MAIL TO AGENT    
        /*         * ************************************************************* */


        array_push($bulk_upload_log, array(
            'enquiry_id' => $enquiry_id,
            'mobile_number' => $mobile_number,
            'agent_id' => $agent_id,
            'remark' => 'Reassigned Successfully'
        ));
    } else {

        array_push($bulk_upload_log, array(
            'enquiry_id' => $enquiry_id,
            'mobile_number' => $mobile_number,
            'agent_id' => $agent_id,
            'remark' => 'Reassigned UnSuccessfull'
        ));
    }
}

// End Foreach
// RESPONSE
$response = array(
    'success' => (int) 1,
    'message' => 'Bulk assignment of enquiries has been completed',
    'log_data' => $bulk_upload_log
);

echo json_encode($response, true);
exit;
