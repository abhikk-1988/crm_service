<?php
include_once('ip2locationlite.class.php');
 
$api_key= '6e8d972da79a7d05704a57d2b46178bbe5be7b95056b4438fe014b8f9df75588';

//Set geolocation cookie
if(!$_COOKIE["geolocation"]){
  $ipLite = new ip2location_lite;
  $ipLite->setKey($api_key);
 
  $visitorGeolocation = $ipLite->getCountry();
  if ($visitorGeolocation['statusCode'] == 'OK') {
    $data = base64_encode(serialize($visitorGeolocation));
    setcookie("geolocation", $data, time()+3600*24*7); //set cookie for 1 week
  }
}else{
  $visitorGeolocation = unserialize(base64_decode($_COOKIE["geolocation"]));
}
 
echo '<pre>';
print_r($visitorGeolocation);