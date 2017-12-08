<?php

require_once 'db_connection.php';

/**
 * General utility php functions
 */
function generateEnquiryID($range = null) {


    $enquiry_id = rand($range[0], $range[1]);


    // 	Strore generated enquiry id in db
    if (!mysql_query('INSERT INTO enquiry_ids_log (enquiry_id) VALUES (' . $enquiry_id . ')')) {

        generateEnquiryID(array(1, 100000));
    }

    return $enquiry_id;
}

function generateLeadNumber($enquiry_id = null) {


    $current_year = date('Y');

    $lead_number = rand(1, 100000);


    // 	Strore generated enquiry id in db
    if (!mysql_query('INSERT INTO lead_numbers_log (lead_id) VALUES (' . $lead_number . ')')) {

        generateLeadNumber(generateEnquiryID(array(1, 100000)));
    }


    return $current_year . '-' . $enquiry_id . '-' . $lead_number;
}

/*
 * get last enquiry number generated 
 */

function lastGeneratedEnquiryId() {


    $last_generated_enquiry_id = '';
}

/**
 * 
 */
function authenticateUser($id = null, $email = null) {


    if ($id === null || $email == null) {

        return 0;
    }


    $is_user_exists = 'SELECT id FROM `employees` WHERE id= ' . $id . ' AND email = "' . $email . '" LIMIT 1';


    $result = mysql_query($is_user_exists);


    if (mysql_num_rows($result) > 0) {


        return 1;
    } else {

        return 0;
    }
}

function unauthorizedResponse() {

    return json_encode(array('success' => 0, 'http_status_code' => 401, 'message' => 'Not Authorized'), true);
}

function getEnquiryStatusText($status_id = null) {


    if ($status_id === null) {

        return '';
    }

    $enquiry_status_text = 'SELECT `status_title` FROM `enquiry_status` WHERE id = ' . $status_id . ' LIMIT 1';


    $enquiry_resource = mysql_query($enquiry_status_text);


    $enquiry_text = '';


    if ($enquiry_resource) {


        if (mysql_num_rows($enquiry_resource) > 0) {


            $enquiry_text = mysql_fetch_row($enquiry_resource)[0];
        }
    }


    return $enquiry_text;
}

function getEmployeeByDesignationSlug($designation_slug = '', $designation_id = '') {

    $where = '';

    if ($designation_slug == '' && $designation_id == '') {
        return '';
    }

    if ($designation_id != '') {
        $where = ' WHERE id = ' . $designation_id;
    } else {
        $where = ' WHERE designation_slug = "' . $designation_slug . '"';
    }


    $select_employee = 'SELECT `id`, `designation` FROM `designationmaster` ' . $where . ' LIMIT 1';


    $result = mysql_query($select_employee);


    if ($result) {


        if (mysql_num_rows($result) > 0) {


            $row = mysql_fetch_object($result);

            return $row;
        }
    }
}

/**
 * Function to get sub modules 
 * @param <array> $sub_module_ids
 * @param integer|string $designation_id designation id of user 
 */
function getSubModules($sub_modules_ids = NULL, $designation_id = null) {


    if (empty($sub_modules_ids) || count($sub_modules_ids) <= 0) {


        return array();
    }


    $module_ids = implode(',', $sub_modules_ids);

    if ($designation_id == 2) {
        // 		here we skip "my leads" and "assigned leads" sub modules of "lead module" for admin	
        $child_modules = 'SELECT * FROM `crmmodules` WHERE id IN(' . $module_ids . ') AND ( (title NOT LIKE "%My Leads") AND (title NOT LIKE "%Assigned Leads") )';
        // $child_modules = 'SELECT * FROM `crmmodules` WHERE id IN('.$module_ids.')';
    } else {
        $child_modules = 'SELECT * FROM `crmmodules` WHERE id IN(' . $module_ids . ')';
    }


    $child_modules_result = mysql_query($child_modules);

    $child_modules_results_data = array();


    while ($row = mysql_fetch_assoc($child_modules_result)) {

        $temp_row = array();

        $temp_row['id'] = $row['id'];

        $temp_row['title'] = $row['title'];

        $temp_row['link'] = $row['link'];

        $temp_row['params'] = $row['params'];

        if ($designation_id == 2) {
            $temp_row['permission'] = 7;
        } else {
            $temp_row['permission'] = file_get_contents(BASE_URL . 'apis/helper.php?method=getModulePermission&params=module_id:' . $row['id'] . '/designation_id:' . $designation_id);
        }

        array_push($child_modules_results_data, $temp_row);
    }

    return $child_modules_results_data;
}

/**
 * Function to get assigned sub modules ids with designation 
 * @param integer $parent_id
 * @param integer $designation_id
 * @return array
 */
