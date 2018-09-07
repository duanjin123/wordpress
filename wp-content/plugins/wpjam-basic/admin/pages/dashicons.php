<?php

function wpjam_dashicons_page(){
	?>
	<h1>Dashicons</h1>

	<style type="text/css">
	h2{
		margin:30px 0 24px 0;
	}

	.wpjam-dashicons{
		font-size:15px;
	}

	.wpjam-dashicons .dashicons{
		font-size:26px;
		width: 26px;
		height: 26px;
	}
	</style>
	<?php
	$dashicon_css_file	= fopen(ABSPATH.'/'.WPINC.'/css/dashicons.css','r') or die("Unable to open file!");

	$i	= 0;

	while(!feof($dashicon_css_file)) {
		$line	= fgets($dashicon_css_file);
		$i++;
		
		if($i < 32) continue;

		if($line){
			if (preg_match_all('/.dashicons-(.*?):before/i', $line, $matches)) {
				echo '<p class="wpjam-dashicons"><span class="dashicons-before dashicons-'.$matches[1][0].'"></span> dashicons-'.$matches[1][0].'</p>'."\n";
			}elseif(preg_match_all('/\/\* (.*?) \*\//i', $line, $matches)){
				echo '<h2>'.$matches[1][0].'</h2>'."\n";
			}
		}

		// echo  $line. "<br>";
	}

	fclose($dashicon_css_file);
}