<?php
	/*
	 * Copyright 2011 Google Inc.
	 *
	 * Licensed under the Apache License, Version 2.0 (the "License");
	 * you may not use this file except in compliance with the License.
	 * You may obtain a copy of the License at
	 *
	 *     http://www.apache.org/licenses/LICENSE-2.0
	 *
	 * Unless required by applicable law or agreed to in writing, software
	 * distributed under the License is distributed on an "AS IS" BASIS,
	 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 * See the License for the specific language governing permissions and
	 * limitations under the License.
	 */
	if (ini_get('register_globals') === "1") {
		die("register_globals must be turned off before using the starter application");
	}

	require_once 'google-api-php-client/src/apiClient.php';
	require_once 'google-api-php-client/src/contrib/apiPlusService.php';

	session_start();

	$client = new apiClient();
	$client->setApplicationName("Get Friends Id");
	$client->setClientId('1057327068472.apps.googleusercontent.com');
	$client->setClientSecret('EOO__L0l3dn2i97W1OHN8KT8');
	$client->setRedirectUri('http://localhost/googleplus/initialize.php');
	$client->setDeveloperKey('AIzaSyCoR7Exd0QXZsvE8q5CaLGtVnAM3RPGeQY');
	$client->setScopes(array('https://www.googleapis.com/auth/plus.me', 'https://www.google.com/m8/feeds/'));
	$plus = new apiPlusService($client);

	if (isset($_REQUEST['logout'])) {
		unset($_SESSION['access_token']);
	}

	if (isset($_GET['code'])) {
		$client->authenticate();
		$_SESSION['access_token'] = $client->getAccessToken();
		$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}

	if (isset($_SESSION['access_token'])) {
		$client->setAccessToken($_SESSION['access_token']);
	}

	if ($client->getAccessToken()) 
	{
		$me = $plus->people->get('me');
		$url = filter_var($me['url'], FILTER_VALIDATE_URL);
		$img = filter_var($me['image']['url'], FILTER_VALIDATE_URL);
		$name = filter_var($me['displayName'], FILTER_SANITIZE_SPECIAL_CHARS);
		$personMarkup = "<a rel='me' href='$url'>$name</a><div><img src='$img'></div>";
		$optParams = array('maxResults' => 750);
		$activities = $plus->activities->listActivities('109152392103989095686', 'public', $optParams);
		$activityMarkup = '';
		
########################################
		$oToken = json_decode($client->getAccessToken());
		$cAccessToken = $oToken->access_token; 
		$req="https://www.google.com/m8/feeds/contacts/default/full?max-results=750";
		$header = array( "Host: www.google.com","GData-Version: 3", "Content-length: 0", "Authorization: OAuth ".$cAccessToken );
		$ch = curl_init($req);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$data = curl_exec($ch);
		curl_close($ch);
		$xmldata  = simplexml_load_string($data);	
		
		
		$myFile = "friends.txt";
		$fh = fopen($myFile, 'w') or die("can't open file");
		$text = "<table border=\"1\">";
		foreach ($xmldata->entry as $contact) :
			// get the ID of the current album	
			$id =  $contact->children('http://schemas.google.com/contact/2008')->website;
			$mail_xml = $contact->children('http://schemas.google.com/g/2005')->email;
			$mail = $mail_xml->attributes()->{'address'};
			$loc =  $id->attributes()->{'href'} ;
			if($loc)
			{				
					$pieces = explode("/", $loc);					
					$stringData = $pieces[4] . "\n";
					fwrite($fh, $stringData);
					$text = $text . "<tr><td>" . $mail . "</td><td>" . $pieces[4] . "</td></tr>";			
			}
		endforeach;
		$text = $text . "</table>";
		fclose($fh);		
	} 
	else 
	{
		$authUrl = $client->createAuthUrl();
	}

?>

<!doctype html>
<html>
<head><link rel='stylesheet' href='style.css' /></head>
<body>
<header><h1>Google+ PhotoScanner - Initialize</h1></header>
<div class="box">
<div class="me"><?php if(isset($personMarkup))
	print $personMarkup; 
	
	?></div>
<div class="activities"> <?php
echo "<br/>".$text;
?>

<div class="box" style="top:0; position: absolute; margin-right: 0; right: 0; float: right; margin: 0px 0px; width: 250px;
">
<?php
if(isset($authUrl)) {
	print "<a class='login' href='$authUrl'>Connect Me!</a>";
} else {
	print "<a class='logout' href='?logout'>Logout</a>";
}
?>
</div>
</body>
</html>
