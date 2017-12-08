<?php
/*
 * @author: sudhanshu
 * @description: an api for generating drop down fileds of 
 * filters on lead enquiry(/lead-management) page.
 */
require_once 'db_connection.php';

$output = [];
$output['source'] = [];
$output['source'][] = 'All';
$output['agents'] = [];
$output['agents'][] = array(
    'id' => '',
    'name' => 'All',
);
$output['agents'][] = array(
    'id' => -2,
    'name' => 'No Agent',
);
$output['dispositions'] = [];
$output['dispositions'][] = array(
    'id' => '',
    'name' => 'All'
);
$output['dispositions'][] = array(
    'id' => -1,
    'name' => 'Pending'
);
// for source filters 
$sql = "SELECT enquiry_from FROM crm_enquiry_capture GROUP BY enquiry_from ORDER BY enquiry_from";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if($row['enquiry_from'] != ''){
        $output['source'][] = $row['enquiry_from'];    
    }
    
}


// for agents
$sql = "SELECT id,firstname,lastname FROM employees WHERE isDelete = 0 AND designation IN (SELECT id FROM designationmaster WHERE designation_slug LIKE 'agent') ORDER BY firstname ASC";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $output['agents'][] = array(
        'id' => $row['id'],
        'name' => $row['firstname'].' '.$row['lastname']
    );
}


// for disposition status
$sql = "SELECT desg.id,desg.status_title FROM disposition_status_substatus_master as desg WHERE desg.parent_status IS NULL AND desg.active_state = 1 AND desg.delete_state = 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $output['dispositions'][] = array(
        'id' => $row['id'],
        'name' => $row['status_title']
    );
}

// for sub disposition status
$sql = "SELECT desg.id,parent.status_title,desg.sub_status_title FROM disposition_status_substatus_master as desg LEFT JOIN disposition_status_substatus_master as parent ON parent.id = desg.parent_status WHERE desg.parent_status IS NOT NULL AND desg.active_state = 1 AND desg.delete_state = 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $output['dispositions'][] = array(
        'id' => $row['id'],
        'name' => $row['status_title'].' - '.$row['sub_status_title']
    );
}

header('Content-Type: application/json');
echo json_encode($output);
