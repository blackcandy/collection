<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<span style="color:green;">completed:</span><br>
<?php
$txt = file_get_contents('./record.txt');
$checked = explode("\r\n", $txt);
foreach ($checked as $key => $value) {
	echo $value.'<br>';
}
?>
<br>
<form action="./south.php" method="post">
	URL:<input type="text" name="url">
	Category:<input type="text" name="category"><br>
	<button type="submit">submit</button>
</form>
</body>
</html>