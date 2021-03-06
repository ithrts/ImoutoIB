<?php

require 'require.php';

if ($config['generated_in'] != true) {
	$start_time = false;
}

if (!isset($_POST['board'])) {
	//error('No board selected.');
	$_POST['board'] = array_key_first($config['boards']); //set a board and allow seeing bans instead:
}

//if modonly
if ($config['boards'][phpClean($_POST['board'])]['mod_only'] == 1) {
	if ($config['mod']['mod_only'] > $mod_level) {
		error('Permission denied. Authenticated staff only.');
	}
}

//CHECK BANS, move this to a different file maybe.
$check_ban = crypt($_SERVER['REMOTE_ADDR'] , $secure_hash);
$check_ban = preg_replace('/(\/|\.)/i','' , $check_ban);
if (file_exists($path . '/' . $database_folder . '/bans/' . $check_ban)) {
	$existing_bans = [];
	$existing_bans = glob($path . '/' . $database_folder . '/bans/' . $check_ban . '/*');
	foreach ($existing_bans as $bans) {
		$ban = [];
		include $bans;
		//check if expired
		if ($ban['is_active'] == "1") {
			if ($ban['duration'] == 'permanent') {
				//SHOW BAN MESSAGE
				include $path . '/templates/banned.php';
				echo $output_html;
				exit();
			}
			if (($ban['time'] + $ban['duration']) < time()) {
				//edit file to inactive
				$edit_ban = file_get_contents($bans);
				$edit_ban = preg_replace('/ban\[\'is_active\'\] = "1";/i' , 'ban[\'is_active\'] = "0";' , $edit_ban);
				//save as expired
				file_put_contents($bans, $edit_ban);
				$ban['is_active'] = "0"; //remind banned.php that its no longer active
			} else {
				//this ban hasnt expired...
				//SHOW BAN MESSAGE
				include $path . '/templates/banned.php';
				echo $output_html;
				exit();
			}
		}

		//then check if its been read
		if ($ban['is_read'] == "0") {
			$edit_ban = file_get_contents($bans);
			$edit_ban = preg_replace('/ban\[\'is_read\'\] = "0";/i' , 'ban[\'is_read\'] = "1";' , $edit_ban);
			file_put_contents($bans, $edit_ban);
			//SHOW BAN MESSAGE
			include $path . '/templates/banned.php';
			echo $output_html;
			exit();
		}
		//cool lets continue
	}
}

//MOD FIELDS:
if (($config['mod']['thread_sticky'] <= $mod_level) && isset($_POST['sticky'])) {
	$info_sticky = 1;
}
if (($config['mod']['thread_lock'] <= $mod_level) && isset($_POST['lock'])) {
	$info_locked = 1;
}
if (($config['mod']['thread_autosage'] <= $mod_level) && isset($_POST['autosage'])) {
	$info_autosage = 1;
}

//POST FIELDS
	$post_board = phpClean($_POST['board']);
	if (isset($_POST['name'])) {
		$post_name = phpClean($_POST['name']);
	} else {
		$post_name = $default_name;
	}

	if ($disable_email !== true) {
		$post_email = phpClean($_POST['link']);
	} else { 
		$post_email = '';
	}
	if ($post_email == 'sage') {
		$_POST['sage'] = true;
	}
	if ($post_email == 'spoiler' || isset($_POST['spoiler'])) {
		$isSpoiler_ = true;
	}
	if (isset($_POST['sage'])) { //show sage if sage, even if email = nothing or spoiler
		$post_email = 'sage';
	}

	if (isset($_POST['subject'])) {
		$post_subject = phpClean($_POST['subject']);
	} else {
		$post_subject = '';
	}
	$post_body = phpClean($_POST['body']);

	//filter zalgo from all
	if ($config['filter_zalgo'] == true) {
		$post_name = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis","", $post_name);
		$post_email = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis","", $post_email);
		$post_subject = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis","", $post_subject);
		$post_body = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis","", $post_body);
	}


