<?php
require_once 'ini.php';

$request_url = "https://tgw.baofoo.com/apipay/sdk";  //SDK尊享版请求地址

$txn_sub_type = "02"; //SDK交易类型为02
$id_card = isset($_POST["id_card"]) ? $_POST["id_card"] : "";  //身份证号
$id_holder = isset($_POST["id_holder"]) ? $_POST["id_holder"] : "";	//持卡人姓名
$mobile = isset($_POST["mobile"]) ? $_POST["mobile"] : "";	//持卡人手机号
$txn_amt = isset($_POST["txn_amt"]) ? $_POST["txn_amt"] : "";	//交易金额
$page_url = "http://127.0.0.1:8008";
$return_url = "http://10.0.60.26:8008/return_url.php";//支付结果服务器通知地址
$pay_code = isset($_POST["pay_code"]) ? $_POST["pay_code"] : "";	//银行编码
$acc_no = isset($_POST["acc_no"]) ? $_POST["acc_no"] : "";	//银行卡号

//ob_start (); //打开缓冲区
$arr = array ('txn_sub_type'=>$txn_sub_type,
			  'biz_type'=>"0000",
			  'terminal_id'=>$terminal_id,
			  'member_id'=>$member_id,
			  'pay_code'=>$pay_code,
			  'acc_no'=>$acc_no,
			  'id_card_type'=>"01",
			  'id_card'=>$id_card,
			  'id_holder'=>$id_holder,
			  'mobile'=>$mobile,
			  'trans_id'=>"TID".get_transid().rand4(),
			  'txn_amt'=>$txn_amt,
			  'trade_date'=>return_time(),
			  'page_url'=>$page_url,
			  'return_url'=>$return_url
			  );

$baofoosdk = new BaofooSdk($pfxfilename,$cerfilename,$private_key_password); //初始化加密类。		  

if($data_type == "json"){
    $Encrypted_string = str_replace("\\/", "/",json_encode($arr));//转JSON
}else{
    $toxml = new SdkXML();
    $Encrypted_string = $toxml->toXml($arr);//转XML
}

//Log::LogWirte("请求的明文：".$Encrypted_string); //记录密文
$Encrypted = $baofoosdk->encryptedByPrivateKey($Encrypted_string);	//先BASE64进行编码再RSA加密
//Log::LogWirte("请求密文：".$Encrypted); //记录密文
$ApiPostData["txn_sub_type"]=$txn_sub_type;
$ApiPostData["data_content"]=$Encrypted;
$Result = HttpClient::Post($ApiPostData, $request_url);//发送请求并接收结果
//Log::LogWirte("返回结果：".$Result);
echo $Result;
die();
function get_transid(){//生成时间戳
return strtotime(date('Y-m-d H:i:s',time()));
}
function rand4(){//生成四位随机数
return rand(1000,9999);
}
function return_time(){//生成时间
return date('YmdHis',time());
}