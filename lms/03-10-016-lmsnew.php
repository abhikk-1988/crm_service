<?php

$challenge = $_REQUEST['hub_challenge'];
$verify_token = $_REQUEST['hub_verify_token'];

if ($verify_token === 'testtoken') {
  echo $challenge;
}

$ldata		=	file_get_contents('php://input');

$input 		= json_decode($ldata, true);
error_log(print_r($input, true));

$leadgen_id = $input["entry"][0]["changes"][0]["value"]["leadgen_id"];

function getLead($leadgen_id,$user_access_token)
{
	
    //fetch lead info from FB API
    $graph_url= 'https://graph.facebook.com/v2.7/'.$leadgen_id."?access_token=".$user_access_token;
    
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $graph_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
 
	$output = curl_exec($ch); 
    curl_close($ch);
    
	//work with the lead data
    $leaddata 			= json_decode($output);
    $lead 				= [];
   
    for($i=0;$i<count($leaddata->field_data);$i++)
	{
       
	   $lead[$leaddata->field_data[$i]->name]	=	$leaddata->field_data[$i]->values[0];
    }
	
	return $lead;
}

//Take input from Facebook webhook request

//$input = json_decode(file_get_contents('php://input'),true);
//$leadgen_id = $input["entry"][0]["changes"][0]["value"]["leadgen_id"];
//Token - you must generate this in the FB API Explorer - tip: exchange it to a long-lived (valid 60 days) token

$user_access_token 	= 'EAAXGNVvoxOcBAGHpnzk1GNn5cAjvVl2gvIfZAzBWUtyVfDZCSk8ofsKRfVrOQI0c1f2TUzF9WJPjFd00BFu6xqjjLSJpzs2xud0NJ7vIKk7ADv5JhcZAanBmjAd6WWQJ3QRDwYyRhIrrufv5IuOrLcqdpGbKcONdIsIU6egnwZDZD';
//$leadgen_id			= '1070882733031543';
//Get the lead info

$lead = getLead($leadgen_id,$user_access_token);//get lead info

//print_r($lead);


$jsonInput 			=		json_encode($lead);

$con				=		mysql_connect("localhost","root","itasku@123!");
$selectDb			=		mysql_select_db("itaskyou");


$insert				=	"insert into ity_leads set hub_challenge ='".$challenge."', inputval='".file_get_contents('php://input')."', leadvalujson='".$jsonInput."'";


//$insert				=	"insert into ity_leads set hub_challenge ='asif', inputval='asd'";
$executeQuery		=	mysql_query($insert);


?>

<h2>My Platform</h2>

<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '1625307361101031',
      xfbml      : true,
      version    : 'v2.7'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));

  function subscribeApp(page_id, page_access_token) {
    console.log('Subscribing page to app! ' + page_id);
    FB.api(
      '/' + page_id + '/subscribed_apps',
      'post',
      {access_token: page_access_token},
      function(response) {
      console.log('Successfully subscribed page', response);
    });
  }

  // Only works after `FB.init` is called
  function myFacebookLogin() {
    FB.login(function(response){
      console.log('Successfully logged in', response);
      FB.api('/me/accounts', function(response) {
        console.log('Successfully retrieved pages', response);
        var pages = response.data;
        var ul = document.getElementById('list');
        for (var i = 0, len = pages.length; i < len; i++) {
          var page = pages[i];
          var li = document.createElement('li');
          var a = document.createElement('a');
          a.href = "#";
          a.onclick = subscribeApp.bind(this, page.id, page.access_token);
          a.innerHTML = page.name;
          li.appendChild(a);
          ul.appendChild(li);
        }
      });
    }, {scope: 'manage_pages'});
  }
</script>

<button onclick="myFacebookLogin()">Login with Facebook</button>
<ul id="list"></ul>