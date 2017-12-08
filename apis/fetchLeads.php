<?php
session_start();
require_once 'function.php';
require_once 'user_authentication.php';

if (!$is_authenticate) {
    echo unauthorizedResponse();
    exit;
}

// Get query string 
$filter_by = '';

// Initially load results 30 days back from current date 
$default_date_range = date('Y-m-d', strtotime('-30 days'));


// Filter By lead create date
if (isset($_GET['create_date_filter']) && $_GET['create_date_filter'] != '') {

    $range  = explode(' / ', $_GET['create_date_filter']);
    $from   = $range[0] . ' 00:00:00';
    $to     = $range[1] . ' 23:59:59';

    if ($filter_by != '') {
        $filter_by .= ' AND leadAddDate BETWEEN "' . $from . '" AND "' . $to . '" ';
    } else {
        $filter_by = ' WHERE leadAddDate BETWEEN "' . $from . '" AND "' . $to . '" ';
    }
} else {
    $filter_by .= ' WHERE DATE(leadAddDate) >= "' . $default_date_range . '"';
}

// Filter by Disposition status

if (isset($_GET['filter_lead_status']) && $_GET['filter_lead_status'] != 'null') {
    $filter_by .= ' AND (disposition_status_id = ' . $_GET['filter_lead_status'] . ' OR disposition_sub_status_id =  ' . $_GET['filter_lead_status'] . ' )';
}

// Filter by lead update date
if (isset($_GET['update_date_filter']) && $_GET['update_date_filter'] != '') {

    $range  = explode(' / ', $_GET['update_date_filter']);
    $from   = $range[0] . ' 00:00:00';
    $to     = $range[1] . ' 23:59:59';

    if ($filter_by != '') {
        $filter_by .= ' AND leadUpdateDate BETWEEN "' . $from . '" AND "' . $to . '" ';
    } else {
        $filter_by = ' WHERE leadUpdateDate BETWEEN "' . $from . '" AND "' . $to . '" ';
    }
}


// Query start to fetch leads
$leads_query = 'SELECT * FROM `lead` ' . $filter_by . ' ORDER BY `leadAddDate` DESC';

// Measuring Query Execution Time
$msc            = microtime(true);
$lead_resource  = mysql_query($leads_query);
$net_msc        = microtime(true) - $msc;


$leads = array();
if ($lead_resource) {

    while ($row = mysql_fetch_assoc($lead_resource)) {
        
        $url = BASE_URL . 'apis/helper.php?method=getEnquiryProjects&params=enquiry_id:' . $row['enquiry_id'] . '/lead_id:' . $row['lead_id'];

        $projects                       = json_decode(file_get_contents($url), true);
        $primary_disposition_title      = getStatusLabel($row['disposition_status_id'], 'parent');
        $secondary_disposition_title    = getStatusLabel($row['disposition_sub_status_id'], '');
        $row['disposition']             = $primary_disposition_title . ' ' . $secondary_disposition_title;
       
//      Get terittory manager and sales manager name
        $employee_names = getMultipleEmployeeName(array($row['lead_added_by_user'],$row['lead_assigned_to_asm'],$row['lead_assigned_to_sp']));
        
        if(count($employee_names) > 0){
            $row['crm_name']            = $employee_names[0];
            $row['tm_name']             = $employee_names[1];
            $row['sales_manager_name']  = $employee_names[2];
        }
        
        
        $row['enquiry_projects']        = $projects;    
        
        // Current CRM
        $row['current_crm']         = currentAssignedCRM($row['enquiry_id']);

        // CRM and Sales Disposition
        $crm_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                . ' FROM `lead_status` '
                . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type = "agent" '
                . ' ORDER BY date DESC LIMIT 1');

        if ($crm_last_disposition && mysql_num_rows($crm_last_disposition) > 0) {

            $crm_disposition_data                   = mysql_fetch_assoc($crm_last_disposition);
            $row['crm_disposition_status_id']       = $crm_disposition_data['disposition_status_id'];
            $row['last_crm_activity']               = getStatusLabel($crm_disposition_data['disposition_status_id']);
            $row['crm_sub_disposition_status_id']   = $crm_disposition_data['disposition_sub_status_id'];
            $row['last_crm_sub_activity']           = getStatusLabel($crm_disposition_data['disposition_sub_status_id'], 'child');
        }

        // Get Sales Team Last Disposition on enquiry  
        $sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id '
                . ' FROM lead_status '
                . ' WHERE enquiry_id = ' . $row['enquiry_id'] . ' AND user_type != "agent" '
                . ' ORDER BY date DESC LIMIT 1');

        if ($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0) {

            $sales_disposition_data                 = mysql_fetch_assoc($sales_last_disposition);
            $row['sales_disposition_status_id']     = $sales_disposition_data['disposition_status_id'];
            $row['last_sales_activity']             = getStatusLabel($sales_disposition_data['disposition_status_id']);
            $row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
            $row['last_sales_sub_activity']         = getStatusLabel($sales_disposition_data['disposition_sub_status_id']);
        }


        // Getting Voice/Call Recording of customer mobile number 
        $recording = mysql_query('SELECT voice_url '
                . ' FROM voice_logger '
                . ' WHERE cust_mobile_no = ' . $row['customerMobile'] . ' AND agent_id != 0');

        $recording_url          = mysql_fetch_assoc($recording);
        $row['recording_url']   = $recording_url;
        array_push($leads, $row);
    }
}

$result = array(
    'success' => 1, 
    'http_status_code' => 200, 
    'data' => $leads, 
    'query_execution_time_sec' => $net_msc . 's', 
    'query_execution_time_ms' => ($net_msc * 1000) . 'ms');

$utf_encoded_result     = array_utf8_encode($result);
$json_result            = json_encode($utf_encoded_result, true);
echo $json_result;
exit;
