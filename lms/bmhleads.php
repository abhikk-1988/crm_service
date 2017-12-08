<?php


$challenge = $_REQUEST['hub_challenge'];
$verify_token = $_REQUEST['hub_verify_token'];

	if ($verify_token === 'bmhtest') {
		echo $challenge;
	}
	
	$ldata			=	file_get_contents('php://input');
	$input 			= json_decode($ldata, true);
	error_log(print_r($input, true));
	
	$leadgen_id 		 =  $input["entry"][0]["changes"][0]["value"]["leadgen_id"];
	$form_id 			 =  $input["entry"][0]["changes"][0]["value"]["form_id"];
	$created_time		 =  $input["entry"][0]["changes"][0]["value"]["created_time"];
	$page_id 			 =  $input["entry"][0]["changes"][0]["value"]["page_id"];
	$adgroup_id 		 =  $input["entry"][0]["changes"][0]["value"]["adgroup_id"];

	
	
	$con				=		mysql_connect('localhost','root','bmhproduction@123!');
	$selectDb			=		mysql_select_db("testmydb");
	$insert				=		"insert into fb_leads set hub_challenge ='test', inputval='test'";
	$executeQuery		=		mysql_query($insert);
	
	
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
	
	
		
		
		return  array('lead'=>$lead,"respnse"=>$leaddata);
	}

	
	
	
	function  push_to_crm($postDataval){
		
		
		
		
		
		$ch = curl_init();                   
		$url = "http://52.77.73.171/CRM/apis/save_enquiry.php"; 
		$veryString            =  "fb_enquery";
		//$postDataval        =    array();   //send a copy of enquiry data. 
		$postData           =     json_encode(array('key'=>$veryString,'postEnqueryData'=>$postDataval));
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'enquery='.$postData); // Define what you want to post
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the output in string format
		$output = curl_exec ($ch); // Execute
		curl_close ($ch); // Close cURL handle
		//var_dump($output);
	}
	
	//Take input from Facebook webhook request
	//Token - you must generate this in the FB API Explorer - tip: exchange it to a long-lived (valid 60 days) token
	
	//$user_access_token 	= 'EAAH8GunwryEBAHgVzPRIghNZBSwON8oqsrP0VqoL8qRBsgQSsmKJkxHwVEZAyprvUndZCLQ27ZCICpNoCmH7ZCgTPV1oXG254XKB8SBSNK9WRpscGEFyU7UBCew6ZAl9Sqa6JHXEIwJWSvIUDGaqYXCjC7Xn0BBK0ASqZBHeZBEEIQZDZD';
	
	
	$user_access_token 	= 'EAAH8GunwryEBAMZBZA1Wt8Ym1WGNC6EK9qRRvFRnooLdwMozUZBO5XsIzLnLtu4Xd5sxABKkZCINAdT7c5yqjRZBrch5YwjABnCgvf0fi40bZCZCCiIq84akHNrEPKYIl7HwZCux2rgC7BkNAf16h6tg72DmfZCZBTtwgZD';

	$lead = getLead($leadgen_id,$user_access_token);//get lead info

	if(count($lead['lead'])>0){
		
		push_to_crm($lead['lead']);
	
	}

	
	$jsonInput 			=		json_encode($lead);
	$insert				=	"insert into ity_leads set hub_challenge ='".$challenge."', inputval='".file_get_contents('php://input')."', leadvalujson='".$jsonInput."',form_id='".$form_id."', created_time='".$created_time."', page_id='".$page_id."',leadgen_id='".$leadgen_id."', adgroup_id='".$adgroup_id."'";
	$executeQuery			=	mysql_query($insert);
	
	
?>

<h2>My Platform</h2>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '558667500924705',
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
