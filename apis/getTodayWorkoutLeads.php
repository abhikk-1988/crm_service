<?php

session_start();

require 'function.php';

$date = date('Y-m-d', time());

$user = '';

$lead_type = '';

$status = '';

$sub_status = '';

$sub_status_condition = '';

$leads_collection = array();

$post = file_get_contents('php://input');

$post_data = json_decode($post, true);

if (isset($post_data['user']) && $post_data['user'] != '') {

    $user       = $post_data['user'];

    $lead_type  = $post_data['lead_type'];

    // 	Fetch today's Followup and calback leads

    if ($lead_type['primary'] != '') {

        $status = $lead_type['primary'];

        if ($lead_type['secondary'] != '') {

            $sub_status = $lead_type['secondary'];

            $sub_status_condition = ' AND disposition_sub_status_id = ' . $sub_status . ' ';
        }

        // 		Query to database
        // 		Umesh
        
        $asm_reassign_enquiries = mysql_query('SELECT distinct enquiry_id FROM lead_re_assign WHERE to_user_id = ' . $user . ' AND user_type = "agent" AND change_status="processed" ORDER BY id DESC LIMIT 1');

        $re_assigned_lead_query = '';

        if ($asm_reassign_enquiries && mysql_num_rows($asm_reassign_enquiries) > 0) {

            $reasign_enquiry_ids = array();

            while ($row = mysql_fetch_assoc($asm_reassign_enquiries)) {

                $reAssignIds[] = $row['enquiry_id'];

                array_push($reasign_enquiry_ids, $row['enquiry_id']);
            }

            $re_assigned_lead_query = ' UNION ALL ';

            $re_assigned_lead_query .= ' SELECT * FROM lead WHERE enquiry_id IN (' . implode(',', $reasign_enquiry_ids) . ') AND disposition_status_id = ' . $status . ' ' . $sub_status_condition;
        }

        //End Umesh Work

//        $leads = mysql_query('SELECT * '
//                . ' FROM lead '
//                . ' WHERE disposition_status_id = ' . $status . ' ' . $sub_status_condition . ' '
//                . ' AND future_followup_date <= "' . $date . '" '
//                . ' AND lead_added_by_user = ' . $user . ' '
//                . ' AND is_callback_done = 0 '
//                . ' AND  enquiry_id NOT IN (' . implode(',', $reAssignIds) . ')' . $re_assigned_lead_query . ' '
//                . ' ORDER BY leadAddDate DESC');

        
        $leads = mysql_query('SELECT * '
                . ' FROM lead '
                . ' WHERE disposition_status_id = ' . $status . ' ' . $sub_status_condition . ' '
                . ' AND future_followup_date <= "' . $date . '" '
                . ' AND (lead_added_by_user = ' . $user . ' OR reassign_user_id = '.$user.')'
                . ' AND is_callback_done = 0 '
                . ' ORDER BY leadAddDate DESC');
                
                  
        if ($leads && mysql_num_rows($leads) > 0) {

            while ($row = mysql_fetch_assoc($leads)) {

                /**
                 * Skip leads which is not belongs to the logged in user
                 * Leads which either reassigned to some other crm agents
                 * 
                 * Code added on: 14th July 2017, Abhishek Agrawal
                 */
                if($row['reassign_user_id'] != NULL || $row['reassign_user_id'] > 0){
                    
                    if($row['reassign_user_id'] != $user){
                        continue;
                    }
                    
                }
                // End: code
                
                
                // Filter Callback/ Followup of current logged in user 
                
                $last_callback_updated_by = mysql_query('SELECT user_id 
                 FROM `future_references` WHERE enquiry_id = '.$row['enquiry_id'].' AND disposition_status_id = '.$row['disposition_status_id'].' AND disposition_sub_status_id = '.$row['disposition_sub_status_id'].' ORDER BY creation_date DESC LIMIT 1');
                
                if($last_callback_updated_by){
                    
                    $last_callback_user = mysql_fetch_assoc($last_callback_updated_by);
                    
                    if($last_callback_user['user_id'] != $user) {
                        continue;
                    }
                }
                
                
                // fetch projects 
                $row['projects'] = array();

                $projects = mysql_query('SELECT project_name FROM lead_enquiry_projects WHERE enquiry_id = ' . $row['enquiry_id'] . '');


                if ($projects && mysql_num_rows($projects) > 0) {

                    while ($project = mysql_fetch_assoc($projects)) {

                        array_push($row['projects'], $project['project_name']);
                    }
                } else {

                    array_push($row['projects'], 'NA');
                }


                if ($row['reassign_user_id'] != '') {

                    $row['lead_created_by'] = getEmployeeName($row['reassign_user_id']);
                } else {

                    $row['lead_created_by'] = getEmployeeName($row['lead_added_by_user']);
                }


                array_push($leads_collection, $row);
            }
        }
    }
}


echo json_encode($leads_collection, true);
