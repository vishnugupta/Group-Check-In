<!DOCTYPE html>
<html>
    <head>
    	<title>Log In</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.0/jquery.mobile-1.0.min.css" />
        <link rel="stylesheet" href="resources/css/mpcheckin.css" />
		<script src="resources/js/jquery-1.6.4.min.js"></script>
		<script src="resources/js/mpcheckin.js"></script>
		<script src="resources/js/jquery.mobile-1.0.min.js"></script>
		<script>
			$(document).ready(function() {
				hideLoadingScreen(); // disables the loading screen on page load

				// submits login form
				$("#submit").click(function(){
				   //$("[type='submit']").button('disable'); // disable submit button
				    var formData = $("#login_form").serialize();
				    hideLoginScreen(); // hides the login screen
				    showLoadingScreen(); // shows the authenticating animated gif
				    $.ajax({
				        type: "POST",
				        url: "login.php",
				        cache: false,
				        dataType: 'json',
				        data: formData,
				        success: onSuccess,
				        error: onError
				    });

				    return false;
				});

			});

			function onSuccess(data, status) {
				//data = $.trim(data);
				var loginsuccess = $.trim(data.success);
				var message = $.trim(data.message);

				hideLoadingScreen(); // disable loading screen after loading the data response

				if( loginsuccess == 'true') {
					//$.mobile.changePage("events.php", { reloadPage: true } ); // doesn't display back/logout buttons correctly
					$.mobile.loadingMessage = "Retrieving Events";
					$.mobile.showPageLoadingMsg();
					window.location.href="events.php";
				}

				else {
					$("#error_message").text(message);
					showLoginScreen();
				}
			}

			function onError(data, status) {
				hideLoadingScreen();
				$("#error_message").text("Your server connection failed. Please check your settings.");
				showLoginScreen();
			}

			function showLoginScreen() {
				$('#loginFormScreen').show();
				$('[type="submit"]').button('enable');
			}
			function hideLoginScreen() {
				$('#loginFormScreen').hide();
			}
			function showLoadingScreen() {
				$('#loadingScreen').show();
			}
			function hideLoadingScreen() {
				$('#loadingScreen').hide();
			}
		</script>
    </head>
    <body>
    	<div data-role="page" id="login_page" data-title="Log In" data-theme="c">
			<div data-role="header">
				<h1>Group Check-In</h1>
			</div><!-- /header -->
			<div data-role="content" id='loginFormScreen'>
				<div id='welcome'>
					<p>Welcome to Group Check-In. Log in with your MinistryPlatform credentials below to begin.
				</div>
				<div id='login'>
					<form id='login_form' method="POST" class="ui-body ui-body-b ui-corner-all">
						<fieldset>
							<div data-role="fieldcontain">
								<label for="username" class="ui-hidden-accessible">Username:</label>
								<input type="text" name="username" id="username" class="ui-body ui-br" value="" placeholder="Username"/>
							</div>
							<div data-role="fieldcontain">
								<label for="password" class="ui-hidden-accessible">Password:</label>
								<input type="password" name="password" id="password" class="ui-body ui-br" value="" placeholder="Password"/>
							</div>
							<div data-theme="b"  aria-disabled="false">
								<button type="submit" id="submit" data-theme="b" class="ui-btn-hidden">Submit</button>
							</div>
						</fieldset>
					</form>
				</div>
				<div id='error_message' class='error'></div>
			</div> <!-- end login page -->

			<!-- loading screen: only displayed while forms are submitted -->
			<div data-role="page" id="loadingScreen" data-title="Loading"  data-theme="c">
				<div data-role="header">
					<h1>Logging In..</h1>
				</div><!-- /header -->
				<div data-role="content" id="loadingScreenContent" name="loadingScreen">
					<div align="center"><h4>Authenticating...</h4></div>
					<div align="center"><img id="loading" name="loading" src="resources/images/loading.gif"/></div>
				</div>
			</div> <!-- / loading screen -->






			<!--
			<div data-role="footer">
				<h4>Page Footer</h4>
			</div>
			--><!-- /footer -->
		</div> <!-- // end page -->
    </body>
</html>