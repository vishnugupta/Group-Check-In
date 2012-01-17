<?php 
	if( !isset( $_COOKIE["data"]["user"] ) || ( $_COOKIE["data"]["user"] == "") || (is_null($_COOKIE["data"]["user"]) ) ) {
		header('Location: index.php');
		exit();
	}
	else {
	date_default_timezone_set('America/New_York');
	
	require_once('resources/classes/class_API.php');
	require_once('resources/classes/class_user.php');
?>
<!DOCTYPE html>
<html>
    <head>
    	<title>Available Events</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.0/jquery.mobile-1.0.min.css" />
        <link rel="stylesheet" href="resources/css/mpcheckin.css" />
		<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		<script src="resources/js/mpcheckin.js"></script>
		<script src="http://code.jquery.com/mobile/1.0/jquery.mobile-1.0.min.js"></script>
		<script>
				// creates a back button on the events page for the nested lists
				$(':jqmData(url^=events_page)').live('pagebeforecreate', function(event) {
					$(this).filter(':jqmData(url*=ui-page)').find(':jqmData(role=header)').prepend('<a href="#" data-rel="back" data-icon="arrow-l">Back</a>');
				});
				
				// create logout button
				$(':jqmData(url^=events_page)').live('pagebeforecreate', function(event) {
					text = '<a id="logout_button" data-ajax="false" data-inline="true" href="logout.php" class="ui-btn-right" data-rel="logout">Log Out</a>';
					$(this).find(':jqmData(role=header)').append(text);
				});
				
				$(document).ready(function() {
					$.mobile.hidePageLoadingMsg();
				});
		</script>
    </head>
    <body>		
    	<div data-role="page" id="events_page" data-title="Available Events" data-theme='c'>
			<div data-role="header" data-theme="b">
				<h1>Available Events</h1>
			</div><!-- /header -->
			<div data-role="content" data-theme="c">
				<div id='Events' class="content-primary">
					<?php
						
						$API = new API();
						$event_list = $API->getEventData();
						echo $event_list; 
						
					?>
				</div>
			</div>
			<!--
			<div data-role="footer">
				<h4>Page Footer</h4>
			</div>
			--><!-- /footer -->
		</div> <!-- // end EVENTS page -->
		<div data-role="page" id="events_processing" class='hidden'>
    		<div data-role="header" data-theme="b">
				<h1>Loading</h1>
			</div><!-- /header -->
			<div data-role="content" data-theme="c">
				<div align="center"><h4>Processing</h4></div>
				<div align="center"><img name="processing image" src="resources/images/loading.gif"/></div>
			</div>
		</div><!-- end processing page -->
    </body>
</html>
<?php } ?>