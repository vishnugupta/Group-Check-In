<?php

class API {
	/**
	 * @Variable Declarations
	 */

	public $wsdl; // replace with your URL
	public $guid; // your guid here
	public $pw; // your password here
	public $servername; // reference the domain table while logged in as 'SETUP'
	public $hours; // provides an upper limit on events. All events with a a start date up to __ hours in the future
	public $client; // creates the soapClient as a variable
	public $params; //
	public $row; // all EVENT HTML data
	public $securityRoleID; // security role ID for Group check-in

	function __construct() {

		/*
		 * @import config values
		 * pulls in config values from /config/config.php. Allows for app udpates w/o overwriting these values
		 */

		require_once('config/config.php');
		/*
		 * these don't change
		 * do NOT place the @new soapClient() in the construct method or the connection will never close
		 */

		$context = stream_context_create(
						array(
					    	'http' => array('header' => "Connection: close")
					    	//'http' => array('header' => "apikey: this-must-still-be-here")
					    )
					);

		$this->params['trace'] = true;
		$this->params['exceptions'] = 1;
		$this->params['stream_context'] = $context;

		$this->row = "";
	}

	function cleanup($var) {
		?><pre><?
		var_dump($var);
		?></pre><?
	}

	/*
	 * @convertDateTime
	 * creates separate date and time variables for each date
	 */

	public function convertDateTime($var) {
		$date = strftime("%m/%d/%Y", strtotime($var));
		$time = strftime("%R %p", strtotime($var));

		$result = array(
			'date' => $date,
			'time' => $time
		);
		return($result);
	}

	/**
	 * @User Authentication
	 */

	function authenticate_user($user, $userpassword) {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}

		$fields = array(
					'UserName' 		=> $user,
					'Password' 		=> $userpassword,
					'ServerName' 	=> $this->servername
				);

		$request = $this->client->__soapCall('AuthenticateUser', array('parameters' => $fields));
		return $request;
		unset($request); // clear the object to free memory
	}

	/**
	 * @Retrieve User Security Role
	 */

	function getSecurityRole($userID) {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}

		$sp = "api_GetUserRoles";
		$request = "UserID=".$userID;

		$params = array(
			'GUID' => $this->guid,
			'Password' => $this->pw,
			'StoredProcedureName' => $sp,
			'RequestString' => $request
		);

		$response = $this->client->__soapCall('ExecuteStoredProcedure', array('parameters' => $params));

		$data = simplexml_load_string($response->ExecuteStoredProcedureResult->any);
		$roles = $data->NewDataSet;
		$auth = false; // defaults authorization to false

		foreach($roles->Table as $role) {
			$id = (string)$role->Role_ID;
			if($id == $this->securityRoleID) {
				$auth = true;
			}
			//$this->cleanup($auth);
		}

		return $auth;
		unset($response); // clear the object to close the API connection
	}


	/**
	 * @Retrieve Events
	 */

	public function getEventData() {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}

		$from_date = date('m/d/Y');
		$through_date = date('m/d/Y', strtotime('+' . $this->hours . ' hours'));

		$sp = "api_GroupCheckIn_GetCheckInEvents";
