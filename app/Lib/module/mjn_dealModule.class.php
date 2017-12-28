<?php
require APP_ROOT_PATH."system/utils/Depository/Require.php";
define(ACTION_NAME,"mjn_deal");
define(MODULE_NAMEN,"index");
class mjn_dealModule extends SiteBaseModule
{
    private $TotalAssetsUrl = 'http://47.95.254.249:18087/liquidation/callback/jctCall';//请求资产地址
	/*
		接收资产方推送的数据信息
	*/
    
    public function deals_info(){
        $deals_info = file_get_contents('php://input');
        $deals_info = json_decode($deals_info, true);
        // 		$deals_info = $this->rsa_decrypt($deals_info['mjn_data']);
        if(empty($deals_info)){
            $this->mjn_return(0,'参数错误！');
        }
        // 数据处理
        $SerialNumber = $deals_info['SerialNumber'];//验密时流水号
        $data['AssetsSerialNumber'] = $GLOBALS['db']->getOne("select AssetsSerialNumber from ".DB_PREFIX."mjn_assets order by id desc limit 1");//资产流水号
        $data['RegisterPhone'] = $deals_info['RegisterPhone'];//注册时手机号
        $data['DealName'] = $deals_info['DealName'];//标的名称
        $data['RepaymentType'] = 2;//还款方式 0 等额本息；1按月付息，到期还本；2 到期还本息；  默认 2
        $data['LoanAmount'] = $deals_info['LoanAmount'];//借款金额
        $data['Fee'] = $deals_info['Fee'];//服务费
        $data['Contract'] = $deals_info['Contract'];//借款合同地址
        $data['DealTimeType'] = $deals_info['DealTimeType'];//借款期限类型0：天标；1：月标
        $data['LoanTime'] = $deals_info['LoanTime'];//借款期限
        $data['Rate'] = $deals_info['Rate'];//年利率
        $data['DealDescription'] = $deals_info['DealDescription'];//项目介绍
        $data['DealPics'] = $deals_info['DealPics'];//项目图片资料
        $data['Rate'] = 8;
        // 防止重复数据
    
        //判断金额是否达到
    
        $AssetsSerialNumber = $data['AssetsSerialNumber'];
        $received_amount = $GLOBALS['db']->getOne("select sum(LoanAmount) from ".DB_PREFIX."mjn_deal where AssetsSerialNumber="."'$AssetsSerialNumber'");
        $AssetsSerial_money = $GLOBALS['db']->getOne("select Money from ".DB_PREFIX."mjn_assets where AssetsSerialNumber="."'$AssetsSerialNumber'");
        if($received_amount >= $AssetsSerial_money){
            $this->mjn_return(0,'资产金额已达接收上限');
        }
    
        // 数据更新
        //         $GLOBALS['db']->startTrans();
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX."mjn_deal",$data,"UPDATE","SerialNumber="."'$SerialNumber'"." and IsPwd=1");
        $res = $GLOBALS['db']->affected_rows();
        if($res){
    
            if(($received_amount+$data['LoanAmount']) >= $AssetsSerial_money){
                $GLOBALS['db']->query("update ".DB_PREFIX."mjn_assets set MjnNum=MjnNum+1,MjnMoney=MjnMoney+".$data['LoanAmount'].",status=1 where AssetsSerialNumber="."'$AssetsSerialNumber'");
            }else{
                $GLOBALS['db']->query("update ".DB_PREFIX."mjn_assets set MjnNum=MjnNum+1,MjnMoney=MjnMoney+".$data['LoanAmount']." where AssetsSerialNumber="."'$AssetsSerialNumber'");
            }
            $data['UserId'] = $GLOBALS['db']->getOne("select UserId from ".DB_PREFIX."mjn_deal where SerialNumber="."'$SerialNumber'");
            $rs = $this->insert_deal($data, $SerialNumber);
            if($rs){
                // 		        $GLOBALS['db']->commit();
                $this->mjn_return(1,'成功！',$SerialNumber);
            }else{
                // 		        $GLOBALS['db']->rollback();
                $this->mjn_return(0,'失败，请重试1！','');
            }
            	
        }else{
            // 		    $GLOBALS['db']->rollback();
            $this->mjn_return(0,'数据更新失败，请重试2！','');
        }
    }
    /*public function deals_info(){
		// $deals_info = file_get_contents('php://input');
		// $deals_info = json_decode($deals_info, true);
// 		$deals_info = $this->rsa_decrypt($deals_info['mjn_data']);
		// if(empty($deals_info)){
		// 	$this->mjn_return(0,'参数错误！');
		// }
		$deals_info = $_POST;
		unset($deals_info['mjn_data']);
		$sign = $_POST['mjn_data'];
		$mjn_data['msg1'] = $sign;

		$res = $this->rsa_decrypt($sign,$deals_info);  // 验签

		$mjn_data['msg2'] = serialize($deals_info);
		$GLOBALS['db']->autoExecute("mjn_msg", $mjn_data, "INSERT");

		// 验签结果
		if(!$res){
			$this->mjn_return(0,'验签失败4！',trim($deals_info['SerialNumber']));
		}

		// 数据处理
		$SerialNumber = $deals_info['SerialNumber'];//验密时流水号
        $data['AssetsSerialNumber'] = $GLOBALS['db']->getOne("select AssetsSerialNumber from ".DB_PREFIX."mjn_assets order by id desc limit 1");//资产流水号
        $data['RegisterPhone'] = $deals_info['RegisterPhone'];//注册时手机号
        $data['DealName'] = $deals_info['DealName'];//标的名称
        $data['RepaymentType'] = 2;//还款方式 0 等额本息；1按月付息，到期还本；2 到期还本息；  默认 2
        $data['LoanAmount'] = $deals_info['LoanAmount'];//借款金额
        $data['Fee'] = $deals_info['Fee'];//服务费
        $data['Contract'] = $deals_info['Contract'];//借款合同地址
        $data['DealTimeType'] = $deals_info['DealTimeType'];//借款期限类型0：天标；1：月标
        $data['LoanTime'] = $deals_info['LoanTime'];//借款期限
        $data['Rate'] = $deals_info['Rate'];//年利率
        $data['DealDescription'] = $deals_info['DealDescription'];//项目介绍
        $data['DealPics'] = $deals_info['DealPics'];//项目图片资料
        $data['Rate'] = 10;
		// 防止重复数据

        //判断金额是否达到
        
        $AssetsSerialNumber = $data['AssetsSerialNumber'];
        $received_amount = $GLOBALS['db']->getOne("select sum(LoanAmount) from ".DB_PREFIX."mjn_deal where AssetsSerialNumber="."'$AssetsSerialNumber'");
        $AssetsSerial_money = $GLOBALS['db']->getOne("select Money from ".DB_PREFIX."mjn_assets where AssetsSerialNumber="."'$AssetsSerialNumber'");
        if($received_amount >= $AssetsSerial_money){
            $this->mjn_return(0,'资产金额已达接收上限');
        }
        
		// 数据更新
//         $GLOBALS['db']->startTrans();
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX."mjn_deal",$data,"UPDATE","SerialNumber="."'$SerialNumber'"." and IsPwd=1");
        $res = $GLOBALS['db']->affected_rows();
		if($res){
		    
		    if(($received_amount+$data['LoanAmount']) >= $AssetsSerial_money){
		        $GLOBALS['db']->query("update ".DB_PREFIX."mjn_assets set MjnNum=MjnNum+1,MjnMoney=MjnMoney+".$data['LoanAmount'].",status=1 where AssetsSerialNumber="."'$AssetsSerialNumber'");
		    }else{
		        $GLOBALS['db']->query("update ".DB_PREFIX."mjn_assets set MjnNum=MjnNum+1,MjnMoney=MjnMoney+".$data['LoanAmount']." where AssetsSerialNumber="."'$AssetsSerialNumber'");
		    }
		    $data['UserId'] = $GLOBALS['db']->getOne("select UserId from ".DB_PREFIX."mjn_deal where SerialNumber="."'$SerialNumber'");
		    $rs = $this->insert_deal($data, $SerialNumber);
		    if($rs){
// 		        $GLOBALS['db']->commit();
		        $this->mjn_return(1,'成功！',$SerialNumber);
		    }else{
// 		        $GLOBALS['db']->rollback();
		        $this->mjn_return(0,'失败，请重试1！','');
		    }
			
		}else{
// 		    $GLOBALS['db']->rollback();
			$this->mjn_return(0,'数据更新失败，请重试2！','');
		}
	}*/
	//校验交易密码
	public function check_pay_password(){
	    // 	    $info = file_get_contents('php://input');
	    // 	    $info['RegisterPhone'] = '18931046560';
	    $info['RegisterPhone'] = trim($_GET['RegisterPhone']);
	    $info['CallBackUrl'] = trim($_GET['CallBackUrl']);
	    if(empty($info['RegisterPhone']) || empty($info['CallBackUrl'])){
	        showErr("参数错误");
	    }
	    $condition = 'w'.$info['RegisterPhone'];
	    $Publics = new Publics();
	    $seqno = $Publics->seqno();
	    $user_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name="."'$condition'");
	    //数据入库
	    $data['UserId'] = $user_id;
	    $data['CallBackUrl'] = $info['CallBackUrl'];
	    $data['SerialNumber'] = $seqno;
	    $data['CreateTime'] = time();
	    $rs = $GLOBALS['db']->autoExecute(DB_PREFIX . "mjn_deal", $data, "INSERT");
	    //跳转验密
	    if($rs){
	        $html = $Publics->verify_trans_password('mjn_deal','confirm_pay_password',$user_id,'4',$seqno,'_self');
	        echo $html;
	    }
	     
	}
	
	//校验交易密码
