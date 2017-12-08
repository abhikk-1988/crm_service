<?php

header("Access-Control-Allow-Origin: *");
require_once 'db_connection.php';

$post = json_decode($_POST['enquery'], true);
//    if($post['key'] == ''){
//	save_enquery($post['postEnqueryData'], 'BMH');	
//	}
//        else{
switch ($post['key']) {
    case 'bmh_enquery':
        save_enquery($post['postEnqueryData'], 'BMH');
        break;
    case 'fb_enquery':
        save_enquery($post['postEnqueryData'], 'FB');
        break;
    case 'twiter_enquery':
        save_enquery($post['postEnqueryData'], 'TWITER');
        break;
    case 'linkedin_enquery':
        save_enquery($post['postEnqueryData'], 'LINKEDIN');
        break;
    case 'bmhinfomail_enquery':
        save_enquery($post['postEnqueryData'], 'BMHINFOMAIL');
        break;
    case 'shortcode_enquery':
        save_enquery($post['postEnqueryData'], 'SHORTCODE');
        break;
    case 'misscall_enquery':
        save_enquery($post['postEnqueryData'], 'MISSCALL');
        break;
    case 'magicbric_enquery':
        save_enquery($post['postEnqueryData'], 'MAGICBRIC');
        break;
    case 'Microsites':
        save_enquery($post['postEnqueryData'], 'MICROSITES');
        break;
    case 'MICROSITES':
        save_enquery($post['postEnqueryData'], 'MICROSITES');
        break;
    case 'pmay':
        save_enquery($post['postEnqueryData'], 'PMAY');
        break;
    case 'bmhapp':
        save_enquery($post['postEnqueryData'], 'BMHAPP');
        break;
    default :
        if ($post['key'] != '') {
            save_enquery($post['postEnqueryData'], $post['key']);
        }
        break;
}

//}
//Save enquery.
function save_enquery($post, $leadsource) {

    $enquiry_from = $leadsource;
    $numberdigit = $enquiry_from . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $jsonPost = json_encode($post);
    $executive = $post['added_by'];

    //$Mydata 			=   mapFiledKey($post);

    $Mydata = mapFiledKey($post, 'maped');
    $jsonPost = json_encode(mapFiledKey($post, 'all'));

    if (count($Mydata) > 0) {

        $addingQuery = '';


        foreach ($Mydata as $key => $val) {

            $addingQuery .= ", " . $key . "='" . $val . "'";

            if ($key == 'phone') {

                $phone = $val;
            }
        }

        $sql_selectQuery = "select * from crm_enquiry_capture where phone = '" . $phone . "'";

        $sqlQuery = mysql_query($sql_selectQuery);
        $numrow = mysql_num_rows($sqlQuery);


        if ($numrow > 0) {
            $insertLead = "insert into  crm_capture_duplicate set query_request_id='" . $numberdigit . "' ,enquiry_from='" . $enquiry_from . "', leadvalujson='" . $jsonPost . "',executive = '" . $executive . "',  created_on='" . date('Y-m-d H:i:s') . "' ,created_time='" . time() . "' " . $addingQuery;
//            echo json_encode(array('action' => 'error', 'message' => 'phone number already exist.'));
//            die();
        } else {
            $insertLead = "insert into crm_enquiry_capture set query_request_id='" . $numberdigit . "' ,enquiry_from='" . $enquiry_from . "', leadvalujson='" . $jsonPost . "',executive = '" . $executive . "',  created_on='" . date('Y-m-d H:i:s') . "' ,created_time='" . time() . "' " . $addingQuery;
        }
    } else {

        $insertLead = "insert into crm_enquiry_capture set query_request_id='" . $numberdigit . "' ,enquiry_from='" . $enquiry_from . "', leadvalujson='" . $jsonPost . "',executive = '" . $executive . "', created_on='" . date('Y-m-d H:i:s') . "' , created_time='" . time() . "'";
    }

//	echo $insertLead;die;

    $result = mysql_query($insertLead);

    if ($result) {

        echo json_encode(array('action' => 'success', 'message' => 'enquery has been saved sucessfully.'));
    } else {

        echo json_encode(array('action' => 'error', 'message' => 'fail to insert.'));
    }
}

function mapFiledKey($jsonPost, $type = 'maped') {

    try {

        $NameArr = array(
            'full_name' => 'name',
            'name' => 'name',
            'user_name' => 'name',
            'phone_number' => 'phone',
            'email' => 'email',
            'user_email' => 'email',
            'user_contact' => 'phone',
            'project_name' => 'project_name',
            'phone' => 'phone',
            'tell_us_are_you_interested_' => 'tell_us_are_you_interested',
            'want_to_schedule_a_free_site_visit_' => "want_to_schedule_a_free_site_visit",
            'preferred_day_for_site_visit_' => 'preferred_day_for_site_visit',
            'city' => 'city',
            'gender' => 'gender',
            'country' => 'country',
            'enquiry' => 'enquiry',
            'builder_name' => 'builder_name',
            'builder_url' => 'builder_url'
        );

        if ($type == 'maped') {

            $jsonPost2 = array();

            foreach ($NameArr as $key => $val) {

                if (@array_key_exists($key, $jsonPost)) {
                    @$jsonPost2[$val] = @$jsonPost[$key];
                }
            }
            return $jsonPost2;
        } else {

            $c = array();
            foreach ($jsonPost as $key => $va) {

                if (isset($keyArr[$key])) {

                    $c[$keyArr[$key]] = $va;
                    unset($rawArr[$key]);
                } else {

                    $c[$key] = $va;
                }
            }

            return $c;
        }
    } catch (Exception $e) {

        return false;
    }
}

?>