//		$request = "ShowEventsFromTime=N'".$from_date."'&ShowEventsThroughTime=N'".$through_date."'"; // with quotes on dates
		$request = "ShowEventsFromTime=".$from_date."&ShowEventsThroughTime=".$through_date; // without quotes on dates


		$params = array(
			'GUID' => $this->guid,
			'Password' => $this->pw,
			'StoredProcedureName' => $sp,
			'RequestString' => $request
		);

		$response = $this->client->__soapCall('ExecuteStoredProcedure', array('parameters' => $params));
		$data = simplexml_load_string($response->ExecuteStoredProcedureResult->any);
		$events_object = $data->NewDataSet;
		//$this->cleanup($data);

		$event_html = $this->process_events($events_object);

		return $event_html; // return the list of events
		unset($response); // clear the object to close the API connection
	}

	function getGroups($eventID) {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}

		$requeststring = "EventID=$eventID";

		$params = array(
			'GUID' => $this->guid,
			'Password' => $this->pw,
			'StoredProcedureName' => "api_GroupCheckIn_GetGroupsByActivity",
			'RequestString' => $requeststring
		);

		$response = $this->client->__soapCall('ExecuteStoredProcedure', array('parameters' => $params));
		$data = simplexml_load_string($response->ExecuteStoredProcedureResult->any);
		$groups_object = $data->NewDataSet;

		return($groups_object);
		unset($response); // clear the object to close the API connection
	}

	public function getMembers($eventID, $groupID) {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}

		$params = array(
			'GUID' => $this->guid,
			'Password' => $this->pw,
			'StoredProcedureName' => "api_GroupCheckIn_GetGroupMembers",
			'RequestString' => "EventID=" . $eventID . "&GroupID=" . $groupID
		);

		$response = $this->client->__soapCall('ExecuteStoredProcedure', array('parameters' => $params));
		$data = simplexml_load_string($response->ExecuteStoredProcedureResult->any);
		$members_object = $data->NewDataSet;

		return($members_object);
		unset($response); // clear the object to close the API connection
	}


	function create_event_participant($participant_id, $group_participant_id, $event_id, $uid, $current_time) {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}


		$RequestString = "Event_ID=" . $event_id; // event ID
		$RequestString .= "&Participation_Status_ID=3"; // status = attended
		$RequestString .= "&Participant_ID=" . $participant_id;
		$RequestString .= "&Notes=Created By Group Check-In.";
		$RequestString .= "&Time_In=" . $current_time;
		$RequestString .= "&Group_Participant_ID=" . $group_participant_id;

		//echo $RequestString;

		$params = array(
			'GUID'				=> $this->guid,
			'Password'			=> $this->pw,
			'UserID'			=> $uid,
			'TableName'			=> "Event_Participants",
			'PrimaryKeyField'	=> "Event_Participant_ID",
			'RequestString'		=> $RequestString
		);

		$request = $this->client->__soapCall('AddRecord', array('parameters' => $params));
		$response = $request->AddRecordResult;
		$response = explode("|",$response); // separates the pipe delimited response string into an array
		return $response[0]; // new event participant id

		unset($request); // clear the object to close the API connection
	}


	function member_check_in($EventParticipantID, $UserID, $current_time) {
		try {
			$this->client = @new SoapClient($this->wsdl, $this->params);
		}
		catch(SoapFault $soap_error) {
			echo $soap_error->faultstring;
		}

		$params = array(
			'GUID'				=> $this->guid,
			'Password'			=> $this->pw,
			'UserID'			=> $UserID,
			'TableName'			=> "Event_Participants",
			'PrimaryKeyField'	=> "Event_Participant_ID",
			'RequestString'		=> "Time_In=" . $current_time . "&Event_Participant_ID=".$EventParticipantID."&Participation_Status_ID=3"
		);

		$request = $this->client->__soapCall('UpdateRecord', array('parameters' => $params));
		$response = $request->UpdateRecordResult;
		$response = explode("|",$response); // separates the pipe delimited response string into an array

		if ($response[0] == -1) {
			return true;
		}
		else {
			return false;
		}
		unset($request); // clear the object to close the API connection
	}

	function process_events($events) {
		$this->row .= '<!-- begin Event List -->';
		$this->row .= '<ul data-role="listview" data-filter="true" id="events-list" class="events_list">';

		if( empty($events) ) {
			$this->row .= '<li>No Events Found.';
		}

		else {
			foreach($events->Table as $event){ // retrieve events
				$eventID = (string)$event->RecordID;
				$eventDescription = (string)$event->RecordDescription;
				$eventTitle = (string)$event->Event_Title;
				$eventCongregation = (string)$event->Congregation_Name;
				$eventMinistry = (string)$event->Ministry_Name;
				$eventProgram = (string)$event->Program_Name;
				$eventStartDateTime = $this->convertDateTime((string)$event->Event_Start_Date);
					$eventStartDate = $eventStartDateTime['date'];
					$eventStartTime = $eventStartDateTime['time'];
				$eventEndDateTime = (string)$event->Event_End_Date;
					$eventEndDateTime = $this->convertDateTime($eventEndDateTime);
					$eventEndDate = $eventEndDateTime['date'];
					$eventEndTime = $eventEndDateTime['time'];
				$eventEarlyStart = (string)$event->EarlyCheckinStart;
					if ( !empty($eventEarlyStart) && ( $eventEarlyStart != "" ) )  {
						$eventEarlyStart = $this->convertDateTime($eventEarlyStart);
						$earlyStartDate = $eventEarlyStart['date'];
						$earlyStartTime = $eventEarlyStart['time'];
					}
				$eventEarlyStop = (string)$event->EarlyCheckinStop;
					if ( !empty($eventEarlyStop) && ( $eventEarlyStop != "" ) )  {
						$eventEarlyStop = $this->convertDateTime($eventEarlyStop);
						$earlyStopDate = $eventEarlyStop['date'];
						$earlyStopTime = $eventEarlyStop['time'];
					}

				$this->row .= '<li><div class="event_title">'.$eventTitle.'</div>';
				$this->row .= '<div class="secondary-list-content">';
					$this->row .= '<div class="congregation">'.$eventCongregation.'</div>';
					$this->row .= '<div class="startdate">'.$eventStartDate.'</div>';
					$this->row .= '<div class="time">'. $eventStartTime . ' - '. $eventEndTime .'</div>';
				$this->row .= '</div>';

					$group_list = $this->getGroups($eventID); // retrieve list of groups for event
						$this->row .= '<!-- group list -->';
						$this->row .= '<ul data-role="listview" data-filter="true" class="groups_list">';
							foreach($group_list->Table as $group) { // iterate through groups
									$this->process_groups($eventID, $group);
							} // end group loop
						$this->row .= '</ul><!-- end group? -->';

			}// end event loop
		}//end "else" statement
		$this->row .= '</li></ul>';

		return $this->row;
	} // end function 'process events'

	function process_groups($eventID, $group) {
		$groupID = (string)$group->Record_ID;
		$groupDescription = (string)$group->Description;

		$member_list = $this->getMembers($eventID, $groupID); // gets the member list for each group
		$group_details = $this->get_leader_data($member_list); // gets counts of # of attendees, checked-in, leaders, etc.

		if( $group_details['total'] > 0 ) {
			$this->row .= '<li id="'.$groupID.'"><div>'.$groupDescription.'</div>';
			$this->row .= '<div class="secondary-list-content">';
			$this->row .= '<div class="total_members">Leaders: '.$group_details['leaders'].' | Attendees: '.$group_details['participants'].'</div>';
			$this->row .= '<div class="total_checked_in">Leaders: <span id="'.$eventID.'_'.$groupID.'_leaders">'.$group_details['leaders_checked_in'].'</span> | Attendees: <span id="'.$eventID.'_'.$groupID.'_attendees">'.$group_details['participants_checked_in'].'</span></div>';
			$this->row .= '</div>';

				$this->row .= '<ul data-role="listview" data-filter="true" class="members_list">';
					foreach($member_list->Table as $member) {
						$this->process_members($member, $eventID);
					} // end member loop
				$this->row .='</ul></li>'; // end members <ul> element
		}
	}

	function process_members($member, $eventID) {
		//$this->cleanup($member);
		$checkin_class = "";
		$checkin_onclick = "";
		//$eventID = (string)$member->Event_ID;// echo $eventID . "<br />";
		$groupID = (string)$member->Group_ID;
		$contactID =  (string)$member->Contact_ID;
		$participantID =  (string)$member->Participant_ID;
		$eventParticipantID =  (string)$member->Event_Participant_ID;
		$groupParticipantID =  (string)$member->Group_Participant_ID;
		$ParticipationStatusID =  (string)$member->Participation_Status_ID;
		$displayName =  (string)$member->Display_Name;
		$roleTitle = (string)$member->Role_Title;
		$groupRoleType = (string)$member->Group_Role_Type;
		$time_in = (string)$member->Time_In;

			switch($ParticipationStatusID) {
				case 1:
					$member_status = "Interested";
					break;
				case 2:
					$member_status = "Registered";
					break;
				case 3:
					$member_status = "Attended";
					break;
				case 4:
					$member_status = "Confirmed";
					break;
				case 5:
					$member_status = "Cancelled";
					break;
				default:
					$member_status = "Registered";
			}

			if($groupID != 0) {
				if($eventParticipantID == "") {
					$eventParticipantID = $eventID. "_" .$groupParticipantID;
					//$eventParticipantID = "0_" .$groupParticipantID;
					$roleTitle = "Unregistered";
				}
			}

			if ( !empty($time_in) && ( $time_in != "" ) )  {
				$time_in_verified = true;
				$time_in = $this->convertDateTime($time_in);
				$time_in_date = $time_in['date'];
				$time_in_time = $time_in['time'];
				$checkin_class = 'class="checked_in"';
			}
			else {
				$checkin_class = 'class=""'; // only adds onclick attribute for people not checked in
				$checkin_onclick = 'onclick="memberCheckIn(this, \''.$groupRoleType.'\', \''.$groupID.'\', \''.$eventID.'\');"';
			}
		if($eventParticipantID == ($eventID . "_" . $groupParticipantID) ) {
			$parameters = "this, '".$participantID."', '".$groupParticipantID."', '".$eventID."', '".$_COOKIE['data']['user']."', '".$groupRoleType."', '".$groupID."'";
			//echo $parameters.'</br>';
			$checkin_onclick = 'onclick="newMemberCheckIn('.$parameters.');"';
		}


		$this->row .= '<li id="'.$eventParticipantID.'" '.$checkin_class.' '.$checkin_onclick.'><div>'.$displayName.'</div>';
		$this->row .= '<div class="secondary-list-content">';
			$this->row .= '<div class="role_type">Role: '.$groupRoleType.'</div>';
			// $this->row .= '<div class="participant_type">Status: <span id="'.$eventParticipantID.'_status">'.$roleTitle.'</span></div>';
			$this->row .= '<div class="participant_type">Status: <span id="'.$eventParticipantID.'_status">'.$member_status.'</span></div>';
			if($time_in_verified == true) {
				$this->row .= '<div id="'.$eventParticipantID.'_time_in" class="role_type">Time In: '.$time_in_date.' '.$time_in_time.'</div>';
			}
			else {
				$this->row .= '<div id="'.$eventParticipantID.'_time_in" class="role_type hidden">Time In: </div>';
			}
		$this->row .= '</div>';
		$this->row .= '</li>';
		/*
		 * TODO: consider adding additional data that displays check-in status, etc.
		 */
	}

	function get_leader_data($members) {
		$results = array(
			'leaders'					=> 0,
			'leaders_checked_in'		=> 0,
			'participants'				=> 0,
			'participants_checked_in'	=> 0,
			'total'						=> 0
		);

		foreach($members->Table as $member){
			$results['total'] ++; // gets a total count of members in this group
			if($member->Group_Role_Type == "Leader") {
				$results['leaders']++;
				if(isset($member->Time_In) && ($member->Time_In != "")) {
					$results['leaders_checked_in']++;
				}
			}
			else {
				$results['participants']++;
				if(isset($member->Time_In) && ($member->Time_In != "")) {
					$results['participants_checked_in']++;
				}
			}
		}
		return $results;
	}
}

?>