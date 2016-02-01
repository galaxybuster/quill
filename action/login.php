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
		// Credentials passed to the server
		$user = new User();
		$success = $user->login($_POST['email'], $_POST['pass']);
		// $user->login handles the session, so i think we're done here

		if ($success) {
			header("Location: ".$GLOBALS['config']['domain'].$GLOBALS['config']['directory']);
			die("Redirecting");
		} else {
			$msg = "Incorrect login.";
		}
	}

	// Render page as usual.
	$tpl->assign('msg', $msg);
	$html = $tpl->draw('login', $return_string = true );
	echo $html;