//CHECK SOME REQS BEFORE BODY EDIT, length. Check newlines after by scanning <br>'s
//IF NEW REPLY
if (isset($_POST['thread'])) {
	//get thread info
	$post_thread_number = phpClean($_POST['thread_number']);
	if (!is_numeric($post_thread_number)) {
		error('Invalid thread number.');
	}
	include ($path . '/' . $database_folder . '/boards/' . $post_board . '/' . phpClean($_POST['thread_number']) . "/info.php");
	if (($info_locked == 1) && ($config['mod']['post_in_locked'] > $mod_level)) {
		error('This thread is locked...');
	}
	if(!isset($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
		if (strlen($post_body) < $config['reply_body_min']) {
			error('Reply too short. Min: ' . $config['reply_body_min'] . '.');
		}
	} else {
		if ($config['reply_file_only'] == false) {
			if (strlen($post_body) < $config['reply_body_min']) {
				error('Reply too short. Min: ' . $config['reply_body_min'] . '.');
			}
		}
	}
	if (strlen($post_body) > $config['reply_body_max']) {
		error('Reply too long. Max: ' . $config['reply_body_max'] . '.');
	}
}



//WORDFILTERS, CITATIONS, ETC.
	if ($post_body != '') {
		

		//citations (probably gonna be a pain to fix dead links later?)
		//todo
		
		//VERY TEMPORARY REPLY-FUNCTION
		if (isset($_POST['thread'])) {
			$post_body = preg_replace("/&gt;&gt;([0-9]+)/", "<a class='quotelink' href='" . $prefix_folder . '/' . $main_file . '?board=' . $post_board . '&thread=' . $post_thread_number . "#$1'>$0</a>", $post_body);
		}


		if ($config['linkify_urls'] == true) {
			$post_body = preg_replace("/((https?|ftp):\/\/(\S*?\.\S*?))([\s)\[\]{},;\"\':<]|\.\s|$)/i","<a class='post-link' href='$0' target='_blank' rel='noopener noreferrer nofollow'>$0</a>",$post_body);
		}


		//add quotes
		$post_body = preg_replace("/^\s*&gt;.*$/m", "<span class='quote'>$0</span>", $post_body);
		//add replyquotes
		$post_body = preg_replace("/^\s*&lt;.*$/m", "<span class='rquote'>$0</span>", $post_body);

		//AsciiArt [aa]
		$post_body = preg_replace("/\[aa\](.+?)\[\/aa\]/ms", "<span class='aa'>$1</span>", $post_body);
		//Code [code]
		$post_body = preg_replace("/\[code\](.+?)\[\/code\]/ms", "<div class='code'>$1</div>", $post_body);
		//Spoilers
		$post_body = preg_replace("/\[spoiler\](.+?)\[\/spoiler\]/ms", "<span class='spoiler'>$1</span>", $post_body);

		//remove newlines from start and end of string
		$post_body = ltrim($post_body); //start
		$post_body = rtrim($post_body); //end
		//add newlines
		$post_body = preg_replace('/\n/i', '<br>', $post_body);

		//WORDFILTERS
		foreach ($config['wordfilters'] as $key => $wordfilter) {
			$post_body = preg_replace($wordfilter[0], $wordfilter[1], $post_body);
		}
	}



//Requirements met?

	//max lines?
	if (preg_match_all('/<br>/', $post_body) > $config['max_lines']) { 
		error('Too many new lines. Max: ' . $config['max_lines'] . '.');
	}

if ($captcha_required == true) {
	if(isset($_POST['captcha'])){
		session_start();
		if (($captcha_required == true) && ($_SESSION['captcha_text'] != strtolower($_POST['captcha']))) {
			error('Wrong captcha!! How annoying...');
		} else {
		//session_destroy();
		}
	} else {
		error('No captcha entered.');
	}
}

if ($post_name === '') {
	$post_name = $default_name;
}
if (strlen($post_name) > 256) {
	error('Name too long. Max 256.');
}

//if mod only board, show username and modlevel.
if ($config['boards'][phpClean($_POST['board'])]['mod_only'] == 1) {
	$post_name = $logged_in_user;
	$_POST['mod_level'] = true;
}

//add extra stuff to name:
if ($post_email == 'showlevel' || isset($_POST['mod_level'])) {
	$post_email = '';

	if ($config['mod']['public_mod_level'] <= $mod_level ) {
		switch($mod_level) {
			case 9001:
				$post_name .= ' <span class=\"modlevel admin\">## Admin</span>';
				break;
			case 40:
				$post_name .= ' <span class=\"modlevel mod\">## Moderator</span>'; //default configuration has moderator as minimum requirement to use this function.
				break;
			case 10:
				$post_name .= ' <span class=\"modlevel janitor\">## Janitor</span>';
				break;
			case 0:
				$post_name .= ' <span class=\"modlevel user\">## User</span>';
				break;
		}
	}
}


if (strlen($post_email) > 256) {
	error('Email too long. Max 256.');
}
if (strlen($post_subject) > 256) {
	error('Subject too long. Max 256.');
}
if (strlen($post_password) > 256) {
	error('Password too long. Max 256.');
}

//IF NEW THREAD
if (isset($_POST['index'])) {
	if (strlen($post_body) > $config['post_body_max']) {
		error('Post too long. Max: ' . $config['post_body_max'] . '.');
	}
	if (strlen($post_body) < $config['post_body_min']) {
		error('Comment too short. Min: ' . $config['post_body_min'] . '.');
	}
	if ($config["boards"][$post_board]["type"] == "img") {
		if ($config['post_require_file'] == true) { //if no file then error
			if(!isset($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
				error('You must upload a file to start a thread.');
			}
		}
		if ($config['post_require_subject'] == true) {
			if (trim($post_subject) == '') {
				error('You must submit a subject.');
			}
		} else {
			//check if neither file nor text is given
			if((!isset($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) && ($post_body == '' && $post_subject == '')) {
				error('You must either upload a file or input some text in the subject or comments.');
			}
		}
	}
	if ($config["boards"][$post_board]["type"] == "txt") { //check if empty subject in textboard
		if (trim($post_subject) == '') {
			error('You must enter a subject when creating a thread.');
		}
	}
}


//ARE WE POSTING?
if ((isset($post_board)) && (isset($_POST['index']))) {
	//SHOULD IT EXIST?
	if (!isset($config['boards'][$post_board])) {
		error('This board shouldn\'t exist...');
	}
	//IF NOT EXIST, CREATE DIRECTORY
	if (!file_exists($path . '/' . $database_folder . '/boards')) {
		mkdir($path . '/' . $database_folder . '/boards', 0755, true);
	}
	if ((!file_exists($path . '/' . $database_folder . '/boards/' . $post_board) && (isset($config['boards'][$post_board])) === true)) {
		mkdir($path . '/' . $database_folder . '/boards/' . $post_board, 0755, true);
	}

	if ($config['boards'][$post_board]['locked'] == 1) {
		error('This board is locked. Sneaky.');
	}

	//IS THIS OUR FIRST THREAD?
	
	// if no file in folder
	if (dir_is_empty($path . '/' . $database_folder . '/boards/' . $post_board)) {
		file_put_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/counter.php', 1); //create post count
	}
	//CREATE THREAD FOLDER
	$counter = file_get_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/counter.php');
	//CHECK FOR AND HANDLE FILES
	include $path . '/includes/filehandler.php';
	$current_count = $counter;
	mkdir($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $current_count, 0755, true); //create thread folder

	//COLLECT POST INFORMATION
	$create_OP = '<?php $op_name = "' . $post_name . '";';
	$create_OP .= '$op_email = "' . $post_email . '";';
	$create_OP .= '$op_subject = "' . $post_subject . '";';
	$create_OP .= '$op_body = "' . $post_body . '";';
	$create_OP .= '$op_password = "' . $post_password . '";';

	$create_OP .= '$op_file = array( array("' . $file_type . '","' . $new_filename . '","' . $original_filename . '","' . $upload_resolution . '","' . $filesize_ . '","' . $isSpoiler_ . '", "'.$new_thumbname.'","'.$thmb_res.'") );'; //array in array to prepare for multifiles later, easy upgrade i guess


	$create_OP .= '$op_ip = "' . crypt($_SERVER['REMOTE_ADDR'] , $secure_hash) . '";';
	$create_OP .= '$op_time = "' . time() . '"; ?>';

	//SAVE POST INFORMATION
	$current_count = $counter;
	file_put_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $current_count . '/OP.php', $create_OP);
	
	//INCREMENT COUNTER
	$counter = file_get_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/counter.php');
	$newcount = $counter + 1;
	file_put_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/counter.php', $newcount);

	if ($captcha_required == true) {
		session_destroy(); //reset captcha only when passed all requirements
	}

	UpdateOP($database_folder, $post_board, $current_count, 1, 0, $current_count, 1, $info_sticky, $info_locked, $info_autosage); //information about thread and replies
	UpdateThreads($database_folder, $post_board, $current_count); //update recents.php and board bumps.
	UpdateRecents($database_folder, $post_board, $current_count, $recent_replies);
	include $path . '/includes/update-frontpage.php';
	PostSuccess($prefix_folder . $main_file . '/?board=' . $post_board . '&thread=' . $counter . '#' . $counter, true, $start_time);
	
	}

if ((isset($post_board)) && (isset($_POST['thread']))) {
	$post_is_thread = phpClean($_POST['thread']);
	$post_thread_number = phpClean($_POST['thread_number']);
	if (!is_numeric($post_thread_number)) {
		error('Invalid thread number.');
	}
	//board exists?
	if (!isset($config['boards'][$post_board])) {
		error('This board shouldn\'t exist...');
	}
	//thread exists?
	if (($post_is_thread == 'thread') && (file_exists($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $post_thread_number . '/OP.php'))) {
		//THREAD EXISTS

		//CREATE/INCREASE COUNTER+LAST BUMPED. to do: (reset bump on post deletion by user or mod, do elsewhere)
			$counter = file_get_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/counter.php');
		//CHECK FOR AND HANDLE FILES
			include $path . '/includes/filehandler.php';
			$newcount = $counter + 1;
			//save it as last bumped if not sage tho
			if (!isset($_POST['sage']) && $info_autosage == 0) {
			file_put_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $post_thread_number . '/bumped.php', $counter);
			}
			//save it as last post number
			file_put_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/counter.php', $newcount);
		//counter handled... moving on:

		//POST STUFF
		//COLLECT POST INFORMATION
		$create_reply = '<?php $reply_name = "' . $post_name . '";';
		$create_reply .= '$reply_email = "' . $post_email . '";';
		$create_reply .= '$reply_subject = "' . $post_subject . '";';
		$create_reply .= '$reply_body = "' . $post_body . '";';
		$create_reply .= '$reply_password = "' . $post_password . '";';

		$create_reply .= '$reply_file = array( array("' . $file_type . '","' . $new_filename . '","' . $original_filename . '","' . $upload_resolution . '","' . $filesize_ . '","' . $isSpoiler_ . '", "'.$new_thumbname.'","'.$thmb_res.'") );'; //array in array to prepare for multifiles later, easy upgrade i guess

		$create_reply .= '$reply_ip = "' . crypt($_SERVER['REMOTE_ADDR'] , $secure_hash) . '";';
		$create_reply .= '$reply_time = "' . time() . '"; ?>';

		//SAVE POST INFORMATION
		$current_count = $counter;
		file_put_contents($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $post_thread_number . '/' . $current_count . '.php', $create_reply);

		//how many replies do we have?
			//FIND REPLIES
		$replies_ = [];
		$replies_ = glob($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $post_thread_number . "/*");
		$reply_counter = 0;
		foreach ($replies_ as $reply) {
			if (is_numeric(basename($reply, '.php'))) {
				$reply_counter += 1;
			}
		}
		//how many unique posters do we have?
		$ip_counter = 1;
		$ips_ = [];

		//Get OP IP
		include ($path . '/' . $database_folder . '/boards/' . $post_board . '/' . $post_thread_number . "/OP.php");
		$ips_ = [];
		$ips_[] = $op_ip;
		//Get replies ips
		foreach ($replies_ as $reply) {
		include ($reply);
		$ips_[] = $reply_ip;
		}
		$ip_counter = count(array_unique($ips_)); 

		if ($captcha_required == true) {
			session_destroy(); //reset captcha only when passed all requirements
		}

		UpdateOP($database_folder, $post_board, $post_thread_number, 0, $reply_counter, $current_count, $ip_counter, $info_sticky, $info_locked, $info_autosage);
		UpdateThreads($database_folder, $post_board, $current_count); //update recents.php and board bumps.
		UpdateRecents($database_folder, $post_board, $post_thread_number, $recent_replies); //update recents.php and board bumps.
		include $path . '/includes/update-frontpage.php';
		PostSuccess($prefix_folder . $main_file . '/?board=' . $post_board . '&thread=' . $post_thread_number . '#' . $current_count, true, $start_time);
		
		}
}


error('This shouldn\'t happen..');
?>