<?php
require APP_ROOT_PATH."system/utils/Wapllpay/llpay_submit.php";
require APP_ROOT_PATH."system/utils/Depository/Require.php";
class paymentModule extends SiteBaseModule
{
	public function pay()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		
		if($payment_notice)
		{
			if($payment_notice['is_paid'] == 0)
			{
				$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_notice['payment_id']);
				
				if($payment_info){
					require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
					$payment_class = $payment_info['class_name']."_payment";
					$payment_object = new $payment_class();
					$payment_code = $payment_object->get_payment_code($payment_notice['id']);
				}
				$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_NOW']);
				$GLOBALS['tmpl']->assign("payment_code",$payment_code);
				//$GLOBALS['tmpl']->assign("order",$order);
				$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
				if(intval($_REQUEST['check'])==1)
				{
					showErr($GLOBALS['lang']['PAYMENT_NOT_PAID_RENOTICE'],0,'',0,strim($_REQUEST["from"]));
				}
				if(strim($_REQUEST["from"]) == "debit")
					$GLOBALS['tmpl']->display("debit/debit_payment_pay.html");
				else
					$GLOBALS['tmpl']->display("page/payment_pay.html");
			}
			else
			{				
				showSuccess('支付成功',0,APP_ROOT."/",1,strim($_REQUEST["from"]));
			}
		}
		else
		{
			showErr($GLOBALS['lang']['NOTICE_SN_NOT_EXIST'],0,APP_ROOT."/",1,strim($_REQUEST["from"]));
		}
	}
	public function tip()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
		if(strim($_REQUEST["from"]) == "debit")
			$GLOBALS['tmpl']->display("debit/debit_payment_tip.html");
		else
			$GLOBALS['tmpl']->display("page/payment_tip.html");
	}
	
	public function done()
	{
		/*
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		
		$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		if(!$deal_ids)
		$deal_ids = 0;
		$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (".$deal_ids.")");

		$GLOBALS['tmpl']->assign("order_info",$order_info);
		$GLOBALS['tmpl']->assign("order_deals",$order_deals);
		$is_coupon = 0;	
		$send_coupon_sms = 0;
		foreach($order_deals as $k=>$v)
		{
			if($v['is_coupon'] == 1&&$v['buy_status']>0)
			{
				$is_coupon = 1;
				break;
			}
		}
		
		foreach($order_deals as $k=>$v)
		{
			if($v['forbid_sms'] == 0)
			{
				$send_coupon_sms = 1;
				break;
			}
		}
	
		$is_lottery = 0;	
		foreach($order_deals as $k=>$v)
		{
			if($v['is_lottery'] == 1&&$v['buy_status']>0)
			{
				$is_lottery = 1;
				break;
			}
		}
		
		$GLOBALS['tmpl']->assign("is_lottery",$is_lottery);
		$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
		$GLOBALS['tmpl']->assign("send_coupon_sms",$send_coupon_sms);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
		$GLOBALS['tmpl']->display("page/payment_done.html");
		*/
	}
	
	public function incharge_done()
	{
		$order_id = intval($_REQUEST['id']);
		//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		//$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (select distinct deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order_id.")");
		//$GLOBALS['tmpl']->assign("order_info",$order_info);
		//$GLOBALS['tmpl']->assign("order_deals",$order_deals);

		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
		if(strim($_REQUEST["from"]) == "debit")
			$GLOBALS['tmpl']->display("debit/debit_payment_done.html");
		else
			$GLOBALS['tmpl']->display("page/payment_done.html");
	}
	
	public function response()
	{
		//支付跳转返回页
		if($GLOBALS['pay_req']['class_name'])
			$_REQUEST['class_name'] = $GLOBALS['pay_req']['class_name'];

		$class_name = addslashes(trim($_REQUEST['class_name']));
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
		if($payment_info)
		{
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			adddeepslashes($_REQUEST);
			$payment_code = $payment_object->response($_REQUEST);
		}
		else
		{
			showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
		}
	}
	
	public function notify()
	{
		//支付跳转返回页
		if($GLOBALS['pay_req']['class_name'])
			$_REQUEST['class_name'] = $GLOBALS['pay_req']['class_name'];
		
		$class_name = addslashes(trim($_REQUEST['class_name']));
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
		if($payment_info)
		{
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			adddeepslashes($_REQUEST);
			$payment_code = $payment_object->notify($_REQUEST);
		}
		else
		{
			showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
		}
	}

	public function DoDpTrade()
	{
		$now = TIME_UTC;
		$info["user_add_time"] = date("ymdhis", $now);
		$info["user_id"] = intval(strim($_REQUEST['user_id']));
        $info["user_type"] = intval(strim($_REQUEST['incharge_mode'])); //快捷还是网银
        $info["pTrdAmt"] = floatval(strim($_REQUEST['pTrdAmt']));//金钱
        $pTrdBnkCode = strim($_REQUEST['incharge_channel']);//选择渠道
        $datas = get_user_info("*", "id = " . $info["user_id"]);
        
        /*
        if($pTrdBnkCode == 29){
        	$this->llpay($info,$datas);
        elseif
        	$this->baofoo($info,$datas);
       	*/
       	if($pTrdBnkCode==39 || $pTrdBnkCode==36){
        	$this->Yibin($pTrdBnkCode,$info);
        }      	
    }

    /*
	*  存管
	*/
	public function Yibin($pTrdBnkCode,$info){
			$user_id = $info["user_id"];
	   		$Publics = new Publics();   		
	   		$trader_id=$Publics->seqno();
	   		$orderId = $this->orderId();
	   		//$bankcard = $GLOBALS['db']->getRow("SELECT bankcard,bank_mobile,bank_id,baofootag FROM ".DB_PREFIX."user_bank as u LEFT JOIN ".DB_PREFIX."bank as b on bank_id = b.id WHERE status=1 and user_id=" . $info["user_id"]);	   		
	   		$bankcard = $GLOBALS['db']->getRow("SELECT bankcard FROM ".DB_PREFIX."user_bank where user_id=$user_id and cunguan_tag=1 and status=1");
	   		switch ($pTrdBnkCode){
	   			case '39':
   					$custody="WAP-APP银行充值";
   					$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$info,$bankcard,$trader_id);
   					//$html=$Publics->verify_trans_password('charge',"charge_pay",$info['user_id'],'4',$trader_id);		

   					$html=$this->verify_trans_password('charge',"charge_pay",$info["user_id"],'4',$trader_id);
   					echo $html;
   					exit;						
	   			break;

	   			// case '36':
   				// 	$custody="WAP-APP汇元认证";
   				// 	$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$info,$bankcard,$trader_id);
   				// 	$html=$this->verify_trans_password('charge',"chongzhi_kj_one_ceshi",$info['user_id'],'4',$trader_id);
   				// 	echo $html;
   				// 	exit;				
       //   		break;
	   		}
	}

	/*
	*  存管
	*/
	public function rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id){
		$trans_data=array(
          	'is_paid'=>0,
            'create_time'=>time(),
            'money'=>$record['pTrdAmt'],
            'order_id'=>$orderId,
            'seqno'=>$trader_id,
            'user_id'=>$record['user_id'],
            'outer_notice_sn'=>$custody,
            'payment_id'=>$pTrdBnkCode,
            'create_date'=>date("Y-m-d"),
            'cunguan_tag'=>1,
            'bank_id' =>$bankcard['bankcard'],
        );
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$trans_data,'INSERT','','SILENT');
	}


	/*
	*  存管
	*/
	public function verify_trans_password($module,$action,$userId,$type,$SeqNo){
		$is_mobile = isMobile();
		switch($type){		
			case 4:
				if($is_mobile){
					$url = 'https://36.110.98.254:19001/p2ph5/standard/checkPassword.html';
				 }else{  
				 	$url = 'https://36.110.98.254:19001/p2ph5/pc/checkPassword.html';
				 }
				//$url = 'https://test-p2.heepay.com/passWord/PASSWORDVERIFY';
				$data['type'] = 'J04';
				break;			
		}
		$data['user_id'] = intval($userId);
        $data['seqno'] = $SeqNo;
        $data['add_time'] = TIME_UTC;
        $data['date_time'] = date("Y-m-d H:i:s");
        //$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
        $backurl = urlencode("https://" . $_SERVER['HTTP_HOST'] . "/".$module."/".$action); 
        list($msec, $sec) = explode(' ', microtime());
        $signtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $signature = $signtime."|".$data['user_id'];
        $Publics = new Publics();
        $data_content = $Publics->rsa_encrypt($signature);	//RSA加密
		$urls = "?systemCode=JCT&userId=".$data['user_id']."&backURL=".$backurl."&signTime=".$signtime."&signature=".$data_content."&businessSeqNo=".$SeqNo;	     
  //       $curl = curl_init();    //启动一个curl会话
  //       curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
  //       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
  //       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
  //       //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
  //      // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
  //      // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
  //      // curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
  //       //curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
  //       curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
  //       curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
  //       curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
  //       $output = curl_exec($curl);  //执行curl会话 
  //       curl_close($curl);
		// $arr = explode("Location:", $output);
		// $real_arr = explode("\n", $arr[1]);
		// $con = trim($real_arr[0]);
  //       $yb_con_arr = explode("?", $con);
		// $yb_con = str_replace($yb_con_arr[0], "", $con);
		// $y_url = $yb_con_arr[0];
		$url = $url.$urls; 
		$html = "<form action='".$url."' method='get' enctype='text/plain' id='form1'>";
		$cd_con = str_replace("?", "", $urls);
		$arr_con = explode("&", $cd_con);
		foreach($arr_con as $k=>$v){
			$abc = explode("=", $v);
			if($abc[0] == "signature"){
				$html .="<input type='hidden' name='".$abc[0]."' value='".$data_content."'>";
			}elseif($abc[0] == "backURL"){
      			$html .="<input type='hidden' name='".$abc[0]."' value='"."https://" . $_SERVER['HTTP_HOST'] . "/".$module."/".$action."'>";
				//$html .="<input type='hidden' name='".$abc[0]."' value='"."https://" . $_SERVER['HTTP_HOST'] . "/dep/pwd_call_back'>";
			}else{
				$html .="<input type='hidden' name='".$abc[0]."' value='".$abc[1]."'>";
			}
		}
		$html .= "         
			 </form>
			<script>
				document.getElementById('form1').submit();
			</script>";
		$data['form_con'] = $html;
		$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
		return $html;
		exit;
    }
    /*
    //RSA加密解密----开始
    //私钥加密
    public function rsa_encrypt($data){
        $private_key = file_get_contents("system/utils/Depository/jct/privatekey.pem");
        openssl_sign($data,$encrypted,$private_key, OPENSSL_ALGO_SHA256);
        //$pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        //openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }*/

    /**
	 * 宝付支付 根据充值信息建立连连表单请求
	 * @author dy
	 * @param array $info  充值信息
	 * @return null
	 */
    private function baofoo($info,$datas){
    	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
    	require APP_ROOT_PATH."system/utils/BAOFOOSDK/ini.php";
    	/*SELECT bankcard,bank_mobile,bank_id,baofootag FROM  jctp2p_user_bank as u LEFT JOIN jctp2p_bank as b on bank_id = b.id WHERE user_id=151*/
    	$bank = $GLOBALS['db']->getRow("SELECT bankcard,bank_mobile,bank_id,baofootag FROM ".DB_PREFIX."user_bank as u LEFT JOIN ".DB_PREFIX."bank as b on bank_id = b.id WHERE status=1 and user_id=" . $info["user_id"]);
		$pay_code = $bank['baofootag'];	//银行编码
		$acc_no = $bank['bankcard'];//银行卡卡号
		$id_card = $datas['idno'];//身份证号码
		$id_holder = $datas['real_name'];//姓名
		$mobile = $bank['bank_mobile'];//银行预留手机号
		$txn_amt = $info["pTrdAmt"];//交易金额额
		$txn_amt *=100;//金额以分为单位（把元转换成分）
			//====================系统动态生成值=======================================
		$trans_id = $this->orderId();;	//商户订单号
		$trade_date = date('YmdHis',time());	//订单日期
			//=================接口固定参数==========================================
		$version = "4.0.0.0";//接口版本
		$txn_sub_type = "01"; //交易子类
		$biz_type = "0000"; //接入类型
		$txn_type = "03311" ;//交易类型
		$id_card_type = "01"; //身份证类型
		$page_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=payment&act=return_url_baofoo";////页面通知地址（支付成功跳转的页面）
		$back_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=payment&act=return_url_baofoo";//支付出现异常时返回的商户页面
		$return_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=payment&act=baofoo_notify_url";//服务器通知地址。（支付成功后宝付异步通知商户服务器的地址）
		$language = "1"; //固定值1（中文）
		$input_charset = "1" ;//字符集(1 代表UTF-8，2 代表GBK，3 代表GB2312）
		$data_content_parms = array('txn_sub_type' =>$txn_sub_type,
			'biz_type'=>$biz_type,
			'terminal_id'=>$terminal_id,
			'member_id'=>$member_id,
			'pay_code'=>$pay_code,
			'acc_no'=>$acc_no,
			'id_card_type'=>$id_card_type,
			'id_card'=>$id_card,
			'id_holder'=>$id_holder,
			'mobile'=>$mobile,
			'valid_date'=>'',//暂不支持信用卡（传空）
			'valid_no'=>'',//暂不支持信用卡（传空）
			'trans_id'=>$trans_id,
			'txn_amt'=>$txn_amt,
			'trade_date'=>$trade_date,
			'commodity_name'=>'玖财通wap充值',
			'commodity_amount'=>'1',//商品数量（默认为1）
			'user_name'=>$id_holder,
			'page_url'=>$page_url,
			'return_url'=>$return_url,
			'additional_info'=>$datas['real_name'],
			'req_reserved'=>$GLOBALS['user_info']['money']);
		$this->payment_notice($trans_id,$info["user_id"],24,$info["pTrdAmt"],$acc_no);
		$Encrypted_string = str_replace("\\/", "/",json_encode($data_content_parms));//转JSON
		$baofoosdk = new BaofooSdk($pfxfilename,$cerfilename,$private_key_password,FALSE); //实例化加密类。
		$data_content = $baofoosdk->encryptedByPrivateKey($Encrypted_string);	//RSA加密
			//Log::LogWirte("请求密文：".$Encrypted_string);

		$FromString =  '<body onload="document.pay.submit()" >
		<form id="pay" name="pay" action="https://gw.baofoo.com/apipay/wap" method="post">
			<input name="version" type="hidden" id="version" value="'.$version.'" />
			<input name="input_charset" type="hidden" id="input_charset" value="'.$input_charset.'" />
			<input name="language" type="hidden" id="language" value="'.$language.'" />
			<input name="terminal_id" type="hidden" id="terminal_id" value="'.$terminal_id.'" />
			<input name="txn_type" type="hidden" id="txn_type" value="'.$txn_type.'" />
			<input name="txn_sub_type" type="hidden" id="txn_sub_type" value="'.$txn_sub_type.'" />
			<input name="member_id" type="hidden" id="member_id" value="'.$member_id.'" />
			<input name="data_type" type="hidden" id="data_type" value="'.$data_type.'" />
			<textarea name="data_content" style="display:none;" id="data_content">'.$data_content.'</textarea>
			<input name="back_url" type="hidden" id="back_url" value="'.$back_url.'" />
		</form></body>';
			//Log::LogWirte("表单跳转参数：".$FromString);
        error_log(PHP_EOL."FromString=".var_export($FromString,1),3,"D:/aaa.log");
        echo $FromString;
		exit;
	}
	/*
宝付通知页面 此处与PC端一致，不走现在WAP机制。直接返回出借列表

*/
	public function return_url_baofoo(){
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$path = "system/utils";
		require_once $path.'/BAOFOOSDK/ini.php';
		$endata_content = $_REQUEST["data_content"];
		$baofoosdk = new BaofooSdk($pfxfilename,$cerfilename,$private_key_password); //实例化加密类。  
		$endata_content = $baofoosdk->decryptByPublicKey($endata_content);	//RSA解密
		$endata_content = json_decode($endata_content,TRUE);
		$FactMoney = $endata_content['succ_amt']; //交易成功后返回的金额
		if($endata_content['resp_code'] == "0000"){
			$GLOBALS['tmpl']->assign("payment_notice",$FactMoney);
			$GLOBALS['tmpl']->display("page/payment_pay.html");
		}else{
			$GLOBALS['tmpl']->assign("payment_notice",$FactMoney);
			$GLOBALS['tmpl']->display("page/payment_fail.html");
		}
	}

   /*
     * 宝付支付异步页面  此处代码与PC端一致
     * */
    public function baofoo_notify_url()
    {
		$path = "system/utils";
		require_once $path.'/BAOFOOSDK/ini.php';
		$endata_content = $_REQUEST["data_content"];
		$baofoosdk = new BaofooSdk($pfxfilename,$cerfilename,$private_key_password); //实例化加密类。  
		$endata_content = $baofoosdk->decryptByPublicKey($endata_content);	//RSA解密
        if (!empty($endata_content)) {
			if($data_type =="xml"){
				$endata_content = \SdkXML::XTA($endata_content);
			}else{
				$endata_content = json_decode($endata_content,TRUE);
			}		
			$signStr = implode('&', $endata_content); //存储所有接收到数据
			$FactMoney = $endata_content['succ_amt']; //交易成功后返回的金额
			$TransID = $endata_content['trans_id']; //交易成功后返回的金额
			$trans_no = $endata_content['trans_no']; //交易成功后返回的金额
			$acct_name = $endata_content['additional_info'];
			$no_order = $TransID;
			$user_info = $this->user_info($acct_name);
			$recharge_log = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$no_order."'");
			if($recharge_log['money'] == $FactMoney){
				if($recharge_log['is_paid'] == 0 && $endata_content['resp_code'] == "0000"){
					$pay_notify_data = array(
                        'version' => 1,
                        'order_no' => $TransID,
                        'return_mode' => 1,
                        'status'=>1,
                        'pay_type' => 24,
                        'trans_no' => $trans_no, 
                        'addtime' => time(),
                        'signstr' => $signStr,
                    );
                    $GLOBALS['db']->autoExecute(DB_PREFIX."pay_notify",$pay_notify_data,'INSERT','','SILENT');
					$pay_id = intval($GLOBALS['db']->insert_id());
					if($pay_id){
						$trans_data['is_paid'] = 1;
						$trans_data['pay_time'] = time();
						$trans_data['pay_date'] = date("Y-m-d H:i:s");
						$trans_data['notice_sn'] = $trans_no;
						$trans_data['memo'] = $signStr;
						$trans_data['outer_notice_sn'] = "宝付WAP-APP支付";
						$order_id = $GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$trans_data,"UPDATE","id=".$recharge_log['id']);
						require_once APP_ROOT_PATH.'system/libs/user.php';
						modify_account(array('money'=>$FactMoney,'recharge_money'=>$FactMoney),$recharge_log['user_id'],"宝付wap端充值",1);
                        /************充值成功后微信模板消息开始*********************/
                        $wx_openid = $GLOBALS['db']->getOne("select wx_openid from ".DB_PREFIX."user where id=".$recharge_log['user_id']);
                        if($wx_openid){
                            if(app_conf('WEIXIN_TMPL')){
                                $tmpl_url = app_conf('WEIXIN_TMPL_URL');
                                $tmpl_datas = array();
                                $tmpl_datas['first'] = '尊敬的用户，您完成了一笔充值';
                                $tmpl_datas['keyword1'] = $FactMoney.'元';
                                $tmpl_datas['keyword2'] = date('Y-m-d H:i:s');
                                $tmpl_datas['keyword3'] = $endata_content['req_reserved'] + $FactMoney.'元';
                                $tmpl_datas['remark'] = "请登录玖财通查看详情~\r\n\r\n下载客户端，理财更简单。推荐您下载玖财通APP！";
                                $tmpl_data = create_request_data('3',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
                                $resl = request_curl($tmpl_url,$tmpl_data);
                                $tmpl_msg['dest'] = $wx_openid;
                                $tmpl_msg['send_type'] = 3;
                                $tmpl_msg['content'] = serialize($tmpl_datas);
                                $tmpl_msg['send_time'] = time();
                                $tmpl_msg['create_time'] = time();
                                $tmpl_msg['user_id'] = $recharge_log['user_id'];
                                $tmpl_msg['title'] = '充值成功';
                                if($resl===true){
                                    $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$recharge_log['user_id']);
                                    $tmpl_msg['is_send'] = 1;
                                    $tmpl_msg['result'] = '发送成功';
                                    $tmpl_msg['is_success'] = 1;
                                }else{
                                    $tmpl_msg['is_send'] = 0;
                                    $tmpl_msg['result'] = $resl['message'];
                                    $tmpl_msg['is_success'] = 0;
                                }
                                $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');
                            }
                        }

                        /************充值成功后微信模板消息结束*********************/

						echo "OK";//接收到通知并处理本地数据后返回OK
					}
				}
			}
			
        } else {
            die("{'ret_code':'9999','ret_msg':'验签失败'}");

        }
		

    }

    public function llpay($info,$datas){
    	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
    	$bankcard = $GLOBALS['db']->getOne("SELECT bankcard FROM  " . DB_PREFIX . "user_bank WHERE status=1 and user_id=" . $info["user_id"]);
    	$llpay_config = $this->llpay_return_config();
    	$user_id = $info["user_id"];
        //支付类型
    	$busi_partner = '101001';
        //商户订单号
    	$no_order = $this->orderId();
        //商户网站订单系统中唯一订单号，必填
        //付款金额
    	$money_order = $info["pTrdAmt"];
        //必填
        //商品名称
        //$name_goods = '玖信贷充值';
    	$name_goods = '玖财通wap充值';
        //订单地址
    	$url_order = '';
        //订单描述
    	$info_order ='玖财通客户'.$sessionInfo['uid'].'wap端充值'.$info['money'].'元';
        //银行网银编码
    	$bank_code = '';
        //支付方式
    	$pay_type = '';
        //卡号
    	$card_no = $bankcard;
        //姓名
    	$acct_name = $datas['real_name'];
        //身份证号
    	$id_no = $datas['idno'];
        //协议号
    	$no_agree = '';
        //修改标记
    	$flag_modify = '0';
        //风险控制参数
    	$risk_item = addslashes(json_encode(array('frms_ware_category' => '1002', 'user_info_mercht_userno' => $this->user_id, 'user_info_dt_register' => $user_add_time)));
        //分账信息数据
    	$shareing_data = '';
        //返回修改信息地址
    	$back_url = '';
        //订单有效期
    	$valid_order = '30';
        //服务器异步通知页面路径
    	$notify_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=payment&act=llpay_notify_url";
		//需http://格式的完整路径，不能加?id=123这类自定义参数
		$back_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=payment&act=llpay_return_url";//支付出现异常时返回的商户页面
        //构造要请求的参数数组，无需改动
		//页面跳转同步通知页面路径
    	$return_url = $http_type. $_SERVER['HTTP_HOST'] . "/member.php?ctl=payment&act=llpay_return_url";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost
    	$parameter = array(
    		"version" => trim($llpay_config['version']),
    		"oid_partner" => trim($llpay_config['oid_partner']),
    		"sign_type" => trim($llpay_config['sign_type']),
    		"userreq_ip" => trim($llpay_config['userreq_ip']),
    		"id_type" => trim($llpay_config['id_type']),
    		"valid_order" => trim($llpay_config['valid_order']),
    		"app_request" => trim($llpay_config['app_request']),
    		"user_id" => $user_id,
    		"timestamp" => $this->local_date('YmdHis', $now),
    		"busi_partner" => $busi_partner,
    		"no_order" => $no_order,
    		"dt_order" => $this->local_date('YmdHis', $now),
    		"name_goods" => $name_goods,
    		"info_order" => $info_order,
    		"money_order" => $money_order,
    		"notify_url" => $notify_url,
            "url_return" => $return_url,
    		"url_order" => $url_order,
    		"bank_code" => $bank_code,
    		"pay_type" => $pay_type,
    		"no_agree" => $no_agree,
    		"shareing_data" => $shareing_data,
    		"risk_item" => $risk_item,
    		"id_no" => $id_no,
    		"acct_name" => $acct_name,
    		"flag_modify" => $flag_modify,
    		"card_no" => $card_no,
    		"back_url" => $back_url
    		);
    	$this->payment_notice($no_order,$user_id,29,$money_order,$card_no);
    	$llpayNotify = new LLpaySubmit($llpay_config);
    	$html_text = $llpayNotify->buildRequestForm($parameter, "post","确认");
    	echo ($html_text);
    }
    public function llpay_return_config(){
    	$wapllpay_config =array(
    		'oid_partner'=>'201411031000083504',
    		'RSA_PRIVATE_KEY'=>'-----BEGIN RSA PRIVATE KEY-----
    		MIICXAIBAAKBgQCmRl6Zn4MmtoBoelHRT6j6ounts/x1+GiJTB9/eBTl01cBK50h
    		mOUtGBcOVrJCa0C1NkR8BYgOT/WLfFT8cICw6XSJtf2uzZco71jbwXfFe8MiEx/L
    		XiQNQHuclpkUa1hXFUUo6Qat8X8L++pVZfjav40dPKf7oFWCYLWBCDOdyQIDAQAB
    		AoGANe0mqz4/o+OWu8vIE1F5pWgG5G/2VjBtfvHwWUARzwP++MMzX/0dfsWMXLsj
    		b0UnpF3oUizdFn86TLXTPlgidDg6h0RbGwMZou/OIcwWRzgMaCVePT/D1cuhyD7Y
    		V8YkjVHGnErfxyia1COswAqcpiS4lcTG/RqkAMsdwSZe640CQQDRvkQ7M2WJdydc
    		9QLQ9FoIMnKx9mDge7+aN6ijs9gEOgh1gKUjenLr6hcGlLRyvYDKQ4b1kes22FUT
    		/n+AMaEPAkEAyvH05KRzax3NNdRPI45N1KuT1kydIwL3KpOK6mWuHlffed2EiWLS
    		dhZNiZy9wWuwFPqkrZ8g+jL0iKcCD0mjpwJBAKbWxWmeCZ+eY3ZjAtl59X/duTRs
    		ekU2yoN+0KtfLG64RvBI45NkHLQiIiy+7wbyTNcXfewrJUIcNRjRcVRkpesCQEM8
    		BbX6BYLnTKUYwV82NfLPJRtKJoUC5n/kgZFGPnkvA4qMKOybIL6ehPGiS/tYge1x
    		XD1pCrPZTco4CiambuECQDNtlC31iqzSKmgSWmA5kErqVJB0f1i+a0CbQLlaPGYN
    		/qwa7TE13yByaUdDDaTIEUrDyuqWd5+IvlbwuVsSlMw=
    		-----END RSA PRIVATE KEY-----',
    		'key'=>'jiuxindai521',
    		'version'=>'1.2',
    		'app_request'=>'3',
    		'sign_type'=>strtoupper('MD5'),
    		'valid_order'=>'30',
    		'input_charset'=>strtolower('utf-8'),
    		'transport'=>'http',
    		);

    	return $wapllpay_config;
    }

    public  function local_date($format, $time = NULL)
    {
    	$now = TIME_UTC;
    	if ($time === NULL) {

    		$time = $now;
    	} elseif ($time <= 0) {
    		return '';
    	}
    	return date($format, $time);
    }
    public function orderId(){
    	$yCode = array('Q', 'W', 'E', 'R', 'T', 'Y', 'N', 'M', 'C', 'O');
    	$orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    	return $orderSn;
    }

    /**
	 * 连连支付回调请求地址  写入pay_notify表 trans_ststus_log记录表
	 * @author dy
	 * @param null
	 * @return null
	 */
    public  function llpay_notify_url(){
    	require APP_ROOT_PATH."system/utils/Wapllpay/llpay_notify.php";
    	$llpay_config = $this->llpay_return_config();
		//计算得出通知验证结果
    	$llpayNotify = new LLpayNotify($llpay_config);
    	$llpayNotify->verifyNotify();		
		if ($llpayNotify->result) { //验证成功
			//获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
			$no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
			$oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
			$result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
			$money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
			$acct_name = $llpayNotify->notifyResp['acct_name'];//真实姓名
			$id_no = $llpayNotify->notifyResp['id_no'];//身份证号码
			$signstr=$result_pay.'&'.$no_order.'&'.$oid_paybill.'&'.$money_order.'&'.$acct_name.'&'.$id_no;
			$user_info = $this->user_info($acct_name);
			if($result_pay == "SUCCESS"){
				$recharge_log = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$no_order."'");
				if($recharge_log['money'] == $money_order){
					if($recharge_log['is_paid'] == 0){
						$pay_notify_data = array(
							'version'=>1,
							'order_no'=>$no_order,
							'return_mode'=>1,
							'status'=>1,
							'pay_type'=>29,
							'trans_no'=>$oid_paybill,
							'addtime'=>time(),
							'signstr'=>$signstr,
						);
						$GLOBALS['db']->autoExecute(DB_PREFIX."pay_notify",$pay_notify_data,'INSERT','','SILENT');
						$pay_id = intval($GLOBALS['db']->insert_id());
						if($pay_id){
							$trans_data['is_paid'] = 1;
							$trans_data['pay_time'] = time();
							$trans_data['pay_date'] = date("Y-m-d H:i:s");
							$trans_data['notice_sn'] = $oid_paybill;
							$trans_data['memo'] = $signstr;
							$trans_data['outer_notice_sn'] = "连连WAP-APP支付";
							$order_id = $GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$trans_data,"UPDATE","id=".$recharge_log['id']);
							require_once APP_ROOT_PATH.'system/libs/user.php';
							modify_account(array('money'=>$money_order,'recharge_money'=>$money_order),$recharge_log['user_id'],"连连wap端充值",1);
                            /************充值成功后微信模板消息开始*********************/
                            $wx_openid = $GLOBALS['db']->getOne("select wx_openid from ".DB_PREFIX."user where id=".$recharge_log['user_id']);
                            $user_money = $GLOBALS['db']->getOne("select AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where id =".$recharge_log['user_id']);
                            if($wx_openid){
                                if(app_conf('WEIXIN_TMPL')){
                                    $tmpl_url = app_conf('WEIXIN_TMPL_URL');
                                    $tmpl_datas = array();
                                    $tmpl_datas['first'] = '尊敬的用户，您完成了一笔充值';
                                    $tmpl_datas['keyword1'] = $money_order.'元';
                                    $tmpl_datas['keyword2'] = date('Y-m-d H:i:s');
                                    $tmpl_datas['keyword3'] = $user_money+$money_order.'元';
                                    $tmpl_datas['remark'] = "请登录玖财通查看详情~\r\n\r\n下载客户端，理财更简单。推荐您下载玖财通APP！";
                                    $tmpl_data = create_request_data('3',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
                                    $resl=request_curl($tmpl_url,$tmpl_data);
                                    $tmpl_msg['dest'] = $wx_openid;
                                    $tmpl_msg['send_type'] = 3;
                                    $tmpl_msg['content'] = serialize($tmpl_datas);
                                    $tmpl_msg['send_time'] = time();
                                    $tmpl_msg['create_time'] = time();
                                    $tmpl_msg['user_id'] = $recharge_log['user_id'];
                                    $tmpl_msg['title'] = '充值成功';
                                    if($resl===true){
                                        $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$recharge_log['user_id']);
                                        $tmpl_msg['is_send'] = 1;
                                        $tmpl_msg['result'] = '发送成功';
                                        $tmpl_msg['is_success'] = 1;
                                    }else{
                                        $tmpl_msg['is_send'] = 0;
                                        $tmpl_msg['result'] = $resl['message'];
                                        $tmpl_msg['is_success'] = 0;
                                    }
                                    $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');
                                }
                            }

                            /************充值成功后微信模板消息结束*********************/

							echo "OK";//接收到通知并处理本地数据后返回OK
							
						}
					}
				}
			}else{
				die("{'ret_code':'9999','ret_msg':'验签失败'}");				
			}
		}else{
			echo "{'ret_code':'9999','ret_msg':'验签失败'}";
		}

	}
	public function user_info($real_name){
		$user_info = $GLOBALS['db']->getRow("SELECT user_id,bankcard  FROM ".DB_PREFIX."user_bank WHERE real_name = '$real_name'");
		return$user_info;
	}

	/**
	 * 连连支付同步页面
	 * @author dy
	 * @param null
	 * @return null
	 */

	
	public function llpay_return_url(){
		header('content-type:text/html;charset=utf-8');
		//移动端交互处理
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$res_data =json_decode($_POST['res_data'],true);
		if($res_data['result_pay']=='SUCCESS'){
			$payment_notice = $res_data['money_order'];
			$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
			$GLOBALS['tmpl']->display("page/payment_pay.html");
		}else{
			$payment_notice = $res_data['money_order'];
			$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
			$GLOBALS['tmpl']->display("page/payment_fail.html");
		}
	}

	public function payment_notice($orderId,$user_id,$payment_id,$money,$bank_id){
		$trans_data=array(
			'is_paid'=>0,
			'pay_time'=>0,
			'create_time'=>time(),			
			'order_id'=>$orderId,
			'user_id'=>$user_id,
			'payment_id'=>$payment_id,
			'create_date'=>date("Y-m-d"),
			'money'=>$money,
			'bank_id' =>$bank_id,
		);
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$trans_data,'INSERT','','SILENT');
	}


	/**
	 * 存管代扣同步页面
	 * @author dy
	 * @param null
	 * @return null
	 */
	public function cunguan_return_url(){
		header('content-type:text/html;charset=utf-8');
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$panotice=$_GET['odl'];
		$res_d=$_GET['res_d'];
		$payment_notice=strtoupper($panotice);		
		//$res_data=strtoupper($res_d);
		$Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid,outer_notice_sn FROM ".DB_PREFIX."payment_notice where seqno='".$payment_notice."' and cunguan_tag=1");	
		$userbanks=$GLOBALS['db']->getRow("SELECT back_con,callback_con FROM ".DB_PREFIX."decository WHERE seqno='".$payment_notice."' order by id desc limit 0,1");		
		$userbank=json_decode($userbanks['back_con'],true);
		if($userbank['respHeader']['respCode']=="P2PS000"){
			$GLOBALS['tmpl']->assign("payment_notice",$Query['money']);
			$GLOBALS['tmpl']->assign("callback_con",$userbanks['callback_con']);
			$GLOBALS['tmpl']->display("page/payment_pay.html");
		}else{
			$GLOBALS['tmpl']->assign("payment_notice",$Query['money']);
			$GLOBALS['tmpl']->assign("callback_con",$userbanks['callback_con']);
			$GLOBALS['tmpl']->display("page/payment_fail.html");				
		}
	}




	/**
	 * 汇元认证支付 
	 * @author dy  R08
	 * @param null
	 * @return null
	 */
	public function certificate_return_payment(){
		header('content-type:text/html;charset=utf-8');
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$panotice=$_GET['odl'];
		$payment_notice=strtoupper($panotice);
		$Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid,outer_notice_sn FROM ".DB_PREFIX."payment_notice where seqno='".$payment_notice."' and cunguan_tag=1");	
		$userbanks=$GLOBALS['db']->getRow("SELECT back_con,callback_con,form_con,information FROM ".DB_PREFIX."decository WHERE seqno='".$payment_notice."' and type ='R08' order by id desc limit 1");			
		$userbank=json_decode($userbanks['callback_con'],true);
		$userbank_form=json_decode($userbanks['form_con'],true);
		$mobile_ownerMobile=$userbank_form['inBody']['ownerMobile'];
		if($userbank['respHeader']['respCode']=="P2P0000"){
			$GLOBALS['tmpl']->assign("payment_notice",$Query['money']);
			$GLOBALS['tmpl']->assign("mobile_ownerMobile",$mobile_ownerMobile);
			$GLOBALS['tmpl']->assign("payet_nie",$payment_notice);
			$GLOBALS['tmpl']->display("page/certified_payment.html");
		}else{
			$GLOBALS['tmpl']->assign("payment_notice",$Query['money']);
			$GLOBALS['tmpl']->assign("callback_con",$userbanks['information']);
			$GLOBALS['tmpl']->display("page/payment_fail.html");				
		}
	}


	/**
	 * 汇元认证支付 
	 * @author dy  R09
	 * @param null
	 * @return null
	 */
	public function certificate_payment(){
		header('content-type:text/html;charset=utf-8');
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$panotice=$_GET['odl'];
		$payment_notice=strtoupper($panotice);
	
		$Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid,outer_notice_sn FROM ".DB_PREFIX."payment_notice where seqno='".$payment_notice."' and cunguan_tag=1");	
		$userbanks=$GLOBALS['db']->getRow("SELECT back_con,information FROM ".DB_PREFIX."decository WHERE seqno='".$payment_notice."' and type ='R09' order by id desc limit 1");					
		$userbank_form=json_decode($userbanks['back_con'],true);
		if($userbank_form['respHeader']['respCode']=="P2P0000"){
			$GLOBALS['tmpl']->assign("payment_notice",$Query['money']);
			$GLOBALS['tmpl']->assign("callback_con",$userbanks['information']);
			$GLOBALS['tmpl']->display("page/payment_pay.html");
		}else{
			$GLOBALS['tmpl']->assign("payment_notice",$Query['money']);
			$GLOBALS['tmpl']->assign("callback_con",$userbanks['information']);
			$GLOBALS['tmpl']->display("page/payment_fail.html");			
		}
	}


}
?>