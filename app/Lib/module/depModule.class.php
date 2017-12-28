<?php
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
require_once APP_ROOT_PATH."system/libs/user.php";

class depModule extends SiteBaseModule
{
    //绑卡异步通知
    public function bank_back($arr){
        /* $con = file_get_contents("php://input");
        $arr = json_decode($con,true); */
        $seqno = $arr['inBody']['oldbusinessSeqNo'];
		$Publics = new Publics();
		$map['reqHeader'] = $Publics->reqheader("U00001");
       // $id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."decository where seqno='".$seqno."' order by id desc limit 1");
        if($arr['inBody']['respCode'] =="P2P0000") {
			if($arr['type']=="B02"){
				$id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user_bank where bankcard='".$arr['bankcard']."' order by id desc limit 1");
				$GLOBALS['db']->query("update ".DB_PREFIX."user_bank set status=0 where id =$id and bankcard='".$arr['bankcard']."' and cunguan_tag=1");
				
			} 
            $data['callback_con'] = json_encode($arr,JSON_UNESCAPED_UNICODE);
            $data['notice_sn'] = $arr['inBody']['businessSeqNo'];
            $data['status'] = 1;
            $data['suc_time'] = time();
            //$Publics = new Publics();
            $data['secBankaccNo'] = $Publics->decrypt($arr['inBody']['secBankaccNo']);
            $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=".$arr['id']."");
			
        }
		$map['reqHeader']['respCode'] =$arr['inBody']['respCode'];
		$map['reqHeader']['respMsg'] =$arr['inBody']['respMsg'];
		$map['reqHeader']['signTime'] = time();
		$map['reqHeader']['signature'] = "";
		echo json_encode($map);exit;
    }
	public function async_back(){
		$con = file_get_contents("php://input");
        $arr = json_decode($con,true);
        $orderid=$arr['inBody']['oldbusinessSeqNo'];
        $Query = $GLOBALS['db']->getRow("SELECT seqno,id,user_id,objectaccNo,money,type,bankcard FROM ".DB_PREFIX."decository where seqno='".$orderid."' and status=0 order by id desc limit 1");
		if($Query['type']=='R01' || $Query['type']=='R02' || $Query['type']=='R09' || $Query['type']=='RR02'){//存管代扣充值异步通知
			$Publics = new Publics();
			$map['reqHeader'] = $Publics->reqheader("T00001");
            if($Query['type']=='R01' || $Query['type']=='R02' || $Query['type']=='RR02'){
				
                $payment = $GLOBALS['db']->getRow("SELECT id,user_id,money,is_paid,outer_notice_sn,cunguan_tag,bank_id,seqno FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=2");
            }else{
                $payment = $GLOBALS['db']->getRow("SELECT id,user_id,money,is_paid,outer_notice_sn,cunguan_tag,bank_id,seqno FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=0");
            }
            $user_id=$payment['user_id'];
            if($payment){
                $data['callback_con'] = $con;
                $data['notice_sn']  = $arr['inBody']['businessSeqNo'];
                $data['suc_time'] = time();   
                if($arr['inBody']['respCode'] =="P2P0000"){
                    $GLOBALS['db']->startTrans();//开始事务
                    $data['status'] =1;       
                    $whether_success= $GLOBALS['db']->getRow("SELECT user_id,money,outer_notice_sn,cunguan_tag,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=1");
                    if($whether_success['is_paid'] ==1){ 
                        $GLOBALS['db']->rollback();
                        exit;
                    }
                  
                    $cunguan_tag =1;
                    $trans_data = array(
                        'is_paid' => 1,
                        'notice_sn' => $arr['inBody']['businessSeqNo'],
                        'memo' => $con,
                        'outer_notice_sn' =>$payment['outer_notice_sn']."-充值成功",
                        'pay_time' =>time(),
                        'pay_date' => date("Y-m-d H:i:s"),
                    );

                    $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $payment['id'] . " and seqno='".$payment['seqno']."'");
                    if($recharge){    
                        // 查询是否首次充值
                       /*  $is_first_charge = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_money_log where type=1 and cunguan_tag=1 and user_id=".$user_id);
                        // 充值成功  奖励成长值
                        //1， 是否首次充值
                        if($is_first_charge == 0){
                            require_once APP_ROOT_PATH."system/user_level/Level.php";
                            $level=new Level();
                            $level->get_grow_point(8,'',$user_id);
                        }else{
                            //2,  再次充值
                            require_once APP_ROOT_PATH."system/user_level/Level.php";
                            $level=new Level();
                            $r = $level->get_grow_point(9,$payment['money'],$user_id);

                            $a['msg1'] = $r;
                            $a['msg2'] = $payment['money'];
                            $GLOBALS['db']->autoExecute("mjn_msg",$a,"INSERT");


                            
                        } */
                        $sql = "update ".DB_PREFIX."user set cunguan_money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($payment['money'],2).",'".AES_DECRYPT_KEY."') where id =".$user_id;
                        $chmod=$GLOBALS['db']->query($sql);//更新账户钱
                        if($chmod){
                            $storage = $GLOBALS['db']->getRow("SELECT user_id,money,outer_notice_sn,cunguan_tag,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=1");                                 
                            $result= $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as cunguan_money FROM ".DB_PREFIX."user WHERE id=".$user_id);                                              
                            
                            $create_time = $GLOBALS['db']->getOne("select create_time  from ".DB_PREFIX."user_money_log where user_id =$user_id FOR UPDATE");
                            $money_log_info = array();
                            $money_log_info['memo'] = $storage['outer_notice_sn'];
                            $money_log_info['brief'] = "充值成功";
                            $money_log_info['money'] = round($storage['money'],2);
                            $money_log_info['account_money'] = $result;
                            $money_log_info['user_id'] = $user_id;
                            $money_log_info['create_time'] = TIME_UTC;
                            $money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
                            $money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
                            $money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
                            $money_log_info['type'] = 1;
                            $money_log_info['cunguan_tag'] = $cunguan_tag;
                            $details=$GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
                            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . " and status=0");
                            if($res&&$details){
                                $GLOBALS['db']->commit(); 
                                // 给用户发送短信通知
                                if(app_conf("SMS_ON")==1)
                                {
                                    $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                                    $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_CHARGE_SUCCESS'");
                                    $notice['user_name'] = $user_info['user_name'];
                                    $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                                    $notice['site_name'] = app_conf("SHOP_TITLE");
                                    $notice['recharge_money'] = round($storage['money'],2);
                                    $GLOBALS['tmpl']->assign("notice",$notice);
                                    $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                                    $msg_data['dest'] = $user_info['mobile'];
                                    $msg_data['send_type'] = 0;
                                    $msg_data['title'] = "充值成功短信通知";
                                    $msg_data['content'] = addslashes($msg);
                                    $msg_data['send_time'] = 0;
                                    $msg_data['is_send'] = 0;
                                    $msg_data['create_time'] = TIME_UTC;
                                    $msg_data['user_id'] = $user_id;
                                    $msg_data['is_html'] = 0;                                
                                    send_sms_email($msg_data);
                                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                                }
                            }else{ 
                                 $GLOBALS['db']->rollback();
                            }  
                        }else{
                            $GLOBALS['db']->rollback();
                        }
                    }else{
                        $GLOBALS['db']->rollback();
                    }                   
                }else{
                    $trans_failed = array('is_paid' =>0,);
                    $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_failed, "UPDATE", "id=" . $payment['id'] . "");
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . "");
									
                }
			}
			$map['reqHeader']['respCode'] =$arr['inBody']['respCode'];
			$map['reqHeader']['respMsg'] =$arr['inBody']['respMsg'];
			$map['reqHeader']['signTime'] = time();
			$map['reqHeader']['signature'] = "";
			echo json_encode($map);exit;
        }elseif($Query['type'] == "R03"||$Query['type'] == "R04"||$Query['type'] == "R05"||$Query['type'] == "R06"||$Query['type'] == "W02"||$Query['type'] == "W03"||$Query['type'] == "W04"||$Query['type'] == "W05"){
			$Publics = new Publics();
			$map['reqHeader'] = $Publics->reqheader("T00001");
            $jct_money_log = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."jct_money_log where seqno='".$Query['seqno']."' and types='".$Query['type']."'");
            if($jct_money_log){
                $data['callback_con'] = $con;
                $data['notice_sn']  = $arr['inBody']['businessSeqNo'];
                $data['handle_con'] = $arr['inBody']['respMsg'];                
                $data['suc_time'] = date("Ymd");
                if($arr['inBody']['respCode'] =="P2P0000"){                    
                    $data['status'] =1;
                }else{
                    $data['status'] =2;
                }
                $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . "");
                $GLOBALS['db']->autoExecute(DB_PREFIX . "jct_money_log", $data, "UPDATE", "id=" . $jct_money_log . "");
			}
			$map['reqHeader']['respCode'] =$arr['inBody']['respCode'];
			$map['reqHeader']['respMsg'] =$arr['inBody']['respMsg'];
			$map['reqHeader']['signTime'] = time();
			$map['reqHeader']['signature'] = "";
			echo json_encode($map);exit;
        }elseif($Query['type'] == "W01" || $Query['type'] == "W06"){//提现异步通知
            require_once APP_ROOT_PATH."/system/libs/user.php";
            $vo = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_carry where (seqno='".$orderid."' and cunguan_tag=1 and cunguan_pwd=1) or (seqno='".$orderid."' and user_type=2 and cunguan_tag=1)");
            $user_id = $vo['user_id'];
            if($arr['inBody']['respCode'] =="P2P0000"){
                if($vo['status'] == 5){
                    $info['status'] = 1;
                    
                    modify_account(array("cunguan_lock_money"=>-$vo['money']),$vo['user_id'],"存管提现成功",8,"存管提现成功",1);                 
       
//                     if($vo['fee'] > 0){
//                         modify_account(array("withdraw_fee"=>-$vo['fee']),$vo['user_id'],"存管提现成功-手续费",9,"存管提现成功-手续费",1);
//                     }
//                         modify_account(array("cunguan_lock_money"=>-$vo['fee']),$vo['user_id'],"存管提现成功-手续费",9,"存管提现成功-手续费",1);

                    $user_cg_money = get_user_info("*","id=".$user_id);

                    $memo = $vo['fee'] ? "提现成功,-手续费：".$vo['fee']."元，实际到账：".($vo['money']-$vo['fee'])."元" : "存管提现成功";
                    $money_log_info['memo'] = $memo;
                    $money_log_info['brief'] = '存管提现成功';
                    $money_log_info['money'] = round(-$vo['money'],2);
                    $money_log_info['account_money'] = $user_cg_money['cunguan_money'];
                    $money_log_info['user_id'] = $user_id;
                    $money_log_info['create_time'] = TIME_UTC;
                    $money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
                    $money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
                    $money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
                    $money_log_info['type'] = 8;
                    $money_log_info['cunguan_tag'] = 1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);

                    $group_arr = array(0,$user_id);
                    sort($group_arr);
                    $group_arr[] =  6;

                    $sh_notice['time'] = to_date($vo['create_time'],"Y年m月d日 H:i:s");        //提现时间
                    $sh_notice['money'] = format_price($vo['money']);                       // 提现金额
                    $GLOBALS['tmpl']->assign("sh_notice",$sh_notice);
                    $tmpl_sz_failed_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_WITHDRAWS_SUCCESS'",false);
                    $sh_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_sz_failed_content['content']);

                    $msg_data['content'] = $sh_content;
                    $msg_data['to_user_id'] = $user_id;
                    $msg_data['create_time'] = TIME_UTC;
                    $msg_data['type'] = 0;
                    $msg_data['group_key'] = implode("_",$group_arr);
                    $msg_data['is_notice'] = 6;
                    $msg_data['title'] = "您的存管提现资金已成功到账，请留意查看";

                    $GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
                    $id = $GLOBALS['db']->insert_id();
                    $GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);

                    if($vo['user_type'] !="2"){
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and cunguan_tag=1 and cunguan_pwd=1");
                    }else{ 
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and cunguan_tag=1 and user_type=2");
                    }
                    
                    /*  //扣除成长值
                    require_once APP_ROOT_PATH."system/user_level/Level.php";
                    $level=new Level();
                    $level->get_grow_point(20,$money_log_info['money']); */
                
                    //提现成功短信通知
                    if(app_conf("SMS_ON")==1)
                    {
                        $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                        $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_CASH_DIVIDENDS_SUCCESS'");
                        $notice['user_name'] = $user_info['user_name'];
                        $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                        $notice['site_name'] = app_conf("SHOP_TITLE");
                        //$notice['recharge_money'] = round($storage['money'],2);
                        $notice['recharge_money'] = round($money_log_info['money'],2);
                        $GLOBALS['tmpl']->assign("notice",$notice);
                        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = "提现成功短信通知";
                        $msg_data['content'] = addslashes($msg);
                        $msg_data['send_time'] = time();
                        $msg_data['is_send'] = 0;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $user_id;
                        $msg_data['is_html'] = 0;
                        //send_lbsms_email($msg_data);
                        send_sms_email($msg_data);
                        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                               
                    }
                    
					
				}
            }else{
                if($vo['status'] == 5){
                    $info['status'] = 2;

             
                    modify_account(array("cunguan_money"=>$vo['money'],"cunguan_lock_money"=>-$vo['money']),$vo['user_id'],"存管提现打款失败",8,"存管提现打款失败",1);
                
                   
                    //modify_account(array("cunguan_money"=>$vo['fee'],"cunguan_lock_money"=>-$vo['fee']),$vo['user_id'],"存管提现打款失败-手续费",9,'存管提现打款失败-手续费',1);

                    $group_arr = array(0,$user_id);
                    sort($group_arr);
                    $group_arr[] =  7;

                    $sh_notice['time'] = to_date($vo['create_time'],"Y年m月d日 H:i:s");        //提现时间
                    $sh_notice['money'] = format_price($vo['money']);                       // 提现金额
                    $sh_notice['msg'] = '存管打款失败';                           // 驳回原因
                    $GLOBALS['tmpl']->assign("sh_notice",$sh_notice);
                    $tmpl_sz_failed_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_WITHDRAWS_FAILED'",false);
                    $sh_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_sz_failed_content['content']);

                    $msg_data['content'] = $sh_content;
                    $msg_data['to_user_id'] = $user_id;
                    $msg_data['create_time'] = TIME_UTC;
                    $msg_data['type'] = 0;
                    $msg_data['group_key'] = implode("_",$group_arr);
                    $msg_data['is_notice'] = 7;
                    $msg_data['title'] = "对不起，您的存管提现打款失败";

                    $GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
                    $id = $GLOBALS['db']->insert_id();
                    $GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
                    

                    if($vo['user_type'] !="2"){ 
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and cunguan_tag=1 and cunguan_pwd=1");
                    }else{ 
                         $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and user_type=2 and cunguan_tag=1");
                    }
                   
                
                    //提现失败短信通知
                    if(app_conf("SMS_ON")==1)
                    {
                        $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                        $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_WITHDRAWALS_UNSUCCESSFUL'");
                        $notice['user_name'] = $user_info['user_name'];
                        $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                        $notice['site_name'] = app_conf("SHOP_TITLE");
                        $notice['recharge_money'] = round($storage['money'],2);
                        $GLOBALS['tmpl']->assign("notice",$notice);
                        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = "提现失败短信通知";
                        $msg_data['content'] = addslashes($msg);
                        $msg_data['send_time'] = time();
                        $msg_data['is_send'] = 0;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $user_id;
                        $msg_data['is_html'] = 0;
                        send_lbsms_email($msg_data);
                        //send_sms_email($msg_data);
                        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                               
                    }
                
                }
            }
            $data['callback_con'] = json_encode($arr,JSON_UNESCAPED_UNICODE) ;
            $data['notice_sn'] = $arr['inBody']['businessSeqNo'];
            $data['status'] = $arr['inBody']['dealStatus'];
            $data['suc_time'] = time();
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . "");
			$Publics = new Publics();
			$maps['reqHeader'] = $Publics->reqheader("T00001");
			$maps['reqHeader']['respCode'] =$arr['inBody']['respCode'];
			$maps['reqHeader']['respMsg'] =$arr['inBody']['respMsg'];
			$maps['reqHeader']['signTime'] = time();
			$maps['reqHeader']['signature'] = "";
			echo json_encode($maps);exit;
        }elseif($Query['type']=="J01"){
			$arr = array_merge($arr,$Query);
			$this->pwd_back($arr);
		}elseif($Query['type']=="T14"||$Query['type']=="T15"){
			$arr = array_merge($arr,$Query);
			$this->repay_withdraw($arr);
		}elseif($Query['type']=="B01"||$Query['type']=="B02"||$Query['type']=="B03"){
			$arr = array_merge($arr,$Query);
			$this->bank_back($arr);
		}
	}
    //充值提现异步通知
    /*public function charge_back(){
        $con = file_get_contents("php://input");
        $arr = json_decode($con,true);
        $orderid=$arr['inBody']['oldbusinessSeqNo'];
        $Query = $GLOBALS['db']->getRow("SELECT seqno,id,type  FROM ".DB_PREFIX."decository where seqno='".$orderid."' and status=0 order by id desc limit 1");
        
        if($Query['type']=='R01' || $Query['type']=='R02' || $Query['type']=='R09' || $Query['type']=='RR02'){//存管代扣充值异步通知
            if($Query['type']=='R01' || $Query['type']=='RR02'){
                $payment = $GLOBALS['db']->getRow("SELECT id,user_id,money,is_paid,outer_notice_sn,cunguan_tag,bank_id,seqno FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=2");
            }else{
                $payment = $GLOBALS['db']->getRow("SELECT id,user_id,money,is_paid,outer_notice_sn,cunguan_tag,bank_id,seqno FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=0");
            }
            $user_id=$payment['user_id'];
            if($payment){
                $data['callback_con'] = $con;
                $data['notice_sn']  = $arr['inBody']['businessSeqNo'];
                $data['suc_time'] = time();   
                if($arr['inBody']['respCode'] =="P2P0000"){
                    $GLOBALS['db']->startTrans();//开始事务  
                    $data['status'] =1;       
                    $whether_success= $GLOBALS['db']->getRow("SELECT user_id,money,outer_notice_sn,cunguan_tag,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=1");
                    if($whether_success['is_paid'] ==1){ 
                        $GLOBALS['db']->rollback();
                        exit;
                    }
                  
                    $cunguan_tag =1;
                    $trans_data = array(
                        'is_paid' => 1,
                        'notice_sn' => $arr['inBody']['businessSeqNo'],
                        'memo' => $con,
                        'outer_notice_sn' =>$payment['outer_notice_sn']."-充值成功",
                        'pay_time' =>time(),
                        'pay_date' => date("Y-m-d H:i:s"),
                    );
                    $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $payment['id'] . " and seqno='".$payment['seqno']."'");
                    if($recharge){             
                        $sql = "update ".DB_PREFIX."user set cunguan_money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($payment['money'],2).",'".AES_DECRYPT_KEY."') where id =".$user_id;
                        $chmod=$GLOBALS['db']->query($sql);//更新账户钱
                        if($chmod){
                            $storage = $GLOBALS['db']->getRow("SELECT user_id,money,outer_notice_sn,cunguan_tag,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$Query['seqno']."' and cunguan_tag=1 and is_paid=1");                                 
                            $result= $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as cunguan_money FROM ".DB_PREFIX."user WHERE id=".$user_id);                                              
                            
                            $create_time = $GLOBALS['db']->getOne("select create_time  from ".DB_PREFIX."user_money_log where user_id =$user_id FOR UPDATE");
                            $money_log_info = array();
                            $money_log_info['memo'] = $storage['outer_notice_sn'];
                            $money_log_info['brief'] = "充值成功";
                            $money_log_info['money'] = round($storage['money'],2);
                            $money_log_info['account_money'] = $result;
                            $money_log_info['user_id'] = $user_id;
                            $money_log_info['create_time'] = TIME_UTC;
                            $money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
                            $money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
                            $money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
                            $money_log_info['type'] = 1;
                            $money_log_info['cunguan_tag'] = $cunguan_tag;
                            $details=$GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
                            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . " and status=0");
                            if($res&&$details){
                                $GLOBALS['db']->commit(); 
                                // 给用户发送短信通知
                                if(app_conf("SMS_ON")==1)
                                {
                                    $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                                    $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_CHARGE_SUCCESS'");
                                    $notice['user_name'] = $user_info['user_name'];
                                    $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                                    $notice['site_name'] = app_conf("SHOP_TITLE");
                                    $notice['recharge_money'] = round($storage['money'],2);
                                    $GLOBALS['tmpl']->assign("notice",$notice);
                                    $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                                    $msg_data['dest'] = $user_info['mobile'];
                                    $msg_data['send_type'] = 0;
                                    $msg_data['title'] = "充值成功短信通知";
                                    $msg_data['content'] = addslashes($msg);
                                    $msg_data['send_time'] = 0;
                                    $msg_data['is_send'] = 0;
                                    $msg_data['create_time'] = TIME_UTC;
                                    $msg_data['user_id'] = $user_id;
                                    $msg_data['is_html'] = 0;                                
                                    send_sms_email($msg_data);
                                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                                }
                            }else{ 
                                 $GLOBALS['db']->rollback();
                            }  
                        }else{
                            $GLOBALS['db']->rollback();
                        }
                    }else{
                        $GLOBALS['db']->rollback();
                    }                   
                }else{
                    $trans_failed = array('is_paid' =>0,);
                    $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_failed, "UPDATE", "id=" . $payment['id'] . "");
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . "");  
                }
            }
        }elseif($Query['type'] == "R03"||$Query['type'] == "R04"||$Query['type'] == "R05"||$Query['type'] == "R06"||$Query['type'] == "W02"||$Query['type'] == "W03"||$Query['type'] == "W04"||$Query['type'] == "W05"){
            $jct_money_log = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."jct_money_log where seqno='".$Query['seqno']."' and types='".$Query['type']."'");
            if($jct_money_log){
                $data['callback_con'] = $con;
                $data['notice_sn']  = $arr['inBody']['businessSeqNo'];
                $data['handle_con'] = $arr['inBody']['respMsg'];                
                $data['suc_time'] = date("Ymd");
                if($arr['inBody']['respCode'] =="P2P0000"){                    
                    $data['status'] =1;
                }else{
                    $data['status'] =2;
                }
                $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . "");
                $GLOBALS['db']->autoExecute(DB_PREFIX . "jct_money_log", $data, "UPDATE", "id=" . $jct_money_log . "");
            }
        }elseif($Query['type'] == "W01" || $Query['type'] == "W06"){//提现异步通知
            require_once APP_ROOT_PATH."/system/libs/user.php";
            $vo = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_carry where seqno='".$orderid."' and cunguan_tag=1 and cunguan_pwd=1 or seqno='".$orderid."' and user_type=2 and cunguan_tag=1");
            $user_id = $vo['user_id'];
            if($arr['inBody']['respCode'] =="P2P0000"){
                if($vo['status'] == 5){
                    $info['status'] = 1;
                    
                    modify_account(array("cunguan_lock_money"=>-$vo['money']),$vo['user_id'],"存管提现成功",8,"存管提现成功",1);                 
       
//                     if($vo['fee'] > 0){
//                         modify_account(array("withdraw_fee"=>-$vo['fee']),$vo['user_id'],"存管提现成功-手续费",9,"存管提现成功-手续费",1);
//                     }
//                         modify_account(array("cunguan_lock_money"=>-$vo['fee']),$vo['user_id'],"存管提现成功-手续费",9,"存管提现成功-手续费",1);

                    $user_cg_money = get_user_info("*","id=".$user_id);

                    $memo = $vo['fee'] ? "提现成功,-手续费：".$vo['fee']."元，实际到账：".($vo['money']-$vo['fee'])."元" : "存管提现成功";
                    $money_log_info['memo'] = $memo;
                    $money_log_info['brief'] = '存管提现成功';
                    $money_log_info['money'] = round(-$vo['money'],2);
                    $money_log_info['account_money'] = $user_cg_money['cunguan_money'];
                    $money_log_info['user_id'] = $user_id;
                    $money_log_info['create_time'] = TIME_UTC;
                    $money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
                    $money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
                    $money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
                    $money_log_info['type'] = 8;
                    $money_log_info['cunguan_tag'] = 1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);

                    $group_arr = array(0,$user_id);
                    sort($group_arr);
                    $group_arr[] =  6;

                    $sh_notice['time'] = to_date($vo['create_time'],"Y年m月d日 H:i:s");        //提现时间
                    $sh_notice['money'] = format_price($vo['money']);                       // 提现金额
                    $GLOBALS['tmpl']->assign("sh_notice",$sh_notice);
                    $tmpl_sz_failed_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_WITHDRAWS_SUCCESS'",false);
                    $sh_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_sz_failed_content['content']);

                    $msg_data['content'] = $sh_content;
                    $msg_data['to_user_id'] = $user_id;
                    $msg_data['create_time'] = TIME_UTC;
                    $msg_data['type'] = 0;
                    $msg_data['group_key'] = implode("_",$group_arr);
                    $msg_data['is_notice'] = 6;
                    $msg_data['title'] = "您的存管提现资金已成功到账，请留意查看";

                    $GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
                    $id = $GLOBALS['db']->insert_id();
                    $GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);

                    if($vo['user_type'] !="2"){
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and cunguan_tag=1 and cunguan_pwd=1");
                    }else{ 
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and cunguan_tag=1 and user_type=2");
                    }
                    
                    //扣除成长值
                    require_once APP_ROOT_PATH."system/user_level/Level.php";
                    $level=new Level();
                    $level->get_grow_point(20,$money_log_info['money']);
                
                    //提现成功短信通知
                    if(app_conf("SMS_ON")==1)
                    {
                        $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                        $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_CASH_DIVIDENDS_SUCCESS'");
                        $notice['user_name'] = $user_info['user_name'];
                        $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                        $notice['site_name'] = app_conf("SHOP_TITLE");
                        $notice['recharge_money'] = round($storage['money'],2);
                        $GLOBALS['tmpl']->assign("notice",$notice);
                        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = "提现成功短信通知";
                        $msg_data['content'] = addslashes($msg);
                        $msg_data['send_time'] = time();
                        $msg_data['is_send'] = 0;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $user_id;
                        $msg_data['is_html'] = 0;
                        //send_lbsms_email($msg_data);
                        send_sms_email($msg_data);
                        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                               
                    }
                    
                    
                }
            }else{
                if($vo['status'] == 5){
                    $info['status'] = 2;

             
                    modify_account(array("cunguan_money"=>$vo['money'],"cunguan_lock_money"=>-$vo['money']),$vo['user_id'],"存管提现打款失败",8,"存管提现打款失败",1);
                
                   
                    //modify_account(array("cunguan_money"=>$vo['fee'],"cunguan_lock_money"=>-$vo['fee']),$vo['user_id'],"存管提现打款失败-手续费",9,'存管提现打款失败-手续费',1);

                    $group_arr = array(0,$user_id);
                    sort($group_arr);
                    $group_arr[] =  7;

                    $sh_notice['time'] = to_date($vo['create_time'],"Y年m月d日 H:i:s");        //提现时间
                    $sh_notice['money'] = format_price($vo['money']);                       // 提现金额
                    $sh_notice['msg'] = '存管打款失败';                           // 驳回原因
                    $GLOBALS['tmpl']->assign("sh_notice",$sh_notice);
                    $tmpl_sz_failed_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_WITHDRAWS_FAILED'",false);
                    $sh_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_sz_failed_content['content']);

                    $msg_data['content'] = $sh_content;
                    $msg_data['to_user_id'] = $user_id;
                    $msg_data['create_time'] = TIME_UTC;
                    $msg_data['type'] = 0;
                    $msg_data['group_key'] = implode("_",$group_arr);
                    $msg_data['is_notice'] = 7;
                    $msg_data['title'] = "对不起，您的存管提现打款失败";

                    $GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
                    $id = $GLOBALS['db']->insert_id();
                    $GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
                    

                    if($vo['user_type'] !="2"){ 
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and cunguan_tag=1 and cunguan_pwd=1");
                    }else{ 
                         $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $info, "UPDATE", "id=" . $vo['id'] . " and user_type=2 and cunguan_tag=1");
                    }
                   
                
                    //提现失败短信通知
                    if(app_conf("SMS_ON")==1)
                    {
                        $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                        $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_WITHDRAWALS_UNSUCCESSFUL'");
                        $notice['user_name'] = $user_info['user_name'];
                        $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                        $notice['site_name'] = app_conf("SHOP_TITLE");
                        $notice['recharge_money'] = round($storage['money'],2);
                        $GLOBALS['tmpl']->assign("notice",$notice);
                        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = "提现失败短信通知";
                        $msg_data['content'] = addslashes($msg);
                        $msg_data['send_time'] = time();
                        $msg_data['is_send'] = 0;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $user_id;
                        $msg_data['is_html'] = 0;
                        send_lbsms_email($msg_data);
                        //send_sms_email($msg_data);
                        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                               
                    }
                
                }
            }
            $data['callback_con'] = $con;
            $data['notice_sn'] = $arr['inBody']['businessSeqNo'];
            $data['status'] = $arr['inBody']['dealStatus'];
            $data['suc_time'] = time();
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=" . $Query['id'] . "");
        }
    }*/
    /*function logs(){
        $curlobj = curl_init();
        curl_setopt($curlobj,CURLOPT_URL,"ftp://".gethostbyname('p2.heepay.com')."/yb_files_down/hyzf_JCT_OUTPAYTRADECHECK_20170624.txt");
        curl_setopt($curlobj,CURLOPT_HEADER,0);
        curl_setopt($curlobj,CURLOPT_RETURNTRANSFER,0);
        //time out after 300s
        curl_setopt($curlobj,CURLOPT_TIMEOUT,500);
        //通过这个函数设置ftp的用户名和密码,没设置就不需要!
        curl_setopt($curlobj,CURLOPT_USERPWD,"tpds:tpds123456");
        //sets up the output file
        $path = APP_ROOT_PATH."new/cunguan_log/ab.txt";
        $outfile = fopen($path,'wb');  //保存到本地文件的文件名
        curl_setopt($curlobj,CURLOPT_FILE,$outfile);
        $rtn = curl_exec($curlobj);
        fclose($outfile);
        if(!curl_errno($curlobj)){
            $a = file_get_contents($path);
            echo $a;
            @unlink($path);
        }else{
            echo 'Curl:error: '.curl_errno($curlobj);
        }
        curl_close($curlobj);
    }*/
    function chaxun_ceshi(){
        $userId = $_REQUEST['userid'];
        $Publics = new Publics();
        $maps['reqHeader'] = $Publics->reqheader("SXCX01");
        $maps['inBody']['checkType'] = '01';//用户信息查询
        $maps['inBody']['customerId'] = $userId;//会员编号
        $maps['inBody']['accountNo'] = '';//台帐帐号
        $maps['inBody']['beginDate'] = '';//开始日期
        $maps['inBody']['endDate'] = '';//结束日期
        $maps['inBody']['beginPage'] = "";//起始页码
        $maps['inBody']['endPage'] = "";//截止页码
        $maps['inBody']['showNum'] = "10";//每页显示条数
        $maps['inBody']['note'] = "";//备注
        $depss = $Publics->sign($maps);
        $maps['reqHeader']['signTime'] = $depss['signTime'];
        $maps['reqHeader']['signature'] = $depss['signature'];
        $depsss = $Publics->encrypt(json_encode($maps));
        $DepSdk = new DepSdk();
        $result11=$DepSdk->dataQuery($depsss);
        echo "<pre>";
        var_dump($result11);
    }
    public function do_repay(){
        $id = $_REQUEST['id'];
        $is_last = $_REQUEST['is_last'];
        if(!$id){
            showErr('请稍后重试！',0,url("index","deal"));
        }
        $arr = explode ( ',', $id );
       foreach($arr as $key=>$value){
        
        $sqlarr = $GLOBALS['db']->getRow("select dlr.id,dlr.self_money,u.id as user_id,dlr.virtual_info,dlr.repay_money,d.objectaccno,u.accno,d.id as deal_id,dlr.repay_id as load_repay_id,dlr.load_id from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."deal_repay dr on dr.id=dlr.repay_id left join ".DB_PREFIX."deal d on d.id = dr.deal_id left join ".DB_PREFIX."user u on u.id =dlr.user_id where dlr.id = ".$value." and dr.has_repay in(1,3) and dlr.has_repay=0 and dr.cunguan_tag=1 ");
        $repay1['oderNo'] = 1;
        $repay1['oldbusinessSeqNo']="";
        $repay1['oldOderNo']="";
        $repay1['debitAccountNo']=$sqlarr['objectaccno'];
        $repay1['cebitAccountNo']=$sqlarr['accno'];
        $repay1['currency']="CNY";
        $repay1['amount']=abs(floatval($sqlarr['repay_money']));
        $repay1['otherAmounttype']="";
        $repay1['otherAmount']="";
        $repays[]=$repay1;
        $deal_id = $sqlarr['deal_id'];
        if($repays){
        $s[]=$sqlarr;
        $pub = new Publics();
        $seqno = $pub ->seqno();
        $deal = new Deal();
        $data1['accountList'] = $repays;
        $data1['deal_repay_info'] = $s;
        $data1['deal_id'] = (string)$deal_id;
        $res1 = $deal ->do_repay($seqno,'T05',$data1);//出款
        $res1_code =$res1['respHeader']['respCode'];
        if($res1_code=='P2P0000'){
                if($sqlarr['self_money']>0){ //还本还息才需要解冻投资资金
                    $datas['cunguan_lock_money'] = -$sqlarr['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
                }
                $datas['cunguan_money']=$sqlarr['repay_money'];
                //资金增加++++++++++++++++++++++
                $datas['create_time'] = time(); //还款时间
                $datas['brief'] = $sqlarr['virtual_info']; //虚拟货币消息
                $datas['deal_id'] = $sqlarr['deal_id'];
                $datas['load_repay_id'] = $sqlarr['load_repay_id'];
                $datas['load_id'] = $sqlarr['load_id'];
                $msg = $sqlarr['self_money']>0?"还本还息":"还息"; 
                modify_account($datas, $sqlarr['user_id'], $msg, 5,$msg,1);
                //添加资金记录
                $statusArray['calculate_status'] = 1;
                $statusArray['has_repay'] = 1; //设置已还款标志
                $statusArray['true_repay_time'] = strtotime(date('Y-m-d'));
                $statusArray['true_repay_date'] = date('Y-m-d', time());
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$statusArray,"UPDATE","id=".$value);
                }
            }
                
        }
        var_dump($res1);die;
    }
    // 验密
    public function password_check(){
        require_once APP_ROOT_PATH."system/utils/Depository/Public.php";
        $publics=new Publics();
        $load_seqno=$publics->seqno;
        $html =  $publics ->verify_trans_password('dep','lmsb',$GLOBALS['user_info']['id'],4,$load_seqno,"_self");
        echo $html;
    }
    // T07
    public function lmsb(){
        $seqno=$_REQUEST['businessSeqNo'];
        require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
        $deal=new Deal;
        $status=$deal->deal_temp($seqno,'T07',$total_money,469229,$GLOBALS['user_info']['id']);

    }
    public function market_money(){
        $id = $_REQUEST['id'];
        if(!$id){
            showErr('请稍后重试！',0,url("index","deal"));
        }
        $arr = explode ( ',', $id );
        foreach($arr as $key=>$value){
            $sqlarr = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."decository where id=".$value." and status=0 and market_type in(1,2,3)");
            if(!$sqlarr){
                exit;
            }
            $repay1['oderNo'] = 1;
            $repay1['oldbusinessSeqNo']="";
            $repay1['oldOderNo']="";
            $repay1['debitAccountNo']="";
            $repay1['cebitAccountNo']=$sqlarr['accNo'];
            $repay1['currency']="CNY";
            $repay1['amount']=abs(floatval($sqlarr['money']));
            $repay1['otherAmounttype']="";
            $repay1['otherAmount']="";
            $repays[]=$repay1;
            $deal_id = $sqlarr['deal_id'];
            if($repays){
                $pub = new Publics();
                $seqno = $pub ->seqno();
                $deal = new Deal();
                $data1['accountList'] = $repays;
                $data1['deal_id'] = (string)$deal_id;
                $data1['id'] = (string)$sqlarr['id'];
                $res1 = $deal ->market_money($seqno,'T10',$data1);//出款
                if($res1['respHeader']['respCode']=="P2P0000"){
                    
                    if($sqlarr['market_type']==2){
                        modify_account(array('cunguan_money'=>abs($sqlarr['money'])), $sqlarr['user_id'], "加息卡收益", 58, "加息卡收益",1);
                    }elseif($sqlarr['market_type']==1){
                        modify_account(array('cunguan_money'=>abs($sqlarr['money'])), $sqlarr['user_id'], "募集期收益", 58, "募集期收益",1);
                    }elseif($sqlarr['market_type']==3){
                        modify_account(array('cunguan_money'=>abs($sqlarr['money'])), $sqlarr['user_id'], "标的奖励收益", 58, "标的奖励收益",1);
                    }
                    $datas['seqno'] = $seqno;
                    $datas['user_id'] = $sqlarr['user_id'];
                    $datas['money'] = $sqlarr['money'];
                    $datas['types'] ='T10';
                    $datas['status'] =1;
                    $datas['create_time'] =time();
                    $datas['suc_time'] =date("Ymd");
                    $GLOBALS['db']->autoExecute(DB_PREFIX."jct_money_log",$datas,"INSERT");
                    $statusArray['status']=1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$statusArray,"UPDATE","id=".$value);
                }
                var_dump($seqno);die;
            }
        }
    }
    //设密异步通知
    public function pwd_back($arr){
        /* $con = file_get_contents("php://input");
        $arr = json_decode($con,true); */
        $seqno = $arr['inBody']['oldbusinessSeqNo'];
        //$list = $GLOBALS['db']->getRow("SELECT id,user_id FROM ".DB_PREFIX."decository where seqno='".$seqno."' order by id desc limit 1");
		$Publics = new Publics();
		$arr['inBody']['secBankaccNo'] = $Publics->decrypt($arr['inBody']['secBankaccNo']);
        if($arr['inBody']['respCode'] =="P2P0000") {
            $data['callback_con'] = json_encode($arr,JSON_UNESCAPED_UNICODE);
            $data['notice_sn'] = $arr['inBody']['businessSeqNo'];
            $data['status'] = 1;
            $data['suc_time'] = time();
            $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "UPDATE", "id=".$arr['id']."");
            $map['cunguan_pwd'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $map, "UPDATE", "id=".$arr['user_id']."");
        }
		
		$maps['reqHeader'] = $Publics->reqheader("T00001");
		$maps['reqHeader']['respCode'] =$arr['inBody']['respCode'];
		$maps['reqHeader']['respMsg'] =$arr['inBody']['respMsg'];
		$maps['reqHeader']['signTime'] = time();
		$maps['reqHeader']['signature'] = "";
		echo json_encode($maps);exit;
    }
    public function pwd_call_back(){
		var_dump($_REQUEST);die;
        $Publics = new Publics();
        $back = $_GET;
        $Publics->call_back($back);
    }
	//绑卡
	public function bind_bank(){
		$pub =new Publics();
		$seqno = $pub->seqno();
		$data['user_id'] = 1123447;
        $data['seqno'] = $seqno;
        $data['add_time'] = TIME_UTC;
        $data['date_time'] = date("Y-m-d H:i:s");
		if(isMobile()){
			$url = "https://36.110.98.254:19001/p2ph5/standard/cardBind2.html";
		}else{
			$url = "https://36.110.98.254:19001/p2ph5/pc/cardBind2.html";
		}
		
		
        //$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
        $backurl = urlencode("https://" . $_SERVER['HTTP_HOST'] . "/dep/pwd_call_back"); 
        list($msec, $sec) = explode(' ', microtime());
        $signtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $signature = $signtime."|".$data['user_id'];
        $data_content = $pub->rsa_encrypt($signature); //RSA加密
		$urls = "?systemCode=JCT&userId=".$data['user_id']."&backURL=".$backurl."&signTime=".$signtime."&signature=".$data_content."&businessSeqNo=".$seqno."&channelname=玖财通";
		$s="&cardNo=6217000010023785510&idCardNo=421102199207080836&cardPhoneNumber=15136295459&userName=尴尬&channelname=玖财通&userNameType=1&idCardNoType=1&cardNoType=1&cardPhoneNumberType=1";
        $url = $url.$urls;
		app_redirect($url);
	}
	//开户
	public function kaihu(){
			 $Publics = new Publics();
			$type="U01";
            $map['reqHeader'] = $Publics->reqheader("U00001");
            $map['inBody']['customerId'] ="1123919";//会员编号
            if($type=='B01' || $type =='B02' || $type=='B03'){
                $map['inBody']['businessSeqNo'] =  $user_msg['businessSeqNo'];//业务流水号
            }else{
                $map['inBody']['businessSeqNo'] =  $Publics->seqno();//业务流水号
            }

            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['ctype'] = "00";//会员类型---00：个人   01：企业
            $map['inBody']['crole'] = "00";//会员角色---00：投资方  01：融资方  02：担保方  09：全部
            $map['inBody']['username'] = "哈哈";//用户名
            $map['inBody']['certType'] = "00";//证件类型---身份证
            $map['inBody']['certNo'] = "421102199207080836";//证件号码
            $map['inBody']['certFImage'] = "";//身份证正面影像
            $map['inBody']['certBImage'] = "";//身份证反面影像
            $map['inBody']['certInfo'] = "";//身份证详情
            $map['inBody']['idvalidDate'] = '';//身份证有效起始日期
            $map['inBody']['idexpiryDate'] = '';//身份证有效截止日期
            $map['inBody']['jobType'] =  '';//职业类型---自由职业
            $map['inBody']['job'] =  '';//职业描述---自由职业
            $map['inBody']['postcode'] = '';//邮编
            $map['inBody']['address'] = '';//地址
            $map['inBody']['national'] = '';//民族
            $map['inBody']['completeFlag'] = "0";//身份证信息完整标识---完整
            $map['inBody']['phoneNo'] = "15136295444";//手机号
            $map['inBody']['companyName'] = "";//企业名称   会员类型为企业时必填
            $map['inBody']['uniSocCreCode'] = "";//统一社会信用代码
            $map['inBody']['uniSocCreDir'] = "";//统一社会信用地址
			if($type=='U01'||$type=='U02'){
				$map['inBody']['bindFlag'] = "01";//绑卡标识--注册
                $map['inBody']['bindType'] = "";//绑定类型
                $map['inBody']['acctype'] = "";//卡帐标识-银行卡
                $map['inBody']['oldbankAccountNo'] = "";//原绑定银行卡号
                $map['inBody']['bankAccountNo'] = "";//银行卡号
                $map['inBody']['bankAccountName'] = "";//银行账户名称
                $map['inBody']['bankAccountTelNo'] = "";//银行手机号
			}
			if($type=='B01' || $type=='B02' || $type=='B03'){
				$map['inBody']['bindFlag'] = "00";//绑卡标识--绑卡
				$map['inBody']['bindType'] = "99";//绑定类型
				$map['inBody']['acctype'] = "00";//卡帐标识-银行卡
                //$map['inBody']['accNo'] = $user_msg['dep_account']; //开户账号
				$map['inBody']['oldbankAccountNo'] = $user_msg['oldbankcard'];//原绑定银行卡号
				$map['inBody']['bankAccountNo'] = $user_msg['bankcard'];//银行卡号
				$map['inBody']['bankAccountName'] = $user_msg['real_name'];//银行账户名称
				$map['inBody']['bankAccountTelNo'] = $user_msg['bank_mobile'];//银行手机号
			}
            $map['inBody']['note'] = "";//备注
            $map['inBody']['bizLicDomicile'] = "";//营业执照住所
            $map['inBody']['entType'] = "";//主体类型
            $map['inBody']['dateOfEst'] = "";//成立日期
            $map['inBody']['corpacc'] = "";//对公户账号
            $map['inBody']['corpAccBankNo'] = "";//对公户开户行行号
            $map['inBody']['corpAccBankNm'] = "";//对公户开户行名称
            $dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
           /*  $map['inBody']['busiLiceNo'] = "";//营业执照编号
            $map['inBody']['busiLiceDir'] = "";//营业执照存放地址
            $map['inBody']['orgCodeNo'] = "";//组织机构代码
            $map['inBody']['orgCodeDir'] = "";//组织机构存放地址
            $map['inBody']['taxRegisNo'] = "";//税务登记号
            $map['inBody']['taxRegisDir'] = "";//税务登记号地址 */
			
			foreach($map['inBody'] as $k=>$v){
				if($k=="certNo"){
					$map['inBody'][$k] = $Publics->encrypt($map['inBody'][$k]);
					
				}elseif($k=="username"){
					$map['inBody'][$k] = $Publics->encrypt($map['inBody'][$k]);
				}elseif($k=="phoneNo"){
					$map['inBody'][$k] = $Publics->encrypt($map['inBody'][$k]);
				}elseif($k=="customerId"){
					$map['inBody'][$k] = $Publics->encrypt($map['inBody'][$k]);
				}
			}  
			/*  $a =$Publics->encrypt('15136295444');
			var_dump($a);die;  */
			$url = "https://36.110.98.254:19002/p2pwg/JCT";  
			$da=json_encode($map);
			//var_dump(json_encode($map));die;
            $ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($map));
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_TIMEOUT,40);//单位S 秒
			$datas = curl_exec ( $ch );
			curl_close ( $ch );
			$data['a']=$datas;
			$data['dat']=$da;
			$data['da']=$dep['aa'];
			var_dump($data);die;
        }
	
    public function up_repay(){
        $publics = new Publics();
        $deals = new Deal();
        $deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id=7122 and user_id=1123447");
        if($deal){
                $seqno2 = $publics ->seqno();
                $res1 = $deals -> save_deal(7122,$seqno2,"06",$list);//修改标的状态为还款中
                $res_code = $res1['respHeader']['respCode'];
                if($res_code=="P2P0000"){
                    $deal_data['cunguan_status']="06";
                    //$deal_data['deal_status']=4;
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=7122");
                    //modify_account(array("cunguan_money"=>0,"deal_id"=>8199),1151865,"存管标的放款成功",3,"存管标的放款",1);
                }else{
                    var_dump($res1);
                }
            
        }else{
            echo "失败";
        }
        
    }
    //撤销误操作标第
    public function deal_del(){
        $deal_id = intval($_REQUEST['deal_id']);
        $data = $GLOBALS['db']->getRow("select id,name,borrow_amount,rate,user_id from ".DB_PREFIX."deal where id=".$deal_id);
        $Deal = new Deal();
        $res = $Deal -> deals('P03','05',$data);//修改标的状态为还款中
        if($res['respHeader']['respCode']=="P2P0000"){
            $deal_data['is_effect'] = 0;
            $deal_data['is_delect'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal_id);
            echo "处理成功";
        }else{
            echo "处理失败,错误码：".$res['respHeader']['respCode'];
        }
        /*
        echo "<pre>";
        var_dump($res);
        die;
        */
    }
    public function up_deal(){
        $deal_id = intval($_REQUEST['deal_id']);
        $status = intval($_REQUEST['status']);
        $data = $GLOBALS['db']->getRow("select id,name,borrow_amount,rate,user_id from ".DB_PREFIX."deal where id=".$deal_id);
        $Deal = new Deal();
        $res = $Deal -> deals('P04',$status,$data);//修改标的状态为还款中
        if($res['respHeader']['respCode']=="P2P0000"){
            $deal_data['cunguan_status'] = $status;
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal_id);
            echo "处理成功";
        }else{
            echo "处理失败,错误码：".$res['respHeader']['respMsg'];
        }
        /*
        echo "<pre>";
        var_dump($res);
        die;
        */
    }
    
    function change_check_pwd()
    {
        $user = $GLOBALS['user_info'];
		$ctl = strim($_GET['m']);
		$act = strim($_GET['v']);
		if(!$ctl||!$act){
			echo "错误";
		   die;
		}
        if ($user['id'] > 0) {
            $Publics = new Publics();
            $seqno = $Publics->seqno();
            $re = $Publics->verify_trans_password($ctl, $act, $user['id'], '4', $seqno,'_self');
            echo $re;die;
        }else{
            app_redirect(url('index','user#login'));
        }

    }
    function change_bank_index(){
        $user = $GLOBALS['user_info'];
        if($user['id']>0){
            $seqno = strim($_GET['businessSeqNo']);
            $GLOBALS['tmpl']->assign("seqno",$seqno);
            $vo['real_name'] = $user['real_name'];
            $vo['idno'] = $user['idno'];
            $GLOBALS['tmpl']->assign('vo',$vo);
            $banks = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."bank where bankid != ''");
            $GLOBALS['tmpl']->assign("banks",$banks);
            $GLOBALS['tmpl']->assign("cate_title","更换银行卡");
            $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_change_bank.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }else{
            app_redirect(url('index','user#login'));
        }
    }
     //更换银行卡
    function change_bank()
    {

        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {
            if (!$user['accno']) {
                $root['status'] = 0;
                $root['info'] = '请先开户';
                ajax_return($root);
            }

            $bank_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user_bank where user_id = " . $user['id']." and status=1 and cunguan_tag=1");
            if(empty($bank_info['bankcard'])){
                $root['status'] = 0;
                $root['info'] = '请先绑卡';
                ajax_return($root);
            }

            $validateCode = strim($_REQUEST['validateCode']);
            $sms_code = strim($_REQUEST['sms_code']);
            if($validateCode){
                $sms_code = $validateCode;
            }
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($_REQUEST["mobile"])."' AND verify_code='".$sms_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
                $json['status'] = 0;
                $json['info'] = "短信验证码出错或已过期";
                ajax_return($json);
            }
            $user_msg['user_id'] = $user['id'];
            $user_msg['real_name'] = $user['real_name'];;
            $user_msg['idno'] = $user['idno'];
            $user_msg['mobile'] = $user['mobile'];
            $user_msg['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
            $user_msg['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
            $user_msg['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
            $user_msg['businessSeqNo'] = strim($_REQUEST['businessSeqNo']); //流水号
            $user_msg['oldbankcard'] = $bank_info['bankcard'];
            $user_msg['dep_account'] = $user['accno'];

            $Register = new Register();
            $result = $Register->register1($user_msg, 'B03');
            $data['seqno'] = strim($_REQUEST['businessSeqNo']);
            $data['user_id'] = $user['id'];
            $data['accNo'] = $user['accno'];
            $data['secBankaccNo'] = $result['res']['outBody']['secBankaccNo'];
            $data['form_con'] = json_encode($result['map']);
            $data['back_con'] = json_encode($result['res']);
            $data['type'] = "B03";
            $data['add_time'] = TIME_UTC;
            $data['date_time'] = date('Y-m-d H:i:s');

            $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT");
            if ($result['res']['respHeader']['respCode'] == 'P2P0000') {
                $GLOBALS['db']->query("update ".DB_PREFIX."user_bank set status=0 where id=".$bank_info['id']);
                $bank['user_id'] = $user['id'];
                $bank['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
                $bank['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
                $bank['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
                $bank['real_name'] = $user['real_name'];
                $bank['create_time'] = TIME_UTC;
                $bank['addip'] = get_client_ip();
                $bank['status'] = 1;
                $bank['cunguan_tag'] = 1;
                $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank", $bank, "INSERT");
                $root['status'] = 1;
                $root['jump'] = url('index','uc_money#bank');
                $root['info'] = '换卡成功';
                ajax_return($root);
            } else {
                $root['status'] = 0;
                $root['info'] = $result['res']['respHeader']['respMsg'];
                ajax_return($root);
            }
        } else {
            $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root);
        }
    }
   public function transfer_deal(){
	   $user = $GLOBALS['user_info'];
	   if(!$user){
		   echo "错误";
		   die;
	   }
	   $SeqNo = $_REQUEST['businessSeqNo'];
	   /* $user_id = intval($_REQUEST['user_id']);
	   $deal_id = intval($_REQUEST['deal_id']);
	   if(!$seqno ||!$user_id || !$deal_id){
		   echo "错误";
		   die;
	   } */
	   $de = new Deal();
	   $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("ZJTB01");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['busiTradeType'] = "T07";//业务操作类型
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
			$map['inBody']['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>"1123447","cebitAccountNo"=>"1123485","currency"=>"CNY","amount"=>21999,"otherAmounttype"=>"","otherAmount"=>""),array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>"1123447","cebitAccountNo"=>"1123485","currency"=>"CNY","amount"=>21999,"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['objectId'] = "8521";//标的id
			$map['inBody']['note'] = "";//备注
			//print_r($userId);die;
            //$dep = $Publics->sign($map);//签名
			$dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
			$deps = $Publics->encrypt(json_encode($map));
            $DepSdk = new DepSdk();
            $result=$DepSdk->fundTrans($deps);
	   var_dump($result);die;
   }
   public function delegate(){
	   $user = $GLOBALS['user_info'];
	   if(!$user){
		   echo "错误";
		   die;
	   }
	   $SeqNo = $_REQUEST['businessSeqNo'];
	   $reg = new Register();
	   $no = strval(rand(100,9999));
	   $res = $reg -> delegate($SeqNo,$user['id'],'B04','T01', $no);
	   var_dump($res);die;
   }
   public function new_deal(){
	   $user = $GLOBALS['user_info'];
	   if(!$user){
		   echo "错误";
		   die;
	   }
	   $pub = new Publics();
	   $SeqNo = $pub->seqno();
	   $reg = new Deal();
	   $no = strval(rand(100,9999));
	   $res = $reg -> new_deal($SeqNo,'T07',900,7921,1123539);
	   var_dump($res);die;
   }
   public function new_charge(){
			$Publics = new Publics();
			$SeqNo = $_GET['businessSeqNo'];
            $map['reqHeader'] = $Publics->reqheader("KPCZ01");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['businessOrderNo'] = "";//订单流水号
            $map['inBody']['rType'] = 'R01';//类型---R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
            $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>"1123447","currency"=>"CNY","amount"=>1000,"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['payType'] = "00";//支付方式--00:银行支付渠道 01：第三方支付渠道
            $map['inBody']['busiBranchNo'] = "";//支付公司代码
            $map['inBody']['bankAccountNo'] = "6212261715001814245";//银行卡号
            $map['inBody']['secBankaccNo'] = "6230799990000120417";//二类户账户
            $map['inBody']['note'] = "";//备注
            $dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            $map['inBody']['ownerName'] = "骆猛";//持卡人姓名
            $map['inBody']['ownerCertNo'] = "412825199303090518";//持卡人身份证号
            $map['inBody']['ownerMobile'] = "15136295459";//持卡人手机号
            $map['inBody']['bankId'] = "102";//银行ID
            $map['inBody']['bankName'] = "中国工商银行";//银行名称
            $map['inBody']['cardType'] = "SAVING";//卡类型
            $map['inBody']['identifycode'] = "";//短信验证码
            $deps = $Publics->encrypt(json_encode($map));
            $DepSdk = new DepSdk();
            $result=$DepSdk->charge($deps);
			$data['seqno'] = $SeqNo;
			$data['form_con'] = json_encode($map);
			$data['back_con'] = json_encode($result);
			$data['callback_con'] = $result['respHeader']['respMsg'];
			$data['type'] = 'R01';
			$data['money'] = 1000;
			$data['add_time'] = time();
			$data['date_time'] = date("Y-m-d H:i:s");
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			var_dump($result); 
		
   }
   //放款提现异步通知
	public function repay_withdraw($arr){		
		/* $con = file_get_contents("php://input");
        $arr = json_decode($con,true); 
		if(!$arr){
			echo "错误";
		} */ 
		$seqno=$arr['inBody']['oldbusinessSeqNo'];
		$statusArr['callback_con'] = $con;
		$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$statusArr,"UPDATE","seqno='".$seqno."'");
		$Query = $arr;
		/* $Query = $GLOBALS['db']->getRow("SELECT seqno,id,user_id,objectaccNo,money,type  FROM ".DB_PREFIX."decository where seqno='".$seqno."' order by id desc limit 1"); */
		if($Query['type']=='T15'){
			$this->do_loans($arr);
			
		}elseif($Query['type']=='T14'){
			//$Query = $GLOBALS['db']->getRow("SELECT seqno,id,user_id,objectaccNo,money,type  FROM ".DB_PREFIX."decository where seqno='".$seqno."' order by id desc limit 1");
			if($arr['inBody']['respCode']=="P2P0000"){			
				$GLOBALS['db']->query("update ".DB_PREFIX."decository set status=1,suc_time=".time()."  where id=".$Query['id']);
				$deal_id = explode("O",$Query['objectaccNo']);
				modify_account(array("cunguan_money"=>$Query['money'],"deal_id"=>$deal_id[0]),$Query['user_id'],"存管标的放款成功",3,"存管标的放款",1);
				modify_account(array("cunguan_money"=>-$Query['money']),$Query['user_id'],"存管提现成功",8,"存管提现成功",1); 
				$deal = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'deal where id='.$deal_id[0]);
				$deal['deal_status'] = $loan_data['deal_status'] = 4;
				$deal['is_has_loans'] = $loan_data['is_has_loans'] = 1;
				$deal['repay_start_time'] = TIME_UTC;
				$loan_data['repay_start_date'] = to_date(TIME_UTC,"Y-m-d");
				$loan_data['repay_start_time'] = TIME_UTC;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$loan_data,"UPDATE","id=".$deal_id[0]." AND is_has_loans=0 ");
				$loantype = intval($deal['loantype']);
				$LoanModule = $this->LoadLoanModule($loantype);
				$list = $LoanModule->make_repay_plan($deal);
				$total_money = array();
				foreach($list as $i=>$load_repay){
					if($old_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id[0]." AND repay_time=".$load_repay['repay_time']."  ") ){
					$repay_id = $old_info['id'];
					if($old_info['has_repay']==0){
						$load_repay['l_key'] = $i;
						$load_repay['status'] = 0;
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$load_repay,"UPDATE","deal_id=".$deal['id']." AND repay_time=".$load_repay['repay_time']."");
					}else{
						unset($load_repay['self_money']);
						unset($load_repay['repay_money']);
						unset($load_repay['has_repay']);
						unset($load_repay['manage_money']);
						unset($load_repay['manage_money_rebate']);
						$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."deal_repay SET l_key='".$i."' WHERE deal_id=".$deal['id']." AND repay_time=".$load_repay['repay_day']."");
							}

					}else{
							$load_repay['l_key'] = $i;
							$load_repay['status'] = 0;
							$load_repay['has_repay'] = 0;
							$load_repay['cunguan_tag'] = $deal['cunguan_tag'];
							$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$load_repay,"INSERT");
							$repay_id = $GLOBALS['db']->insert_id();
						}
							$this->make_user_repay_plan($deal,$i,$load_repay['repay_time'],$old_info['true_repay_time'],$repay_id,$total_money);
					}
					$deals = new Deal();
					$publics = new Publics();
					$res = $deals -> deals("P04","03",$deal);//修改标的状态为放款
					$res_code = $res['respHeader']['respCode'];
					if($res_code=="P2P0000"){
						$deal_data['cunguan_status']="03";
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal['id']);
						$seqno2 = $publics ->seqno();
						$res1 = $deals -> deals("P04","06",$deal);//修改标的状态为还款中
						$res_code = $res1['respHeader']['respCode'];
						if($res_code=="P2P0000"){
							$deal_data['cunguan_status']="06";
							$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal['id']);
						}
					} 
					$GLOBALS['db']->query("UPDATE ".DB_PREFIX."plan SET deal_status=4,is_has_loans=1,repay_start_time=".time().",update_time=".time()." WHERE id=".$deal['plan_id']);
					echo "成功";
			}else{
				echo "失败";
			}
		}else{
			echo "失败";
		}
	}
	
	/**
 * 生成还款计划和回款计划
 */
