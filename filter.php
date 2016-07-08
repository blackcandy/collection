<?php
header("Content-type:text/html;charset=utf-8");
set_time_limit(0);
// ignore_user_abort(); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.

// 设置参数
$homepage1 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=sports');
$category1 = 1;
$homepage2 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=yule');
$category2 = 2;
$homepage3 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=it');
$category3 = 3;
$homepage4 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=auto');
$category4 = 4;
$homepage5 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=focus');
$category5 = 5;
$homepage6 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=women');
$category6 = 6;
$homepage7 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=cul');
$category7 = 7;
$homepage8 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=fashion');
$category8 = 8;
$homepage9 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=health');
$category9 = 9;
$homepage10 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=learning');
$category10 = 10;
$homepage11 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=money');
$category11 = 11;
$homepage12 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=stock');
$category12 = 12;
$homepage13 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=games');
$category13 = 13;
$homepage14 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=media');
$category14 = 14;
$homepage15 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=star');
$category15 = 15;
$homepage16 = file_get_contents('http://www.zhaoshuoshuo.com/news/?c=travel');
$category16 = 16;


// 抓取函数
function spider($homepage, $category){
	$preg = '/<a.*?href="(.*?)".*?>(.*?)<\/a><span.*?>(.*?)<\/span>/';
	preg_match_all($preg, $homepage, $match);
	for ($i=0; $i < count($match[1]); $i++) { 
		$title[$i] = $match[2][$i]; //标题
		$time[$i] = date("Y")."-".$match[3][$i].":00"; //时间
		$link[$i] = 'http://www.zhaoshuoshuo.com/news/'.$match[1][$i]; //链接
		// 连接数据库
		$con = mysql_connect("localhost","dedecms","Fw2590Ncpe6");
		if (!$con) {
			die('Could not connect: '. mysql_error());
		}
		mysql_select_db("dedecms", $con);
		mysql_query("set names 'utf8' ");
		mysql_query("set character_set_client=utf8");
		mysql_query("set character_set_results=utf8");
		// 查重
		$sql = "SELECT ID FROM dm_posts WHERE spider_url='$link[$i]'";
		$result = mysql_query($sql);
		$rs = mysql_fetch_array($result);
		if ($rs) {
			echo $rs[0].":".$title[$i]."已存在<br>";
			continue; //跳过本次循环
		}
		
		$content[$i] = file_get_contents($link[$i]);
		// print_r($content[$i]);
		$text_preg = '/<div\s*class="text\s*clear"\s*id="contentText">(.*?)<SCRIPT>/is';
		preg_match_all($text_preg, $content[$i], $text_match[$i]);
		// $text_content[$i] = $text_match[$i][0][0]; //内容

		$rand = mysql_query("SELECT * FROM dm_extend ORDER BY rand() LIMIT 1");
		while ($ra = mysql_fetch_array($rand)){ //返回查询结果到数组
			$extend_title = $ra["title"]; //将数据从数组取出
			$extend_url = $ra["url"];
			$extend = '<br><a href="'.$extend_url.'">'.$extend_title.'</a><br>';
		}
		$text_content[$i] = str_replace('<SCRIPT>', $extend, $text_match[$i][0][0]); //内容
		// print_r($text_content[$i]);

		$guid[$i] = rand(10000,99999);
		$url[$i] = "http://www.nxlhcec.com/wordpress/".date('Y/m/d')."/".$guid[$i];
		// 发布
		if (mysql_query("INSERT INTO dm_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, guid, menu_order, post_type, spider_url) VALUES ('1', '$time[$i]', '$time[$i]', '$text_content[$i]', '$title[$i]', 'publish', 'open', 'open', '$guid[$i]', '$time[$i]', '$time[$i]', '0', '$url[$i]', '0', 'post', '$link[$i]')")) {
			$id = mysql_insert_id();
			mysql_query("INSERT INTO dm_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($id, $category, '0')");
			echo $id.":".$title[$i]."<br>抓取成功！<br>";
		} else {
			echo "insert failed<br>";
		}
		mysql_close($con);
	}
}


// 自动执行
$interval=60*60; //间隔时间
do {
	spider($homepage1, $category1);
	// spider($homepage2, $category2);
	// spider($homepage3, $category3);
	// spider($homepage4, $category4);
	// spider($homepage5, $category5);
	// spider($homepage6, $category6);
	// spider($homepage7, $category7);
	// spider($homepage8, $category8);
	// spider($homepage9, $category9);
	// spider($homepage10, $category10);
	// spider($homepage11, $category11);
	// spider($homepage12, $category12);
	// spider($homepage13, $category13);
	// spider($homepage14, $category14);
	// spider($homepage15, $category15);
	// spider($homepage16, $category16);

	sleep($interval); //按设置的时间等待1小时循环执行
} while(true);
?>