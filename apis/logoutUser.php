<?php
session_start();
// Identify user session 

if(isset($_SESSION['currentUser']) && !empty($_SESSION['currentUser'])){
    
    // Now logout user 
    
    unset($_SESSION['currentUser']);
    
    // remove all session variables
    session_unset(); 

    // destroy the session 
    session_destroy(); 
    echo 1; exit;
}else{
    echo 0; exit;
}