function make_repay_plan($deal){
	$loantype = intval($deal['loantype']);
	$LoanModule = $this->LoadLoanModule($loantype);
	$list = $LoanModule->make_repay_plan($deal);
	$total_money = array();
	foreach($list as $i=>$load_repay){
		if($old_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." AND repay_time=".$load_repay['repay_time']."  ") ){
			$repay_id = $old_info['id'];
			if($old_info['has_repay']==0){
				$load_repay['l_key'] = $i;
				$load_repay['status'] = 0;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$load_repay,"UPDATE","deal_id=".$deal['id']." AND repay_time=".$load_repay['repay_time']."");
			}
			else{
				unset($load_repay['self_money']);
				unset($load_repay['repay_money']);
				unset($load_repay['has_repay']);
				unset($load_repay['manage_money']);
				unset($load_repay['manage_money_rebate']);
				$GLOBALS['db']->query("UPDATE FROM ".DB_PREFIX."deal_repay SET l_key='".$i."' WHERE deal_id=".$deal['id']." AND repay_time=".$load_repay['repay_day']."");
			}

		}else{
			$load_repay['l_key'] = $i;
			$load_repay['status'] = 0;
			$load_repay['has_repay'] = 0;
			$load_repay['cunguan_tag'] = $deal['cunguan_tag'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$load_repay,"INSERT");
			$repay_id = $GLOBALS['db']->insert_id();
		}
		make_user_repay_plan($deal,$i,$load_repay['repay_time'],$old_info['true_repay_time'],$repay_id,$total_money);
	}
	return true;

}

