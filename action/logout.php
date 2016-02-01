<?php
	session_destroy();
	header("Location: ".$GLOBALS['config']['domain'].$GLOBALS['config']['directory']);
	die("Redirecting");