<?php

require_once 'db_connection.php';
$data = file_get_contents('php://input');
$josnDecode = json_decode($data, true);

$ivr_success_push = 1;
$ivr_fail_push = 1;

if (count($josnDecode['mydata']) > 0) {

    foreach ($josnDecode['mydata'] as $key => $row) {

        if ($josnDecode['ptype'] == 'ivr') {

            if (push_query_to_ivr($row)) {

                $update = "update crm_enquiry_capture set ivr_push_status='1', ivr_push_type='Manual', ivr_push_date='" . time() . "' where query_request_id='" . $row['query_request_id'] . "'";
                $qq = mysql_query($update);
                if ($qq) {
//                    Start LMS IVR Push By sudhanshu
                    
                    $pusharray = array('mobile' => $row['phone'], 'camp_name' => 'Bookmyhouse', 'Customer_Name' => $row['name']);
                    
                    foreach ($pusharray as $keys => $values) {
                        $urls .= urlencode($keys) . '=' . urlencode($values) . '&';
                    }
                    $hitpush = "https://admin.c-zentrixcloud.com/apps/addlead.php";
                    $contentpush = file_get_contents($hitpush . '?' . $urls);
                    //print_r($contentpush);
                    #End LMS IVR Push By sudhanshu#
                    $ivr_success_push++;
                    ivr_enquiry_push_log($row);
                }
            } else {

                $ivr_fail_push++;
            }
        } else {

            $assigned_by    = $josnDecode["assigned_by"];
            $agent_id       = $josnDecode['agent_id'];
            
            $update = "update crm_enquiry_capture set  enquiry_assign_to_agent_id='" . $agent_id . "', enquiry_assign_by_to_agent_id='" . $assigned_by . "',agent_assign_date='" . date('Y-m-d H:i:s') . "' , agent_assign_status='1' ,agent_assign_time='" . time() . "' where query_request_id='" . $row['query_request_id'] . "'";
            $updateQuery = mysql_query($update);
            
            if ($updateQuery) {
                #Start LMS IVR Push By sudhanshu#
                $pusharray = array('mobile' => $row['phone'], 'camp_name' => 'Bookmyhouse', 'Customer_Name' => $row['name']);
                foreach ($pusharray as $keys => $values) {
                    $urls .= urlencode($keys) . '=' . urlencode($values) . '&';
                }
                $hitpush = "https://admin.c-zentrixcloud.com/apps/addlead.php";
                $contentpush = file_get_contents($hitpush . '?' . $urls);
                //print_r($contentpush);
                #End LMS IVR Push By sudhanshu#
            }
        }
    }

    if ($josnDecode['ptype'] == 'ivr') {

        echo json_encode(array(
            'action' => 'success',
            'message' => 'Queries has been pushed succesfully to the IVR.',
            'sucessCount' => $ivr_success_push,
            'failureCount' => $ivr_fail_push
        ));
    } else {

        echo json_encode(array(
            'action' => 'success',
            'message' => 'Enquery has been assigned succesfully.',
            'sucessCount' => 'agent',
            'failureCount' => 'agent'
        ));
    }
} else {

    echo json_encode(array(
        'action' => 'fail',
        'message' => 'technically issue.',
        'sucessCount' => 0,
        'failureCount' => 0
    ));
}

//comman function  it can be move in  comman function section .

function push_query_to_ivr($row = array()) {
    return true;
}

function ivr_enquiry_push_log($row = array()) {

    return true;
}