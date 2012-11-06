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
	$client->setApplicationName("Google+ PHP Starter Application");
	$client->setClientId('583952610464-skm0ipo4hms1qleut4jinp106fnf0daf.apps.googleusercontent.com');
	$client->setClientSecret('FijubFKkMgXvH-UQV4SfEypV');
	$client->setRedirectUri('http://localhost/googleplus/index.php');
	$client->setDeveloperKey('AIzaSyDiz5RmJmrz09Oxd8e7J8uDXXhmXJ_EJ3M');
	$client->setScopes(array('https://www.googleapis.com/auth/plus.me', 'https://picasaweb.google.com/data/'));
	$plus = new apiPlusService($client);

	if (isset($_REQUEST['logout'])) {
		$files = glob('images/*'); // get all file names
		foreach($files as $file) // iterate files
		{
		  	if(is_file($file))
				unlink($file); // delete file
		}
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
		$myFile = "updated.txt";
		$fh = fopen($myFile, 'r');
		$last_time = fgets($fh);
		fclose($fh);
		


		$users = array();
		$handle = @fopen("friends.txt", "r");
		if ($handle)
		{
			while (($buffer = fgets($handle, 4096)) !== false) 
			{
				array_push($users, trim($buffer));
			}
			if (!feof($handle)) 
			{
				echo "Error: unexpected fgets() fail\n";
		 	}
			fclose($handle);
		}

		$content = "";
		//Getting albums and displaying the photos
		foreach($users as $user_id)
		{			
			//$user_id="default";
			$oToken = json_decode($client->getAccessToken());
			$cAccessToken = $oToken->access_token; 

			// Sending CURL request to get list of Albums for user
			$album_feed = 'https://picasaweb.google.com/data/feed/api/user/'.$user_id.'/';
			$header = array( "Host: picasaweb.google.com","Gdata-version: 2", "Content-length: 0", "Authorization: OAuth ".$cAccessToken );
			$ch = curl_init($album_feed);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$data = curl_exec($ch);
			curl_close($ch);

			// Downloading images in all Google Plus Albums
			$albums  = simplexml_load_string($data);	
			print $albums;
			/*foreach ($albums->entry as $album)
			{			
				//get updated timee of album
				$album_modtime = $album->children('http://www.w3.org/2005/Atom')->updated;
				$pieces = explode("T", $album_modtime);
				$pieces2 = explode(".", $pieces[1]);
				$album_modtime = $pieces[0] . " " . $pieces2[0];
				
				// get the ID of the current album	
				$album_type =  $album->children('http://schemas.google.com/photos/2007')->albumType;
				// get photos for this album if its google plus
				if($album_type == "Buzz" && $album_modtime > $last_time)
				{
					$album_id = $album->children('http://schemas.google.com/photos/2007')->id;	
					$content .= "Album ID : " . $album_id . " <br/>";
					$photo_feed = 'https://picasaweb.google.com/data/feed/api/user/'.$user_id.'/albumid/'.$album_id.'?imgmax=d';
					$pch = curl_init($photo_feed);
					curl_setopt($pch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($pch, CURLOPT_HTTPHEADER, $header);
					$photo_data = curl_exec($pch);
					curl_close($pch);
					$photos  = simplexml_load_string($photo_data);
					foreach ($photos->entry as $photo)
					{
						//print $photo->children('http://schemas.google.com/photos/2007');
						$media = $photo->children('http://search.yahoo.com/mrss/');
						$mediagroup =  $media->group;
						$loc =  $mediagroup->content->attributes()->{'url'} ;
						$content .= "<img src='" . $mediagroup->thumbnail[0]->attributes()->{'url'} . "' /><br/><br/>";
						$filename =  basename($loc);
						echo $loc;
						file_put_contents("images/".$filename, file_get_contents($loc));
					}
				}
			}*/
			// The access token may have been updated lazily.
			$_SESSION['access_token'] = $client->getAccessToken();
		}
		$command = "python decode.py 2>&1";
		$pw_content = "";
		$pid = popen( $command,"r");
		while( !feof( $pid ) )
		{
			 $pw_content .= fread($pid, 256);
			 flush();
			 ob_flush();
			 usleep(100000);
		}
		pclose($pid);
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
<header><h1>Google+ PhotoScanner</h1></header>
<div class="box">	
	<div class="activities"> 
		<?php			
			print 
$pw_content."<br/>";
			echo $content."<br/>" ;
		?>
	</div>
</div>
<div class="box" align="right" style="top:0; position: absolute; margin-right: 0; right: 0; float: right; margin: 0px 0px; width: 250px;">
	<div class="me">
		<?php 
			if(isset($personMarkup))
				print $personMarkup;
		?>
	</div>
	<?php		
		if(isset($authUrl))
		{
			print "<a class='login' href='$authUrl'>Connect Me!</a>";
		} 
		else
		{
			print "<a class='logout' href='?logout'>Logout</a>";
		}
	?>
</div>
</body>
</html>
