<?php
    class Deal{
		/*
		*$type:业务操作类型（P01：标的发布  P02：标的流标    P03：标的撤标     P04：标的修改）
		*$status:标的状态（01：发布   02：投资中   03：放款   04：流标   05：撤标    06：还款中    07：结束）
		*$data: 标的信息
		*/
		function deals($type,$status,$data,$mjn_seqno=""){
			$Publics = new Publics();
			$seqno = $mjn_seqno ? $mjn_seqno : $Publics->seqno();
            //$map['reqHeader'] = $Publics->reqheader("BXTB01");
            $map['reqHeader'] = $Publics->reqheader("O00001");
            $map['inBody']['businessSeqNo'] = $seqno;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型---发标  P01：标的发布  P02：标的流标    P03：标的撤标     P04：标的修改
            $map['inBody']['objectId'] = strval($data['id']);//标的编号
            $map['inBody']['objectName'] = $data['name'];//标的名称
            $map['inBody']['totalAmount'] = $data['borrow_amount'];//标的金额
            $map['inBody']['interestRate'] = $data['rate'];//年化利率
            $map['inBody']['returnType'] = "08";//还款方式 08：按月付息到期还本
            $map['inBody']['returnInfoList'] = array(array('oderNo'=>'0','returnNo'=>'1','returnDate'=>'20170609'));//还款计划列表
//            $map['inBody']['oderNo'] = "";//序号
//            $map['inBody']['returnNo'] = "";//还款期数
//            $map['inBody']['returnDate'] = "";//还款日期
            $map['inBody']['customerId'] = $data['user_id'];//会员编号
            $map['inBody']['projectStatus'] = $status;//标的状态--发标
            $map['inBody']['nature'] = "00";//标的属性
            $map['inBody']['note'] = "";//备注
            $map['inBody']['objectType'] = $mjn_seqno ? "01" : "00";//标的类型
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            //$dep = $Publics->encrypt(json_encode($map));
			foreach($map as $k=>$v){
				if($v["objectName"]){
					$map[$k]["objectName"] = $Publics->encrypt($v["objectName"]);
					
				}
				if($v["objectId"]){
					$map[$k]["objectId"] = $Publics->encrypt($v["objectId"]);
				}
				if($v["phoneNo"]){
					$map[$k]["phoneNo"] = $Publics->encrypt($v["phoneNo"]);
				}
				if($v["customerId"]){
					$map[$k]['customerId'] = $Publics->encrypt($v["customerId"]);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->bidinfosync(json_encode($map));
			if($result['respHeader']['respCode']=='P2P0000'){
				$res['status'] = 1;
			}
			$res['user_id'] = $data['user_id'];
            $res['seqno'] = $map['inBody']['businessSeqNo'];
            $res['objectaccNo'] = $result['outBody']['objectaccNo']=$Publics->decrypt($result['outBody']['objectaccNo']);
			$res['money'] = $data['borrow_amount'];
            $res['form_con'] = json_encode($map);
            $res['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $res['type'] = $type;
            $res['add_time'] = time();
			$res['date_time'] = date("Y-m-d H:i:s");
			$res['suc_time'] = date("Ymd");
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$res,"INSERT");
			return $result;
		}
		/*
		*$type:业务操作类型（T01：投标，T10：营销）
		*$SeqNo:流水号
		*$money:金额
		*$userId 用户id
		*$is_auto 是否自动投标
		*/
		function deal($SeqNo,$type,$money,$deal_id,$userId,$is_auto=false){
			$SeqNo = strim($SeqNo);
			$type = strim($type);
			$money = floatval($money);
			$userId = intval($userId);
			$deal_id = intval($deal_id);
			$user_info = $GLOBALS['db']->getRow("select accno,real_name,idno,mobile from ".DB_PREFIX."user where id = ".$userId);
			$deal_info = $GLOBALS['db']->getRow("select user_id,objectaccno,old_deal_id,plan_id from ".DB_PREFIX."deal where id = ".$deal_id);
			$objectaccNo = $deal_info['objectaccno'];
			$accNo = $user_info['accno'];
            $debts_user=$GLOBALS['db']->getOne("select accno from ".DB_PREFIX."user where id = ".$deal_info['user_id']);  // 转让方台账账户
			$Publics = new Publics();
            //$map['reqHeader'] = $Publics->reqheader("ZJTB01");
            $map['reqHeader'] = $Publics->reqheader("T00004");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
			if($is_auto){
				$map['inBody']['entrustflag'] = "01";//委托标识-已委托
			}else{
				$map['inBody']['entrustflag'] = "00";//委托标识-未委托
			}
			if($type=='T10'){
				$map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"JCTPM20170630","cebitAccountNo"=>"$accNo","currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T10"));//资金账务处理列表
				$map['inBody']['contractList'] = array();//资金账务处理列表
            	$map['inBody']['objectId'] = "";//标的id
			}elseif($type=='T01'){
				$map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"$accNo","cebitAccountNo"=>"$objectaccNo","currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T01"));//资金账务处理列表
				$map['inBody']['contractList'] = array();//资金账务处理列表
            	$map['inBody']['objectId'] = strval($deal_id);//标的id
			}elseif($type=='T07'){
                $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"$accNo","cebitAccountNo"=>"$debts_user","currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T07"));//资金账务处理列表
				$map['inBody']['contractList'] = array();//资金账务处理列表
                $map['inBody']['objectId'] = strval($deal_info['old_deal_id']);//标的id
            }else{
				return false;
			}
            $map['inBody']['note'] = "";//备注
			//print_r($userId);die;
            //$dep = $Publics->sign($map);//签名
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            //$dep = $Publics->encrypt(json_encode($map));

            //$dep = $Publics->sign($map);//签名
