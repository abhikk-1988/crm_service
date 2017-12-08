    <?php
    session_start();
    require 'db_connection.php';
    $user = $_SESSION['currentUser'];
    $mob = $_REQUEST['mobno'];
    $ivr_sesssion = $_REQUEST['sessionid'];
    $calltype = $_REQUEST['calltype'];
    $status = $_REQUEST['status'];
    $ivr_user_id = $user['crm_id'];
    if($status == 'Disconnect'){
    $url1 = "https://admin.c-zentrixcloud.com/apps/appsHandler.php?transaction_id=CTI_MIX_VOICE_FILE&agent_id=$ivr_user_id&session_id=$ivr_sesssion&resFormat=0";
    $response1 = file_get_contents($url1);
    if(trim($response1) == 'SUCCESS'){
    $url2 = "https://admin.c-zentrixcloud.com/apps/appsHandler.php?transaction_id=GET_VOICE_FILENAME&agent_id=$ivr_user_id&session_id=$ivr_sesssion&ip=192.168.1.85&resFormat=0";
    $response2 = trim(file_get_contents($url2));
    }
    $insert_query = "INSERT into voice_logger (agent_id,cust_mobile_no,session_id,voice_url,call_type,status)VALUES('$ivr_user_id','$mob','$ivr_sesssion','$response2','$calltype','$status')";

    mysql_query($insert_query);
    }
    $query = "Select lead_id,enquiry_id,lead_added_by_user from lead where customerMobile = '$mob'";
    $entries = mysql_query($query);
    $row = mysql_fetch_assoc($entries);

    if($row){
    if($row['lead_added_by_user'] == $user['id']){
    echo json_encode(array("direction"=>1,"lead_id"=>$row['lead_id'],"enquiry_id"=>$row['enquiry_id']));	

    }else{

    echo json_encode(array("direction"=>0,"lead_id"=>$row['lead_id'],"enquiry_id"=>$row['enquiry_id']));	
    }	
    }

