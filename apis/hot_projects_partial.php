<?php

$hot_project_html  = '';
$hot_project_html .= '<h4>Hot Projects:</h4>';
$hot_project_html .= '<div>';
$hot_project_html .= '<ol style="padding-left:25px;">';
foreach($hot_projects as $project){
    $hot_project_html .= '<li> <a href="'.$project['project_url'].'">'.$project['project_name'].' - '. $project['project_city'].'</a></li>';
}
$hot_project_html .= '</ol>';
$hot_project_html .= '</div>';


// hot project text to include in sms sent to client

$hot_project_text  = "";
$hot_project_text  .= "Hot Projects:";
$hot_project_text  .= "\n\n";
foreach($hot_projects as $project){
    $hot_project_text .= "".$project['project_name']."";
    $hot_project_text .= " - ";
    $hot_project_text .= "".$project['project_city']."";
    $hot_project_text .= " - ";
    $hot_project_text .= "".$project['project_url']."";
    $hot_project_text .="\n";
}