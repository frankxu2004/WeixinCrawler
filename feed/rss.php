<?php

// You should use an autoloader instead of including the files directly.
// This is done here only to make the examples work out of the box.
include 'Item.php';
include 'Feed.php';
include 'RSS2.php';
include 'db_conn.php';

date_default_timezone_set('Asia/Shanghai');

use \FeedWriter\RSS2;

if ($_GET['id'])
{
	$name = $_GET['id'];

	$sql = "SELECT * FROM list WHERE name = '$name'";

	mysql_query('set names utf8');
	mysql_query("set character set 'utf8'");
	$retval = mysql_query($sql);
	$row = mysql_fetch_array($retval, MYSQL_ASSOC);

	$TestFeed = new RSS2;

	$TestFeed->setTitle($row['name']);
	$TestFeed->setLink($row['link']);
	$TestFeed->setDescription($row['description']);
	$TestFeed->setChannelElement('language', 'zh-CN');
	$TestFeed->setDate(date(DATE_RSS, time()));
	$TestFeed->setChannelElement('copyright', $row['account']);
	$TestFeed->addGenerator();


	mysql_query("set character set 'utf8'");
	$sql = "SELECT * FROM `$name` ORDER BY lastModified DESC";
	mysql_select_db('weixincrawler');
	$retval = mysql_query($sql);

	if(! $retval )
	{
	  die('Could not get data: ' . mysql_error());
	}

	while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
	{
		$newItem = $TestFeed->createNewItem();
		$newItem->setTitle($row["title"]);
		$newItem->setLink($row["url"]);
		$newItem->setDescription($row["description"]);
		$newItem->setDate($row["lastModified"]);
		$newItem->setAuthor($name);
		$newItem->setId($row["url"], true);
		$TestFeed->addItem($newItem);
	}

	// OK. Everything is done. Now generate the feed.
	// If you want to send the feed directly to the browser, use the printFeed() method.
	$myFeed = $TestFeed->generateFeed();

	// Do anything you want with the feed in $myFeed. Why not send it to the browser? ;-)
	// You could also save it to a file if you don't want to invoke your script every time.
	echo $myFeed;
	mysql_close($conn);
}