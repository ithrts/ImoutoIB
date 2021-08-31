<?php 

require dirname(__FILE__) . '/require.php';


//IF NO BOARD PARAMETER (FRONT PAGE)
if ((!isset($_GET["board"])) || ($_GET["board"] == '')) {
	
	$title = $site_name;

	$total_posts = 0;
	foreach ($config['boards'] as $boards) {
		//prevent error on new board with 0 posts. you need to visit frontpage at least once otherwise board throws an error, but its not fatal or anything....
		if (!file_exists(__dir__ . '/' . $database_folder . '/boards/' . $boards['url'] . '/counter.php')) {
			@mkdir(__dir__ . '/' . $database_folder . '/boards/' . $boards['url']);
			file_put_contents(__dir__ . '/' . $database_folder . '/boards/' . $boards['url'] . '/counter.php', 1);
		}
		//
		$total_posts += file_get_contents(__dir__ . '/' . $database_folder . '/boards/' . $boards['url'] . '/counter.php');
		$total_posts -= 1; //idk how i fucked up the counter.php in post.php this badly.
	}
	
	if (isset($_GET["theme"])) {
		echo '<html data-stylesheet="'. htmlspecialchars($_GET["theme"]) .'">';
	} else {
		echo '<html data-stylesheet="'. $current_theme .'">';	
	}
	echo '<head>';
	include $path . '/templates/header.html';
	echo '</head>';
	echo '<body class="frontpage">';
	include $path . '/templates/boardlist.html';
	include $path . '/templates/pages/frontpage.html';
	include $path . '/templates/footer.html';
	echo '</body>';
	echo '</html>';
	exit();
}

