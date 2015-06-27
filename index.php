<?php
date_default_timezone_set('Asia/Tehran');
/**
 * Fetch the contents of a remote file.
 *
 * @param string The URL of the remote file
 * @param array The array of post data
 * @return string The remote file contents.
*/ 
function fetch_remote_file($url, $post_data=array())
{
	$post_body = '';
	if(!empty($post_data))
	{
		foreach($post_data as $key => $val)
		{
			$post_body .= '&'.urlencode($key).'='.urlencode($val);
		}
		$post_body = ltrim($post_body, '&');
	}

	if(function_exists("curl_init"))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(!empty($post_body))
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	else if(function_exists("fsockopen"))
	{
		$url = @parse_url($url);
		if(!$url['host'])
		{
			return false;
		}
		if(!$url['port'])
		{
			$url['port'] = 80;
		}
		if(!$url['path'])
		{
			$url['path'] = "/";
		}
		if($url['query'])
		{
			$url['path'] .= "?{$url['query']}";
		}

		$scheme = '';

		if($url['scheme'] == 'https')
		{
			$scheme = 'ssl://';
			if($url['port'] == 80)
			{
				$url['port'] = 443;
			}
		}

		$fp = @fsockopen($scheme.$url['host'], $url['port'], $error_no, $error, 10);
		@stream_set_timeout($fp, 10);
		if(!$fp)
		{
			return false;
		}
		$headers = array();
		if(!empty($post_body))
		{
			$headers[] = "POST {$url['path']} HTTP/1.0";
			$headers[] = "Content-Length: ".strlen($post_body);
			$headers[] = "Content-Type: application/x-www-form-urlencoded";
		}
		else
		{
			$headers[] = "GET {$url['path']} HTTP/1.0";
		}

		$headers[] = "Host: {$url['host']}";
		$headers[] = "Connection: Close";
		$headers[] = '';

		if(!empty($post_body))
		{
			$headers[] = $post_body;
		}
		else
		{
			// If we have no post body, we need to add an empty element to make sure we've got \r\n\r\n before the (non-existent) body starts
			$headers[] = '';
		}

		$headers = implode("\r\n", $headers);
		if(!@fwrite($fp, $headers))
		{
			return false;
		}
		while(!feof($fp))
		{
			$data .= fgets($fp, 12800);
		}
		fclose($fp);
		$data = explode("\r\n\r\n", $data, 2);
		return $data[1];
	}
	else if(empty($post_data))
	{
		return @implode("", @file($url));
	}
	else
	{
		return false;
	}
}
//-----------------------//-----------------------//-----------------------//-----------------------
echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>

	<channel>';
//-----------------//-----------------//-----------------//-----------------//-----------------
$con = '/////////////////////////////////////صفحه ی اعلام نتایج ysc//////////////////////////////////////////////////'
.PHP_EOL
.fetch_remote_file('http://ysc.ac.ir/include_news_post.php?id+post=196', array('postid' => '196'))
.PHP_EOL
.'/////////////////////////////////////سایت کمیته//////////////////////////////////////////////////'
.PHP_EOL
.fetch_remote_file('http://inoi.ir')
.PHP_EOL
.'/////////////////////////////////////صفحه اصلی ysc//////////////////////////////////////////////////'
.PHP_EOL
.fetch_remote_file('http://ysc.ac.ir/');
$old=file_get_contents("ysc.txt");
$all=file_get_contents("all.txt");
if($old!=$con){
//یک بار دیگر چک می کنیم
$con = '/////////////////////////////////////صفحه ی اعلام نتایج ysc//////////////////////////////////////////////////'
.PHP_EOL
.fetch_remote_file('http://ysc.ac.ir/include_news_post.php?id+post=196', array('postid' => '196'))
.PHP_EOL
.'/////////////////////////////////////سایت کمیته//////////////////////////////////////////////////'
.PHP_EOL
.fetch_remote_file('http://inoi.ir')
.PHP_EOL
.'/////////////////////////////////////صفحه اصلی ysc//////////////////////////////////////////////////'
.PHP_EOL
.fetch_remote_file('http://ysc.ac.ir/');
if($old!=$con){
echo '	<item>
		<title>'.date("Y/m/d  H:i:s").' update</title>
	</item>'.$all;
file_put_contents("all.txt",'	<item>
		<title>'.date("Y/m/d  H:i:s").' update</title>
	</item>'.$all);
file_put_contents("ysc.txt",$con);
file_put_contents('./changelog/'.date("YmdHis").'.txt',$old);
}
}
else
echo $all;
echo '	</channel>
</rss>
';
?>
