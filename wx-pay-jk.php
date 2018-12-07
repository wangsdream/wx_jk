<?php
header("Content-type: text/html; charset=utf-8");


date_default_timezone_set("Asia/Shanghai");

/**
*王震
*测试微信支付功能 2017-02-15
*service :接口类型
*version :版本号
*mch_id  :商户号
*out_trade_no :商户订单号
*body    :商品描述
*total_fee :总金额
*mch_create_ip :终端IP
*nonce_str  :随机字符串
*auth_code  :授权码
*
*/

$request = array(
	"service" => "pay.weixin.micropay", 
	"version" => "2.0",
	"mch_id" => "010154000014",
	"out_trade_no" => date("YmdHis"),
	"body" => "测试购买商品",
	"total_fee" => 1,
	"mch_create_ip" => "127.0.0.1",
	"nonce_str" => rand(),
	"auth_code" => "",

);
$key = "key";

ksort($request);

$query = array();
foreach($request as $k => $v){
	$query[] = $k . "=" . $v;
}

$sign = strtoupper(md5(implode("&", $query) . "&key=" . $key));

$xml = array();
foreach($request as $k => $v){
	$xml[] = "<" . $k .">" . $v . "</" . $k . ">";
}
$xml[] = "<sign>" . $sign . "</sign>";

$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';  //微信支付请求地址


/**
*下方为curl请求
**/
$header[] = "Content-type: text/xml";
$ch = curl_init ($url);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "<xml>" . implode($xml) . "</xml>");

$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response,true);

var_dump($response);


$weixin_xml = simplexml_load_string($response);

/*
*输出结果
*/
echo iconv("utf-8", "gbk", "测试功能:支付\n");
echo iconv("utf-8", "gbk", "测试结果:").iconv("utf-8", "gbk", $weixin_xml->return_msg);

exit;

?>