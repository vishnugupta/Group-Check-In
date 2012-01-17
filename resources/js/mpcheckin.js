$(document).bind("mobileinit", function(){
	
	$("[type='submit']").button('enable');	
	
	$.extend(  $.mobile , {
	    defaultDialogTransition	: 'pop',  // mirrors the default action, but here so I can remember that I can change it
	    defaultPageTransition	: 'slide', // disables the default "slide" action
	    loadingMessage			: 'Retrieving Data', // so I can remember that I can modify later
	    pageLoadErrorMessage	: 'The requested page failed to load.'
	  });
	
});


function memberCheckIn(ID, role, groupID, eventID) {
	
	var confirmation = confirmCheckIn();
	if(confirmation == false) {
		return false;
	}
	else {
		
		var member = {};
		member.id = ID.id;
		member.role = role;
		member.groupID = groupID;
		member.eventID = eventID;
		var datastring = JSON.stringify(member);
		$.mobile.loadingMessage = "Checking In Attendee";
		$.mobile.showPageLoadingMsg();

		$.post( "membercheckin.php", { data: datastring }, 
			function(data) {
				
				var response = JSON.parse(data);
				if(response.success == true) {
	
					var member_html = "#" + response.id; // id='1'
					var member_time_in = member_html + "_time_in";	// id='1_time_in'
					$(member_html).addClass("checked_in");
					$(member_html).attr('onclick',"duplicate_check_in();");
					$(member_time_in).removeClass("hidden");
					$(member_time_in).append(" " + response.time_in);
				
					// update group checked in count based on response.groupID and response.role
					var increment_id = member.eventID + "_" + member.groupID;
					increment_group_count(increment_id, response.role);
					
					var status_span = "span" + member_html + "_status";
					//alert(status_span);
					$(status_span).text("Checked In");
				}
				else {
					alert(response.message);
				}
				$.mobile.hidePageLoadingMsg();
			}
		);
	}
}

function newMemberCheckIn(item, participantID, groupParticipantID, eventID, userID, role, groupID) {
	var confirmation = confirmCheckIn();
	if(confirmation == false) {
		return false;
	}
	else {

		var request = {};		
		request.participantID = participantID;
		request.groupParticipantID = groupParticipantID;
		request.eventID = eventID;
		request.userID = userID;
		request.returnID = item.id;
		request.role = role;
		request.groupID = groupID;
		
		var datastring = JSON.stringify(request);
		$.mobile.loadingMessage = "Checking In Attendee";
		$.mobile.showPageLoadingMsg();
		
			$.post( "newmembercheckin.php", { data: datastring }, 
			function(data) {
				var response = JSON.parse(data);
				if(response.success == true) {
					
					var member_html = "#" + request.returnID;
					var member_time_in = member_html + "_time_in";
					$(member_html).addClass("checked_in");
					$(member_html).attr('onclick',"duplicate_check_in();");
					$(member_time_in).removeClass("hidden");
					$(member_time_in).append(" " + response.time_in);
					
					var status_span = "span#" + request.eventID + "_" + request.groupParticipantID + "_status";
					//alert(status_span);
					$(status_span).text("Checked In");
					
					// update group checked in count based on response.groupID and response.role
					var increment_id = request.eventID + "_" + request.groupID;
					increment_group_count(increment_id, response.role);
	
				}
				else {
					alert(response.message);
				}
				$.mobile.hidePageLoadingMsg();
			}
		);
	}

}

function duplicate_check_in() {
	alert("This member is already checked in.");
}


function increment_group_count(id, isleader) {
	var group_span_id = "";
	
	if(isleader == "Leader") {
		group_span_id = id + "_leaders";
	}
	else {
		group_span_id = id + "_attendees";
	}
	
	var span = "span#" + group_span_id;
	var old_html = $(span).html();
	var old_int = parseInt(old_html);
	var new_int = old_int + 1;
	$(span).text(new_int);
	
	return true;
}

function confirmCheckIn() {
	var message = "Complete Check-In for this user?";
	var result = confirm(message);
	return result;
}

function testConfirmation(name) {
	var c = confirmCheckIn(name);
	$("div#processing").removeClass("hidden");
}