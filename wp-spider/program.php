<?php
/*
** @Author:spenser
** @Date:2016-07-12
** @Explain:k线入门图解, 经典头部K线形态图解, 经典底部K线形态图解, k线图解分析教程, 基本K线组合形态图解, k线图分析实战, 酒田战法(K线战法78条), k线组合口诀图解, k线组合形态图解, K线组合形态经典图解, 基本K线组合及应用图解,
**  k线形态实战技术图解
** @warning:内容中的相关链接
*/
set_time_limit(0);
$enter_url = "http://www.net767.com/gupiao/k/";
$localhost = "localhost";
$usn = "root";
$psd = "root";
$db = "program";
$key[0] = "k线入门图解";
$key[1] = "经典头部K线形态图解";
$key[2] = "经典底部K线形态图解";
$key[3] = "k线图解分析教程";
$key[4] = "基本K线组合形态图解";
$key[5] = "k线图分析实战";
$key[6] = "酒田战法";
$key[7] = "k线组合口诀图解";
$key[8] = "k线组合形态图解";
$key[9] = "K线组合形态经典图解";
$key[10] = "基本K线组合及应用图解";
$key[11] = "k线形态实战技术图解";

function news($enter_url, $key) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $enter_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$web = curl_exec($ch);
	curl_close($ch);
	$str = mb_convert_encoding($key, "GB2312", "UTF-8");
	$preg = '/(>'.$str.'<|>'.$str.'.*?<\/STRONG><\/SPAN><\/FONT><\/P><\/td>)(.*?)<\/table>/is';
	preg_match_all($preg, $web, $area);
	$preg2 = '/<a.*?href="(.+?)".*?>(.+?)<\/a>/';
	preg_match_all($preg2, $area[2][0], $link);
	for ($i=0; $i < count($link[1]); $i++) { 
		$title[$i] = $link[2][$i]; //标题
		$url[$i] = 'http://www.net767.com/'.$link[1][$i]; //链接
		$ch2[$i] = curl_init();
		curl_setopt($ch2[$i], CURLOPT_URL, $url[$i]);
		curl_setopt($ch2[$i], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2[$i], CURLOPT_HEADER, 0);
		$news_web[$i] = curl_exec($ch2[$i]);
		curl_close($ch2[$i]);
		// 匹配内容页第一页正文 
		$preg3 = '/(<td\s*class=neirong\s*vAlign=top\s*align=left>|<td\s*vAlign=top\s*class=neirong\s*colSpan=2\s*height=241\s*width="92%">)(.*?)(<div\s*id=\'pagebar\'>|<td\s*width="3%"><\/td>
		)/is';
		preg_match_all($preg3, $news_web[$i], $page[$i]);
		$content[$i] = $page[$i][2][0]; //内容页正文（第一页）
		// 匹配内容页分页链接区域
		$preg4 = '/<a\s*href=\'(.*?)\'>(.*?)<\/a>/';
		preg_match_all($preg4, $news_web[$i], $pagebar[$i]);
		$end_page_url[$i] = end($pagebar[$i][1]);

		// 方法一
		// for ($j=2; $j<30; $j++) { 
		// 	$new_url[$i][$j] = str_replace(".html", "_".$j.".html", $url[$i]);
		// 	if (!(varify_url($new_url[$i][$j]))) {
		// 		echo $j."wrong<br>";
		// 		break;
		// 	}
		// 	$ch3[$i][$j] = curl_init();
		// 	curl_setopt($ch3[$i][$j], CURLOPT_URL, $new_url[$i][$j]);
		// 	curl_setopt($ch3[$i][$j], CURLOPT_RETURNTRANSFER, 1);
		// 	curl_setopt($ch3[$i][$j], CURLOPT_HEADER, 0); 
		// 	$paging[$i][$j] = curl_exec($ch3[$i][$j]);
		// 	curl_close($ch3[$i][$j]);
		// 	// 匹配内容页分页正文
		// 	preg_match_all($preg3, $paging[$i][$j], $paging_text[$i][$j]);
		// 	$paging_content[$i][$j] = $paging_text[$i][$j][1][0]; //内容页正文（分页）
		// 	$content[$i] .= $paging_content[$i][$j]; //拼接正文	
		// }


		// 方法二
		$preg5 = '/\_(.*?)\.html/';
		preg_match_all($preg5, $end_page_url[$i], $end_page[$i]);
		$page_count[$i] = $end_page[$i][1][0]; //内容页分页数量
		print_r($page_count[$i]);
		if ($page_count[$i] == 2) {
			$end_page_url[$i] = array_slice($pagebar[$i][1],-2,1);
			print_r($pagebar[$i]);
			preg_match_all($preg5, $end_page_url[$i][0], $end_page[$i]);
			$page_count[$i] = $end_page[$i][1][0]; //内容页分页数量
			if ($page_count[$i] == 10) {
				for ($j=10; $j<30; $j++) { 
					$new_url[$i][$j] = str_replace(".html", "_".$j.".html", $url[$i]);
					if (!(varify_url($new_url[$i][$j]))) {
						echo $j."wrong<br>";
						break;
					}
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
			}
		} else {
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
		}
		// 去广告
		$preg6 = '/<table\s*height=252\s*cellSpacing=0\s*cellPadding=0\s*width=320\s*align=left\s*border=0>(.*?)<\/table>/is';
		$content[$i] = preg_replace($preg6, '', $content[$i]);
		echo '<br><h5 style="color:red;">'.$title[$i].'</h5><br>';
		print_r($content[$i]);
	}
}
news($enter_url, $key[0]);
function varify_url($url)
{
	$check = @fopen($url,"r");
	if($check) {
		$status = true;
	}
	else {
		$status = false;
	}	
	return $status;
}
?>