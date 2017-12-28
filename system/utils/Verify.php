<?php
 require APP_ROOT_PATH."system/utils/BinkCard/HttpClient.class.php";
 require APP_ROOT_PATH."system/utils/BinkCard/crypt3des.php";
 class VerifyFun {
	var $url;
	function VerifyFun($url){
		$this->url = $url;
	}

	function RealnameVerify($name,$idnum,$cpid,$cpserialnum,$md5key,$despwd){
		$des = new Crypt3Des($despwd);

		//数据加密	 
		$desname=$des->encrypt($name);
		$desidnum=$des->encrypt($idnum);
		$md5str=md5($desname.$desidnum.$cpserialnum.$md5key);

		//JSON串
		$arr = Array('name'=>$desname,'idnum'=>$desidnum,'cpid'=>$cpid,'verifycode'=>'Vrealname','md5num'=>$md5str,'cpserialnum'=>$cpserialnum);
		$jsonstr=json_encode($arr);

		//Post的参数
		$params = array('info'=>$jsonstr);
		$Contents = HttpClient::quickPost($this->url, $params);
		return $Contents;
	}

	function RealpersonVerify($name,$idnum,$cpid,$cpserialnum,$md5key,$despwd,$photo){
		$des = new Crypt3Des($despwd);
		//数据加密	 
		$desname=$des->encrypt($name);
		$desidnum=$des->encrypt($idnum);
		$md5str=md5($desname.$desidnum.$cpserialnum.$md5key);

		//JSON串
		$arr = Array('name'=>$desname,'idnum'=>$desidnum,'cpid'=>$cpid,'verifycode'=>'Vrealperson','md5num'=>$md5str,'cpserialnum'=>$cpserialnum,'cameraphoto'=>$photo);
		$jsonstr=json_encode($arr);

		//Post的参数
		$params = array('info'=>$jsonstr);
		$Contents = HttpClient::quickPost($this->url, $params);
		return $Contents;
	}

	function RealcertVerify($idcardphotof, $name, $idnum, $cpid, $cpserialnum, $despwd, $md5key){
		$des = new Crypt3Des($despwd);
 		$desname=$des->encrypt($name);
	 	$desidnum=$des->encrypt($idnum);
	  	//数据MD5签名
	 	$md5str=md5($cpserialnum.$md5key);
	  	//JSON串
	 	$arr = Array('idcardphotof'=>$idcardphotof,'cpid'=>$cpid,'verifycode'=>'Vcertification','md5num'=>$md5str,'cpserialnum'=>$cpserialnum,'name'=>$desname,'idnum'=>$desidnum,);
	  	$jsonstr=json_encode($arr);

	  	//Post的参数
      	$params = array('info'=>$jsonstr);
	  	$Contents = HttpClient::quickPost($this->url, $params);
	  	return $Contents;
	}

	function zXBank($sid, $name, $idnum, $vtype, $phone, $bankCard, $cpserialnum, $despwd, $md5key){
		$des = new Crypt3Des($despwd);
 		$desname=$des->encrypt($name);
	 	$desidnum=$des->encrypt($idnum);
	 	$desphone=$des->encrypt($phone);
	 	$desBankCard=$des->encrypt($bankCard);
	  	//数据MD5签名
	 	$md5str=md5($desBankCard.$desidnum.$cpserialnum.$md5key);
	  	//JSON串
	 	$arr = Array('sid'=>$sid,'name'=>$desname, 'phone'=>$desphone,'md5num'=>$md5str,'cpserialnum'=>$cpserialnum,'bankCard'=>$desBankCard,'vtype'=>$vtype, 'idnum'=>$desidnum,);
	  	$jsonstr=json_encode($arr);	
		return  HttpClient::http_post_json($this->url, $jsonstr);
	}
}
?>