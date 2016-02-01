<?php
	/*
	 * This page is compose.php
	 *
	 * This is the page that allows users to write posts.
	 *
	*/
	include "../lib/rain.tpl.class.php";
	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "../tpl/" );
	raintpl::configure("cache_dir", "../temp/" );

	$tpl = new RainTPL;

	require_once("../lib/init.php");
	require_once("../lib/database.class.php");

	include "../lib/Parsedown.php";
	$Parsedown = new Parsedown();


	// First, we want to make sure the user is authenticated
	$auth = isset($_SESSION['user']);

	$formTitle = "";
	$formImage = "";
	$formBody = "";
	$formSlug = "";
	$formTags = "";
	$formEdit = "";
	$formID = "";

	$msg = "";

	if ($auth) {
		$db = Database::getInstance();

		// This page also acts as form submit
		if (isset($_POST['submit'])) {
			// Some data was sent. Lets handle it
			if (isset($_POST['isEdit'])) {
				if ($_POST['isEdit'] == "1") {
					// This was an edited post. 
					if (isset($_POST['loadID'])) {
						$required = array('title', 'content', 'slug');
						// Loop over field names, make sure each one exists and is not empty
						$error = false;
						foreach($required as $field) {
							if (empty($_POST[$field])) {
								$error = true;
							}
						}

						if (!$error) {
							$db->query("UPDATE posts SET title=?, image=?, content=?, urlslug=?, lastedit=now() WHERE id=?",
								array($_POST['title'], $_POST['image'], $_POST['content'], $_POST['slug'], $_POST['loadID']));

							parseTags($_POST['loadID'], $_POST['tags']);

							if ($db->error()) {
								$msg = "Error updating post";
							} else {
								$msg = "Successfully updated post. <a href='".$GLOBALS['config']['domain'].$GLOBALS['config']['directory'].$_POST['slug']."'>Click here</a> to see it.";
							}
						} else {
							$msg = "Missing required field.";
							$formTitle = $_POST['title'];
							$formImage = $_POST['image'];
							$formBody = $_POST['content'];
							$formSlug = $_POST['slug'];
							$formTags = $_POST['tags'];
							$formEdit = "1";
							$formID = $_POST['loadID'];
						}
					} else {
						$msg = "Error updating post: unknown post";
					}
				} else {
					// Not editing, post as normal
					$required = array('title', 'content', 'slug');
					// Loop over field names, make sure each one exists and is not empty
					$error = false;
					foreach($required as $field) {
						if (empty($_POST[$field])) {
							$error = true;
						}
					}

					if (!$error) {
						$db->query("INSERT INTO posts(title, image, content, urlslug, date, lastedit) VALUES (?, ?, ?, ?, now(), now())",
							array($_POST['title'], $_POST['image'], $_POST['content'], $_POST['slug']));
						if ($db->error()) {
							$msg = "Error submitting new post.";
						} else {
							// actual post was saved. let's save those tags along with it.

							// Query up to get the ID we just saved
							$db->query("SELECT id FROM posts WHERE urlslug=?", array($_POST['slug']));
							$thisPostID = $db->firstResult();
							if (parseTags($thisPostID['id'], $_POST['tags'])) {
								$msg = "Successfully created post. <a href='".$GLOBALS['config']['domain'].$GLOBALS['config']['directory'].$_POST['slug']."'>Click here</a> to see it.";
							} else {
								$msg = "Error submitting new post tags.";
							}

						}
					} else {
						$msg = "Missing required field.";
						// Set these vars so they dont get mad that their business is empty
						$formTitle = $_POST['title'];
						$formImage = $_POST['image'];
						$formBody = $_POST['content'];
						$formSlug = $_POST['slug'];
						$formTags = $_POST['tags'];
					}
				}				
			} else {
				$msg = "edit mode not set";
			}
		} else {
			// No data send. Just show the form.

			// Check if we editing or not
			if (isset($_GET['isEdit'])) {
				if (isset($_GET['loadID'])) {
					// Load the current post information
					$db->query("SELECT * FROM posts WHERE id=? LIMIT 1", array($_GET['loadID']));
					$post = $db->firstResult();
					// Load up the tag info
					$db->query("SELECT * FROM posts_tags INNER JOIN tags ON posts_tags.tag_id = tags.id WHERE post_id=?", array($post['id']));
					$r = $db->result();
					//var_dump($r);
					$tagsStr = "";
					for ($k = 0; $k < sizeof($r); $k++) {
						$tagsStr .= $r[$k]['tagname'] . ", ";
					}
					if ($db->error()) {
						$msg = "Error fetching post contents.";
					} else {
						// No problems getting the info, dump that data into the form.
						$formTitle = $post['title'];
						$formImage = $post['image'];
						$formBody = $post['content'];
						$formSlug = $post['urlslug'];
						$formTags = $tagsStr;
						$formEdit = "1";
						$formID = $post['id'];
					}
				} else {
					$msg = "Error: unspecified edit id";
				}
			} else if (isset($_GET['slug'])) {
				// Load the form with a specific slug
				$formSlug = $_GET['slug'];
			}
			// If this is not set then its a new post form directly, just load the empty form
		}

	} else {
		// Dump visitor to sign in (or maybe just index)
		header("Location: ".$GLOBALS['config']['domain'].$GLOBALS['config']['directory']);
		die("Redirecting");
	}







	function loadTagsReadable() {
		// This function loads all the tags of a given post
		// and returns them in a readable format
		// for the compose form text input.

		// find the post ID (because we are editing it, we have it or are able to find it)
		// select * where post_id = pid
		// implode by ', '
	}

	function parseTags($postID, $str) {
		$str = preg_replace('/\s*,\s*/', ',', $str);
		$tags = explode(',', $str);

		$db = Database::getInstance();

		// We have an array of all the tags for this post.
		// Find newly used tags and put them in the database.
		// Load the IDs for the newly stored tags as well as any existing ones

		// If editing the post, be sure to delete all tags in case the user removed a tag for that post.
		$db->query("DELETE FROM posts_tags WHERE post_id=?", array($postID));

		
		///array_walk($tags, create_function('&$str', '$str = \'("$str")\';'));
		$tagID = array();
		for ($i = 0; $i < sizeof($tags); $i++) {
			$db->query("INSERT IGNORE INTO tags(tagname) VALUES (?)", array($tags[$i]));

			// Get all the IDs on those tags we just put in
			$db->query("SELECT id FROM tags WHERE tagname=?", array($tags[$i]));
			$result = $db->firstResult();
			array_push($tagID, $result['id']);
		}

		// Put the pair post-id and tag-id in the posts/tags table

		// How can i make sure these IDs match up?
		for ($j = 0; $j < sizeof($tagID); $j++) {
			$db->query("INSERT INTO posts_tags(post_id, tag_id) VALUES (?, ?)", array($postID, $tagID[$j]));
		}

		return true;
	}


	$tpl->assign('formTitle', $formTitle);
	$tpl->assign('formImage', $formImage);
	$tpl->assign('formBody', $formBody);
	$tpl->assign('formSlug', $formSlug);
	$tpl->assign('formTags', $formTags);
	$tpl->assign('formEdit', $formEdit);
	$tpl->assign('formID', $formID);
	$tpl->assign('msg', $msg);
	$html = $tpl->draw('compose', $return_string = true );
	echo $html;