// 	public function check_pay_password(){
// 		$deals_info = $_GET;
// 		$sign = $deals_info['mjn_data'];

// 		$deals_info['CallBackUrl'] = urlencode($deals_info['CallBackUrl']);
// 		unset($deals_info['mjn_data']);
// 		unset($deals_info['act']);
// 		unset($deals_info['ctl']);
// 		// print_r($deals_info);die;
		
// 		$mjn_data['msg1'] = $sign;  // 签名
// 		$sign_res = $this->rsa_decrypt($sign,$deals_info);  // 验签
// 		$mjn_data['msg2'] = serialize($deals_info);	// 加签的参数
// 		$GLOBALS['db']->autoExecute("mjn_msg", $mjn_data, "INSERT");   // 签名信息入库
		
// 		$deals_info['PwdSeqno'] = $this->serial_number();
// 		if(!$sign_res){
// 			$this->mjn_return(0,"验签失败333！",$deals_info['PwdSeqno']);
// 		}

// // 	    $info = file_get_contents('php://input');
// // 	    $info['RegisterPhone'] = '18931046560';
// 	    $info['RegisterPhone'] = trim($_GET['RegisterPhone']);
// 	    $info['CallBackUrl'] = trim($_GET['CallBackUrl']);
// 	    if(empty($info['RegisterPhone']) || empty($info['CallBackUrl'])){
// 	        showErr("参数错误");
// 	    }
// 	    $condition = 'w'.$info['RegisterPhone'];
// 	    $Publics = new Publics();
// 	    $seqno = $Publics->seqno();
// 	    $user_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name="."'$condition'");
// 	    //数据入库
// 	    $data['UserId'] = $user_id;
// 	    $data['CallBackUrl'] = $info['CallBackUrl'];
// 	    $data['SerialNumber'] = $seqno;
// 	    $data['CreateTime'] = time();
// 	    $rs = $GLOBALS['db']->autoExecute(DB_PREFIX . "mjn_deal", $data, "INSERT");
// 	    //跳转验密
// 	    if($rs){
// 	        $html = $Publics->verify_trans_password('mjn_deal','confirm_pay_password',$user_id,'4',$seqno,'_self');
// 	        echo $html;
// 	    }
	    
