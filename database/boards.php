<?php 

// just copa pasta one and edit it

$config['cat']['1'] = array(
	'id' => '1',
	'name' => 'Imageboards'
);

$config['cat']['5'] = array(
	'id' => '5',
	'name' => 'Textboards'
);

$config['cat']['7'] = array(
	'id' => '7',
	'name' => 'Other'
);

//just copy paste one and edit it, set 'cat' to 'id' from categories above

$config['boards']['img'] = array(
	'url' => 'img',
	'title' => 'imageboard',
	'slogan' => 'Imageboard Demo',
	'description' => 'You can test the imageboard functions here.',
	'locked' => 0,
	'hidden' => 0,
	'nsfw' => 1,
	'cat' => '1',
	'mod_only' => 0,
	'type' => 'img'
);

$config['boards']['txt'] = array(
	'url' => 'txt',
	'title' => 'textboard',
	'slogan' => 'Textboard Demo',
	'description' => 'You can test the textboard functions here.',
	'locked' => 0,
	'hidden' => 0,
	'nsfw' => 0,
	'cat' => '5',
	'mod_only' => 0,
	'type' => 'txt'
);

$config['boards']['mod'] = array(
	'url' => 'mod',
	'title' => 'Moderator Board',
	'slogan' => 'They fear the janny.',
	'description' => 'Only readable and writeable by moderators.',
	'locked' => 0,
	'hidden' => 1,
	'nsfw' => 0,
	'cat' => '7',
	'mod_only' => 1,
	'type' => 'img'
);

$config['boards']['hidden'] = array(
	'url' => 'hidden',
	'title' => 'hidden',
	'slogan' => 'shh.',
	'description' => 'testing.',
	'locked' => 0,
	'hidden' => 1,
	'nsfw' => 0,
	'cat' => '7',
	'mod_only' => 0,
	'type' => 'txt'
);

foreach ($config['boards'] as $boards) {
	$config['boardlist'][] = $boards['url'];
}


?>
