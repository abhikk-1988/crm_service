<?php

session_start();
require 'function.php';
require_once 'user_authentication.php';

if (!$is_authenticate) {
    echo unauthorizedResponse();
    exit;
}

function getEnquiryProjects($projects = '', $type = '') {

    $enquiry_projects = array();
    if ($projects && mysql_num_rows($projects) > 0) {

        if ($type == 'S' || $type == 'M') {

            $p_data = mysql_fetch_object($projects);

            if ($p_data->project != '') {

                // convert json string to array
                $project_json_to_array = json_decode($p_data->project, true);

                if (is_array($project_json_to_array) && !empty($project_json_to_array)) {

                    foreach ($project_json_to_array as $val) {
                        array_push($enquiry_projects, $val['project_name']);
                    }
                }
            } else {
                array_push($enquiry_projects, 'NA');
            }
        } else {
            while ($project = mysql_fetch_assoc($projects)) {
                array_push($enquiry_projects, $project['project_name']);
            }
        }
    } else {
        array_push($enquiry_projects, 'NA');
    }

    return $enquiry_projects;
}

$leads = array();

$data = filter_input_array(INPUT_POST);

$enquiry_filter = '';
$enquiry_filter_condition = '';
if (isset($data['enquiry_filter']) && $data['enquiry_filter']) {
    $enquiry_filter = $data['enquiry_filter'];
    $enquiry_filter_condition = ' AND (lead.disposition_status_id = ' . $enquiry_filter . ' OR lead.disposition_sub_status_id = ' . $enquiry_filter . ')';
}

$date_range_condition = '';
$from = '';
$to = '';

// Date Range Filter
if (isset($data['date_range_filter']) && $data['date_range_filter'] != '') {

    // explode string and extract from and to date 

    $range = explode(' / ', $data['date_range_filter']);
    $from = $range[0] . ' 00:00:00';
    $to = $range[1] . ' 23:59:59';

    $date_range_condition = ' AND lead.leadAddDate BETWEEN "' . $from . '" AND "' . $to . '" ';
}


// Date filter on lead update date 
$update_date_filter = '';

if (isset($data['lead_update_date_filter']) && $data['lead_update_date_filter'] != '') {

    // explode string and extract from and to date 

    $update_filter_date = explode(' / ', $data['lead_update_date_filter']);
    $from1 = $update_filter_date[0] . ' 00:00:00';
    $to1 = $update_filter_date[1] . ' 23:59:59';

    if ($date_range_condition == '') {
        $update_date_filter = ' AND lead.leadUpdateDate BETWEEN "' . $from1 . '" AND "' . $to1 . '" ';
    } else {
        $update_date_filter = ' OR lead.leadUpdateDate BETWEEN "' . $from1 . '" AND "' . $to1 . '" ';
    }
}