function getAssignedChildModules($parent_id = "", $designation_id = "") {



    $parent_all_modules = 'SELECT * FROM crmmodules WHERE `parent` = ' . $parent_id . '';


    $result = mysql_query($parent_all_modules);


    $child_module_data = array();


    $sub_modules_ids = array();


    $assigned_sub_modules_ids = array();


    while ($row = mysql_fetch_assoc($result)) {


        array_push($sub_modules_ids, $row['id']);
    }


    if ($designation_id != 2) {


        if (!empty($sub_modules_ids)) {


            $ids_string = implode(',', $sub_modules_ids);


            // 			Now we query in designation module master to get only assigned modules for given designation 
            $assigned = 'SELECT * FROM `designationmodulemaster` WHERE designationId = ' . $designation_id . ' AND ModuleId IN (' . $ids_string . ') ';


            $assigned_result = mysql_query($assigned);


            if (mysql_num_rows($assigned_result)) {


                while ($row = mysql_fetch_assoc($assigned_result)) {


                    array_push($assigned_sub_modules_ids, $row['ModuleId']);
                }


                $child_module_data = getSubModules($assigned_sub_modules_ids, $designation_id);
            }
        }
    } else {


        $child_module_data = $sub_modules_ids;
    }


    return $child_module_data;
}

/**
 * Function to get disposition status label 
 * @param int $status_id
 */
function getStatusLabel($status_id = NULL, $type = "parent") {


    if ($status_id == NULL) {
        return '';
    }

    $column_to_select = '';

    if ($type == 'parent') {

        $column_to_select = 'status_title';
    } else {

        $column_to_select = 'sub_status_title';
    }


    $select_status_title = 'SELECT ' . $column_to_select . ' as title ' . ' FROM `disposition_status_substatus_master` WHERE id = ' . $status_id . ' LIMIT 1 ';


    $result = mysql_query($select_status_title);


    if ($result) {


        if (mysql_num_rows($result) > 0) {


            $data = mysql_fetch_object($result);


            return $data->title;
        }
    }
}

/**
 * Function to get meeting data of lead 
 * @param int $enquiry_id
 */
function getLeadMeetingData($enquiry_id = '', $meeting_id = '') {


    if ($enquiry_id == '') {

        return array();
    }


    if ($meeting_id == '') {

        return array();
    }


    $select_meeting_data = 'SELECT lm.* '
            . ' FROM `lead_meeting` as lm '
            . ' WHERE lm.enquiry_id = ' . $enquiry_id . ' AND lm.meetingId = "' . $meeting_id . '" ORDER BY `id` DESC LIMIT 1';


    $meeting_result = mysql_query($select_meeting_data);


    if ($meeting_result) {


        if (mysql_num_rows($meeting_result) > 0) {


            $data = mysql_fetch_assoc($meeting_result);

            return $data;
        }
    }
}

/**
 * Function to get site visit data of lead
 * @param int $enquiry_id
 */
function getLeadSiteVisitData($enquiry_id = null, $site_visit_id = '') {


    if ($enquiry_id == NULL) {

        return array();
    }


    $select_visit_data = 'SELECT site_visit.* FROM `site_visit` WHERE enquiry_id = ' . $enquiry_id . ' AND site_visit_id = "' . $site_visit_id . '" ORDER BY `id` DESC LIMIT 1';


    $visit_result = mysql_query($select_visit_data);


    if ($visit_result) {


        if (mysql_num_rows($visit_result) > 0) {


            $data = mysql_fetch_assoc($visit_result);

            return ($data);
        }
    }
}

function getDesignationBySlug($designation_slug = '', $response = 'json') {

    if ($designation_slug == '') {
        return '';
    }

    $select_designation = 'SELECT `id`, `designation` FROM `designationmaster` WHERE designation_slug = "' . $designation_slug . '" LIMIT 1';

    $result = mysql_query($select_designation);

    if ($result) {

        $data = mysql_fetch_assoc($result);

        if ($response == 'json') {

            return json_encode($data, true);
            exit;
        } else {
            return $data;
            exit;
        }
    }
}

/**
 * Function to get Employee name by Employee ID 
 */
function getEmployeeName($employee_id = '') {

    if ($employee_id === '' || $employee_id === null || $employee_id == 'null') {
        return '';
    }
    
    $employee_name  = 'SELECT firstname, lastname FROM employees WHERE id = ' . $employee_id . ' LIMIT 1';

    $result         = mysql_query($employee_name);

    if ($result && mysql_num_rows($result) > 0) {
        $data_row = mysql_fetch_row($result);
        return $data_row[0] . ' ' . $data_row[1];
    }else{
        return '';
    }
}

/**
 * function to get multiple employee names
 * @param type $employee_id
 * @return array
 */
