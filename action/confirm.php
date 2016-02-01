<?php
	require_once("../lib/database.class.php");

	if (isset($_GET['key'])) {
		// Check if it exists

		$db = Database::getInstance();
		$db->query("SELECT * FROM confirm WHERE addedon = ?", array($_GET['key']));
		$result = $db->firstResult();
		if ($result != null) {
			$db->query("INSERT INTO users (email, password, salt) VALUES (?, ?, ?)", array($result['email'], $result['password'], $result['salt']));
			echo "Successfully confirmed account.";
			echo "<a href='login.php'>click here to login</a>";

			// Remove the data from confirm table
			$db->query("DELETE * FROM confirm WHERE addedon = ?", array($_GET['key']));
		} else {
			echo "Error...";
		}
	} else {
		// Nothing set, redirect to index
		header("Location: index.php");
		die("Redirecting");
	}