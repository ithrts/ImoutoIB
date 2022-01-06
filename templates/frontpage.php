<?php 

if ($config['display_banner'] === true) {
	include $path . '/assets/img/banner.php';
}


$output_html .= '<div class="page-info">
	<h1><?php echo $site_name; ?></h1>
	<span class="small"><?php echo $site_slogan; ?></span>
</div>

<br>

<div class="main first">
	<h2>Live Demo</h2>
	<p> This software is probably riddled with bugs and exploits.<br>Please send me questions, feature requests, and bug reports on <a href="https://github.com/ithrts/ImoutoIB" target="_blank">Github</a>.<br><br>You should not use ImoutoIB in any serious capacity.</p>
</div>';


$output_html .= '<br>
<div class="main">
	<h2>Boards</h2>';

	$output_html .= '<table id="boards">';
	//$output_html .= '<thead><tr><th>Board</th><th>Description</th><th>Posts</th></tr></thead>';
	$output_html .= '<tbody>';
	foreach ($config['cat'] as $cat) {
		$output_html .= '<tr class="cat"><th><span class="small">Board</span></th><th><h2>' . $cat['name'] . '</h2></th><th><span class="small">Posts</span></th>';
		foreach ($config['boards'] as $boards) {
			if($boards['cat']==$cat['id']) {
				if ($boards['hidden'] === 0) {
					$output_html .= '<tr><th><a href="' . $prefix_folder . '/' . $main_file . '?board=';
					$output_html .= $boards['url'];
					$output_html .= '">';
					$output_html .= '/' . $boards['url'] . '/' . ' - ' . $boards['title'];
					$output_html .= '</a></th><th><span class="nsfw-' . $boards['nsfw'] . '">' . $boards['description'] . '</span></th>';
					$board_counter = file_get_contents($path . '/' . $database_folder . '/boards/' . $boards['url'] . '/counter.php') - 1;
					$output_html .= '<th>' . $board_counter . '</th>';
				}
			}
		}
	}
$output_html .= '</tr></tbody>';
$output_html .= '</table>';
$output_html .= '</div>

<br>
<div class="main">
	<h2>Stats</h2>
	<div class="stats">
		<div><b>Total Posts:</b> ' . $total_posts . '</div>
		<div><b>Unique Posters:</b> ';
if (file_exists($path . '/' . $database_folder . '/frontpage.php')) {
	include $path . '/' . $database_folder . '/frontpage.php'; 
}
				$output_html .= $frontpage_uniqueids;
$output_html .= '</div>
		<div><b>Active Content:</b> ' . formatBytes($frontpage_active) . '</div>
	</div>
</div>';