function getMultipleEmployeeName($employee_id = ''){
    
    if(!is_array($employee_id) || count($employee_id) < 0){
        return array();
    }
    
    $employees = array();
    foreach($employee_id as $id){
        
        $find_employee  = 'SELECT CONCAT(firstname," ",lastname) AS name FROM employees WHERE id = ' . $id . '';
        $result = mysql_query($find_employee);
        
        if($result && mysql_num_rows($result) > 0){
            
            $row = mysql_fetch_row($result);
            array_push($employees, $row[0]);
        }else{
            array_push($employees, '');
        }
    }
    
    return $employees;
}

/**
 * To get Employee data by role 
 */
function getEmployeeByRole($role_id) {


    if ($role_id == '') {

        return '';
    }


    $select_employee = 'SELECT '
            . '		id, firstname, lastname, email, designation '
            . '		FROM employees '
            . '		WHERE role = "' . $role_id . '" '
            . '		LIMIT 1';


    $result = mysql_query($select_employee);


    if ($result && mysql_num_rows($result) > 0) {


        return mysql_fetch_object($result);
    } else {

        return '';
    }
}

/** -----------------------------------------------------------
  Helper function to check email address exisit in DB or not
  --------------------------------------------------------- */
function getEmployeeByEmail($email_id = '') {


    if ($email_id === '' || $email_id === null) {

        return '';
    }


    $employee_name = 'SELECT id, CONCAT(firstname," ",lastname) AS employee_name FROM employees WHERE email = "' . $email_id . '" LIMIT 1';


    $result = mysql_query($employee_name);


    $employee_name = '';


    if ($result && mysql_num_rows($result) > 0) {


        $data_row = mysql_fetch_object($result);

        return $data_row->employee_name;
    } else {

        return '';
    }
}

// Get employee mobile number 

function getEmployeeMobileNumber($emp_id = '') {


    if ($emp_id === '' || $emp_id === null) {

        return '';
    }


    $get_mobile_number = 'SELECT contactNumber as mobile_number FROM employees WHERE id = ' . $emp_id . ' LIMIT 1';


    $result = mysql_query($get_mobile_number);


    if ($result && mysql_num_rows($result) > 0) {


        $data_row = mysql_fetch_object($result);

        return $data_row->mobile_number;
    } else {

        return '';
    }
}

/**
 * Get Lead Source Text
 */
function getCampaignText($campaign_id = '') {


    if ($campaign_id == '') {

        return '';
    }


    $sql = 'SELECT `title` FROM `campaign_master` WHERE id = ' . $campaign_id . ' LIMIT 1 ';


    $result = mysql_query($sql);


    if ($result && mysql_num_rows($result) > 0) {


        $source = mysql_fetch_object($result);


        return $source->title;
    } else {

        return '';
    }
}

/**
 * To get total notes counts for a single enquiry id
 */
function getNotesCount($enquiry_id = '') {


    if ($enquiry_id == '') {

        return 0;
    }


    $select_notes = 'SELECT COUNT(id) as total_notes FROM notes WHERE enquiry_id = ' . $enquiry_id . '';


    $result = mysql_query($select_notes);


    if ($result && mysql_num_rows($result) > 0) {


        $total_notes = mysql_fetch_object($result);


        return $total_notes->total_notes;
    } else {

        return 0;
        exit;
    }
}

/**
 * Function to get lead Number 
 * @param integer $enquiry_id
 * @return string
 */
function getLeadNumber($enquiry_id = '') {


    if ($enquiry_id == '') {
        return 'NULL';
    }


    $select_lead_number = 'SELECT lead_id FROM `lead` WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';

    $result = mysql_query($select_lead_number);

    if ($result && mysql_num_rows($result) > 0) {

        $data = mysql_fetch_object($result);

        if ($data->lead_id != 'NULL' || $data->lead_id != null) {

            return $data->lead_id;
        } else {

            return 'NULL';
        }
    } else {

        return 'NULL';
    }
}

/**
 * Function to get client information by enquiryId
 * @param integer $enquiry_id
 */
function getCLientInfoByEnquiry($enquiry_id = '') {

    if ($enquiry_id == '') {
        return (array());
    }

    $select_client = 'SELECT 
		customerEmail, customerMobile, customerName, customerProfession, customerCity,customerCityId, customerState,customerStateId, customerAddress, customerDOB, customer_alternate_mobile,customerLandLine, customer_gender, customerCountry, customer_reference_number, customerRemark'
            . ' FROM `lead` '
            . ' WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';

    $result = mysql_query($select_client);


    if ($result && mysql_num_rows($result) > 0) {


        $client_object = mysql_fetch_assoc($result);

        return($client_object);
        // 		array
    } else {

        return(array());
    }
}

/**
 * Function to get client information by phone no.
 * @param integer $phone_no
 */
