<?php
	include "../lib/rain.tpl.class.php";
	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "../tpl/" );
	raintpl::configure("cache_dir", "../temp/" );

	$tpl = new RainTPL;

	require_once("../lib/init.php");
	require_once("../lib/database.class.php");
	require_once("../lib/user.class.php");

	$msg = "";
	if (isset($_POST['submit']))
	{
		// Check to see that email does not already exist!
		// Validate account details
		if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$msg = "Please enter a valid email...";
		} else if (empty($_POST['password'])) {
			$msg = "Please enter a password...";
		} else if (empty($_POST['retype'])) {
			$msg = "Please retype password...";
		} else if ($_POST['password'] != $_POST['retype']) {
			$msg = "Passwords do not match.";
		} else {
			// Form has all required, valid fields
			$usr = new User();
			if ($usr->queueNewUser($_POST['email'], $_POST['password'])) {
				$msg = "An activation email has been sent to " . $_POST['email'] . ".";
			} else {
				$msg = "There was a problem registering your account.";
			}
		}
	}


	$tpl->assign('msg', $msg);
	$html = $tpl->draw('register', $return_string = true );
	echo $html;