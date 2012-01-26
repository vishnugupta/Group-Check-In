<?php
	require_once('resources/classes/class_user.php');
	$user = new user($_COOKIE['data']['user'], $_COOKIE['data']['name']);
	$user->end_session();
	header('Location: index.php');
	exit();

/* no ending ?> on purpose */