//            $map['inBody']['summaryCode'] = "";//摘要码   与资金账务处理列表中业务操作类型相同、T12：其他费用收取
//            $map['inBody']['contractList'] = "";//此列表放款、债券转受让时必填
//            $map['inBody']['contractType'] = "";//    01 投资人合同
//            $map['inBody']['contractRole'] = "";//角色   01投资人
//            $map['inBody']['contractFileNm'] = "";//合同名称命名规则：平台代码_借方汇元编号_贷方会员编号_标的编号_交易日期
//            $map['inBody']['debitUserid'] = "";//角色为投资人、转让/受让人时必填
//            $map['inBody']['cebitUserid'] = "";//角色为融资人、转让/受让人时必填
			$map['inBody']['objectId'] = $Publics->encrypt($map['inBody']['objectId']);
			foreach($map['inBody']['accountList'] as $key=>$value){
				if(!empty($value['cebitAccountNo'])){
					$map['inBody']['accountList'][$key]['cebitAccountNo'] = $Publics->encrypt($value['cebitAccountNo']);
				}
				if(!empty($value['debitAccountNo'])){
					$map['inBody']['accountList'][$key]['debitAccountNo'] = $Publics->encrypt($value['debitAccountNo']);
				}
			}

            //$deps = $Publics->encrypt(json_encode($map));
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));

            // 资金同步交易信息入库
			$res['seqno'] = $map['inBody']['businessSeqNo'];
			$res['user_id'] = $userId;
			$res['accNo'] = $accNo;
            $res['objectaccNo'] = $objectaccNo;
			$res['money'] = $money;
            $res['form_con'] = json_encode($map);
            $res['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $res['type'] = $type;
			$res['add_time'] =time();
			$res['date_time'] =date("Y-m-d H:i:s");
			$res['suc_time'] =time();
			/* if($result['respHeader']['respCode']=="P2P0000"){
				$res['status'] =1;
				$res['suc_time']=date("Ymd");
			} */
			if($type=='T10'){
				$res['create_time'] =time();
				if($result['respHeader']['respCode']=="P2P0000"){
					$res['status'] =1;
					$res['suc_time']=date("Ymd");
				}
				$res['types'] =$type;
				$GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$res,"INSERT");
			}
			
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$res,"INSERT");
            return $result;
		}
		/*
		*$type:业务操作类型（T01：投标，T10：营销）
		*$SeqNo:流水号
		*$money:金额
		*$userId 用户id
		*/
		function experience_money($SeqNo,$type,$money,$userId){
			$SeqNo = strim($SeqNo);
			$type = strim($type);
			$money = floatval($money);
			$userId = intval($userId);
			$user_info = $GLOBALS['db']->getRow("select accno,real_name,idno,mobile from ".DB_PREFIX."user where id = ".$userId);
			$accNo = $user_info['accno'];
			$Publics = new Publics();
			if($type!='T10'){
				return false;
			}
            $map['reqHeader'] = $Publics->reqheader("T00004");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			$map['inBody']['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>"JCTPM20170630","cebitAccountNo"=>"$accNo","currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
			$map['inBody']['contractList'] = array();//资金账务处理列表
            $map['inBody']['objectId'] = "";//标的id
			$map['inBody']['note'] = "";//备注
			$dep = $this->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
			foreach($map['inBody']['accountList'] as $key=>$value){
				if($value['cebitAccountNo']){
					$map['inBody']['accountList'][$key]['cebitAccountNo'] = $Publics->encrypt($value['cebitAccountNo']);
				}
				if($value['debitAccountNo']){
					$map['inBody']['accountList'][$key]['debitAccountNo'] = $Publics->encrypt($value['debitAccountNo']);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));
			$data['seqno'] = $map['inBody']['businessSeqNo'];
			$data['user_id'] = $userId;
			$data['accNo'] = $accNo;
            $data['objectaccNo'] = $result['outBody']['objectaccNo'];
			$data['money'] = $money;
            $data['form_con'] = json_encode($map);
            $data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $data['type'] = $type;
			$data['add_time'] =time();
			$data['date_time'] =date("Y-m-d H:i:s");
			if($result['respHeader']['respCode']=="P2P0000"){
				$data['status'] =1;
				$data['suc_time']=date("Ymd");
			}
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			return $result;
		}
		//满标后，修改标的状态为放款,还款中,出款
		function save_deal($deal_id,$seqno,$type,$list){
			$SeqNo = strim($seqno);
			$deal_id = intval($deal_id);
			$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
			$Publics = new Publics();
			$loantype = $deal_info['loantype'];
			if($loantype==1){//按月付息到期还本
				$loantype = "08";
			}elseif($loantype==0){//等额本息
				$loantype = "03";
			}elseif($loantype==2){//等额本金
				$loantype ="04";
			}elseif($loantype==3){//等额等息
				$loantype ="05";
			}elseif($loantype==4){//一次付息
				$loantype = "07";
			}elseif($loantype==5){//一次性还本还息
				$loantype="01";
			}elseif($loantype==7){
				$loantype="07";//一次付息
			}
			$oderNo =0;
			if($list){//不为空
				 foreach($list as $key=>$value){
					$oderNo++;
					$repay['oderNo'] = "$oderNo";
					$repay['returnDate'] = str_replace("-","",$value['repay_date']);
					$repay['returnNo'] = "$oderNo";
					$repays[] = $repay;
				}
			}
            $map['reqHeader'] = $Publics->reqheader("O00001");
            $map['inBody']['businessSeqNo'] = "$SeqNo";//业务流水号
            $map['inBody']['busiTradeType'] = "P04";//业务操作类型---发标  P01：标的发布  P02：标的流标    P03：标的撤标     P04：标的修改
            $map['inBody']['objectId'] = "$deal_id";//标的编号
            $map['inBody']['objectName'] = $deal_info['name'];//标的名称
            $map['inBody']['totalAmount'] = $deal_info['borrow_amount'];//标的金额
            $map['inBody']['interestRate'] = $deal_info['rate'];//年化利率
            $map['inBody']['returnType'] = $loantype;//还款方式 08：按月付息到期还本
            $map['inBody']['returnInfoList'] = $repays;//还款计划列表
			if(!$list){
				$map['inBody']['returnInfoList'] = array(array('oderNo'=>'0','returnNo'=>'1','returnDate'=>'20170610'));//还款计划列表
			}
           
            $map['inBody']['customerId'] = $deal_info['user_id'];//会员编号
			$map['inBody']['projectStatus'] = $type;//标的状态--放款
			$map['inBody']['nature'] = "00";//标的属性
            $map['inBody']['note'] = "";//备注
			$map['inBody']['objectType'] = "00";//标的类型
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            foreach($map as $k=>$v){
				if($v["objectName"]){
					$map[$k]["objectName"] = $Publics->encrypt($v["objectName"]);
					
				}
				if($v["objectId"]){
					$map[$k]["objectId"] = $Publics->encrypt($v["objectId"]);
				}
				if($v["phoneNo"]){
					$map[$k]["phoneNo"] = $Publics->encrypt($v["phoneNo"]);
				}
				if($v["customerId"]){
					$map[$k]['customerId'] = $Publics->encrypt($v["customerId"]);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->bidinfosync(json_encode($map));
            $data['seqno'] = $map['inBody']['businessSeqNo'];
            $data['user_id'] = $deal_info['user_id'];
            $data['objectaccNo'] = $result['outBody']['objectaccNo']=$Publics->decrypt($result['outBody']['objectaccNo']);
			$data['money'] = $deal_info['borrow_amount'];
            $data['form_con'] = json_encode($map);
            $data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $data['type'] = 'P04';
            $data['add_time'] = time();
			$data['date_time'] =date("Y-m-d H:i:s");
			if($result['respHeader']['respCode']=="P2P0000"){
				$data['status'] =1;
				$data['suc_time']=date("Ymd");
			}
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			return $result;
		}
		//标的放款,还款,出款,代偿还款,代偿回款
		function do_repay($seqno,$type,$data){
			$SeqNo = strim($seqno);
			$type = strim($type);
			$deal_id = intval($deal_id);
			//$userId = intval($userId);
			/* $deal_info = $GLOBALS['db']->getRow("select user_id,objectaccno,borrow_amount from ".DB_PREFIX."deal where id = ".$deal_id);
			$user_info =$GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id = ".$deal_info['user_id']);
			$money = floatval($deal_info['borrow_amount']);
			$objectaccno = $deal_info['objectaccno'];
			$accno = $user_info['accno']; */
			$Publics = new Publics();
			$map['reqHeader'] = $Publics->reqheader("T00004");
		    $map['inBody']['contractList'] = array();//此列表放款、债券转受让时必填
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			$map['inBody']['accountList'] = $data['accountList'];//资金账务处理列表
            $map['inBody']['objectId'] = strval($data['deal_id']);//标的id
			$map['inBody']['note'] = "";//备注
			$dep = $Publics->sign($map);//签名
			$sign_str = $dep['aa'];
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
			$map['inBody']['objectId'] = $Publics->encrypt($map['inBody']['objectId']);
            foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));
			if($type=='T05'){
				foreach($data['deal_repay_info'] as $k=>$v){
					$data['seqno'] = $SeqNo;
					$data['user_id'] = $v['user_id'];
					$data['objectaccNo'] = $data['objectaccno'];
					$data['accNo'] = $v['accno'];
					$data['money'] = $v['repay_money'];
					$data['form_con'] = json_encode($map);
					$data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
					$data['type'] =$type;
					$data['add_time'] =time();
					$data['date_time'] =date("Y-m-d H:i:s");
					if($result['respHeader']['respCode']=="P2P0000"){
						$data['status'] =1;
						$data['suc_time']=time();
					} 
					$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
				}
				
			}else{
				$data['seqno'] = $SeqNo;
				$data['form_con'] = json_encode($map);
				$data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
				$data['type'] =$type;
				if($result['respHeader']['respCode']=="P2P0000"){
					$data['status'] =1;
					$data['suc_time']=date("Ymd");
				}
				if($type=='T08'){
					$data['jctaccNo'] = 'JCTPR20170630';
					$data['money'] =-$data['money'];
					$data['create_time'] =time();
					$data['types'] =$type;
					$GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$data,"INSERT");
					
				}elseif($type=='T09'){
					$data['create_time'] =time();
					$data['jctaccNo'] = $data['accountList']['cebitAccountNo'];
					$data['types'] =$type;
					$GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$data,"INSERT");
				}elseif($type=='T10'){
					$data['create_time'] =time();
					$data['jctaccNo'] = 'JCTPM20170630';
					$data['types'] =$type;
					$GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$data,"INSERT");
				}
				$data['add_time'] =time();
				$data['date_time'] =date("Y-m-d H:i:s");
				$data['callback_con'] =$sign_str;
				$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			}
			return $result;
		}
		//募集期收益
		public function earning_money($SeqNo,$type,$list){
			$SeqNo = strim($SeqNo);
			$type = strim($type);
			$oderNo=0;
			foreach($list as $key=>$value){
				$oderNo++;
				//$user_info = $GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id = ".$list[$key]['user_id']);
				$repay['oderNo'] = $oderNo;
				$repay['debitAccountNo']="JCTPM20170630";
				$repay['cebitAccountNo']=$value['accno'];
				$repay['currency']="CNY";
				if($value['increase_interest']>0){//奖励收益
					$repay['amount']=floatval($value['increase_interest']);
				}
				if($value['raise_money']>0){//募集期收益
					$repay['amount']=floatval($value['raise_money']);
				}
				if($value['experience_money']>0){//体验金收益
					$repay['amount']=floatval($value['experience_money']);
				}
				if($value['interestrate_money']>0){//加息卡收益
					$repay['amount']=floatval($value['interestrate_money']);
				}
				$repay['otherAmounttype']="";
				$repay['otherAmount']="";
				$repay['summaryCode']="T10";
				$repays[]=$repay;
			}
			$Publics = new Publics();
			if($type!='T10'){
				return false;
			}
            $map['reqHeader'] = $Publics->reqheader("T00004");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			$map['inBody']['accountList'] = $repays;//资金账务处理列表
            $map['inBody']['objectId'] = "";//标的id
			$map['inBody']['contractList'] = array();//资金账务处理列表
			$map['inBody']['note'] = "";//备注
			//print_r($userId);die;
            //$dep = $Publics->sign($map);//签名
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));
			foreach($list as $key=>$value){
				$data['seqno'] = $map['inBody']['businessSeqNo'];
				$data['user_id'] = $value['user_id'];
				$data['accNo'] = $value['accno'];
				$data['jctaccNo'] = 'JCTPM20170626';
				$data['form_con'] = json_encode($map);
				$data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
				if($value['increase_interest']>0){//奖励收益
					$data['money']=floatval(-$value['increase_interest']);
				}
				if($value['raise_money']>0){//募集期收益
					$data['money']=floatval(-$value['raise_money']);
				}
				if($value['experience_money']>0){//体验金收益
					$data['money']=floatval(-$value['experience_money']);
				}
				if($value['interestrate_money']>0){//加息卡收益
					$data['money']=floatval(-$value['interestrate_money']);
				}
				$data['type'] = $type;
				$data['types'] = $type;
				$data['add_time'] =time();
				$data['date_time'] =date("Y-m-d H:i:s");
				if($result['respHeader']['respCode']=="P2P0000"){
					$data['status'] =1;
					$data['suc_time']=date("Ymd");
				}
				$data['create_time'] =time();
				$GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$data,"INSERT");
				$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			}
			
			return $result;
			
		}
		//网站代还款
		public function exchange_repay($seqno,$type,$repay_id){
			$SeqNo = strim($seqno);
			$type = strim($type);
			$repay_id = intval($repay_id);
			$repay_info = $GLOBALS['db']->getRow("select dr.deal_id,d.user_id,dr.repay_money,d.objectaccno from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.id = ".$repay_id);
			//$deal_info = $GLOBALS['db']->getRow("select objectaccno from ".DB_PREFIX."deal where id = ".$repay_info['deal_id']);
			$user_info =$GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id = ".$repay_info['user_id']);
			$Publics = new Publics();
			$deal_id = $repay_info['deal_id'];
			$money = floatval($repay_info['repay_money']);
			$map['reqHeader'] = $Publics->reqheader("ZJTB01");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			if($type=='T09'){
				$map['inBody']['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>$user_info['accno'],"cebitAccountNo"=>'JCTPR20170630',"currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T09"));//资金账务处理列表
            	$map['inBody']['objectId'] = "$deal_id";//标的id
			}else{
				return false;
			}
			$map['inBody']['contractList'] = array();//资金账务处理列表
			$map['inBody']['note'] = "";//备注
			//print_r($userId);die;
            //$dep = $Publics->sign($map);//签名
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
             foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));
			$data['seqno'] = $SeqNo;
			$data['user_id'] = $repay_info['user_id'];
			$data['objectaccNo'] = $repay_info['objectaccno'];
			$data['accNo'] = $user_info['accno'];
			$data['money'] = $money;
			$data['form_con'] = json_encode($map);
			$data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
			$data['type'] =$type;
			$data['add_time'] =time();
			$data['date_time'] =date("Y-m-d H:i:s");
			if($result['respHeader']['respCode']=="P2P0000"){
				$data['status'] =1;
				$data['suc_time']=date("Ymd");
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			return $result;
		}
		//体验金收益
		public function experience_repay($seqno,$type,$data){
			$SeqNo = strim($seqno);
			$type = strim($type);
			$Publics = new Publics();
			$map['reqHeader'] = $Publics->reqheader("ZJTB01");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			$map['inBody']['accountList'] = $data['accountList'];//资金账务处理列表
            $map['inBody']['objectId'] = $data['deal_id'];//标的id
			$map['inBody']['contractList'] = array();//资金账务处理列表
			$map['inBody']['note'] = "";//备注
			//print_r($userId);die;
            //$dep = $Publics->sign($map);//签名
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));
			foreach($data['experience'] as $key=>$value){
				$datas['seqno'] = $value['seqno'];
				$datas['user_id'] = $value['user_id'];
				$datas['objectaccNo'] = $value['objectaccno'];
				$datas['jctaccNo'] = $value['jctaccNo'];
				$datas['accNo'] = $value['accno'];
				$datas['money'] = -$value['money'];
				$datas['form_con'] = json_encode($map);
				$datas['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
				$datas['type'] =$type;
				$datas['types'] =$type;
				$datas['add_time'] =time();
				$datas['date_time'] =date("Y-m-d H:i:s");
				if($result['respHeader']['respCode']=="P2P0000"){
					$datas['status'] =1;
					$datas['suc_time']=date("Ymd");
				}
			$GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$datas,"INSERT");
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$datas,"INSERT");
			}
			return $result;
		}
		//营销代发
		public function market_money($SeqNo,$type,$list){
			$SeqNo = strim($SeqNo);
			$type = strim($type);
			$Publics = new Publics();
			if($type!='T10'){
				return false;
			}
            $map['reqHeader'] = $Publics->reqheader("T00004");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			$map['inBody']['accountList'] = $list['accountList'];//资金账务处理列表
            $map['inBody']['objectId'] = "";//标的id
			$map['inBody']['contractList'] = array();//资金账务处理列表
			$map['inBody']['note'] = "";//备注
			//print_r($userId);die;
            //$dep = $Publics->sign($map);//签名
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
             foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans(json_encode($map));
			return $result;
			
		}
		public function new_deal($SeqNo,$type,$money,$deal_id,$userId,$is_auto=false){
			$SeqNo = strim($SeqNo);
			$type = strim($type);
			$money = floatval($money);
			$userId = intval($userId);
			$deal_id = intval($deal_id);
			$user_info = $GLOBALS['db']->getRow("select accno,real_name,idno,mobile from ".DB_PREFIX."user where id = ".$userId);
			$deal_info = $GLOBALS['db']->getRow("select user_id,objectaccno,old_deal_id,plan_id from ".DB_PREFIX."deal where id = ".$deal_id);
			$objectaccNo = $deal_info['objectaccno'];
			$accNo = $user_info['accno'];
            $debts_user=$GLOBALS['db']->getOne("select accno from ".DB_PREFIX."user where id = ".$deal_info['user_id']);  // 转让方台账账户
			$Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("T00004");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
			$map['inBody']['entrustflag'] = "00";//委托标识-已委托
			//$map['inBody']['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>"$objectaccNo","cebitAccountNo"=>"1123539","currency"=>"CNY","amount"=>$money,"summaryCode"=>$type));//资金账务处理列表
			$map['inBody']['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>"1123447","cebitAccountNo"=>"1123539","currency"=>"CNY","amount"=>$money,"summaryCode"=>$type));//资金账务处理列表
			//$map['inBody']['summaryCode'] = "";//摘要码   与资金账务处理列表中业务操作类型相同、T12：其他费用收取
			$identifier = 'JCT_1123539_1123447_'.$objectaccNo.'_'.date("Ymd");//合同文件名
            $map['inBody']['contractList'] = array(array('oderNo'=>"1",'contractType'=>"01",'contractRole'=>"01",'contractFileNm'=>$identifier,'debitUserid'=>'1123447','cebitUserid'=>"1123539"));//此列表放款、债券转受让时必填
            /* $map['inBody']['contractType'] = "";//    01 投资人合同
            $map['inBody']['contractRole'] = "";//角色   01投资人
            $map['inBody']['contractFileNm'] = "";//合同名称命名规则：平台代码_借方汇元编号_贷方会员编号_标的编号_交易日期
            $map['inBody']['debitUserid'] = "";//角色为投资人、转让/受让人时必填
            $map['inBody']['cebitUserid'] = "";//角色为融资人、转让/受让人时必填 */
            $map['inBody']['objectId'] = strval($deal_id);//标的id
			$map['inBody']['note'] = "";//备注
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            $deps = $Publics->encrypt(json_encode($map));
            $DepSdk = new DepSdk();
            $result=$DepSdk->newfundTrans($deps);

            // 资金同步交易信息入库
			$res['seqno'] = $map['inBody']['businessSeqNo'];
			$res['user_id'] = $userId;
			$res['accNo'] = $accNo;
            $res['objectaccNo'] = $result['outBody']['objectaccNo'];
			$res['money'] = $money;
            $res['form_con'] = json_encode($map);
            $res['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $res['type'] = $type;
			$res['add_time'] =time();
			$res['date_time'] =date("Y-m-d H:i:s");
			if($result['respHeader']['respCode']=="P2P0000"){
				$res['status'] =1;
				$res['suc_time'] =time();
			} 
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$res,"INSERT");
            return $result;
		}
		public function repay_withdraw($seqno,$type,$data){
			$SeqNo = strim($seqno);
			$type = strim($type);
			$Publics = new Publics();
			$map['reqHeader'] = $Publics->reqheader("T00005");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
			$map['inBody']['accountList'] = $data['accountList'];//资金账务处理列表
            $map['inBody']['objectId'] = $data['deal_id'];//标的id
			$map['inBody']['contractList'] = $data['contractList'];
			$map['inBody']['bankAccountNo'] = $data['bankAccountNo'];
			$map['inBody']['note'] = "";//备注
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
			$map['inBody']['objectId'] = $Publics->encrypt($map['inBody']['objectId']);
			$map['inBody']['bankAccountNo'] = $Publics->encrypt($map['inBody']['bankAccountNo']);
			$DepSdk = new DepSdk();
		    $result=$DepSdk->newfundTrans(json_encode($map));
			$data['seqno'] = $SeqNo;
			$data['user_id'] = $data['accNo'];
			$data['form_con'] = json_encode($map);
			$data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
			$data['type'] =$type;
			$data['add_time'] =time();
			$data['date_time'] =date("Y-m-d H:i:s");
			$data['create_time'] =time();
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			return $result;
		}
		//信息查询接口
		public function transaction($data=array()){
			$Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("C00002");
            $map['inBody']['checkType'] = strval($data['type']);//用户信息查询
            $map['inBody']['customerId'] =strval($data['accno']);//会员编号
            $map['inBody']['accountNo'] = strval($data['objectaccNo']);//台帐帐号
            $map['inBody']['beginDate'] = strval($data['start_time']);//开始日期
            $map['inBody']['endDate'] = strval($data['end_time']);//结束日期
            $map['inBody']['beginPage'] = "";//起始页码
            $map['inBody']['endPage'] = "";//截止页码
            $map['inBody']['showNum'] = "10";//每页显示条数
            $map['inBody']['note'] = "";//备注
            $dep = $Publics->sign($map);
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
			$map['inBody']['accountNo'] = $Publics->encrypt(strval($data['objectaccNo']));
			$map['inBody']['customerId'] = $Publics->encrypt(strval($data['customerId']));
            $DepSdk = new DepSdk();
            $result=$DepSdk->dataQuery(json_encode($map));
			$data['form_con'] = json_encode($map);
			$result['outBody']['customerId'] = $Publics->encrypt($result['outBody']['customerId']);
			$result['outBody']['phoneNo'] = $Publics->encrypt($result['outBody']['phoneNo']);
			$result['outBody']['accountNo'] = $Publics->encrypt($result['outBody']['accountNo']);
			$result['outBody']['secBankaccNo'] = $Publics->encrypt($result['outBody']['secBankaccNo']);
			$result['outBody']['tiedAccno'] = $Publics->encrypt($result['outBody']['tiedAccno']);
			$data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
			$data['add_time'] =time();
			$data['date_time'] =date("Y-m-d H:i:s");
			$data['create_time'] =time();
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			return $result;
		}
		//资金冲正
		public function rush_positive($OldSeqNo,$userId,$money,$jctaccNo){
		    $Publics = new Publics();
		    $map['reqHeader'] = $Publics->reqheader("T00004");
		    $map['inBody']['businessSeqNo'] = $Publics->seqno();//业务流水号
		    $map['inBody']['busiTradeType'] = "T11";//业务操作类型
		    $map['inBody']['entrustflag'] = "00";//委托标识-已委托
		    //$map['inBody']['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>"$objectaccNo","cebitAccountNo"=>"1123539","currency"=>"CNY","amount"=>$money,"summaryCode"=>$type));//资金账务处理列表
		    $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>$userId,"cebitAccountNo"=>$jctaccNo,"currency"=>"CNY","amount"=>$money,"summaryCode"=>"T11"));//资金账务处理列表
		    //$map['inBody']['contractList'] = array(array('oderNo'=>"",'contractType'=>"",'contractRole'=>"",'contractFileNm'=>"",'debitUserid'=>'','cebitUserid'=>""));//此列表放款、债券转受让时必填
			$map['inBody']['contractList'] = array();
		    $map['inBody']['objectId'] = "";//标的id
		    $map['inBody']['note'] = "";//备注
		    $dep = $Publics->sign($map);//签名
		    
		    $map['reqHeader']['signTime'] = $dep['signTime'];
		    $map['reqHeader']['signature'] = $dep['signature'];
		    foreach($map['inBody']['accountList'] as $k=>$v){
				if($v['cebitAccountNo']){
					$map['inBody']['accountList'][$k]['cebitAccountNo'] = $Publics->encrypt($v['cebitAccountNo']);
				}
				if($v['debitAccountNo']){
					$map['inBody']['accountList'][$k]['debitAccountNo'] = $Publics->encrypt($v['debitAccountNo']);
				}
			}
		    $DepSdk = new DepSdk();
		    $result=$DepSdk->newfundTrans(json_encode($map));
		
		    // 资金同步交易信息入库
		    $res['seqno'] = $map['inBody']['businessSeqNo'];
		    $res['user_id'] = $userId;
		    $res['accNo'] = $userId;
		    $res['objectaccNo'] = $result['outBody']['objectaccNo'] =$Publics->decrypt($result['outBody']['objectaccNo']);
		    $res['money'] = $money;
		    $res['form_con'] = json_encode($map);
		    $res['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
		    $res['type'] = "T11";
		    $res['add_time'] =time();
		    $res['date_time'] =date("Y-m-d H:i:s");
		    if($result['respHeader']['respCode']=="P2P0000"){
		        $res['status'] =1;
		        $res['suc_time'] =time();
		    }
		    $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$res,"INSERT");
		    return $result;
		}
		
	}