function getCLientInfoByPhone($phone_no = '') {


    if ($phone_no == '') {

        return (array());
    }


    $select_client = 'SELECT enquiry_id, lead_id , customerEmail, customerName, customerProfession, customerCity, customerState, customerAddress, customerDOB'
            . ' FROM `lead` '
            . ' WHERE customerMobile = "' . $phone_no . '" LIMIT 1';


    $result = mysql_query($select_client);


    if ($result && mysql_num_rows($result) > 0) {


        $client_object = mysql_fetch_assoc($result);

        return($client_object);
    } else {

        return(array());
    }
}

function getReportingPersons($user_id = '') {


    if ($user_id === '') {

        return array();
    }


    $select_reporting = 'SELECT id FROM employees WHERE reportingTo = ' . $user_id . '';


    $result = mysql_query($select_reporting);


    $reportings = array();


    if ($result && mysql_num_rows($result) > 0) {


        while ($rows = mysql_fetch_assoc($result)) {


            array_push($reportings, $rows['id']);
        }


        return $reportings;
    } else {

        return array();
    }
}

/**
 * Function to get hierarchy of user
 * @param type $designation_id
 * @return string
 */
function getUsersHierarchy($user_id = NULL, $level = 0) {


    if ($user_id === '') {

        return json_encode(array(), true);
        exit;
    }

    $user_ids = array();


    if ($level == 0) {

        array_push($user_ids, $user_id);
    }


    if ($level == 1) {


        array_push($user_ids, $user_id);


        $one_level_reportings = getReportingPersons($user_id);


        if (count($one_level_reportings) > 0) {


            foreach ($one_level_reportings as $user) {


                array_push($user_ids, $user);
            }
        }
    }


    if ($level == 2) {


        array_push($user_ids, $user_id);


        $one_level_users = getReportingPersons($user_id);


        if (count($one_level_users) > 1) {


            foreach ($one_level_users as $user) {


                array_push($user_ids, $user);


                $second_level_users = getReportingPersons($user);


                if (!empty($second_level_users)) {


                    if (count($second_level_users) > 0) {

                        foreach ($second_level_users as $user) {

                            array_push($user_ids, $user);

                            $third_level_users = getReportingPersons($user);


                            if (count($third_level_users) > 0) {

                                foreach ($third_level_users as $user) {

                                    array_push($user_ids, $user);
                                }
                            }
                        }
                    }
                }
            }
        } else {


            array_push($user_ids, $one_level_users[0]);


            $second_level_users = getReportingPersons($one_level_users[0]);


            foreach ($second_level_users as $user) {


                array_push($user_ids, $user);


                $third_level_users = getReportingPersons($user);


                if (count($third_level_users) > 0) {


                    foreach ($third_level_users as $user) {

                        array_push($user_ids, $user);
                    }
                }
            }
        }
    }


    return $user_ids;
}

/**
 * Function to get Number on which OTP will be sent 
 * @param type $request_id
 * @return string
 */
function getNumberToResendOTP($request_id = '') {


    if ($request_id == '') {

        return '';
    }


    $get_otp_number = 'SELECT `otp_sent_on_number` FROM reset_password WHERE id = ' . $request_id . ' LIMIT 1';


    $result = mysql_query($get_otp_number);


    if ($result && mysql_num_rows($result) > 0) {


        return mysql_fetch_object($result)->otp_sent_on_number;
    } else {

        return '';
    }
}

function confirmRequestID($req_id = '') {


    if ($req_id === '') {

        return false;
    }


    $confirm_request = 'SELECT id FROM reset_password WHERE id = ' . $req_id . ' LIMIT 1';


    $result = mysql_query($confirm_request);


    $rows = mysql_num_rows($result);


    if ($result && $rows > 0) {

        return true;
    } else {

        return FALSE;
    }
}

/*
 * Function: Random string generator 
 */

