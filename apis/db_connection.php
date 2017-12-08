<?php
require_once 'constant.php';
$link = mysql_connect('localhost','root','bmhproduction@123!');
if($link){
    mysql_select_db('bmh_crm');
}else{
    echo mysql_error(); exit;
}
