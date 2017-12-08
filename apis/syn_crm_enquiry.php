<?php

require_once 'db_connection.php';

$selectEnquiry  = "select * from crm_enquiry_capture where syn_in_crm = '0' order by created_time asc limit 1 ";
$result         = mysql_query($selectEnquiry);
$rows           = mysql_num_rows($result);
$enquery        = array();

if ($rows > 0) {

    while ($row = mysql_fetch_assoc($result)) {

        $row['leadJson']    = json_decode($row['leadvalujson']);
        unset($row['leadvalujson']);
        $date = new DateTime();
        $date->setTimestamp($row['created_time']);
        $row['created_time'] = $date->format('Y-m-d H:i:s');
        array_push($enquery, $row);

        $selectupdate = "update crm_enquiry_capture set syn_in_crm = 1 where query_request_id ='" . $row['query_request_id'] . "'";

        mysql_query($selectupdate);
    }
}

echo json_encode($enquery, true);