if (isset($data['user_id'])) {

    $sales_person_id = $data['user_id'];

    $order_by = " ORDER BY leadAddDate DESC ";

    $sql = "SELECT lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate, lead.is_cold_call,lead.lead_added_by_user, lead.customerName, lead.customerEmail, lead.customerMobile, lead.customerLandline, CONCAT(emp.firstname,' ', emp.lastname) as lead_added_by_employee, lead.lead_assigned_to_asm, lead.lead_assigned_to_sp, lead.is_lead_accepted, lead.is_lead_rejected, lead.lead_rejection_reason, lead.lead_accept_datetime, lead.reassign_user_id , lead.meeting_id, lead.site_visit_id FROM lead as lead LEFT JOIN employees as emp ON (lead.lead_added_by_user = emp.id) WHERE lead.lead_assigned_to_sp = " . $sales_person_id . " AND lead_closure_date IS NULL " . $enquiry_filter_condition . " " . $date_range_condition . " " . $update_date_filter;


    // Re-assign Enquiries	
    if (!$enquiry_filter_condition) {
        $sql_re_assign = 'SELECT distinct enquiry_id FROM lead_re_assign WHERE to_user_id = "' . $sales_person_id . '" AND user_type = "sales_person" ORDER BY id DESC';
        $asm_reassign_enquiries = mysql_query($sql_re_assign);
    }
    $re_assigned_lead_query = '';

    $reAssignIds = array();

    if ($asm_reassign_enquiries && mysql_num_rows($asm_reassign_enquiries) > 0) {

        $reasign_enquiry_ids = array();

        while ($row = mysql_fetch_assoc($asm_reassign_enquiries)) {

            $reAssignIds[] = $row['enquiry_id'];

            array_push($reasign_enquiry_ids, $row['enquiry_id']);
        }

        $re_assigned_lead_query = ' UNION ALL ';

        $re_assigned_lead_query .= "SELECT lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate, lead.is_cold_call,lead.lead_added_by_user, lead.customerName, lead.customerEmail, lead.customerMobile, lead.customerLandline, CONCAT(emp.firstname,' ', emp.lastname) as lead_added_by_employee, lead.lead_assigned_to_asm, lead.lead_assigned_to_sp, lead.is_lead_accepted, lead.is_lead_rejected, lead.lead_rejection_reason, lead.lead_accept_datetime, lead.reassign_user_id , lead.meeting_id, lead.site_visit_id FROM lead as lead LEFT JOIN employees as emp ON (lead.lead_added_by_user = emp.id) WHERE lead.enquiry_id IN (" . implode(',', $reasign_enquiry_ids) . ") AND lead.lead_closure_date IS NULL " . $enquiry_filter_condition . " " . $date_range_condition . " " . $update_date_filter;
    }

    $sql = $sql . ' ' . $re_assigned_lead_query;

    $sql = "SELECT A.* FROM ($sql) AS A GROUP BY A.enquiry_id";

    $sql .= $order_by;

    $result = mysql_query($sql);

    if ($result && mysql_num_rows($result) > 0) {

        while ($row = mysql_fetch_assoc($result)) {

            //$row['primary_status_title']	= getstatuslabel($row['disposition_status_id'],'parent');
            //$row['secondary_status_title']	= getstatuslabel($row['disposition_sub_status_id'],'child');
            $row['asm_name'] = getemployeename($row['lead_assigned_to_asm']);

            if ($row['reassign_user_id'] != '') {
                unset($row['lead_added_by_employee']);
                $row['lead_added_by_employee'] = getEmployeeName($row['reassign_user_id']);
            }
            // Fetch sales last update on lead 
            $sales_last_disposition = mysql_query("SELECT disposition_status_id, disposition_sub_status_id FROM lead_status WHERE enquiry_id = '" . $row['enquiry_id'] . "' AND user_type = 'sales_person' AND user_id = '" . $sales_person_id . "' ORDER BY date DESC LIMIT 1");

            if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {

                $sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);

                $row['sales_disposition'] = getstatuslabel($sales_disposition_data['disposition_status_id'], 'parent');

                if ($sales_disposition_data['disposition_sub_status_id'] != '') {
                    $row['sales_disposition'] .= ' ' . getstatuslabel($sales_disposition_data['disposition_sub_status_id'], 'child');
                }
            } else {
                $row['sales_disposition'] = 'NA';
            }

            $assignment = mysql_query("SELECT * FROM lead_assignment_sales WHERE sp_id='$sales_person_id' AND enquiry_id='" . $row['enquiry_id'] . "'");
            if (mysql_num_rows($assignment) > 0) {

                $row['current_status'] = 'removed';
            }
            
            if ($row['lead_assigned_to_sp'] == $sales_person_id && $row['is_lead_accepted'] == 1 && $row['is_lead_rejected'] == 0) {
                $row['current_status'] = 'accepted';
            } elseif ($row['lead_assigned_to_sp'] == $sales_person_id && $row['is_lead_accepted'] == 1 && $row['is_lead_rejected'] == 1) {
                $row['current_status'] = 'accepted & rejected';
            } elseif ($row['lead_assigned_to_sp'] == $sales_person_id && $row['is_lead_accepted'] == 0 && $row['is_lead_rejected'] == 1) {
                $row['current_status'] = 'rejected';
            } elseif ($row['lead_assigned_to_sp'] == $sales_person_id && $row['is_lead_accepted'] == 0 && $row['is_lead_rejected'] == 0) {
                $row['current_status'] = 'assign';
            } elseif ($row['lead_assigned_to_sp'] != $sales_person_id && $row['is_lead_accepted'] == 0 && $row['is_lead_rejected'] == 0) {
                $row['current_status'] = 'removed';
            } elseif ($row['lead_assigned_to_sp'] != $sales_person_id && $row['is_lead_accepted'] == 0 && $row['is_lead_rejected'] == 1) {
                $row['current_status'] = 'rejected';
            }else {
                $row['current_status'] = 'removed';
            }


            // Current CRM
            $row['current_crm'] = currentAssignedCRM($row['enquiry_id']);

            $row['projects'] = array();
            $status_type = '';

            if ($row['disposition_status_id'] == 3) {
                $status_type = 'M';

                $projects = mysql_query('SELECT project FROM lead_meeting WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND meetingId = "' . $row['meeting_id'] . '" LIMIT 1');
            } else if ($row['disposition_status_id'] == 6) {
                $status_type = 'S';

                $projects = mysql_query('SELECT project FROM site_visit WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND site_visit_id = "' . $row['site_visit_id'] . '" LIMIT 1');
            } else {
                $status_type = 'O';
                $projects = mysql_query('SELECT project_name FROM lead_enquiry_projects WHERE enquiry_id = ' . $row['enquiry_id'] . '');
            }


            $enquiry_projects = getEnquiryProjects($projects, $status_type);

            $row['projects'] = $enquiry_projects;

            array_push($leads, $row);
        }
    }


    // response in JSON format
    $response = array(
        'success' => 1,
        'http_status_code' => 200,
        'data' => $leads,
    );

    echo json_encode($response, true);

    exit;
} else {
    $error_response = array(
        'success' => 0,
        'http_status_code' => 401,
        'message' => 'Unauthorized access'
    );

    echo json_encode($error_response, true);
    exit;
}