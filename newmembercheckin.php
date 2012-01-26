<?php

	require_once('resources/classes/class_API.php');
	require_once('resources/classes/class_user.php');

	$API = new API();
	$user = new user($_COOKIE['data']['user'], $_COOKIE['data']['name']);

	//$API->cleanup($_POST);
	$data = json_decode($_POST['data']);

	$participant_id = (string)$data->participantID;
	$group_participant_id = (string)$data->groupParticipantID;
	$event_id = (string)$data->eventID;
	$user_id = (string)$data->userID;
	$role = (string)$data->role;
	$groupID = (string)$data->groupID;

	$current_time = date("Y-m-d H:i:s", time());

	$checkin = $API->create_event_participant($participant_id, $group_participant_id, $event_id, $user_id, $current_time);

				$time_in = $API->convertDateTime($current_time);
				$time_in_date = $time_in['date'];
				$time_in_time = $time_in['time'];

	if( isset($checkin) && ($checkin != "")) {
		$response['id'] = $checkin;
		$response['success'] = true;
		$response['message'] = "Check-In successful.";
		$response['time_in'] = $time_in_date.' '.$time_in_time;
		$response['role'] = $role;
		$response['groupID'] = $groupID;
	}
	else {
		$response['id'] = 0;
		$response['success'] = false;
		$response['message'] = "Check-In failed. Please contact your IT Administrator.";
	}

	$response = json_encode($response);
	echo($response);

/* no ending ?> on purpose */