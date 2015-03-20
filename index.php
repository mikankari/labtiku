<?php
$url = 'https://www.google.com/calendar/feeds/8p80s16or98dh8nujqf9tma1amrsofko%40import.calendar.google.com/public/basic';
$session = curl_init($url);
curl_setopt($session, CURLOPT_TIMEOUT_MS, 5000);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($session, CURLOPT_HTTPHEADER, array());
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
$text = curl_exec($session);
curl_close($session);
$text = str_replace("openSearch:", "", $text);
$text = str_replace("gCal:", "", $text);

if($text){
	$entries_display = array();
	$entries_interval = array();
	$dom = simplexml_load_string($text);
	foreach($dom->children() as $entry){
		if($entry->getName() === "entry"){
			if(preg_match('/(\d{4}\/\d{2}\/\d{2}) \([日月火水木金土]\) (\d{2}\:\d{2}).(\d{2}\:\d{2})/u', $entry->content, $matches)){
				$start = date_timestamp_get(date_create_from_format("Y/m/d H:i", $matches[1] . " " . $matches[2], timezone_open("UTC")));
				$end = date_timestamp_get(date_create_from_format("Y/m/d H:i", $matches[1] . " " . $matches[3], timezone_open("UTC")));
			}else if(preg_match('/(\d{4}\/\d{2}\/\d{2}) \([日月火水木金土]\) (\d{2}\:\d{2}).(\d{4}\/\d{2}\/\d{2}) \([日月火水木金土]\) (\d{2}\:\d{2})/u', $entry->content, $matches)){
				$start = date_timestamp_get(date_create_from_format("Y/m/d H:i", $matches[1] . " " . $matches[2], timezone_open("UTC")));
				$end = date_timestamp_get(date_create_from_format("Y/m/d H:i", $matches[3] . " " . $matches[4], timezone_open("UTC")));
			}else if(preg_match('/(\d{4}\/\d{2}\/\d{2}) \([日月火水木金土]\).(\d{4}\/\d{2}\/\d{2}) \([日月火水木金土]\)/u', $entry->content, $matches)){
				$start = date_timestamp_get(date_create_from_format("Y/m/d H:i", $matches[1] . " 09:00", timezone_open("UTC")));
				$end = date_timestamp_get(date_create_from_format("Y/m/d H:i", $matches[2] . " 09:00", timezone_open("UTC")));
			}
			$now = date_timestamp_get(date_create("now", timezone_open("UTC")));
			if($now > $start && $now < $end){
				array_push($entries_display, $entry->title);
				array_push($entries_interval, $end - $start);
			}
		}
	}

	$entries_interval_min = PHP_INT_MAX;
	$entries_interval_key = null;
	foreach($entries_interval as $key => $value){
		if($value < $entries_interval_min){
			$entries_interval_min = $value;
			$entries_interval_key = $key;
		}
	}
	if($entries_interval_key !== null){
		$result = $entries_display[$entries_interval_key];
	}else{
		$result = "帰宅";
	}
}else{
	$result = "取得失敗";
}

/********** Twitter　@mikankari の最新ツイート取得 **********/
/********** ライブラリドキュメント：https://twitteroauth.com/ **********/
$consumer_key = "bPx4iWt1ufT5RDVTn8O0oybHG";
$consumer_secret = "m1DSAcgj2VLCNzbV2kkXAia1X0a5lZuMqlkPdY2lRu3rf4vp2q";
$access_token = "995222642-vFxv5pimW2JPUXWk0tzft6CRvhr3qm9tHjxyYCus";
$access_token_secret = "5YngS3WSWHhikdbfY3anp20rQUNJsvdopEfcvmY8iyVRm";
require_once("twitteroauth/autoload.php");
use Abraham\TwitterOAuth\TwitterOAuth;

