<?php

session_start();

require 'function.php';

$_post_data = filter_input_array(INPUT_POST);

if (isset($_post_data)) {

//	Array
//(
//    [date] => Mon Feb 06 2017 11:01:54 GMT+0530 (India Standard Time)
//    [remark] => 
//    [status_id] => 7
//    [sub_status_id] => 33
//)

    $errors = [];
    $close_lead = array();

    if (isset($_post_data['status_id']) && $_post_data['status_id'] != '') {
        $close_lead['disposition_status_id'] = $_post_data['status_id'];
    } else {
        $errors[] = 'Undefined Status.';
    }

    if (isset($_post_data['sub_status_id']) && $_post_data['sub_status_id'] != '') {
        $close_lead['disposition_sub_status_id'] = $_post_data['sub_status_id'];
    } else {
        $errors[] = 'Undefined Sub Status.';
    }

    if (isset($_post_data['date']) && $_post_data['date'] != '') {
        $close_lead['lead_closure_date'] = date('Y-m-d H:i:s', strtotime($_post_data['date']));
    } else {
        $errors[] = 'Please set lead closing date';
    }

    if (isset($_post_data['remark']) && $_post_data['remark'] != '') {
        $close_lead['lead_closure_remark'] = $_post_data['remark'];
    }

    $employee_id = '';
    if (isset($_post_data['user_id']) && $_post_data['user_id'] != '') {
        $employee_id = $_post_data['user_id'];
    } else {
        $errors[] = 'User is not identified for this action. Please check your session and login again';
    }

    $employee_name = '';
    if (isset($_post_data['user_name']) && $_post_data['user_name'] != '') {
        $employee_name = $_post_data['user_name'];
    }

    // Employee Designation/Type
    $employee_type = '';

    $employee_designation = getEmployeeDesignation($employee_id);

    if (count($employee_designation) > 0) {
        $employee_type = $employee_designation[1];
    }

    $enquiry_id = '';

    if (isset($_post_data['enquiry_id']) && $_post_data['enquiry_id'] != '') {
        $enquiry_id = $_post_data['enquiry_id'];
    } else {
        $errors[] = 'Enquiry number is missing';
    }


    if (!empty($errors)) {

        $response = array(
            'success' => 0,
            'errors' => $errors
        );

        echo json_encode($response, true);
        exit;
    }

    // Updating lead table
    $query = 'UPDATE lead SET '
            . ' lead_updated_by = "' . $employee_id . '" , '
            . ' lead_closed_by = "' . $employee_id . '" , ';

    foreach ($close_lead as $col => $val) {

        $query .= $col . ' = "' . $val . '" , ';
    }

    $trimmed_query = rtrim($query, " , ");
    $trimmed_query .= ' WHERE enquiry_id = ' . $enquiry_id . '';

    if (mysql_query($trimmed_query)) {


        /**
         * Update in leadStatus table also
         */
        // Get Lead Activity status
        $lead_activity_status = mysql_query('SELECT leadStatus FROM lead WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');
        $lead_current_activity_status = '';
        if ($lead_activity_status) {

            $row = mysql_fetch_object($lead_activity_status);
            $lead_current_activity_status = $row->leadStatus;
        }


        // Update Status in [lead_status] table 
        $update_lead_status = mysql_query('INSERT INTO `lead_status` 
                SET enquiry_id = ' . $enquiry_id . ' , disposition_status_id=' . $_post_data['status_id'] . ' , disposition_sub_status_id= ' . $_post_data['sub_status_id'] . ',
                user_type = "' . $employee_type . '" , user_id = ' . $employee_id . ', hot_warm_cold_status = "' . $lead_current_activity_status . '"');


        // log histroy 
        $details = 'Lead/ Enquiry ' . $enquiry_id . ' has been closed by ' . $employee_name . ' at ' . date('d-M-Y H:i:s');

        $lead_number = getLeadNumber($enquiry_id);

        $leadCloseLog = array(
            'enquiry_id' => $enquiry_id,
            'lead_number' => $lead_number,
            'type' => 'edit',
            'details' => $details,
            'employee_id' => $employee_id
        );

        createLog($leadCloseLog);

        // Send mail of lead closing to users 


        $response = array(
            'success' => 1,
            'message' => 'Lead status has been updated successfully'
        );

        // LEAD CLOSURE MAIL TO CLIENT
        sendLeadClosureEmail($enquiry_id);

        echo json_encode($response, true);
        exit;
    } else {
        $errors[] = 'Server Error. Lead status could not be updated';
        $response = array(
            'success' => 0,
            'error' => $errors
        );
        echo json_encode($response, true);
        exit;
    }
} else {
    $errors[] = 'No data received';
    $response = array(
        'success' => 0,
        'error' => $errors
    );
    echo json_encode($response, true);
    exit;
}