function generateRandomString($length = 10) {

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*';

    $charactersLength = strlen($characters);

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {

        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

/**
 * 
 */
function getEmployeeEmailAddress($employee_id = '') {


    if ($employee_id === '') {

        return '';
    }


    $get_email = 'SELECT email FROM employees WHERE id = ' . $employee_id . ' LIMIT 1';


    $result = mysql_query($get_email);


    $rows = mysql_num_rows($result);


    if ($result && $rows > 0) {


        return mysql_fetch_object($result)->email;
    } else {

        return '';
    }
}

/**
 * Function to get employee designation 
 */
function getEmployeeDesignation($employee_id = '') {


    if ($employee_id == '') {

        return '';
    }


    $query = 'SELECT tbl2.designation, tbl2.designation_slug FROM employees as tbl1 '
            . ' LEFT JOIN designationmaster as tbl2 ON (tbl1.designation = tbl2.id)'
            . ' WHERE tbl1.id = ' . $employee_id . ' LIMIT 1';


    $res = mysql_query($query);


    if ($res && mysql_num_rows($res) > 0) {


        $data = mysql_fetch_row($res);

        return $data;
    } else {

        return '';
    }
}

// Function to get reporting employees 
function getDirectReportings($employee_id = '') {


    if ($employee_id == '') {

        return array();
    }


    $query = 'SELECT id FROM employees WHERE reportingTo = ' . $employee_id . '';


    $result = mysql_query($query);


    $direct_reportings = array();


    if ($result && mysql_num_rows($result) > 0) {


        while ($row = mysql_fetch_assoc($result)) {

            array_push($direct_reportings, $row['id']);
        }
    }


    return $direct_reportings;
    // 	array of user ids 
}

/*
  Helper function to fetch email template id by category and template event
 */

function getEmailTemplateId($category = '', $template_event = '') {


    if ($category === '' || $template_event === '') {

        return 0;
    }


    $query = 'SELECT template_id FROM email_templates WHERE email_category = "' . $category . '" AND event = "' . $template_event . '" LIMIT 1';


    $result = mysql_query($query);


    if ($result && mysql_num_rows($result) > 0) {


        $template_id = mysql_fetch_object($result);


        return $template_id->template_id;
    } else {

        return 0;
    }
}

/**
  Helper function to send meeting reminder mail
 */
function sendMeetingReminder($enquiry_id = '') {

    // 	send cURL request to mail sender API
    $curl_url = BASE_URL . 'apis/send_meeting_reminder_mail_to_client.php';
    $curl = curl_init($curl_url);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id)
    ));

    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

function sendSimpleReminderMail($enquiry_id = '', $hotProjects = '') {

    $base_64_encoded_hot_projects = '';

    // base64 encode hot projects array
    if (!empty($hotProjects)) {
        $base_64_encoded_hot_projects = base64_encode(serialize($hotProjects));
    }

    $curl_url = BASE_URL . 'apis/send_simple_reminder_mail_to_client.php';

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id, 'hot_projects' => $base_64_encoded_hot_projects)
    ));


    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function sendCallBackMailReminder($enquiry_id = '') {


    $curl_url = BASE_URL . 'apis/send_callback_reminder_mail_to_client.php';

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id)
    ));


    $result = curl_exec($curl);

    curl_close($curl);


    return $result;
}

/*
  Helper function to send site visit reminder
 */

function sendSiteVisitReminder($enquiry_id = '') {


    $curl_url = BASE_URL . 'apis/send_site_visit_reminder_mail_to_client.php';

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id)
    ));


    $result = curl_exec($curl);

    curl_close($curl);


    return $result;
}

/**
 * Function to fetch all employees of given designation 
 * @param type $designation_slug
 */
function getEmployeeByDesignation($designation_slug = '') {

    if ($designation_slug == '') {
        return array();
    }

    $designation = getEmployeeByDesignationSlug($designation_slug);

    $users = array();

    if (is_object($designation)) {

        $query = 'SELECT id, firstname, lastname, email, contactNumber FROM employees 
        WHERE designation = ' . $designation->id . ' AND isDelete = 0';

        $result = mysql_query($query);

        if ($result && mysql_num_rows($result) > 0) {

            while ($row = mysql_fetch_assoc($result)) {
                array_push($users, $row);
            }
        }
    }

    return $users;
}

/**
 * Function to create meeting ID
 * @return string
 */
function createMeetingID() {

    $meeting_id = 'M' . time();

    return $meeting_id;
}

/**
  Helper function to create a new site visit id
 */
function createSiteVisitID() {

    $site_visit_id = 'S' . time();

    return $site_visit_id;
}

/**
 * Helper function to get current enquiry status 
 */
function getCurrentEnquiryStatus($enquiry_id = '') {


    if ($enquiry_id === '') {

        return array();
    }


    $query = 'SELECT disposition_status_id, disposition_sub_status_id FROM lead WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';


    $result = mysql_query($query);


    if ($result && mysql_num_rows($result) > 0) {


        $status_data = mysql_fetch_assoc($result);


        return array(
            'primary_status_id' => $status_data['disposition_status_id'],
            'primary_status_title' => getStatusLabel($status_data['disposition_status_id'], 'parent'),
            'secondary_status_id' => $status_data['disposition_sub_status_id'],
            'secondary_status_title' => getStatusLabel($status_data['disposition_sub_status_id'], 'child')
        );
    } else {

        return array();
    }
}

/**
  Function to get ASM capacity of running month
 */
