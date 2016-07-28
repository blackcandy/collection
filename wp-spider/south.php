<?php
/**
*  抓取南方财富网k线图
*/
set_time_limit(0);
class Collection
{
	private $url;
	private $category;
	// private $rel_link = 'http://rumen.southmoney.com';
	const REL_LINK = 'http://rumen.southmoney.com';
	const HOST = 'localhost';
	const USER = 'root';
	const PSD = 'root';
	const DATE_BASE = 'program';
	/**
     * @param String $url         需要采集的页面地址
     * @param Int $category       需要保存文章分类
     */
	function __construct($url, $category)
	{
		$this->url = $url;
		$this->category = $category;  
	}
	/**
	 * 打开页面
     * @return String $web         获取到页面内容
     */
	function open(){
		if($this->check($this->url)){
			echo '<span style="color:red;">this web has collected!</span><br>
				  <a href="./form.php">back</a>';
			die;
		}
		$web = file_get_contents($this->url);
		return $web;
	}
	/**
	 * 获取列表
     * @return String $link         内容页完整路径
     */
	function listing(){
		$preg = '/<ul class="newslist">(.*?)<\/ul>/is';
		preg_match_all($preg, $this->open(), $range);
		$preg2 = '/<a href="(.*?)".*?<\/a>/is';
		preg_match_all($preg2, $range[1][0], $list);
		function completion($n){
			return 'http://rumen.southmoney.com'.$n;
			// $rel_link = self::REL_LINK;
			// return $rel_link.$n;
		}
		$link = array_map("completion", $list[1]);
		return $link;
	}
	/**
	 * 保存文章
     * @return Boole true           处理完毕返回值
     */
	function get(){
		$link = $this->listing();
		// 连接数据库
		$con = mysql_connect(self::HOST, self::USER, self::PSD);
		if (!$con) {
			die('Could not connect: '. mysql_error());
		}
		mysql_select_db(self::DATE_BASE, $con);


		$preg3 = '/<h1 class="artTitle">(.*?)<\/h1>.*?<div class="articleCon">(.*?)<\/div>\s*<div class="articleFoot">/is';
		for ($i=0; $i < count($link); $i++) { 
			// 查重
			$sql[$i] = "SELECT ID FROM wp_posts WHERE from_url='$link[$i]'";
			$result[$i] = mysql_query($sql[$i]);
			$rs[$i] = mysql_fetch_array($result[$i]);
			if ($rs[$i]) {
				echo $rs[$i][0].':<br><span style="color:red;">is existed!</span><br>';
				continue; 
			}

			$content_web[$i] = file_get_contents($link[$i]);
			preg_match_all($preg3, $content_web[$i], $arr[$i]);
			$title[$i] = $arr[$i][1][0];
			$content[$i] = $this->img($arr[$i][2][0]);
			$content[$i] = $this->filter($content[$i]);
			
			$time[$i] = date("Y-m-d H-i-s");
			$guid[$i] = $this->url($i);
			$url[$i] = "http://localhost/program/".date('Y/m/d')."/".$guid[$i];
			// 发布
			if (mysql_query("INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, guid, menu_order, post_type, from_url) VALUES ('1', '$time[$i]', '$time[$i]', '$content[$i]', '$title[$i]', 'publish', 'open', 'open', '$guid[$i]', '$time[$i]', '$time[$i]', '0', '$url[$i]', '0', 'post', '$link[$i]')")) {
				$id = mysql_insert_id();
				mysql_query("INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($id, $this->category, '0')");
				echo $id.":".$title[$i].'<br><span style="color:red;">insert success!</span><br>';
			} else {
				echo '<br><span style="color:red;">insert failed!</span><br>';
			}
		}
		mysql_close($con);
		if($this->write($this->url)){
			echo '<span style="color:green;">completed!</span><br><br>
				  <a href="./form.php">back</a><br><br>
				  <form action="" method="post">
					NEXT-URL:<input type="hidden" name="url" value="'.$this->next().'"><a href="'.$this->next().'">'.$this->next().'</a>
					Category:<input type="hidden" name="category" value="'.$this->category.'">'.$this->category.'<br>
					<button type="submit">next</button>
				  </form>';
		}	
	}
	/**
	 * 图片路径修正
	 * @param Array $m              文章内容
     * @return Array $content       返回处理后的内容
     */
	function img($m){
		$preg4 = '/\/Kxian\//';
		$repl = 'http://rumen.southmoney.com/Kxian/';
		return preg_replace($preg4, $repl, $m);
	}
	/**
	 * 过滤JavaScript
	 * @param Array $j              文章内容
     * @return Array $content       返回过滤后的内容
     */
	function filter($j){
		$preg5 = '/(<a.*?>).*?(<\/a>)/is';
		$preg6 = '/(<script language="javascript".*?><\/script>)/is';
		$repl = '';
		return preg_replace($preg6, $repl, preg_replace($preg5, $repl, $j));
	}
	/**
	 * 创建guid
	 * @param Int $i                遍历的key值
     * @return String $guid[$i]     返回生成的guid
     */
	function url($i){
		$guid[$i] = rand(10000,99999);
		return $guid[$i];
	}
	/**
	 * 检查是否采集过页面
	 * @param String $url           采集网站url
     * @return Boole                返回是否采集过该url
     */
	function check($url){
		$txt = file_get_contents('./record.txt');
		$checked = explode("\r\n", $txt);
		if(in_array($url, $checked)){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 记录采集过的页面
	 * @param String $url           采集网站url
     * @return Boole true           返回处理完毕
     */
	function write($url){
		$handle = fopen('./record.txt', "a");
		if(fwrite($handle, "\r\n".$url)){
			fclose($handle);
			return true;
		}	
	}
	/**
	 * 获取下一个页面
	 * @param String $url           采集网站url
     * @return Boole true           返回处理完毕
     */
	function next(){
		$web = file_get_contents($this->url);
		$preg = '/<font\scolor="FF0000">(.*?)<\/font>\s*<a\shref="(.*?)">.*?<\/a>/';
		preg_match_all($preg, $web, $next);
		$next_url = 'http://rumen.southmoney.com'.$next[2][0];
		return $next_url;
	}
}

/**
 * 获取数据
 * @param String $_POST              post过来的数据
 */
$run = new Collection($_POST['url'], $_POST['category']);
$run->get();
?>