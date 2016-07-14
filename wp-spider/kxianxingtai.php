<?php
set_time_limit(0);
$enter_url = "http://www.net767.com/gupiao/k/";
$localhost = "localhost";
$usn = "root";
$psd = "root";
$db = "program";
$key = "k线形态实战技术图解";

$web = file_get_contents($enter_url);
// print_r($web);
$str = mb_convert_encoding($key, "GB2312", "UTF-8");
$preg = '/<STRONG><FONT\sstyle="FONT-SIZE:\s14px"\scolor=#0d519c>'.$str.'<\/FONT><\/STRONG>(.*?)<\/table>/is';
preg_match_all($preg, $web, $area);
$preg2 = '/<a.*?href="(.+?)".*?>(.+?)<\/a>/';
preg_match_all($preg2, $area[1][0], $link);
for ($i=0; $i < count($link[1]); $i++) { 
	$title[$i] = $link[2][$i]; //标题
	$url[$i] = 'http://www.net767.com/'.$link[1][$i]; //链接
	// 连接数据库
	$con = mysql_connect($localhost, $usn, $psd);
	if (!$con) {
		die('Could not connect: '. mysql_error());
	}
	mysql_select_db($db, $con);
	// 查重
	$sql[$i] = "SELECT ID FROM wp_posts WHERE from_url='$url[$i]'";
	$result[$i] = mysql_query($sql[$i]);
	$rs[$i] = mysql_fetch_array($result[$i]);
	if ($rs[$i]) {
		echo $rs[$i][0].":".$title[$i]."existed<br>";
		continue; //跳过本次循环
	}

	$news_web[$i] = file_get_contents($url[$i]);
	// 匹配内容页第一页正文 
	$preg3 = '/(<td\s*class=neirong\s*vAlign=top\s*align=left>|<td\s*vAlign=top\s*class=neirong\s*colSpan=2\s*height=241\s*width="92%">)(.*?)(<div\s*id=\'pagebar\'>|<td\s*width="3%"><\/td>
	)/is';
	preg_match_all($preg3, $news_web[$i], $page[$i]);
	if (empty($page[$i][2][0])) {
		continue;
	}
	$content[$i] = $page[$i][2][0]; //内容页正文（第一页）
	//内容页分页数量
	$preg4 = '/<a\s*href=\'(.*?)\'>(.*?)<\/a>/';
	preg_match_all($preg4, $news_web[$i], $pagebar[$i]);
	$end_page_url[$i] = end($pagebar[$i][1]);
	$preg5 = '/\_(.*?)\.html/';
	preg_match_all($preg5, $end_page_url[$i], $end_page[$i]);
	$page_count[$i] = $end_page[$i][1][0]; 
	if ($page_count[$i] == 2) {
		$end_page_url[$i] = array_slice($pagebar[$i][1], -2, 1);
		preg_match_all($preg5, $end_page_url[$i][0], $end_page[$i]);
		$page_count[$i] = $end_page[$i][1][0];
	} 
	if ($page_count[$i] == 11) {
		$paging_url[$i] = str_replace(".html", "_11.html", $url[$i]);
		$news_web[$i] = file_get_contents($paging_url[$i]);
		preg_match_all($preg4, $news_web[$i], $pagebar[$i]);
		$end_page_url[$i] = end($pagebar[$i][1]);
		preg_match_all($preg5, $end_page_url[$i], $end_page[$i]);
		$page_count[$i] = $end_page[$i][1][0];
		if ($page_count[$i] == 12) {
			$end_page_url[$i] = array_slice($pagebar[$i][1], -2, 1);
			preg_match_all($preg5, $end_page_url[$i][0], $end_page[$i]);
			$page_count[$i] = $end_page[$i][1][0];
		}	
	}
	// 遍历内容页分页
	for ($j=2; $j < ($page_count[$i] + 1); $j++) {
		$new_url[$i][$j] = str_replace(".html", "_".$j.".html", $url[$i]);
		$paging[$i][$j] = file_get_contents($new_url[$i][$j]);
		// 匹配内容页分页正文
		preg_match_all($preg3, $paging[$i][$j], $paging_text[$i][$j]);
		$paging_content[$i][$j] = $paging_text[$i][$j][2][0]; //内容页正文（分页）
		$content[$i] .= $paging_content[$i][$j]; //拼接正文	
	}
	// 修正图片路径
	$preg4 = '/\/gupiao\//';
	$repl = 'http://www.net767.com/gupiao/';
	$content[$i] = preg_replace($preg4, $repl, $content[$i]);
	// 去广告
	$preg6 = '/<table\s*height=252\s*cellSpacing=0\s*cellPadding=0\s*width=320\s*align=left\s*border=0>(.*?)<\/table>/is';
	$content[$i] = preg_replace($preg6, '', $content[$i]);
	$preg7 = '/<FONT\sstyle=\'FONT-SIZE:\s14px;LINE-HEIGHT:25px\'>/is';
	$content[$i] = preg_replace($preg7, '', $content[$i]);
	$preg8 = '/<FONT\sstyle=\'FONT-SIZE:\s14px\'>/is';
	$content[$i] = preg_replace($preg8, '', $content[$i]);
	echo '<br><h5 style="color:red;">'.$title[$i].'</h5><br>';
	print_r($page_count[$i]);
	// print_r($content[$i]);
	
	$time[$i] = date("Y-m-d H:i:s"); //时间
	$guid[$i] = rand(10000,99999);
	$title_url[$i] = "http://localhost/program/".date('Y/m/d')."/".$guid[$i];
	// 发布
	if (mysql_query("INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, guid, menu_order, post_type, from_url) VALUES ('1', '$time[$i]', '$time[$i]', '$content[$i]', '$title[$i]', 'publish', 'open', 'open', '$guid[$i]', '$time[$i]', '$time[$i]', '0', '$title_url[$i]', '0', 'post', '$url[$i]')")) {
		$id = mysql_insert_id();
		mysql_query("INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($id, 15, '0')");
		echo $id.":".$title[$i]."<br>success<br>";
	} else {
		echo mysql_errno() . ": " . mysql_error() . "\n";
		echo "<br>insert failed<br>";
	}
	mysql_close($con);
}
?>