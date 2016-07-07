<?php
/*
** @Author:spenser
** @Date:2016-07-07
** @Explain:
*/
set_time_limit(0);
// 设置参数
$enter_url = "http://www.net767.com/gupiao/k/";
$localhost = "localhost";
$usn = "root";
$psd = "root";
$db = "program";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $enter_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$web = curl_exec($ch);
curl_close($ch);
$preg = '/<STRONG>(.+?)<\/STRONG>/is';
preg_match_all($preg, $web, $tag);
for ($i=0; $i < count($tag[1]); $i++) { 
	$tag[1][$i] = strip_tags($tag[1][$i]);
	$tag[1][$i] = trim($tag[1][$i]);
	// $tag[1][$i] = preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/', '', $tag[1][$i]);
	// if ($tag[1][$i] = ) {
	// 	# code...
	// }
}
var_dump($tag[1]);