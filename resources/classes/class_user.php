<?php

require_once('class_API.php');

class user {
	/**
	 * @Variable Declarations
	 */

	function __construct($user_id, $display_name) {
		$this->user_id = $user_id;
		$this->display_name = $display_name;
	}

	function start_session() {
		// session_destroy(); // kills any session that might have carried over erroneously
		$unique_id = md5(time());
		session_id($unique_id);
		session_start();
		setcookie('logged_in',$unique_id, time()+(60*60*6) ); // 6 hours
		setcookie('data[user]', $this->user_id);
		setcookie('data[name]', $this->display_name);
	}

	function end_session() {
		$_SESSION = array();

		setcookie('logged_in',"", time()-(60*60*6) ); // expire cookie
		setcookie('data[user]', $this->user_id, time()-(60*60*6));
		setcookie('data[name]', $this->display_name, time()-(60*60*6));
		setcookie('PHPSESSID', $this->display_name, time()-(60*60*6));
		session_id("");

		session_destroy();
	}
}

/* no ending ?> on purpose */