function getASMCurrentMonthCapacity($asm_id = '') {


    if ($asm_id == '') {

        return 0;
    }


    $current_month = date('m') - 1;

    $current_year = date('Y');


    $query = 'SELECT SUM(capacity) as capacity FROM capacity_master WHERE userId = ' . $asm_id . ' AND capacity_month = ' . $current_month . ' AND capacity_year = "' . $current_year . '"';


    $result = mysql_query($query);


    $rows = mysql_num_rows($result);


    if ($result && $rows > 0) {


        $data = mysql_fetch_object($result);


        return $data->capacity;
    } else {

        return 0;
    }
}

/**
  Function to create log
 */
function createLog($log_data = '') {


    // 	create log in history table

    if (count($log_data) > 0) {


        $query = 'INSERT INTO `lead_history` SET ';


        if (is_array($log_data)) {

            foreach ($log_data as $col => $val) {


                $query .= $col . ' = "' . $val . '" , ';
            }
        }


        // 		trim query string any space or comma

        $query = trim(rtrim($query, ' ,'));

        mysql_query($query);

        return $query;
    }
}

/**
  Function to get Site Visit Data by site visit Id
 */
function getSiteVisitDataById($site_visit_id = null) {


    if ($site_visit_id == NULL) {

        return array();
    }


    $select_visit_data = 'SELECT site_visit.* FROM `site_visit` WHERE site_visit_id = "' . $site_visit_id . '" ORDER BY `id` DESC LIMIT 1';


    $visit_result = mysql_query($select_visit_data);


    if ($visit_result) {


        if (mysql_num_rows($visit_result) > 0) {


            $data = mysql_fetch_assoc($visit_result);

            return ($data);
        }
    }
}

// Helper function to get event time
function getEventTime($event = '', $event_id = '') {


    switch ($event) {


        case 'meeting':

            $query = 'SELECT meeting_timestamp FROM lead_meeting WHERE meetingId = "' . $event_id . '" LIMIT 1';


            $result = mysql_query($query);


            if ($result && mysql_num_rows($result) > 0) {


                $data = mysql_fetch_object($result);


                return date('Y-m-d H:i:s', $data->meeting_timestamp / 1000);
            } else {

                return '';
            }


            break;


        case 'site_visit':

            $query = 'SELECT site_visit_timestamp FROM site_visit WHERE site_visit_id = "' . $event_id . '" LIMIT 1';


            $result = mysql_query($query);


            if ($result && mysql_num_rows($result) > 0) {


                $data = mysql_fetch_object($result);


                return date('Y-m-d H:i:s', $data->site_visit_timestamp / 1000);
            } else {

                return '';
            }

            break;
    }
}

// Function to get Sr Team Leader designation user
function getTLCRMName() {


    $sr_tl_designation = 'SELECT id FROM designationmaster WHERE designation_slug = "sr_team_leader" LIMIT 1';


    $result = mysql_query($sr_tl_designation);


    $sr_tl_name = '';


    if ($result && mysql_num_rows($result) > 0) {


        $sr_tl_designation_resource = mysql_fetch_object($result);


        $employee = mysql_query('SELECT CONCAT(firstname," ",lastname) as employee_name
         FROM employees
         WHERE designation = ' . $sr_tl_designation_resource->id . ' LIMIT 1');


        if ($employee && mysql_num_rows($employee) > 0) {


            $employee_data = mysql_fetch_object($employee);


            return $employee_data->employee_name;
        }
    }
}

/**
  /** Function to call internal reminder mail script
 * */
function sendInternalReminderMail($script = '', $enquiry_id = '') {


    if ($script == '') {

        return '';
    }


    if ($enquiry_id == '') {

        return '';
    }


    $curl_url = BASE_URL . 'apis/' . $script;

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id)
    ));


    $result = curl_exec($curl);

    curl_close($curl);
}

function isLeadAssigned($enquiry_id = '') {


    if ($enquiry_id == '') {

        return array();
    }


    $query = 'SELECT lead_assigned_to_asm, lead_assigned_to_sp FROM lead WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';


    $result = mysql_query($query);


    if ($result && mysql_num_rows($result) > 0) {


        $data = mysql_fetch_object($result);


        return array(
            'asm' => $data->lead_assigned_to_asm,
            'sp' => $data->lead_assigned_to_sp
        );
    }
}

// Function to get lead data
function getLead($enquiry_id = '') {


    $get_lead = 'SELECT * FROM lead WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';

    $lead = array();

    $result = mysql_query($get_lead);

    if ($result && mysql_num_rows($result) > 0) {


        $lead = mysql_fetch_assoc($result);
    }


    return $lead;
}

function sendLeadAssginementMailToTLCRM($data = array()) {


    $curl_url = BASE_URL . 'apis/send_lead_assignment_mail_to_tl_crm.php';

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $data
    ));


    $result = curl_exec($curl);

    curl_close($curl);

    return true;
}

