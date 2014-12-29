<?php

include ("db_conn.php");
include ("simple_html_dom.php");

$name = $_POST['name'];
$link = $_POST['link'];

$html = file_get_html($link);
$description = $html->find('span[class=sp-txt]',0)->plaintext;
$account = $html->find('h4',0)->plaintext;

$sql = "INSERT INTO list (name, link, description, account) VALUES ('$name','$link','$description','$account')";

mysql_query('set names utf8');
$retval = mysql_query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `$name` (
		  docid varchar(68) COLLATE utf8_unicode_ci NOT NULL,
		  title text COLLATE utf8_unicode_ci NOT NULL,
		  url text COLLATE utf8_unicode_ci NOT NULL,
		  description mediumtext COLLATE utf8_unicode_ci NOT NULL,
		  date date NOT NULL,
		  lastModified datetime NOT NULL,
		  PRIMARY KEY (docid),
		  UNIQUE KEY docid (docid)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

mysql_query('set names utf8');
$retval = mysql_query($sql);

if(! $retval )
{
	die('Could not insert data: ' . mysql_error());
}

else
{
	header("Location: http://maview.us/feed/feedlist.php");
}
