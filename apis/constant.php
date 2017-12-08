<?php

date_default_timezone_set('Asia/Kolkata');

$root_folder = '';

if(strtolower($_SERVER['HTTP_HOST']) != 'localhost'){
    $root_folder = 'crm/';
}
else{
	$root_folder = 'crm';
}

$base_url = 'http://crm.bookmyhouse.biz/'.$root_folder.'/';

define('BASE_URL',$base_url);

//define('LOGIN_URL','http://crm.bookmyhouse.biz/crm/');

// ADMIN CONSTANT
define('ROLE_ADMIN', 1);

define('NOTES_PER_PAGE', 5);

$current_date = date('Y-m-d H:i:s');

define('NEW_LEAD_ASSIGNMENT_TO_SP', 'Lead/ Enquiry has been assigned to sales person on '. $current_date);

// Email functionality ON/OFF
define('IS_EMAIL_ON',1);
define('DEFAULT_SALES_PERSON_STATUS', '[{"7":["16","33","17"]},{"4":["10"]},{"3":["11","12"]},{"6":["14","15"]}]');
define('DEFAULT_AGENT_STATUS','[{"4":["10","18"]},{"34":[]},{"3":["11","12","22"]},{"1":[]},{"6":["14","15","23"]},{"5":["21","20","19"]}]');
define('CHEQUE_UPLOAD_PATH','upload/cheques/');
define('TRANSACTION_UPLOAD_PATH','upload/transaction/');
define('DEFAULT_TO_USERS', 'default_to_users');
define('DEFAULT_BCC_USERS', 'default_bcc_users');
define('DEFAULT_CC_USERS', 'default_cc_users');
define('TO','to');
define('BCC','bcc');
define('CC','cc');
define('SUBJECT', 'subject');
define('MESSAGE', 'message');
define('TO_NAME', 'to_name');
define('REPLY_TO', 'reply_to');