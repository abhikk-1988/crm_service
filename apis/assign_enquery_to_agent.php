<?php
require_once 'db_connection.php';
//Assign enquery to agent 
//Get Assigned enquery  based on the designnation.

$data = file_get_contents('php://input');

$data = json_decode($data, true);

if (count($data) > 0) {
    
    $userID         = $data['userID'];
    $assign_by      = $data['assign_by'];
    $enqueryID      = $data['enqueryID'];

    $sql_selectQuery = "select id , enquiry_assign_to_agent_id from crm_enquiry_capture where id='" . $enqueryID . "'";
    $sqlQuery = mysql_query($sql_selectQuery);
    $numrow = mysql_num_rows($sqlQuery);

    if ($numrow > 0) {

        $getQueryData = mysql_fetch_assoc($sqlQuery);

        /*
          if($getQueryData['enquiry_assign_to_agent_id']!=0){

          echo json_encode(array('action'=>'danger','message'=>'This enquery already assigned.'));
          die();
          }


          if($getQueryData['enquiry_assign_to_agent_id']==$userID){

          echo json_encode(array('action'=>'warning','message'=>'This enquery already assigned.'));
          die();
          }
         */


        if ($getQueryData['enquiry_assign_to_agent_id'] == 0) {

            $message = 'Enquery has been assigned successfully.';

            $update = "update crm_enquiry_capture set  enquiry_assign_to_agent_id='" . $userID . "', enquiry_assign_by_to_agent_id='" . $assign_by . "',agent_assign_date='" . date('Y-m-d H:i:s') . "' , agent_assign_status='1' ,agent_assign_time='" . time() . "' where id='" . $enqueryID . "'";
            $updateQuery = mysql_query($update);
        } else {

            $message = 'Agent assignment has removed successfully.';
            $update = "update crm_enquiry_capture set  enquiry_assign_to_agent_id='', enquiry_assign_by_to_agent_id='', agent_assign_status='0', agent_assign_date='' where id='" . $enqueryID . "'";
            $updateQuery = mysql_query($update);
        }

        $sql_selectQuery = "select agent_assign_status from crm_enquiry_capture where id='" . $enqueryID . "'";

        $sqlQuery = mysql_query($sql_selectQuery);
        $getQueryData = mysql_fetch_assoc($sqlQuery);

        echo json_encode(array('action' => 'success', 'message' => $message, 'agent_assign_status' => $getQueryData['agent_assign_status']));
        die();
    } else {

        echo json_encode(array('action' => 'error', 'message' => 'enquery is not valid.'));
        die();
    }
}
?>
