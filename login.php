<?php

//require_once('resources/config.php'); // contains API connection info and general functions
require_once('resources/classes/class_API.php');
require_once('resources/classes/class_user.php');

$API = new API();

/*
 * Store POST vars
 */

// $response = new array(); // initialize the response array
$user = $_POST['username'];
$userpassword = $_POST['password'];

//echo "User: $user, Password: $userpassword";
// cleanup($_POST);

$request = $API->authenticate_user($user, $userpassword);

$userID = $request->UserID;
$displayname = $request->DisplayName;
$userEmail = $request->ContactEmail;
$contact_id = $request->ContactID;
$user_guid = $request->UserGUID;

if ($userID > 0) { // success
	$isAuthorized = $API->getSecurityRole($userID);

	if($isAuthorized == true) {
		/*
		 * create user class, session, and return "true", allowing events page to load
		 */
		$user = new user($userID, $displayname);
		$user->start_session();	// user class created
		$response['success'] = true;
		$response['message'] = "Successfully logged in as $displayname.";
	}
	else {
		$response['success'] = false;
		$response['message'] = "You successfully logged in, but your user account lacks rights to access this application.";
	}
}
else { // failed

	// $response = "Login failed.";

	$response['success'] = false;
	$response['message'] = "Your username or password was not recognized.";
}

$response = json_encode($response);
print_r($response);

?>