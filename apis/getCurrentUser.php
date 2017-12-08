<?php

/**
 * Session API
 */
session_start();
if( isset($_SESSION['currentUser']) && !empty($_SESSION['currentUser'])){
    echo json_encode($_SESSION['currentUser'], true); exit;
}else{
    echo json_encode(array(), true); exit;
}