// Lead closure send function to client
function sendLeadClosureEmail($enquiry_id = '') {

    $curl_url = BASE_URL . 'apis/send_lead_closure_mail_to_client.php';

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id)
    ));


    $result = curl_exec($curl);

    curl_close($curl);

    return true;
}

// Function to get employee manager 

function getEmployeeManager($emp_id = '') {


    if ($emp_id == '') {

        return '';
    }


    $employee_manager = mysql_query('SELECT CONCAT(emp2.firstname," ", emp2.lastname) as manager_name, emp2.email as manager_email, emp2.contactNumber as manager_number 
FROM `employees` as emp1 
LEFT JOIN employees as emp2 ON (emp1.reportingTo = emp2.id)
WHERE emp1.id = ' . $emp_id . ' LIMIT 1');


    if ($employee_manager && mysql_num_rows($employee_manager) > 0) {


        $data = mysql_fetch_assoc($employee_manager);


        return $data;
    }
}

// Send Follow up internal mail to agent
function sendFollowupReminder($enquiry_id) {


    $curl_url = BASE_URL . 'apis/send_followup_reminder_mail_internal.php';

    $curl = curl_init($curl_url);

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('enquiry_id' => $enquiry_id)
    ));


    $result = curl_exec($curl);

    curl_close($curl);


    return $result;
}

// Function to validate site visit booking time
function validateSiteBookingVisitTime($callback_date, $callback_time) {


    $form_errors = array();


    $site_visit_timestamp = strtotime($callback_date . ' ' . str_replace(array(' ', 'AM', 'PM'), '', $callback_time));


    $site_visit_timestamp_year = date('Y', $site_visit_timestamp);


    $site_visit_timestamp_month = date('m', $site_visit_timestamp);


    if ($site_visit_timestamp_year > date('Y', time())) {


        $site_visit_hours = date('H', $site_visit_timestamp);


        if ($site_visit_hours < 7) {

            $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
        } else if ($site_visit_hours > 17) {

            $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
        } else {


            if ($site_visit_hours == 17) {


                $site_visit_minutes = date('i', $site_visit_timestamp);


                if ($site_visit_minutes > 30) {

                    $form_errors['site_visit'] = 'Site visit can not be book for time after 5:30 PM';
                }
            }
        }
    } else if ($site_visit_timestamp_year < date('Y', time())) {

        $form_errors['site_visit'] = 'Site Visit is not allowed for back date';
    } else if ($site_visit_timestamp_year == date('Y', time())) {


        if ($site_visit_timestamp_month > date('m', time())) {


            $site_visit_hours = date('H', $site_visit_timestamp);


            if ($site_visit_hours < 7) {

                $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
            } else if ($site_visit_hours > 17) {

                $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
            } else {


                if ($site_visit_hours == 17) {


                    $site_visit_minutes = date('i', $site_visit_timestamp);


                    if ($site_visit_minutes > 30) {

                        $form_errors['site_visit'] = 'Site visit can not be book for time after 5:30 PM';
                    }
                }
            }
        } else if ($site_visit_timestamp_month < date('m', time())) {


            $form_errors['site_visit'] = 'Site Visit is not allowed for back date';
        } else {


            // 			If previous date  
            if (date('d', time()) > date('d', $site_visit_timestamp)) {

                $form_errors['site_visit'] = 'Site Visit is not allowed for back date';
            } else if (date('d', time()) == date('d', $site_visit_timestamp)) {


                // 				case when site visit date is today

                $site_visit_hours = date('H', $site_visit_timestamp);


                if ($site_visit_hours < 7) {


                    $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
                } else if ($site_visit_hours > 17) {


                    $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
                } else {


                    if ($site_visit_hours == 17) {


                        $site_visit_minutes = date('H', $site_visit_timestamp);


                        if ($site_visit_minutes > 30) {

                            $form_errors['site_visit'] = 'Site visit can not be book for time after 5:30 PM';
                        }
                    } else {


                        $time_diff = $site_visit_timestamp - time();

                        if (round(($time_diff) / 60, 2) < 90) {

                            $form_errors['site_visit'] = 'Site visit can be set 90 minutes later from now or choose next day between 7 AM to 5:30 PM.';
                        }
                    }
                }
            }

            // 			case for date greater than current 
            else {

                $site_visit_hours = date('H', $site_visit_timestamp);


                if ($site_visit_hours < 7) {

                    $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
                } else if ($site_visit_hours > 17) {

                    $form_errors['site_visit'] = 'Site visit time is between 7:00 AM to 5:00 PM';
                } else {


                    if ($site_visit_hours == 17) {


                        $site_visit_minutes = date('i', $site_visit_timestamp);


                        if ($site_visit_minutes > 30) {

                            $form_errors['site_visit'] = 'Site visit can not be book for time after 5:30 PM';
                        }
                    }
                }
            }
        }
    }


    return $form_errors;
}

