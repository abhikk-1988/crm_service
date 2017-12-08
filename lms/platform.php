<script>
/*
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '637556229778233',
      xfbml      : true,
      version    : 'v2.8'
    });
    FB.AppEvents.logPageView();
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
 */ 
</script>
<?php 
// get leads data..
	function getLead($leadgen_id,$user_access_token)
	{
		
		echo $graph_url= 'https://graph.facebook.com/v2.7/'.$leadgen_id."?access_token=".$user_access_token;
		
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
	
	
	$user_access_token			=	"EAACOeQ2mBqMBALLr7upUF0sJuCCsaJDxALb1I3xj2JduxY5m3mYi3Kd7WQko0oa4DHnlC7Bqdnjtcm0iHDKOuwPZC7SDkMFIOadlDuZAmz8WNLysQZAUduZCiKUEUPCzUPfZB04HUDZAMOSWVdBACiEAxvCW5u9g4ZD
";	
	
	$leadData 	= getLead('748251368661201',$user_access_token);	

	$leadData	=	json_encode($leadData);

	echo "<pre>";
	print_r($leadData);
	
	
?>

