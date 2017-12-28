<?php
class bankCard {
	function zXBank($uid,$image){
		$host = "http://bankcard.aliapi.hanvon.com";
    	$path = "/rt/ws/v1/ocr/bankcard/recg";
    	$querys = "code=d72b071d-f407-42c9-b50c-8122bc4d5fc8";
		$appcode = "5aa907848ce74a86a3e48dbba43e5f87";
		$url = $host . $path . "?" . $querys;
		$method = "POST";
    	$headers = array();
    	array_push($headers, "Authorization:APPCODE " . $appcode);
    	array_push($headers, "Content-Type".":"."application/octet-stream");
    	//根据API的要求，定义相对应的Content-Type
    	array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");

	 	$arr = array('uid'=>$uid,'image'=>$image);
	 	$data = json_encode($arr);
	 	return $this->curl($url,$data,$headers,$method,$host);
	}
	function curl($url,$data,$headers,$method,$host){
		$curl = curl_init();
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl, CURLOPT_FAILONERROR, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    if (1 == strpos("$".$host, "https://"))
	    {
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    $result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
}