// 	}
	//校验交易密码成功回调
	public function confirm_pay_password(){
	    $result = $_REQUEST;
	    $seqno = $result['businessSeqNo'];
	    if($result['flag'] == 1){
	        $rs = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."mjn_deal SET IsPwd=1 where SerialNumber="."'$seqno'"." and UserId=".$result['userId']);
	        $rs = $GLOBALS['db']->affected_rows();
	        if($rs){
	            $status['Result'] = 1;
        		$status['ResonText'] = '成功';
        		$status['SerialNumber'] = $seqno;
	        }else{
	            $status['Result'] = 0;
	            $status['ResonText'] = '失败';
	            $status['SerialNumber'] = '';
	        }
	    }
// 	    $rsa_str = $this->rsa_encrypt($status);
// 	    $url = '&return='.$rsa_str;
        $CallBackUrl = $GLOBALS['db']->getOne("select CallBackUrl from ".DB_PREFIX."mjn_deal where SerialNumber="."'$seqno'");
        if(strpos($CallBackUrl, '?')){
            $url = $CallBackUrl."&Result=".$status['Result']."&ResonText=".$status['ResonText']."&SerialNumber=".$status['SerialNumber'];
        }else{
            $url = $CallBackUrl."?Result=".$status['Result']."&ResonText=".$status['ResonText']."&SerialNumber=".$status['SerialNumber'];
        }
        header("Location:".$url);
	    
	} 
	
	//借款信息反查
	public function get_deal_info(){
	    $SerialNumber = $_POST;
	    unset($SerialNumber['mjn_data']);
	    $sign = $_POST['mjn_data'];
	    $mjn_data['msg1'] = $sign;
	    
	    $res = $this->rsa_decrypt($sign,$SerialNumber);  // 验签
	    
	    $mjn_data['msg2'] = serialize($SerialNumber);
	    $GLOBALS['db']->autoExecute("mjn_msg", $mjn_data, "INSERT");
	    
	    // 验签结果
	    if(!$res){
	        $this->mjn_return(0,'验签失败1004！',trim($SerialNumber['SerialNumber']));
	    }
	    
	    $SerialNumber = $SerialNumber['SerialNumber'];
	    
// 	    $SerialNumber = file_get_contents('php://input');
// 	    $SerialNumber = json_decode($SerialNumber, true);
// 	    $SerialNumber = $SerialNumber['SerialNumber'];
// 	    $SerialNumber = $_POST['SerialNumber'];
// 	    if(empty($SerialNumber)) {
// 	        $result['Result'] = 0;
// 	        $result['ResonText'] = "参数错误";
// 	        ajax_return($result);
// 	    }
	    $rs = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mjn_deal where SerialNumber="."'$SerialNumber'"." and IsPwd=1");
	    $deal_info['DealSerialNumber'] = $rs['SerialNumber'];
	    $deal_info['AskMoneySerialNumber'] = $rs['AssetsSerialNumber'];
	    $deal_info['DealName'] = $rs['DealName'];
	    $deal_info['RepaymentType'] = $rs['RepaymentType'];
	    $deal_info['LoanAmount'] = $rs['LoanAmount'];
	    $deal_info['Contract'] = $rs['Contract'];
	    $deal_info['DealTimeType'] = $rs['DealTimeType'];
	    $deal_info['LoanTime'] = $rs['LoanTime'];
	    $deal_info['Rate'] = $rs['Rate'];
	    $deal_info['DealDescription'] = $rs['DealDescription'];
	    $deal_info['DealPics'] = $rs['DealPics'];
	    $deal_info['Result'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where serial_number="."'$SerialNumber'");
	    if(!$deal_info['Result']){
	    	$deal_info['Result'] = 7;
	    }
	    if($rs && $deal_info['LoanAmount'] > 0){
	        ajax_return($deal_info);
	    }else{
	        $result['Result'] = 0;
	        $result['ResonText'] = "查询失败,标的不存在";
	        ajax_return($result);
	    }
	}
	
	//还款计划查询
	public function get_deal_repayment(){
	    $SerialNumber = $_POST['SerialNumber'];
	    if(empty($SerialNumber)) {
	        $result['Result'] = 0;
	        $result['ResonText'] = "参数错误";
	        ajax_return($result);
	    }
	    $deal_repay_data = $GLOBALS['db']->getRow("select d.id,d.serial_number,d.deal_status,r.repay_date,r.self_money,r.interest_money as fee from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_repay as r on d.id=r.deal_id where d.serial_number="."'$SerialNumber'");
	    if($deal_repay_data){
	        ajax_return($deal_repay_data);
	    }else{
	        $result['Result'] = 0;
	        $result['ResonText'] = "还款计划未生成";
	        ajax_return($result);
	    }

	}
	
	//还款计划批量查询
	public function get_deal_repayments(){
	    $start_time = strtotime($_POST['start_time']);
	    $end_time = strtotime($_POST['end_time']." 23:59:59");
	    if(empty($start_time) || empty($end_time)) {
	        $result['Result'] = 0;
	        $result['ResonText'] = "参数错误";
	        ajax_return($result);
	    }
	    if($start_time > $end_time){
	        $result['Result'] = 0;
	        $result['ResonText'] = "开始时间不能大于结束时间";
	        ajax_return($result);
	    }
	    $data = $GLOBALS['db']->getAll("select d.serial_number,d.deal_status,d.repay_start_date,(d.borrow_amount-d.fee) as loan_money,r.repay_date,r.repay_money,u.real_name from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_repay as r on d.id=r.deal_id left join ".DB_PREFIX."user as u on d.user_id=u.id where d.repay_start_time>=".$start_time." and d.repay_start_time<".$end_time." and d.AssetsSerialNumber<>''");
	    if($data){
	        ajax_return($data);
	    }else{
	        $result['Result'] = 0;
	        $result['ResonText'] = "信息不存在";
	        ajax_return($result);
	    }
	    
	}

	// 获取令牌
	function get_jct_token(){
		// $deals_info = file_get_contents('php://input');
		// $deals_info = json_decode($deals_info, true);
		// $deals_info = $this->rsa_decrypt($deals_info['mjn_data']);
		$sign = $_POST['mjn_data'];
		$deals_info['Telephone'] = $_POST['Telephone'];
		$mjn_data['msg2'] = serialize($deals_info);
		$res = $this->rsa_decrypt($sign,$deals_info);
		if(!$res){
			$result['status'] = 0;
			$result['jct_token'] = '验签失败1';
			ajax_return($result);
		}
		$mjn_data['msg1'] = serialize($deals_info);
		
		$GLOBALS['db']->autoExecute("mjn_msg", $mjn_data, "INSERT");

		$data['mobile'] = $deals_info['Telephone'];
		$data['jct_token'] = md5(session_id());
		$data['time'] = time();
		$data['date'] = date('Y-m-d H:i:s',time());
		$data['status'] = 1;
		// 已存在token
		$count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."mjn_token where mobile='".$data['mobile']."' and jct_token='".$data['jct_token']."'");
		if($count > 0){
            $result['status'] = 1;
			$result['jct_token'] = $data['jct_token'];
			ajax_return($result);
		}else{
			$res = $GLOBALS['db']->autoExecute(DB_PREFIX . "mjn_token", $data, "INSERT");
			if($res){
			    $result['status'] = 1;
			    $result['jct_token'] = $data['jct_token'];
				ajax_return($result);
			}
		}
	}
	// 注册 & 开户
	function cg_reg(){

		// $deals_info = file_get_contents('php://input');
		// $deals_info = json_decode($deals_info, true);
		$deals_info = $_POST;
		unset($deals_info['mjn_data']);
		$sign = $_POST['mjn_data'];
		$mjn_data['msg1'] = $sign;

		$res = $this->rsa_decrypt($sign,$deals_info);  // 验签

		$mjn_data['msg2'] = serialize($deals_info);
		$GLOBALS['db']->autoExecute("mjn_msg", $mjn_data, "INSERT");


		// 注册流水号
		$deals_info['RegSeqno'] = $this->serial_number();
		// 验签结果
		if(!$res){
			$this->mjn_return(0,'验签失败2！',trim($deals_info['RegSeqno']));
		}

		// 数据验证
		$deals_info = $this->reg_data($deals_info);

		// 注册
		$user_id = $this->mjn_register($deals_info);

		// 开户
		$result = $this->mjn_cg_user($deals_info,$user_id);
		$this->error_log(json_encode($result,JSON_UNESCAPED_UNICODE));
		if($result){
			$this->mjn_return(1,'开户成功！',trim($deals_info['RegSeqno']));
		}else{
			$this->mjn_return(0,'开户失败！',trim($deals_info['RegSeqno']));
		}
		
		
		// return $this->mjn_cg_user($deals_info,$user_id);

	}
	function reg_data($deals_info){
		if(!isset($deals_info['Telephone']) || empty($deals_info['Telephone'])){
			$this->mjn_return(0,'注册手机号缺失！',trim($deals_info['RegSeqno']));
		}
		if(!isset($deals_info['RealName']) || empty($deals_info['RealName'])){
			$this->mjn_return(0,'真实姓名缺失！',trim($deals_info['RegSeqno']));
		}
		if(!isset($deals_info['PasswordMd5']) || empty($deals_info['PasswordMd5'])){
			$this->mjn_return(0,'登录密码缺失！',trim($deals_info['RegSeqno']));
		}
		if(!isset($deals_info['CardNumber']) || empty($deals_info['CardNumber'])){
			$this->mjn_return(0,'身份证号缺失！',trim($deals_info['RegSeqno']));
		}
		if(!isset($deals_info['JctToken']) || empty($deals_info['JctToken'])){
			$this->mjn_return(0,'令牌缺失！',trim($deals_info['RegSeqno']));
		}
		return $deals_info;

	}
	// 注册
	function mjn_register($deals_info){
		$mjn_data['jct_token'] = trim($deals_info['JctToken']);   //令牌
		$mjn_data['seqno'] = trim($deals_info['RegSeqno']);		//开户流水号
		$mjn_data['trick_name'] = $deals_info['DealUser'];	//昵称
		$register_data['user_name'] = $mjn_data['user_name'] = 'w'.$deals_info['Telephone'];// 用户名
		$register_data['real_name'] = $mjn_data['real_name'] = trim($deals_info['RealName']);	// 真实姓名
		$register_data['idno'] = $mjn_data['idno'] = trim($deals_info['CardNumber']);				// 身份证
		$register_data['user_pwd'] = $mjn_data['user_pwd'] = trim($deals_info['PasswordMd5']);	// 登录密码
		$register_data['create_time'] = $mjn_data['create_time'] = time();					
		$register_data['mobile'] = $mjn_data['mobile'] = $deals_info['Telephone'];	// 开户手机号
		$register_data['idcardpassed'] = 1;
		$register_data['idcardpassed_time'] = time();
		$register_data['mobilepassed'] = 1;
		$register_data['register_source'] = 1; // 注册来源
		$register_data['is_effect'] = 1; 
		$register_data['is_delete'] = 0; 
		$register_data['create_date'] = date("Y-m-d",time());
		$register_data['real_name_encrypt'] = "AES_ENCRYPT('".$deals_info['RealName']."','".AES_DECRYPT_KEY."')";
		$register_data['idno_encrypt'] = "AES_ENCRYPT('".$deals_info['CardNumber']."','".AES_DECRYPT_KEY."')";
		$register_data['mobile_encrypt'] = "AES_ENCRYPT('".$deals_info['Telephone']."','".AES_DECRYPT_KEY."')";
		$reg_res = $GLOBALS['db']->autoExecute(DB_PREFIX . "mjn_register", $mjn_data, "INSERT");

		if(!$reg_res){
			$status['Result'] = 0;
			$status['ResonText'] = '获取注册信息失败，请重试！';
			$status['SerialNumber'] = $mjn_data['seqno'];
            return $status;
		}
		// 是否注册过
		$user_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name='".$register_data['user_name']."'");
		if(!isset($user_id) || empty($user_id)){
			$GLOBALS['db']->autoExecute(DB_PREFIX . "user", $register_data, "INSERT");
			$user_id = $GLOBALS['db']->insert_id();
			if($user_id>0){
				return $user_id;
			}else{
				$status['Result'] = 0;
				$status['ResonText'] = '注册失败，请重试！';
				$status['SerialNumber'] = $mjn_data['seqno'];
	            return $status;
			}
		}else{
			return $user_id;
		}
	}
	// 开户
	function mjn_cg_user($deals_info,$user_id){
		$user_info = $GLOBALS['db']->getRow("select accno,cunguan_tag from ".DB_PREFIX."user where id=".$user_id);
		if(empty($user_info['accno']) || $user_info['cunguan_tag'] == 0){
			$user_msg['user_id'] = $user_id;
	        $user_msg['real_name'] = $deals_info['RealName'];
	        $user_msg['idno'] = $deals_info['CardNumber'];
	        $user_msg['mobile'] = $deals_info['Telephone'];
	        //var_dump($user_msg);die;
	        $Register = new Register();
	        $res = $Register->register1($user_msg, 'U01');
	        $data['seqno'] = $res['map']['inBody']['businessSeqNo'];
	        $data['user_id'] = $user_id;
	        $data['accNo'] = $res['res']['outBody']['accNo'];
	        $data['secBankaccNo'] = $res['res']['outBody']['secBankaccNo'];
	        $data['form_con'] = json_encode($res['map']);
	        $data['back_con'] = json_encode($res['res']);
	        $data['type'] = 'U01';
	        $data['add_time'] = time();
	        $data['date_time'] = date('Y-m-d H:i:s');
	        $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
	        if($res['res']['respHeader']['respCode']=='P2P0000' || $res['callback_return_data']['respHeader']['respCode']=='P2P0000'){
	            if(empty($res['res']['outBody']['accNo']) && !empty($res['callback_return_data']['outBody']['accountNo'])){
	                $res['res']['outBody']['accNo'] = $res['callback_return_data']['outBody']['accountNo'];
	            }
	            $sql = "UPDATE ".DB_PREFIX."user SET accno='".$res['res']['outBody']['accNo']."',cunguan_tag=1 WHERE id=".$user_id; // 修改user表的信息
	            $GLOBALS['db']->query($sql);
	            $sql2 = "update ".DB_PREFIX."decository set status=1 where seqno='".$data['seqno']."'";  // 修改dec表的状态
	            $GLOBALS['db']->query($sql2);
	            return true;
			}else{
				$res['res']['respHeader']['respMsg'] = $res['res']['respHeader']['respMsg']?$res['res']['respHeader']['respMsg']:'开户失败';
				
	            return false;
			}
		}else{
            return true;
		}
	}
	
	function mjn_return($result,$text,$serial_number){
			$status['Result'] = $result;
			$status['ResonText'] = $text;
			$status['SerialNumber'] = $serial_number;
			$this->error_log(json_encode($status,JSON_UNESCAPED_UNICODE));
			ajax_return($status);
	}
	//RSA加密解密----开始
	//私钥加密
	function rsa_encrypt($data){
		ksort($data);
		// $data = json_encode($data);
		// $private_key = file_get_contents(APP_ROOT_PATH."system/utils/Depository/jct_mjn/private_key.pem");
		// // openssl_sign($data,$encrypted,$private_key, OPENSSL_ALGO_SHA256);
		// $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
		// openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
		// $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		// return $encrypted;
		$original_str=json_encode($data,JSON_UNESCAPED_UNICODE);//原数据
		$private_content=file_get_contents(APP_ROOT_PATH."system/utils/Depository/jct_mjn/private_key_sc.pem");
		$private_key=openssl_get_privatekey($private_content);
		openssl_sign($original_str,$sign,$private_key);
		openssl_free_key($private_key);
		$sign=base64_encode($sign);//最终的签名
		return $sign;
		// var_dump($sign);die;
	}

	//公钥解密
	function rsa_decrypt($sign,$data){
		ksort($data);
		// $public_key = file_get_contents(APP_ROOT_PATH."system/utils/Depository/jct_mjn/public_key.pem");
		// $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
		// openssl_public_decrypt(base64_decode($data),$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来
		// $decrypted = json_decode($decrypted);
		// return $decrypted;
		$public_content=file_get_contents(APP_ROOT_PATH."system/utils/Depository/jct_mjn/rsa_public_key.pem");
		$public_key=openssl_get_publickey($public_content);
		$sign=base64_decode($sign);//得到的签名
		$original_str=json_encode($data,JSON_UNESCAPED_UNICODE);	//得到的数据
		$result=(bool)openssl_verify($original_str,$sign,$public_key);
		openssl_free_key($public_key);
		return $result;
	}
	//RSA加密解密----结束
	function aaa(){
		$url = "https://jctwapcg.9caitong.com/member.php?ctl=mjn_deal&act=set_cg_password";
		

	
    	// $res = $this->post_curl($url,$data);
    	// $sign = $this->rsa_encrypt($data);
    	// var_dump($sign);die;
    	// $sign = 'aNTkeLveo07ZuU7nzpvgqmw9Dr9bCkt/67WfkDprUfw9FDny4MVNenV2MxO9BRR48ye0ubmV7Fu+gNwVb8yL400KRUFrCJL8HOyBcLqVAJ2G3boi/awVBFK1jUYHDddP/ZUTFI80loc3Q+YgxX8Wpc3d86fVhcO/UhpNnZpjbYc=';
    	// $data2 = $this->rsa_decrypt($sign,$data);
    	// var_dump($data2);die;
    	$res = file_get_contents($url);
		var_dump($res);die;

    	$sign = $this->rsa_encrypt($data);
    	var_dump($sign);
    	echo "<br>";
    	$data2 = $this->rsa_decrypt($sign,$data);
    	var_dump($data2);
    }
	//生成流水号
	function serial_number($type){
		$yCode = $type ? 'ZC' : 'JCT';
		$orderSn = $yCode.date('YmdHis',time()).time().rand(0,9999);
		return $orderSn;
	}

	/*
		请求资产方，上传所需金额
	*/
	function get_asset(){
		$post_data['Money'] = 3000;
		$post_data['AssetsSerialNumber'] = $this->serial_number(1);
		$post_data['CreateTime'] = time();
		$GLOBALS['db']->autoExecute(DB_PREFIX."mjn_assets",$post_data,"INSERT");
		$url = $this->TotalAssetsUrl;	
		$param = "Money=".$post_data['Money']."&AssetsSerialNumber=".$post_data['AssetsSerialNumber'];
		$result = $this->post_curl($url,$param);
		if($result == "OK"){
		    
		}
		echo "<pre>";
		print_r($result);die;
	}
	/*
		借款状态通知
	*/
	function deal_information(){
		$ask_times = 30;
		$mjn_deals = $GLOBALS['db']->getAll("select id,user_id,status,async_status,serial_number,ask_times,reson_text from ".DB_PREFIX."mjn_async where async_status = 0 and ask_times <=".$ask_times);
		foreach($mjn_deals as $k => $v){
			$post_data['Result'] = $mjn_deals['status'];
			$post_data['SerialNumber'] = $mjn_deals['SerialNumber'];
			$post_data['ResonText'] = $mjn_deals['reson_text'];
			$url = $url;
			$output = $this->post_curl($url,$post_data);
			if($output == 'OK'){
				$res=$GLOBALS['db']->query("update ".DB_PREFIX."mjn_async set status=1 where id=".$v['id']);
			}else{
				if($v['ask_times'] >= $ask_times){
					$condition = "status = 1,";
				}else{
					$condition = '';
				}
				$res=$GLOBALS['db']->query("update ".DB_PREFIX."mjn_async set ".$condition."ask_times=ask_times+1 where id=".$v['id']);
			}
		}
	}
	function post_curl($url,$post_data){
		$curl = curl_init();    //启动一个curl会话
		curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
		curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		$output = curl_exec($curl);  //执行curl会话 
		curl_close($curl);
		return $output;
	}
	function get_curl($url,$get_data){
		//初始化
		$ch = curl_init();
		//设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		//执行并获取HTML文档内容
		$output = curl_exec($ch);
		//释放curl句柄
		curl_close($ch);
		return $output;
	}
	//标的入库 同步宜宾
	function insert_deal($info,$SerialNumber){
         $data['serial_number'] = $SerialNumber;//流水号
         $data['AssetsSerialNumber'] = $info['AssetsSerialNumber'];//资产流水号
         $data['name'] = $info['DealName'];//贷款名称
         $data['sub_name'] = $info['DealName'];//简短名称
         $data['cate_id'] = 7;//分类 车贷 房贷
         $data['user_id'] = $info['UserId'];
         $data['description'] = $info['DealDescription'];
         $data['sort'] = $GLOBALS['db']->getOne("select sort from ".DB_PREFIX."deal order by sort desc limit 1") + 1;
         $data['type_id'] = 12;//标的类型 12理财计划
         $data['seo_title'] = '';
         $data['seo_keyword'] = '';
         $data['seo_description'] = '';
         $data['borrow_amount'] = $info['LoanAmount'];//借款金额
         $data['min_loan_money'] = 100;//最低起投
         $data['repay_time'] = $info['LoanTime'];//借款期限
         $data['rate'] = $info['Rate'];//年利率
         $data['enddate'] = 30;//筹标期限
         $data['services_fee'] = '';
         $data['loantype'] = $info['RepaymentType'];//还款方式 0 等额本息    1 按月付息,到期还本  2到期还本息  3本金均摊，利息固定 ',
         $data['repay_time_type'] = $info['DealTimeType']; //0天 1月
         $data['max_loan_money'] = 0;//最高投标额度
         $data['risk_rank'] = 0;//风险等级
         $data['risk_security'] = '借款人信息';
         $data['deal_sn'] = "MER".to_date(TIME_UTC,"Y")."".str_pad($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal order by id desc limit 1") + 1,7,0,STR_PAD_LEFT);
         $data['fee'] = $info['Fee']; //借款者管理费
         $data['user_loan_manage_fee'] = 0;//投资者管理费
         $data['user_loan_interest_manage_fee'] = 0;
         $data['manage_impose_fee_day1'] = 0;//普通逾期管理费
         $data['manage_impose_fee_day2'] = 0;//严重逾期管理费
         $data['impose_fee_day1'] = 0.05; //普通逾期费率
         $data['impose_fee_day2'] = 0;//严重逾期费率
         $data['user_load_transfer_fee'] = 0;//债权转让管理费
         $data['transfer_day'] = 0;//满标放款多少天后才可以进行转让 0不限制
         $data['compensate_fee'] = 0;//提前还款补偿
         $data['user_bid_rebate'] = 0;//投资返利%
         $data['guarantees_amt'] = 0.00;//借款保证金（冻结借款人的金额，需要提前存钱）
         $data['guarantor_amt'] = 0.00;//担保方，担保金额(代偿金额累计不能大于担保金额)
         $data['guarantor_margin_amt'] = 0.00;//担保方，担保保证金额(需要冻结担保方的金额）
         $data['guarantor_pro_fit_amt'] = 0.00;//担保收益
         $data['generation_position'] = 100;//申请延期的额度
         $data['uloadtype'] = 0;//用户投标类型 0按金额，1 按份数
         $data['portion'] = 5;//分成多少份
         $data['max_portion'] = 0;//最多买多少份
         $data['contract_id'] = 11;
         $data['tcontract_id'] = 11;
         $data['score'] = 0;
         $data['user_bid_score_fee'] = 0;
         $data['use_ecv'] = 1;//是否可以使用红包
         $data['use_interestrate'] = 1;//是否允许使用加息券 0 不允许 1允许
         $data['mortgage_type'] = '';
         $data['mortgage_color'] = '';
         $data['mortgage_brand'] = '';
         $data['mortgage_year'] = '';
         $data['mortgage_info'] = '';
         $data['mortgage_insurance'] = '';
         $data['cunguan_tag'] = 1;
         $data['house_info'] = '';//房贷宝抵押物信息
         $data['risk_grade'] = 5;//风险等级
         $data['is_effect'] = 0;
         $data['deal_status'] = 1;
         $data['create_time'] = TIME_UTC;
         $data['update_time'] = TIME_UTC;
         
         $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,"INSERT");
         $deal_id = intval($GLOBALS['db']->insert_id());
         
         $Deal = new Deal();
         $map['id'] = $deal_id;
         $map['name'] = $data['name'];
         $map['borrow_amount'] = $data['borrow_amount'];
         $map['rate'] = $data['rate'];
         $map['user_id'] = $data['user_id'];
         $res = $Deal->deals('P01','01',$map,$SerialNumber);
             
         if($res['respHeader']['respCode']=='P2P0000') {
             $GLOBALS['db']->query("update jctp2p_deal set objectaccno='".$res['outBody']['objectaccNo']."',cunguan_status='01' where id=".$deal_id);
             
             $rs = $Deal->deals('P04','02',$map);
             if($rs['respHeader']['respCode']=='P2P0000') {
                 $rs1 = $GLOBALS['db']->query("update jctp2p_deal set cunguan_status='02' where id=".$deal_id);
                 $re1 = $GLOBALS['db']->affected_rows();
                 if($rs1){
//                      $this->send_async_notice($SerialNumber, 1, $SerialNumber."标的已发布");
                     return true;
                 }else{
                     return false;
                 }
             }
//                  $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal SET objectaccno="."{$res['outBody']['objectaccNo']}".",cunguan_status='01' where id=".$deal_id);
                 //异步通知
                 // 	        insert_mjn_async($info['SerialNumber'],$seqno,$user_id,2,$info['SerialNumber']."标的已发布");
         }else{
             // 	        insert_mjn_async($info['SerialNumber'],$seqno,$user_id,7,$info['SerialNumber']."标的发布失败");
             return false;
         }
    }
	
	
	// 设置交易密码
	function set_cg_password(){
		// $deals_info = file_get_contents('php://input');
		// $deals_info = json_decode($deals_info, true);

		$deals_info = $_GET;
		$sign = $deals_info['mjn_data'];

		$deals_info['CallBackUrl'] = urlencode($deals_info['CallBackUrl']);
		unset($deals_info['mjn_data']);
		unset($deals_info['act']);
		unset($deals_info['ctl']);
		// print_r($deals_info);die;
		
		$mjn_data['msg1'] = $sign;  // 签名
		$sign_res = $this->rsa_decrypt($sign,$deals_info);  // 验签

		$mjn_data['msg2'] = serialize($deals_info);	// 加签的参数
		$GLOBALS['db']->autoExecute("mjn_msg", $mjn_data, "INSERT");   // 签名信息入库
		
		$deals_info['PwdSeqno'] = $this->serial_number();
		if(!$sign_res){
			$this->mjn_return(0,"验签失败22！",$deals_info['PwdSeqno']);
		}

		$user_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name='w".$deals_info['RegisterPhone']."'");
		if($user_id>0){
			$deals_info['user_id'] = $user_id;
		}else{
			$this->mjn_return(0,"未检测到用户信息！",$deals_info['PwdSeqno']);
		}
		$data = $this->bank_data($deals_info);
		$res = $GLOBALS['db']->autoExecute(DB_PREFIX . "mjn_user_bank", $data, "INSERT");
		if(!$res){
			$this->mjn_return(0,"接受数据失败！",$deals_info['PwdSeqno']);
		}
		$result = $this->setpaypassword($data,$user_id);
		return $result;
	}
	// 处理数据
	function bank_data($deals_info){
		$data['reg_mobile'] = $deals_info['RegisterPhone'];
		$data['real_name'] = $deals_info['RealName'];
		$data['bank_card'] = $deals_info['BankCard'];
		$data['bank_mobile'] = $deals_info['Telephone'];
		$data['bank_id'] = $deals_info['BankId'];
		$data['bank_zone'] = $deals_info['BankZone']?$deals_info['BankZone']:'';
		$data['seqno'] = $deals_info['PwdSeqno'];
		$data['status'] = 1;
		// $data['callback_url'] = "http://beta.wallet.91naxia.com/h5/callback/bindCardCallBack";
		$data['callback_url'] = urldecode($deals_info['CallBackUrl']);
		$data['user_id'] = $deals_info['user_id'];
		if(!isset($data['bank_id']) || empty($data['bank_id'])){
			$this->mjn_return(0,"bank_id不能为空！",$deals_info['PwdSeqno']);
		}
		if(!isset($data['real_name']) || empty($data['real_name'])){
			$this->mjn_return(0,"姓名不能为空！",$deals_info['PwdSeqno']);
		}
		if(!isset($data['bank_card']) || empty($data['bank_card'])){
			$this->mjn_return(0,"银行卡号不能为空！",$deals_info['PwdSeqno']);
		}
		if(!isset($data['bank_mobile']) || empty($data['bank_mobile'])){
			$this->mjn_return(0,"银行预留手机号不能为空！",$deals_info['PwdSeqno']);
		}
		if(!isset($data['callback_url']) || empty($data['callback_url'])){
			$this->mjn_return(0,"回调地址不能为空！",$deals_info['PwdSeqno']);
		}
		return $data;
	}
	//设置存管交易密码
    function setpaypassword($deals_info,$user_id){
        if($user_id>0){
            $cunguan_info = $GLOBALS['db']->getRow("select cunguan_pwd,accno from ".DB_PREFIX."user where id=".$user_id);
            if($cunguan_info['accno']){
            	$Publics = new Publics();
                $seqno = $Publics->seqno();
                // 设密流水号入库
                $GLOBALS['db']->query("update ".DB_PREFIX."mjn_user_bank set set_pwd_seqno='".$seqno."' where seqno='".$deals_info['seqno']."'");
                if($cunguan_info['cunguan_pwd']){
                    app_redirect(url("index","mjn_deal#wap_check_pwd",array("id"=>$user_id,'businessSeqNo'=>$seqno)));
                }else{
                    $Publics->verify_trans_password('mjn_deal','back_paypassword',$user_id,'1',$seqno,'_self');
                }
            }else{
                $this->mjn_return(0,"未开户成功！",$deals_info['seqno']);
            }
        }else{
            $this->mjn_return(0,"未检测到用户信息！",$deals_info['seqno']);
        }
    }
    //支付密码回调
    function back_paypassword(){
        $user_info['userId'] = $_GET['userId'];
        $user_info['flag'] = $_GET['flag'];
        $user_info['signTime'] = $_GET['signTime'];
        $user_info['signature'] = $_GET['signature'];
        $user_info['businessSeqNo'] = $_GET['businessSeqNo'];
        $map['back_con'] = json_encode($user_info);
        if($_GET['businessSeqNo']){
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$map,"UPDATE","seqno='".$_GET['businessSeqNo']."'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$map);
        }

        if($user_info['flag']==1){
            // $types = $GLOBALS['db']->getOne("select `type` from ".DB_PREFIX."decository where seqno='".$_GET['businessSeqNo']."'");
            $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "user SET cunguan_pwd=1 WHERE id=" . $user_info['userId']);
			app_redirect(url("index","mjn_deal#wap_check_pwd",array("id"=>$user_info['userId'],'businessSeqNo'=>$user_info['businessSeqNo'])));
            

        }

    }
    //绑卡--第一步:验证交易密码
    function wap_check_pwd()
    {
        $user_id = intval($_GET['id']);
        $set_pwd_seqno = $_GET['businessSeqNo'];
        $user = $GLOBALS['db']->getRow("select id,cunguan_pwd,accno from ".DB_PREFIX."user where id=".$user_id);
        if ($user['id'] > 0) {
            if($user['accno']){
                if($user['cunguan_pwd']){
                    $Publics = new Publics();
                    $seqno = $Publics->seqno();
                    // 验密流水号入库
                	$GLOBALS['db']->query("update ".DB_PREFIX."mjn_user_bank set check_pwd_seqno='".$seqno."' where set_pwd_seqno='".$set_pwd_seqno."'");
                    $re = $Publics->verify_trans_password('mjn_deal', "cg_bind_bank", $user['id'], '4', $seqno,'_self');
                    echo $re;die;
                }
            }
        }

    }
    // 绑卡页面
    function cg_bind_bank(){
    	$user_id = intval($_GET['userId']);
    	$seqno = $_GET['businessSeqNo'];
        if($user_id>0){
            $bank_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mjn_user_bank where status=1 and check_pwd_seqno='".$seqno."'");
            $bank_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."bank where bankid=".$bank_info['bank_id']);
            $GLOBALS['tmpl']->assign("bank_info",$bank_info);
            $GLOBALS['tmpl']->assign("seqno",$seqno);
            $GLOBALS['tmpl']->assign("bank_name",$bank_name);
            $GLOBALS['tmpl']->assign("cate_title","绑定银行卡");
            $GLOBALS['tmpl']->assign("inc_file","inc/uc/mjn_bank.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }

    }
    // 绑卡逻辑
    function add_bank(){
    	$seqno = $_POST['businessSeqNo'];
    	$bank_mobile = $_POST['mobile'];
    	$sms_code = $_POST['sms_code'];

    	if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($bank_mobile)."' AND verify_code='".$sms_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
            $json['status'] = 2;
            $json['info'] = "短信验证码出错或已过期";
            ajax_return($json);
        }
    	$bank_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mjn_user_bank where check_pwd_seqno='".$seqno."'");
    	$user_info = $GLOBALS['db']->getRow("select accno,real_name,idno,mobile from " . DB_PREFIX . "user where id = " . $bank_info['user_id']);
    	$user_msg['user_id'] = $bank_info['user_id'];
        $user_msg['real_name'] = $user_info['real_name'];
        $user_msg['idno'] = $user_info['idno'];
        $user_msg['mobile'] = $user_info['mobile'];
        $user_msg['bank_id'] = $bank_info['bank_id']; //所属银行ID
        $user_msg['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
        $user_msg['bank_mobile'] = $bank_info['bank_mobile']; //银行预留手机号
        $user_msg['businessSeqNo'] = strim($seqno); //流水号
        $user_msg['oldbankcard'] = '';
        $user_msg['dep_account'] = $user_info['accno'];
        $Register = new Register();
        $res = $Register->register1($user_msg, 'B01');

        $data['seqno'] = $res['map']['inBody']['businessSeqNo'];
        $data['user_id'] = $bank_info['user_id'];
        $data['accNo'] = $user_info['accno'];
        $data['secBankaccNo'] = $res['res']['outBody']['secBankaccNo'];
        $data['form_con'] = json_encode($res['map'],JSON_UNESCAPED_UNICODE);
        $data['back_con'] = json_encode($res['res'],JSON_UNESCAPED_UNICODE);
        $data['type'] = 'B01';
        $data['add_time'] = TIME_UTC;
        $data['date_time'] = date('Y-m-d H:i:s');
        $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT");
        if ($res['res']['respHeader']['respCode'] == 'P2P0000') {

            $bank['user_id'] = $bank_info['user_id'];
            $bank['bank_id'] = $bank_info['bank_id']; //所属银行ID
            $bank['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
            $bank['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
            $bank['real_name'] = $user_info['real_name'];
            $bank['create_time'] = TIME_UTC;
            $bank['addip'] = get_client_ip();
            $bank['status'] = 1;
            $bank['cunguan_tag'] = 1;
            $bank['bank_source'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank", $bank, "INSERT");
            $root['jump'] = $bank_info['callback_url']."&Result=1&ResonText=绑卡成功&SerialNumber=".$bank_info['seqno']."&BankMobile=".$bank_mobile;
            $root['status'] = 1;
            $root['info'] = '绑卡成功';
            ajax_return($root);
        } else {
            $root['status'] = 0;
            $root['info'] = $res['res']['respHeader']['respMsg'];
            $root['jump'] = $bank_info['callback_url']."&Result=0&ResonText=".$root['info']."&SerialNumber=".$bank_info['seqno']."&BankMobile=".$bank_mobile;
            ajax_return($root);
        }
    }
    
    function send_async_notice($serial_number,$status,$reson_text){
        $data['serial_number'] = $serial_number;
        $data['status'] = $status;
        $data['reson_text'] = $reson_text;
        $data['create_time'] = time();
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "mjn_async", $data, "INSERT");
        if($res){
            return true;
        }else{
            return false;
        }
    }
    function error_log($str){
    	$str = $str."\r\n";
    	file_put_contents('./mjn_log.txt',$str.PHP_EOL,FILE_APPEND);
    }

}