/**
 * 生成投标者的回款计划
 *
 * 生成回款计划请注意募集其收益------1.5版本在还本是返还用户
 */
function make_user_repay_plan($deal,$idx,$repay_day,$true_time,$repay_id,&$total_money){
	static $fload_users;
	if(!isset($fload_users[$deal['id']])){
		$fload_users[$deal['id']] = $GLOBALS['db']->getAll("SELECT dl.id,dl.deal_id,dl.user_id,dl.cunguan_tag,dl.money,dl.red,dl.plan_id,dl.plan_load_id,dl.ecv_money,dl.raise_money,dl.increase_interest,dl.interestrate_money,ic.rate,ic.use_time FROM ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."interest_card as ic on dl.interestrate_id = ic.id  WHERE dl.deal_id=".$deal['id']." ORDER BY dl.id ASC ");

		foreach($fload_users[$deal['id']] as $k=>$v){
			$fload_users[$deal['id']][$k]['money'] = $v['money']+$v['red']+$v['ecv_money'];
		}
	}

	$loantype = intval($deal['loantype']);
	$LoanModule = $this->LoadLoanModule($loantype);

	$load_users = $LoanModule->make_user_repay_plan($deal,$idx,$repay_day,$true_time,$repay_id,$fload_users[$deal['id']],$total_money);
	foreach($load_users as $kk=>$vv){
		$repay_data =array();
		$repay_data = $vv;

		if($old_info = $GLOBALS['db']->getRow("SELECT id,has_repay,interestrate_money,load_id FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$vv['deal_id']." AND u_key=$kk and l_key= $idx ")){
			if($old_info['has_repay']==1)
			{
				unset($repay_data['self_money']);
				unset($repay_data['repay_money']);
				unset($repay_data['interest_money']);
				unset($repay_data['manage_money']);
				unset($repay_data['repay_manage_money']);
				unset($repay_data['manage_interest_money']);
				unset($repay_data['manage_interest_money_rebate']);
				unset($repay_data['has_repay']);
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data,"UPDATE","id=".$old_info['id']);
			/*//更新deal_load interestrate_money
			$load_data = array();
			$load_date["interestrate_money"] = $old_info["interestrate_money"];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$load_data,"UPDATE","id=".$old_info['load_id']);	*/
		}
		else{
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data,"INSERT");

			/*//更新deal_load interestrate_money
			$load_data = array();
			$load_data["interestrate_money"] = $repay_data["interestrate_money"];

			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$load_data,"UPDATE","id=".$repay_data['load_id']);*/
		}
	}
	$all_money = $GLOBALS['db']->getRow("SELECT sum(repay_money) as repay_money,sum(self_money) as self_money FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$vv['deal_id']." AND l_key= $idx ");
	if($all_money['repay_money'] != 0){
		$interest_money = $all_money['repay_money']-$all_money['self_money'];
		$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_repay SET repay_money = '".$all_money['repay_money']."',interest_money = '".$interest_money."' WHERE deal_id=".$vv['deal_id']." AND l_key= $idx");
	}
}
//载入贷款模板--
function LoadLoanModule($loantype){
	static $make_module = array();
	if(!isset($make_module[$loantype])){
		require APP_ROOT_PATH."system/loantype/loantype_".$loantype.".class.php";
		$obj_class = "loantype_".$loantype;
		$make_module[$loantype] = new $obj_class;
	}
	return $make_module[$loantype];
}
//理财计划全部放款
	 function do_repays(){
		$plan = $GLOBALS['db']->getAll('select id,deal_id from '.DB_PREFIX.'plan where deal_status=2 order by id asc ');
		if(!$plan){
			echo "失败";
		}
		foreach($plan as $key=>$value){
			$deal_id = unserialize($value['deal_id']);
			$arr = implode(",",$deal_id);
			require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
			require_once APP_ROOT_PATH."system/libs/user.php";
			$deal_infos = $GLOBALS['db']->getAll('select id,objectaccno,borrow_amount,user_id,fee  from '.DB_PREFIX.'deal  where id in('.$arr.') and deal_status=2');
			if(!$deal_infos){
				continue;
			}
			$deal_load_info_all = $GLOBALS['db']->getAll('select user_id from '.DB_PREFIX.'deal_load where deal_id in ('.$arr.')');
			$i = 0;
			foreach($deal_infos as $k=>$v){
				$publics = new Publics();
				$seqno = $publics ->seqno();
				$deals = new Deal();
				$arr1['type']='03';
				$arr1['objectaccNo']=$v['objectaccno'];
				$res = $deals->transaction($arr1); 
				if(!$res){
					continue;
				}
				if($res['outBody']['withdrawalamount']<$v['borrow_amount']){
					continue;
				}
				
				$data['deal_id'] = (string)$v['id'];
				$money = floatval($v['borrow_amount']-$v['fee']);
				$data['accountList'][] = array("oderNo"=>"0","debitAccountNo"=>$v['objectaccno'],"cebitAccountNo"=>$v['user_id'],"currency"=>"CNY","amount"=>$money,"summaryCode"=>"T14","amountType"=>'');
				$deal_load_info = $deal_load_info_all[$k];
				$bankAccountNo = $GLOBALS['db']->getOne('select bankcard from '.DB_PREFIX.'user_bank where user_id ='.$v['user_id'].' and cunguan_tag=1');
				if($v['fee']){
					$data['accountList'][] = array("oderNo"=>"1","debitAccountNo"=>$v['objectaccno'],"cebitAccountNo"=>'JCTPE20170807',"currency"=>"CNY","amount"=>$v['fee'],"summaryCode"=>"T12",'amountType'=>'01');
				}
				$data['contractList'] = array();
				foreach($deal_load_info as $key=>$value){
					$dl['oderNo'] ="$k";
					$dl['contractType']='01';
					$dl['contractRole']='01';
					$identifier = 'JCT_'.$v['user_id'].'_'.$value['user_id'].'_'.$v['objectaccno'].'_'.date("Ymd");//合同文件名
					$dl['contractFileNm']=$identifier;
					$dl['debitUserid']=$value['user_id'];
					$dl['cebitUserid']=$v['user_id'];
					$data['contractList'][]= $dl;
				} 
				
				$data['accNo'] =$v['user_id'];
				$data['money'] ="$money";
				$data['bankAccountNo'] ="$bankAccountNo";
				$data['objectaccNo'] =$v['objectaccno'];
				$repay = $deals ->repay_withdraw($seqno,'T14',$data);//标的放款
				unset($data);
				if($repay['respHeader']['respCode']=="P2PS000"){
					$i++;
				}  
			}
		}
		
		if($i>0){
			echo "成功";
		}else{
			echo "失败";
		}
	}
	//代扣还款成功后出款
	function do_loans($arr){
        $seqno=$arr['inBody']['oldbusinessSeqNo'];
        $repay = $GLOBALS['db']->getRow("SELECT has_repay,id  FROM ".DB_PREFIX."deal_repay where seqno='".$seqno."' order by id desc limit 1");
		if($repay['has_repay']!=3){
			echo 1;
			//echo "已处理";
			exit;
		}  
        if($arr['inBody']['respCode'] =="P2P0000"){
			$repay_info = $GLOBALS['db']->getRow("select dr.deal_id as deal_id,d.user_id as user_id,dr.repay_money as repay_money,dr.self_money as self_money,dr.interest_money as interest_money,d.objectaccno as objectaccno,d.repay_time as repay_time,dr.l_key as l_key from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.id = ".$repay['id']." and dr.has_repay =3 and dr.cunguan_tag=1");
			//修改状态
			$statusArr['calculate_status'] = 1;
			$statusArr['has_repay'] = 1; //设置已还款标志
			$statusArr['true_repay_time'] = strtotime(date('Y-m-d'));
			$statusArr['true_repay_date'] = date('Y-m-d', time());
			$statusArr['true_repay_money'] = $money;
			$statusArr['true_self_money'] = $repay_info['self_money'];
			$statusArr['true_interest_money'] = $repay_info['interest_money'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$statusArr,"UPDATE","id=".$repay['id']);
			$pub = new Publics();
			$seqno = $pub ->seqno();
			$infos = $GLOBALS['db']->getAll("select dlr.id,u.id as user_id,u.accno,dlr.raise_money,dlr.repay_money,dlr.interest_money,dlr.virtual_info,dlr.deal_id,dlr.load_id,dlr.self_money,dlr.increase_interest,dlr.interestrate_money,dlr.debts_deal_id from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."user u on u.id=dlr.user_id  where dlr.repay_id=".$repay['id']." and dlr.has_repay =0 and dlr.cunguan_tag=1");
			if(!$infos){
				echo 0;
				//echo "标第".$repay_info['deal_id'].":出借用户回款计划不存在";
				exit;
			}
			foreach($infos as $key => $value){
				$oderNo++;
				if($value['debts_deal_id']){
					$deal_id = $value['debts_deal_id'];
				}
				$repay1['oderNo'] = $oderNo;
				$repay1['oldbusinessSeqNo']="";
				$repay1['oldOderNo']="";
				$repay1['debitAccountNo']=$repay_info['objectaccno'];
				$repay1['cebitAccountNo']=$value['accno'];
				$repay1['currency']="CNY";
				$repay1['amount']=floatval($value['repay_money']);
				$repay1['summaryCode']="T05";
				$repay1['amountType']="";
				$repays[]=$repay1;
			}
			$data['accountList']=$repays;
			$data['contractList'] = array(array("oderNo"=>"","contractType"=>'',"contractRole"=>'',"contractFileNm"=>'',"debitUserid"=>'',"cebitUserid"=>''));
			$data['deal_repay_info']=$infos;
			$data['deal_id'] = (string)$repay_info['deal_id'];
			$data['objectaccNo'] = $repay_info['objectaccno'];
			$deal = new Deal();
			$res1 = $deal ->do_repay($seqno,'T05',$data);//出款
			$res1_code =$res1['respHeader']['respCode'];
			if($res1_code=="P2P0000"){
				foreach($infos as $key=>$value){
					if($value['self_money']>0){ //还本还息才需要解冻投资资金
						$datas['cunguan_lock_money'] = -$value['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
					}
					$datas['cunguan_money']=$value['repay_money'];
					if($value['increase_interest']>0){//奖励利息收益
						$increase_interest =$value;
						unset($increase_interest['interestrate_money']);
						unset($increase_interest['raise_money']);
						$inc_interest[] = $increase_interest;
					}
					if($value['interestrate_money']>0){//加息券增加的利息
						$interestrate_money = $value;
						unset($interestrate_money['increase_interest']);
						unset($interestrate_money['raise_money']);
						$interestrate[] = $interestrate_money;
					}
					if($value['raise_money']>0){//募集期收益
						$raise_money = $value;
						unset($raise_money['increase_interest']);
						unset($raise_money['interestrate_money']);
						$raise_arr[] = $raise_money;
					}
					//资金增加++++++++++++++++++++++
					$datas['create_time'] = time(); //还款时间
					$datas['brief'] = $value['virtual_info']; //虚拟货币消息
					$datas['deal_id'] = $value['deal_id'];
					$datas['load_repay_id'] = $repay['id'];
					$datas['load_id'] = $value['load_id'];
					$msg = $value['self_money']>0?"还本还息":"还息";	
					modify_account($datas, $value['user_id'], $msg, 5, $datas['brief'],1);
					//添加资金记录
					$statusArray['calculate_status'] = 1;
					$statusArray['has_repay'] = 1; //设置已还款标志
					$statusArray['true_repay_time'] = time();
					$statusArray['true_repay_date'] = date('Y-m-d', time());
					$statusArray['true_repay_money'] = $value['repay_money'];
					$statusArray['true_self_money'] = $value['self_money'];
					$statusArray['true_interest_money'] = $value['interest_money'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$statusArray,"UPDATE","id=".$value['id']);
				}
				if($inc_interest){//奖励收益
					$seqno = $pub ->seqno();
					$res1 = $deal ->earning_money($seqno,'T10',$inc_interest);
					$res1_code =$res1['respHeader']['respCode'];
					if($res1_code=="P2P0000"){
						foreach($inc_interest as $key=>$value){
							modify_account(array('cunguan_money'=>$value['increase_interest']), $value['user_id'], "奖励加息收益", 59, "奖励加息收益",1);
						}
					}
				}
				if($interestrate){//加息收益
					$seqno = $pub ->seqno();
					$res1 = $deal ->earning_money($seqno,'T10',$interestrate);
					$res1_code =$res1['respHeader']['respCode'];
					if($res1_code=="P2P0000"){
						foreach($inc_interest as $key=>$value){
							modify_account(array('cunguan_money'=>$value['interestrate_money']), $value['user_id'], "加息卡收益", 60, "加息卡收益",1);
						}
					}
				}
				if($repay['self_money']>0){//是否是最后一期且是还本还息类型
					if($raise_arr){//是否有募集期收益
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$raise_arr);//募集期收益
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code=="P2P0000"){
							foreach($raise_arr as $key=>$value){
								modify_account(array('cunguan_money'=>$value['raise_money']), $value['user_id'], "募集期收益", 58, "募集期收益",1);
							}
						}
					}
					$seqno1 = $pub ->seqno();
					$res2 = $deal -> save_deal($repay['deal_id'],$seqno1,"07",0);//设置标的已结束
					$res2_code = $res2['respHeader']['respCode'];
					$deal_data['cunguan_status']="07";
					$deal_data['deal_status']=5;
					if($deal_id>0){
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal_id);
					}
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal_info['id']);
					$GLOBALS['db']->query("UPDATE FROM ".DB_PREFIX."plan SET deal_status=5,cunguan_status='07',update_time=".time()." WHERE id=".$id."");
				}
			}else{
				$this->success("标第".$v."出款失败:".$res1['respHeader']['respMsg']);
				exit;
			}
		}else{
			//修改状态
			$statusArr['has_repay'] = 0; //设置已还款标志
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$statusArr,"UPDATE","id=".$repay['id']);
		}
	}


    /*
        提现批量拒绝
    */
    function user_carry_reject(){
        $user_carry = $GLOBALS['db']->getAll("select id,user_id,status,money from ".DB_PREFIX."user_carry where cunguan_pwd=1 and status=0 and create_time between 1507996800 and 1511366400 limit 1");
        $count = 0;
        $count_all = count($user_carry);
        foreach($user_carry as $k=>$v){
            $user_info = $GLOBALS['db']->getRow("select id,is_effect,AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."'),cunguan_lock_money from ".DB_PREFIX."user where id =".$v['user_id']);
            if(!$user_info){
                continue;
            }
            // 扣冻结  加存管余额
            $datas['create_time'] = time(); //提现处理时间
            $datas['brief'] = '提现审核失败'; //虚拟货币消息
            $datas['cunguan_money'] = $v['money'];
            $datas['cunguan_lock_money'] = -$v['money'];
            $msg = '提现初审拒绝';
            modify_account($datas, $v['user_id'], $msg, 8, $datas['brief'],1);

            // 修改提现状态
            $GLOBALS['db']->query("update ".DB_PREFIX."user_carry set status=2 where id=".$v['id']);
            $count++;
        }
        echo "一共".$count_all."条数据，执行成功".$count."条";
    }
	
	function deal_upd(){
		$deal_id = $_REQUEST['id'];
		$data = $GLOBALS['db']->getRow("select id,name,rate,user_id from ".DB_PREFIX."deal where id = ".$deal_id."");
		$data['borrow_amount'] = "5000";
		$deal = new Deal();
		$res2 = $deal -> deals('P04',"03",$data);//设置标的已结束
		var_dump($res2);
	}
    //处理提现成功未收到异步通知
    function user_carry_suc(){
        //$ids = '451170,451473,451519,451614,451620,451622,451641,451663,451665,451669,451670,451672,451673,451674,451677,451680,451684,451695,451708,451713,451723,451725,451729,451736,451738,451741,451745,451751,451752,451756,451761,451763,451768,451776,451778,451787,451788,451789,451790,451791,451792,451796,451797,451798,451799,451801,451818,451822,451824,451827,451828,451839,451844,451850,451856,451860,451861,451864,451865,451879,451884,451889,451897,451905,451914,451915,451922,451924,451929,451936,451941,451948,451953,451954,451955,451956,451958,451961,451962,451978,451979,451980,451987,451994,451996,451997,452004,452007,452017,452018,452021,452023,452025,452026,452028,452032,452033,452034,452037,452041,452042,452043,452045,452052,452056,452057,452059,452067,452068,452069,452078,452080,452082,452085,452086,452091,452103,452119,452120,452121,452124,452126,452127,452128,452135,452142,452144,452149,452150,452152,452153,452154,452158,452167,452169,452170,452172,452178,452179,452180,452181,452184,452186,452189,452191,452193,452195,452198,452202,452204,452205,452206,452208,452212,452213,452215,452217,452225,452230,452231,452239,452240,452243,452244,452245,452246,452254,452255,452258,452259,452262,452288,452291,452298,452346,452348,452350';
        $ids = '314085,314086,314087,314088,314090';
        $id_array = explode(',',$ids);
        $count_id = count($id_array);
        $count_no = 0;
        $count_yes = 0;
        $count_nos = 0;
        foreach($id_array as $k=>$v){
            $user_carry = $GLOBALS['db']->getRow("select id,user_id,status,money,seqno,fee from ".DB_PREFIX."user_carry where id=".$v." and status=5");
            if(empty($user_carry)){
                $count_no++;
            }else{
                $Publics = new Publics();
                $map['reqHeader'] = $Publics->reqheader("JZCX01");
                $map['inBody']['businessSeqNo'] = $Publics->seqno();//业务流水号
                $map['inBody']['oldbusinessSeqNo'] = strval($user_carry['seqno']);//原交易流水号
                $map['inBody']['operType'] = "W01";//原交易类型
                $dep = $Publics ->sign($map);
                $map['reqHeader']['signTime'] = $dep['signTime'];
                $map['reqHeader']['signature'] = $dep['signature'];
                $dep = $Publics->encrypt(json_encode($map));
                $DepSdk = new DepSdk();
                $result=$DepSdk->transStatusQuery($dep);
                //echo "<pre>";
                //print_r($result);die;
                if($result['outBody']['respCode']=='P2P0000'){
                    $count_yes++;
                    modify_account(array("cunguan_lock_money"=>-$user_carry['money']),$user_carry['user_id'],"存管提现成功",8,"存管提现成功",1);
                    $user_cg_money = get_user_info("*","id=".$user_carry['user_id']);
                    $memo = $user_carry['fee'] ? "提现成功,-手续费：".$user_carry['fee']."元，实际到账：".($user_carry['money']-$user_carry['fee'])."元" : "存管提现成功";
                    $money_log_info['memo'] = $memo;
                    $money_log_info['brief'] = '存管提现成功';
                    $money_log_info['money'] = round(-$user_carry['money'],2);
                    $money_log_info['account_money'] = $user_cg_money['cunguan_money'];
                    $money_log_info['user_id'] = $user_carry['user_id'];
                    $money_log_info['create_time'] = TIME_UTC;
                    $money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
                    $money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
                    $money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
                    $money_log_info['type'] = 8;
                    $money_log_info['cunguan_tag'] = 1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
                    // 修改提现状态
                    $GLOBALS['db']->query("update ".DB_PREFIX."user_carry set status=1 where id=".$v);
                }else{
                    $count_nos++;
                    // 扣冻结  加存管余额
                    $datas['create_time'] = time(); //提现处理时间
                    $datas['brief'] = '存管提现失败'; //虚拟货币消息
                    $datas['cunguan_money'] = $user_carry['money'];
                    $datas['cunguan_lock_money'] = -$user_carry['money'];
                    $msg = $result['outBody']['respMsg'];
                    modify_account($datas, $user_carry['user_id'], $msg, 8, $datas['brief'],1);
                    // 修改提现状态
                    $GLOBALS['db']->query("update ".DB_PREFIX."user_carry set status=6 where id=".$v);
                }
            }
        }
        echo "一共".$count_id."条数据，打款成功".$count_yes."条,打款失败".$count_nos."条，查询失败".$count_no."条";
    }
}
?>