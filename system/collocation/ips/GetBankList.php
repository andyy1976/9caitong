<?php

/**
 * 商户端获取银行列表查询(WS) 
 * @param int $MerCode
 * @param unknown_type $cert_md5
 * @param unknown_type $ws_url
 * @return  
 * 		  pMerCode 6 “平台”账号 否 由IPS颁发的商户号 pErrCode 4 返回状态 否 0000成功； 9999失败；
 * 		  pErrMsg 100 返回信息 否 状态0000：成功 除此乊外：反馈实际原因 
 * 		  pBankList 银行名称|银行卡别名|银行卡编号#银行名称|银行卡别名|银行卡编号
 * 		  BankList[] = array('name'=>银行名称,'sub_name'=>银行卡别名,'id'=>银行卡编号);
 */

function GetBankList($MerCode, $cert_md5, $ws_url) {
	$post_data["operationType"] = "query.bankQuery";
	$post_data["merchantID"] = $MerCode;
	$Crypt3Des=new Crypt3Des();//new 3des class
	$post_data["request"]=$Crypt3Des->DESEncrypt(json_encode(array()));//3des 加密
	$str = $operationType.$MerCode.$request.$cert_md5;
	$post_data["sign"] = md5 ( $str );
	$data = http_build_query($post_data);
	$curl = curl_init();    //启动一个curl会话
	curl_setopt($curl, CURLOPT_URL, $ws_url);   //要访问的地址
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	$return = curl_exec($curl);  //执行curl会话
	$return = json_decode($return,true); 
	curl_close($curl);      //关闭curl会话
	$Crypt3Des=new Crypt3Des(); 
	$str3XmlParaInfo=$Crypt3Des->DESDecrypt($return["response"]);//3des解密
	$str3ParaInfo = json_decode($str3XmlParaInfo, true); 
	if($return["resultCode"]=="000000"){
		$result = array ();
		$result ['pErrCode'] = $return["resultCode"];
		$result ['pErrMsg'] = $return["resultMsg"];
		$result ['pBankList'] =  $str3ParaInfo ['banks'];;
		$result ['BankList'] = $str3ParaInfo ['banks'];
		
	}else{
		
		$result = array ();
		$result ['pErrCode'] = 9999;
		$result ['pErrMsg'] = "发生错误，请重试";
		$result ['pBankList'] = '';
	}
	return $result;
}

?>