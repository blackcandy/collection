<?php
/*
** @Author:spenser
** @Date:2016-07-07
** @Explain:k线图首页采集文件(第一页)
*/
set_time_limit(0);
// 设置参数
$enter_url = "http://www.net767.com/gupiao/k/";
$category = 1;
$localhost = "localhost";
$usn = "root";
$psd = "root";
$db = "program";

// 初始化curl及设置选项
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.net767.com/gupiao/k/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
// 执行并获取html内容
$output = curl_exec($ch);
// 释放curl句柄
curl_close($ch);

// 匹配k线图首页区域
$preg = '/<\/h1>(.*?)<div\s*class="showpage">/is';
preg_match_all($preg, $output, $web);
// 匹配内容页链接及标题
$preg2 = '/<a\s*class=""\s*href="(.*?)"\s*target="_blank">(.*?)<\/a>/is';
preg_match_all($preg2, $web[1][0], $link);

// 遍历内容页
for ($i=0; $i < count($link[1]); $i++) { 
	$title[$i] = $link[2][$i]; //标题
	$url[$i] = "http://www.net767.com".$link[1][$i]; //链接
	$ch2[$i] = curl_init();
	curl_setopt($ch2[$i], CURLOPT_URL, $url[$i]);
	curl_setopt($ch2[$i], CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch2[$i], CURLOPT_HEADER, 0);
	$cont[$i] = curl_exec($ch2[$i]); //获取内容页
	curl_close($ch2[$i]);
	// 匹配内容页第一页正文 
	$preg3 = '/<td\s*class=neirong\s*vAlign=top\s*align=left>(.*?)<div\s*id=\'pagebar\'>/is';
	preg_match_all($preg3, $cont[$i], $page[$i]);
	$content[$i] = $page[$i][1][0]; //内容页正文（第一页）
	// 匹配内容页分页链接区域
	$preg4 = '/<a\s*href=\'(.*?)\'>(.*?)<\/a>/';
	preg_match_all($preg4, $cont[$i], $pagebar[$i]);
	$end_page_url[$i] = end($pagebar[$i][1]);
	// 获取内容页分页数量
	$preg5 = '/\_(.*?)\.html/';
	preg_match_all($preg5, $end_page_url[$i], $end_page[$i]);
	$page_count[$i] = $end_page[$i][1][0]; //内容页分页数量

	// 遍历内容页分页
	for ($j=2; $j < ($page_count[$i] + 1); $j++) {
		$new_url[$i][$j] = str_replace(".html", "_".$j.".html", $url[$i]);
		$ch3[$i][$j] = curl_init();
		curl_setopt($ch3[$i][$j], CURLOPT_URL, $new_url[$i][$j]);
		curl_setopt($ch3[$i][$j], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch3[$i][$j], CURLOPT_HEADER, 0);
		$paging[$i][$j] = curl_exec($ch3[$i][$j]);
		curl_close($ch3[$i][$j]);
		// 匹配内容页分页正文
		preg_match_all($preg3, $paging[$i][$j], $paging_text[$i][$j]);
		$paging_content[$i][$j] = $paging_text[$i][$j][1][0]; //内容页正文（分页）
		$content[$i] .= $paging_content[$i][$j]; //拼接正文
	}

	// 修正图片路径
	$preg6 = '/\/gupiao\//';
	$repl = 'http://www.net767.com/gupiao/';
	$content[$i] = preg_replace($preg6, $repl, $content[$i]);

	// 连接数据库
	$con = mysql_connect($localhost, $usn, $psd);
	if (!$con) {
		die('Could not connect: '. mysql_error());
	}
	mysql_select_db($db, $con);
	
	$time[$i] = date("Y-m-d H:i:s"); //时间
	$guid[$i] = rand(10000,99999);
	$title_url[$i] = "http://localhost/k/wordpress/".date('Y/m/d')."/".$guid[$i];
	// 发布
	if (mysql_query("INSERT INTO dm_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, guid, menu_order, post_type) VALUES ('1', '$time[$i]', '$time[$i]', '$content[$i]', '$title[$i]', 'publish', 'open', 'open', '$guid[$i]', '$time[$i]', '$time[$i]', '0', '$title_url[$i]', '0', 'post')")) {
		$id = mysql_insert_id();
		mysql_query("INSERT INTO dm_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($id, $category, '0')");
		echo $id.":".$title[$i]."<br>success<br>";
	} else {
		echo "insert failed<br>";
	}
	mysql_close($con);
}	





?>