$oauth = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
$response = $oauth->get("statuses/user_timeline", array(
	"count" => "1",
	"exclude_replies" => "true",
	"include_rts" => "false"
));
$status = $response[0];
$name = "<img src=\"images/Twitter_logo_white.png\" alt=\"Twitter Bird\"> <span>{$status->user->name}</span><a href=\"https://twitter.com/mikankari/\" target=\"_blank\"><small>(@{$status->user->screen_name})</small></a>";
$created_ago = date_diff(date_create(), date_create_from_format("D M d H:i:s P Y", $status->created_at));
if($created_ago->days > 0){
	$created_ago = $created_ago->format("%a 日前");
}else if($created_ago->h > 0){
	$created_ago = $created_ago->format("%h 時間前");
}else{
	$created_ago = $created_ago->format("%i 分前");
}
$tweet = "{$status->text}<br><a href=\"https://twitter.com/mikankari/status/{$status->id_str}\" target=\"_blank\"><small>{$created_ago}</small></a>";
/********** ここまで **********/
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ラボ畜カレンダー（β）</title>
<style>
	body,p,blockquote,h1,h2,h3{
		margin: 0px;
		padding: 0px;
	}
	a{
		color: inherit;
	}
	a:hover,a:focus{
		
	}
	section>h2,section>div{
		margin: 10px;
	}
	#container{
		margin: 0px auto;
		width: 1020px;
	}
	.clearfix:after{
		content: "";
		display: block;
		clear: both;
	}
	.clearfix .left{
		float: left;
	}
	.result{
		width: 560px;
		height: 420px;
		position: relative;
	}
	.result>div{
		position: absolute;
	}
	.resultbox{
		background-color: rgba(102, 136, 238, 1);
		color: #ffffff;
		padding: 5px;
		border: 3px ridge rgba(102, 136, 238, 1);
		left: 20px;
		top: 20px;
		z-index: 200;
		width: 200px;
/*		height: 50px;*/
	}
	.namebox{
		background-color: rgba(102, 136, 238, 1);
		color: #ffffff;
		padding: 5px;
		border: 3px ridge rgba(102, 136, 238, 1);
		left: 57px;
		bottom: 110px;
		z-index: 100;
/*		width: 100px;*/
	}
	.tweetbox{
		background-color: rgba(102, 136, 238, 1);
		color: #ffffff;
		padding: 10px;
		border: 3px ridge rgba(102, 136, 238, 1);
		left: 57px;
		bottom: 20px;
		z-index: 110;
		width: 420px;
		height: 64px;
	}
	.resultbox strong{
		font-weight: normal;
	}
	.calendar{
		width: 420px;
		height: 420px;
	}
	.install{
		width: 556px;
		height: auto;
	}
	.install h3{
		font-size: 100%;
		margin: 1em 0px;
	}
	.install>div{
		padding-left: 20px;
	}
	.install p{
		margin: 5px 0px;
	}
	.install input{
		width: 100%;
	}
	.ad{
		text-align: center;
	}
</style>
<script>
	var labtiku = {};
	labtiku.init = function (event){
		var inputs = document.querySelectorAll(".install input");
		var callback = function (event){
			event.target.focus();
			event.target.select();
		};
		var i;
		for(i = 0; i < inputs.length; i++){
			inputs.item(i).addEventListener("click", callback, false);
		}
	}
	window.addEventListener("DOMContentLoaded", labtiku.init, false);
</script>
</head>

<body>
<div id="container">
	<div class="clearfix">
		<div class="left">
			<section>
				<h2>いまどこにいる？</h2>
				<div class="result">
					<div class="resultbox"><strong><?php echo $result; ?></strong></div>
					<div><img src="http://placehold.it/560x420" alt=""></div>
					<div class="namebox"><?php echo $name; ?></div>
					<div class="tweetbox"><blockquote cite="https://twitter.com/mikankari"><p><?php echo $tweet ?></p></blockquote></div>
				</div>
			</section>
		</div>
		<div class="left">
			<section>
				<h2>このあとは…</h2>
				<div class="calendar"><iframe src="https://www.google.com/calendar/embed?showTitle=0&amp;showCalendars=0&amp;showTz=0&amp;mode=AGENDA&amp;height=420&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=8p80s16or98dh8nujqf9tma1amrsofko%40import.calendar.google.com&amp;color=%23AB8B00&amp;ctz=Asia%2FTokyo" style=" border-width:0 " width="420" height="420" frameborder="0" scrolling="no"></iframe></div>
			</section>
		</div>
	</div>
	<div>
		<div>
			<section>
				<h2>カレンダーアプリを使いますか？</h2>
				<div class="install">
					<h3>iCal形式</h3>
					<div><p>GoogleカレンダーやOutlookなどで予定を表示できます</p><div><input type="text" value="https://www.google.com/calendar/ical/8p80s16or98dh8nujqf9tma1amrsofko%40import.calendar.google.com/public/basic.ics"></div></div>
					<h3>XML形式</h3>
					<div><p>どこで使うのでしょうね</p><div><input type="text" value="<?php echo $url ?>"></div></div>
				</div>
			</section>
		</div>
	</div>
	<div>
		<div class="ad"><!--nobanner--><script type="text/javascript" src="http://imgj.xrea.com/xa.j?site=mikankari.s602.xrea.com"></script><noscript><iframe height="60" width="468" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true" src="http://img.xrea.com/ad_iframe.fcg?site=mikankari.s602.xrea.com"><a href="http://img.xrea.com/ad_click.fcg?site=mikankari.s602.xrea.com" target="_blank"><img src="http://img.xrea.com/ad_img.fcg?site=mikankari.s602.xrea.com" border="0" alt="xreaad"></a></iframe></noscript></div>
	</div>
</div>
</body>
</html>
