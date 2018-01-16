<?php
require_once 'vendor/autoload.php';
$client_id = 'ed270853f87d97aefe264bc10d0027374e785ae7';
$Email_address = 'sheep-pro@imposing-gadget-185211.iam.gserviceaccount.com';
$key_file_location = './SheepTimer-ed270853f87d.json';	

function checkServiceAccountCredentialsFile()
{
	$key_file_location = './SheepTimer-ed270853f87d.json';	
  	// service account creds
  	$application_creds = $key_file_location;
  	return file_exists($application_creds) ? $application_creds : false;
}


/************************************************	
 ************************************************/
	// $client_id = 'ed270853f87d97aefe264bc10d0027374e785ae7';
	// $Email_address = 'sheep-pro@imposing-gadget-185211.iam.gserviceaccount.com';	 
	// $key_file_location = './SheepTimer-ed270853f87d.json';	

	// $client = new Google_Client();
	// $client->setAuthConfig($key_file_location); 
	// $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);	




/************************************************
  Make an API request authenticated with a service
  account.
 ************************************************/
$client = new Google_Client();
/************************************************
  ATTENTION: Fill in these values, or make sure you
  have set the GOOGLE_APPLICATION_CREDENTIALS
  environment variable. You can get these credentials
  by creating a new Service Account in the
  API console. Be sure to store the key file
  somewhere you can get to it - though in real
  operations you'd want to make sure it wasn't
  accessible from the webserver!
  Make sure the Books API is enabled on this
  account as well, or the call will fail.
 ************************************************/
  define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));

if ($credentials_file = checkServiceAccountCredentialsFile()) {
  // set the location manually
  $client->setAuthConfig($credentials_file);
} elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
  // use the application default credentials
  $client->useApplicationDefaultCredentials();
} else {
  echo missingServiceAccountDetailsWarning();
  return;
}



$client->setApplicationName("Client_Library_Examples");
$client->setScopes(SCOPES);
$client->setAccessType('offline');

// echo "<pre>";
// print_r($client);


$service = new Google_Service_Calendar($client);


$calendarId = 'en.indian#holiday@group.v.calendar.google.com';
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => TRUE,
  'timeMin' => date('c'),
);


try {
 	$results = $service->events->listEvents($calendarId, $optParams);
  if (count($results->getItems()) == 0) {
  print "No upcoming events found.\n";
} else {
  print "Upcoming events:\n";
  foreach ($results->getItems() as $event) {
    $start = $event->start->dateTime;
    if (empty($start)) {
      $start = $event->start->date;
    }
    printf("%s (%s)\n", $event->getSummary(), $start);
  }
  }
}
	catch (Exception $e) {
	echo $e->getMessage();
}
//print_r($results);
?>

</html>	