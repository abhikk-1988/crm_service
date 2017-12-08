<?php
/**
 * API to fetch leads of individual user 
 */

session_start();

require_once 'function.php';
require_once 'user_authentication.php';

if (!$is_authenticate) {
    echo unauthorizedResponse();
    exit;
}

$user           = $_SESSION['currentUser'];
$reportingTo    = $user['reportingTo'];
$post_data      = filter_input_array(INPUT_POST);

$leads_data = array();

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

if (isset($post_data) && $post_data['user_id']) {

    $user_id            = $post_data['user_id'];
    $designation_id     = $post_data['designation_id'];
    $designation_slug   = $post_data['designation_slug'];

    $enquiry_filter             = '';
    $enquiry_filter_condition   = '';
    $date_range_condition       = '';
    $from                       = '';
    $to                         = '';

    // Disposition status filter 
    if (isset($post_data['enquiry_filter']) && $post_data['enquiry_filter']) {
        $enquiry_filter = $post_data['enquiry_filter'];
        $enquiry_filter_condition = ' AND (lead.disposition_status_id = ' . $enquiry_filter . ' OR lead.disposition_sub_status_id = ' . $post_data['enquiry_filter'] . ' )';
    }

    // Date Range Filter
    if (isset($post_data['date_range_filter']) && $post_data['date_range_filter'] != '') {

        // explode string and extract from and to date 

        $range = explode(' / ', $post_data['date_range_filter']);
        $from = $range[0] . ' 00:00:00';
        $to = $range[1] . ' 23:59:59';
        $date_range_condition = ' AND lead.leadAddDate BETWEEN "' . $from . '" AND "' . $to . '" ';
    }

    // Date filter on lead update date 
    $update_date_filter = '';

    if (isset($post_data['lead_update_date_filter']) && $post_data['lead_update_date_filter'] != '') {

        // explode string and extract from and to date 

        $update_filter_date = explode(' / ', $post_data['lead_update_date_filter']);
        $from1 = $update_filter_date[0] . ' 00:00:00';
        $to1 = $update_filter_date[1] . ' 23:59:59';

        if ($date_range_condition == '') {
            $update_date_filter = ' AND lead.leadUpdateDate BETWEEN "' . $from1 . '" AND "' . $to1 . '" ';
        } else {
            $update_date_filter = ' OR lead.leadUpdateDate BETWEEN "' . $from1 . '" AND "' . $to1 . '" ';
        }
    }


    // for re_assigned lead query 
    $re_assigned_leads = '';

    switch ($designation_slug) {

        case 'agent':
            
            $lead_users_ids             = "'" . implode("', '", getUsersHierarchy($user_id, 0)) . "'";
            $get_re_assigned_enquiries  = mysql_query('SELECT enquiry_id '
                    . ' FROM `lead_re_assign` '
                    . ' WHERE user_type = "agent" AND to_user_id = "' . $user_id . '" AND change_status != "pending" AND change_status != "removed" AND lead_type != "create"');

            $enquiry_ids = array();
            if ($get_re_assigned_enquiries) {
                while ($row = mysql_fetch_assoc($get_re_assigned_enquiries)) {
                    array_push($enquiry_ids, $row['enquiry_id']);
                }
            }

            if (!empty($enquiry_ids)) {
                $re_assigned_leads = ' UNION ALL ';
                $re_assigned_leads .= ' SELECT lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate,  lead.is_cold_call,lead.lead_added_by_user, lead.customerName, lead.customerEmail, lead.customerMobile, lead.customerLandline, CONCAT(emp.firstname," ", emp.lastname) as lead_added_by_employee, lead.lead_category, lead.lead_assigned_to_asm, lead.lead_assigned_to_sp, lead.meeting_id, lead.site_visit_id, lead.enquiry_status_remark, lead.reassign_user_id 
			FROM lead as lead 
			LEFT JOIN employees as emp ON (lead.lead_added_by_user = emp.id ) 
			WHERE enquiry_id IN (' . implode(',', $enquiry_ids) . ') ' . $enquiry_filter_condition . '' . $date_range_condition . ' ' . $update_date_filter;
            }
            
            break;

        case 'senior_executive':
            $lead_users_ids = "'" . implode("', '", getUsersHierarchy($user_id, 0)) . "'";
            $re_assigned_leads = '';
            break;

        case 'executive':
            $lead_users_ids = "'" . implode("', '", getUsersHierarchy($user_id, 0)) . "'";
            $re_assigned_leads = '';
            break;

        case 'team_leader':
            $lead_users_ids = "'" . implode("', '", getUsersHierarchy($user_id, 1)) . "'";
            $re_assigned_leads = '';
            break;

        case 'sr_team_leader':
            $lead_users_ids = "'" . implode("', '", getUsersHierarchy($user_id, 2)) . "'";
            $re_assigned_leads = '';
            break;
    }

    if ($lead_users_ids) {

        // Get leads other than closed leads
        $order_by = ' ORDER BY leadAddDate DESC ';

        $leads = "SELECT "
                . " lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate,  lead.is_cold_call,lead.lead_added_by_user, lead.customerName, lead.customerEmail, lead.customerMobile, lead.customerLandline, CONCAT(emp.firstname,' ', emp.lastname) as lead_added_by_employee,"
                . " lead.lead_category, lead.lead_assigned_to_asm, lead.lead_assigned_to_sp, lead.meeting_id, lead.site_visit_id, lead.enquiry_status_remark, lead.reassign_user_id "
                . " FROM lead as lead"
                . " LEFT JOIN employees as emp ON (lead.lead_added_by_user = emp.id)"
                . " WHERE lead.lead_added_by_user IN (" . $lead_users_ids . ") AND lead_closure_date IS NULL " . $enquiry_filter_condition . " " . $date_range_condition . " " . $update_date_filter . " ";

        $leads = $leads . $re_assigned_leads . $order_by;

        $result = mysql_query($leads);

        $projects = '';

        if ($result) {
            if (mysql_num_rows($result) > 0) {

                while ($row = mysql_fetch_assoc($result)) {

                    if ($row['reassign_user_id'] != '') {
                        $row['lead_added_by_employee'] = getEmployeeName($row['reassign_user_id']);
                    }

                    $row['projects'] = array();
                    $status_type = '';

                    if ($row['disposition_status_id'] == 3) {
                        $status_type = 'M';
                        $projects = mysql_query('SELECT project '
                                . ' FROM lead_meeting '
                                . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND meetingId = "' . $row['meeting_id'] . '" '
                                . ' LIMIT 1');
                        
                    } else if ($row['disposition_status_id'] == 6) {
                        $status_type = 'S';
                        $projects = mysql_query('SELECT project '
                                . ' FROM site_visit '
                                . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND site_visit_id = "' . $row['site_visit_id'] . '" '
                                . ' LIMIT 1');
                        
                    } else {
                        $status_type = 'O'; 
                        $projects = mysql_query('SELECT project_name '
                                . ' FROM lead_enquiry_projects '
                                . ' WHERE enquiry_id = ' . $row['enquiry_id'] . '');
                    }

                    // Enquiry Projects
                    $enquiry_projects = getEnquiryProjects($projects, $status_type);
                    $row['projects'] = $enquiry_projects;

                    /**
                     * We need to do some workout for agent data to get last updated status of self or sales team
                     * Also we need to determine if this lead is reassiged to some other crm or not;
                     */
                    if ($designation_slug == 'agent') {

                        $crm_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                                . ' FROM lead_status '
                                . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_id = ' . $user_id . ' AND user_type = "agent" '
                                . ' ORDER BY date DESC LIMIT 1');
                        if ($crm_last_disposition && mysql_num_rows($crm_last_disposition) > 0) {

                            $crm_disposition_data = mysql_fetch_assoc($crm_last_disposition);

                            $row['crm_disposition_status_id'] = $crm_disposition_data['disposition_status_id'];
                            $row['crm_sub_disposition_status_id'] = $crm_disposition_data['disposition_sub_status_id'];
                        }

                        // If reassigned lead and login user is not same
                        if (!is_null($row['reassign_user_id'])) {

                            if ($row['reassign_user_id'] != $user_id) {
                                $row['self_lead'] = false;
                            } else {
                                $row['self_lead'] = true;
                            }
                        } else {
                            $row['self_lead'] = true;
                        }

                        // Umesh
                        if ($row['reassign_user_id'] == NULL) {
                            $row['asm_name'] = getemployeename($row['lead_assigned_to_asm']);
                            $row['sp_name']  = getemployeename($row['lead_assigned_to_sp']);

                            if ($row['asm_name'] == NULL || $row['asm_name'] == '' || $row['asm_name'] == 0) {
                                $sp_query = mysql_query('SELECT * '
                                        . ' FROM lead_assignment_sales '
                                        . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND agent_id = ' . $user_id . ' '
                                        . ' ORDER BY id DESC LIMIT 1');
                                
                                if ($sp_query && mysql_num_rows($sp_query) > 0) {
                                    $res_sp             = mysql_fetch_assoc($sp_query);
                                    $row['asm_name']    = getemployeename($res_sp['asm_id']);
                                    $row['sp_name']     = getemployeename($res_sp['sp_id']);
                                    $row['lead_assigned_to_sp'] = $res_sp['sp_id'];
                                }
                            }

                            // Get Sales Team Last Disposition on enquiry  
                            $sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                                    . ' FROM lead_status '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" AND user_id = ' . $row['lead_assigned_to_sp'] . ' '
                                    . ' ORDER BY id DESC LIMIT 1');

                            if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {
                                $sales_disposition_data                 = mysql_fetch_assoc($sales_last_disposition);
                                $row['sales_disposition_status_id']     = $sales_disposition_data['disposition_status_id'];
                                $row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
                            }
                        } elseif (!is_null($row['reassign_user_id']) && ($user_id == $row['lead_added_by_user'])) {

                            $asm_sql_name = mysql_query('SELECT to_user_id '
                                    . ' FROM lead_re_assign '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "area_sales_manager" '
                                    . ' ORDER BY id ASC LIMIT 1');
                            
                            if ($asm_sql_name && mysql_num_rows($asm_sql_name) > 0) {
                                $previous_asm_id = mysql_fetch_assoc($asm_sql_name);
                                $row['asm_name'] = getEmployeeName($previous_asm_id['to_user_id']);
                            }

                            $sp_sql_name = mysql_query('SELECT to_user_id '
                                    . ' FROM lead_re_assign '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" '
                                    . ' ORDER BY id ASC LIMIT 1');
                            
                            if ($sp_sql_name && mysql_num_rows($sp_sql_name) > 0) {
                                $previous_sp_id = mysql_fetch_assoc($sp_sql_name);
                                $row['sp_name'] = getEmployeeName($previous_sp_id['to_user_id']);
                            }

                            // Get Sales Team Last Disposition on enquiry  
                            $sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                                    . ' FROM lead_status '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" AND user_id = ' . $previous_sp_id['to_user_id'] . ' '
                                    . ' ORDER BY id DESC LIMIT 1');

                            if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {

                                $sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);

                                $row['sales_disposition_status_id'] = $sales_disposition_data['disposition_status_id'];
                                $row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
                            }
                        } elseif (!is_null($row['reassign_user_id']) && ($user_id == $row['reassign_user_id'])) {
                            //Working
                            $row['asm_name'] = getemployeename($row['lead_assigned_to_asm']);
                            $row['sp_name'] = getemployeename($row['lead_assigned_to_sp']);

                            if ($row['asm_name'] == NULL || $row['asm_name'] == '' || $row['asm_name'] == 0) {

                                $sp_query = mysql_query('SELECT * '
                                        . ' FROM lead_assignment_sales '
                                        . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND agent_id = ' . $user_id . ' '
                                        . ' ORDER BY id DESC LIMIT 1');
                                if ($sp_query && mysql_num_rows($sp_query) > 0) {
                                    $res_sp = mysql_fetch_assoc($sp_query);
                                    $row['asm_name'] = getemployeename($res_sp['asm_id']);
                                    $row['sp_name'] = getemployeename($res_sp['sp_id']);
                                }

                                // Get Sales Team Last Disposition on enquiry  
                                $sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                                        . ' FROM lead_status '
                                        . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" AND user_id = ' . $res_sp['sp_id'] . ' '
                                        . ' ORDER BY id DESC LIMIT 1');

                                if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {

                                    $sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);

                                    $row['sales_disposition_status_id'] = $sales_disposition_data['disposition_status_id'];
                                    $row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
                                }
                            }

                            // Get Sales Team Last Disposition on enquiry  
                            $sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                                    . ' FROM lead_status '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" AND user_id = ' . $row['lead_assigned_to_sp'] . ' '
                                    . ' ORDER BY id DESC LIMIT 1');

                                if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {

                                    $sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);

                                    $row['sales_disposition_status_id'] = $sales_disposition_data['disposition_status_id'];
                                    $row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
                                }
                            } else {
                            $asm_sql = mysql_query('SELECT date '
                                    . ' FROM lead_re_assign '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "agent" AND lead_type="re-assign" AND from_user_id = ' . $user_id . ' '
                                    . ' ORDER BY id DESC LIMIT 1');

                            if ($asm_sql && mysql_num_rows($asm_sql) > 0) {
                                $re_assign_date = mysql_fetch_assoc($asm_sql);

                                $asm_sql_name = mysql_query('SELECT to_user_id '
                                        . ' FROM lead_re_assign '
                                        . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "area_sales_manager" AND date < "' . $re_assign_date['date'] . '" ORDER BY id DESC LIMIT 1');
                                if ($asm_sql_name && mysql_num_rows($asm_sql_name) > 0) {
                                    $previous_asm_id = mysql_fetch_assoc($asm_sql_name);
                                    $row['asm_name'] = getEmployeeName($previous_asm_id['to_user_id']);
                                    //$row['self_lead']   = true;
                                }

                                $sp_sql_name = mysql_query('SELECT to_user_id '
                                        . ' FROM lead_re_assign '
                                        . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" AND date < "' . $re_assign_date['date'] . '" ORDER BY id DESC LIMIT 1');
                                if ($sp_sql_name && mysql_num_rows($sp_sql_name) > 0) {
                                    $previous_sp_id = mysql_fetch_assoc($sp_sql_name);
                                    $row['sp_name'] = getEmployeeName($previous_sp_id['to_user_id']);
                                    //$row['self_lead']   = true;
                                }
                            }
                            // Get Sales Team Last Disposition on enquiry  
                            $sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                                    . ' FROM lead_status '
                                    . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "sales_person" AND user_id = ' . $previous_sp_id['to_user_id'] . ' '
                                    . ' ORDER BY id DESC LIMIT 1');

                            if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {

                                $sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);

                                $row['sales_disposition_status_id'] = $sales_disposition_data['disposition_status_id'];
                                $row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
                            }
                        }
                        //End Umesh
                    }

                    /**
                     * Getting Voice Data
                     */
                    $recording = mysql_query('SELECT voice_url '
                            . ' from voice_logger '
                            . ' where agent_id = ' . $user['crm_id'] . ' and cust_mobile_no = ' . $row['customerMobile'] . '');

                    $recording_url          = mysql_fetch_assoc($recording);
                    $row['recording_url']   = $recording_url;
                    /*End./ Voice data */
                    
                    array_push($leads_data, $row);
                }
            }
        }
    }
    
    $response = array_utf8_encode(array(
        'success' => 1,
        'http_status_code' => 200,
        'data' => $leads_data,
    ));
    echo json_encode($response, true);exit;
} else {

    $error_response = array(
        'success' => 0,
        'http_status_code' => 401,
        'message' => 'Unauthorized access'
    );
    echo json_encode($error_response, true);exit;
}