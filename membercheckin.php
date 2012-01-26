<?php

	require_once('resources/classes/class_API.php');
	require_once('resources/classes/class_user.php');

	$userID = $_COOKIE['data']['user'];
	$name = $_COOKIE['data']['name'];

	$API = new API();
	$user = new user($userID, $name);

	//$API->cleanup($_POST);
	$data = json_decode($_POST['data']);
	$event_participant_id = (string)$data->id;
	$role = (string)$data->role;
	$groupID = (string)$data->groupID;
	$current_time = date("Y-m-d H:i:s", time());

	$checkin = $API->member_check_in($event_participant_id, $userID, $current_time);

				$time_in = $API->convertDateTime($current_time);
				$time_in_date = $time_in['date'];
				$time_in_time = $time_in['time'];

	if($checkin == true) {
		$response['id'] = $event_participant_id;
		$response['success'] = true;
		$response['message'] = "Check-In successful.";
		$response['time_in'] = $time_in_date.' '.$time_in_time;
		$response['role'] = $role;
		$response['groupID'] = $groupID;
	}
	else {
		$response['id'] = $event_participant_id;
		$response['success'] = false;
		$response['message'] = "Check-In failed. Please contact your IT Administrator.";
	}

	$response = json_encode($response);
	echo($response);

/* no ending ?> on purpose */