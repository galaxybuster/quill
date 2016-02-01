<?php
	include "lib/rain.tpl.class.php";
	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "tpl/" );
	raintpl::configure("cache_dir", "temp/" );

	$tpl = new RainTPL;

	require_once("lib/init.php");
	require_once("lib/database.class.php");

	include "lib/Parsedown.php";
	$Parsedown = new Parsedown();
	$newPost = "";


	// Get parameters from URL
	if (isset($_GET['page'])) {
		// Load up the correct post from URL slug
		$loadSlug = $_GET['page'];

		$auth = isset($_SESSION['user']);

		// load database
		$db = Database::getInstance();
		$db->query("SELECT * FROM posts WHERE urlslug=? LIMIT 1", array($loadSlug));
		$result = $db->firstResult();

		
		$title = "";
		$date = "";
		$body = "";
		$parentMU = "";
		$sidebar = "";

		$tagsList = "";

		if ($result != null) {
			// Post was found. Load the contents and display them.
			$title = $result['title'];
			$date = date('M.d.Y', strtotime($result['lastedit']));

			$db->query("SELECT `tagname` FROM `tags` WHERE id IN (SELECT tag_id FROM posts_tags WHERE post_id IN (SELECT id FROM posts WHERE urlslug=?))", array($_GET['page']));
			$r = $db->result();
			$tl = array();
			foreach ($r as $t) {
				array_push($tl, '<a href="/notes/?tag='.$t['tagname'].'">'.$t['tagname'].'</a>');
			}
			$tagsList = implode(' - ', $tl);

			$image = "";
			if ($result['image'] != "") {
				$image = '<img src="'.$result['image'].'" class="post-header-image" />';
			}

			$body = $Parsedown->text($result['content']);
			
			if ($auth) {
				$sidebar .= "<a class='link-edit' href='action/compose.php?loadID=".$result['id']."&isEdit'>Edit this page</a><br/>";
			}
		} else {
			$title = "PAGE NOT FOUND";
			$body = "The page ". $loadSlug ." does not exist.";
			if ($auth) {
				$body .= "<br/><br/><a href='action/compose.php?slug=". $loadSlug ."'>Create this page</a>";
			}
		}

		// render
		$tpl->assign('sidebar', $sidebar);
		$tpl->assign('title', $title);
		$tpl->assign('tagsList', $tagsList);
		$tpl->assign('date', $date);
		$tpl->assign('image', $image);
		$tpl->assign('body', $body);
		echo $tpl->draw('post', true);

	} else if (isset($_GET['tag'])) {
		// Load posts of the given tag

		// load database
		$db = Database::getInstance();
		$db->query("SELECT `urlslug`, `title` FROM `posts` WHERE id IN (SELECT post_id FROM posts_tags WHERE tag_id IN (SELECT id FROM tags WHERE tagname=?)) ORDER BY date desc", array($_GET['tag']));
		$results = $db->result();
		$postlist = array();
		foreach($results as $p) {
			array_push($postlist, "<a href='".$p['urlslug']."'>".$p['title']."</a>");
		}


		// get all tags from the tags table
		$db->query("SELECT tagname FROM tags");
		$results = $db->result();
		$tagslist = array();
		foreach ($results as $t) {
			array_push($tagslist, "<a href='?tag=".$t['tagname']."'>".$t['tagname']."</a>");
		}

		$auth = isset($_SESSION['user']);
		$newPost = "";
		if ($auth) {
			$newPost = "<a href='action/compose.php'>New Post</a>";
		}

		$tpl->assign('newPost', $newPost);
		$tpl->assign('postlist', $postlist);
		$tpl->assign('taglist', $tagslist);
		echo $tpl->draw('index', true);
	} else {
		// No parameters set. simply load the latest posts.
		$db = Database::getInstance();
		$db->query("SELECT title, urlslug, LEFT(content, 100) FROM posts AS EXCERPT ORDER BY `date` desc");
		$results = $db->result();
		$postlist = array();
		foreach($results as $p) {
			array_push($postlist, "<div class='post-preview'><a href='".$p['urlslug']."'>".$p['title']."</a><br/><div>".$Parsedown->text($p['LEFT(content, 100)'])."</div></div>");
		}

		// get all tags from the tags table
		$db->query("SELECT tagname FROM tags");
		$results = $db->result();
		$tagslist = array();
		foreach ($results as $t) {
			array_push($tagslist, "<a href='?tag=".$t['tagname']."'>".$t['tagname']."</a>");
		}

		$auth = isset($_SESSION['user']);

		$newPost = "";
		if ($auth) {
			$newPost = "<a href='action/compose.php'>New Post</a>";
		}

		$tpl->assign('newPost', $newPost);
		$tpl->assign('postlist', $postlist);
		$tpl->assign('taglist', $tagslist);
		echo $tpl->draw('index', true);
	}