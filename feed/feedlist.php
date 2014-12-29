<head>
<meta charset="UTF-8">
<title>Feed List</title>
</head>

<html>
<body>

<form action="add.php" method="post">
微信公众号名称: <input type="text" name="name"><br>
sogou对应的链接: <input type="text" name="link"><br>
<input type="submit">
</form>

<?php
echo "<table border='1'>
	  	<tr>
	    <td>微信公众号名称</td>
	    <td>描述</td>		
	    <td>RSS链接</td>
	  	</tr>";

include ("db_conn.php");
$sql = "SELECT * FROM list";
mysql_query('set names utf8');
mysql_query("set character set 'utf8'");
$retval = mysql_query($sql);
while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
	{
		$name=$row['name'];
		$description = $row['description'].$row['account'];
		$rsslink = 'http://maview.us/feed/rss.php?id='.$name;
		echo "<tr>
			    <td>$name</td>
			    <td>$description</td>		
			    <td><a href='$rsslink'>RSS</a></td>
			  </tr>";
	}
echo "</table>";
?>
</body>
</html>