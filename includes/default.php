<?php

$config['debug'] = true; // enables error logging, soykaf code, you'll want this enabled until you're sure everything works as should lol
$config['generated_in'] = true; //enables time spend generating in page footer
$prefix_folder = ''; // leave empty if in directory root, example 'ib' if in /ib/
$main_file = 'main.php'; //leave empty if using handlers like apache to hide filename example: /ib/?boards= vs /ib/main.php?boards=
$post_file = 'post.php'; //i cant imagine any reason to change this, but i suppose it could be in a different folder if you want to

$site_name = 'ImoutoIB';
$domain = '3dpd.moe'; //MUST BE SET FOR COOKIES
$captcha_required = false;

$secure_hash = "SQp3FaEgyMyHe3=Zc!-vS%ya6W!JAt+9fqwdbGk&ev!hbG!nSMgN_KUbLrmRpCQy"; //Will be used to hash your post passwords. You should change this.

$time_method = 'since'; //(iso:iso8601 unix:numberstamp since:howlongsince human:humanreadable
$time_method_hover = "human"; //unix will always be in data-timestamp for potential js use

$forced_anon = false;
$default_name = 'Anonymous';

$disable_email = false; //Disables the email field. Checkboxes will still work.
$show_email = true; //shows email in post name

$max_filesize = 1024*1000*8; //default 8mb

$thumb_method =  'GD'; //i probably wont implement any others
$thumb_ext = '.jpg'; //add support for transparent png, would use webp if apple stops shilling HEIC already and enables webp+webm support
$thumb_res_op =  250; //250x250
$thumb_res_reply =  125; //125x125
$thumb_spoiler = 'spoiler.png';
$spoiler_enabled = true;

$image_max_res = 9999; //9999x9999

$uploads_folder = 'uploads';

$filename_method = 'unix'; //unix = Time()+ 3 random digits 000-999 - uniqid for a random generation+time

$config['allowed_ext']['img'][] = '.jpg';
$config['allowed_ext']['img'][] = '.jpeg';
$config['allowed_ext']['img'][] = '.gif';
$config['allowed_ext']['img'][] = '.png';
$config['allowed_ext']['img'][] = '.webp';

$config['allowed_ext']['audio'][] = '.mp3';
$config['allowed_ext']['audio'][] = '.wav';
$config['allowed_ext']['audio'][] = '.ogg';

$config['allowed_ext']['video'][] = '.mp4';
$config['allowed_ext']['video'][] = '.webm';

$config['allowed_ext']['downloads'][] = '.pdf';



$config['display_banner'] = true;

$post_buttons = true; //adds a no-JS friendly post button on each post for delete/report using html5 details


// STYLESHEETS
$config['css'][] = 'Yotsuba B'; //mandatory, foundation for all other styles.
$config['css'][] = 'Yotsuba';
$config['css'][] = 'Burichan';
$config['css'][] = 'Futaba';

$config['css'][] = 'Tomorrow';

$default_theme = 'Yotsuba';

// JAVASCRIPTS
$config['js'][] = 'main.js'; //mandatory
//$config['js'][] = 'extensions.js';

//POST SETTINGS
$config['post_body_min'] = 10; //minimum characters, 0 to allow
$config['post_body_max'] = 4000; //maximum characters

$config['reply_body_min'] = false; //allow replies with only images
$config['reply_body_max'] = 4000; //maximum characters


//DATABASE CONFIGURATION
$config['db']['type'] = 'flat'; // flat, mysql
// Flat file (No Database)
$database_folder = 'database';

//(MySQL) -- Not implemented yet.
//$config['db']['server'] = 'localhost';
//$config['db']['username'] = 'username';
//$config['db']['password'] = 'password';



?>