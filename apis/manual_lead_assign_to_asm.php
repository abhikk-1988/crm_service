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
            CURLOPT_URL => 'http://52.77.73.171/apimain/api/get_project_city.php',
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

$data = filter_input_array(INPUT_POST);
$enquiry_id = '';
$asm_id = '';
$user = '';
$errors = array();

if (isset($data['enquiry_id'])) {
    $enquiry_id = $data['enquiry_id'];
} else {
    $errors['enquiry_id'] = 'Enquiry Id not provided';
}

if (isset($data['asm_id']) && $data['asm_id'] != '') {
    $asm_id = $data['asm_id'];
} else {
    $errors['asm_id'] = 'ASM id not provided';
}

if (isset($data['login_user_id']) && $data['login_user_id'] != '') {
    $user = $data['login_user_id'];
} else {
    $errrors['login_user_id'] = 'User not authorize';
}


// Validation check for enquiry id or asm id 
if (!empty($errors)) {
    echo json_encode(
            array(
        'success' => 0,
        'message' => 'Either enquiry number or ASM id not provided'
            ), true
    );
    exit;
}

// Get category of $enquiry_id 
$current_month = (int) date('m') - 1;
$current_year = date('Y');
$current_date = date('Y-m-d H:i:s');
$user_name = '';
if ($user != '') {
    $user_name = $crm_manager = getEmployeeName($user);
}

$lead_category = '';

// Get lead category type 
$get_lead_category_type = 'SELECT lead_category,customerName,customerEmail,customerAddress, customer_alternate_mobile,customerProfession, customerMobile,lead_added_by_user, disposition_status_id,disposition_sub_status_id, meeting_id, site_visit_id FROM lead WHERE enquiry_id = ' . $enquiry_id . '  LIMIT 1';

// Get lead category type 
$lead = getLead($enquiry_id);

if (count($lead) > 0) {

    $lead_data = (object) $lead;
    $lead_category = $lead_data->lead_category;
    $client_name = $lead_data->customerName;
    $client_email = $lead_data->customerEmail;
    $client_mobile = $lead_data->customerMobile;
    $client_address = $lead_data->customerAddress;
    $client_alternate_number = $lead_data->customer_alternate_mobile;
    $client_profession = $lead_data->customerProfession;
    $lead_current_status = getStatusLabel($lead_data->disposition_status_id, 'parent'); // Primary status 
    $lead_current_sub_status = getStatusLabel($lead_data->disposition_sub_status_id, 'child'); // Secondary Status
    $lead_meeting_id = $lead_data->meeting_id; // Meeting ID
    $lead_site_visit_id = $lead_data->site_visit_id; // Site Visit ID

    if ($lead_data->reassign_user_id != '') {
        $lead_owner = getEmployeeName($lead_data->reassign_user_id); // lead CRM
    } else {
        $lead_owner = getEmployeeName($lead_data->lead_added_by_user); // lead CRM	
    }
}

$meeting_data = '';
$site_visit_data = '';

$meeting_project_ids = array(); // Id's of the meeting projects if multiple
$meeting_project_list = array(); // List of selected projects for meeting
// Get Meeting or Site Visit Project 
if ($lead_meeting_id != '') {

    // Fetch from meeting 

    $meeting_data = getLeadMeetingData($enquiry_id, $lead_meeting_id);

    if (isset($meeting_data['project'])) {

        $meeting_project = json_decode($meeting_data['project'], true);

        foreach ($meeting_project as $key => $single_project) {

            array_push($meeting_project_ids, $single_project['project_id']);
            array_push($meeting_project_list, $single_project['project_name']);
        }
    }
} else {
    // fetch from site visit

    $site_visit_data = getSiteVisitDataById($lead_site_visit_id);

    if (isset($site_visit_data['project'])) {

        $site_visit_project = json_decode($site_visit_data['project'], true);
        foreach ($site_visit_project as $key => $single_project) {

            array_push($meeting_project_ids, $single_project['project_id']);
            array_push($meeting_project_list, $single_project['project_name']);
        }
    }
}