// IF BOARD EXISTS
if (in_Array(htmlspecialchars($_GET["board"]), $config['boardlist'])) {

	$current_board = htmlspecialchars($_GET["board"]);
	$board_description = $config['boards'][$current_board]['description'];
	$board_title = $config['boards'][$current_board]['title'];


	// IF NOT THREAD (if index)
	if (htmlspecialchars($_GET["thread"]) === "") {
		$current_page = "index";
		$title = '/' . $current_board . '/ - ' . $config['boards'][$current_board]['title'] . ' - ' . $site_name;
	}

	// TO DO: CHECK WHAT INDEX PAGE (starting with 1 infinite index for now)

	// IF THREAD
	if ((htmlspecialchars($_GET["thread"]) != '') && (file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . htmlspecialchars($_GET["thread"])))) {
		$current_page = "thread";
		include __dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . htmlspecialchars($_GET["thread"]) . '/OP.php';
		$post_number_op = htmlspecialchars($_GET["thread"]);
		if ($op_subject == '') {
			$title = '/' . $current_board . '/' . ' - ' . substr($op_body,0,30) . ' - ' . $config['boards'][$current_board]['title'] . ' - ' . $site_name;
		} else {
			$title = '/' . $current_board . '/' . ' - ' . $op_subject . ' - ' . $config['boards'][$current_board]['title'] . ' - ' . $site_name;
		}
	}

	if ((htmlspecialchars($_GET["thread"]) != '') && (!file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . htmlspecialchars($_GET["thread"])))) {
	$title = "Oh no!! A 404...";
	//the error is in a different castle actually, look further down
	}

	if (isset($_GET["theme"])) {
		echo '<html data-stylesheet="'. htmlspecialchars($_GET["theme"]) .'">';
	} else {
		echo '<html data-stylesheet="'. $current_theme .'">';	
	}
	echo '<head>';
	include $path . '/templates/header.html';
	echo '</head>';
	echo '<body class="' . $current_page . '">';
	include $path . '/templates/boardlist.html';
	include $path . '/templates/page-info.html';
	include $path . '/templates/post-form.html';

		//get thread info
	if (htmlspecialchars($_GET["thread"]) != '') {
	include __dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . $post_number_op . "/info.php";
	$thread_stats = '<span class="thread-stats">Replies: ' . $info_replies . ' Posters: ' . $info_uniqueids . '</span>';
		echo '[<a href="' . $prefix_folder . '/' . $main_file . '?board=' . $current_board . '">Return</a>]&nbsp;';
		echo $thread_stats;
		echo '<hr>';
	}

	//if index 
	//foreach folder in current_board 
		if (htmlspecialchars($_GET["thread"]) === "") {

		//if zero threads aka new board
		if (!file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board)) {
			echo 'This board has no threads yet.';
			include $path . '/templates/footer.html';
			exit();
		}

		if (file_get_contents(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/counter.php') === "1") {
			echo 'This board has no threads yet.';
			include $path . '/templates/footer.html';
			exit();
		}

		//FIND THREADS
		$threads_full = [];
		$threads_full = glob(__dir__ . '/' . $database_folder . '/boards/' . $current_board . "/*", GLOB_ONLYDIR);
		
		//SORTING
		foreach ($threads_full as $key => $thread) {
			$threadz= basename($thread);
			if (!file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . basename($thread) . '/bumped.php')) {
				$bumped = basename($thread);
			}
			if (file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . basename($thread) . '/bumped.php')) {
				$bumped = file_get_contents(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . basename($thread) . '/bumped.php');
			}
			$threads[$key] = [];
			$threads[$key]['id'] = $threadz;
			$threads[$key]['bumped'] = $bumped;
		}
		$keys_ = array_column($threads, 'bumped');
		array_multisort($keys_, SORT_DESC, $threads);

		////// to do: use post/mod.php to allow for a sticky 0/1, locked 0/1, if sticky show 0-1 replies max

		//SHOW THEM
		foreach (array_keys($threads) as $key => $value) {
			include __dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . $threads[$key]['id'] . '/OP.php';
			$post_number_op = $threads[$key]['id'];

			//SHOW REPLIES TO THREADS ON INDEX (im gonna need a lot of changes here later for sorting and maximum, configurable recents.php updated by post.php for performance?)
				//ADD ALL REPLIES HERE
			//FIND REPLIES
				$replies_full = [];
				$replies_full = glob(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . $post_number_op . "/*");
			//SORTING
				$replies = [];
				foreach ($replies_full as $reply) {
					if (basename($reply) != ('OP.php') && basename($reply) != ('info.php') && basename($reply) != ('bumped.php')) {
							$replies[] = basename($reply, '.php');
					}
				}
			$total_replies = count($replies);
			rsort($replies); //sort by biggest to lowest
			$replies = array_slice($replies, 0, 5); //remove everything except biggest
			sort($replies); //sort back to show from low to high

			if ($total_replies > 5) {
				$replies_omitted = $total_replies - count($replies);
			} else {
				$replies_omitted = 0;
			}

			//SHOW THREADS
			include $path . '/templates/thread.html';
			//SHOW SHOW REPLIES
			foreach (array_keys($replies) as $rkey => $value) {
				include __dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . $post_number_op . '/' . $replies[$value] . '.php';
				$post_number_reply = $replies[$value];
				include $path . '/templates/reply.html';
		   	}

			if ($key != array_key_last($threads)) {
		        echo '<hr>';
		    }
	   	}
	include $path . '/templates/footer.html';
	echo '</body>';
	echo '</html>';
	exit();
	}

	//IF THREAD
	if ((htmlspecialchars($_GET["thread"]) !== '') && (file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . htmlspecialchars($_GET["thread"])))) {
	include __dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . htmlspecialchars($_GET["thread"]) . '/OP.php';
	$post_number_op = htmlspecialchars($_GET["thread"]);

	//show thread
	include $path . '/templates/thread.html';
	}
	//set current_thread 
	if ($current_page === 'thread') {
		$current_thread = $post_number_op;
	}
	//else does not exist
	if ((htmlspecialchars($_GET["thread"]) !== '') && (!file_exists(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . htmlspecialchars($_GET["thread"])))) {
	echo '<div class="message">This thread doesn\'t exist.. Did the glowies get it — or worse, a janny??<br><img style="height: 500px;width: 500px;margin-top: 5px;" src="'. $prefix_folder . '/assets/img/404.png" width="" height=""></div><style>.message { margin-top: 0!important }</style>';
	echo '<div class="message">[<a href="' . $prefix_folder . $main_file . '?board=' . $current_board . '">Return</a>]</div>';
	include $path . '/templates/footer.html';
	exit();
	}

	//ADD ALL REPLIES HERE
	//FIND REPLIES
		$replies_full = [];
		$replies_full = glob(__dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . $post_number_op . "/*");
	//SORTING
		$replies = [];
		foreach ($replies_full as $reply) {
			if (basename($reply) != ('OP.php') && basename($reply) != ('info.php') && basename($reply) != ('bumped.php')) {
				$replies[] = basename($reply, '.php');
			}
		}
	sort($replies);
	//SHOW THEM
	foreach (array_keys($replies) as $key => $value) {
		include __dir__ . '/' . $database_folder . '/boards/' . $current_board . '/' . $post_number_op . '/' . $replies[$value] . '.php';
		$post_number_reply = $replies[$value];
		include $path . '/templates/reply.html';
   	}


	//footer for thread+index
	if (htmlspecialchars($_GET["thread"]) != '') {
		echo '<hr>';
		echo '[<a href="' . $prefix_folder . '/' . $main_file . '?board=' . $current_board . '">Return</a>]&nbsp;';
		echo $thread_stats;
	}
	include $path . '/templates/footer.html';
	echo '</body>';
	echo '</html>';
}

if ((htmlspecialchars($_GET["board"]) !== '') && (!in_Array(htmlspecialchars($_GET["board"]), $config['boardlist']))) {
	error('This board doesn\'t exist.. You\'re not trying anything funny — are you, Anon-san??');
}

?>