<?php
/*
** @Author:spenser
** @Date:2016-07-07
** @Explain:k线图
** @warning:内容中的相关链接
*/
set_time_limit(0);
$enter_url[1] = "http://www.net767.com/gupiao/k/";
$enter_url[2] = "http://www.net767.com/gupiao/k/List_3.html";
$enter_url[3] = "http://www.net767.com/gupiao/k/List_2.html";
$enter_url[4] = "http://www.net767.com/gupiao/k/List_1.html";
$localhost = "localhost";
$usn = "root";
$psd = "root";
$db = "program";
$key ="k线图";

for ($i=1; $i < count($enter_url); $i++) { 
	$ch[$i] = curl_init();
	curl_setopt($ch[$i], CURLOPT_URL, $enter_url[$i]);
	curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch[$i], CURLOPT_HEADER, 0);
	$output[$i] = curl_exec($ch[$i]);
	curl_close($ch[$i]);
	$key = mb_convert_encoding($key, "GB2312", "UTF-8");
	// 匹配k线图首页区域
	$preg = '/<H1>'.$key.'<\/H1>(.*?)<div\s*class="showpage">/is';
	preg_match_all($preg, $output[$i], $area[$i]);
	// 匹配内容页链接及标题
	$preg2 = '/<a\s*class=""\s*href="(.*?)"\s*target="_blank">(.*?)<\/a>/is';
	preg_match_all($preg2, $area[$i][1][0], $link[$i]);
}
$url = array_merge($link[1][1], $link[2][1], $link[3][1], $link[4][1]);
$title = array_merge($link[1][2], $link[2][2], $link[3][2], $link[4][2]);
for ($i=1; $i < count($url); $i++) { 
	$news_url[$i] = "http://www.net767.com".$url[$i];
	$ch[$i] = curl_init();
	curl_setopt($ch[$i], CURLOPT_URL, $news_url[$i]);
	curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch[$i], CURLOPT_HEADER, 0);
	$output[$i] = curl_exec($ch[$i]);
	// print_r($output[$i]);
	curl_close($ch[$i]);
	// 匹配内容页第一页正文 
	$preg = '/<td\s*class=neirong\s*vAlign=top\s*align=left>(.*?)<div\s*id=\'pagebar\'>/is';
	preg_match_all($preg, $output[$i], $page[$i]);
	$content[$i] = $page[$i][1][0]; //内容页正文（第一页）
	// 匹配内容页分页链接区域
	$preg2 = '/<a\s*href=\'(.*?)\'>(.*?)<\/a>/';
	preg_match_all($preg2, $output[$i], $pagebar[$i]);
	$end_page_url[$i] = end($pagebar[$i][1]);
	// 获取内容页分页数量
	$preg3 = '/\_(.*?)\.html/';
	preg_match_all($preg3, $end_page_url[$i], $end_page[$i]);
	$page_count[$i] = $end_page[$i][1][0]; //内容页分页数量
	if ($page_count[$i] == 2) {
		$end_page_url[$i] = array_slice($pagebar[$i][1], -2, 1);
		preg_match_all($preg3, $end_page_url[$i][0], $end_page[$i]);
		$page_count[$i] = $end_page[$i][1][0]; //内容页分页数量
	}
	// 遍历内容页分页
	for ($j=2; $j < ($page_count[$i] + 1); $j++) {
		$new_url[$i][$j] = str_replace(".html", "_".$j.".html", $news_url[$i]);
		$ch2[$i][$j] = curl_init();
		curl_setopt($ch2[$i][$j], CURLOPT_URL, $new_url[$i][$j]);
		curl_setopt($ch2[$i][$j], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2[$i][$j], CURLOPT_HEADER, 0);
		$paging[$i][$j] = curl_exec($ch2[$i][$j]);
		curl_close($ch2[$i][$j]);
		// 匹配内容页分页正文
		preg_match_all($preg, $paging[$i][$j], $paging_text[$i][$j]);
		$paging_content[$i][$j] = $paging_text[$i][$j][1][0]; //内容页正文（分页）
		$content[$i] .= $paging_content[$i][$j]; //拼接正文
	}
	// 修正图片路径
	$preg4 = '/\/gupiao\//';
	$repl = 'http://www.net767.com/gupiao/';
	$content[$i] = preg_replace($preg4, $repl, $content[$i]);
	// 去广告
	$preg5 = '/<table\s*height=252\s*cellSpacing=0\s*cellPadding=0\s*width=320\s*align=left\s*border=0>(.*?)<\/table>/is';
	$content[$i] = preg_replace($preg5, '', $content[$i]);
	echo $title[$i]."<br>";
	echo $content[$i]."<br>";
}
?>