// Function to log remarks for an enquiry
function createRemarkLog($data = '') {


    if (is_array($data)) {


        $query = 'INSERT INTO `enquiry_remarks_log` SET ';


        foreach ($data as $col => $val) {

            $query .= $col . ' = "' . $val . '" , ';
        }


        // 		trim query string any space or comma

        $query = trim(rtrim($query, ' ,'));

        mysql_query($query);
    }
}

function array_utf8_encode($dat) {

    if (is_string($dat))
        return utf8_encode($dat);

    if (!is_array($dat))
        return $dat;

    $ret = array();

    foreach ($dat as $i => $d)
        $ret[$i] = array_utf8_encode($d);

    return $ret;
}

// Function to return project city by project id
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

function callCURL($url = '', $data = '') {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => BASE_URL . 'apis/' . $url,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $data
    ));

    $resp = curl_exec($curl);

    curl_close($curl);
}

function getDispositionStatusSlug($id) {

    if ($id == '') {
        return '';
    }

    $get_slug = mysql_query('SELECT status_slug FROM disposition_status_substatus_master WHERE id = ' . $id . ' LIMIT 1');

    if ($get_slug && mysql_num_rows($get_slug) > 0) {

        $slug_data = mysql_fetch_assoc($get_slug);

        return $slug_data['status_slug'];
    }
}

function getCallbackCounter($enquiry_id = '') {

    if ($enquiry_id == '') {
        return '';
    }

    $get_callback_counter = mysql_query('SELECT callback_counter FROM `lead` WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');

    if ($get_callback_counter && mysql_num_rows($get_callback_counter) > 0) {

        // return json string 
        $data = mysql_fetch_assoc($get_callback_counter);

        return $data['callback_counter'];
    }
}

function getFollowupCounter($enquiry_id = '') {

    if ($enquiry_id == '') {
        return '';
    }

    $get_followup_counter = mysql_query('SELECT followup_counter FROM `lead` WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');

    if ($get_followup_counter && mysql_num_rows($get_followup_counter) > 0) {

        // return json string 
        $data = mysql_fetch_assoc($get_followup_counter);

        return $data['followup_counter'];
    }
}

// Function to get reporting manager of an employee 
function getReportingManager($user_id = '') {

    if ($user_id === '') {
        return array();
    }

    $select_reporting = 'SELECT reportingTo FROM employees WHERE id = ' . $user_id . '';

    $result = mysql_query($select_reporting);

    if ($result && mysql_num_rows($result) > 0) {

        $data = mysql_fetch_object($result);

        return $data->reportingTo;
    } else {
        return '';
    }
}

// Get user current month capacity 

function getUserCurrentMonthCapacities($user_id = '') {

    if ($user_id == '') {
        return array();
    }

    $current_month = date('m') - 1;
    $current_year = date('Y');

    $query = mysql_query('SELECT SUM(capacity) as capacity, SUM(remaining_capacity) as remaining_capacity FROM capacity_master WHERE userId = ' . $user_id . ' AND capacity_month = "' . $current_month . '" AND capacity_year = "' . $current_year . '" LIMIT 1');

    if ($query) {

        $capacity_data = mysql_fetch_assoc($query);

        return array('capacity' => $capacity_data['capacity'], 'remaining_capacity' => $capacity_data['remaining_capacity']);
    } else {
        return array();
    }
}

function isActivityStatusAttached($id = '') {

    if ($id == '') {
        return '';
    }

    $query = 'SELECT `is_activity_status` FROM disposition_status_substatus_master WHERE id = ' . $id . ' LIMIT 1';
    $result = mysql_query($query);
    if ($result) {

        $data = mysql_fetch_assoc($result);
        return $data['is_activity_status'];
    }
}

// Function to get current Assigned CRM

function currentAssignedCRM($enquiry_id = '') {

    if ($enquiry_id == '') {
        return '';
    }
    
    $query = mysql_query('SELECT lead_added_by_user, reassign_user_id '
            . ' FROM lead '
            . ' WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');

    $result         = '';
    $current_crm    = '';

    if ($query) {

        $result         = mysql_fetch_object($query);

        $current_crm    = getEmployeeName($result->lead_added_by_user);

        if ($result->reassign_user_id != 0) {
            $current_crm = getEmployeeName($result->reassign_user_id);
        }
    }

    return $current_crm;
}

// Employee Details 
function getEmployeeDetails($emp_id = '') {


    $employee = mysql_query('SELECT * FROM employees WHERE id = ' . $emp_id . ' LIMIT 1');

    if ($employee && mysql_num_rows($employee) > 0) {

        $data = mysql_fetch_assoc($employee);
        return $data;
    }

    return array();
}
