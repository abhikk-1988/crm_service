<?php

// Prepare a string of projects to insert in text message

$sms_project_string = '';

foreach($inquired_projects as $project){

    $sms_project_string .= "".$project['project_name']."";
    $sms_project_string .= " - ";
    $sms_project_string .= "".$project['project_city']."";
    $sms_project_string .= " - ";
    $sms_project_string .= "".$project['project_url']."";
    $sms_project_string .="\n";
}
