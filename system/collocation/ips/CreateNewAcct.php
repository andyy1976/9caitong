<?php
	/**
	 * 
	 * @param unknown_type $pMerBillNo
	 * @return string added by zt
	 */
	function CreateNewAcctXml($IpsAcct,$pWebUrl,$pS2SUrl){		//此方法已废弃
		$strxml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>"
				."<pReq>"
				."<pMerBillNo>" .$IpsAcct['pMerBillNo'] ."</pMerBillNo>"
				."<pIdentType>" .$IpsAcct['pIdentType'] ."</pIdentType>"
				."<pIdentNo>" .$IpsAcct['pIdentNo'] ."</pIdentNo>"
				."<pRealName>" .$IpsAcct['pRealName'] ."</pRealName>"
				."<pMobileNo>" .$IpsAcct['pMobileNo'] ."</pMobileNo>"
				."<pEmail>" .$IpsAcct['pEmail'] ."</pEmail>"
				."<pSmDate>" .$IpsAcct['pSmDate'] ."</pSmDate>"
				."<pWebUrl><![CDATA[" .$pWebUrl ."]]></pWebUrl>"
				."<pS2SUrl><![CDATA[" .$pS2SUrl ."]]></pS2SUrl>"
				."<pMemo1><![CDATA[" .$IpsAcct['pMemo1'] ."]]></pMemo1>"
				."<pMemo2><![CDATA[" .$IpsAcct['pMemo2'] ."]]></pMemo2>"
				."<pMemo3><![CDATA[" .$IpsAcct['pMemo3'] ."]]></pMemo3>"
				."</pReq>";	
		
		
		
		$strxml=preg_replace("/[\s]{2,}/","",$strxml);//去除空格、回车、换行等空白符
		$strxml=str_replace('\\','',$strxml);//去除转义反斜杠\		
		return $strxml;		
	}
	

	/**
	 * 创建新帐户
	 * @param int $user_id
	 * @param int $user_type 用户类型 1 个人 2 企业   以下作废0:普通用户user.id;1:担保用户deal_agency.id
	 * @param unknown_type $MerCode
	 * @param unknown_type $cert_md5
	 * @param unknown_type $post_url
	 * @return string
	 */
	function CreateNewAcct($user_id,$user_type,$MerCode,$cert_md5,$post_url){
	
		
		
		$pWebUrl= SITE_DOMAIN.APP_ROOT."/index.php?ctl=collocation&act=response&class_name=Ips&class_act=CreateNewAcct&from=".$_REQUEST['from'];//web方式返回
		$pS2SUrl= SITE_DOMAIN.APP_ROOT."/index.php?ctl=collocation&act=notify&class_name=Ips&class_act=CreateNewAcct&from=".$_REQUEST['from'];//s2s方式返回		
	
		$user = array();
		$user = get_user_info("*","id = ".$user_id);
		
		$data = array();
		$data['merBillNo'] = $MerCode.sprintf('%021s', $user_id).to_date(get_gmtime(),'Ymd');//$user_id;//'merBillNo商户开户流水号 否 商户系统唯一不重复 针对用户在开户中途中断（开户未完成，但关闭了IPS开 户界面）时，必须重新以相同的商户订单号发起再次开户 ',
		$data['userName'] = $user['email'];//'注册邮箱 否 用于登录账号，IPS系统内唯一不能重复',
		$data['userType'] = $user_type?2:1; //系统设置 0普通用户；1企业用户；做转换  用户类型 1 个人 2 企业 
		//$data['argMerCode'] = $MerCode;// '“平台”账号 否 由IPS颁发的商户号 ',
		$data['mobileNo'] = $user['mobile'];//'手机号 否 用户发送短信 '
		//废弃$data['pIdentType'] = 1;//'证件类型 否 1#身份证，默认：1',
		$data['identNo'] = $user['idno'];//'证件号码 否 真实身份证 ',
		$data['realName'] = $user['real_name'];//'姓名 否 真实姓名（中文） '
		$data['bizType'] = 1;//业务类型1:P2P;2:众筹
		$data['enterName'] = "";// 企业名称  用户类型为企业必填 
		$data['orgCode'] = "";// 营业执照编码 企业名称用户类型为企业必填 
		$data['isAssureCom'] = "";// 是否为担保企业 1:是;0:否
		$data['webUrl'] = $pWebUrl;// 页面返回地址
		$data['s2SUrl'] = $pS2SUrl;// 后台通知地址
		$data['merDate'] = to_date(get_gmtime(),'Y-m-d');//'提交日期 否 时间格式“yyyyMMdd”,商户提交日期,。如：20140323 ',
		$Jsondata = json_encode($data);
		
		//数据库字段按2.0接口定义，现做对应转义，以便数据入库
		$olddata = array();
		$olddata['user_type'] = $user_type;
		$olddata['user_id'] = $user_id;
		$olddata['argMerCode'] = $MerCode;// '“平台”账号 否 由IPS颁发的商户号 ',
		$olddata['pMerBillNo'] = $data['merBillNo'];//$user_id;//'pMerBillNo商户开户流水号 否 商户系统唯一丌重复 针对用户在开户中途中断（开户未完成，但关闭了IPS开 户界面）时，必须重新以相同的商户订单号发起再次开户 ',
		$olddata['pIdentType'] = 1;//'证件类型 否 1#身份证，默认：1',
		$olddata['pIdentNo'] = $user['idno'];//'证件号码 否 真实身份证 ',
		$olddata['pRealName'] = $user['real_name'];//'姓名 否 真实姓名（中文） '
		$olddata['pMobileNo'] = $user['mobile'];;//'手机号 否 用户发送短信 '
		$olddata['pEmail'] = $user['email'];//'注册邮箱 否 用于登录账号，IPS系统内唯一丌能重复',
		$olddata['pSmDate'] = $data['merDate'];//'提交日期 否 时间格式“yyyyMMdd”,商户提交日期,。如：20140323 ',
		//对应转义结束
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."ips_create_new_acct",$olddata,'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$Crypt3Des=new Crypt3Des();//new 3des class
		$p3DesXmlPara=$Crypt3Des->DESEncrypt($Jsondata);//3des 加密
	
		//print_r($data); die;
		$operationType = "user.register";
		$str=$operationType.$MerCode.$p3DesXmlPara.$cert_md5;
		
		//print_r($cert_md5); exit;
		
		$pSign=md5($str);
	//提交参数有变  
		$html = '
		<form name="form1" id="form1" method="post" action="'.$post_url.'" target="_self">
		<input type="hidden" name="operationType" value="'.$operationType.'"  />
		<input type="hidden" name="merchantID" value="'.$MerCode.'" />
		<input type="hidden" name="request" value="'.$p3DesXmlPara.'" />
		<input type="hidden" name="sign" value="'.$pSign.'" />
		</form>
		<script language="javascript">document.form1.submit();</script>';
		//echo $html; exit;
		return $html;
	
	}
	
	//创建新帐户回调
	function CreateNewAcctCallBack($pMerCode,$pErrCode,$pErrMsg,$str3Req){
		//print_r($str3XmlParaInfo);
		$pMerBillNo = $str3Req["merBillNo"];
		$where = " pMerBillNo = '".$pMerBillNo."'";
		$sql = "update ".DB_PREFIX."ips_create_new_acct set is_callback = 1 where is_callback = 0 and ".$where;
		$GLOBALS['db']->query($sql);
		if ($GLOBALS['db']->affected_rows()){		
			//操作成功
			$data = array();
			
			//兼容2.0状态码
			if($str3Req["status"]==0){
				$str3Req["status"] = 9; //失败
			}else if($str3Req["status"]==1){
				$str3Req["status"] = 10; //成功
			}else if($str3Req["status"]==2){
				$str3Req["status"] = 5; //待审核
			}else{
				$str3Req["status"] = 11; //未知错误
			}
			$data['pStatus'] = $str3Req["status"]; //0:失败;1:成功;2:待审核;
			//	废弃	2 开户状态 否	状态：10#开户成功，5#注册超时，9#开户失败。
		//	$data['pBankName'] = $str3Req["pMerCode"];//64 银行名称 是/否
			//$data['pBkAccName']  = $str3Req["pMerCode"];//50 户名 是/否
			//$data['pBkAccNo'] = $str3Req["pBkAccNo"];// 4 银行卡账号 是/否
		//	$data['pCardStatus'] = $str3Req["pCardStatus"];// 1 身份证状态 是/否				
			
			
			$data['pIpsAcctNo'] = $str3Req["ipsAcctNo"];//pIpsAcctNo 30 IPS托管平台账 户号是/否 pErrCode 返回状态为 MG00000F 时返回，由 IPS生成颁发的资金账号。 
			$data['ipsBillNo'] = $str3Req["ipsBillNo"];//IPS 订单号  新增字段 
			
			
			$data['pIpsAcctDate'] = $str3Req["ipsDoTime"];// 8 IPS开户日期 否 pErrCode 返回状态为 000000 时返回，格 式：yyyymmdd
			
			$data['pErrCode'] = $pErrCode;
			$data['pErrMsg'] = $pErrMsg;
			
	
			$GLOBALS['db']->autoExecute(DB_PREFIX."ips_create_new_acct",$data,'UPDATE',$where);
			
			if ($pErrCode == '000000'){
				$user_id = intval($GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."ips_create_new_acct where ".$where));
				
				$GLOBALS['db']->query("update ".DB_PREFIX."user set ips_acct_no = '".$data['pIpsAcctNo']."' where id = ".$user_id);
					
				return 	$user_type;		
			}
		}
	}	
	
?>