<?php

$html  = '';
$html .= '<div>';
$html .= '<ol style="padding-left:25px;">';
foreach($inquired_projects as $project){
    $html .= '<li> <a href="'.$project['project_url'].'">'.$project['project_name'].' - '. $project['project_city'].'</a></li>';
}
$html .= '</ol>';
$html .= '</div>';