if (empty($meeting_project_ids)) {
//	$response = array(
//		'success' => 0, 
//		'message' => 'Lead could not be assigned to TM as no meeting project',
//		'title' => 'Meeting Project Missing'
//		);
//		echo json_encode($response,true); exit;
} else {
    // Check lead category and assign lead to TM accordingly

    if (strtolower($lead_category) === 'spl') {

        $select_area_sales_manager_sql = 'SELECT userId, capacity, remaining_capacity '
                . ' FROM capacity_master '
                . ' WHERE pId IN (' . implode(',', $meeting_project_ids) . ') AND capacity_month = ' . $current_month . ' AND capacity_year = "' . $current_year . '"  AND userId = ' . $asm_id . ' AND remaining_capacity > 0 GROUP BY userId';

        $area_sales_manager_result = mysql_query($select_area_sales_manager_sql);

        if ($area_sales_manager_result && mysql_num_rows($area_sales_manager_result) > 0) {

            $asm_user = mysql_fetch_object($area_sales_manager_result);

            // Getting TM information	
            $asm_name = getEmployeeName($asm_user->userId);
            $asm_email = getEmployeeEmailAddress($asm_user->userId);
            $asm_number = getEmployeeMobileNumber($asm_user->userId);
            $sales_person = '';

            // Assigning Lead to TM
            $assign_lead_to_asm = 'UPDATE `lead` '
                    . ' SET lead_assigned_to_asm = ' . $asm_user->userId . ','
                    . ' lead_assigned_to_asm_on = "' . $current_date . '"'
                    . ' WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';

            if (mysql_query($assign_lead_to_asm)) {

                // Updating capacity of TM
                // insert data into re-assign table as a log purpose (Umesh)

                mysql_query("INSERT INTO lead_re_assign(enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, lead_type, remark, change_status, added_by) VALUES('$enquiry_id','area_sales_manager','0','" . $asm_user->userId . "','" . $lead_data->disposition_status_id . "','" . $lead_data->disposition_sub_status_id . "','assign','lead assign to asm','pending','$user')");

                foreach ($meeting_project_ids as $p_id) {
                    $update_remaining_capacity = 'UPDATE capacity_master SET'
                            . ' remaining_capacity = remaining_capacity - 1 '
                            . ' WHERE userId = ' . $asm_user->userId . ' AND pId= ' . $p_id . ' AND capacity_month = ' . $current_month . ' AND capacity_year = "' . $current_year . '" LIMIT 1';
                    mysql_query($update_remaining_capacity);
                }

                $assignee_name = getEmployeeName($user);
                $history_text = 'Lead has been assigned to TM ' . $asm_name . ' from CRM ' . $lead_owner . ' at ' . date('d/m/Y H:i A');
                $details = '<p>Lead Details - <br/>Enquiry ID - ' . $enquiry_id;
                $history_text .= $details;
                $lead_number = getLeadNumber($enquiry_id);

                // TM assingment log
                $assignment_history = array(
                    'enquiry_id' => $enquiry_id,
                    'lead_number' => $lead_number,
                    'details' => $history_text,
                    'type' => 'new'
                );

                createLog($assignment_history);

                sleep(1);

                /*                 * ************************************************************* */
                /* SEND MAIL TO ASM OF LEAD ASSIGNMENT
                  /*************************************************************** */

                $get_email_template = 'SELECT * FROM `email_templates` WHERE email_category = "internal" AND event = "lead_assignment_level_2" LIMIT 1';

                $email_template_resource = mysql_query($get_email_template);

                if ($email_template_resource && mysql_num_rows($email_template_resource) > 0) {

                    $email_template_object = mysql_fetch_object($email_template_resource);

                    $address = '';
                    $scheduled_datetime = '';

                    if ($lead_meeting_id != '') {
                        $meeting_data = getLeadMeetingData($enquiry_id, $lead_meeting_id);
                        $project = json_decode($meeting_data['project'], true);
                        $address = $meeting_data['meeting_address'];
                        $scheduled_date = date('d-M-Y', $meeting_data['meeting_timestamp'] / 1000);
                        $scheduled_time = date('H:i A', $meeting_data['meeting_timestamp'] / 1000);
                    } else if ($lead_site_visit_id != '') {
                        $site_visit_data = getSiteVisitDataById($lead_site_visit_id);
                        $project = json_decode($site_visit_data['project'], true);
                        $address = $site_visit_data['site_location'];
                        $scheduled_date = date('d-M-Y', $site_visit_data['site_visit_timestamp'] / 1000);
                        $scheduled_time = date('H:i A', $site_visit_data['site_visit_timestamp'] / 1000);
                    }

                    $project_name = '';
                    $project_city = '';

                    // Iterating projects
                    // Assuming there is only single project					
                    foreach ($project as $key => $val) {
                        $project_city = $val['project_city'];
                        $project_name = $val['project_name'];
                    }

                    $mail_keywords = array(
                        '{{enquiry_id}}',
                        '{{status}}',
                        '{{event_date}}',
                        '{{event_time}}',
                        '{{lead_owner}}',
                        '{{client_name}}',
                        '{{client_mobile_number}}',
                        '{{client_alternate_number}}',
                        '{{profession}}',
                        '{{client_address}}',
                        '{{project_name}}',
                        '{{sales_person}}',
                        '{{sales_manager}}'
                    );

                    $keyword_replacement_values = array(
                        $enquiry_id,
                        $lead_current_status . ' ' . $lead_current_sub_status,
                        $scheduled_date,
                        $scheduled_time,
                        $lead_owner,
                        $client_name,
                        $client_mobile,
                        $client_alternate_number,
                        $client_profession,
                        $client_address,
                        $project_name,
                        $sales_person,
                        $asm_name
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
                        TO => $asm_email,
                        CC => 'abhishek.agrawal@bookmyhouse.com',
                        BCC => '', // add if any 
                        SUBJECT => $email_template_object->subject,
                        TO_NAME => $asm_name
                    );

                    sendMailData($mail_data, $enquiry_id);

                    /*                     * ************************************************************** */
                    /* SEND SMS TO ASM OF LEAD ASSIGNMENT    
                      /**************************************************************** */

                    $sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "internal" AND event = "lead_assign_to_asm" LIMIT 1');

                    if ($sms_template && mysql_num_rows($sms_template) > 0) {

                        $sms_template_object = mysql_fetch_object($sms_template);

                        $sms_keywords = array(
                            '{{enquiry_id}}',
                            '{{status}}',
                            '{{event_date}}',
                            '{{event_time}}',
                            '{{lead_owner}}',
                            '{{client_name}}',
                            '{{client_mobile_number}}',
                            '{{client_alternate_number}}',
                            '{{profession}}',
                            '{{client_address}}',
                            '{{project_name}}',
                            '{{sales_person}}',
                            '{{sales_manager}}'
                        );

                        $sms_keyword_values = array(
                            $enquiry_id,
                            $lead_current_status . ' ' . $lead_current_sub_status,
                            $scheduled_date,
                            $scheduled_time,
                            $lead_owner,
                            $client_name,
                            $client_mobile,
                            $client_alternate_number,
                            $client_profession,
                            $client_address,
                            $project_name,
                            '',
                            $asm_name
                        );

                        $sms_body = str_replace($sms_keywords, $sms_keyword_values, $sms_template_object->message);

                        $sms_receiver_numbers = array();

                        if ($sms_template_object->default_numbers != '') {
                            // create an array of numbers 
                            $sms_receiver_numbers = explode(',', $sms_template_object->default_numbers);
                        }

                        array_push($sms_receiver_numbers, $asm_number);
                        array_push($sms_receiver_numbers, 7838104984);

                        sendSMS($sms_receiver_numbers, $sms_body);
                    } // end of sms template
                } // End of email template 
                // Apply Round Robin lead Assignment Process to sales person in manual lead assignment process of ASM also  

                $request_url = BASE_URL . 'apis/round_robin_assignment.php';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $request_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('enquiry_ids' => serialize(array($enquiry_id)), 'asm_id' => $asm_user->userId));
                curl_exec($ch);
                curl_close($ch);

                // Toast message
                $response = array(
                    'success' => 1,
                    'message' => 'Lead has been successfully assigned to TM ' . ucfirst($asm_name) . '',
                    'title' => 'Lead Assignment'
                );
//				echo json_encode($response, true); exit;
            } else {
                $error_response = array(
                    'success' => 0,
                    'message' => 'Lead could not be assigned to TM. Please try again later.',
                    'title' => 'Server Error'
                );
//			echo json_encode($error_response,true); exit;
            }
        } else {

//			$response = array(
//							'success' => 0, 
//							'message' => 'Lead could not be assigned as no TM found to assign lead for selected project',
//							'title' => 'TM not found'
//						);
//			echo json_encode($response,true); exit;
        }
    }

    if (strtolower($lead_category) === 'mpl') {

        // Get the area sales manager and remaining capacity of MPL category
        // Query to Random selection of asm
        // $asm = 'SELECT emp.id as user_id, concat(emp.firstname ," ", emp.lastname) as username
        // -- 		FROM employees as emp
        // -- 		LEFT JOIN mpl_capacity as mpl ON (emp.id = mpl.user_id)
        // -- 		WHERE emp.designation = (SELECT id from designationmaster where designation_slug = "area_sales_manager")
        // -- 		AND mpl.capacity IS NOT NULL AND mpl.remaining_capacity > 0 ORDER  BY rand() LIMIT 1';
        // Query to forcely select asm by asm id 
        $asm = 'SELECT emp.id as user_id, concat(emp.firstname ," ", emp.lastname) as username,emp.email
				FROM employees as emp
				LEFT JOIN mpl_capacity as mpl ON (emp.id = mpl.user_id)
				WHERE emp.id = ' . $asm_id . '
				AND mpl.capacity IS NOT NULL AND mpl.remaining_capacity > 0 ORDER  BY rand() LIMIT 1';

        $asm_result = mysql_query($asm);

        if ($asm_result && mysql_num_rows($asm_result) > 0) {

            $asm_user = mysql_fetch_object($asm_result);
            $asm_id = $asm_user->user_id;
            $asm_name = $asm_user->username;
            $asm_email = $asm_user->email;

            $sales_person = '';

            // Assigning lead to TM 
            $assign_lead_to_asm = 'UPDATE `lead` '
                    . ' SET lead_assigned_to_asm = ' . $asm_id . ','
                    . ' lead_assigned_to_asm_on = "' . $current_date . '"'
                    . ' WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';


            if (mysql_query($assign_lead_to_asm)) {

                // insert data into re-assign table as a log purpose (Umesh)

                mysql_query("INSERT INTO lead_re_assign(enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, lead_type, remark, change_status, added_by) VALUES('$enquiry_id','area_sales_manager','0','" . $asm_user->userId . "','" . $lead_data->disposition_status_id . "','" . $lead_data->disposition_sub_status_id . "','assign','lead assign to asm','pending','$user')");

                // updating TM capacity 
                $update_remaining_capacity = 'UPDATE mpl_capacity SET'
                        . ' remaining_capacity = remaining_capacity - 1 , edited_by = ' . $user . ''
                        . ' WHERE user_id = ' . $asm_id . ' LIMIT 1';
                mysql_query($update_remaining_capacity);

                // log history 
                $history_text = 'Multiple project lead has been assigned to Area Sales Manager ' . $asm_name . ' on ' . date('Y-m-d H:i:s');
                $details = '<p>Lead Details - <br/>Enquiry ID - ' . $enquiry_id . '</p>';
                $history_text .= $details;
                $lead_number = getLeadNumber($enquiry_id);

                createLog(array('details' => $history_text, 'type' => 'new', 'lead_number' => $lead_number, 'enquiry_id' => $enquiry_id));

                // Mail shooting
                $mp_lead_mail = mysql_query('SELECT * FROM email_templates WHERE email_category = "internal" AND event = "multiple_project_lead_assignment_level_2" LIMIT 1');

                if ($mp_lead_mail) {

                    $template = mysql_fetch_object($mp_lead_mail);

                    $client_info = getCLientInfoByEnquiry($enquiry_id);

                    if ($meeting_data) {
                        $scheduled_date = date('d-M-Y', $meeting_data['meeting_timestamp'] / 1000);
                        $scheduled_time = date('H:i A', $meeting_data['meeting_timestamp'] / 1000);
                    }

                    if ($site_visit_data) {
                        $scheduled_time = date('H:i A', $site_visit_data['site_visit_timestamp'] / 1000);
                        $scheduled_time = date('H:i A', $site_visit_data['site_visit_timestamp'] / 1000);
                    }

                    $search = array(
                        '{{enquiry_id}}',
                        '{{status}}',
                        '{{event_date}}',
                        '{{event_time}}',
                        '{{lead_owner}}',
                        '{{client_name}}',
                        '{{client_mobile_number}}',
                        '{{client_alternate_number}}',
                        '{{profession}}',
                        '{{client_address}}',
                        '{{projects_list}}',
                        '{{sales_person}}',
                        '{{sales_manager}}'
                    );

                    $replace = array(
                        $enquiry_id,
                        $lead_current_status . ' ' . $lead_current_sub_status,
                        $scheduled_date,
                        $scheduled_time,
                        $lead_owner,
                        $client_info['customerName'],
                        $client_info['customerMobile'],
                        $client_info['customer_alternate_mobile'],
                        $client_info['customerProfession'],
                        $client_info['customerAddress'],
                        implode(' , ', $meeting_project_list),
                        $asm_name,
                        $sales_person
                    );

                    $mail_body = str_replace($search, $replace, $template->message_body);

                    $default_to_users = '';
                    $default_cc_users = '';
                    $default_bcc_users = '';

                    if ($template->to_users != '') {
                        $default_to_users = $template->to_users;
                    }
                    if ($template->cc_users != '') {
                        $default_cc_users = $template->cc_users;
                    }
                    if ($template->bcc_users != '') {
                        $default_bcc_users = $template->bcc_users;
                    }

                    $mail_data = array(
                        MESSAGE => $mail_body,
                        DEFAULT_TO_USERS => $default_to_users,
                        DEFAULT_CC_USERS => $default_cc_users,
                        DEFAULT_BCC_USERS => $default_bcc_users,
                        TO => $asm_email,
                        CC => 'nitesh@bookmyhouse.com',
                        BCC => 'abhishek.agrawal@bookmyhouse.com', // add if any 
                        SUBJECT => $template->subject,
                        TO_NAME => $asm_name
                    );

                    sendMailData($mail_data, $enquiry_id);
                }

                // Success response 
                $response = array(
                    'success' => 1,
                    'message' => 'Lead has been successfully assigned to TM ' . ucfirst($asm_name),
                    'title' => 'Lead Assignment'
                );
//				echo json_encode($response, true); exit;
            }
        } else {

            // error response 
            $error_response = array('success' => 0, 'message' => 'Lead could not be assigned as no TM found for assignment', 'title' => 'Assignment Error');
//			echo json_encode($error_response,true); exit;
        }
    }
}
