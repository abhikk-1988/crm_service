<?php

require_once 'db_connection.php';

function getStatusLabel($status_id = NULL, $type = "parent") {


    if ($status_id == NULL) {


        return '';
    }


    $column_to_select = '';


    if ($type == 'parent') {


        $column_to_select = 'status_title';
    } else {

        $column_to_select = 'sub_status_title';
    }


    $select_status_title = 'SELECT ' . $column_to_select . ' as title '
            . ' FROM `disposition_status_substatus_master` WHERE id = ' . $status_id . ' LIMIT 1 ';


    $result = mysql_query($select_status_title);


    if ($result) {


        if (mysql_num_rows($result) > 0) {


            $data = mysql_fetch_object($result);


            return $data->title;
        }
    }
}

$data = file_get_contents('php://input');
$jsonDecode = json_decode($data, true);

$todayDate = date('Y-m-d');
$dateValue = '';
$where = " and created_on = '" . $todayDate . "'";
$dateValue = "Today";

if (isset($jsonDecode['date_range']) AND ! empty($jsonDecode['date_range'])) {
    unset($jsonDecode['filterType']);
}

if (!isset($jsonDecode['filterType'])) {

    // Date range section ussing the calendar.. 
    if (isset($jsonDecode['date_range']) && $jsonDecode['date_range'] != '') {

        $var = $jsonDecode['date_range'];
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

            $where = " and created_on BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
            $dateValue = $varArr[0] . "-" . $varArr[1];
        } else {


            $where = " and created_on = '" . $startDate . "'";



            if ($todayDate == $startDate) {

                $dateValue = "Today";
            } else {

                $dateValue = $startDate;
            }
        }

        $dateValue = $dateValue;
    }
    //End of date range section using the calendar..
} else {

    $where = '';

    switch ($jsonDecode['filterType']) {

        case 'all':

            /* $selecDate_range = "select max( created_on ) as end_date , min( created_on ) as start_date from crm_enquiry_capture ENQ left join employees EMP on (EMP.id=ENQ.enquiry_assign_to_agent_id)";

              $executeQuery = mysql_query($selecDate_range);
              $dates = mysql_fetch_assoc($executeQuery);

              $start_date = explode("-", $dates['start_date']);
              $sd = trim($start_date[2]);
              $sm = trim($start_date[1]);
              $sy = trim($start_date[0]);

              $start_date = $sd . "/" . $sm . "/" . $sy;

              $end_date = explode("-", $dates['end_date']);
              $sd = trim($end_date[2]);
              $sm = trim($end_date[1]);
              $sy = trim($end_date[0]);
              $end_date = $sd . "/" . $sm . "/" . $sy;
              $dates = $start_date . " - " . $end_date;
              $dateValue = $dates; */
            $where = "";
            $dateValue = "ALL";
            break;
        case 'today':

            $todayDate = date('Y-m-d');
            $where = " and created_on = '" . $todayDate . "'";
            $dateValue = "Today";
            break;
        case 'yestday':


            $newDate = date("'Y-m-d'", time() - 60 * 60 * 24); //date('Y-m-d');
            $where = " and created_on = " . $newDate;
            $dateValue = "Yesterday";

            break;
    }
}

/*
 * author: @sudhanshu
 * code for applying filters
 */
if (isset($jsonDecode['filters']['source']) AND ! empty($jsonDecode['filters']['source']) AND $jsonDecode['filters']['source'] != "All") {
    $where .= " AND ENQ.enquiry_from = '" . $jsonDecode['filters']['source'] . "'";
}
// agent filter
if (isset($jsonDecode['filters']['agent']) AND ! empty($jsonDecode['filters']['agent']['id'])) {
    if ($jsonDecode['filters']['agent']['id'] == -2) {
        $where .= " AND ENQ.enquiry_assign_to_agent_id = '0'";
    } else {
        $where .= " AND ENQ.enquiry_assign_to_agent_id = " . (int) $jsonDecode['filters']['agent']['id'] . "";
    }
}
// disposition filter
if (isset($jsonDecode['filters']['disposition']) AND ! empty($jsonDecode['filters']['disposition']['id'])) {
    $dispositionId = (int) $jsonDecode['filters']['disposition']['id'];
    if ($dispositionId > 0) {
        $where .= " AND ENQ.phone IN (SELECT customerMobile FROM lead WHERE disposition_status_id = $dispositionId OR disposition_sub_status_id = $dispositionId)";
    }
    // for pending status becuase it is hard coded not exist in database
    if ($dispositionId <= 0) {
        $where .= " AND ENQ.phone NOT IN (SELECT customerMobile FROM lead WHERE 1=1)";
    }
}

// @sudhanshu applied filters logice ended here

$selectEnquiry = 'select ENQ.*,EMP.firstname as agent_firstname,EMP.lastname as agent_lastname,EMP.email as agent_email,EMP.contactNumber as agent_contact_number , EMP.id as agent_id '
        . ' from crm_enquiry_capture ENQ  '
        . ' left join employees EMP on (EMP.id=ENQ.enquiry_assign_to_agent_id)  '
        . ' where 1=1 ' . $where . ' order by ENQ.created_time desc';

//echo $selectEnquiry; exit;

$result = mysql_query($selectEnquiry);
$rows = @mysql_num_rows($result);
$enquery = array();

if ($rows > 0) {

    while ($row = mysql_fetch_assoc($result)) {

        $row['leadJson'] = json_decode($row['leadvalujson']);

        $mob = $row['phone'];
        $sqli = "select disposition_status_id,disposition_sub_status_id from lead where customerMobile = '$mob'";
        $sql_result = mysql_query($sqli);
        $sqli_rows = @mysql_fetch_assoc($sql_result);
        if (getStatusLabel($sqli_rows['disposition_sub_status_id'], 'child') == '') {
            $status_dis = getStatusLabel($sqli_rows['disposition_status_id'], 'parent');
        } else {
            $status_dis = getStatusLabel($sqli_rows['disposition_status_id'], 'parent') . '/' . getStatusLabel($sqli_rows['disposition_sub_status_id'], 'child');
        }
        if (getStatusLabel($sqli_rows['disposition_status_id'], 'parent') == '' && getStatusLabel($sqli_rows['disposition_sub_status_id'], 'child') == '') {
            $status_dis = 'Pending';
        }

        //$row['leadJson']->status_dis = $status_dis;
        unset($row['leadvalujson']);
        $row['status_dis'] = $status_dis;
        $date = new DateTime();
        $date->setTimestamp($row['created_time']);
        $row['created_time'] = $date->format('d/m/Y H:i:s');
        $date->setTimestamp($row['ivr_push_date']);
        $row['ivr_push_date'] = $date->format('d/m/Y H:i:s');

        array_push($enquery, $row);
        //$selectupdate 		=	"update crm_enquiry_capture set syn_in_crm=1,syn_marker_new=1";
        //$selectupdate = "update crm_enquiry_capture set syn_in_crm=1";
        //mysql_query($selectupdate);
    }
}

echo json_encode(array("action" => 'success', "dateRange" => $dateValue, 'enData' => $enquery, 'count' => count($enquery)), true);
?>

