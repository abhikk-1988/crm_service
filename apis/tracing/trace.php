<?php

$api_key= '6e8d972da79a7d05704a57d2b46178bbe5be7b95056b4438fe014b8f9df75588';

$url = json_decode(file_get_contents("http://api.ipinfodb.com/v3/ip-city/?key=".$api_key."&format=json"));

echo "<table border='1' width='50%' align='center'><tr><td>COUNTRY:</td><td>";
echo $url->countryName;
echo "</td></tr><tr><td>CITY:</td><td>";
echo $url->cityName;
echo "</td></tr><tr><td>STATE OR REGION:</td><td>";
echo $url->regionName;
echo "</td></tr><tr><td>IP ADDRESS:</td><td>";
echo $url->ipAddress;
echo "</td></tr><tr><td>COUNTRY CODE:</td><td>";
echo $url->countryCode;
echo "</td></tr><tr><td>LATITUTE:</td><td>";
echo $url->latitude;
echo "</td></tr><tr><td>LONGITUDE:</td><td>";
echo $url->longitude;
echo "</td></tr><tr><td>TIMEZONE:</td><td>";
echo $url->timeZone;
echo "</td></tr><tr><td>Remote IP</td><td>".$_SERVER['REMOTE_ADDR']."</td></table>";

?>