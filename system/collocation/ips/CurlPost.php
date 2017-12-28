<?php
//IPS3.0数据传输及解密
class CurlPost(){
	function curlpost($operationType, $merchantID, $request=array(), $url, $cert_md5){
		$post_data["operationType"] = "project.regProject";
		$post_data["merchantID"] = $merchantID;
		$Crypt3Des=new Crypt3Des();//new 3des class
		$post_data["request"]=$Crypt3Des->DESEncrypt(json_encode($request));//3des 加密
		$str = $operationType.$merchantID.$request.$cert_md5;
		$post_data["sign"] = md5 ( $str );
		$data = http_build_query($post_data);
		$curl = curl_init();    //启动一个curl会话
		curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
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
		$str3CurlParaInfo=$Crypt3Des->DESDecrypt($return["response"]);//3des解密
		return $str3ParaInfo = json_decode($str3CurlParaInfo, true); 
	}
}	
?>