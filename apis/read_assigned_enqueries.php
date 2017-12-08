<?php

session_start();

require_once 'function.php';
require_once 'user_authentication.php';
if (!$is_authenticate) {
    echo unauthorizedResponse();
    exit;
}
$user = $_SESSION['currentUser'];

require_once 'db_connection.php';

$data = file_get_contents('php://input');
$josnDecode = json_decode($data, true);


$designation_slug = $josnDecode['designation_slug'];
$userID = $josnDecode['userID'];

/*
  Array
  (
  [date_range] => 30/01/2017 - 01/02/2017
  [userID] => 1
  [designation_slug] => admin
  )
 */

$todayDate = date('Y');

$dateValue = '';
$where = "";
//~ $where = " and agent_assign_date = '" . $todayDate . "'";
$dateValue = "Today";

if (isset($josnDecode['date_range']) && !empty($josnDecode['date_range'])) {

    $var = $josnDecode['date_range'];
    $varArr = explode("-", $var);

    $dateStr1 = explode("/", $varArr[0]);
    $sd = trim($dateStr1[0]);
    $sm = trim($dateStr1[1]);
    $sy = trim($dateStr1[2]);

    $dateEnd = explode("/", $varArr[1]);
    $ed = trim($dateEnd[0]);
    $em = trim($dateEnd[1]);
    $ey = trim($dateEnd[2]);

    $startDate = $sy . "-" . $sm . "-" . $sd;
    $endDate = $ey . "-" . $em . "-" . $ed;

    if ($startDate != $endDate) {

        $where = " and agent_assign_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        $dateValue = $varArr[0] . "-" . $varArr[1];
    } else {

        $where = " and agent_assign_date = '" . $startDate . "'";

        if ($todayDate == $startDate) {

            $dateValue = "Today";
        } else {

            $dateValue = $startDate;
        }
    }

    $dateValue = $dateValue;
}

if (!empty($userID)) {
    $where .= " AND enquiry_assign_to_agent_id = " . $userID;
} else {
    $where .= " AND enquiry_assign_to_agent_id = " . $user['id'];
}

if ($designation_slug == 'admin') {

    $selectEnquiry = "select ENQ.*,EMP.firstname as agent_firstname,EMP.lastname as agent_lastname,EMP.email as agent_email,EMP.contactNumber as agent_contact_number , EMP.id as agent_id "
            . " from crm_enquiry_capture ENQ  "
            . " left join employees EMP on (EMP.id=ENQ.enquiry_assign_to_agent_id)  "
            . " where 1=1 AND query_request_id NOT LIKE 'PORT%' " . $where . " and enquiry_assign_by_to_agent_id='" . $userID . "' order by ENQ.agent_assign_time desc";
} else {
    $selectEnquiry = "select ENQ.*,EMP.firstname as agent_firstname,EMP.lastname as agent_lastname,EMP.email as agent_email,EMP.contactNumber as agent_contact_number , EMP.id as agent_id,l.enquiry_id "
            . " from crm_enquiry_capture ENQ  "
            . " left join employees EMP on (EMP.id = ENQ.enquiry_assign_to_agent_id)  "
            . " LEFT JOIN lead as l ON (ENQ.phone = l.customerMobile)"
            . " where 1=1 AND query_request_id NOT LIKE 'PORT%' " . $where . "  order by ENQ.agent_assign_time desc";
}

//~ die($selectEnquiry);
$result = mysql_query($selectEnquiry);
$rows = mysql_num_rows($result);
$enquery = array();

if ($rows > 0) {

    while ($row = mysql_fetch_assoc($result)) {
        $mob = str_split($row['phone']);

        if ($mob[0] == 0) {
            unset($mob[0]);
            $row['phone'] = implode("", $mob);
        }
        $row['leadJson'] = json_decode($row['leadvalujson']);
        unset($row['leadvalujson']);

        $date = new DateTime();

        $date->setTimestamp($row['agent_assign_time']);
        $row['created_time'] = $date->format('d/m/Y H:i:s');

        //$date->setTimestamp($row['created_time']);
        //$row['created_time']	=	$date->format('d/m/Y H:i:s');
        array_push($enquery, $row);
    }
}

echo json_encode(array(
    "action" => 'success',
    "dateRange" => $dateValue,
    'enData' => $enquery,
    'count' => count($enquery)
        ), true);
?>
