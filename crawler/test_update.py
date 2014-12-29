# -*- coding: utf-8 -*-
import urllib2
import json
import time,datetime
import sys
import xmltodict
import MySQLdb
from bs4 import BeautifulSoup

reload(sys)
sys.setdefaultencoding('utf8')

def getlastupdate(name):
	conn = MySQLdb.connect('localhost', 'root', 'root', 'weixincrawler', charset='utf8', use_unicode=True)
	select_stmt = "SELECT lastModified FROM `%s` ORDER BY lastModified DESC LIMIT 1" %(name)
	with conn:
		cursor = conn.cursor()
		cursor.execute(select_stmt)
		results = cursor.fetchall()
		if results:
			return time.mktime(results[0][0].timetuple())
		else:
			return 0

def getallaccount():
	accounts=[]
	conn = MySQLdb.connect('localhost', 'root', 'root', 'weixincrawler', charset='utf8', use_unicode=True)
	select_stmt = "SELECT name, link FROM list"
	with conn:
		cursor = conn.cursor()
		cursor.execute(select_stmt)
		results = cursor.fetchall()
	for row in results:
		account={}
		account["name"]=row[0]
		account["link"]="http://weixin.sogou.com/gzhjs?" + row[1].split("gzh?")[1]
		accounts.append(account)
	return accounts

def getcontent(link):
	content = urllib2.urlopen(link).read()
	soup = BeautifulSoup(content)
	article = soup.find("div", class_="rich_media_content")
	print link
	for imagetag in article.find_all('img'):
		try:
			imagetag['src'] = imagetag['data-src']
		except:
			pass
	
	if (soup.find("div", class_="rich_media_thumb") != None):
		imgsrc = soup.find("div", class_="rich_media_thumb").text.replace(" ", "").split('varcover=')[1].split(';')[0]
		imgtag = BeautifulSoup('<p><img id="js_cover" src='+ imgsrc + '></p>').p		
		article.find('p').insert_before(imgtag)
	return article

def crawl(tablename, link):
	conn = MySQLdb.connect('localhost', 'root', 'root', 'weixincrawler', charset='utf8', use_unicode=True);
	jsonp = urllib2.urlopen(link).read()
	jsonobj = json.loads(jsonp[jsonp.index("(") + 1 : jsonp.rindex(")")])
	pageitems = jsonobj["items"]
	lastUpdateTimestamp = getlastupdate(tablename)
	for i in range(len(pageitems)):
		obj = xmltodict.parse(jsonobj["items"][i])
		lastModifiedTimestamp = float(obj["DOCUMENT"]["item"]["display"]["lastModified"])
		if 1:
			docid = obj["DOCUMENT"]["item"]["display"]["docid"]
			title = conn.escape_string(obj["DOCUMENT"]["item"]["display"]["title1"])
			url = obj["DOCUMENT"]["item"]["display"]["url"]
			description = conn.escape_string(str(getcontent(url)))
			date = obj["DOCUMENT"]["item"]["display"]["date"]
			lastModified = datetime.datetime.fromtimestamp(lastModifiedTimestamp).strftime("%Y-%m-%d %H:%M:%S")
			insert_stmt = "INSERT INTO `%s` (docid, title, url, description, date, lastModified) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')" %(tablename, docid, title, url, description, date, lastModified)
			with conn:
				cursor = conn.cursor()
				try:
					cursor.execute(insert_stmt)
				except MySQLdb.IntegrityError:
					update_stmt = "UPDATE `%s` SET description = '%s' WHERE docid='%s'" %(tablename, description, docid)
					cursor.execute(update_stmt)
					pass

# try:
logFile = open("weixincrawler.log", 'a')
logFile.write(datetime.datetime.fromtimestamp(time.time()).strftime("\n----%Y-%m-%d %H:%M:%S----\n"))
accounts = getallaccount()
for account in accounts:
	crawl(account["name"],account["link"])
# except Exception as err:
#     print err
#     logFile.write(str(err)+"\n")
# finally:
#     logFile.close()