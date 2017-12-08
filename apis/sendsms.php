<?php


if(isset($_GET)){
    
    $number     = $_GET['number'];
    $text       = urlencode($_GET['text']);

    $url  = 'http://promotionsms.in/api/swsendSingle.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$number.'&message='.$text;
    
    // echo $url; exit;

    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 120
    ));
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);
    
    echo $resp; exit;
}

